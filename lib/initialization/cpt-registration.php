<?php

//////////////////////
// CPT REGISTRATION //
//////////////////////

add_action( 'init', 'rentfetch_register_content_types' );
function rentfetch_register_content_types() {
            
    //* figure out whether this is a single 
    $apartment_site_type = get_option( 'rentfetch_options_apartment_site_type' );
            
    //* only register the properties and neighborhoods post types if this is a 'multiple' site
    if ( $apartment_site_type == 'multiple' ) {
        
        // properties cpt
        add_action( 'init', 'rentfetch_register_properties_cpt', 20 );
        
        // amenities property taxes
        add_action( 'init', 'rentfetch_register_amenities_taxonomy', 20 );
        add_action( 'init', 'rentfetch_register_propertytype_taxonomy', 20 );
        
        // neighborhoods cpt
        // add_action( 'init', 'rentfetch_register_neighborhoods_cpt', 20 );
        
        // neighborhoods taxes
        // add_action( 'init', 'rentfetch_register_areas_taxonomy', 20 );
        
    }
}