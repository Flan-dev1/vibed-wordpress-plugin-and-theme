<?php

// Lightweight regression test: run with `php tests/city-hierarchy.php`.
define( 'ABSPATH', __DIR__ );

$GLOBALS['sf_test_posts'] = array(
	1 => 'sf_city',
	2 => 'sf_city',
	3 => 'sf_city',
	4 => 'sf_city',
);
$GLOBALS['sf_test_meta'] = array(
	1 => array( '_sf_parent_city_ids' => array(), '_sf_estatik_property_ids' => array( 101 ) ),
	2 => array( '_sf_parent_city_ids' => array( 1 ), '_sf_estatik_property_ids' => array( 102 ) ),
	3 => array( '_sf_parent_city_ids' => array( 1 ), '_sf_estatik_property_ids' => array( 103 ) ),
	4 => array( '_sf_parent_city_ids' => array( 2, 3 ), '_sf_estatik_property_ids' => array( 104, 102 ) ),
);

function get_post_type( $post_id ) {
	return $GLOBALS['sf_test_posts'][ $post_id ] ?? false;
}

function get_post_meta( $post_id, $key, $single ) {
	return $GLOBALS['sf_test_meta'][ $post_id ][ $key ] ?? array();
}

function get_posts( $args ) {
	return array_keys( $GLOBALS['sf_test_posts'] );
}

function apply_filters( $hook, $value ) {
	return $value;
}

function absint( $value ) {
	return abs( (int) $value );
}

function register_rest_field( $post_type, $field_name, $args ) {
	$GLOBALS['sf_test_rest_fields'][ $field_name ] = $args;
}

require dirname( __DIR__ ) . '/src/CityHierarchy.php';

use StarFlan\RealEstate\CityHierarchy;

function sf_assert_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

sf_assert_same( array( 2, 3 ), CityHierarchy::direct_subcity_ids( 1 ), 'Direct subcities are incorrect.' );
sf_assert_same( array( 2, 3, 4 ), CityHierarchy::descendant_ids( 1 ), 'Recursive descendants should be unique across multiple paths.' );
sf_assert_same( array( 2, 3, 1 ), CityHierarchy::ancestor_ids( 4 ), 'Ancestors are incorrect.' );
sf_assert_same( 2, CityHierarchy::subcity_count( 1 ), 'Direct count is incorrect.' );
sf_assert_same( 3, CityHierarchy::subcity_count( 1, true ), 'Recursive count is incorrect.' );
sf_assert_same( array( 101, 102, 103, 104 ), CityHierarchy::property_ids( 1 ), 'Inherited properties are incorrect or duplicated.' );
sf_assert_same( true, CityHierarchy::would_create_cycle( 1, array( 4 ) ), 'A descendant parent must be rejected.' );
sf_assert_same( false, CityHierarchy::would_create_cycle( 4, array( 2, 3 ) ), 'Valid multiple parents must be accepted.' );
sf_assert_same( array( 103 ), CityHierarchy::filter_property_query_args( array( 'post__in' => array( 103, 999 ) ), 1 )['post__in'], 'Existing query restrictions must be intersected.' );

CityHierarchy::register_rest_fields();
$rest_count = $GLOBALS['sf_test_rest_fields']['descendant_count']['get_callback']( array( 'id' => 1 ) );
sf_assert_same( 3, $rest_count, 'REST descendant count is incorrect.' );

echo "City hierarchy tests passed.\n";
