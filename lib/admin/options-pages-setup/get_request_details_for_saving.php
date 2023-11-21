<?php

function rentfetch_settings_get_tab() {
	
	if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
		$referer = esc_url_raw( wp_unslash( $_REQUEST['_wp_http_referer'] ) );
		parse_str( parse_url( $referer, PHP_URL_QUERY ), $query_params );
		$tab = isset( $query_params['tab'] ) ? sanitize_text_field( $query_params['tab'] ) : '';
	} else {
		$tab = null;
	}
	
	return $tab;
}

function rentfetch_settings_get_section() {
	
	if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
		$referer = esc_url_raw( wp_unslash( $_REQUEST['_wp_http_referer'] ) );
		parse_str( parse_url( $referer, PHP_URL_QUERY ), $query_params );
		$section = isset( $query_params['section'] ) ? sanitize_text_field( $query_params['section'] ) : '';
	} else {
		$section = null;
	}
	
	return $section;
}