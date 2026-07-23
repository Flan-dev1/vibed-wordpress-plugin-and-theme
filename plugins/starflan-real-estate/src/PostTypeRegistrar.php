<?php

namespace StarFlan\RealEstate;

defined( 'ABSPATH' ) || exit;

final class PostTypeRegistrar {
	public function register(): void {
		foreach ( Schema::all() as $schema ) {
			register_post_type(
				$schema['post_type'],
				array(
					'labels' => array(
						'name'          => $schema['plural'],
						'singular_name' => $schema['label'],
						'add_new_item'  => sprintf( __( 'Add New %s', 'starflan-real-estate' ), $schema['label'] ),
						'edit_item'     => sprintf( __( 'Edit %s', 'starflan-real-estate' ), $schema['label'] ),
					),
					'public'          => false,
					'show_ui'         => true,
					'show_in_menu'    => 'starflan-data',
					'show_in_rest'    => true,
					'menu_icon'       => 'dashicons-admin-home',
					// Required for registered metadata to be writable through REST.
					'supports'        => array( 'custom-fields' ),
					'capability_type' => 'post',
					'map_meta_cap'    => true,
				)
			);

			$this->register_meta( $schema );
		}
	}

	private function register_meta( array $schema ): void {
		foreach ( $schema['fields'] as $field ) {
			if ( 'meta' !== $field['storage'] ) {
				continue;
			}
			$type = 'number' === $field['type'] ? 'number' : ( in_array( $field['type'], array( 'estatik_properties', 'relations' ), true ) ? 'array' : ( in_array( $field['type'], array( 'media', 'relation' ), true ) ? 'integer' : 'string' ) );
			$show_in_rest = 'array' === $type
				? array( 'schema' => array( 'type' => 'array', 'items' => array( 'type' => 'integer' ) ) )
				: true;
			register_post_meta(
				$schema['post_type'],
				$field['meta_key'],
				array(
					'type'              => $type,
					'single'            => true,
					'show_in_rest'      => $show_in_rest,
					'sanitize_callback' => static function ( $value ) use ( $field ) {
						return RecordService::sanitize_value( $value, $field );
					},
					'auth_callback'     => static function (): bool {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
