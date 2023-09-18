<?php


function rentfetch_start_sync() {
    
    global $wpdb;

    $table_list = array(
        'actionscheduler_actions',
        'actionscheduler_logs',
        'actionscheduler_groups',
        'actionscheduler_claims',
    );
        
    foreach( $table_list as $table ) {
        
        $found_tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}{$table}'" );
        
        // if the tables are missing, output the notice and return
        if ( !$found_tables ) {
            
            add_action( 'admin_notices', 'rentfetch_database_tables_missing_notice' );
            
            return;
        }
    }
    
    
    //* REMOVE ALL ACTIONS ADDED BY OLD APARTMENTSYNC, ADDED IN 3.0, CAN BE REMOVED IN A LATER RELEASE
    // as_unschedule_action( 'apartmentsync_do_get_yardi_property_from_api' );
    as_unschedule_all_actions( 'apartmentsync_do_get_yardi_property_from_api' );
    // as_unschedule_action( 'apartmentsync_do_get_yardi_floorplans_from_api_for_property' );
    as_unschedule_all_actions( 'apartmentsync_do_get_yardi_floorplans_from_api_for_property' );
    // as_unschedule_action( 'apartmentsync_do_fetch_yardi_floorplans' );
    as_unschedule_all_actions( 'apartmentsync_do_fetch_yardi_floorplans' );
    // as_unschedule_action( 'do_get_yardi_property_from_api' );
    as_unschedule_all_actions( 'do_get_yardi_property_from_api' );
    // as_unschedule_action( 'do_get_yardi_floorplans_from_api_for_property' );
    as_unschedule_all_actions( 'do_get_yardi_floorplans_from_api_for_property' );
    // as_unschedule_action( 'do_fetch_yardi_floorplans' );
    as_unschedule_all_actions( 'do_fetch_yardi_floorplans' );
    
    $sync_term = get_option( 'options_sync_term' );
    $data_sync = get_option( 'options_data_sync' );
            
    if ( $sync_term == 'paused' || $data_sync == 'delete' || $data_sync == 'nosync' ) {       
         
        // yardi
        as_unschedule_all_actions( 'rentfetch_do_get_yardi_property_from_api' );
        as_unschedule_all_actions( 'rentfetch_do_get_yardi_floorplans_from_api_for_property' );
        as_unschedule_all_actions( 'rentfetch_do_fetch_yardi_floorplans' );
        as_unschedule_all_actions( 'rentfetch_do_remove_floorplans_from_orphan_yardi_properties_specific' );
        as_unschedule_all_actions( 'rentfetch_do_remove_orphan_yardi_properties' );
        
        // appfolio
        // as_unschedule_action( 'rentfetch_appfolio_do_process_and_save_floorplans' );
        as_unschedule_all_actions( 'rentfetch_appfolio_do_process_and_save_floorplans' );
        
        // geocoding
        as_unschedule_all_actions( 'rentfetch_geocoding_get_lat_long' );
        
        
    } else {
        
        //* We're doing these async because we don't want them constantly triggering on each pageload.
        if ( as_next_scheduled_action( 'rentfetch_do_sync_logic' ) === false  ) 
            as_enqueue_async_action( 'rentfetch_do_sync_logic' );
            
        // if ( as_next_scheduled_action( 'rentfetch_do_chron_activation' ) === false  ) 
        //     as_enqueue_async_action( 'rentfetch_do_chron_activation' );
            
        if ( as_next_scheduled_action( 'rentfetch_do_remove_old_data' ) === false  ) 
            as_enqueue_async_action( 'rentfetch_do_remove_old_data' );
            
    }
        
    //* Delete everything if we're set to delete
    if ( $data_sync == 'delete' )
        do_action( 'rentfetch_do_delete' );
            
    // do_action( 'rentfetch_do_sync_logic' );
    // do_action( 'rentfetch_do_chron_activation' );
        
    // // Look and see whether there's another scheduled action waiting
    // var_dump( as_next_scheduled_action( 'rentfetch_do_sync_logic' ) ); 
    // var_dump( as_next_scheduled_action( 'rentfetch_do_chron_activation' ) );
    
}

function rentfetch_database_tables_missing_notice() {
    echo '<div class="notice notice-error is-dismissible">';
    echo '<p>' . __( '<strong>Rent Fetch:</strong> The Action Scheduler tables appear to be missing. Please <a href="/wp-admin/tools.php?page=action-scheduler">vist the Action Scheduler admin page</a> to regenerate those.', 'rentfetch' ) . '</p>';
    echo '</div>';
}