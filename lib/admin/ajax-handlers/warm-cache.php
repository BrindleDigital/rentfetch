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

	// Check if cache warming is enabled.
	if ( get_option( 'rentfetch_options_enable_cache_warming', '0' ) !== '1' ) {
		wp_send_json_error(
			array(
				'message' => 'Cache warming is disabled. Please enable it in settings first.',
			)
		);
	}

	// Warm the cache.
	$result = rentfetch_warm_popular_searches( 50 );

	if ( $result['warmed'] > 0 ) {
		wp_send_json_success(
			array(
				'message' => sprintf(
					'Successfully pre-fetched %d popular search(es). %s',
					$result['warmed'],
					$result['failed'] > 0 ? sprintf( '(%d failed)', $result['failed'] ) : ''
				),
				'warmed'  => $result['warmed'],
				'failed'  => $result['failed'],
				'total'   => $result['total'],
			)
		);
	} elseif ( $result['total'] === 0 ) {
		wp_send_json_success(
			array(
				'message' => 'No popular searches found to pre-fetch. Searches will be tracked as users perform them.',
			)
		);
	} else {
		wp_send_json_error(
			array(
				'message' => sprintf(
					'Failed to pre-fetch searches (%d failed).',
					$result['failed']
				),
			)
		);
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
