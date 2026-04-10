<?php
/**
 * This file is functions to get the section and tab from the URL.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get valid settings tabs and sections.
 *
 * @return array
 */
function rentfetch_settings_get_valid_routes() {
	return array(
		'general'    => array( 'data-sync', 'performance', 'analytics' ),
		'properties' => array( 'property-maps', 'property-search', 'property-archives', 'property-single', 'global-property-fees', 'property-settings-embed' ),
		'floorplans' => array( 'floorplan-search', 'floorplan-display', 'floorplan-buttons', 'floorplan-embed' ),
		'labels'     => array( '' ),
	);
}

/**
 * Sanitize a routing parameter from a request/query array.
 *
 * @param mixed $value The raw parameter value.
 * @return string
 */
function rentfetch_settings_sanitize_route_param( $value ) {
	if ( is_array( $value ) ) {
		return '';
	}

	return sanitize_text_field( wp_unslash( (string) $value ) );
}

/**
 * Normalize settings tab and section parameters to a route the UI can render and save.
 *
 * @param array $query_params Query parameters from the current URL or referer.
 * @return array
 */
function rentfetch_settings_normalize_route( $query_params = array() ) {
	$tab     = isset( $query_params['tab'] ) ? rentfetch_settings_sanitize_route_param( $query_params['tab'] ) : 'general';
	$section = isset( $query_params['section'] ) ? rentfetch_settings_sanitize_route_param( $query_params['section'] ) : '';

	$legacy_tabs = array(
		'property-search'          => array(
			'tab'     => 'properties',
			'section' => 'property-search',
		),
		'property-archives'        => array(
			'tab'     => 'properties',
			'section' => 'property-archives',
		),
		'single-property-template' => array(
			'tab'     => 'properties',
			'section' => 'property-single',
		),
	);

	if ( isset( $legacy_tabs[ $tab ] ) ) {
		return $legacy_tabs[ $tab ];
	}

	$valid_routes = rentfetch_settings_get_valid_routes();

	if ( ! isset( $valid_routes[ $tab ] ) ) {
		$tab = 'general';
	}

	if ( ! in_array( $section, $valid_routes[ $tab ], true ) ) {
		$section = $valid_routes[ $tab ][0];
	}

	return array(
		'tab'     => $tab,
		'section' => $section,
	);
}

/**
 * Get the normalized route from the current URL.
 *
 * @return array
 */
function rentfetch_settings_get_current_route() {
	return rentfetch_settings_normalize_route( $_GET );
}

/**
 * Get the normalized route from the submitted referer.
 *
 * @return array
 */
function rentfetch_settings_get_referer_route() {
	if ( ! isset( $_REQUEST['_wp_http_referer'] ) ) {
		return rentfetch_settings_normalize_route();
	}

	if ( is_array( $_REQUEST['_wp_http_referer'] ) ) {
		return rentfetch_settings_normalize_route();
	}

	$referer = esc_url_raw( wp_unslash( (string) $_REQUEST['_wp_http_referer'] ) );
	$query   = wp_parse_url( $referer, PHP_URL_QUERY );

	if ( ! is_string( $query ) ) {
		$query = '';
	}

	parse_str( $query, $query_params );

	return rentfetch_settings_normalize_route( $query_params );
}

/**
 * Get the tab from the URL.
 *
 * @return string $tab The tab from the URL.
 */
function rentfetch_settings_get_tab() {
	$route = rentfetch_settings_get_referer_route();

	return $route['tab'];
}

/**
 * Get the section from the URL.
 *
 * @return string $section The section from the URL.
 */
function rentfetch_settings_get_section() {
	$route = rentfetch_settings_get_referer_route();

	return $route['section'];
}
