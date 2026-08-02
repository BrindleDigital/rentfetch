<?php
/**
 * Property synchronization field definitions.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Filter the syncing fields for properties.
 *
 * @param array $array_fields Existing synchronized fields.
 * @param int   $post_id      Property post ID.
 * @return array Fields that sync with the API.
 */
function rentfetch_property_syncing_fields( $array_fields, $post_id ) {

	$property_source = get_post_meta( $post_id, 'property_source', true );

	// Set a blank array, just in case the source is not set.
	if ( ! is_array( $array_fields ) ) {
		$array_fields = array();
	}

	// Add the correct fields for each of the APIs, allowing them to be filtered separately.
	if ( 'yardi' === $property_source ) {
		$array_fields = apply_filters( 'rentfetch_filter_property_syncing_fields_yardi', $array_fields, $post_id );
	} elseif ( 'realpage' === $property_source ) {
		$array_fields = apply_filters( 'rentfetch_filter_property_syncing_fields_realpage', $array_fields, $post_id );
	} elseif ( 'rentmanager' === $property_source ) {
		$array_fields = apply_filters( 'rentfetch_filter_property_syncing_fields_rentmanager', $array_fields, $post_id );
	} elseif ( 'entrata' === $property_source ) {
		$array_fields = apply_filters( 'rentfetch_filter_property_syncing_fields_entrata', $array_fields, $post_id );
	} elseif ( 'engrain' === $property_source ) {
		$array_fields = apply_filters( 'rentfetch_filter_property_syncing_fields_engrain', $array_fields, $post_id );
	}

	if ( ! empty( $property_source ) ) {

		// Add the default fields to the array that would never be editable.
		$never_editable_fields = array(
			'property_source',
			'property_name', // property name is never editable because it's determined, it's not entered. Property, not property.
			'api_response',
		);

		$array_fields = array_merge( $array_fields, $never_editable_fields );
	}

	return $array_fields;
}
add_filter( 'rentfetch_filter_property_syncing_fields', 'rentfetch_property_syncing_fields', 10, 2 );

/**
 * Filter the syncing fields for properties using the Yardi API.
 *
 * @param array $array_fields Existing synchronized fields.
 * @param int   $post_id      Property post ID.
 * @return array Fields that sync with the API.
 */
function rentfetch_property_syncing_fields_yardi( $array_fields, $post_id ) {
	unset( $array_fields, $post_id );
	return array(
		'title',
		'property_id',
		'address',
		'city',
		'state',
		'zipcode',
		'url',
		'description',
		'email',
		'phone',
		'latitude',
		'longitude',
		'synced_property_images',
		'office_hours',
	);
}
add_filter( 'rentfetch_filter_property_syncing_fields_yardi', 'rentfetch_property_syncing_fields_yardi', 10, 2 );

/**
 * Filter the syncing fields for properties using the Entrata API.
 *
 * @param array $array_fields Existing synchronized fields.
 * @param int   $post_id      Property post ID.
 * @return array Fields that sync with the API.
 */
function rentfetch_property_syncing_fields_entrata( $array_fields, $post_id ) {
	unset( $array_fields, $post_id );
	return array(
		'title',
		'property_id',
		'address',
		'city',
		'state',
		'zipcode',
		'url',
		'description',
		'email',
		'latitude',
		'longitude',
		'synced_property_images',
		'phone',
	);
}
add_filter( 'rentfetch_filter_property_syncing_fields_entrata', 'rentfetch_property_syncing_fields_entrata', 10, 2 );

/**
 * Get shared property fields synchronized by Engrain.
 *
 * @param array $array_fields Existing fields.
 * @param int   $post_id Property post ID.
 * @return array
 */
function rentfetch_property_syncing_fields_engrain( $array_fields, $post_id ) {
	unset( $array_fields, $post_id );
	return array(
		'title',
		'property_id',
		'address',
		'address_2',
		'city',
		'state',
		'country',
		'zipcode',
		'description',
		'latitude',
		'longitude',
		'synced_property_images',
	);
}
add_filter( 'rentfetch_filter_property_syncing_fields_engrain', 'rentfetch_property_syncing_fields_engrain', 10, 2 );

/**
 * Filter the syncing fields for properties using the Rent Manager API.
 *
 * @param array $array_fields Existing synchronized fields.
 * @param int   $post_id      Property post ID.
 * @return array Fields that sync with the API.
 */
function rentfetch_property_syncing_fields_rentmanager( $array_fields, $post_id ) {
	unset( $array_fields, $post_id );
	return array(
		'title',
		'property_id',
		'address',
		'city',
		'state',
		'zipcode',
		'email',
		'phone',
		'synced_property_images',
	);
}
add_filter( 'rentfetch_filter_property_syncing_fields_rentmanager', 'rentfetch_property_syncing_fields_rentmanager', 10, 2 );

/**
 * Filter the syncing fields for properties using the RealPage API.
 *
 * @param array $array_fields Existing synchronized fields.
 * @param int   $post_id      Property post ID.
 * @return array Fields that sync with the API.
 */
function rentfetch_property_syncing_fields_realpage( $array_fields, $post_id ) {
	unset( $array_fields, $post_id );
	return array(
		'property_id',
	);
}
add_filter( 'rentfetch_filter_property_syncing_fields_realpage', 'rentfetch_property_syncing_fields_realpage', 10, 2 );
