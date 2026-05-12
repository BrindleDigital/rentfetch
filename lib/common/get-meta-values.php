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
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		$cached = rentfetch_get_cache_transient( $cache_key );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}
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

	// Sanitize meta values before caching.
	$r = array_map(
		function( $value ) {
			return sanitize_text_field( wp_unslash( $value ) );
		},
		(array) $r
	);

	// Cache the result briefly to avoid repeated expensive queries.
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		rentfetch_set_cache_transient( $cache_key, $r );
	}

	return $r;
}

/**
 * Store shortcode attributes for floorplan-level filters during the current request.
 *
 * @param array $atts Shortcode attributes.
 * @return void
 */
function rentfetch_set_floorplan_filter_shortcode_atts( $atts ) {
	$GLOBALS['rentfetch_floorplan_filter_shortcode_atts'] = is_array( $atts ) ? $atts : array();
}

/**
 * Get shortcode attributes for floorplan-level filters during the current request.
 *
 * @return array
 */
function rentfetch_get_floorplan_filter_shortcode_atts() {
	return isset( $GLOBALS['rentfetch_floorplan_filter_shortcode_atts'] ) && is_array( $GLOBALS['rentfetch_floorplan_filter_shortcode_atts'] )
		? $GLOBALS['rentfetch_floorplan_filter_shortcode_atts']
		: array();
}

/**
 * Normalize a comma-separated property ID list.
 *
 * @param string|array $property_ids Property IDs.
 * @return array
 */
function rentfetch_normalize_floorplan_filter_property_ids( $property_ids ) {
	if ( is_array( $property_ids ) ) {
		$values = $property_ids;
	} else {
		$values = explode( ',', (string) $property_ids );
	}

	$values = array_map(
		function( $value ) {
			return sanitize_text_field( trim( wp_unslash( (string) $value ) ) );
		},
		$values
	);

	return array_values( array_unique( array_filter( $values, 'strlen' ) ) );
}

/**
 * Get property IDs that should scope floorplan filter options.
 *
 * @return array
 */
function rentfetch_get_floorplan_filter_property_ids() {
	$atts = rentfetch_get_floorplan_filter_shortcode_atts();

	foreach ( array( 'property_id', 'propertyids', 'property' ) as $key ) {
		if ( ! empty( $atts[ $key ] ) ) {
			return rentfetch_normalize_floorplan_filter_property_ids( $atts[ $key ] );
		}
	}

	foreach ( array( 'property_id', 'propertyids', 'property' ) as $key ) {
		if ( ! empty( $_POST[ $key ] ) ) {
			return rentfetch_normalize_floorplan_filter_property_ids( wp_unslash( $_POST[ $key ] ) );
		}

		if ( ! empty( $_GET[ $key ] ) ) {
			return rentfetch_normalize_floorplan_filter_property_ids( wp_unslash( $_GET[ $key ] ) );
		}
	}

	if ( is_singular( 'properties' ) ) {
		$property_id = get_post_meta( get_the_ID(), 'property_id', true );
		if ( $property_id ) {
			return rentfetch_normalize_floorplan_filter_property_ids( $property_id );
		}
	}

	return array();
}

/**
 * Get floorplan meta values scoped to one or more property IDs.
 *
 * This intentionally avoids persistent transients so property-scoped filter options
 * always reflect the current property context.
 *
 * @param string $key          Meta key.
 * @param array  $property_ids Property IDs.
 * @param string $status       Post status.
 * @return array
 */
function rentfetch_get_floorplan_meta_values_for_property_ids( $key, $property_ids, $status = 'publish' ) {
	global $wpdb;

	$key          = sanitize_key( $key );
	$property_ids = rentfetch_normalize_floorplan_filter_property_ids( $property_ids );
	$status       = sanitize_text_field( $status );

	if ( empty( $key ) || empty( $property_ids ) ) {
		return array();
	}

	static $request_cache = array();
	$cache_key = md5( wp_json_encode( array( $key, $property_ids, $status ) ) );

	if ( isset( $request_cache[ $cache_key ] ) ) {
		return $request_cache[ $cache_key ];
	}

	$property_placeholders = implode( ',', array_fill( 0, count( $property_ids ), '%s' ) );
	$query_args            = array_merge( array( $key, $status ), $property_ids );

	$values = $wpdb->get_col(
		$wpdb->prepare(
			"
			SELECT pm.meta_value
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			INNER JOIN {$wpdb->postmeta} property_meta ON property_meta.post_id = p.ID
			WHERE pm.meta_key = %s
			AND p.post_status = %s
			AND p.post_type = 'floorplans'
			AND property_meta.meta_key = 'property_id'
			AND property_meta.meta_value IN ($property_placeholders)
			",
			$query_args
		)
	);

	$values = array_map(
		function( $value ) {
			return sanitize_text_field( wp_unslash( $value ) );
		},
		(array) $values
	);

	$request_cache[ $cache_key ] = $values;

	return $values;
}

/**
 * Get floorplan taxonomy terms scoped to one or more property IDs.
 *
 * @param string $taxonomy     Taxonomy slug.
 * @param array  $property_ids Property IDs.
 * @param string $status       Post status.
 * @return array
 */
function rentfetch_get_floorplan_terms_for_property_ids( $taxonomy, $property_ids, $status = 'publish' ) {
	$taxonomy     = sanitize_key( $taxonomy );
	$property_ids = rentfetch_normalize_floorplan_filter_property_ids( $property_ids );
	$status       = sanitize_text_field( $status );

	if ( empty( $taxonomy ) || empty( $property_ids ) || ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	static $request_cache = array();
	$cache_key = md5( wp_json_encode( array( $taxonomy, $property_ids, $status ) ) );

	if ( isset( $request_cache[ $cache_key ] ) ) {
		return $request_cache[ $cache_key ];
	}

	$floorplan_ids = get_posts(
		array(
			'post_type'      => 'floorplans',
			'post_status'    => $status,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'   => 'property_id',
					'value' => $property_ids,
				),
			),
		)
	);

	if ( empty( $floorplan_ids ) ) {
		$request_cache[ $cache_key ] = array();
		return array();
	}

	$terms = wp_get_object_terms(
		$floorplan_ids,
		$taxonomy,
		array(
			'orderby' => 'name',
			'order'   => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}

	$request_cache[ $cache_key ] = $terms;

	return $terms;
}
