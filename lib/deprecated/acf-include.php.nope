<?php


/////////////////
// INCLUDE ACF //
/////////////////

if( !class_exists('ACF') ) {
    
    // Include the ACF plugin.
    include_once( RENTFETCH_ACF_PATH . 'acf.php' );
    
    // Customize the url setting to fix incorrect asset URLs.
    add_filter('acf/settings/url', 'rentfetch_acf_settings_url');
    
}

function rentfetch_acf_settings_url( $url ) {
    return RENTFETCH_ACF_URL;
}

//! UNCOMMENT THIS FILTER TO SAVE ACF FIELDS TO PLUGIN
// add_filter('acf/settings/save_json', 'rentfetch_acf_json_save_point');
function rentfetch_acf_json_save_point( $path ) {
    
    // update path
    $path = RENTFETCH_DIR . 'acf-json';
    
    // return
    return $path;
    
}

// add_filter( 'acf/settings/load_json', 'rentfetch_acf_json_load_point' );
function rentfetch_acf_json_load_point( $paths ) {
    
    // remove original path (optional)
    unset($paths[0]);
    
    // append path
    $paths[] = RENTFETCH_DIR . 'acf-json';
    
    // return
    return $paths;
    
}