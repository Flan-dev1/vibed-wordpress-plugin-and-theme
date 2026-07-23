<?php

use StarFlan\RealEstate\CityHierarchy;

defined( 'ABSPATH' ) || exit;

function starflan_get_subcity_ids( int $city_id, bool $recursive = false ): array {
	return $recursive ? CityHierarchy::descendant_ids( $city_id ) : CityHierarchy::direct_subcity_ids( $city_id );
}

function starflan_get_subcity_count( int $city_id, bool $recursive = false ): int {
	return CityHierarchy::subcity_count( $city_id, $recursive );
}

function starflan_get_city_property_ids( int $city_id, bool $include_descendants = true ): array {
	return CityHierarchy::property_ids( $city_id, $include_descendants );
}

function starflan_filter_properties_by_city( array $query_args, int $city_id ): array {
	return CityHierarchy::filter_property_query_args( $query_args, $city_id );
}
