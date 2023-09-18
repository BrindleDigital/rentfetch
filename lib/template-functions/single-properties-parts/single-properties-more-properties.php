<?php

/**
 * Output the more properties section
 *
 */
function rentfetch_single_properties_parts_more_properties() {
    
    $maybe_do_more_properties = apply_filters( 'rentfetch_maybe_do_property_part_more_properties', true );    
    if ( $maybe_do_more_properties !== true )
        return;
        
    global $post;
    
    echo '<div id="moreproperties" class="single-properties-section">';
		echo '<div class="wrap">';
		
		echo '<h2>More Properties Nearby</h2>';
        
        $city = get_post_meta( get_the_ID(), 'city', true );
        
        $args = array(
            'post_type' => 'properties',
            'posts_per_page' => 9,
            'meta_key' => 'city',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key'   => 'city',
                    'value' => $city,
                ),
            ),
        );
        
        // The Query
        $custom_query = new WP_Query( $args );

        // The Loop
        if ( $custom_query->have_posts() ) {
            
            echo '<div class="properties-simple-grid">'; // .properties-loop

            while ( $custom_query->have_posts() ) {
                
                $custom_query->the_post();
                
                $class = implode( ' ', get_post_class() );
                
                printf( '<div class="%s">', $class );
                    do_action( 'rentfetch_do_each_property_in_archive' );
                echo '</div>';
            
            }
            
            echo '</div>';
            
            // Restore postdata
            wp_reset_postdata();

        } else {
            echo 'No properties found.';
        }
        
		
		echo '</div>'; // .wrap
	echo '</div>'; // #moreproperties
    
}


/**
 * Decide whether to output the more_properties section
 */
add_filter( 'rentfetch_maybe_do_property_part_more_properties', 'rentfetch_maybe_property_part_more_properties' );
function rentfetch_maybe_property_part_more_properties() {
    
    // bail if this section is not enabled
    $property_components = get_option( 'options_single_property_components' );
    if ( !is_array( $property_components ) || !in_array( 'nearby_properties', $property_components ) )
        return false;
        
    return true;
}

function rentfetch_single_properties_parts_subnav_more_properties() {
    $maybe_do_more_properties = apply_filters( 'rentfetch_maybe_do_property_part_more_properties', true );
    if ( $maybe_do_more_properties === true ) {
        $label = apply_filters( 'rentfetch_nearby_properties_subnav_label', 'Nearby Properties' );
        printf( '<li><a href="#moreproperties">%s</a></li>', $label );
    }
}