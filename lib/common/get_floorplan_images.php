<?php

// add_action( 'wp_footer', 'test_rentfetch_get_floorplan_images' );
// function test_rentfetch_get_floorplan_images() {
    
//     $args = array(
//         'post_type' => 'floorplans',  // Replace 'floorplans' with the actual name of your content type
//         'posts_per_page' => 100,       // Retrieve all posts of the specified content type
//     );

//     $query = new WP_Query($args);

//     if ($query->have_posts()) {
//         while ($query->have_posts()) {
//             $query->the_post();
//             the_title('<h2>', '</h2>');  // Output the title within <h2> tags
//             $images = rentfetch_get_floorplan_images();
            
//             foreach( $images as $image ) {
//                 printf( '<img style="width:100px; height: auto;" src="%s" />', $image['url'] );
//             }
            
//             edit_post_link();
            
//         }
//         wp_reset_postdata();
//     } else {
//         echo 'No floorplans found.';
//     }
        
    
// } 

function rentfetch_get_floorplan_images() {
    global $post;
    
    // bail if this isn't a floorplan
    if ($post->post_type != 'floorplans' )
        return;
    
    $manual_images = rentfetch_get_floorplan_images_manual();
    $yardi_images = rentfetch_get_floorplan_images_yardi();
    $fallback_images = rentfetch_get_floorplan_images_fallback();
        
    if ( $manual_images ) {
        return $manual_images;
    } elseif ( $yardi_images ) {
        return $yardi_images;
    } elseif ( $fallback_images) {
        return $fallback_images;
    } else {
        return;
    } 
}

function rentfetch_get_floorplan_images_manual() {
    global $post;
    
    $manual_image_ids = get_post_meta( get_the_ID(), 'manual_images', true );
                
    // bail if we don't have any
    if ( !$manual_image_ids )
        return;
        
    // bail if we only have one empty string in the manual images array
    if (count($manual_image_ids) === 1 && $manual_image_ids[0] === "" )
        return;
                        
    $manual_images = array();
        
    foreach ( $manual_image_ids as $manual_image_id ) {
        
        $manual_images[] = [
            'url' => wp_get_attachment_image_url($manual_image_id, 'large' ),
            'title' => get_the_title( $manual_image_id ),
            'alt' => get_post_meta( $manual_image_id, '_wp_attachment_image_alt', true ),
            'caption' => get_the_excerpt( $manual_image_id ),
        ];
    }
    
    return $manual_images;
    
}

function rentfetch_get_floorplan_images_yardi() {
    global $post; 
        
    $yardi_images_string = get_post_meta( get_the_ID(), 'floorplan_image_url', true );
            
    // bail if there's no yardi images
    if ( !$yardi_images_string )
        return;
        
    $yardi_images_array = explode( ',', $yardi_images_string );
    
    foreach( $yardi_images_array as $yardi_image ) {
        $yardi_images[] = [
            'url' => esc_url( $yardi_image ),
        ];
    }
    
    return $yardi_images;
    
}

function rentfetch_get_floorplan_images_fallback() {
    
    $fallback_images[] = [
        'url' => apply_filters( 'rentfetch_sample_image', RENTFETCH_PATH . 'images/fallback-property.svg' ),
        'title' => 'Sample image',
        'alt' => 'Sample image',
        'caption' => null,
    ];
    
    return $fallback_images;
}