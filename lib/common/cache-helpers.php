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
 * Get the reason this request should not contribute to persistent cache stats.
 *
 * Admin/editor traffic and cache warming are operational activity, not visitor
 * behavior, so they should not skew the dashboard hit/miss history.
 *
 * @return string Empty string when stats should be recorded.
 */
function rentfetch_get_search_query_cache_stats_skip_reason() {
	if ( ! empty( $GLOBALS['rentfetch_force_cache_write'] ) || ! empty( $GLOBALS['rentfetch_refreshing_cache'] ) ) {
		return 'cache_warming_or_refresh';
	}

	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return 'administrator';
	}

	return '';
}

/**
 * Determine whether this request should contribute to persistent cache stats.
 *
 * @return bool
 */
function rentfetch_should_record_search_query_cache_stats() {
	$metadata = rentfetch_get_search_query_cache_stats_recording_metadata();

	return ! empty( $metadata['recorded'] );
}

/**
 * Build metadata about whether the current request can record cache stats.
 *
 * @return array
 */
function rentfetch_get_search_query_cache_stats_recording_metadata() {
	$skip_reason = rentfetch_get_search_query_cache_stats_skip_reason();

	return array(
		'recorded'    => '' === $skip_reason,
		'skip_reason' => $skip_reason,
	);
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

	if ( ! rentfetch_should_record_search_query_cache_stats() ) {
		return;
	}

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
 * Determine whether cache diagnostics should be collected.
 *
 * @return bool
 */
function rentfetch_cache_diagnostics_enabled() {
	return ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || get_option( 'rentfetch_options_enable_cache_console_logging', '0' ) === '1';
}

/**
 * Record a search/query transient cache write during the current request.
 *
 * @param string $key    Transient key.
 * @param bool   $stored Whether WordPress reported the transient was stored.
 * @param mixed  $value  Stored value.
 * @return void
 */
function rentfetch_record_search_query_cache_write_event( $key, $stored, $value ) {
	if ( ! rentfetch_cache_diagnostics_enabled() ) {
		return;
	}

	if ( ! rentfetch_is_search_query_cache_key( $key ) ) {
		return;
	}

	if ( ! isset( $GLOBALS['rentfetch_cache_write_events'] ) || ! is_array( $GLOBALS['rentfetch_cache_write_events'] ) ) {
		$GLOBALS['rentfetch_cache_write_events'] = array();
	}

	$GLOBALS['rentfetch_cache_write_events'][] = array(
		'key'         => $key,
		'description' => rentfetch_describe_search_query_cache_key( $key ),
		'family'      => rentfetch_get_search_query_cache_family( $key ),
		'stored'      => (bool) $stored,
		'value_type'  => gettype( $value ),
		'value_size'  => strlen( maybe_serialize( $value ) ),
	);
}

/**
 * Record a search/query transient prune during the current request.
 *
 * @param string $key Transient key.
 * @return void
 */
function rentfetch_record_search_query_cache_prune_event( $key ) {
	if ( ! rentfetch_cache_diagnostics_enabled() ) {
		return;
	}

	if ( ! rentfetch_is_search_query_cache_key( $key ) ) {
		return;
	}

	if ( ! isset( $GLOBALS['rentfetch_cache_prune_events'] ) || ! is_array( $GLOBALS['rentfetch_cache_prune_events'] ) ) {
		$GLOBALS['rentfetch_cache_prune_events'] = array();
	}

	$GLOBALS['rentfetch_cache_prune_events'][] = array(
		'key'         => $key,
		'description' => rentfetch_describe_search_query_cache_key( $key ),
		'family'      => rentfetch_get_search_query_cache_family( $key ),
	);
}

/**
 * Log failed cache writes while cache diagnostics are enabled.
 *
 * @param string $key    Transient key.
 * @param mixed  $value  Stored value.
 * @return void
 */
function rentfetch_maybe_log_failed_cache_write( $key, $value ) {
	if ( ! rentfetch_is_search_query_cache_key( $key ) ) {
		return;
	}

	if ( ! rentfetch_cache_diagnostics_enabled() ) {
		return;
	}

	global $wpdb;

	error_log(
		wp_json_encode(
			array(
				'source'       => 'rentfetch_cache_write',
				'key'          => $key,
				'description'  => rentfetch_describe_search_query_cache_key( $key ),
				'family'       => rentfetch_get_search_query_cache_family( $key ),
				'value_type'   => gettype( $value ),
				'value_size'   => strlen( maybe_serialize( $value ) ),
				'db_error'     => isset( $wpdb ) ? $wpdb->last_error : '',
				'logged_in'    => is_user_logged_in(),
				'cache_option' => get_option( 'rentfetch_options_disable_query_caching', '1' ),
			)
		)
	);
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

	if ( ! isset( $GLOBALS['rentfetch_recently_stored_cache_keys'] ) || ! is_array( $GLOBALS['rentfetch_recently_stored_cache_keys'] ) ) {
		$GLOBALS['rentfetch_recently_stored_cache_keys'] = array();
	}
	$GLOBALS['rentfetch_recently_stored_cache_keys'][ $key ] = true;

	update_option( 'rentfetch_search_query_cache_registry', $registry, false );
	rentfetch_prune_search_query_cache_registry( $registry );
}

/**
 * Remove a search/query cache key from the bounded cache registry.
 *
 * @param string $key Transient key.
 * @return void
 */
function rentfetch_unregister_search_query_cache_key( $key ) {
	if ( ! rentfetch_is_search_query_cache_key( $key ) ) {
		return;
	}

	$registry = get_option( 'rentfetch_search_query_cache_registry', array() );
	if ( ! is_array( $registry ) || ! isset( $registry[ $key ] ) ) {
		return;
	}

	unset( $registry[ $key ] );
	update_option( 'rentfetch_search_query_cache_registry', $registry, false );
}

/**
 * Delete an empty search/query cache entry.
 *
 * @param string $key Transient key.
 * @return void
 */
function rentfetch_delete_empty_search_query_cache_entry( $key ) {
	if ( ! rentfetch_is_search_query_cache_key( $key ) ) {
		return;
	}

	delete_transient( $key );
	rentfetch_unregister_search_query_cache_key( $key );
	rentfetch_record_search_query_cache_prune_event( $key );
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
			$a_last_set = (int) ( $a['last_set_at'] ?? 0 );
			$b_last_set = (int) ( $b['last_set_at'] ?? 0 );

			if ( $a_last_set !== $b_last_set ) {
				return $a_last_set - $b_last_set;
			}

			$a_priority = ! empty( $a['priority'] ) ? 1 : 0;
			$b_priority = ! empty( $b['priority'] ) ? 1 : 0;

			if ( $a_priority !== $b_priority ) {
				return $a_priority - $b_priority;
			}

			return 0;
		}
	);

	$delete_count = count( $registry ) - $limit;
	$deleted      = 0;
	$protected    = isset( $GLOBALS['rentfetch_recently_stored_cache_keys'] ) && is_array( $GLOBALS['rentfetch_recently_stored_cache_keys'] )
		? $GLOBALS['rentfetch_recently_stored_cache_keys']
		: array();

	foreach ( array_keys( $registry ) as $key ) {
		if ( isset( $protected[ $key ] ) ) {
			continue;
		}

		delete_transient( $key );
		rentfetch_record_search_query_cache_prune_event( $key );
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
 * Get cache write events recorded during the current request.
 *
 * @return array
 */
function rentfetch_get_cache_debug_write_events() {
	return isset( $GLOBALS['rentfetch_cache_write_events'] ) && is_array( $GLOBALS['rentfetch_cache_write_events'] )
		? $GLOBALS['rentfetch_cache_write_events']
		: array();
}

/**
 * Get cache prune events recorded during the current request.
 *
 * @return array
 */
function rentfetch_get_cache_debug_prune_events() {
	return isset( $GLOBALS['rentfetch_cache_prune_events'] ) && is_array( $GLOBALS['rentfetch_cache_prune_events'] )
		? $GLOBALS['rentfetch_cache_prune_events']
		: array();
}

/**
 * Build response metadata for a specific transient cache lookup.
 *
 * @param string $key                          Transient key.
 * @param string $status                       hit|miss.
 * @param bool   $is_stale                     Whether the hit was stale.
 * @param bool   $background_refresh_scheduled Whether a background refresh was scheduled.
 * @param array  $context                      Additional cache context.
 * @return array
 */
function rentfetch_get_cache_debug_metadata( $key, $status, $is_stale = false, $background_refresh_scheduled = false, $context = array() ) {
	$metadata = array(
		'lookup_attempted' => true,
		'read_enabled'     => true,
		'write_enabled'    => false,
		'write_attempted'  => false,
		'write_stored'     => null,
	);

	if ( is_array( $context ) ) {
		$metadata = array_merge( $metadata, $context );
	}

	return array(
		'key'                          => $key,
		'description'                  => rentfetch_describe_search_query_cache_key( $key ),
		'family'                       => rentfetch_get_search_query_cache_family( $key ),
		'status'                       => $status,
		'stale'                        => (bool) $is_stale,
		'background_refresh_scheduled' => (bool) $background_refresh_scheduled,
		'lookup_attempted'             => (bool) $metadata['lookup_attempted'],
		'read_enabled'                 => (bool) $metadata['read_enabled'],
		'write_enabled'                => (bool) $metadata['write_enabled'],
		'write_attempted'              => (bool) $metadata['write_attempted'],
		'write_stored'                 => null === $metadata['write_stored'] ? null : (bool) $metadata['write_stored'],
		'stats'                        => rentfetch_get_search_query_cache_stats_recording_metadata(),
		'events'                       => rentfetch_get_cache_debug_events(),
		'writes'                       => rentfetch_get_cache_debug_write_events(),
		'prunes'                       => rentfetch_get_cache_debug_prune_events(),
	);
}

/**
 * Count rendered posts in cached search markup.
 *
 * @param string $key   Transient key.
 * @param mixed  $value Cached value.
 * @return int|null Result count, or null when the value is not rendered markup.
 */
function rentfetch_count_markup_cache_results( $key, $value ) {
	if ( 0 === strpos( $key, 'rentfetch_propertysearch_markup_' ) ) {
		if ( is_array( $value ) && isset( $value['result_count'] ) ) {
			return max( 0, (int) $value['result_count'] );
		}

		if ( is_array( $value ) && isset( $value['map_points'] ) && is_array( $value['map_points'] ) ) {
			return count( $value['map_points'] );
		}

		$html = is_array( $value ) && isset( $value['html'] ) ? $value['html'] : $value;
		if ( is_string( $html ) ) {
			return (int) preg_match_all( '/<div\s+class="[^"]*\btype-properties\b/', $html );
		}

		return null;
	}

	if ( 0 === strpos( $key, 'rentfetch_floorplansearch_markup_' ) ) {
		if ( is_array( $value ) && isset( $value['result_count'] ) ) {
			return max( 0, (int) $value['result_count'] );
		}

		$html = is_array( $value ) && isset( $value['html'] ) ? $value['html'] : $value;
		if ( is_string( $html ) ) {
			return (int) preg_match_all( '/<div\s+class="[^"]*\btype-floorplans\b/', $html );
		}

		return null;
	}

	return null;
}

/**
 * Determine whether cached search markup contains any available result cards.
 *
 * @param string $key   Transient key.
 * @param mixed  $value Cached value.
 * @return bool|null Whether availability was found, or null when not markup.
 */
function rentfetch_markup_cache_has_positive_availability( $key, $value ) {
	if ( 0 !== strpos( $key, 'rentfetch_propertysearch_markup_' ) && 0 !== strpos( $key, 'rentfetch_floorplansearch_markup_' ) ) {
		return null;
	}

	$html = is_array( $value ) && isset( $value['html'] ) ? $value['html'] : $value;
	if ( ! is_string( $html ) ) {
		return null;
	}

	return false !== strpos( $html, 'has-units-available' );
}

/**
 * Determine whether a cached floorplan aggregate has positive availability.
 *
 * @param string $key   Transient key.
 * @param mixed  $value Cached value.
 * @return bool|null Whether availability was found, or null when not an aggregate.
 */
function rentfetch_floorplan_aggregate_cache_has_positive_availability( $key, $value ) {
	if ( 0 !== strpos( $key, 'rentfetch_floorplans_array_sql_' ) ) {
		return null;
	}

	if ( ! is_array( $value ) ) {
		return null;
	}

	foreach ( $value as $floorplan_group ) {
		if ( ! is_array( $floorplan_group ) ) {
			continue;
		}

		if ( isset( $floorplan_group['availability'] ) && (int) $floorplan_group['availability'] > 0 ) {
			return true;
		}

		if ( isset( $floorplan_group['available_units'] ) && is_array( $floorplan_group['available_units'] ) ) {
			foreach ( $floorplan_group['available_units'] as $available_units ) {
				if ( (int) $available_units > 0 ) {
					return true;
				}
			}
		}
	}

	return false;
}

/**
 * Determine whether a cached value represents an empty result set.
 *
 * Empty search and query results are cheap to become stale and expensive when
 * they hide newly synced availability, so do not persist them.
 *
 * @param string $key   Transient key.
 * @param mixed  $value Cached value.
 * @return bool
 */
function rentfetch_cache_value_is_empty_result( $key, $value ) {
	if ( ! rentfetch_is_search_query_cache_key( $key ) ) {
		return false;
	}

	$markup_count = rentfetch_count_markup_cache_results( $key, $value );
	if ( null !== $markup_count ) {
		if ( $markup_count <= 0 ) {
			return true;
		}

		$markup_has_availability = rentfetch_markup_cache_has_positive_availability( $key, $value );
		if ( null !== $markup_has_availability ) {
			return ! $markup_has_availability;
		}

		return false;
	}

	$markup_has_availability = rentfetch_markup_cache_has_positive_availability( $key, $value );
	if ( null !== $markup_has_availability ) {
		return ! $markup_has_availability;
	}

	$aggregate_has_availability = rentfetch_floorplan_aggregate_cache_has_positive_availability( $key, $value );
	if ( null !== $aggregate_has_availability ) {
		return ! $aggregate_has_availability;
	}

	if ( is_array( $value ) && empty( $value ) ) {
		return true;
	}

	return false === $value;
}

/**
 * Determine whether a value should be stored in the Rent Fetch cache.
 *
 * @param string $key   Transient key.
 * @param mixed  $value Value to cache.
 * @return bool
 */
function rentfetch_should_store_cache_transient( $key, $value ) {
	return ! rentfetch_cache_value_is_empty_result( $key, $value );
}

/**
 * Store a Rent Fetch cache value with metadata.
 *
 * @param string $key   Transient key.
 * @param mixed  $value Value to cache.
 * @return bool
 */
function rentfetch_set_cache_transient( $key, $value ) {
	if ( is_user_logged_in() && empty( $GLOBALS['rentfetch_force_cache_write'] ) ) {
		return false;
	}

	if ( ! rentfetch_should_store_cache_transient( $key, $value ) ) {
		rentfetch_delete_empty_search_query_cache_entry( $key );
		rentfetch_record_search_query_cache_write_event( $key, false, $value );
		return false;
	}

	$payload = array(
		'rentfetch_cache_version' => 1,
		'generated_at'           => time(),
		'value'                  => $value,
	);

	$stored = set_transient( $key, $payload, RENTFETCH_CACHE_TTL );
	rentfetch_record_search_query_cache_write_event( $key, $stored, $payload );

	if ( ! $stored ) {
		rentfetch_maybe_log_failed_cache_write( $key, $payload );
	}

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

	if ( is_array( $cached ) && isset( $cached['rentfetch_cache_version'], $cached['generated_at'] ) && array_key_exists( 'value', $cached ) ) {
		if ( ! rentfetch_should_store_cache_transient( $key, $cached['value'] ) ) {
			rentfetch_delete_empty_search_query_cache_entry( $key );
			rentfetch_record_search_query_cache_event( $key, false );
			return false;
		}

		rentfetch_record_search_query_cache_event( $key, true );
		$is_stale = ( time() - (int) $cached['generated_at'] ) > RENTFETCH_CACHE_STALE_AFTER;
		return $cached['value'];
	}

	if ( ! rentfetch_should_store_cache_transient( $key, $cached ) ) {
		rentfetch_delete_empty_search_query_cache_entry( $key );
		rentfetch_record_search_query_cache_event( $key, false );
		return false;
	}

	rentfetch_record_search_query_cache_event( $key, true );
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
				if ( get_option( 'rentfetch_options_disable_query_caching', '1' ) === '1' ) {
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
