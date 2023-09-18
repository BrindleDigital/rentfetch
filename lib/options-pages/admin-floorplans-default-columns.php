<?php

add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_floorplans_admin_style' );
function rentfetch_enqueue_floorplans_admin_style() {
    
    // bail if admin columns pro is active, or admin columns is active, since our styles conflict with those plugins
    if ( is_plugin_active( 'admin-columns-pro/admin-columns-pro.php' ) || is_plugin_active( 'codepress-admin-columns/codepress-admin-columns.php' ) )
        return;
    
    $current_screen = get_current_screen();
  
    // Check if the current screen is the admin archive page of the floorplans content type
    if ( $current_screen->base === 'edit' && $current_screen->post_type === 'floorplans' ) {
        
        // Enqueue your custom admin style
        wp_enqueue_style( 'floorplans-edit-admin-style', RENTFETCH_PATH . 'css/admin/admin-edit-floorplans.css', array(), RENTFETCH_VERSION, 'screen' );
    }
}

add_filter( 'manage_floorplans_posts_columns', 'rentfetch_default_floorplans_admin_columns' );
function rentfetch_default_floorplans_admin_columns( $columns ) {
    
    $columns = array(
        'cb' =>                         '<input type="checkbox" />',
        'title' =>                      __( 'Title', 'rentfetch' ),
        'floorplan_source' =>           __( 'Floorplan Source', 'rentfetch' ),
        'property_id' =>                __( 'Property ID', 'rentfetch' ),
        'floorplan_id' =>               __( 'Floorplan ID', 'rentfetch' ),
        'unit_type_mapping' =>          __( 'Unit Type', 'rentfetch' ),
        'manual_images' =>              __( 'Manual Images', 'rentfetch' ),
        'floorplan_images' =>           __( 'Synced Images', 'rentfetch' ),
        'floorplan_description' =>      __( 'Floorplan Description', 'rentfetch' ),
        'floorplan_video_or_tour' =>    __( 'Video/Tour', 'rentfetch' ),
        'beds' =>                       __( 'Beds', 'rentfetch' ),
        'baths' =>                      __( 'Baths', 'rentfetch' ),
        'minimum_deposit' =>            __( 'Min Deposit', 'rentfetch' ),
        'maximum_deposit' =>            __( 'Max Deposit', 'rentfetch' ),
        'minimum_rent' =>               __( 'Min Rent', 'rentfetch' ),
        'maximum_rent' =>               __( 'Max Rent', 'rentfetch' ),
        'minimum_sqft' =>               __( 'Min Sqrft', 'rentfetch' ),
        'maximum_sqft' =>               __( 'Max Sqrft', 'rentfetch' ),
        'availability_date' =>          __( 'Availability Date', 'rentfetch' ),
        'property_show_specials' =>     __( 'Show Specials', 'rentfetch' ),
        'has_specials' =>               __( 'Has Specials', 'rentfetch' ),
        'availability_url' =>           __( 'Availability URL', 'rentfetch' ),
        'available_units' =>            __( 'Availiable Units', 'rentfetch' ),
    );
    
    return $columns;
    
}

add_action( 'manage_floorplans_posts_custom_column', 'rentfetch_floorplans_default_column_content', 10, 2);
function rentfetch_floorplans_default_column_content( $column, $post_id ) {
        
    if ( 'title' === $column )
        echo esc_attr( get_the_title( $post_id ) );
        
    if ( 'floorplan_source' === $column )
        echo esc_attr( get_post_meta( $post_id, 'floorplan_source', true ) );        
    
    if ( 'property_id' === $column )
        echo esc_attr( get_post_meta( $post_id, 'property_id', true ) );        
    
    if ( 'floorplan_id' === $column )
        echo esc_attr( get_post_meta( $post_id, 'floorplan_id', true ) );        
    
    if ( 'unit_type_mapping' === $column )
        echo esc_attr( get_post_meta( $post_id, 'unit_type_mapping', true ) );        
    
    // if ( 'manual_images' === $column )
    //     echo esc_attr( get_post_meta( $post_id, 'manual_images', true ) );   
        
    if ( 'manual_images' === $column ) {
        $images = get_post_meta( $post_id, 'manual_images', true );
        
        if ( is_array( $images ) ) {            
            foreach ( $images as $image ) {
                $image = wp_get_attachment_image_url( $image, 'thumbnail' );
                echo '<img src="' . esc_attr( $image ) . '" style="width: 40px; height: 40px;" />';
            }
        }
    }
    
    if ( 'floorplan_images' === $column )
        echo esc_attr( get_post_meta( $post_id, 'floorplan_images', true ) );        
    
    if ( 'floorplan_description' === $column )
        echo esc_attr( get_post_meta( $post_id, 'floorplan_description', true ) );        
    
    if ( 'floorplan_video_or_tour' === $column )
        echo esc_attr( get_post_meta( $post_id, 'floorplan_video_or_tour', true ) );        
    
    if ( 'beds' === $column )
        echo esc_attr( get_post_meta( $post_id, 'beds', true ) );        
    
    if ( 'baths' === $column )
        echo esc_attr( get_post_meta( $post_id, 'baths', true ) );        
    
    if ( 'minimum_deposit' === $column )
        echo esc_attr( get_post_meta( $post_id, 'minimum_deposit', true ) );        
    
    if ( 'maximum_deposit' === $column )
        echo esc_attr( get_post_meta( $post_id, 'maximum_deposit', true ) );        
    
    if ( 'minimum_rent' === $column )
        echo esc_attr( get_post_meta( $post_id, 'minimum_rent', true ) );        
    
    if ( 'maximum_rent' === $column )
        echo esc_attr( get_post_meta( $post_id, 'maximum_rent', true ) );        
    
    if ( 'minimum_sqft' === $column )
        echo esc_attr( get_post_meta( $post_id, 'minimum_sqft', true ) );        
    
    if ( 'maximum_sqft' === $column )
        echo esc_attr( get_post_meta( $post_id, 'maximum_sqft', true ) );        
    
    if ( 'availability_date' === $column )
        echo esc_attr( get_post_meta( $post_id, 'availability_date', true ) );        
    
    if ( 'property_show_specials' === $column ) {
        $property_show_specials = get_post_meta( $post_id, 'property_show_specials', true );
        
        if ( $property_show_specials ) {
            echo 'Yes';
        } else {
            echo 'No';
        }
    }
            
    if ( 'has_specials' === $column ) {
        $has_specials = get_post_meta( $post_id, 'has_specials', true );
        
        if ( $has_specials ) {
            echo 'Yes';
        } else {
            echo 'No';
        }
    }
        
    if ( 'availability_url' === $column )
        echo esc_attr( get_post_meta( $post_id, 'availability_url', true ) );        
    
    if ( 'available_units' === $column )
        echo esc_attr( get_post_meta( $post_id, 'available_units', true ) );        
    
}