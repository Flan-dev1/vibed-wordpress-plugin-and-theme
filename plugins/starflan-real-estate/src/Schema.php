<?php

namespace StarFlan\RealEstate;

defined( 'ABSPATH' ) || exit;

final class Schema {
	/**
	 * Data definitions are intentionally centralized. Add future types with the
	 * starflan_data_schemas filter instead of duplicating admin or import code.
	 */
	public static function all(): array {
		$schemas = array(
			'city' => array(
				'label'       => __( 'City', 'starflan-real-estate' ),
				'plural'      => __( 'Cities', 'starflan-real-estate' ),
				'post_type'   => 'sf_city',
				'title_field' => 'name',
				'fields'      => array(
					'name'       => array( 'label' => __( 'Name', 'starflan-real-estate' ), 'type' => 'text', 'storage' => 'post_title', 'required' => true ),
					'image'      => array( 'label' => __( 'Image', 'starflan-real-estate' ), 'type' => 'media', 'storage' => 'meta', 'meta_key' => '_sf_image_id' ),
					'properties' => array( 'label' => __( 'Estatik Properties', 'starflan-real-estate' ), 'type' => 'estatik_properties', 'storage' => 'meta', 'meta_key' => '_sf_estatik_property_ids' ),
				),
			),
			'testimonial' => array(
				'label'       => __( 'Testimonial', 'starflan-real-estate' ),
				'plural'      => __( 'Testimonials', 'starflan-real-estate' ),
				'post_type'   => 'sf_testimonial',
				'title_field' => 'name',
				'fields'      => array(
					'rating'      => array( 'label' => __( 'Rating', 'starflan-real-estate' ), 'type' => 'number', 'storage' => 'meta', 'meta_key' => '_sf_rating', 'min' => 0, 'max' => 5, 'step' => 0.1, 'required' => true ),
					'name'        => array( 'label' => __( 'Name', 'starflan-real-estate' ), 'type' => 'text', 'storage' => 'meta', 'meta_key' => '_sf_name' ),
					'testimonial' => array( 'label' => __( 'Testimonial', 'starflan-real-estate' ), 'type' => 'textarea', 'storage' => 'post_content', 'required' => true ),
				),
			),
		);

		return apply_filters( 'starflan_data_schemas', $schemas );
	}

	public static function get( string $key ): ?array {
		$schemas = self::all();
		return isset( $schemas[ $key ] ) ? $schemas[ $key ] : null;
	}

	public static function key_by_post_type( string $post_type ): ?string {
		foreach ( self::all() as $key => $schema ) {
			if ( $schema['post_type'] === $post_type ) {
				return $key;
			}
		}
		return null;
	}
}
