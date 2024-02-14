<?php
/**
 * This file adds some of our filters.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Bedroom labels
 *
 * @param float $beds The number of bedrooms.
 *
 * @return string The label for the number of bedrooms.
 */
function rentfetch_bedroom_number_label( $beds ) {

	// set defaults.
	if ( 0 === $beds ) {
		$label = '<span class="label bedroom-label studio-label">Studio</span>';
	} elseif ( 1 === $beds ) {
		$label = '1 <span class="label bedroom-label">Bed</span>';
	} else {
		$label = $beds . ' <span class="label bedroom-label">Beds</span>';
	}

	// return the label.
	return $label;
}
add_filter( 'rentfetch_get_bedroom_number_label', 'rentfetch_bedroom_number_label' );

/**
 * Bathroom number labels
 *
 * @param   float $baths  The number of bathrooms.
 *
 * @return  string The label for the number of bathrooms.
 */
function rentfetch_bathroom_number_label( $baths ) {
	
	if ( 0 === $baths ) {
		$label = null;
	} elseif ( 1 === $baths || 1.0 === $baths ) {
		$label = '1 <span class="label bathroom-label">Bath</span>';
	} else {
		$label = $baths . ' <span class="label bathroom-label">Baths</span>';
	}

	return $label;
}
add_filter( 'rentfetch_get_bathroom_number_label', 'rentfetch_bathroom_number_label' );

/**
 * Square feet number labels
 *
 * @param int $number The number of square feet.
 *
 * @return string The label for the number of square feet.
 */
function rentfetch_square_feet_number_label( $number ) {
		
	if ( ! $number || null === $number || 0 === $number ) {
		return null;
	}

	return $number . ' <span class="label square-feet-label">sq. ft.</span>';
}
add_filter( 'rentfetch_get_square_feet_number_label', 'rentfetch_square_feet_number_label' );

/**
 * Available units labels
 *
 * @param int $number The number of available units.
 *
 * @return string The label for the number of available units.
 */
function rentfetch_available_units_label( $number ) {

	$number = intval( $number );

	if ( 0 === $number || empty( $number ) ) {
		$available = 'No units available';
	} elseif ( 1 === $number ) {
		$available = '1 unit available';
	} else {
		$available = $number . ' units available';
	}

	return $available;
}
add_filter( 'rentfetch_get_available_units_label', 'rentfetch_available_units_label' );

/**
 * Google Maps API Key
 *
 * @param string $key The Google Maps API key.
 *
 * @return string The Google Maps API key.
 */
function rentfetch_google_maps_api_key( $key ) {

	// if there's a constant defined, use that.
	if ( defined( 'RENTFETCH_GOOGLE_MAPS_API_KEY' ) ) {
		return RENTFETCH_GOOGLE_MAPS_API_KEY;
	}

	// otherwise, just get the field the normal way.
	$key = get_option( 'rentfetch_options_google_maps_api_key' );
	if ( $key ) {
		return $key;
	}

	return null;
}
add_filter( 'rentfetch_get_google_maps_api_key', 'rentfetch_google_maps_api_key', 10, 1 );
