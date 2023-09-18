<?php

/**
 * Get the orderby of the properties
 */
add_filter( 'rentfetch_get_property_orderby', 'rentfetch_property_orderby', 10, 1 );
function rentfetch_property_orderby( $orderby ) {
    
    $orderby = get_option( 'options_property_orderby' );
    
    // default to menu_order if no selection made
    if ( !$orderby )
        return 'menu_order';
        
    return $orderby;
    
}

/**
 * Get the order of the properties
 */
add_filter( 'rentfetch_get_property_order', 'rentfetch_property_order', 10, 1 );
function rentfetch_property_order( $order ) {
    
    $order = get_option( 'options_property_order' );
    
    // default to menu_order if no selection made
    if ( !$order )
        return 'ASC';
        
    return $order;
    
}