<?php

add_action( 'rentfetch_do_check_creds_entrata', 'rentfetch_check_creds_entrata' );
function rentfetch_check_creds_entrata() {
    
    $entrata_user = get_option( 'options_entrata_integration_creds_entrata_user' );
    $entrata_pass = get_option( 'options_entrata_integration_creds_entrata_pass' );
    
    if ( !$entrata_user || !$entrata_pass ) {
        add_action( 'admin_notices', 'rentfetch_entrata_missing_user_pass_notice');
        return;
    }
    
}

function rentfetch_entrata_missing_user_pass_notice() {
    echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>Syncing of data with Entrata is enabled, but we\'re missing a username or password for the integration.</p>';
    echo '</div>';
}