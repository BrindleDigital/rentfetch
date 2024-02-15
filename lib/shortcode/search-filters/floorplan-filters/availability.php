<?php
/**
 * Availability filter
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the form markup for the availability (this is a hidden field)
 *
 * @return null.
 */
function rentfetch_search_filters_availability() {

	$display_availability = get_option( 'rentfetch_options_property_availability_display' );

	// bail if the option isn't set to 'availability'.
	if ( 'available' !== $display_availability ) {
		return;
	}

	echo '<input type="hidden" name="availability" value="1" />';
}
add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_availability' );

/**
 * Force the availability search to only show properties with available floorplans
 *
 * @param array $floorplans_args the floorplan args to be filtered.
 *
 * @return array $floorplans_args
 */
function rentfetch_search_floorplans_args_availability( $floorplans_args ) {

	// bail if we don't have a price search (neither are set).
	if ( ! isset( $_POST['availability'] ) ) {
		return $floorplans_args;
	}

	$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';
	
	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_frontend_nonce_action' ) ) {
		die( 'Nonce verification failed (availability)' );
	}

	$floorplans_args['meta_query'][] = array(
		array(
			'key'     => 'available_units',
			'value'   => 1,
			'type'    => 'numeric',
			'compare' => '>=',
		),
	);

	return $floorplans_args;
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_availability', 10, 1 );
