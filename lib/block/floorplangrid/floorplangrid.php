<?php

//* Register the block
add_action('acf/init', 'rentfetch_floorplangrid_block_register_block');
function rentfetch_floorplangrid_block_register_block() {

    // Check function exists.
    if( function_exists( 'acf_register_block_type') ) {

        // register a testimonial block.
        acf_register_block_type(array(
            'name'              => 'floorplangrid',
            'title'             => __('Floorplan grid'),
            'description'       => __('A block to show a floorplan grid with simple filters'),
            'render_callback'   => 'rentfetch_floorplangrid_block_render',
            'enqueue_assets'    => 'rentfetch_floorplangrid_block_enqueue',
            'category'          => 'formatting',
            'icon'              => 'table-col-before',
            'keywords'          => array( 'floorplan', 'apartment', 'grid', 'availability', 'rent', 'fetch' ),
            'mode'              => 'preview',
            'align'              => 'normal',
            'supports'          => array(
                'align' => array( 'full', 'wide', 'normal' ),
                'mode' => false,
                'jsx' => true
            ),
        ));
    }
}

//* Enqueues
function rentfetch_floorplangrid_block_enqueue() {
        
    // Fancybox
    wp_enqueue_style( 'rentfetch-fancybox-style', RENTFETCH_PATH . 'vendor/fancybox/jquery.fancybox.min.css', array(), RENTFETCH_VERSION, 'screen' );
    wp_enqueue_script( 'rentfetch-fancybox-script', RENTFETCH_PATH . 'vendor/fancybox/jquery.fancybox.min.js', array( 'jquery' ), RENTFETCH_VERSION, true );
    
    // Specific scripts and styles
    wp_enqueue_script( 'rentfetch-filters', RENTFETCH_PATH . 'lib/block/floorplangrid/js/filters.js', array( 'jquery' ), RENTFETCH_VERSION, true );
    
    // Slick
    wp_enqueue_script( 'rentfetch-slick-main-script', RENTFETCH_PATH . 'vendor/slick/slick.min.js', array('jquery'), RENTFETCH_VERSION, true );
    wp_enqueue_script( 'rentfetch-floorplan-images-slider-init', RENTFETCH_PATH . 'js/rentfetch-floorplan-images-slider-init.js', array( 'rentfetch-slick-main-script' ), RENTFETCH_VERSION, true );
    wp_enqueue_style( 'rentfetch-slick-main-styles', RENTFETCH_PATH . 'vendor/slick/slick.css', array(), RENTFETCH_VERSION );
    wp_enqueue_style( 'rentfetch-slick-main-theme', RENTFETCH_PATH . 'vendor/slick/slick-theme.css', array(), RENTFETCH_VERSION );
        
}

function rentfetch_floorplangrid_block_render( $block, $content = '', $is_preview = false, $post_id = 0 ) {
        
    //* Default class
    $className = 'floorplangrid';
    
    //* Default ID
    $id = 'floorplangrid-' . $block['id'];
    
    //* Get settings and put them in an object so that we can use them elsewhere
    $settings = array(
        'columns' => get_option( 'options_columns' ),
        'floorplan_filter' => get_option( 'options_floorplan_filter' ),
        'floorplan_limit' => get_option( 'options_floorplan_limit' ),
        'limit_number_of_bedrooms' => get_option( 'options_limit_number_of_bedrooms' ),
        'limit_floorplan_type' => get_option( 'options_limit_floorplan_type' ),
        'maximum_number_of_floorplans_to_show' => get_option( 'options_maximum_number_of_floorplans_to_show' ),
        'display_floorplans_from_specific_properties' => get_option( 'options_display_floorplans_from_specific_properties' ),
    );

    // Create id attribute allowing for custom "anchor" value.
    if( !empty($block['anchor']) ) 
        $id = $block['anchor'];

    // Create class attribute allowing for custom "className" and "align" values.
    if( !empty($block['className']) )
        $className .= ' ' . $block['className'];

    if( !empty($block['align']) )
        $className .= ' align' . $block['align'];
        
                            
    //* Render
    printf( '<div id="%s" class="%s">', $id, $className );

       rentfetch_floorplangrid_output_gform( $settings );
       rentfetch_floorplangrid_block_show_filter( $settings );
        
        // The Query
        $floorplans_query = rentfetch_floorplangrid_block_get_posts( $settings );
        
        // var_dump( $floorplans_query );
        
        if ( $floorplans_query->have_posts() ) {
            
            printf( '<div class="floorplangrid-wrap columns-%s">', $settings['columns'] );
            
            while ( $floorplans_query->have_posts() ) {
                $floorplans_query->the_post();
                
                    do_action( 'rentfetch_do_floorplan_in_floorplans_block', get_the_ID() );
                
            }
            
            echo '</div>'; // .floorplangrid-wrap
        } else {
            echo 'No floorplans found using available criteria.';
        }
                
    echo '</div>';    
   
}

/**
 * Render each floorplan
 *
 * @param   string  $post_id 
 *
 * @return  none
 */
add_action( 'rentfetch_do_floorplan_in_floorplans_block', 'rentfetch_floorplangrid_render_each_floorplan', 10, 1 );
function rentfetch_floorplangrid_render_each_floorplan( $post_id ) {
    
    //* Grab the data
    $title = get_the_title( $post_id );
    $available_units = get_option( 'options_available_units', $post_id );
    $number_of_baths = get_option( 'options_baths', $post_id );
    $number_of_beds = get_option( 'options_beds', $post_id );
        
    //* Set up the classes
    $floorplanclass = get_post_class( $post_id );
    
    if ( is_admin() )
        $floorplanclass[] = 'is-admin';
    
    if ( $available_units > 0 ) {
        $floorplanclass[] = 'units-available';
    } else {
        $floorplanclass[] = 'no-units-available';  
    } 
    
    if ( $number_of_beds !== null )
        $floorplanclass[] = sprintf( 'beds-%s', $number_of_beds );
        
    if ( $number_of_baths !== null )
        $floorplanclass[] = sprintf( 'baths-%s', $number_of_baths );
        
    $floorplanclass = implode( ' ', $floorplanclass );

    //* Do the markup
    printf( '<div class="%s">', $floorplanclass );
        echo '<div class="floorplan-inner">';
        
            if ( !is_admin())
                do_action( 'rentfetch_do_each_floorplan_images' );
                
            echo '<div class="floorplangrid__content">';
            
                if ( $title )
                    printf( '<h3 class="floorplangrid__title">%s</h3>', $title );
                                    
                do_action( 'rentfetch_do_each_floorplan_availability' );
                
                do_action( 'rentfetch_do_each_floorplan_description' );
                
                do_action( 'rentfetch_floorplan_in_archive_do_show_specials' );
                    
                echo '<p class="floorplangrid__info">';
                
                    do_action( 'rentfetch_do_each_floorplan_beds' );
                    
                    
                    do_action( 'rentfetch_do_each_floorplan_baths' ); 
                    
                    do_action( 'rentfetch_do_each_floorplan_squarefoot_range' );
                                                                
                echo '</p>';
                
                echo '<div class="floorplangrid__rent_range">';
            
                    do_action( 'rentfetch_do_each_floorplan_rent_range' );
                    
                echo '</div>'; // .floorplan-rent-range
                
                do_action( 'rentfetch_do_each_floorplan_show_property_label' );
                
                echo '<div class="buttons">';
                    
                    do_action( 'rentfetch_do_each_floorplan_buttons' );
                    
                echo '</div>';
                
                
                
                edit_post_link( 'Edit', '', '', $post_id );
                
            echo '</div>';  
            
          echo '</div>'; // .floorplan-inner  
        
    echo '</div>';
    
}

add_action( 'rentfetch_do_each_floorplan_show_property_label', 'rentfetch_each_floorplan_show_property_label' );
function rentfetch_each_floorplan_show_property_label() {
    
    $display_floorplans_from_specific_properties = get_option( 'options_display_floorplans_from_specific_properties' );
    
    // bail if we're not limiting by property
    if ( empty( $display_floorplans_from_specific_properties ) )
        return;
        
    // bail if there's less than two properties set
    if ( count( $display_floorplans_from_specific_properties ) < 2 )
        return;
        
    global $post;
    $id = get_the_ID();
        
    $property_id = get_post_meta( $id, 'property_id', true );
    
    $get_property_args = array(
        'post_type' => 'properties',
        'meta_query' => array(
            array(
                'key' => 'property_id',
                'value' => $property_id,
            ),
        ),
    );
    
    $properties = get_posts( $get_property_args );
    if ( $properties ) {
        echo '<p class="properties">';
        foreach ( $properties as $property ) {
            $the_id = $property->ID;
            $title = get_the_title( $the_id );
            
            if ( $title )
                printf( '<span class="associated-property">%s</span>', $title );
        }
        echo '</p>';
    }        
}

function rentfetch_floorplansgrid_customize_beds_text( $beds ) {
    if ( $beds == 0 )
        return 'Studio';
        
    if ( $beds == 1 )
        return '1 Bedroom';
        
    return $beds . ' Bedrooms';
}

//* Output the gravity form if needed
function rentfetch_floorplangrid_output_gform() {    
    
    // get the options
    $contact_button = get_option( 'options_contact_button' ); 
            
    $enabled = get_option( 'options_contact_button_enabled' );
        
    $gravity_form_id = get_option( 'options_contact_button_gravity_form_id' );
        
    //* bail if this button isn't enabled
    if ( $enabled !== true )
        return;
    
    //* bail if there's no gravity form ID specified
    if ( !$gravity_form_id )
        return;
    
    printf( '<div style="display:none;" id="gform-%s" class="rentfetch-gform">', $gravity_form_id );
        echo do_shortcode( '[gravityform id="' . $gravity_form_id . '" title=false description=false ajax=true tabindex=49]');
    echo '</div>';
    
}

//* Add the filters
function rentfetch_floorplangrid_block_show_filter( $settings ) {
    
    // bail if we aren't filtering
    if ( !$settings['floorplan_filter' ] )
        return;
        
    wp_enqueue_script( 'rentfetch-filters' );
        
    if ( $settings['floorplan_filter'] == 'bedrooms' )
        rentfetch_floorplangrid_block_show_filter_bedrooms( $settings );
    
}

// if we're filtering by bedroom...
function rentfetch_floorplangrid_block_show_filter_bedrooms( $settings ) {
    
    $floorplans_filter_query = rentfetch_floorplangrid_block_get_posts( $settings );
    $meta_values = array();
    
    if ( $floorplans_filter_query->have_posts() ) {
        while ( $floorplans_filter_query->have_posts() ) {
            $floorplans_filter_query->the_post();
            
            $meta_values[] = get_post_meta( get_the_ID(), 'beds', true );
        }
    }
    
    wp_reset_query();
    
    $bedrooms = array_count_values( $meta_values );
    
    ksort( $bedrooms );
    $bedroomnumbers = array_keys( $bedrooms );
        
    echo '<ul class="filters">';
        printf( '<li><a data-filter="%s" class="active filter-select" href="#">%s</a></li>', 'floorplans', 'All' );
        
        foreach ( $bedroomnumbers as $bedroomnumber ) {
            
            $label = apply_filters( 'rentfetch_get_bedroom_number_label', $label = null, $bedroomnumber );
            printf( '<li><a data-filter="beds-%s" class="filter-select" href="#">%s</a></li>', $bedroomnumber, $label );
        }
    echo '</ul>';
}

//* We do the query to get the posts then return the results
function rentfetch_floorplangrid_block_get_posts( $settings ) {
        
    // limits
    $limit_number_of_bedrooms = $settings['limit_number_of_bedrooms'];
    $limit_floorplan_type = $settings['limit_floorplan_type'];
    $maximum_number_of_floorplans_to_show = $settings['maximum_number_of_floorplans_to_show'];
    $display_floorplans_from_specific_properties = $settings['display_floorplans_from_specific_properties'];
        
    $args = array(
        'post_type' => 'floorplans',
        'posts_per_page' => $maximum_number_of_floorplans_to_show,
    );
    
    if ( $limit_number_of_bedrooms ) {
        $args['meta_query'][] = array(
            'key'     => 'beds',
            'value'   => $limit_number_of_bedrooms,
        );
    }
    
    //* if we're only getting floorplans from specific properties
    if ( $display_floorplans_from_specific_properties ) {
        
        $property_ids = array();
    
        foreach ( $display_floorplans_from_specific_properties as $property ) {
            $property_id = get_post_meta( $property, 'property_id', true );
            $property_ids[] = $property_id;
        }
                
        $args['meta_query'][] = array(
            array(
                'key' => 'property_id',
                'value' => $property_ids,
            ),
        );
    }
    
    //* limit by taxonomy term
    if ( $settings['floorplan_limit'] == 'floorplantype' ) {
        if ( $limit_floorplan_type ) { 
                        
            $arr = array(
                'tax_query' => array(
                    'relation' => 'OR',
                    array(
                        'taxonomy' => 'floorplantype',
                        'field'    => 'term_id',
                        'terms'    => array()
                    ),
                )
            );
                
            foreach ( $limit_floorplan_type as $limit_tax ) {
                $arr['tax_query']['0']['terms'][] = $limit_tax;
            }
            
            $args = array_merge( $args, $arr );
            
        }
    }
    
    // echo '<pre>';
    // print_r( $args );
    // echo '</pre>';
    
    $floorplans = new WP_Query( $args );
        
    return $floorplans;
    
}

//todo probably deprecate this
function rentfetch_floorplangrid_number_of_bedrooms_label( $numberofbeds, $settings ) {
     
    $string = sprintf( '%s_bedrooms_label', $numberofbeds );
    
    if ( !empty( $settings[$string] ) ) {
        $bedslabel = $settings[$string];
    } elseif ( !$numberofbeds ){
        $bedslabel = 'Studio';
    } else {
        $bedslabel = sprintf( '%s Bedroom', $numberofbeds );
    }    
            
    return $bedslabel;
} 