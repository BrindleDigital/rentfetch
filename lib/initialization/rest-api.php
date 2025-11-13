<?php
/**
 * REST API endpoints for search functionality
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register REST API routes for search
 */
function rentfetch_register_rest_routes() {
	// Properties search endpoint
	register_rest_route(
		'rentfetch/v1',
		'/search/properties',
		array(
			'methods'             => 'GET',
			'callback'            => 'rentfetch_rest_search_properties',
			'permission_callback' => '__return_true', // Public endpoint
		)
	);

	// Floorplans search endpoint
	register_rest_route(
		'rentfetch/v1',
		'/search/floorplans',
		array(
			'methods'             => 'GET',
			'callback'            => 'rentfetch_rest_search_floorplans',
			'permission_callback' => '__return_true', // Public endpoint
		)
	);
}
add_action( 'rest_api_init', 'rentfetch_register_rest_routes' );

/**
 * REST API handler for property search
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response
 */
function rentfetch_rest_search_properties( $request ) {

	// Populate $_POST with request parameters so existing filter functions work
	// This allows all the filter hooks that check $_POST to function correctly
	$_POST = array_merge( $_POST, $request->get_params() );

	$property_ids = rentfetch_get_property_ids_with_available_floorplans();
	if ( empty( $property_ids ) ) {
		$property_ids = array( '1' ); // if there aren't any properties, we shouldn't find anything.
	}

	// Get shortcode attributes from request parameters
	$atts = array();
	if ( $request->get_param( 'propertyids' ) ) {
		$atts['propertyids'] = sanitize_text_field( $request->get_param( 'propertyids' ) );
	}

	// Set maximum per page
	$properties_maximum_per_page = get_option( 'rentfetch_options_maximum_number_of_properties_to_show' );
	if ( 0 === $properties_maximum_per_page ) {
		$properties_maximum_per_page = -1;
	}

	// Base property query
	$property_args = array(
		'post_type'      => 'properties',
		'posts_per_page' => $properties_maximum_per_page,
		'no_found_rows'  => true,
		'post_status'    => 'publish',
	);

	$display_availability = get_option( 'rentfetch_options_property_availability_display' );
	if ( 'all' !== $display_availability ) {

		// If we have a propertyids attribute, use the intersection
		if ( isset( $atts['propertyids'] ) ) {
			$property_ids = array_intersect( $property_ids, explode( ',', $atts['propertyids'] ) );
		}

		// If property_ids is empty after intersection, set to nonsense value to prevent matching everything
		if ( empty( $property_ids ) ) {
			$property_ids = array( '0' );
		}

		// Add property IDs to the query
		$property_args['meta_query'] = array(
			array(
				'key'   => 'property_id',
				'value' => $property_ids,
			),
		);

	} else {
		if ( isset( $atts['propertyids'] ) ) {
			$property_ids = explode( ',', $atts['propertyids'] );
		}

		// If property_ids is empty, set to nonsense value to prevent matching everything
		if ( empty( $property_ids ) ) {
			$property_ids = array( '0' );
		}

		$property_args['meta_query'] = array(
			array(
				'key'   => 'property_id',
				'value' => $property_ids,
			),
		);
	}

	// Apply filters (this allows all the filter hooks to modify the query)
	$property_args = apply_filters( 'rentfetch_search_property_map_properties_query_args', $property_args );

	// Build cache key
	$cache_key = 'rentfetch_propertysearch_markup_' . md5( wp_json_encode( array( 'args' => $property_args, 'atts' => $atts ) ) );
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		$cached_markup = get_transient( $cache_key );
		if ( false !== $cached_markup && is_string( $cached_markup ) ) {
			$response = rest_ensure_response(
				array(
					'html'  => $cached_markup,
					'count' => substr_count( $cached_markup, 'class="property' ),
					'cached' => true,
				)
			);
			$response->header( 'Cache-Control', 'public, max-age=1800' );
			return $response;
		}
	}

	// Render the results
	$markup = rentfetch_render_property_query_results( $property_args );

	// Cache the results
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		set_transient( $cache_key, $markup, 30 * MINUTE_IN_SECONDS );
	}

	$response = rest_ensure_response(
		array(
			'html'  => $markup,
			'count' => substr_count( $markup, 'class="property' ),
			'cached' => false,
		)
	);

	// Set cache headers if caching is enabled
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		$response->header( 'Cache-Control', 'public, max-age=1800' );
	} else {
		$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate' );
	}

	return $response;
}

/**
 * REST API handler for floorplan search
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response
 */
function rentfetch_rest_search_floorplans( $request ) {

	// Populate $_POST with request parameters so existing filter functions work
	// This allows all the filter hooks that check $_POST to function correctly
	$_POST = array_merge( $_POST, $request->get_params() );

	// Get shortcode attributes from request parameters
	$atts = array();
	$possible_atts = array( 'property', 'propertyids', 'taxonomy', 'term' );
	foreach ( $possible_atts as $att ) {
		if ( $request->get_param( $att ) ) {
			$atts[ $att ] = sanitize_text_field( $request->get_param( $att ) );
		}
	}

	// Base floorplan query
	$floorplan_args = array(
		'post_type'      => 'floorplans',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	);

	// Apply filters (this allows all the filter hooks to modify the query)
	$floorplan_args = apply_filters( 'rentfetch_search_floorplans_query_args', $floorplan_args );

	// Build cache key
	$cache_key = 'rentfetch_floorplansearch_markup_' . md5( wp_json_encode( array( 'args' => $floorplan_args, 'atts' => $atts ) ) );
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		$cached_markup = get_transient( $cache_key );
		if ( false !== $cached_markup && is_string( $cached_markup ) ) {
			$response = rest_ensure_response(
				array(
					'html'  => $cached_markup,
					'count' => substr_count( $cached_markup, 'class="floorplan' ),
					'cached' => true,
				)
			);
			$response->header( 'Cache-Control', 'public, max-age=1800' );
			return $response;
		}
	}

	// Render the results
	$markup = rentfetch_render_floorplan_query_results( $floorplan_args );

	// Cache the results
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		set_transient( $cache_key, $markup, 30 * MINUTE_IN_SECONDS );
	}

	$response = rest_ensure_response(
		array(
			'html'  => $markup,
			'count' => substr_count( $markup, 'class="floorplan' ),
			'cached' => false,
		)
	);

	// Set cache headers if caching is enabled
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		$response->header( 'Cache-Control', 'public, max-age=1800' );
	} else {
		$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate' );
	}

	return $response;
}
