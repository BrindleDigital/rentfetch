<?php

function rentfetch_single_properties_floorplans_each_default() {
    
    echo '<div><strong>';
        the_title();
    echo '</strong></div>';
    
    $available_units = get_post_meta( get_the_ID(), 'available_units', true );
    
    printf( '%s Available', $available_units );
    
    $floorplan_id = get_post_meta( get_the_ID(), 'floorplan_id', true );
    $property_id = get_post_meta( get_the_ID(), 'property_id', true );
    
    $args = array(
        'post_type' => 'units',
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key'   => 'property_id',
                'value' => $property_id,
            ),
            array(
                'key'   => 'floorplan_id',
                'value' => $floorplan_id,
            ),
        ),
    );
    
    // The Query
    $units_query = new WP_Query( $args );

    // The Loop
    if ( $units_query->have_posts() ) {

        while ( $units_query->have_posts() ) {
            
            $units_query->the_post();
            
                echo '<div>';
                    the_title();
                echo '</div>';

        }
        
        // Restore postdata
        wp_reset_postdata();

    }

}
add_action( 'rentfetch_single_properties_do_floorplans_each', 'rentfetch_single_properties_floorplans_each_default' );