<?php
/**
 * AJAX handler for search/query cache statistics.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Return search/query cache dashboard stats.
 *
 * @return void
 */
function rentfetch_ajax_get_cache_stats() {
	check_ajax_referer( 'rentfetch_cache_stats', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error(
			array(
				'message' => 'You do not have permission to view cache statistics.',
			)
		);
	}

	wp_send_json_success( rentfetch_get_search_query_cache_dashboard_stats() );
}
add_action( 'wp_ajax_rentfetch_get_cache_stats', 'rentfetch_ajax_get_cache_stats' );
