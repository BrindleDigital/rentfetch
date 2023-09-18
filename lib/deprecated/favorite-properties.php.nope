<?php

add_shortcode( 'favoriteproperties', 'rentfetch_output_the_favorite_properties' );
function rentfetch_output_the_favorite_properties( $atts ) {
    
    $args = shortcode_atts( array(
        'foo' => 'something',
    ), $atts );

    ob_start();
    
    $favorite_properties = null;
    
    if( isset( $_COOKIE['favorite_properties'] ) ) 
        $favorite_properties = $_COOKIE['favorite_properties'];
        
    if ( empty( $favorite_properties ) ) {
        do_action( 'rentfetch_do_output_no_properties_default_message' );
    } else {        
        $favorite_properties = explode( ',', $favorite_properties );        
        do_action( 'rentfetch_do_output_favorite_properties', $favorite_properties );
    }
    
    return ob_get_clean();
    
}

add_action( 'rentfetch_do_output_favorite_properties', 'rentfetch_output_favorite_properties', 10, 1 );
function rentfetch_output_favorite_properties( $favorite_properties ) {
    
    //* Get the information we'll need from all floorplans
    // floorplan args
    $args = array(
        'post_type' => 'floorplans',
        'posts_per_page' => -1,
		'orderby' => 'date', // we will sort posts by date
		'order'	=> 'ASC', // ASC or DESC
        'no_found_rows' => true,
	);
    
    //* Process the floorplans
    $floorplans = rentfetch_get_floorplan_info_for_properties_grid( $args );
    
    $orderby = apply_filters( 'rentfetch_get_property_orderby', $orderby = 'menu_order' );
    $order = apply_filters( 'rentfetch_get_property_order', $order = 'ASC' );
    
    //* The base property query
    $propertyargs = array(
        'post_type'         => 'properties',
        'posts_per_page'    => -1,
		'orderby'           => $orderby,
		'order'	            => $order, // ASC or DESC
        'post__in'          => $favorite_properties,
        'no_found_rows'     => true,
	);
    
    $propertyquery = new WP_Query( $propertyargs );
    
    if( $propertyquery->have_posts() ) :
        echo '<div id="favorite-properties-grid">';
                
            echo '<div class="properties-loop">';
        
            while( $propertyquery->have_posts() ): $propertyquery->the_post();
                $property_id = get_post_meta( get_the_ID(), 'property_id', true );
                
                if ( isset( $floorplans[$property_id ] ) ) {
                    $floorplan = $floorplans[$property_id ];
                } else {
                    break;
                }
                                        
                do_action( 'rentfetch_do_each_property', $propertyquery->post->ID, $floorplan );
                
            endwhile;
        
            wp_reset_postdata();
            
        echo '</div></div>';
        
    else :
            
        // echo 'No properties found matching the current search parameters.';
        
    endif;
    
    
}

add_action( 'rentfetch_do_output_no_properties_default_message', 'rentfetch_output_no_properties_default_message' );
function rentfetch_output_no_properties_default_message() {
    ?>
    <p>Oops! Looks like you haven't set any favorite properties. Just click the heart icon to add as many properties as you'd like, and you'll see them show up here whenever you reload this page.</p>
    <?php
}


