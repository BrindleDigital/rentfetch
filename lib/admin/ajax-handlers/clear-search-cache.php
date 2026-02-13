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

	global $wpdb;

	// Get cache transients (search transients + fees CSV transients).
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$all_transients = $wpdb->get_col(
		"SELECT option_name FROM {$wpdb->options} 
		WHERE (option_name LIKE '_transient_rentfetch_propertysearch_%' 
		OR option_name LIKE '_transient_rentfetch_floorplansearch_%'
		OR option_name LIKE '_transient_rentfetch_floorplans_array_sql_%'
		OR option_name LIKE '_transient_rentfetch_property_ids_available_%'
		OR option_name LIKE '_transient_rentfetch_property_floorplans_%'
		OR option_name LIKE '_transient_rentfetch_fees_csv_%')
		AND option_name NOT LIKE '_transient_timeout_%'"
	);

	$transient_count = count( $all_transients );

	if ( $transient_count > 0 ) {
		// Delete cache transients and their timeouts.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->query(
			"DELETE FROM {$wpdb->options} 
			WHERE option_name LIKE '_transient_rentfetch_propertysearch_%' 
			OR option_name LIKE '_transient_timeout_rentfetch_propertysearch_%'
			OR option_name LIKE '_transient_rentfetch_floorplansearch_%' 
			OR option_name LIKE '_transient_timeout_rentfetch_floorplansearch_%'
			OR option_name LIKE '_transient_rentfetch_floorplans_array_sql_%' 
			OR option_name LIKE '_transient_timeout_rentfetch_floorplans_array_sql_%'
			OR option_name LIKE '_transient_rentfetch_property_ids_available_%' 
			OR option_name LIKE '_transient_timeout_rentfetch_property_ids_available_%'
			OR option_name LIKE '_transient_rentfetch_property_floorplans_%' 
			OR option_name LIKE '_transient_timeout_rentfetch_property_floorplans_%'
			OR option_name LIKE '_transient_rentfetch_fees_csv_%'
			OR option_name LIKE '_transient_timeout_rentfetch_fees_csv_%'"
		);

		wp_send_json_success(
			array(
				'message' => sprintf(
					'Successfully cleared %d cached result(s).',
					$transient_count
				),
				'count'   => $transient_count,
			)
		);
	} else {
		wp_send_json_success(
			array(
				'message' => 'No cached results found to clear.',
				'count'   => 0,
			)
		);
	}
}
add_action( 'wp_ajax_rentfetch_clear_search_cache', 'rentfetch_ajax_clear_search_cache' );
