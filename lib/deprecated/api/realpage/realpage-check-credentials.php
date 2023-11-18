<?php

/**
 * Check if the credentials exist
 *
 * @return  bool  true if creds exist, false if not
 */
function rentfetch_check_creds_realpage() {
    
    $realpage_integration_creds = get_option( 'rentfetch_options_realpage_integration_creds' );
    $realpage_user = get_option( 'rentfetch_options_realpage_integration_creds_realpage_user' );
    $realpage_pass = get_option( 'rentfetch_options_realpage_integration_creds_realpage_pass' );
    $realpage_pmc_id = get_option( 'rentfetch_options_realpage_integration_creds_realpage_pmc_id' );
    $realpage_site_ids = get_option( 'rentfetch_options_realpage_integration_creds_realpage_site_ids' );
    
    // return false if there's no api key set
    if ( !$realpage_user || !$realpage_pass || !$realpage_pmc_id || !$realpage_site_ids )      
        return false;
    
    // return true if there's an api key
    return true;
    
}
