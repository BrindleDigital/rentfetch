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
 * Get the tab from the URL.
 *
 * @return string $tab The tab from the URL.
 */
function rentfetch_settings_get_tab() {

	if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
		$referer = esc_url_raw( wp_unslash( $_REQUEST['_wp_http_referer'] ) );
		parse_str( wp_parse_url( $referer, PHP_URL_QUERY ), $query_params );
		$tab = isset( $query_params['tab'] ) ? sanitize_text_field( $query_params['tab'] ) : '';
	} else {
		$tab = null;
	}

	return $tab;
}

/**
 * Get the section from the URL.
 *
 * @return string $section The section from the URL.
 */
function rentfetch_settings_get_section() {

	if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
		$referer = esc_url_raw( wp_unslash( $_REQUEST['_wp_http_referer'] ) );
		parse_str( wp_parse_url( $referer, PHP_URL_QUERY ), $query_params );
		$section = isset( $query_params['section'] ) ? sanitize_text_field( $query_params['section'] ) : '';
	} else {
		$section = null;
	}

	return $section;
}
