<?php

// putting this on the init hook so that someone could remove it and readd it in whatever order they want
function rentfetch_search_properties_dialog_filters() {
    
    // check whether text-based search is enabled
    $options_dialog_filters = get_option( 'options_dialog_filters' );
        
    if ( !empty( $options_dialog_filters ) && in_array( 'text_based_search', $options_dialog_filters ) )
        add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_properties_map_filters_text_search' );
        
    if ( !empty( $options_dialog_filters ) && in_array( 'beds_search', $options_dialog_filters ) )
        add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_properties_map_filters_beds' );
        
    if ( !empty( $options_dialog_filters ) && in_array( 'baths_search', $options_dialog_filters ) )
        add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_properties_map_filters_baths' );
                
    if ( !empty( $options_dialog_filters ) && in_array( 'type_search', $options_dialog_filters ) )
        add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_properties_map_filters_property_types' );
        
    if ( !empty( $options_dialog_filters ) && in_array( 'date_search', $options_dialog_filters ) )
        add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_properties_map_filters_date' );
        
    if ( !empty( $options_dialog_filters ) && in_array( 'price_search', $options_dialog_filters ) )
        add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_properties_map_filters_price' );
        
    if ( !empty( $options_dialog_filters ) && in_array( 'amenities_search', $options_dialog_filters ) )
        add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_properties_map_filters_amenities' );
        
}
add_action( 'init', 'rentfetch_search_properties_dialog_filters' );

function rentfetch_search_properties_featured_filters() {
    
    // check whether text-based search is enabled
    $options_featured_filters = get_option( 'options_featured_filters' );
        
    if ( !empty( $options_featured_filters ) && in_array( 'text_based_search', $options_featured_filters ) )
        add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_properties_map_filters_text_search' );
        
    if ( !empty( $options_featured_filters ) && in_array( 'beds_search', $options_featured_filters ) )
        add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_properties_map_filters_beds' );
        
    if ( !empty( $options_featured_filters ) && in_array( 'baths_search', $options_featured_filters ) )
        add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_properties_map_filters_baths' );
                
    if ( !empty( $options_featured_filters ) && in_array( 'type_search', $options_featured_filters ) )
        add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_properties_map_filters_property_types' );
        
    if ( !empty( $options_featured_filters ) && in_array( 'date_search', $options_featured_filters ) )
        add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_properties_map_filters_date' );
        
    if ( !empty( $options_featured_filters ) && in_array( 'price_search', $options_featured_filters ) )
        add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_properties_map_filters_price' );
        
    if ( !empty( $options_featured_filters ) && in_array( 'amenities_search', $options_featured_filters ) )
        add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_properties_map_filters_amenities' );
        
}
add_action( 'init', 'rentfetch_search_properties_featured_filters' );