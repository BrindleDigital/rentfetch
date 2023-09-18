<?php

add_filter( 'rentfetch_get_bedroom_number_label', 'rentfetch_do_single_properties', 10, 2 );
function rentfetch_do_single_properties( $label, $beds ) {
    
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
