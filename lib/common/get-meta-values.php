<?php
/**
 * This file gets all meta values for a key.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get the meta values for a key for all posts of a given type.
 *
 * @param   string $key     the meta key.
 * @param   string $type    the post type.
 * @param   string $status  the post status.
 *
 * @return  array            the array of the meta values.
 */
function rentfetch_get_meta_values( $key = '', $type = 'post', $status = 'publish' ) {

	global $wpdb;

	if ( empty( $key ) ) {
		return;
	}

	// Pseudocache: transient keyed by key/type/status to avoid repeated DB queries.
	$cache_key = 'rentfetch_meta_values_' . md5( wp_json_encode( array( 'key' => $key, 'type' => $type, 'status' => $status ) ) );
	$cached = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$r = $wpdb->get_col(
		$wpdb->prepare(
			"
		SELECT pm.meta_value FROM {$wpdb->postmeta} pm
		LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = %s 
		AND p.post_status = %s 
		AND p.post_type = %s
	",
			$key,
			$status,
			$type
		)
	);

	// Cache the result briefly to avoid repeated expensive queries.
	set_transient( $cache_key, $r, 5 * MINUTE_IN_SECONDS );

	return $r;
}
