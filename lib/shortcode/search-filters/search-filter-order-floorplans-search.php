<?php

// putting this on the init hook so that someone could remove it and readd it in whatever order they want
function rentfetch_search_floorplans_filters() {
    
    // get the options for which filters are enabled
    $options_floorplan_filters = get_option( 'options_floorplan_filters' );
                    
    if ( !empty( $options_floorplan_filters ) && in_array( 'beds_search', $options_floorplan_filters ) )
        add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_beds' );
    
    if ( !empty( $options_floorplan_filters ) && in_array( 'baths_search', $options_floorplan_filters ) )
        add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_baths' );
    
    if ( !empty( $options_floorplan_filters ) && in_array( 'price_search', $options_floorplan_filters ) )
        add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_price' );
    
    if ( !empty( $options_floorplan_filters ) && in_array( 'date_search', $options_floorplan_filters ) )
        add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_date' );
    
    //TODO - add squarefoot search
    // if ( !empty( $options_floorplan_filters ) && in_array( 'squarefoot_search', $options_floorplan_filters ) )
    //     add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_squarefoot' );
    
    //TODO - add sort for the floorplans
    // if ( !empty( $options_floorplan_filters ) && in_array( 'sort', $options_floorplan_filters ) )
    //     add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_floorplans_filters_sort' );
        
        
}
add_action( 'init', 'rentfetch_search_floorplans_filters' );