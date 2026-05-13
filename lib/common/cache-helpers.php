<?php
/**
 * Helpers for Rent Fetch transient caching.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'RENTFETCH_CACHE_TTL' ) ) {
	define( 'RENTFETCH_CACHE_TTL', DAY_IN_SECONDS );
}

if ( ! defined( 'RENTFETCH_CACHE_STALE_AFTER' ) ) {
	define( 'RENTFETCH_CACHE_STALE_AFTER', HOUR_IN_SECONDS );
}

if ( ! defined( 'RENTFETCH_SEARCH_QUERY_CACHE_LIMIT' ) ) {
	define( 'RENTFETCH_SEARCH_QUERY_CACHE_LIMIT', 500 );
}

/**
 * Check whether a transient key belongs to the bounded search/query cache set.
 *
 * @param string $key Transient key.
 * @return bool
 */
function rentfetch_is_search_query_cache_key( $key ) {
	$prefixes = array(
		'rentfetch_propertysearch_markup_',
		'rentfetch_floorplansearch_markup_',
		'rentfetch_floorplans_array_sql_',
		'rentfetch_meta_values_',
		'rentfetch_property_ids_available_',
		'rentfetch_date_availability_',
		'rentfetch_date_search_',
	);

	foreach ( $prefixes as $prefix ) {
		if ( 0 === strpos( $key, $prefix ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get the cache family for a search/query cache key.
 *
 * @param string $key Transient key.
 * @return string
 */
function rentfetch_get_search_query_cache_family( $key ) {
	if ( 0 === strpos( $key, 'rentfetch_propertysearch_markup_' ) || 0 === strpos( $key, 'rentfetch_floorplansearch_markup_' ) ) {
		return 'html';
	}

	if ( rentfetch_is_search_query_cache_key( $key ) ) {
		return 'query';
	}

	return 'other';
}

/**
 * Get a readable description for a search/query cache key.
 *
 * @param string $key Transient key.
 * @return string
 */
function rentfetch_describe_search_query_cache_key( $key ) {
	if ( 0 === strpos( $key, 'rentfetch_propertysearch_markup_' ) ) {
		return 'property search HTML markup';
	}

	if ( 0 === strpos( $key, 'rentfetch_floorplansearch_markup_' ) ) {
		return 'floorplan search HTML markup';
	}

	if ( 0 === strpos( $key, 'rentfetch_floorplans_array_sql_' ) ) {
		return 'floorplan aggregate SQL query results';
	}

	if ( 0 === strpos( $key, 'rentfetch_meta_values_' ) ) {
		return 'filter meta value query results';
	}

	if ( 0 === strpos( $key, 'rentfetch_property_ids_available_' ) ) {
		return 'property IDs with available floorplans query results';
	}

	if ( 0 === strpos( $key, 'rentfetch_date_availability_' ) ) {
		return 'date option availability query results';
	}

	if ( 0 === strpos( $key, 'rentfetch_date_search_' ) ) {
		return 'date-filtered floorplan ID query results';
	}

	return 'Rent Fetch transient';
}

/**
 * Record a search/query transient cache lookup.
 *
 * @param string $key    Transient key.
 * @param bool   $is_hit Whether the lookup hit cache.
 * @return void
 */
function rentfetch_record_search_query_cache_event( $key, $is_hit ) {
	if ( ! rentfetch_is_search_query_cache_key( $key ) ) {
		return;
	}

	if ( ! isset( $GLOBALS['rentfetch_cache_debug_events'] ) || ! is_array( $GLOBALS['rentfetch_cache_debug_events'] ) ) {
		$GLOBALS['rentfetch_cache_debug_events'] = array();
	}

	$GLOBALS['rentfetch_cache_debug_events'][] = array(
		'key'         => $key,
		'description' => rentfetch_describe_search_query_cache_key( $key ),
		'family'      => rentfetch_get_search_query_cache_family( $key ),
		'status'      => $is_hit ? 'hit' : 'miss',
	);

	$stats = get_option( 'rentfetch_search_query_cache_stats', array() );
	if ( ! is_array( $stats ) ) {
		$stats = array();
	}

	$day    = gmdate( 'Y-m-d' );
	$family = rentfetch_get_search_query_cache_family( $key );

	if ( ! isset( $stats[ $day ] ) || ! is_array( $stats[ $day ] ) ) {
		$stats[ $day ] = array();
	}

	if ( ! isset( $stats[ $day ][ $family ] ) || ! is_array( $stats[ $day ][ $family ] ) ) {
		$stats[ $day ][ $family ] = array(
			'hits'   => 0,
			'misses' => 0,
		);
	}

	if ( $is_hit ) {
		++$stats[ $day ][ $family ]['hits'];
	} else {
		++$stats[ $day ][ $family ]['misses'];
	}

	if ( count( $stats ) > 30 ) {
		ksort( $stats );
		$stats = array_slice( $stats, -30, null, true );
	}

	update_option( 'rentfetch_search_query_cache_stats', $stats, false );
}

/**
 * Track a search/query cache key for pruning.
 *
 * @param string $key Transient key.
 * @return void
 */
function rentfetch_register_search_query_cache_key( $key ) {
	if ( ! rentfetch_is_search_query_cache_key( $key ) ) {
		return;
	}

	$registry = get_option( 'rentfetch_search_query_cache_registry', array() );
	if ( ! is_array( $registry ) ) {
		$registry = array();
	}

	$now = time();
	if ( ! isset( $registry[ $key ] ) || ! is_array( $registry[ $key ] ) ) {
		$registry[ $key ] = array(
			'created_at' => $now,
		);
	}

	$registry[ $key ]['last_set_at'] = $now;
	$registry[ $key ]['priority']    = ! empty( $registry[ $key ]['priority'] ) || ! empty( $GLOBALS['rentfetch_prioritize_search_query_cache'] );

	update_option( 'rentfetch_search_query_cache_registry', $registry, false );
	rentfetch_prune_search_query_cache_registry( $registry );
}

/**
 * Keep the search/query cache registry at or below the configured limit.
 *
 * @param array|null $registry Existing registry.
 * @return void
 */
function rentfetch_prune_search_query_cache_registry( $registry = null ) {
	if ( null === $registry ) {
		$registry = get_option( 'rentfetch_search_query_cache_registry', array() );
	}

	if ( ! is_array( $registry ) ) {
		return;
	}

	$limit = (int) apply_filters( 'rentfetch_search_query_cache_limit', RENTFETCH_SEARCH_QUERY_CACHE_LIMIT );
	if ( $limit < 1 || count( $registry ) <= $limit ) {
		return;
	}

	uasort(
		$registry,
		function( $a, $b ) {
			$a_priority = ! empty( $a['priority'] ) ? 1 : 0;
			$b_priority = ! empty( $b['priority'] ) ? 1 : 0;

			if ( $a_priority !== $b_priority ) {
				return $a_priority - $b_priority;
			}

			return (int) ( $a['last_set_at'] ?? 0 ) - (int) ( $b['last_set_at'] ?? 0 );
		}
	);

	$delete_count = count( $registry ) - $limit;
	$deleted      = 0;

	foreach ( array_keys( $registry ) as $key ) {
		delete_transient( $key );
		unset( $registry[ $key ] );
		++$deleted;

		if ( $deleted >= $delete_count ) {
			break;
		}
	}

	update_option( 'rentfetch_search_query_cache_registry', $registry, false );
}

/**
 * Format seconds as a compact age label.
 *
 * @param int $seconds Age in seconds.
 * @return string
 */
function rentfetch_format_cache_age( $seconds ) {
	$seconds = max( 0, (int) $seconds );

	if ( $seconds < HOUR_IN_SECONDS ) {
		return floor( $seconds / MINUTE_IN_SECONDS ) . 'm';
	}

	if ( $seconds < DAY_IN_SECONDS ) {
		return floor( $seconds / HOUR_IN_SECONDS ) . 'h';
	}

	return floor( $seconds / DAY_IN_SECONDS ) . 'd';
}

/**
 * Get dashboard statistics for search/query transients.
 *
 * @return array
 */
function rentfetch_get_search_query_cache_dashboard_stats() {
	global $wpdb;

	$registry = get_option( 'rentfetch_search_query_cache_registry', array() );
	if ( ! is_array( $registry ) ) {
		$registry = array();
	}

	if ( isset( $wpdb ) ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$transients = $wpdb->get_results(
			"SELECT option_name, option_value FROM {$wpdb->options}
			WHERE option_name NOT LIKE '_transient_timeout_%'
			AND (
				option_name LIKE '_transient_rentfetch_propertysearch_markup_%'
				OR option_name LIKE '_transient_rentfetch_floorplansearch_markup_%'
				OR option_name LIKE '_transient_rentfetch_floorplans_array_sql_%'
				OR option_name LIKE '_transient_rentfetch_meta_values_%'
				OR option_name LIKE '_transient_rentfetch_property_ids_available_%'
				OR option_name LIKE '_transient_rentfetch_date_availability_%'
				OR option_name LIKE '_transient_rentfetch_date_search_%'
			)",
			ARRAY_A
		);

		foreach ( (array) $transients as $transient ) {
			$key = preg_replace( '/^_transient_/', '', (string) $transient['option_name'] );
			if ( isset( $registry[ $key ] ) ) {
				continue;
			}

			$value        = maybe_unserialize( $transient['option_value'] );
			$generated_at = is_array( $value ) && isset( $value['rentfetch_cache_version'], $value['generated_at'] ) ? (int) $value['generated_at'] : 0;

			$registry[ $key ] = array(
				'created_at'  => $generated_at,
				'last_set_at' => $generated_at,
				'priority'    => false,
			);
		}
	}

	$now   = time();
	$stats = array(
		'html'  => array(
			'label'       => 'HTML',
			'count'       => 0,
			'fresh'       => 0,
			'stale'       => 0,
			'oldest_age'  => null,
			'newest_age'  => null,
			'priority'    => 0,
		),
		'query' => array(
			'label'       => 'Query Results',
			'count'       => 0,
			'fresh'       => 0,
			'stale'       => 0,
			'oldest_age'  => null,
			'newest_age'  => null,
			'priority'    => 0,
		),
	);

	foreach ( $registry as $key => $entry ) {
		$family = rentfetch_get_search_query_cache_family( $key );
		if ( ! isset( $stats[ $family ] ) ) {
			continue;
		}

		$generated_at = isset( $entry['last_set_at'] ) ? (int) $entry['last_set_at'] : 0;
		$has_age      = $generated_at > 0;
		if ( $generated_at <= 0 ) {
			$generated_at = $now;
		}

		$age = max( 0, $now - $generated_at );
		++$stats[ $family ]['count'];

		if ( $has_age && $age > RENTFETCH_CACHE_STALE_AFTER ) {
			++$stats[ $family ]['stale'];
		} elseif ( $has_age ) {
			++$stats[ $family ]['fresh'];
		}

		if ( ! empty( $entry['priority'] ) ) {
			++$stats[ $family ]['priority'];
		}

		if ( $has_age ) {
			$stats[ $family ]['oldest_age'] = null === $stats[ $family ]['oldest_age'] ? $age : max( $stats[ $family ]['oldest_age'], $age );
			$stats[ $family ]['newest_age'] = null === $stats[ $family ]['newest_age'] ? $age : min( $stats[ $family ]['newest_age'], $age );
		}
	}

	foreach ( $stats as $family => $family_stats ) {
		$stats[ $family ]['oldest_age_label'] = null === $family_stats['oldest_age'] ? 'n/a' : rentfetch_format_cache_age( $family_stats['oldest_age'] );
		$stats[ $family ]['newest_age_label'] = null === $family_stats['newest_age'] ? 'n/a' : rentfetch_format_cache_age( $family_stats['newest_age'] );
	}

	$history = get_option( 'rentfetch_search_query_cache_stats', array() );
	if ( ! is_array( $history ) ) {
		$history = array();
	}

	ksort( $history );
	$history = array_slice( $history, -14, null, true );

	return array(
		'limit'      => (int) apply_filters( 'rentfetch_search_query_cache_limit', RENTFETCH_SEARCH_QUERY_CACHE_LIMIT ),
		'families'   => $stats,
		'hit_history' => $history,
	);
}

/**
 * Get cache debug events recorded during the current request.
 *
 * @return array
 */
function rentfetch_get_cache_debug_events() {
	return isset( $GLOBALS['rentfetch_cache_debug_events'] ) && is_array( $GLOBALS['rentfetch_cache_debug_events'] )
		? $GLOBALS['rentfetch_cache_debug_events']
		: array();
}

/**
 * Build response metadata for a specific transient cache lookup.
 *
 * @param string $key                          Transient key.
 * @param string $status                       hit|miss.
 * @param bool   $is_stale                     Whether the hit was stale.
 * @param bool   $background_refresh_scheduled Whether a background refresh was scheduled.
 * @return array
 */
function rentfetch_get_cache_debug_metadata( $key, $status, $is_stale = false, $background_refresh_scheduled = false ) {
	return array(
		'key'                          => $key,
		'description'                  => rentfetch_describe_search_query_cache_key( $key ),
		'family'                       => rentfetch_get_search_query_cache_family( $key ),
		'status'                       => $status,
		'stale'                        => (bool) $is_stale,
		'background_refresh_scheduled' => (bool) $background_refresh_scheduled,
		'events'                       => rentfetch_get_cache_debug_events(),
	);
}

/**
 * Store a Rent Fetch cache value with metadata.
 *
 * @param string $key   Transient key.
 * @param mixed  $value Value to cache.
 * @return bool
 */
function rentfetch_set_cache_transient( $key, $value ) {
	if ( is_user_logged_in() ) {
		return false;
	}

	$stored = set_transient(
		$key,
		array(
			'rentfetch_cache_version' => 1,
			'generated_at'           => time(),
			'value'                  => $value,
		),
		RENTFETCH_CACHE_TTL
	);

	if ( $stored ) {
		rentfetch_register_search_query_cache_key( $key );
	}

	return $stored;
}

/**
 * Retrieve a Rent Fetch cache value and report whether it should be refreshed.
 *
 * Legacy unwrapped transient values are treated as stale but still usable.
 *
 * @param string $key      Transient key.
 * @param bool   $is_stale Whether the value is older than the refresh window.
 * @return mixed
 */
function rentfetch_get_cache_transient( $key, &$is_stale = false ) {
	$is_stale = false;

	if ( ! empty( $GLOBALS['rentfetch_refreshing_cache'] ) ) {
		return false;
	}

	$cached   = get_transient( $key );

	if ( false === $cached ) {
		rentfetch_record_search_query_cache_event( $key, false );
		return false;
	}

	rentfetch_record_search_query_cache_event( $key, true );

	if ( is_array( $cached ) && isset( $cached['rentfetch_cache_version'], $cached['generated_at'] ) && array_key_exists( 'value', $cached ) ) {
		$is_stale = ( time() - (int) $cached['generated_at'] ) > RENTFETCH_CACHE_STALE_AFTER;
		return $cached['value'];
	}

	$is_stale = true;
	return $cached;
}

/**
 * Refresh a stale cache entry after the response has been prepared.
 *
 * @param string   $key      Transient key.
 * @param callable $callback Callback that rebuilds and stores the cache.
 * @return void
 */
function rentfetch_refresh_cache_after_response( $key, $callback ) {
	static $callbacks = array();
	static $registered = false;

	if ( isset( $callbacks[ $key ] ) || ! is_callable( $callback ) ) {
		return;
	}

	$callbacks[ $key ] = $callback;

	if ( $registered ) {
		return;
	}

	$registered = true;
	add_action(
		'shutdown',
		function() use ( &$callbacks ) {
			if ( function_exists( 'fastcgi_finish_request' ) ) {
				fastcgi_finish_request();
			}

			foreach ( $callbacks as $callback ) {
				if ( get_option( 'rentfetch_options_disable_query_caching' ) === '1' ) {
					continue;
				}

				$GLOBALS['rentfetch_refreshing_cache'] = true;
				try {
					call_user_func( $callback );
				} finally {
					$GLOBALS['rentfetch_refreshing_cache'] = false;
				}
			}
		},
		0
	);
}
