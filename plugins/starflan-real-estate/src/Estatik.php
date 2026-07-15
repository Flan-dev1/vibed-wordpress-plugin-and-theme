<?php

namespace StarFlan\RealEstate;

defined( 'ABSPATH' ) || exit;

final class Estatik {
	/**
	 * Estatik currently stores listings as WordPress posts of type "properties".
	 * The filter keeps this integration adaptable to another Estatik version or
	 * a site-specific post type override.
	 */
	public static function property_post_type(): string {
		return sanitize_key( apply_filters( 'starflan_estatik_property_post_type', 'properties' ) );
	}

	public static function is_available(): bool {
		return post_type_exists( self::property_post_type() );
	}

	public static function is_property( int $post_id ): bool {
		return 0 < $post_id && self::property_post_type() === get_post_type( $post_id );
	}

	/** @return \WP_Post[] */
	public static function search( string $search = '', int $limit = 20 ): array {
		if ( ! self::is_available() ) {
			return array();
		}

		$properties = get_posts(
			array(
				'post_type'        => self::property_post_type(),
				'post_status'      => 'any',
				'posts_per_page'   => max( 1, min( 50, $limit ) ),
				's'                => sanitize_text_field( $search ),
				'orderby'          => 'title',
				'order'            => 'ASC',
				'suppress_filters' => false,
			)
		);

		if ( ctype_digit( $search ) ) {
			$exact = get_post( (int) $search );
			if ( $exact && self::is_property( $exact->ID ) ) {
				array_unshift( $properties, $exact );
			}
		}

		$unique = array();
		foreach ( $properties as $property ) {
			$unique[ $property->ID ] = $property;
		}
		return array_slice( array_values( $unique ), 0, $limit );
	}
}
