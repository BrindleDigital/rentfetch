<?php

/**
 * Output the images section
 *
 */
function rentfetch_single_properties_parts_images() {
    
    $maybe_do_images = apply_filters( 'rentfetch_maybe_do_property_part_images', true );    
    if ( $maybe_do_images !== true )
        return;
    
    echo '<div id="images" class="single-properties-section no-padding full-width">';
		echo '<div class="wrap">';
		
		rentfetch_property_images_grid();
		
		echo '</div>';
	echo '</div>';
    
}

/**
 * Decide whether to output the images section
 */
add_filter( 'rentfetch_maybe_do_property_part_images', 'rentfetch_maybe_property_part_images' );
function rentfetch_maybe_property_part_images() {
    
    // bail if this section is not enabled
    $property_components = get_option( 'options_single_property_components' );
    if ( !is_array( $property_components ) || !in_array( 'property_images', $property_components ) )
        return false;
        
    // bail if there are no images
    $images = rentfetch_get_property_images();
    if ( !$images )
        return false;
        
    return true;
}

function rentfetch_single_properties_parts_subnav_images() {
    $maybe_do_images = apply_filters( 'rentfetch_maybe_do_property_part_images', true );
    if ( $maybe_do_images === true ) {
        $label = apply_filters( 'rentfetch_property_images_subnav_label', 'Images' );
        printf( '<li><a href="#images">%s</a></li>', $label );
    }
}