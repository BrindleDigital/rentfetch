<?php

// [bartag foo="foo-value"]
add_shortcode( 'propertiesgrid', 'rentfetch_properties_grid' );
function rentfetch_properties_grid( $atts ) {
    
    ob_start();
    
    $orderby = apply_filters( 'rentfetch_get_property_orderby', $orderby = 'menu_order' );
    $order = apply_filters( 'rentfetch_get_property_order', $order = 'ASC' );
    
    $args = shortcode_atts( array(
        'neighborhood' => null,
        'posts_per_page' => 4,
        'orderby' => $orderby,
        'order' => $order,
    ), $atts );
    
    do_action( 'rentfetch_property_grid_shortcode', $args );

    return ob_get_clean();
}

add_action( 'rentfetch_property_grid_shortcode', 'rentfetch_add_properties_to_neighborhood_and_property_footer', 10, 1 );
add_action( 'rentfetch_single_properties_nearby_properties', 'rentfetch_add_properties_to_neighborhood_and_property_footer', 10, 1 );
add_action( 'genesis_after_content_sidebar_wrap', 'rentfetch_add_properties_to_neighborhood_and_property_footer', 10, 1 );
function rentfetch_add_properties_to_neighborhood_and_property_footer( $args ) {

    // bail if we don't have Metabox relationships installed
    if ( !class_exists( 'MB_Relationships_API' ) )
        return;
    
    // bail if we don't have any data and we're not on a page where this should pull in automatically
    if ( !is_singular( 'neighborhoods') && !is_singular( 'properties' ) && !isset( $args['neighborhood'] )  )
        return;
                                        
    // if this is a property, we need to find the connected neighborhoods to use later
    if ( is_singular( 'properties' ) ) {
                
        $neighborhoods = MB_Relationships_API::get_connected( [
            'id'   => 'properties_to_neighborhoods',
            'to' => get_the_ID(),
        ] );
        
        if ( !$neighborhoods )
            return;
                    
        $connected_neighborhoods = array();
        
        foreach ( $neighborhoods as $neighborhood ) {            
            $connected_neighborhoods[] = $neighborhood->ID;
        }        
    }
    
    // if this is a neighborhood, just grab the ID and make that the $connected_neighborhoods 
    if ( is_singular( 'neighborhoods' ) ) {
        $connected_neighborhoods = array( get_the_ID() );
    }
    
    // if this is the shortcode, we need to save the connected neighborhoods
    if ( isset( $args['neighborhood'] ) ) {
        
        // get the nieghborhoods
        $connected_neighborhoods = explode( ',', $args['neighborhood'] );
        
        // convert to int just in case
        $connected_neighborhoods = array_map( 'intval', $connected_neighborhoods );
        
    }
        
    // floorplan args
    $floorplans_args = array(
        'post_type' => 'floorplans',
        'posts_per_page' => -1,
		'orderby' => 'date', // we will sort posts by date
		'order'	=> 'ASC', // ASC or DESC
        'no_found_rows' => true,
        'meta_query' => array(
            array(
                'key' => 'available_units',
                'value' => 1,
                'type' => 'numeric',
                'compare' => '>=',
            )
        ),
	);
    
    //* Process the floorplans
    $floorplans = rentfetch_get_floorplan_info_for_properties_grid( $floorplans_args );
            
    $property_ids = array_keys( $floorplans );
    if ( empty( $property_ids ) )
    $property_ids = array( '1' ); // if there aren't any properties, we shouldn't find anything – empty array will let us find everything, so let's pass nonsense to make the search find nothing
    
    // echo '<pre style="font-size: 14px;">';
    // print_r( $property_ids );
    // echo '</pre>';
    
    $number_properties = '-1';
    
    $property_footer_settings = get_option( 'options_property_footer_grid', 'options' );
    if ( isset( $property_footer_settings['number_properties'] ) )
        $number_properties = $property_footer_settings['number_properties'];
        
    if ( isset( $args['posts_per_page'] ) )
        $number_properties = $args['posts_per_page'];
        
    // 1402,1404,1406,1407,1408,1409,1410,1411,1413,1414,1415,1416,1417,1418,1419
        
    //* The base property query
    $propertyargs = array(
        'post_type' => 'properties',
        'posts_per_page' => $number_properties,
		'orderby' => 'menu_order',
		'order'	=> 'DESC', // ASC or DESC
        'no_found_rows' => true,
        'relationship' => array(
            'id'   => 'properties_to_neighborhoods',
            'from' => $connected_neighborhoods, // You can pass object ID or full object
        ),
	);
    
    //* Add all of our property IDs into the property search
    $propertyargs['meta_query'] = array(
        array(
            'key' => 'property_id',
            'value' => $property_ids,
        ),
    );
    
    $propertyquery = new WP_Query( $propertyargs );
    
    // echo '<pre>';
    // print_r( $propertyquery );
    // echo '</pre>';
    
    $countposts = $propertyquery->post_count;
    
    // var_dump( $countposts );
    
    if ( $countposts < 2 )
        return;
    
    if( $propertyquery->have_posts() ) :
        
        echo '<div id="neighborhood-prefooter">';
                
            if ( is_singular( 'properties' ) )
                echo '<h2>Nearby properties</h2>';
        
            echo '<div class="properties-loop">';
        
                while( $propertyquery->have_posts() ): $propertyquery->the_post();
                    $property_id = get_post_meta( get_the_ID(), 'property_id', true );
                    $floorplan = $floorplans[$property_id ];
                    do_action( 'rentfetch_do_each_property', $propertyquery->post->ID, $floorplan );
                endwhile;
                
                echo '</div>';
                
                wp_reset_postdata();
                
        echo '</div>';
        
    else :
            
        // echo 'No properties found matching the current search parameters.';
        
    endif;
    
}


/**
 * Gets all of the floorplan information for all floorplans matching the $args, then format it
 *
 * @return  array  formatted information for all floorplans to be used in the properties grids
 */
function rentfetch_get_floorplan_info_for_properties_grid( $args ) {
    $query = new WP_Query( $args );

    // echo '<pre style="font-size: 14px;">';
    // print_r( $query->post );
    // echo '</pre>';
    
    // reset the floorplans array
    $floorplans = array();
    
    if( $query->have_posts() ) :
        
        // printf( '<div class="count"><h2 class="post-count"><span class="number">%s</span> results</h2><p>Note: Right now this is searching floorplans. Long-term, it will need to search the floorplans first, then do a secondary search of the associated properties.</p></div>', $numberofposts );
        
            while( $query->have_posts() ): $query->the_post();
            
                $id = get_the_ID();
                $property_id = get_post_meta( $id, 'property_id', true );
                $beds = get_post_meta( $id, 'beds', true );
                $baths = get_post_meta( $id, 'baths', true );
                $minimum_rent = get_post_meta( $id, 'minimum_rent', true );
                $maximum_rent = get_post_meta( $id, 'maximum_rent', true );
                $minimum_sqft = get_post_meta( $id, 'minimum_sqft', true );
                $maximum_sqft = get_post_meta( $id, 'maximum_sqft', true );
                $available_units = get_post_meta( $id, 'available_units', true );
                $has_specials = get_post_meta( $id, 'has_specials', true );
                
                if ( !isset( $floorplans[$property_id ] ) ) {
                    $floorplans[ $property_id ] = array(
                        'id' => array( $id ),
                        'beds' => array( $beds ),
                        'baths' => array( $baths ),
                        'minimum_rent' => array( $minimum_rent ),
                        'maximum_rent' => array( $maximum_rent ),
                        'minimum_sqft' => array( $minimum_sqft ),
                        'maximum_sqft' => array( $maximum_sqft ),
                        'available_units' => array( $available_units ),
                        'has_specials' => array( $has_specials ),
                    );
                } else {
                    $floorplans[ $property_id ]['id'][] = $id;
                    $floorplans[ $property_id ]['beds'][] = $beds;
                    $floorplans[ $property_id ]['baths'][] = $baths;
                    $floorplans[ $property_id ]['minimum_rent'][] = $minimum_rent;
                    $floorplans[ $property_id ]['maximum_rent'][] = $maximum_rent;
                    $floorplans[ $property_id ]['minimum_sqft'][] = $minimum_sqft;
                    $floorplans[ $property_id ]['maximum_sqft'][] = $maximum_sqft;
                    $floorplans[ $property_id ]['available_units'][] = $available_units;
                    $floorplans[ $property_id ]['has_specials'][] = $has_specials;
                }
                
            endwhile;
        
		wp_reset_postdata();
        
	endif;
    
    
    // echo '<pre style="font-size: 14px;">';
    // print_r( $floorplans );
    // echo '</pre>';
    
    foreach ( $floorplans as $key => $floorplan ) {
                
        $max = max( $floorplan['beds'] );
        $min = min( $floorplan['beds'] );
        
        if ( $max == $min ) {
            $floorplans[$key]['bedsrange'] = $max;
        } else {
            $floorplans[$key]['bedsrange'] = $min . '-' . $max;
        }
        
        $max = max( $floorplan['baths'] );
        $min = min( $floorplan['baths'] );
        
        if ( $max == $min ) {
            $floorplans[$key]['bathsrange'] = $max;
        } else {
            $floorplans[$key]['bathsrange'] = $min . '-' . $max;
        }
                
        $floorplan['maximum_rent'] = array_filter( $floorplan['maximum_rent'], 'rentfetch_check_if_above_100' );
        $floorplan['minimum_rent'] = array_filter( $floorplan['minimum_rent'], 'rentfetch_check_if_above_100' );
                        
        if ( !empty( $floorplan['maximum_rent'] ) ) {
            $max = max( $floorplan['maximum_rent'] );
        } else {
            $max = 0;
        }
        
        if ( !empty( $floorplan['minimum_rent'] ) ) {
            $min = min( $floorplan['minimum_rent'] );
        } else {
            $min = 0;
        }
        
        if ( $max == $min ) {
            $floorplans[$key]['rentrange'] = '$' . $max;
        } else {
            $floorplans[$key]['rentrange'] = '$' . $min . '-' . $max;
        }
        
        if ( $min < 100 || $max < 100 )
            $floorplans[$key]['rentrange'] = apply_filters( 'rentfetch_floorplan_pricing_unavailable_text', 'Pricing unavailable' );
        
        $max = max( $floorplan['maximum_sqft'] );
        $min = min( $floorplan['minimum_sqft'] );
        
        if ( $max == $min ) {
            $floorplans[$key]['sqftrange'] = $max;
        } else {
            $floorplans[$key]['sqftrange'] = $min . '-' . $max;
        }
        
        // default value
        $floorplans[$key]['property_has_specials'] = false;
        
        // if there are specials, save that
        $has_specials = $floorplan['has_specials'];
        
        if ( in_array( true, $has_specials ) )        
            $floorplans[$key]['property_has_specials'] = true;
                        
    }
    
    
    
    return $floorplans;
}