<?php
/**
 * AJAX handler for clearing search cache transients
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Clear Rent Fetch search/query cache transients.
 *
 * @return array Clear result.
 */
function rentfetch_clear_search_cache_transients() {
	global $wpdb;

	// Get cache transients (search transients + fees CSV transients).
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$all_transients = $wpdb->get_col(
		"SELECT option_name FROM {$wpdb->options} 
			WHERE (option_name LIKE '_transient_rentfetch_propertysearch_%' 
			OR option_name LIKE '_transient_rentfetch_floorplansearch_%'
			OR option_name LIKE '_transient_rentfetch_floorplans_array_sql_%'
			OR option_name LIKE '_transient_rentfetch_meta_values_%'
			OR option_name LIKE '_transient_rentfetch_property_ids_available_%'
			OR option_name LIKE '_transient_rentfetch_property_floorplans_%'
			OR option_name LIKE '_transient_rentfetch_date_availability_%'
			OR option_name LIKE '_transient_rentfetch_date_search_%'
			OR option_name LIKE '_transient_rentfetch_fees_csv_%'
			OR option_name LIKE '_transient_rentfetch_fees_csv_calc_%')
			AND option_name NOT LIKE '_transient_timeout_%'"
	);

	$keys = array_map(
		static function ( $name ) { return substr( $name, strlen( '_transient_' ) ); },
		(array) $all_transients
	);
	// Registered keys also cover caches stored in Redis/Memcached rather than options.
	$registry = get_option( 'rentfetch_search_query_cache_registry', array() );
	if ( is_array( $registry ) ) {
		foreach ( array_keys( $registry ) as $key ) {
			if ( rentfetch_is_search_query_cache_key( $key ) ) {
				$keys[] = $key;
			}
		}
	}
	$keys = array_unique( $keys );
	foreach ( $keys as $key ) {
		delete_transient( $key );
	}

	delete_option( 'rentfetch_search_query_cache_registry' );
	delete_option( 'rentfetch_search_query_cache_stats' );
	delete_option( 'rentfetch_cache_warming_cursor' );
	unset( $GLOBALS['rentfetch_floorplans'] );

	return array(
		'message' => $keys
			? sprintf( 'Successfully cleared %d cached result(s).', count( $keys ) )
			: 'No cached results found to clear.',
		'count'   => count( $keys ),
	);
}

/**
 * Handle AJAX request to clear search cache
 */
function rentfetch_ajax_clear_search_cache() {
	// Verify nonce.
	check_ajax_referer( 'rentfetch_clear_cache', 'nonce' );

	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error(
			array(
				'message' => 'You do not have permission to perform this action.',
			)
		);
	}

	$result = rentfetch_clear_search_cache_transients();
	if ( function_exists( 'rentfetch_get_admin_bar_cache_states' ) ) {
		$result['states'] = rentfetch_get_admin_bar_cache_states();
	}

	wp_send_json_success( $result );
}
add_action( 'wp_ajax_rentfetch_clear_search_cache', 'rentfetch_ajax_clear_search_cache' );
