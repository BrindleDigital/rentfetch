<?php

/**
 * Output the details section
 *
 */
function rentfetch_single_properties_parts_details() {
    
    $maybe_do_details = apply_filters( 'rentfetch_maybe_do_property_part_details', true );    
    if ( $maybe_do_details !== true )
        return;
    
    echo '<div id="details" class="single-properties-section">';
		echo '<div class="wrap">';
				
            $title = rentfetch_get_property_title();
            $location = rentfetch_get_property_location();
            $location_link = rentfetch_get_property_location_link();
            $url = rentfetch_get_property_url();
            $phone = rentfetch_get_property_phone();
            $property_description = rentfetch_get_property_description();
            $property_rent = rentfetch_get_property_rent();
            $beds = rentfetch_get_property_bedrooms();
            $sqrft = rentfetch_get_property_square_feet();
				
            echo '<div class="property-details-header">';
                echo '<div class="property-details-basic-info">';
                
                    if ( $title )
                        printf( '<h1 class="title">%s</h1>', $title );
                        
                    if ( $location )
                        printf( '<p class="location">%s</p>', $location );
                        
                echo '</div>';
                echo '<div class="property-details-buttons">';
                
                echo '</div>';
            echo '</div>'; // .property-details-header
            echo '<div class="property-details-body">';
                echo '<div class="property-links">';
                                    
                    if ( $location_link )
                        printf( '<a class="location-link property-link" href="%s" target="_blank">Get Directions</a>', $location_link );
                        
                    if ( $url )
                        printf( '<a class="url-link property-link" href="%s" target="_blank">Visit Website</a>', $url );
                        
                    if ( $phone )
                        printf( '<a class="phone-link property-link" href="tel:%s">%s</a>', $phone, $phone );
                        
                    echo '<a href="#" class="property-link">(( contact leasing??? ))</a>';
                    
                echo '</div>'; // .property-links
                    
                echo '<div class="property-basic-info">';
                    echo '<div class="property-stats">';
                    
                        if ( $property_rent )
                            printf( '<p class="rent">%s</p>', $property_rent );
                            
                        if ( $beds )
                            printf( '<p class="beds">%s</p>', $beds );
                            
                        if ( $sqrft )
                            printf( '<p class="sqrft">%s</p>', $sqrft );
                        
                    echo '</div>';
                    
                    if ( $property_description )
                        printf( '<div class="description">%s</div>', $property_description );
                    
                echo '</div>'; // .property-basic-info
            echo '</div>'; // .property-details-body
            
		echo '</div>'; // .wrap
	echo '</div>'; // #details
    
}

/**
 * Decide whether to output the details section
 */
add_filter( 'rentfetch_maybe_do_property_part_details', 'rentfetch_maybe_property_part_details' );
function rentfetch_maybe_property_part_details() {
    
    // bail if this section is not enabled
    $property_components = get_option( 'options_single_property_components' );
    if ( !is_array( $property_components ) || !in_array( 'property_details', $property_components ) )
        return false;
                
    return true;
}

function rentfetch_single_properties_parts_subnav_details() {
    $maybe_do_details = apply_filters( 'rentfetch_maybe_do_property_part_details', true );
    if ( $maybe_do_details === true ) {
        $label = apply_filters( 'rentfetch_property_details_subnav_label', 'Details' );
        printf( '<li><a href="#details">%s</a></li>', $label );
    }
}