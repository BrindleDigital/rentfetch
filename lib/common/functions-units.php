<?php

function rentfetch_get_floorplan_units() {
    $floorplan_wordpress_id = get_the_ID();
    $floorplan_id = get_post_meta( $floorplan_wordpress_id, 'floorplan_id', true );
    $property_id = get_post_meta( $floorplan_wordpress_id, 'property_id', true );

    $args = array(
        'post_type' => 'units',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'floorplan_id',
                'value' => $floorplan_id,
                'compare' => '=',
            ),
            array(
                'key' => 'property_id',
                'value' => $property_id,
                'compare' => '=',
            ),
        ),
    );

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        return $query->posts;
    } else {
        return false;
    }
}

function rentfetch_get_floorplan_units_count() {
    $floorplan_wordpress_id = get_the_ID();
    $available_units = get_post_meta( $floorplan_wordpress_id, 'available_units', true );
    return intval( $available_units );    
}