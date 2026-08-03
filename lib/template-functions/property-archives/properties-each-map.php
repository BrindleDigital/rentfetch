<?php
/**
 * Display each property in the map
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Do the markup for each property in the map
 *
 * @return void.
 */
function rentfetch_properties_each_map() {

	$title      = rentfetch_get_property_title();
	$city_state = rentfetch_get_property_city_state();
	$specials   = rentfetch_get_property_specials_from_meta();

	if ( $city_state ) {
		printf( '<p class="city-state">%s</p>', esc_html( $city_state ) );
	}

	if ( $title ) {
		printf( '<h3>%s</h3>', esc_html( $title ) );
	}

	if ( $specials ) {
		printf( '<p class="specials">%s</p>', esc_html( $specials ) );
	}
}
add_action( 'rentfetch_do_properties_each_map', 'rentfetch_properties_each_map' );
