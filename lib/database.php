<?php
/**
 * Database functions for RentFetch
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Create the custom properties search table on plugin activation
 */
function rentfetch_create_properties_search_table() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'rentfetch_properties_search';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		property_id varchar(255) NOT NULL,
		post_id bigint(20) NOT NULL,
		title varchar(255) NOT NULL,
		latitude decimal(10,8) NOT NULL,
		longitude decimal(11,8) NOT NULL,
		min_price int(11) DEFAULT 0,
		max_price int(11) DEFAULT 0,
		available_units_count int(11) DEFAULT 0,
		floorplan_data longtext,
		updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		UNIQUE KEY property_id (property_id),
		KEY post_id (post_id),
		KEY latitude (latitude),
		KEY longitude (longitude),
		KEY min_price (min_price),
		KEY max_price (max_price)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( RENTFETCH_FILE, 'rentfetch_create_properties_search_table' );

/**
 * Populate the properties search table with data from existing properties and floorplans
 */
function rentfetch_populate_properties_search_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'rentfetch_properties_search';

	// Clear existing data
	$wpdb->query( "TRUNCATE TABLE $table_name" );

	// Get all properties
	$properties = get_posts( array(
		'post_type' => 'properties',
		'posts_per_page' => -1,
		'post_status' => 'publish',
	) );

	// Get all floorplans data
	$floorplans = rentfetch_get_floorplans_array_sql();

	foreach ( $properties as $property ) {
		$property_id = get_post_meta( $property->ID, 'property_id', true );
		$latitude = get_post_meta( $property->ID, 'latitude', true );
		$longitude = get_post_meta( $property->ID, 'longitude', true );

		if ( !$property_id || !$latitude || !$longitude ) {
			continue; // Skip if missing key data
		}

		// Compute aggregates from floorplans
		$min_price = PHP_INT_MAX;
		$max_price = 0;
		$available_units_count = 0;
		$floorplan_counts = array();

		if ( isset( $floorplans[ $property_id ] ) ) {
			$fp_data = $floorplans[ $property_id ];

			// Prices
			$min_rents = array_filter( array_map( 'intval', $fp_data['minimum_rent'] ), function($v) { return $v > 100; } );
			$max_rents = array_filter( array_map( 'intval', $fp_data['maximum_rent'] ), function($v) { return $v > 100; } );
			if ( !empty( $min_rents ) ) $min_price = min( $min_rents );
			if ( !empty( $max_rents ) ) $max_price = max( $max_rents );

			// Available units
			$available_units_count = array_sum( array_map( 'intval', $fp_data['available_units'] ) );

			// Floorplan counts by beds
			foreach ( $fp_data['beds'] as $beds ) {
				$beds = intval( $beds );
				if ( !isset( $floorplan_counts[ $beds ] ) ) {
					$floorplan_counts[ $beds ] = 0;
				}
				$floorplan_counts[ $beds ]++;
			}
		}

		if ( $min_price === PHP_INT_MAX ) $min_price = 0;

		// Insert into table
		$wpdb->insert(
			$table_name,
			array(
				'property_id' => $property_id,
				'post_id' => $property->ID,
				'title' => $property->post_title,
				'latitude' => $latitude,
				'longitude' => $longitude,
				'min_price' => $min_price,
				'max_price' => $max_price,
				'available_units_count' => $available_units_count,
				'floorplan_data' => wp_json_encode( $floorplan_counts ),
			)
		);
	}
}
register_activation_hook( RENTFETCH_FILE, 'rentfetch_populate_properties_search_table' );

/**
 * Update the properties search table when a property or floorplan is saved
 * Note: Disabled for periodic refresh to avoid performance issues with rapid syncs
 */
/*
function rentfetch_update_properties_search_table( $post_id ) {
	if ( wp_is_post_revision( $post_id ) ) return;

	$post = get_post( $post_id );
	if ( !$post ) return;

	if ( $post->post_type === 'properties' ) {
		// Update this property
		rentfetch_update_property_in_search_table( $post_id );
	} elseif ( $post->post_type === 'floorplans' ) {
		// Find the property_id and update that property
		$property_id = get_post_meta( $post_id, 'property_id', true );
		if ( $property_id ) {
			rentfetch_update_property_in_search_table_by_property_id( $property_id );
		}
	}
}
add_action( 'save_post', 'rentfetch_update_properties_search_table' );
*/

/**
 * Update a specific property in the search table
 */
function rentfetch_update_property_in_search_table( $post_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'rentfetch_properties_search';

	$property = get_post( $post_id );
	if ( !$property || $property->post_type !== 'properties' ) return;

	$property_id = get_post_meta( $post_id, 'property_id', true );
	$latitude = get_post_meta( $post_id, 'latitude', true );
	$longitude = get_post_meta( $post_id, 'longitude', true );

	if ( !$property_id || !$latitude || !$longitude ) {
		// If missing key data, delete from table
		$wpdb->delete( $table_name, array( 'post_id' => $post_id ) );
		return;
	}

	// Get floorplans for this property
	$floorplans = rentfetch_get_floorplans_array_sql( array(
		'meta_query' => array(
			array(
				'key' => 'property_id',
				'value' => $property_id,
			),
		),
	) );

	$min_price = PHP_INT_MAX;
	$max_price = 0;
	$available_units_count = 0;
	$floorplan_counts = array();

	if ( isset( $floorplans[ $property_id ] ) ) {
		$fp_data = $floorplans[ $property_id ];

		$min_rents = array_filter( array_map( 'intval', $fp_data['minimum_rent'] ), function($v) { return $v > 100; } );
		$max_rents = array_filter( array_map( 'intval', $fp_data['maximum_rent'] ), function($v) { return $v > 100; } );
		if ( !empty( $min_rents ) ) $min_price = min( $min_rents );
		if ( !empty( $max_rents ) ) $max_price = max( $max_rents );

		$available_units_count = array_sum( array_map( 'intval', $fp_data['available_units'] ) );

		foreach ( $fp_data['beds'] as $beds ) {
			$beds = intval( $beds );
			if ( !isset( $floorplan_counts[ $beds ] ) ) {
				$floorplan_counts[ $beds ] = 0;
			}
			$floorplan_counts[ $beds ]++;
		}
	}

	if ( $min_price === PHP_INT_MAX ) $min_price = 0;

	// Upsert into table
	$wpdb->replace(
		$table_name,
		array(
			'property_id' => $property_id,
			'post_id' => $post_id,
			'title' => $property->post_title,
			'latitude' => $latitude,
			'longitude' => $longitude,
			'min_price' => $min_price,
			'max_price' => $max_price,
			'available_units_count' => $available_units_count,
			'floorplan_data' => wp_json_encode( $floorplan_counts ),
		)
	);
}

/**
 * Update property in search table by property_id
 */
function rentfetch_update_property_in_search_table_by_property_id( $property_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'rentfetch_properties_search';

	// Find the post_id for this property_id
	$post_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT post_id FROM $table_name WHERE property_id = %s",
		$property_id
	) );

	if ( $post_id ) {
		rentfetch_update_property_in_search_table( $post_id );
	}
}

/**
 * Schedule a recurring event to refresh the properties search table
 */
function rentfetch_schedule_properties_search_refresh() {
	if ( ! wp_next_scheduled( 'rentfetch_refresh_properties_search_table' ) ) {
		wp_schedule_event( time(), 'hourly', 'rentfetch_refresh_properties_search_table' );
	}
}
register_activation_hook( RENTFETCH_FILE, 'rentfetch_schedule_properties_search_refresh' );

/**
 * Refresh the properties search table (runs hourly)
 */
function rentfetch_do_refresh_properties_search_table() {
	rentfetch_populate_properties_search_table();
}
add_action( 'rentfetch_refresh_properties_search_table', 'rentfetch_do_refresh_properties_search_table' );

/**
 * Unschedule the periodic refresh on deactivation
 */
function rentfetch_unschedule_properties_search_refresh() {
	$timestamp = wp_next_scheduled( 'rentfetch_refresh_properties_search_table' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'rentfetch_refresh_properties_search_table' );
	}
}
register_deactivation_hook( RENTFETCH_FILE, 'rentfetch_unschedule_properties_search_refresh' );