<?php

add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_properties_admin_style' );
function rentfetch_enqueue_properties_admin_style() {
    
    // bail if admin columns pro is active, or admin columns is active, since our styles conflict with those plugins
    if ( is_plugin_active( 'admin-columns-pro/admin-columns-pro.php' ) || is_plugin_active( 'codepress-admin-columns/codepress-admin-columns.php' ) )
        return;
    
    $current_screen = get_current_screen();
  
    // Check if the current screen is the admin archive page of the properties content type
    if ( $current_screen->base === 'edit' && $current_screen->post_type === 'properties' ) {
        
        // Enqueue your custom admin style
        wp_enqueue_style( 'properties-edit-admin-style', RENTFETCH_PATH . 'css/admin/admin-edit-properties.css', array(), RENTFETCH_VERSION, 'screen' );
    }
}

add_filter( 'manage_properties_posts_columns', 'rentfetch_default_properties_admin_columns' );
function rentfetch_default_properties_admin_columns( $columns ) {
    
    $columns = array(
        'cb' =>              '<input type="checkbox" />',
        'title' =>           __( 'Title', 'rentfetch' ),
        'property_id' =>     __( 'Property ID', 'rentfetch' ),
        // 'property_code' =>   __( 'Property Code', 'rentfetch' ),
        'address' =>         __( 'Address', 'rentfetch' ),
        'city' =>            __( 'City', 'rentfetch' ),
        'state' =>           __( 'State', 'rentfetch' ),
        'zipcode' =>         __( 'Zipcode', 'rentfetch' ),
        'latitude' =>        __( 'Latitude', 'rentfetch' ),
        'longitude' =>       __( 'Longitude', 'rentfetch' ),
        'email' =>           __( 'Email', 'rentfetch' ),
        'phone' =>           __( 'Phone', 'rentfetch' ),
        'url' =>             __( 'URL', 'rentfetch' ),
        'images' =>          __( 'Images', 'rentfetch' ),
        'description' =>     __( 'Description', 'rentfetch' ),
        'matterport' =>      __( 'Matterport', 'rentfetch' ),
        'video' =>           __( 'Video', 'rentfetch' ),
        'pets' =>            __( 'Pets', 'rentfetch' ),
        'content_area' =>    __( 'Content Area', 'rentfetch' ),
        'yardi_property_images' => __( 'Images (Yardi)', 'rentfetch' ),
        'property_source' => __( 'Property Source', 'rentfetch' ),
        'updated' =>         __( 'Last API update', 'rentfetch' ),
        'api_error' =>         __( 'API response', 'rentfetch' ),
    );
    
    return $columns;
    
}

add_action( 'manage_properties_posts_custom_column', 'rentfetch_properties_default_column_content', 10, 2);
function rentfetch_properties_default_column_content( $column, $post_id ) {
        
    if ( 'title' === $column )
        echo esc_attr( get_the_title( $post_id ) );
        
    if ( 'property_id' === $column )
        echo esc_attr( get_post_meta( $post_id, 'property_id', true ) );
        
    if ( 'property_code' === $column )
        echo esc_attr( get_post_meta( $post_id, 'property_code', true ) );
        
    if ( 'address' === $column )
        echo esc_attr( get_post_meta( $post_id, 'address', true ) );
        
    if ( 'city' === $column )
        echo esc_attr( get_post_meta( $post_id, 'city', true ) );
        
    if ( 'state' === $column )
        echo esc_attr( get_post_meta( $post_id, 'state', true ) );
        
    if ( 'zipcode' === $column )
        echo esc_attr( get_post_meta( $post_id, 'zipcode', true ) );
        
    if ( 'latitude' === $column )
        echo esc_attr( get_post_meta( $post_id, 'latitude', true ) );
        
    if ( 'longitude' === $column )
        echo esc_attr( get_post_meta( $post_id, 'longitude', true ) );
        
    if ( 'email' === $column )
        echo esc_attr( get_post_meta( $post_id, 'email', true ) );
        
    if ( 'phone' === $column )
        echo esc_attr( get_post_meta( $post_id, 'phone', true ) );
        
    if ( 'url' === $column )
        printf( '<a target="_blank" href="%s">%s</a>', esc_url( get_post_meta( $post_id, 'url', true ) ), esc_attr( get_post_meta( $post_id, 'url', true ) ) );
        
    if ( 'images' === $column ) {
        $images = get_post_meta( $post_id, 'images', true );
        
        if ( is_array( $images ) ) {            
            foreach ( $images as $image ) {
                $image = wp_get_attachment_image_url( $image, 'thumbnail' );
                echo '<img src="' . esc_attr( $image ) . '" style="width: 40px; height: 40px;" />';
            }
        }
    }
        
    if ( 'property_source' === $column )
        echo esc_attr( get_post_meta( $post_id, 'property_source', true ) );
        
    if ( 'description' === $column )
        echo esc_attr( get_post_meta( $post_id, 'description', true ) );
        
    if ( 'matterport' === $column )
        echo esc_attr( get_post_meta( $post_id, 'matterport', true ) );
        
    if ( 'video' === $column )
        echo esc_attr( get_post_meta( $post_id, 'video', true ) );
        
    if ( 'pets' === $column )
        echo esc_attr( get_post_meta( $post_id, 'pets', true ) );
        
    if ( 'content_area' === $column )
        echo esc_attr( get_post_meta( $post_id, 'content_area', true ) );
        
    if ( 'yardi_property_images' === $column )
        echo esc_attr( get_post_meta( $post_id, 'yardi_property_images', true ) );
    
    if ( 'updated' === $column )
        echo esc_attr( get_post_meta( $post_id, 'updated', true ) );
        
    if ( 'api_error' === $column )
        echo esc_attr( get_post_meta( $post_id, 'api_error', true ) );
    
    if ( 'attraction_type' === $column ) {
        $terms = get_the_terms( $post_id, 'attractiontypes' );
        $count = 0;
        
        if ( $terms ) {
            foreach( $terms as $term ) {
                if ( $count != 0 )
                    echo ', ';
                    
                echo $term->name;
                $count++;
            }
        }            
    }
        
    if ( 'na_attractions_always_show' === $column ) {
        $always_show = get_post_meta( $post_id, 'na_attractions_always_show', true );
        
        if ( $always_show ) {
            echo 'Yes';
        } else {
            echo 'No';
        }
    }
        
    
    
}