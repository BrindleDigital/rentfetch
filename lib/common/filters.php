<?php

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
        
    // get the desired labels from the settings
    $bedroom_numbers = get_option( 'options_bedroom_numbers' );
    
    if ( isset( $bedroom_numbers[ $beds . '_bedroom' ] ) )
        $newlabel = $bedroom_numbers[ $beds . '_bedroom' ];
        
    // if the setting is set, then update the label
    if ( !empty($newlabel) ) {
        $label = $newlabel;
    } else {
        
        // set defaults
        if ( $beds == '0' ) {
            $label = 'Studio';
        } else {
            $label = $beds . ' Bedroom';
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
        $label = '1 Bath';
    }  else {
        $label = $baths . ' Baths';
    }
    
    return $label;
    
}
add_filter( 'rentfetch_get_bathroom_number_label', 'rentfetch_bathroom_number_label' );

function rentfetch_square_feet_number_label( $number ) {
    if ( !$number || $number == null || $number == 0 )
        return null;
    
    return $number . ' sq. ft.';
}
add_filter( 'rentfetch_get_square_feet_number_label', 'rentfetch_square_feet_number_label' );

/**
 * Get the google maps API key
 * 
 * Allows for defining a constant in wp-config 
 * so that we don't have to update this each time 
 * we pull from prod and the local site isn't allowed 
 * by their API key
 */
add_filter( 'rentfetch_get_google_maps_api_key', 'rentfetch_google_maps_api_key', 10, 1 );
function rentfetch_google_maps_api_key( $key ) {
        
    // if there's a constant defined, use that
    if ( defined( 'RENTFETCH_GOOGLE_MAPS_API_KEY' ) )
        return RENTFETCH_GOOGLE_MAPS_API_KEY;
    
    // otherwise, just get the field the normal way
    $key = get_option( 'options_google_maps_api_key' );
    if ( $key )
        return $key;
        
    echo '3';
        
    return null;
    
}