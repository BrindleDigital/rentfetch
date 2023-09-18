<?php

add_filter( 'single_template', 'apartmentsync_load_single_templates' );
function apartmentsync_load_single_templates( $template ) {
    
    global $post;
            
    if ( 'floorplans' === $post->post_type && locate_template( array( 'single-floorplans.php' ) ) !== $template )
        return RENTFETCH_DIR . 'template/single-floorplans.php';
        
    if ( 'properties' === $post->post_type && locate_template( array( 'single-properties.php' ) ) !== $template )
        return RENTFETCH_DIR . 'template/single-properties.php';

    return $template;
}
