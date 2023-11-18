<?php

/**
 * Output the amenities section
 *
 */
function rentfetch_single_properties_parts_amenities() {
    
    $maybe_do_amenities = apply_filters( 'rentfetch_maybe_do_property_part_amenities', true );    
    if ( $maybe_do_amenities !== true )
        return;    
    
    echo '<div id="amenities" class="single-properties-section">';
		echo '<div class="wrap">';
		
			echo '<h2>Amenities</h2>';
			$terms = get_the_terms( get_the_ID(), 'amenities' );
			
			$count = count( $terms );
			$even = ( $count % 2 == 0 ) ? true : false;			
			$number_class = ( $even ) ? 'even' : 'odd';
			
			printf( '<ul class="amenities %s">', $number_class );
				foreach( $terms as $term ) {                
					printf( '<li>%s</li>', esc_attr( $term->name ) );
				}
			echo '</ul>';
				
		echo '</div>'; // .wrap
	echo '</div>'; // #amenities
    
}

/**
 * Decide whether to output the amenities section
 */
add_filter( 'rentfetch_maybe_do_property_part_amenities', 'rentfetch_maybe_property_part_amenities' );
function rentfetch_maybe_property_part_amenities() {
    
    // bail if this section is not enabled
    $property_components = get_option( 'rentfetch_options_single_property_components' );
    if ( !is_array( $property_components ) || !in_array( 'amenities_display', $property_components ) )
        return false;
		
	$terms = get_the_terms( get_the_ID(), 'amenities' );
	if ( !$terms )
		return false;
        
    return true;
}

function rentfetch_single_properties_parts_subnav_amenities() {
    $maybe_do_amenities = apply_filters( 'rentfetch_maybe_do_property_part_amenities', true );
    if ( $maybe_do_amenities === true ) {
    	$label = apply_filters( 'rentfetch_amenities_display_subnav_label', 'Amenities' );
    	printf( '<li><a href="#amenities">%s</a></li>', $label );
    }
}