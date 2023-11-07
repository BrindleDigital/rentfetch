<?php

add_filter( 'single_template', 'rentfetch_load_single_templates' );
function rentfetch_load_single_templates( $template ) {
    
    global $post;
            
    if ( 'floorplans' === $post->post_type && locate_template( array( 'single-floorplans.php' ) ) !== $template )
        return RENTFETCH_DIR . 'template/single-floorplans.php';
        
    if ( 'properties' === $post->post_type && locate_template( array( 'single-properties.php' ) ) !== $template )
        return RENTFETCH_DIR . 'template/single-properties.php';

    return $template;
}
