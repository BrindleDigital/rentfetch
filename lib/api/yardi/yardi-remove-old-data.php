<?php

add_action( 'rentfetch_do_remove_old_data', 'rentfetch_do_remove_floorplans_from_orphan_yardi_properties' );
function rentfetch_do_remove_floorplans_from_orphan_yardi_properties() {
            
    $sync_term = get_option( 'options_sync_term' );
    $data_sync = get_option( 'options_data_sync' );
    
    // if syncing is paused or data dync is off, then then bail, as we won't be restarting anything
    if ( $sync_term == 'paused' || $data_sync == 'delete' || $data_sync == 'nosync' )
        return;
    
    do_action( 'rentfetch_do_remove_orphan_yardi_properties' );
    do_action( 'rentfetch_do_remove_floorplans_from_orphan_yardi_properties_specific' );
 
}

add_action( 'rentfetch_do_remove_orphan_yardi_properties', 'rentfetch_remove_orphan_yardi_properties' );
function rentfetch_remove_orphan_yardi_properties() {
    
    rentfetch_verbose_log( "Running script to delete orphan properties from Yardi." );
    
    $property_ids_attached_to_properties = rentfetch_get_meta_values( 'voyager_property_code', 'properties' );
    $property_ids_attached_to_properties = array_unique( $property_ids_attached_to_properties );
    $property_ids_attached_to_properties = array_map('strtolower', $property_ids_attached_to_properties); // lowercase everything, as case mismatches can give us bad results
    
    // echo 'In the database: ' . count( $property_ids_attached_to_properties ) . '<br/>';
    // var_dump( $property_ids_attached_to_properties );

    // get the property ids from the setting
    $yardi_integration_creds = get_option( 'options_yardi_integration_creds' );
    $yardi_api_key = get_option( 'options_yardi_integration_creds_yardi_api_key' );
    $properties_in_setting = get_option( 'options_yardi_integration_creds_yardi_property_code' );
    $properties_in_setting = preg_replace('/\s+/', '', $properties_in_setting);
    $properties_in_setting = explode( ',', $properties_in_setting );
    $properties_in_setting = array_unique( $properties_in_setting );
    $properties_in_setting = array_map('strtolower', $properties_in_setting); // lowercase everything, as case mismatches can give us bad results
    
    // echo 'In setting: ' . count( $properties_in_setting ) . '<br/>';
    // var_dump( $properties_in_setting );
    
    // console_log( $property_ids_attached_to_properties );
    // console_log( $properties_in_setting );
    
    // get the ones that are in the database, but that aren't in the setting
    $properties = array_diff( $property_ids_attached_to_properties, $properties_in_setting );
    
    // console_log( $properties );
    
    // var_dump( $properties );
    // echo 'Diff: ' . count( $properties );
    
    
    // bail if there's nothing to delete
    if ( empty( $properties )) {
        rentfetch_verbose_log( "No orphan properties found." );
        return;
    }
            
    $args = array(
        'post_type' => 'properties',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'relation' => 'AND',
                array(
                    'key' => 'property_source',
                    'value' => 'yardi',
                ),
                array(
                    'key'   => 'voyager_property_code',
                    'value' => $properties,
                ),
            ),
        ),
    );
    
    $properties_to_delete = new WP_Query($args);
    if ( $properties_to_delete->have_posts() ) {
        while ( $properties_to_delete->have_posts() ) {
            $properties_to_delete->the_post();
            
            $property_id = get_the_ID();
            $voyager_property_code = get_post_meta( $property_id, 'voyager_property_code', true );
            
            rentfetch_log( "Deleting property $voyager_property_code." );
            wp_delete_post( $property_id, true );
        }
    }
}

add_action( 'rentfetch_do_remove_floorplans_from_orphan_yardi_properties_specific', 'rentfetch_remove_floorplans_from_orphan_yardi_properties_specific' );
function rentfetch_remove_floorplans_from_orphan_yardi_properties_specific() {
    
    // get the property ids which exist in our floorplans CPT 'property_id' meta field
    $property_ids_attached_to_floorplans = rentfetch_get_meta_values( 'voyager_property_code', 'floorplans' );
    $property_ids_attached_to_floorplans = array_unique( $property_ids_attached_to_floorplans );
    
    // get the property ids from the setting
    $yardi_integration_creds = get_option( 'options_yardi_integration_creds' );
    $yardi_api_key = get_option( 'options_yardi_integration_creds_yardi_api_key' );
    $properties_in_setting = get_option( 'options_yardi_integration_creds_yardi_property_code' );
    $properties_in_setting = preg_replace('/\s+/', '', $properties_in_setting);
    $properties_in_setting = explode( ',', $properties_in_setting );
    
    // get the ones that are in floorplans, but that aren't in the setting
    $properties = array_diff( $property_ids_attached_to_floorplans, $properties_in_setting );
    
    if ( $properties == null )
        return;
    
    // for each property that's in the DB but not in our list, do a query for floorplans that correspond, then delete those
    foreach( $properties as $property ) {
        
        // remove upcoming actions for pulling floorplans from the API
        rentfetch_verbose_log( "Property $property found in published floorplans, but not found in setting. Removing upcoming api actions." );
        as_unschedule_action( 'rentfetch_do_get_yardi_floorplans_from_api_for_property', array( $property, $yardi_api_key ), 'yardi' );
        as_unschedule_all_actions( 'rentfetch_do_get_yardi_floorplans_from_api_for_property', array( $property, $yardi_api_key ), 'yardi' );
        
        // remove upcoming actions for syncing floorplans
        rentfetch_verbose_log( "Property $property found in published floorplans, but not found in setting. Removing upcoming CPT update actions." );
        as_unschedule_action( 'rentfetch_do_fetch_yardi_floorplans', array( $property ), 'yardi' );
        as_unschedule_all_actions( 'rentfetch_do_fetch_yardi_floorplans', array( $property ), 'yardi' );
        
        $args = array(
            'post_type' => 'floorplans',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'floorplan_source',
                        'value' => 'yardi',
                    ),
                    array(
                        'key'   => 'voyager_property_code',
                        'value' => $property,
                    ),
                ),
            ),
        );
        
        $floorplan_query = new WP_Query($args);
        $floorplanstodelete = $floorplan_query->posts;
        
        foreach ($floorplanstodelete as $floorplantodelete) {
            rentfetch_verbose_log( "Deleting floorplan $floorplantodelete->ID." );
            wp_delete_post( $floorplantodelete->ID, true );
        }
                
    }
    
}
