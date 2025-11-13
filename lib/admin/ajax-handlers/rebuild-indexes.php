<?php
/**
 * AJAX handler for rebuilding search indexes
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle AJAX request to rebuild search indexes
 */
function rentfetch_ajax_rebuild_indexes() {
	// Verify nonce.
	check_ajax_referer( 'rentfetch_rebuild_indexes', 'nonce' );

	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error(
			array(
				'message' => 'You do not have permission to perform this action.',
			)
		);
	}

	// Check if indexes are enabled.
	if ( get_option( 'rentfetch_options_enable_search_indexes', '1' ) !== '1' ) {
		wp_send_json_error(
			array(
				'message' => 'Search indexes are disabled. Please enable them in settings first.',
			)
		);
	}

	// First, remove existing indexes (clean slate).
	rentfetch_remove_indexes();

	// Then create them fresh.
	$result = rentfetch_create_indexes();

	if ( $result['success'] ) {
		wp_send_json_success(
			array(
				'message' => $result['message'],
				'details' => $result['indexes'],
			)
		);
	} else {
		wp_send_json_error(
			array(
				'message' => $result['message'],
				'details' => $result['indexes'],
			)
		);
	}
}
add_action( 'wp_ajax_rentfetch_rebuild_indexes', 'rentfetch_ajax_rebuild_indexes' );
