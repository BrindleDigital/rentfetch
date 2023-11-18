<?php

/**
 * Check if the credentials exist
 *
 * @return  bool  true if creds exist, false if not
 */
function rentfetch_check_creds_appfolio() {
    
    $appfolio_database_name = get_option( 'rentfetch_options_appfolio_integration_creds_appfolio_database_name' );
    $appfolio_client_id = get_option( 'rentfetch_options_appfolio_integration_creds_appfolio_client_id' );
    $appfolio_client_secret = get_option( 'rentfetch_options_appfolio_integration_creds_appfolio_client_secret' );
        
    // return false if there's no api key set
    if ( !$appfolio_database_name || !$appfolio_client_id || !$appfolio_client_secret )      
        return false;
    
    // return true if there's an api key
    return true;
    
}
