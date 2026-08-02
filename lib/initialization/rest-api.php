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
	// Properties search endpoint.
	register_rest_route(
		'rentfetch/v1',
		'/search/properties',
		array(
			'methods'             => 'GET',
			'callback'            => 'rentfetch_rest_search_properties_safe',
			'permission_callback' => '__return_true', // Public endpoint.
		)
	);

	// Floorplans search endpoint.
	register_rest_route(
		'rentfetch/v1',
		'/search/floorplans',
		array(
			'methods'             => 'GET',
			'callback'            => 'rentfetch_rest_search_floorplans_safe',
			'permission_callback' => '__return_true', // Public endpoint.
		)
	);
}
add_action( 'rest_api_init', 'rentfetch_register_rest_routes' );

/**
 * Sanitize request parameters for search failure logging.
 *
 * @param array $params Request parameters.
 * @return array
 */
function rentfetch_sanitize_search_log_params( $params ) {
	$sanitized = array();

	foreach ( $params as $key => $value ) {
		$sanitized_key = sanitize_key( $key );

		if ( is_array( $value ) ) {
			$sanitized[ $sanitized_key ] = rentfetch_sanitize_search_log_params( wp_unslash( $value ) );
		} else {
			$sanitized[ $sanitized_key ] = sanitize_text_field( wp_unslash( $value ) );
		}
	}

	return $sanitized;
}

/**
 * Get the validation rules for search parameters owned by Rent Fetch.
 *
 * Parameters not listed here are deliberately preserved without modification
 * so integrations can continue adding custom search fields and consuming them
 * through the existing query filters.
 *
 * @param string $search_type Search type.
 * @return array<string, string> Parameter rules keyed by request parameter.
 */
function rentfetch_get_builtin_search_parameter_schema( $search_type ) {
	$schema = array(
		'propertyids'               => 'text',
		'property_id'               => 'text',
		'property'                  => 'text',
		'posts_per_page'            => 'integer',
		'taxonomy'                  => 'key',
		'terms'                     => 'text',
		'term'                      => 'text',
		'search-propertytypes'      => 'positive_integer_array',
		'search-propertycategories' => 'positive_integer_array',
		'search-city'               => 'text_array',
		'search-amenities'          => 'positive_integer_array',
		'search-floorplancategory'  => 'positive_integer_array',
		'search-floorplantype'      => 'positive_integer_array',
		'search-beds'               => 'nonnegative_number_array',
		'search-baths'              => 'nonnegative_number_array',
		'search-dates'              => 'text_array',
		'pricesmall'                => 'nonnegative_integer',
		'pricebig'                  => 'nonnegative_integer',
		'sqftsmall'                 => 'nonnegative_integer',
		'sqftbig'                   => 'nonnegative_integer',
		'sort'                      => 'sort',
		'pets'                      => 'pets',
		'availability'              => 'boolean',
		'textsearch'                => 'text',
		'skip_tracking'             => 'boolean',
	);

	/**
	 * Filter validation rules for Rent Fetch-owned search parameters.
	 *
	 * Custom parameters do not need to be added to this schema. Parameters that
	 * are absent from the schema continue through the search request unchanged.
	 *
	 * @param array<string, string> $schema      Built-in parameter rules.
	 * @param string                $search_type Search type.
	 */
	return apply_filters( 'rentfetch_builtin_search_parameter_schema', $schema, sanitize_key( $search_type ) );
}

/**
 * Normalize a list of scalar request values.
 *
 * @param mixed  $value Raw request value.
 * @param string $rule  Item validation rule.
 * @return array|null Normalized values, or null when the submitted type is invalid.
 */
function rentfetch_normalize_builtin_search_array( $value, $rule ) {
	if ( ! is_array( $value ) ) {
		return null;
	}

	$normalized = array();
	foreach ( $value as $item ) {
		if ( ! is_scalar( $item ) || is_bool( $item ) ) {
			continue;
		}

		if ( 'positive_integer_array' === $rule ) {
			$integer = filter_var( $item, FILTER_VALIDATE_INT );
			if ( false !== $integer && 0 < $integer ) {
				$normalized[] = $integer;
			}
			continue;
		}

		if ( 'nonnegative_number_array' === $rule ) {
			$item = sanitize_text_field( wp_unslash( (string) $item ) );
			if ( is_numeric( $item ) && 0 <= (float) $item ) {
				$normalized[] = (string) (float) $item;
			}
			continue;
		}

		$normalized[] = sanitize_text_field( wp_unslash( (string) $item ) );
	}

	return array_values( array_unique( $normalized, SORT_REGULAR ) );
}

/**
 * Normalize one Rent Fetch-owned search parameter.
 *
 * @param mixed  $value Raw request value.
 * @param string $rule  Validation rule.
 * @return mixed|null Normalized value, or null when the submitted type is invalid.
 */
function rentfetch_normalize_builtin_search_value( $value, $rule ) {
	if ( in_array( $rule, array( 'positive_integer_array', 'nonnegative_number_array', 'text_array' ), true ) ) {
		return rentfetch_normalize_builtin_search_array( $value, $rule );
	}

	if ( ! is_scalar( $value ) || ( is_bool( $value ) && 'boolean' !== $rule ) ) {
		return null;
	}

	if ( 'integer' === $rule ) {
		$integer = filter_var( $value, FILTER_VALIDATE_INT );
		return false === $integer ? null : $integer;
	}

	if ( 'nonnegative_integer' === $rule ) {
		$integer = filter_var( $value, FILTER_VALIDATE_INT );
		return false === $integer || 0 > $integer ? null : $integer;
	}

	if ( 'boolean' === $rule ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		$boolean = strtolower( trim( (string) $value ) );
		if ( in_array( $boolean, array( '1', 'true', 'yes', 'on' ), true ) ) {
			return true;
		}
		if ( in_array( $boolean, array( '0', 'false', 'no', 'off', '' ), true ) ) {
			return false;
		}

		return null;
	}

	$value = sanitize_text_field( wp_unslash( (string) $value ) );

	if ( 'key' === $rule ) {
		return sanitize_key( $value );
	}

	if ( 'sort' === $rule ) {
		return in_array( $value, array( 'availability', 'beds', 'baths', 'pricelow', 'pricehigh', 'alphabetical', 'menu_order' ), true ) ? $value : null;
	}

	if ( 'pets' === $rule ) {
		$pets = filter_var( $value, FILTER_VALIDATE_INT );
		return false !== $pets && in_array( $pets, array( 1, 2, 3, 4 ), true ) ? $pets : null;
	}

	return $value;
}

/**
 * Normalize only Rent Fetch-owned search parameters.
 *
 * Unknown parameters retain their original keys, values, and shapes for
 * backward compatibility with third-party search extensions.
 *
 * @param array  $params      Request parameters.
 * @param string $search_type Search type.
 * @return array Normalized parameters.
 */
function rentfetch_normalize_builtin_search_params( $params, $search_type ) {
	$normalized = $params;
	$schema     = rentfetch_get_builtin_search_parameter_schema( $search_type );

	foreach ( $schema as $parameter => $rule ) {
		if ( ! array_key_exists( $parameter, $params ) ) {
			continue;
		}

		$value = rentfetch_normalize_builtin_search_value( $params[ $parameter ], $rule );
		if ( null === $value ) {
			unset( $normalized[ $parameter ] );
			continue;
		}

		$normalized[ $parameter ] = $value;
	}

	return $normalized;
}

/**
 * Get request parameters that should participate in search behavior/tracking.
 *
 * JQuery adds "_" when AJAX cache-busting is enabled for diagnostics. That
 * parameter should not change tracked searches or leak into filter globals.
 *
 * @param WP_REST_Request $request     The REST request.
 * @param string          $search_type Search type.
 * @return array
 */
function rentfetch_get_behavioral_search_request_params( $request, $search_type ) {
	$params = $request instanceof WP_REST_Request ? $request->get_params() : array();

	unset(
		$params['_'],
		$params['rentfetch_cache_debug_request']
	);

	return rentfetch_normalize_builtin_search_params( $params, $search_type );
}

/**
 * Log failed search requests to the PHP/WordPress debug log.
 *
 * @param string          $search_type Search type.
 * @param WP_REST_Request $request     REST request.
 * @param Throwable       $exception   Exception or error.
 * @return string Error reference for correlating frontend errors with logs.
 */
function rentfetch_log_search_failure( $search_type, $request, $exception ) {
	$error_reference = uniqid( 'rf-search-', true );

	error_log(
		wp_json_encode(
			array(
				'source'          => 'rentfetch_search',
				'error_reference' => $error_reference,
				'search_type'     => $search_type,
				'message'         => $exception->getMessage(),
				'file'            => $exception->getFile(),
				'line'            => $exception->getLine(),
				'params'          => rentfetch_sanitize_search_log_params( $request->get_params() ),
				'url'             => esc_url_raw( $request->get_route() ),
			)
		)
	);

	return $error_reference;
}

/**
 * Build a REST error response for failed search requests.
 *
 * @param string          $search_type Search type.
 * @param WP_REST_Request $request     REST request.
 * @param Throwable       $exception   Exception or error.
 * @return WP_Error
 */
function rentfetch_get_search_failure_response( $search_type, $request, $exception ) {
	$error_reference = rentfetch_log_search_failure( $search_type, $request, $exception );

	return new WP_Error(
		'rentfetch_search_failed',
		sprintf(
			/* translators: %s: error reference ID */
			__( 'Search failed because the server could not complete the request. Error reference: %s', 'rentfetch' ),
			$error_reference
		),
		array(
			'status'          => 500,
			'error_reference' => $error_reference,
		)
	);
}

/**
 * REST API handler wrapper for property search failures.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error
 */
function rentfetch_rest_search_properties_safe( $request ) {
	try {
		return rentfetch_rest_search_properties( $request );
	} catch ( Throwable $exception ) {
		return rentfetch_get_search_failure_response( 'properties', $request, $exception );
	}
}

/**
 * REST API handler wrapper for floorplan search failures.
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response|WP_Error
 */
function rentfetch_rest_search_floorplans_safe( $request ) {
	try {
		return rentfetch_rest_search_floorplans( $request );
	} catch ( Throwable $exception ) {
		return rentfetch_get_search_failure_response( 'floorplans', $request, $exception );
	}
}

/**
 * REST API handler for property search
 *
 * @param WP_REST_Request $request The REST request object.
 * @return WP_REST_Response
 */
function rentfetch_rest_search_properties( $request ) {
	$can_read_markup_cache  = get_option( 'rentfetch_options_disable_query_caching', '1' ) !== '1';
	$can_write_markup_cache = $can_read_markup_cache && ( ! is_user_logged_in() || ! empty( $GLOBALS['rentfetch_force_cache_write'] ) );
	$request_params         = rentfetch_get_behavioral_search_request_params( $request, 'properties' );

	// Track this search for analytics (before checking cache), unless it's a cache warming request.
	if ( function_exists( 'rentfetch_track_search' ) ) {
		$skip_tracking = ! empty( $request_params['skip_tracking'] );
		rentfetch_track_search( 'properties', $request_params, $skip_tracking );
	}

	// Populate $_POST with request parameters so existing filter functions work
	// This allows all the filter hooks that check $_POST to function correctly.
	$_POST = array_merge( $_POST, $request_params );

	// Rebuild floorplan aggregates after request params are merged so property-card
	// pricing/availability reflects active floorplan filters (beds, baths, price, etc).
	if ( function_exists( 'rentfetch_set_floorplans' ) ) {
		rentfetch_set_floorplans();
	}

	$property_ids = rentfetch_get_property_ids_with_available_floorplans();
	if ( empty( $property_ids ) ) {
		$property_ids = array( '1' ); // if there aren't any properties, we shouldn't find anything.
	}

	// Get shortcode attributes from request parameters.
	$atts = array();
	if ( ! empty( $request_params['propertyids'] ) ) {
		$atts['propertyids'] = $request_params['propertyids'];
	}

	// Set maximum per page.
	$properties_maximum_per_page = get_option( 'rentfetch_options_maximum_number_of_properties_to_show' );
	if ( 0 === $properties_maximum_per_page ) {
		$properties_maximum_per_page = -1;
	}

	// Base property query.
	$property_args = array(
		'post_type'      => 'properties',
		'posts_per_page' => $properties_maximum_per_page,
		'no_found_rows'  => true,
		'post_status'    => 'publish',
	);

	$display_availability = get_option( 'rentfetch_options_property_availability_display' );
	if ( 'all' !== $display_availability ) {

		// If we have a propertyids attribute, use the intersection.
		if ( isset( $atts['propertyids'] ) ) {
			$property_ids = array_intersect( $property_ids, explode( ',', $atts['propertyids'] ) );
		}

		// If property_ids is empty after intersection, set to nonsense value to prevent matching everything.
		if ( empty( $property_ids ) ) {
			$property_ids = array( '0' );
		}

		// Add property IDs to the query.
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

		// If property_ids is empty, set to nonsense value to prevent matching everything.
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

	// Apply filters (this allows all the filter hooks to modify the query).
	$property_args = apply_filters( 'rentfetch_search_property_map_properties_query_args', $property_args );

	$floorplan_args_for_cache = array(
		'post_type'      => 'floorplans',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'post_status'    => 'publish',
	);
	$floorplan_args_for_cache = apply_filters( 'rentfetch_search_floorplans_query_args', $floorplan_args_for_cache );

	// Build cache key.
	$cache_key = 'rentfetch_propertysearch_markup_' . md5(
		wp_json_encode(
			array(
				'args'           => $property_args,
				'atts'           => $atts,
				'floorplan_args' => $floorplan_args_for_cache,
			)
		)
	);
	if ( $can_read_markup_cache ) {
		$cached_markup = rentfetch_get_cache_transient( $cache_key, $cache_is_stale );
		if ( false !== $cached_markup && is_array( $cached_markup ) ) {
			$background_refresh_scheduled = false;
			$cached_result_count          = rentfetch_count_markup_cache_results( $cache_key, $cached_markup );
			if ( $cache_is_stale && $can_write_markup_cache ) {
				$background_refresh_scheduled = true;
				rentfetch_refresh_cache_after_response(
					$cache_key,
					function () use ( $cache_key, $property_args ) {
						rentfetch_set_cache_transient( $cache_key, rentfetch_render_property_query_results_data( $property_args ) );
					}
				);
			}

			$response = rest_ensure_response(
				array(
					'html'        => $cached_markup['html'],
					'map_points'  => isset( $cached_markup['map_points'] ) && is_array( $cached_markup['map_points'] ) ? $cached_markup['map_points'] : array(),
					'count'       => null !== $cached_result_count ? $cached_result_count : substr_count( $cached_markup['html'], 'class="property' ),
					'cached'      => true,
					'stale'       => $cache_is_stale,
					'cache_debug' => rentfetch_get_cache_debug_metadata(
						$cache_key,
						'hit',
						$cache_is_stale,
						$background_refresh_scheduled,
						array(
							'lookup_attempted' => true,
							'read_enabled'     => $can_read_markup_cache,
							'write_enabled'    => $can_write_markup_cache,
						)
					),
				)
			);
			if ( $can_write_markup_cache ) {
				$response->header( 'Cache-Control', 'public, max-age=3600' );
			} else {
				$response->header( 'Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0' );
				$response->header( 'Pragma', 'no-cache' );
				$response->header( 'Expires', '0' );
			}
			return $response;
		}
	}

	// Render the results.
	$results_data = rentfetch_render_property_query_results_data( $property_args );
	$result_count = rentfetch_count_markup_cache_results( $cache_key, $results_data );

	// Cache the results.
	$cache_write_stored = null;
	if ( $can_write_markup_cache ) {
		$cache_write_stored = rentfetch_set_cache_transient( $cache_key, $results_data );
	}

	$response = rest_ensure_response(
		array(
			'html'        => $results_data['html'],
			'map_points'  => $results_data['map_points'],
			'count'       => null !== $result_count ? $result_count : substr_count( $results_data['html'], 'class="property' ),
			'cached'      => false,
			'stale'       => false,
			'cache_debug' => rentfetch_get_cache_debug_metadata(
				$cache_key,
				'miss',
				false,
				false,
				array(
					'lookup_attempted' => $can_read_markup_cache,
					'read_enabled'     => $can_read_markup_cache,
					'write_enabled'    => $can_write_markup_cache,
					'write_attempted'  => $can_write_markup_cache,
					'write_stored'     => $cache_write_stored,
				)
			),
		)
	);

	// Set cache headers if caching is enabled and this response is safe for shared caches.
	if ( $can_write_markup_cache ) {
		$response->header( 'Cache-Control', 'public, max-age=3600, s-maxage=3600' );
		$response->header( 'X-WP-Cacheable', 'yes' );
		$response->header( 'Pragma', 'public' );
		// Remove cache-busting headers that WordPress might add.
		$response->header( 'Expires', gmdate( 'D, d M Y H:i:s', time() + HOUR_IN_SECONDS ) . ' GMT' );
	} else {
		$response->header( 'Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0' );
		$response->header( 'Pragma', 'no-cache' );
		$response->header( 'Expires', '0' );
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
	$can_read_markup_cache  = get_option( 'rentfetch_options_disable_query_caching', '1' ) !== '1';
	$can_write_markup_cache = $can_read_markup_cache && ( ! is_user_logged_in() || ! empty( $GLOBALS['rentfetch_force_cache_write'] ) );
	$request_params         = rentfetch_get_behavioral_search_request_params( $request, 'floorplans' );

	// Track this search for analytics (before checking cache), unless it's a cache warming request.
	if ( function_exists( 'rentfetch_track_search' ) ) {
		$skip_tracking = ! empty( $request_params['skip_tracking'] );
		rentfetch_track_search( 'floorplans', $request_params, $skip_tracking );
	}

	// Populate $_POST with request parameters so existing filter functions work
	// This allows all the filter hooks that check $_POST to function correctly.
	$_POST = array_merge( $_POST, $request_params );

	// Get shortcode attributes from request parameters.
	$atts          = array();
	$possible_atts = array( 'property', 'propertyids', 'taxonomy', 'term' );
	foreach ( $possible_atts as $att ) {
		if ( ! empty( $request_params[ $att ] ) ) {
			$atts[ $att ] = $request_params[ $att ];
		}
	}

	// Base floorplan query.
	$floorplan_args = array(
		'post_type'      => 'floorplans',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	);

	// Apply filters (this allows all the filter hooks to modify the query).
	$floorplan_args = apply_filters( 'rentfetch_search_floorplans_query_args', $floorplan_args );

	// Build cache key.
	$cache_key = 'rentfetch_floorplansearch_markup_' . md5(
		wp_json_encode(
			array(
				'args' => $floorplan_args,
				'atts' => $atts,
			)
		)
	);
	if ( $can_read_markup_cache ) {
		$cached_markup = rentfetch_get_cache_transient( $cache_key, $cache_is_stale );
		if ( false !== $cached_markup && is_string( $cached_markup ) ) {
			$background_refresh_scheduled = false;
			$cached_result_count          = rentfetch_count_markup_cache_results( $cache_key, $cached_markup );
			if ( $cache_is_stale && $can_write_markup_cache ) {
				$background_refresh_scheduled = true;
				rentfetch_refresh_cache_after_response(
					$cache_key,
					function () use ( $cache_key, $floorplan_args ) {
						rentfetch_set_cache_transient( $cache_key, rentfetch_render_floorplan_query_results( $floorplan_args ) );
					}
				);
			}

			$response = rest_ensure_response(
				array(
					'html'        => $cached_markup,
					'count'       => null !== $cached_result_count ? $cached_result_count : substr_count( $cached_markup, 'class="floorplan' ),
					'cached'      => true,
					'stale'       => $cache_is_stale,
					'cache_debug' => rentfetch_get_cache_debug_metadata(
						$cache_key,
						'hit',
						$cache_is_stale,
						$background_refresh_scheduled,
						array(
							'lookup_attempted' => true,
							'read_enabled'     => $can_read_markup_cache,
							'write_enabled'    => $can_write_markup_cache,
						)
					),
				)
			);
			if ( $can_write_markup_cache ) {
				$response->header( 'Cache-Control', 'public, max-age=3600' );
			} else {
				$response->header( 'Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0' );
				$response->header( 'Pragma', 'no-cache' );
				$response->header( 'Expires', '0' );
			}
			return $response;
		}
	}

	// Render the results.
	$markup       = rentfetch_render_floorplan_query_results( $floorplan_args );
	$result_count = rentfetch_count_markup_cache_results( $cache_key, $markup );

	// Cache the results.
	$cache_write_stored = null;
	if ( $can_write_markup_cache ) {
		$cache_write_stored = rentfetch_set_cache_transient( $cache_key, $markup );
	}

	$response = rest_ensure_response(
		array(
			'html'        => $markup,
			'count'       => null !== $result_count ? $result_count : substr_count( $markup, 'class="floorplan' ),
			'cached'      => false,
			'stale'       => false,
			'cache_debug' => rentfetch_get_cache_debug_metadata(
				$cache_key,
				'miss',
				false,
				false,
				array(
					'lookup_attempted' => $can_read_markup_cache,
					'read_enabled'     => $can_read_markup_cache,
					'write_enabled'    => $can_write_markup_cache,
					'write_attempted'  => $can_write_markup_cache,
					'write_stored'     => $cache_write_stored,
				)
			),
		)
	);

	// Set cache headers if caching is enabled and this response is safe for shared caches.
	if ( $can_write_markup_cache ) {
		$response->header( 'Cache-Control', 'public, max-age=3600, s-maxage=3600' );
		$response->header( 'X-WP-Cacheable', 'yes' );
		$response->header( 'Pragma', 'public' );
		// Remove cache-busting headers that WordPress might add.
		$response->header( 'Expires', gmdate( 'D, d M Y H:i:s', time() + HOUR_IN_SECONDS ) . ' GMT' );
	} else {
		$response->header( 'Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0' );
		$response->header( 'Pragma', 'no-cache' );
		$response->header( 'Expires', '0' );
	}

	return $response;
}
