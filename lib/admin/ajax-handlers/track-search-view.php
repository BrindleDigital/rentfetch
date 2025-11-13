<?php
/**
 * AJAX handler for tracking search views
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle AJAX request to track a search view
 */
function rentfetch_ajax_track_search_view() {
	// Verify nonce.
	check_ajax_referer( 'rentfetch_track_view', 'nonce' );

	// Get search parameters.
	$search_type = isset( $_POST['search_type'] ) ? sanitize_text_field( $_POST['search_type'] ) : '';
	$params      = isset( $_POST['params'] ) && is_array( $_POST['params'] ) ? $_POST['params'] : array();

	// Validate search type.
	if ( ! in_array( $search_type, array( 'properties', 'floorplans' ), true ) ) {
		wp_send_json_error( array( 'message' => 'Invalid search type' ) );
	}

	// Track the search.
	if ( function_exists( 'rentfetch_track_search' ) ) {
		rentfetch_track_search( $search_type, $params );
		wp_send_json_success( array( 'tracked' => true ) );
	} else {
		wp_send_json_error( array( 'message' => 'Tracking function not available' ) );
	}
}
add_action( 'wp_ajax_rentfetch_track_search_view', 'rentfetch_ajax_track_search_view' );
add_action( 'wp_ajax_nopriv_rentfetch_track_search_view', 'rentfetch_ajax_track_search_view' );
