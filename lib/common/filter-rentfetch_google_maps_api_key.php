<?php

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