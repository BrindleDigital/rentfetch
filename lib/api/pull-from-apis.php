<?php

add_action( 'rentfetch_do_sync_logic', 'rentfetch_start_the_sync' );
function rentfetch_start_the_sync() {
    
    do_action( 'rentfetch_do_geocode' );
    
    $enabled_integrations = get_option( 'options_enabled_integrations' );
    
    if ( is_array( $enabled_integrations ) ) {
        foreach ( $enabled_integrations as $enabled_integration ) {
                        
            // start the process
            do_action( 'rentfetch_do_get_floorplans_' . $enabled_integration );
            do_action( 'rentfetch_do_get_properties_' . $enabled_integration );
            
            do_action( 'rentfetch_do_save_' . $enabled_integration . '_properties_to_cpt' );
            do_action( 'rentfetch_do_save_' . $enabled_integration . '_floorplans_to_cpt' );
            
        }
    }
}
