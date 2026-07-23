<?php

namespace StarFlan\RealEstate;

defined( 'ABSPATH' ) || exit;

final class Plugin {
	private static $instance;

	public static function instance(): self {
		if ( ! self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	public function boot(): void {
		$registrar = new PostTypeRegistrar();
		$records = new RecordService();
		$meta_boxes = new MetaBoxes( $records );
		$admin = new AdminPage( $records );

		add_action( 'init', array( $registrar, 'register' ) );
		add_action( 'rest_api_init', array( CityHierarchy::class, 'register_rest_fields' ) );
		add_filter( 'rest_pre_insert_sf_city', array( CityHierarchy::class, 'validate_rest_hierarchy' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $meta_boxes, 'register' ) );
		add_action( 'save_post', array( $meta_boxes, 'save' ), 10, 2 );
		add_action( 'admin_menu', array( $admin, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $admin, 'assets' ) );
		add_action( 'admin_post_starflan_create', array( $admin, 'handle_create' ) );
		add_action( 'admin_post_starflan_import', array( $admin, 'handle_import' ) );
		add_action( 'admin_post_starflan_assign_properties', array( $admin, 'handle_assign_properties' ) );
		add_action( 'wp_ajax_starflan_search_estatik_properties', array( $admin, 'search_estatik_properties' ) );
	}

	public static function activate(): void {
		( new PostTypeRegistrar() )->register();
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
