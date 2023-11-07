<?php

function rentfetch_floorplans_search_floorplans_each_default() {
    
    $title = rentfetch_get_floorplan_title();
    $beds = rentfetch_get_floorplan_bedrooms();
    $baths = rentfetch_get_floorplan_bathrooms();
    $square_feet = rentfetch_get_floorplan_square_feet();
    $available_units = rentfetch_get_floorplan_available_units();
    
    echo '<div class="floorplan-images">';
    
    echo '</div>';
    echo '<div class="floorplan-content">';
    
        if ( $title )
            printf( '<h3>%s</h3>', $title );
        
        echo '<p class="info">';
        
            if ( $beds )
                printf( '<span class="beds">%s</span>', $beds );
            
            if ( $baths )
                printf( '<span class="baths">%s</span>', $baths );
                        
            if ( $square_feet )
                printf( '<span class="square-feet">%s</span>', $square_feet );
                    
        echo '</p>';
    
    echo '</div>';
    echo '<div class="floorplan-availability">';
    
    if ( $available_units )
        printf( '<p class="available-units">%s</p>', $available_units );
    
    echo '</div>';
    
}
add_action( 'rentfetch_floorplans_search_do_floorplans_each', 'rentfetch_floorplans_search_floorplans_each_default' );