<?php
/**
 * Search analytics and cache warming
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Track search queries for analytics
 *
 * @param string $search_type Type of search (properties or floorplans).
 * @param array  $params      Search parameters.
 * @param bool   $skip_tracking Whether to skip tracking (used for cache warming).
 */
function rentfetch_track_search( $search_type, $params, $skip_tracking = false ) {
	// Skip if tracking is disabled or explicitly skipped.
	if ( get_option( 'rentfetch_options_enable_search_tracking', '1' ) !== '1' || $skip_tracking ) {
		return;
	}

	// Clean up params - remove empty values and internal params.
	$clean_params = array_filter(
		$params,
		function( $value, $key ) {
			return ! empty( $value ) && ! in_array( $key, array( 'action', 'nonce', '_wpnonce' ), true );
		},
		ARRAY_FILTER_USE_BOTH
	);

	// Sort params for consistent cache keys.
	ksort( $clean_params );

	$query_string = http_build_query( $clean_params );
	$search_key   = $search_type . '|' . $query_string;

	// Get current tracking data - using 'rf_analytics' prefix to avoid cache clearing operations.
	$cache_key = 'rf_analytics_searches';
	$searches  = get_transient( $cache_key );

	if ( false === $searches || ! is_array( $searches ) ) {
		$searches = array();
	}

	// Increment count for this search.
	if ( ! isset( $searches[ $search_key ] ) ) {
		$searches[ $search_key ] = array(
			'count'      => 0,
			'last_used'  => time(),
			'type'       => $search_type,
			'params'     => $clean_params,
		);
	}

	$searches[ $search_key ]['count']++;
	$searches[ $search_key ]['last_used'] = time();

	// Store for 30 days - using 'rf_analytics' prefix to avoid cache clearing operations.
	set_transient( 'rf_analytics_searches', $searches, 30 * DAY_IN_SECONDS );
}

/**
 * Get popular searches
 *
 * @param int $limit Number of searches to return.
 * @return array Array of popular searches.
 */
function rentfetch_get_popular_searches( $limit = 50 ) {
	$cache_key = 'rf_analytics_searches';
	$searches  = get_transient( $cache_key );

	if ( false === $searches || ! is_array( $searches ) ) {
		return array();
	}

	// Sort by count (descending).
	uasort(
		$searches,
		function( $a, $b ) {
			return $b['count'] - $a['count'];
		}
	);

	return array_slice( $searches, 0, $limit, true );
}

/**
 * Warm cache for popular searches
 *
 * @param int $limit Number of searches to warm.
 * @return array Results with count and status.
 */
function rentfetch_warm_popular_searches( $limit = 50 ) {
	$popular = rentfetch_get_popular_searches( $limit );
	$warmed  = 0;
	$failed  = 0;

	foreach ( $popular as $search_key => $search_data ) {
		try {
			$search_type = $search_data['type'];
			$params      = $search_data['params'];

			// Create REST request with skip_tracking flag.
			$request = new WP_REST_Request( 'GET', "/rentfetch/v1/search/{$search_type}" );
			foreach ( $params as $key => $value ) {
				$request->set_param( $key, $value );
			}
			$request->set_param( 'skip_tracking', true );

			// Execute the search to warm the cache.
			if ( 'properties' === $search_type ) {
				$response = rentfetch_rest_search_properties( $request );
			} else {
				$response = rentfetch_rest_search_floorplans( $request );
			}

			if ( ! is_wp_error( $response ) ) {
				++$warmed;
			} else {
				++$failed;
			}
		} catch ( Exception $e ) {
			++$failed;
		}
	}

	return array(
		'warmed' => $warmed,
		'failed' => $failed,
		'total'  => count( $popular ),
	);
}

/**
 * Schedule cache warming via WP-Cron
 */
function rentfetch_schedule_cache_warming() {
	// Only schedule if feature is enabled.
	if ( get_option( 'rentfetch_options_enable_cache_warming', '0' ) !== '1' ) {
		// Unschedule if it exists but is disabled.
		$timestamp = wp_next_scheduled( 'rentfetch_warm_cache_cron' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'rentfetch_warm_cache_cron' );
		}
		return;
	}

	// Schedule if not already scheduled.
	if ( ! wp_next_scheduled( 'rentfetch_warm_cache_cron' ) ) {
		wp_schedule_event( time(), 'rentfetch_25min', 'rentfetch_warm_cache_cron' );
	}
}
add_action( 'init', 'rentfetch_schedule_cache_warming' );

/**
 * Add custom cron interval
 *
 * @param array $schedules Existing schedules.
 * @return array Modified schedules.
 */
function rentfetch_add_cron_interval( $schedules ) {
	$schedules['rentfetch_25min'] = array(
		'interval' => 1500, // 25 minutes.
		'display'  => esc_html__( 'Every 25 Minutes', 'rentfetch' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'rentfetch_add_cron_interval' );

/**
 * Cron handler to warm cache
 */
function rentfetch_warm_cache_cron_handler() {
	rentfetch_warm_popular_searches( 50 );
}
add_action( 'rentfetch_warm_cache_cron', 'rentfetch_warm_cache_cron_handler' );
