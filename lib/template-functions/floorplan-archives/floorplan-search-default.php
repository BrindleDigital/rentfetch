<?php

function rentfetch_floorplans_search_floorplans_each_default() {
    
    $title = rentfetch_get_floorplan_title();
    $beds = rentfetch_get_floorplan_bedrooms();
    $baths = rentfetch_get_floorplan_bathrooms();
    $square_feet = rentfetch_get_floorplan_square_feet();
    $available_units = rentfetch_get_floorplan_available_units();
    $links = rentfetch_get_floorplan_links();
    $pricing = rentfetch_get_floorplan_pricing();
    
    do_action( 'rentfetch_do_floorplan_images' );
    
    echo '<div class="floorplan-details">';
        echo '<div class="floorplan-content">';
        
            if ( $title )
                printf( '<h3>%s</h3>', $title );
            
            echo '<div class="floorplan-attributes">';
            
                if ( $beds )
                    printf( '<p class="beds">%s</p>', $beds );
                
                if ( $baths )
                    printf( '<p class="baths">%s</p>', $baths );
                            
                if ( $square_feet )
                    printf( '<p class="square-feet">%s</p>', $square_feet );
                        
            echo '</div>';
        
        echo '</div>'; // .floorplan-content
        echo '<div class="floorplan-availability">';
        
            printf( '<p class="pricing">%s</p>', $pricing );
            printf( '<p class="availability">%s</p>', $available_units );
                
        echo '</div>'; // .floorplan-availability
        
        if ( $links )
            echo $links;
        
    echo '</div>'; // .floorplan-details
    
}
add_action( 'rentfetch_floorplans_search_do_floorplans_each', 'rentfetch_floorplans_search_floorplans_each_default' );