<?php
/**
 * Unit synchronization field definitions.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Filter the syncing fields for units.
 *
 * @param array $array_fields Existing synchronized fields.
 * @param int   $post_id      Unit post ID.
 * @return array Fields that sync with the API.
 */
function rentfetch_unit_syncing_fields( $array_fields, $post_id ) {

	$unit_source = get_post_meta( $post_id, 'unit_source', true );

	// Set a blank array, just in case the source is not set.
	if ( ! is_array( $array_fields ) ) {
		$array_fields = array();
	}

	// Add the correct fields for each of the APIs, allowing them to be filtered separately.
	if ( 'yardi' === $unit_source ) {
		$array_fields = apply_filters( 'rentfetch_filter_unit_syncing_fields_yardi', $array_fields, $post_id );
	} elseif ( 'realpage' === $unit_source ) {
		$array_fields = apply_filters( 'rentfetch_filter_unit_syncing_fields_realpage', $array_fields, $post_id );
	} elseif ( 'rentmanager' === $unit_source ) {
		$array_fields = apply_filters( 'rentfetch_filter_unit_syncing_fields_rentmanager', $array_fields, $post_id );
	} elseif ( 'entrata' === $unit_source ) {
		$array_fields = apply_filters( 'rentfetch_filter_unit_syncing_fields_entrata', $array_fields, $post_id );
	} elseif ( 'engrain' === $unit_source ) {
		$array_fields = apply_filters( 'rentfetch_filter_unit_syncing_fields_engrain', $array_fields, $post_id );
	}

	if ( ! empty( $unit_source ) ) {

		// Add the default fields to the array that would never be editable.
		$never_editable_fields = array(
			'unit_source',
			'property_name', // property name is never editable because it's determined, it's not entered. Property, not floorplan.
			'api_response',
		);

		$array_fields = array_merge( $array_fields, $never_editable_fields );
	}

	return $array_fields;
}
add_filter( 'rentfetch_filter_unit_syncing_fields', 'rentfetch_unit_syncing_fields', 10, 2 );

/**
 * Filter the syncing fields for units using the Yardi API.
 *
 * @param array $array_fields Existing synchronized fields.
 * @param int   $post_id      Unit post ID.
 * @return array Fields that sync with the API.
 */
function rentfetch_unit_syncing_fields_yardi( $array_fields, $post_id ) {
	unset( $array_fields, $post_id );
	return array(
		'title',
		'property_id',
		'floorplan_id',
		'unit_id',
		'floorplan_name',
		'beds',
		'baths',
		'apply_online_url',
		'availability_date',
		'amenities',
		'deposit',
		'minimum_rent',
		'maximum_rent',
		'sqrft',
	);
}
add_filter( 'rentfetch_filter_unit_syncing_fields_yardi', 'rentfetch_unit_syncing_fields_yardi', 10, 2 );

/**
 * Filter the syncing fields for units using the Entrata API.
 *
 * @param array $array_fields Existing synchronized fields.
 * @param int   $post_id      Unit post ID.
 * @return array Fields that sync with the API.
 */
function rentfetch_unit_syncing_fields_entrata( $array_fields, $post_id ) {
	unset( $array_fields, $post_id );
	return array(
		'title',
		'property_id',
		'floorplan_id',
		'unit_id',
		'building_name',
		'floor_number',
		'floorplan_name',
		'apply_online_url',
		'availability_date',
		'deposit',
		'minimum_rent',
		'maximum_rent',
		'sqrft',
	);
}
add_filter( 'rentfetch_filter_unit_syncing_fields_entrata', 'rentfetch_unit_syncing_fields_entrata', 10, 2 );

/**
 * Get shared unit fields synchronized by Engrain.
 *
 * @param array $array_fields Existing fields.
 * @param int   $post_id Unit post ID.
 * @return array
 */
function rentfetch_unit_syncing_fields_engrain( $array_fields, $post_id ) {
	unset( $array_fields, $post_id );
	return array(
		'title',
		'property_id',
		'floorplan_id',
		'unit_id',
		'unit_number',
		'apply_online_url',
		'availability_date',
		'amenities',
		'baths',
		'beds',
		'deposit',
		'maximum_rent',
		'maximum_total_monthly_price',
		'minimum_rent',
		'minimum_total_monthly_price',
		'sqrft',
	);
}
add_filter( 'rentfetch_filter_unit_syncing_fields_engrain', 'rentfetch_unit_syncing_fields_engrain', 10, 2 );

/**
 * Filter the syncing fields for units using the Rent Manager API.
 *
 * @param array $array_fields Existing synchronized fields.
 * @param int   $post_id      Unit post ID.
 * @return array Fields that sync with the API.
 */
function rentfetch_unit_syncing_fields_rentmanager( $array_fields, $post_id ) {
	unset( $array_fields, $post_id );
	return array(
		'title',
		'property_id',
		'floorplan_id',
		'unit_id',
		'baths',
		'beds',
		'floorplan_image_url',
		'floorplan_images',
	);
}
add_filter( 'rentfetch_filter_unit_syncing_fields_rentmanager', 'rentfetch_unit_syncing_fields_rentmanager', 10, 2 );

/**
 * Filter the syncing fields for units using the RealPage API.
 *
 * @param array $array_fields Existing synchronized fields.
 * @param int   $post_id      Unit post ID.
 * @return array Fields that sync with the API.
 */
function rentfetch_unit_syncing_fields_realpage( $array_fields, $post_id ) {
	unset( $array_fields, $post_id );
	return array(
		'title',
		'property_id',
		'floorplan_id',
		'baths',
		'beds',
		'minimum_rent',
		'maximum_rent',
		'maximum_sqft',
		'minimum_sqft',
		'available_units',
		'availability_date',
		'updated',
		'api_response',
	);
}
add_filter( 'rentfetch_filter_unit_syncing_fields_realpage', 'rentfetch_unit_syncing_fields_realpage', 10, 2 );
