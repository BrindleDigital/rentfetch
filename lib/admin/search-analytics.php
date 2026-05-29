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
 * Sanitize search parameters for analytics storage/output.
 *
 * @param array $params Search parameters.
 * @return array Sanitized parameters.
 */
function rentfetch_sanitize_search_params( $params ) {
	$sanitized = array();

	foreach ( $params as $key => $value ) {
		if ( is_array( $value ) ) {
			$sanitized[ $key ] = rentfetch_sanitize_search_params( $value );
			continue;
		}

		if ( is_object( $value ) ) {
			continue;
		}

		$sanitized[ $key ] = sanitize_text_field( wp_unslash( (string) $value ) );
	}

	return $sanitized;
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

	$clean_params = rentfetch_sanitize_search_params( $clean_params );
	$clean_params = array_filter( $clean_params );

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
 * Acquire a best-effort cache warming lock.
 *
 * Uses an option instead of a transient so the unique option_name constraint
 * prevents concurrent warmers from acquiring the same lock.
 *
 * @param int $ttl Lock lifetime in seconds.
 * @return bool True when the lock was acquired.
 */
function rentfetch_acquire_cache_warming_lock( $ttl = 600 ) {
	$lock_key = 'rentfetch_cache_warming_lock';
	$now      = time();
	$ttl      = max( MINUTE_IN_SECONDS, (int) $ttl );
	$current  = (int) get_option( $lock_key, 0 );

	if ( $current > 0 && ( $now - $current ) > $ttl ) {
		delete_option( $lock_key );
	}

	return add_option( $lock_key, (string) $now, '', false );
}

/**
 * Release the cache warming lock.
 *
 * @return void
 */
function rentfetch_release_cache_warming_lock() {
	delete_option( 'rentfetch_cache_warming_lock' );
}

/**
 * Determine whether preload failures should be logged.
 *
 * @return bool
 */
function rentfetch_should_log_cache_preload_failures() {
	$manual_admin_preload = wp_doing_ajax()
		&& isset( $_POST['action'] )
		&& 'rentfetch_warm_cache' === sanitize_key( wp_unslash( $_POST['action'] ) )
		&& current_user_can( 'manage_options' );

	$should_log = $manual_admin_preload || ( defined( 'WP_DEBUG' ) && WP_DEBUG );

	return (bool) apply_filters( 'rentfetch_log_cache_preload_failures', $should_log );
}

/**
 * Build failure details for a preload attempt.
 *
 * @param array  $search_data Search data.
 * @param string $message Failure message.
 * @param int    $index Search index in the preload pool.
 * @return array
 */
function rentfetch_get_cache_preload_failure_detail( $search_data, $message, $index = null ) {
	$params = isset( $search_data['params'] ) && is_array( $search_data['params'] ) ? $search_data['params'] : array();
	if ( function_exists( 'rentfetch_sanitize_search_params' ) ) {
		$params = rentfetch_sanitize_search_params( $params );
	}

	return array(
		'index'   => null === $index ? null : (int) $index,
		'type'    => isset( $search_data['type'] ) ? sanitize_key( $search_data['type'] ) : '',
		'query'   => http_build_query( $params ),
		'params'  => $params,
		'message' => sanitize_text_field( (string) $message ),
	);
}

/**
 * Log a preload failure when diagnostic logging is enabled.
 *
 * @param array $detail Failure detail.
 * @return void
 */
function rentfetch_log_cache_preload_failure( $detail ) {
	if ( ! rentfetch_should_log_cache_preload_failures() ) {
		return;
	}

	error_log(
		sprintf(
			'[RentFetch preload] Failed to preload %1$s search at pool index %2$s: %3$s | query=%4$s | params=%5$s',
			isset( $detail['type'] ) && '' !== $detail['type'] ? $detail['type'] : 'unknown',
			isset( $detail['index'] ) && null !== $detail['index'] ? (string) $detail['index'] : 'n/a',
			isset( $detail['message'] ) ? $detail['message'] : 'Unknown failure',
			isset( $detail['query'] ) ? $detail['query'] : '',
			wp_json_encode( isset( $detail['params'] ) ? $detail['params'] : array() )
		)
	);
}

/**
 * Warm one tracked search entry.
 *
 * @param array $search_data Tracked search data.
 * @param string|null $error_message Failure message.
 * @return bool True when a response was generated successfully.
 */
function rentfetch_warm_popular_search_entry( $search_data, &$error_message = null ) {
	$error_message = null;

	if ( empty( $search_data['type'] ) || ! isset( $search_data['params'] ) || ! is_array( $search_data['params'] ) ) {
		$error_message = 'Tracked search is missing a valid type or params array.';
		return false;
	}

	$search_type = sanitize_key( $search_data['type'] );
	if ( ! in_array( $search_type, array( 'properties', 'floorplans' ), true ) ) {
		$error_message = sprintf( 'Unsupported search type: %s.', $search_type );
		return false;
	}

	$request = new WP_REST_Request( 'GET', "/rentfetch/v1/search/{$search_type}" );
	foreach ( $search_data['params'] as $key => $value ) {
		$request->set_param( $key, $value );
	}
	$request->set_param( 'skip_tracking', true );

	$GLOBALS['rentfetch_prioritize_search_query_cache'] = true;
	$GLOBALS['rentfetch_force_cache_write']             = true;
	$previous_post                                      = $_POST;
	try {
		if ( 'properties' === $search_type ) {
			$response = rentfetch_rest_search_properties( $request );
		} else {
			$response = rentfetch_rest_search_floorplans( $request );
		}
	} finally {
		$GLOBALS['rentfetch_prioritize_search_query_cache'] = false;
		$GLOBALS['rentfetch_force_cache_write']             = false;
		$_POST                                             = $previous_post;
	}

	if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();
		return false;
	}

	return true;
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
	$errors  = array();

	foreach ( array_values( $popular ) as $index => $search_data ) {
		try {
			$error_message = null;
			if ( rentfetch_warm_popular_search_entry( $search_data, $error_message ) ) {
				++$warmed;
			} else {
				++$failed;
				$detail = rentfetch_get_cache_preload_failure_detail( $search_data, $error_message ? $error_message : 'Search response did not preload successfully.', $index );
				rentfetch_log_cache_preload_failure( $detail );
				if ( count( $errors ) < 10 ) {
					$errors[] = $detail;
				}
			}
		} catch ( Throwable $e ) {
			++$failed;
			$detail = rentfetch_get_cache_preload_failure_detail( $search_data, $e->getMessage(), $index );
			rentfetch_log_cache_preload_failure( $detail );
			if ( count( $errors ) < 10 ) {
				$errors[] = $detail;
			}
		}
	}

	return array(
		'warmed' => $warmed,
		'failed' => $failed,
		'total'  => count( $popular ),
		'errors' => $errors,
	);
}

/**
 * Warm the popular search cache in cursor-based batches.
 *
 * The pool limit controls how many tracked searches should eventually be kept
 * warm. The batch size controls how much work a single request is allowed to do.
 *
 * @param int $pool_limit Number of popular searches in the warming pool.
 * @param int $batch_size Number of searches to warm in this request.
 * @param bool $reset_cursor Whether to start from the beginning of the warming pool.
 * @return array Results with count and status.
 */
function rentfetch_warm_popular_searches_batch( $pool_limit = 50, $batch_size = 5, $reset_cursor = false ) {
	$pool_limit = max( 1, (int) apply_filters( 'rentfetch_cache_warming_pool_limit', $pool_limit ) );
	$batch_size = max( 1, (int) apply_filters( 'rentfetch_cache_warming_batch_size', $batch_size, $pool_limit ) );

	if ( ! rentfetch_acquire_cache_warming_lock() ) {
		return array(
			'warmed' => 0,
			'failed' => 0,
			'total'  => 0,
			'locked' => true,
		);
	}

	try {
		$popular = array_values( rentfetch_get_popular_searches( $pool_limit ) );
		$total   = count( $popular );
		$warmed  = 0;
		$failed  = 0;
		$errors  = array();

		if ( 0 === $total ) {
			update_option( 'rentfetch_cache_warming_cursor', 0, false );
			return array(
				'warmed' => 0,
				'failed' => 0,
				'total'  => 0,
				'locked' => false,
				'errors' => array(),
			);
		}

		$cursor = $reset_cursor ? 0 : (int) get_option( 'rentfetch_cache_warming_cursor', 0 );
		if ( $cursor < 0 || $cursor >= $total ) {
			$cursor = 0;
		}

		$iterations = min( $batch_size, $total );
		for ( $i = 0; $i < $iterations; ++$i ) {
			$index = ( $cursor + $i ) % $total;

			try {
				$error_message = null;
				if ( rentfetch_warm_popular_search_entry( $popular[ $index ], $error_message ) ) {
					++$warmed;
				} else {
					++$failed;
					$detail = rentfetch_get_cache_preload_failure_detail( $popular[ $index ], $error_message ? $error_message : 'Search response did not preload successfully.', $index );
					rentfetch_log_cache_preload_failure( $detail );
					if ( count( $errors ) < 10 ) {
						$errors[] = $detail;
					}
				}
			} catch ( Throwable $e ) {
				++$failed;
				$detail = rentfetch_get_cache_preload_failure_detail( $popular[ $index ], $e->getMessage(), $index );
				rentfetch_log_cache_preload_failure( $detail );
				if ( count( $errors ) < 10 ) {
					$errors[] = $detail;
				}
			}
		}

		$next_cursor = ( $cursor + $iterations ) % $total;
		update_option( 'rentfetch_cache_warming_cursor', $next_cursor, false );

		return array(
			'warmed'      => $warmed,
			'failed'      => $failed,
			'total'       => $total,
			'locked'      => false,
			'cursor'      => $cursor,
			'next_cursor' => $next_cursor,
			'batch_size'  => $iterations,
			'errors'      => $errors,
		);
	} finally {
		rentfetch_release_cache_warming_lock();
	}
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
	rentfetch_warm_popular_searches_batch( 50, 5 );
}
add_action( 'rentfetch_warm_cache_cron', 'rentfetch_warm_cache_cron_handler' );
