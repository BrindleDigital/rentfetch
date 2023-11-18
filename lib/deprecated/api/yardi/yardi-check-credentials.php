<?php

/**
 * Check if the credentials exist
 *
 * @return  bool  true if creds exist, false if not
 */
function rentfetch_check_creds_yardi() {
    
    $yardi_api_key = get_option( 'rentfetch_options_yardi_integration_creds_yardi_api_key' );
    $yardi_property_code = get_option( 'rentfetch_options_yardi_integration_creds_yardi_property_code' );
    
    // return false if there's no api key set
    if ( !$yardi_api_key )      
        return false;
    
    // return true if there's an api key
    return true;
    
}

/**
 * Echo the notice, for if user credentials are missing
 */
function rentfetch_yardi_missing_user_pass_notice() {
    echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>Syncing of data with Yardi is enabled, but we\'re missing an API key for the integration.</p>';
    echo '</div>';
}