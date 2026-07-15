<?php

namespace StarFlan\RealEstate;

use WP_Error;

defined( 'ABSPATH' ) || exit;

final class RecordService {
	/** @return int|WP_Error */
	public function create( string $type, array $raw ) {
		$schema = Schema::get( $type );
		if ( ! $schema ) {
			return new WP_Error( 'invalid_type', __( 'Unknown data type.', 'starflan-real-estate' ) );
		}

		$values = array();
		foreach ( $schema['fields'] as $key => $field ) {
			if ( ! empty( $field['required'] ) && ( ! array_key_exists( $key, $raw ) || '' === trim( (string) $raw[ $key ] ) ) ) {
				return new WP_Error( 'required_field', sprintf( __( '%s is required.', 'starflan-real-estate' ), $field['label'] ) );
			}
			$value = self::sanitize_value( $raw[ $key ] ?? '', $field );
			if ( 'relation' === $field['type'] && ( $value || ! empty( $field['required'] ) ) && ! $this->valid_relation( $value, $field['target'] ) ) {
				return new WP_Error( 'invalid_relation', sprintf( __( '%s is invalid.', 'starflan-real-estate' ), $field['label'] ) );
			}
			if ( 'estatik_properties' === $field['type'] && ! $this->valid_estatik_properties( $value ) ) {
				return new WP_Error( 'invalid_estatik_properties', __( 'One or more Estatik property IDs are invalid.', 'starflan-real-estate' ) );
			}
			$values[ $key ] = $value;
		}
		$values = $this->apply_defaults( $type, $values );

		$title = $values[ $schema['title_field'] ] ?? '';
		if ( ! $title ) {
			$title = sprintf( '%s %s', $schema['label'], current_time( 'Y-m-d H:i:s' ) );
		}
		$post = array(
			'post_type'   => $schema['post_type'],
			'post_status' => 'publish',
			'post_title'  => $title,
		);
		foreach ( $schema['fields'] as $key => $field ) {
			if ( 'post_content' === $field['storage'] ) {
				$post['post_content'] = $values[ $key ];
			}
		}

		$post_id = wp_insert_post( wp_slash( $post ), true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		$this->save_fields( $post_id, $schema, $values );
		do_action( 'starflan_record_created', $post_id, $type, $values );
		return $post_id;
	}

	public function update( int $post_id, string $type, array $raw ): ?WP_Error {
		$schema = Schema::get( $type );
		if ( ! $schema ) {
			return new WP_Error( 'invalid_type', __( 'Unknown data type.', 'starflan-real-estate' ) );
		}
		$values = array();
		foreach ( $schema['fields'] as $key => $field ) {
			if ( ! empty( $field['required'] ) && ( ! array_key_exists( $key, $raw ) || '' === trim( (string) $raw[ $key ] ) ) ) {
				return new WP_Error( 'required_field', sprintf( __( '%s is required.', 'starflan-real-estate' ), $field['label'] ) );
			}
			$value = self::sanitize_value( $raw[ $key ] ?? '', $field );
			if ( 'relation' === $field['type'] && ( $value || ! empty( $field['required'] ) ) && ! $this->valid_relation( $value, $field['target'] ) ) {
				return new WP_Error( 'invalid_relation', sprintf( __( '%s is invalid.', 'starflan-real-estate' ), $field['label'] ) );
			}
			if ( 'estatik_properties' === $field['type'] && ! $this->valid_estatik_properties( $value ) ) {
				return new WP_Error( 'invalid_estatik_properties', __( 'One or more Estatik property IDs are invalid.', 'starflan-real-estate' ) );
			}
			$values[ $key ] = $value;
		}
		$values = $this->apply_defaults( $type, $values );
		$post = array( 'ID' => $post_id );
		if ( isset( $values[ $schema['title_field'] ] ) && $values[ $schema['title_field'] ] ) {
			$post['post_title'] = $values[ $schema['title_field'] ];
		}
		foreach ( $schema['fields'] as $key => $field ) {
			if ( 'post_content' === $field['storage'] ) {
				$post['post_content'] = $values[ $key ];
			}
		}
		$result = wp_update_post( wp_slash( $post ), true );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$this->save_fields( $post_id, $schema, $values );
		do_action( 'starflan_record_updated', $post_id, $type, $values );
		return null;
	}

	public function assign_estatik_properties( int $city_id, $raw_property_ids ): ?WP_Error {
		$city_schema = Schema::get( 'city' );
		if ( ! $city_schema || $city_schema['post_type'] !== get_post_type( $city_id ) ) {
			return new WP_Error( 'invalid_city', __( 'The selected City is invalid.', 'starflan-real-estate' ) );
		}

		$field = $city_schema['fields']['properties'];
		$property_ids = self::sanitize_value( $raw_property_ids, $field );
		if ( ! $this->valid_estatik_properties( $property_ids ) ) {
			return new WP_Error( 'invalid_estatik_properties', __( 'One or more Estatik property IDs are invalid.', 'starflan-real-estate' ) );
		}

		update_post_meta( $city_id, $field['meta_key'], $property_ids );
		do_action( 'starflan_city_properties_assigned', $city_id, $property_ids );
		return null;
	}

	public static function sanitize_value( $value, array $field ) {
		switch ( $field['type'] ) {
			case 'estatik_properties':
				$ids = is_array( $value ) ? $value : preg_split( '/[\s,;]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY );
				return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
			case 'media':
			case 'relation':
				return absint( $value );
			case 'number':
				$value = is_numeric( $value ) ? (float) $value : 0.0;
				$value = isset( $field['min'] ) ? max( (float) $field['min'], $value ) : $value;
				return isset( $field['max'] ) ? min( (float) $field['max'], $value ) : $value;
			case 'url':
				return esc_url_raw( $value );
			case 'textarea':
				return sanitize_textarea_field( $value );
			default:
				return sanitize_text_field( $value );
		}
	}

	private function save_fields( int $post_id, array $schema, array $values ): void {
		foreach ( $schema['fields'] as $key => $field ) {
			if ( 'meta' === $field['storage'] ) {
				update_post_meta( $post_id, $field['meta_key'], $values[ $key ] );
			}
		}
	}

	private function apply_defaults( string $type, array $values ): array {
		if ( 'city' === $type && empty( $values['image'] ) && ! empty( $values['name'] ) ) {
			$values['image'] = $this->find_city_image_id( (string) $values['name'] );
		}
		return $values;
	}

	private function find_city_image_id( string $city_name ): int {
		$image_id = 0;
		$slug = sanitize_title( $city_name );

		if ( $slug ) {
			$slug_matches = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
					'post_mime_type' => 'image',
					'name'           => $slug,
					'posts_per_page' => 1,
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'fields'         => 'ids',
				)
			);
			$image_id = $slug_matches ? (int) $slug_matches[0] : 0;
		}

		if ( ! $image_id ) {
			$title_matches = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
					'post_mime_type' => 'image',
					's'              => sanitize_text_field( $city_name ),
					'posts_per_page' => 20,
					'orderby'        => 'ID',
					'order'          => 'ASC',
				)
			);
			foreach ( $title_matches as $attachment ) {
				if ( 0 === strcasecmp( trim( get_the_title( $attachment ) ), trim( $city_name ) ) ) {
					$image_id = (int) $attachment->ID;
					break;
				}
			}
		}

		$image_id = absint( apply_filters( 'starflan_default_city_image_id', $image_id, $city_name ) );
		return $image_id && wp_attachment_is_image( $image_id ) ? $image_id : 0;
	}

	private function valid_relation( int $post_id, string $target ): bool {
		$target_schema = Schema::get( $target );
		return $target_schema && $target_schema['post_type'] === get_post_type( $post_id );
	}

	private function valid_estatik_properties( array $post_ids ): bool {
		foreach ( $post_ids as $post_id ) {
			if ( ! Estatik::is_property( (int) $post_id ) ) {
				return false;
			}
		}
		return true;
	}
}
