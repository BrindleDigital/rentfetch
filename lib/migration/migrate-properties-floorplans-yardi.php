<?php
/**
 * Migrate the floorplan tours
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Place this code in your plugin file.
register_activation_hook( RENTFETCH_FILE, 'rentfetch_update_properties_to_new_ids' );

/**
 * Move old property ids to the new system.
 *
 * @return void.
 */
function rentfetch_update_properties_to_new_ids() {

	// query all posts of type 'properties' that have a 'property_code' meta.
	$args = array(
		'post_type'      => 'properties',
		'posts_per_page' => -1, // get all posts.
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'property_code',
				'value'   => '', // this will match posts with any value for 'property_code'.
				'compare' => '!=',
			),
		),
	);

	$properties = get_posts( $args );

	foreach ( $properties as $property ) {

		// get the property_code, bail if we don't have one.
		$property_code = get_post_meta( $property->ID, 'property_code', true );

		if ( ! $property_code ) {
			continue;
		}

		rentfetch_migrate_properties_floorplans_to_new_ids( $property_code );

		// update the property_id to be the property_code instead.
		$success = update_post_meta( $property->ID, 'property_id', $property_code );

		// delete the old property_code meta.
		delete_post_meta( $property->ID, 'property_code' );
	}
}

/**
 * Update the floorplan property ids to the new system.
 *
 * @param   string $property_code The property code.
 *
 * @return  void.
 */
function rentfetch_migrate_properties_floorplans_to_new_ids( $property_code ) {

	// query all posts of type 'floorplans' that have this property_code.
	$args = array(
		'post_type'      => 'floorplans',
		'posts_per_page' => -1, // get all posts.
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'   => 'property_id',
				'value' => $property_code,
			),
		),
	);

	$floorplans = get_posts( $args );

	foreach ( $floorplans as $floorplan ) {

		// update the property_id to be the property_code instead.
		$success = update_post_meta( $floorplan->ID, 'property_id', $property_code );
	}
}
