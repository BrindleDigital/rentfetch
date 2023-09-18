<?php

add_filter( 'rentfetch_get_baths_label', 'rentfetch_baths_number_label', 10, 1 );
function rentfetch_baths_number_label( $baths ) {
    
    // force the number of beds to be an integer in case it was passed as a string
    $baths = intval( $baths );
    
    if ( $baths === null )
        return;
    
    // set defaults
    if ( $baths == '0' || $baths == '1' ) {
        $label = ' Bath';
    } else {
        $label = ' Baths';
    }
        
    // return the label
    return $label;
    
}
