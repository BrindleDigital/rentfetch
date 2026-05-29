<?php
/**
 * AJAX handler for warming search cache
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle AJAX request to warm cache with popular searches
 */
function rentfetch_ajax_warm_cache() {
	// Verify nonce.
	check_ajax_referer( 'rentfetch_warm_cache', 'nonce' );

	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error(
			array(
				'message' => 'You do not have permission to perform this action.',
			)
		);
	}

	// Warm the cache.
	$result = rentfetch_warm_popular_searches_batch( 50, 5, ! empty( $_POST['reset_cursor'] ) );

	if ( ! empty( $result['locked'] ) ) {
		$data = array(
			'message' => 'Preloading is already running. Try again in a few minutes.',
		);

		if ( function_exists( 'rentfetch_get_admin_bar_cache_states' ) ) {
			$data['states'] = rentfetch_get_admin_bar_cache_states();
		}

		wp_send_json_error( $data );
	}

	if ( $result['total'] > 0 ) {
		$message = sprintf(
			'Preloaded %d popular search(es).',
			$result['warmed']
		);
		if ( $result['failed'] > 0 ) {
			$message .= sprintf( ' %d failed.', $result['failed'] );
		}

		$data = array(
			'message'    => $message,
			'warmed'     => $result['warmed'],
			'failed'     => $result['failed'],
			'total'      => $result['total'],
			'batch_size' => isset( $result['batch_size'] ) ? (int) $result['batch_size'] : 0,
			'cursor'     => isset( $result['cursor'] ) ? (int) $result['cursor'] : 0,
			'next_cursor' => isset( $result['next_cursor'] ) ? (int) $result['next_cursor'] : 0,
			'errors'     => isset( $result['errors'] ) && is_array( $result['errors'] ) ? $result['errors'] : array(),
		);

		if ( function_exists( 'rentfetch_get_admin_bar_cache_states' ) ) {
			$data['states'] = rentfetch_get_admin_bar_cache_states();
		}

		wp_send_json_success(
			$data
		);
	} else {
		$data = array(
			'message'    => 'No popular searches found to preload. Searches will be tracked as users perform them.',
			'warmed'     => 0,
			'failed'     => 0,
			'total'      => 0,
			'batch_size' => 0,
		);

		if ( function_exists( 'rentfetch_get_admin_bar_cache_states' ) ) {
			$data['states'] = rentfetch_get_admin_bar_cache_states();
		}

		wp_send_json_success( $data );
	}
}
add_action( 'wp_ajax_rentfetch_warm_cache', 'rentfetch_ajax_warm_cache' );

/**
 * Handle AJAX request to get popular searches list
 */
function rentfetch_ajax_get_popular_searches() {
	// Verify nonce.
	check_ajax_referer( 'rentfetch_popular_searches', 'nonce' );

	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error(
			array(
				'message' => 'You do not have permission to perform this action.',
			)
		);
	}

	$popular = rentfetch_get_popular_searches( 50 );

	// Calculate total searches for percentage calculation.
	$total_searches = 0;
	foreach ( $popular as $search_data ) {
		$total_searches += $search_data['count'];
	}

	$formatted = array();
	foreach ( $popular as $search_key => $search_data ) {
		$sanitized_params = rentfetch_sanitize_search_params( $search_data['params'] );
		$query_string     = http_build_query( $sanitized_params );
		$display_params   = $sanitized_params;

		if ( empty( $display_params ) || ( 1 === count( $display_params ) && isset( $display_params['availability'] ) && '1' === (string) $display_params['availability'] ) ) {
			$display_query = '(all available)';
		} else {
			unset( $display_params['availability'] );
			$display_query = urldecode( http_build_query( $display_params ) );
			if ( '' === $display_query ) {
				$display_query = '(all available)';
			}
		}

		$percentage   = $total_searches > 0 ? round( ( $search_data['count'] / $total_searches ) * 100, 1 ) : 0;
		$formatted[]  = array(
			'type'       => esc_html( sanitize_text_field( $search_data['type'] ) ),
			'query'      => esc_html( $query_string ),
			'display_query' => esc_html( $display_query ),
			'count'      => esc_html( (string) absint( $search_data['count'] ) ),
			'percentage' => esc_html( (string) $percentage ),
			'last_used'  => esc_html( human_time_diff( absint( $search_data['last_used'] ) ) . ' ago' ),
		);
	}

	wp_send_json_success(
		array(
			'searches' => $formatted,
			'total'    => count( $formatted ),
		)
	);
}
add_action( 'wp_ajax_rentfetch_get_popular_searches', 'rentfetch_ajax_get_popular_searches' );
