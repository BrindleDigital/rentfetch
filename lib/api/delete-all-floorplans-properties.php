<?php

/**
 * Delete all posts with a data source
 */
add_action( 'rentfetch_do_delete', 'apartment_delete' );
function apartment_delete() {
    
    //* FLOORPLANS
    rentfetch_log( "Deleting all floorplans that have a third-party data source." );
        
    // do a query to check and see if a post already exists with this ID 
    $args = array(
        'post_type' => 'floorplans',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'floorplan_source',
                'compare' => 'EXISTS',
            )
        )
    );
    $query = new WP_Query($args);
    
    $matchingposts = $query->posts;
    foreach ($matchingposts as $matchingpost) {
        wp_delete_post( $matchingpost->ID, true );
    }
    
    //* PROPERTIES
    rentfetch_log( "Deleting all properties that have a third-party data source." );
    
    // do a query to check and see if a post already exists with this ID 
    $args = array(
        'post_type' => 'properties',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'property_source',
                'compare' => 'EXISTS',
            )
        )
    );
    $query = new WP_Query($args);
    
    $matchingposts = $query->posts;
    foreach ($matchingposts as $matchingpost) {
        wp_delete_post( $matchingpost->ID, true );
    }
    
}