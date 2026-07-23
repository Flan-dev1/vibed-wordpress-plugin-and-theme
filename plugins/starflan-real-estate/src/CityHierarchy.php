<?php

namespace StarFlan\RealEstate;

defined( 'ABSPATH' ) || exit;

/**
 * Read-only city hierarchy and inherited-property queries.
 *
 * Cities form a directed acyclic graph: a city may have more than one parent,
 * and a property's membership flows upward through every ancestor path.
 */
final class CityHierarchy {
	public const PARENT_META_KEY = '_sf_parent_city_ids';
	public const PROPERTY_META_KEY = '_sf_estatik_property_ids';

	public static function register_rest_fields(): void {
		$fields = array(
			'parent_city_ids' => array( 'type' => 'array', 'items' => array( 'type' => 'integer' ), 'callback' => 'parent_ids' ),
			'direct_subcity_ids' => array( 'type' => 'array', 'items' => array( 'type' => 'integer' ), 'callback' => 'direct_subcity_ids' ),
			'descendant_city_ids' => array( 'type' => 'array', 'items' => array( 'type' => 'integer' ), 'callback' => 'descendant_ids' ),
			'subcity_count' => array( 'type' => 'integer', 'callback' => 'subcity_count' ),
			'descendant_count' => array( 'type' => 'integer', 'callback' => 'recursive_subcity_count' ),
			'effective_property_ids' => array( 'type' => 'array', 'items' => array( 'type' => 'integer' ), 'callback' => 'property_ids' ),
		);

		foreach ( $fields as $field_name => $definition ) {
			$callback = $definition['callback'];
			unset( $definition['callback'] );
			$definition['context'] = array( 'view', 'edit' );
			register_rest_field(
				'sf_city',
				$field_name,
				array(
					'get_callback' => static function ( array $object ) use ( $callback ) {
						$city_id = isset( $object['id'] ) ? (int) $object['id'] : 0;
						if ( 'recursive_subcity_count' === $callback ) {
							return self::subcity_count( $city_id, true );
						}
						return call_user_func( array( self::class, $callback ), $city_id );
					},
					'schema' => $definition,
				)
			);
		}
	}

	/** Validate REST meta updates before WordPress writes the parent IDs. */
	public static function validate_rest_hierarchy( $prepared_post, $request ) {
		$meta = $request->get_param( 'meta' );
		if ( ! is_array( $meta ) || ! array_key_exists( self::PARENT_META_KEY, $meta ) ) {
			return $prepared_post;
		}

		$parent_ids = RecordService::sanitize_value( $meta[ self::PARENT_META_KEY ], array( 'type' => 'relations' ) );
		foreach ( $parent_ids as $parent_id ) {
			if ( ! self::is_city( (int) $parent_id ) ) {
				return new \WP_Error( 'invalid_city_parent', __( 'One or more parent cities are invalid.', 'starflan-real-estate' ), array( 'status' => 400 ) );
			}
		}

		$city_id = isset( $prepared_post->ID ) ? (int) $prepared_post->ID : 0;
		if ( $city_id && self::would_create_cycle( $city_id, $parent_ids ) ) {
			return new \WP_Error( 'city_hierarchy_cycle', __( 'A city cannot be its own parent or a child of one of its subcities.', 'starflan-real-estate' ), array( 'status' => 400 ) );
		}

		return $prepared_post;
	}

	public static function is_city( int $city_id ): bool {
		return 0 < $city_id && 'sf_city' === get_post_type( $city_id );
	}

	/** @return int[] */
	public static function parent_ids( int $city_id ): array {
		if ( ! self::is_city( $city_id ) ) {
			return array();
		}

		return self::normalize_ids( get_post_meta( $city_id, self::PARENT_META_KEY, true ) );
	}

	/** @return int[] */
	public static function direct_subcity_ids( int $city_id ): array {
		if ( ! self::is_city( $city_id ) ) {
			return array();
		}

		$city_ids = get_posts(
			array(
				'post_type'      => 'sf_city',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);
		$subcity_ids = array();
		foreach ( $city_ids as $candidate_id ) {
			$candidate_id = (int) $candidate_id;
			if ( in_array( $city_id, self::parent_ids( $candidate_id ), true ) ) {
				$subcity_ids[] = $candidate_id;
			}
		}

		return self::normalize_ids( apply_filters( 'starflan_city_direct_subcity_ids', $subcity_ids, $city_id ) );
	}

	/** @return int[] */
	public static function descendant_ids( int $city_id ): array {
		if ( ! self::is_city( $city_id ) ) {
			return array();
		}

		$visited = array( $city_id => true );
		$descendants = array();
		$queue = self::direct_subcity_ids( $city_id );
		while ( $queue ) {
			$current_id = (int) array_shift( $queue );
			if ( isset( $visited[ $current_id ] ) ) {
				continue;
			}
			$visited[ $current_id ] = true;
			$descendants[] = $current_id;
			$queue = array_merge( $queue, self::direct_subcity_ids( $current_id ) );
		}

		return self::normalize_ids( apply_filters( 'starflan_city_descendant_ids', $descendants, $city_id ) );
	}

	/** @return int[] */
	public static function ancestor_ids( int $city_id ): array {
		if ( ! self::is_city( $city_id ) ) {
			return array();
		}

		$visited = array( $city_id => true );
		$ancestors = array();
		$queue = self::parent_ids( $city_id );
		while ( $queue ) {
			$current_id = (int) array_shift( $queue );
			if ( isset( $visited[ $current_id ] ) || ! self::is_city( $current_id ) ) {
				continue;
			}
			$visited[ $current_id ] = true;
			$ancestors[] = $current_id;
			$queue = array_merge( $queue, self::parent_ids( $current_id ) );
		}

		return self::normalize_ids( apply_filters( 'starflan_city_ancestor_ids', $ancestors, $city_id ) );
	}

	public static function subcity_count( int $city_id, bool $recursive = false ): int {
		return count( $recursive ? self::descendant_ids( $city_id ) : self::direct_subcity_ids( $city_id ) );
	}

	/** @return int[] */
	public static function property_ids( int $city_id, bool $include_descendants = true ): array {
		if ( ! self::is_city( $city_id ) ) {
			return array();
		}

		$city_ids = array( $city_id );
		if ( $include_descendants ) {
			$city_ids = array_merge( $city_ids, self::descendant_ids( $city_id ) );
		}

		$property_ids = array();
		foreach ( $city_ids as $member_city_id ) {
			$property_ids = array_merge( $property_ids, self::normalize_ids( get_post_meta( $member_city_id, self::PROPERTY_META_KEY, true ) ) );
		}

		return self::normalize_ids( apply_filters( 'starflan_city_property_ids', $property_ids, $city_id, $include_descendants, $city_ids ) );
	}

	/**
	 * Restrict arbitrary WP_Query arguments to a city's effective properties.
	 * An existing post__in restriction is intersected instead of overwritten.
	 */
	public static function filter_property_query_args( array $query_args, int $city_id ): array {
		if ( ! self::is_city( $city_id ) ) {
			return $query_args;
		}

		$property_ids = self::property_ids( $city_id, true );
		if ( isset( $query_args['post__in'] ) ) {
			$property_ids = array_values( array_intersect( self::normalize_ids( $query_args['post__in'] ), $property_ids ) );
		}
		$query_args['post__in'] = $property_ids ?: array( 0 );

		return apply_filters( 'starflan_city_property_query_args', $query_args, $city_id, $property_ids );
	}

	public static function would_create_cycle( int $city_id, array $parent_ids ): bool {
		if ( in_array( $city_id, $parent_ids, true ) ) {
			return true;
		}

		$descendant_ids = self::descendant_ids( $city_id );
		return (bool) array_intersect( $parent_ids, $descendant_ids );
	}

	/** @return int[] */
	private static function normalize_ids( $ids ): array {
		$ids = is_array( $ids ) ? $ids : array();
		return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	}
}
