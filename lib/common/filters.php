<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * [rentfetch_do_single_properties description]
 *
 * @param   [type]  $label  [$label description]
 * @param   [type]  $beds   [$beds description]
 *
 * @return  [type]          [return description]
 */
function rentfetch_bedroom_number_label( $beds ) {
	
	// force the number of beds to be an integer in case it was passed as a string
	$beds = intval( $beds ); 
		
	//TODO add the settings back in for bedroom labels
	// // get the desired labels from the settings
	// $bedroom_numbers = get_option( 'rentfetch_options_bedroom_numbers' );
		
	// if ( isset( $bedroom_numbers[ $beds . '_bedroom' ] ) )
	// 	$newlabel = $bedroom_numbers[ $beds . '_bedroom' ];
		
	// if the setting is set, then update the label
	if ( !empty($newlabel) ) {
		$label = $newlabel;
	} else {
		
		// set defaults
		if ( $beds == '0' ) {
			$label = '<span class="label bedroom-label studio-label">Studio</span>';
		} elseif ( $beds == '1' ) {
			$label = '1 <span class="label bedroom-label">Bedroom</span>';
		} else {
			$label = $beds . ' <span class="label bedroom-label">Bedrooms</span>';
		}
	}
			
	// return the label
	return $label;
	
}
add_filter( 'rentfetch_get_bedroom_number_label', 'rentfetch_bedroom_number_label' );

/**
 * [rentfetch_bathroom_number_label description]
 *
 * @param   [type]  $label  [$label description]
 * @param   [type]  $baths  [$baths description]
 *
 * @return  [type]          [return description]
 */
function rentfetch_bathroom_number_label( $baths ) {
	
	if ( $baths == 0 ) {
		$label = null;
	} elseif ( $baths == 1 ) {
		$label = '1 <span class="label bathroom-label">Bath</span>';
	}  else {
		$label = $baths . ' <span class="label bathroom-label">Baths</span>';
	}
		
	return $label;
	
}
add_filter( 'rentfetch_get_bathroom_number_label', 'rentfetch_bathroom_number_label' );

function rentfetch_square_feet_number_label( $number ) {
	if ( !$number || $number == null || $number == 0 )
		return null;
	
	return $number . ' <span class="label square-feet-label">sq. ft.</span>';
}
add_filter( 'rentfetch_get_square_feet_number_label', 'rentfetch_square_feet_number_label' );

function rentfetch_available_units_label( $number ) {
	if ( $number == 0 || empty( $number ) ) {
		$available = 'No units available';
	} elseif ( $number == 1 ) {
		$available = '1 unit available';
	} else {
		$available = $number . ' units available';
	}
	
	return $available;
}
add_filter( 'rentfetch_get_available_units_label', 'rentfetch_available_units_label' );

/**
 * Retrieve and return the Google Maps API key
 *
 */
function rentfetch_google_maps_api_key( $key ) {
		
	// if there's a constant defined, use that
	if ( defined( 'RENTFETCH_GOOGLE_MAPS_API_KEY' ) )
		return RENTFETCH_GOOGLE_MAPS_API_KEY;
	
	// otherwise, just get the field the normal way
	$key = get_option( 'rentfetch_options_google_maps_api_key' );
	if ( $key )
		return $key;
				
	return null;
	
}
add_filter( 'rentfetch_get_google_maps_api_key', 'rentfetch_google_maps_api_key', 10, 1 );