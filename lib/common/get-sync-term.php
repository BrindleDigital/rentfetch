<?php

/**
 * Get the sync term and return it in seconds
 */
function rentfetch_get_sync_term_in_seconds() {
    $sync_term = get_option( 'options_sync_term' );
    
    if ( empty( $sync_term ) )
        $sync_term = 'paused';
        
    if ( $sync_term == 'daily' )
        $sync_term = 86400;
        
    if ( $sync_term == 'hourly' )
        $sync_term = 3600;
        
    if ( $sync_term == 'continuous' )
        $sync_term = 60;
    
    return $sync_term;
    
}

/**
 * Check whether the sync term has changed
 */
function rentfetch_check_if_sync_settings_have_changed() {
    
    $haschanged = false;
    
    /**
     * There are two relevant settings, and if either has changed this should return true
     */
    
    // sync term: this is continuous, hourly, daily, paused
    $current_sync_term = get_option( 'options_sync_term' );
    $old_sync_term = get_transient( 'rentfetch_sync_term' );
    
    if ( empty( $old_sync_term ) )
        set_transient( 'rentfetch_sync_term', $current_sync_term, YEAR_IN_SECONDS );
        
    // if the old one and the new one don't match (it's changed), then reset the transient
    if ( $current_sync_term != $old_sync_term ) {        
        delete_transient( 'rentfetch_sync_term' );
        set_transient( 'rentfetch_sync_term', $current_sync_term, YEAR_IN_SECONDS );
        $haschanged = true;
    }
    
    // data sync: this is nosync, updatesync, delete
    $current_data_sync = get_option( 'options_data_sync' );
    $old_data_sync = get_transient( 'rentfetch_data_sync' );
        
    if ( empty( $old_data_sync ) )
        set_transient( 'rentfetch_data_sync', $current_data_sync, YEAR_IN_SECONDS );
    
    // if the old one and the new one don't match (it's changed), then reset the transient
    if ( $current_data_sync != $old_data_sync ) {        
        delete_transient( 'rentfetch_data_sync' );
        set_transient( 'rentfetch_data_sync', $current_data_sync, YEAR_IN_SECONDS );
        $haschanged = true;
    }
    
    // return false if it hasn't changed
    return $haschanged;
}