<?php

add_action( 'rentfetch_do_get_floorplans_yardi', 'rentfetch_get_floorplans_yardi' );
function rentfetch_get_floorplans_yardi() {
    
    // notify the user, then bail if we're missing credential data
    if ( rentfetch_check_creds_yardi() == false ) {
        add_action( 'admin_notices', 'rentfetch_yardi_missing_user_pass_notice');
        return;
    }
    
    $properties = get_option( 'options_yardi_integration_creds_yardi_property_code' );
    $properties = preg_replace('/\s+/', '', $properties);    
    $properties = explode( ',', $properties );
    $yardi_api_key = get_option( 'options_yardi_integration_creds_yardi_api_key' );
    $sync_term = get_option( 'options_sync_term' );
    $data_sync = get_option( 'options_data_sync' );
        
    foreach( $properties as $property ) {
            
        // if syncing is paused or data dync is off, then bail, as we won't be restarting anything
        if ( $sync_term == 'paused' || $data_sync == 'delete' || $data_sync == 'nosync' ) {
            // as_unschedule_action( 'rentfetch_do_get_yardi_floorplans_from_api_for_property', array( $property, $yardi_api_key ), 'yardi' );
            // as_unschedule_all_actions( 'rentfetch_do_get_yardi_floorplans_from_api_for_property', array( $property, $yardi_api_key ), 'yardi' );
            // rentfetch_verbose_log( "Sync term has changed for pulling from API. Removing upcoming actions." );
            continue;
        }
                        
        if ( as_next_scheduled_action( 'rentfetch_do_get_yardi_floorplans_from_api_for_property', array( $property, $yardi_api_key ), 'yardi' ) == false ) {
            rentfetch_verbose_log( "Upcoming actions not found. Scheduling tasks $sync_term to get Yardi property $property floorplans from API." );    
            as_enqueue_async_action( 'rentfetch_do_get_yardi_floorplans_from_api_for_property', array( $property, $yardi_api_key ), 'yardi' );
            as_schedule_recurring_action( time(), rentfetch_get_sync_term_in_seconds(), 'rentfetch_do_get_yardi_floorplans_from_api_for_property', array( $property, $yardi_api_key ), 'yardi' );
        }            
    }
}

add_action( 'rentfetch_do_get_yardi_floorplans_from_api_for_property', 'get_yardi_floorplans_from_api_for_property', 10, 2 );
function get_yardi_floorplans_from_api_for_property( $property, $yardi_api_key ) {
    
    $floorplans = get_transient( 'yardi_floorplans_property_id_' . $property );
        
    // bail if we already have a transient with this data in it
    if ( $floorplans != false ) {
        rentfetch_verbose_log( "Transient found for Yardi property $property floorplans (yardi_floorplans_property_id_$property). No need to query the API." );
        return;
    }
    
    rentfetch_verbose_log( "Transient not found for Yardi property $property floorplans (yardi_floorplans_property_id_$property). Pulling new data from https://api.rentcafe.com/rentcafeapi.aspx?requestType=floorplan." );
        
    // Do the API request
    $url = sprintf( 'https://api.rentcafe.com/rentcafeapi.aspx?requestType=floorplan&apiToken=%s&VoyagerPropertyCode=%s', $yardi_api_key, $property ); // path to your JSON file
    $data = file_get_contents( $url ); // put the contents of the file into a variable        
    $floorplans = json_decode( $data, true ); // decode the JSON feed
    $errorcode = null;
    
    if ( isset( $floorplans[0]['Error'] ) ) {
        
        do_action( 'rentfetch_yardi_floorplan_show_error', $floorplans[0]['Error'], $property );

        // error has happened
        $errorcode = $floorplans[0]['Error'];
    }
        
    if ( !$errorcode && !empty( $floorplans ) ) {
        set_transient( 'yardi_floorplans_property_id_' . $property, $floorplans, rentfetch_get_sync_term_in_seconds() );
        rentfetch_verbose_log( "Yardi returned a list of floorplans for property $property successfully. New transient set: yardi_floorplans_property_id_$property" );                
    } elseif( !$errorcode && empty( $floorplans ) ) {
        rentfetch_log( "No floorplan data received from Yardi for property $property." );
    } else {
        rentfetch_log( "Floorplans API query: Yardi returned error code $errorcode for property $property." );
    }
    
}