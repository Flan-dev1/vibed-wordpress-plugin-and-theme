<?php

namespace StarFlan\RealEstate;

defined( 'ABSPATH' ) || exit;

final class MetaBoxes {
	private $records;
	private $saving = false;

	public function __construct( RecordService $records ) {
		$this->records = $records;
	}

	public function register(): void {
		foreach ( Schema::all() as $key => $schema ) {
			remove_meta_box( 'postcustom', $schema['post_type'], 'normal' );
			add_meta_box( 'starflan-fields', sprintf( __( '%s Details', 'starflan-real-estate' ), $schema['label'] ), array( $this, 'render' ), $schema['post_type'], 'normal', 'high', array( 'schema_key' => $key ) );
		}
	}

	public function render( \WP_Post $post, array $box ): void {
		$schema = Schema::get( $box['args']['schema_key'] );
		wp_nonce_field( 'starflan_save_record', 'starflan_record_nonce' );
		echo '<div class="starflan-fields">';
		foreach ( $schema['fields'] as $key => $field ) {
			$value = $this->value( $post, $field );
			AdminPage::render_field( $key, $field, $value );
		}
		echo '</div>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		if ( $this->saving ) {
			return;
		}
		if ( ! isset( $_POST['starflan_record_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['starflan_record_nonce'] ) ), 'starflan_save_record' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$type = Schema::key_by_post_type( $post->post_type );
		if ( $type ) {
			$raw = isset( $_POST['starflan'] ) && is_array( $_POST['starflan'] ) ? wp_unslash( $_POST['starflan'] ) : array();
			$this->saving = true;
			$this->records->update( $post_id, $type, $raw );
			$this->saving = false;
		}
	}

	private function value( \WP_Post $post, array $field ) {
		if ( 'post_title' === $field['storage'] ) {
			return $post->post_title;
		}
		if ( 'post_content' === $field['storage'] ) {
			return $post->post_content;
		}
		return get_post_meta( $post->ID, $field['meta_key'], true );
	}
}
