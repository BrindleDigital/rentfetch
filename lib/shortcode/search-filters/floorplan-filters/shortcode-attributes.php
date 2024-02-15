<?php
/**
 * Shortcode attribute filters
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Detect shortcode attributes and apply them to the floorplans query
 *
 * @param   array $floorplans_args the floorplan args to be filtered.
 *
 * @return  array $floorplans_args the filtered floorplan args.
 */
function rentfetch_search_floorplans_args_shortcode( $floorplans_args ) {

	// ! Property IDs
	if ( isset( $_POST['property_id'] ) ) {

		$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';

		// * Verify the nonce
		if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_frontend_nonce_action' ) ) {
			die( 'Nonce verification failed (shortcode attributes)' );
		}

		// Get the values.
		$property_ids = sanitize_text_field( wp_unslash( $_POST['property_id'] ) );

		// Escape the values.
		$property_ids_array = explode( ',', $property_ids );

		// Convert the beds query to a meta query.
		$meta_query = array(
			array(
				'key'   => 'property_id',
				'value' => $property_ids_array,
			),
		);

		// Add the meta query to the property args.
		$floorplans_args['meta_query'][] = $meta_query;

	}

	// ! Posts per page
	if ( isset( $_POST['posts_per_page'] ) ) {

		// Get the values.
		$posts_per_page = intval( $_POST['posts_per_page'] );

		// Add the meta query to the property args.
		$floorplans_args['posts_per_page'] = $posts_per_page;

	}

	return $floorplans_args;
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_shortcode', 10, 1 );
