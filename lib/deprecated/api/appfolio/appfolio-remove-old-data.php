<?php


/**
 * Remove unused properties (based on the properties setting)
 */
add_action( 'rentfetch_do_remove_old_data', 'rentfetch_appfolio_remove_unused_properties' );
function rentfetch_appfolio_remove_unused_properties() {
    
    $sync_term = get_option( 'options_sync_term' );
    $data_sync = get_option( 'options_data_sync' );
        
    // bail if we're paused
    if ( $sync_term == 'paused' || $data_sync == 'delete' || $data_sync == 'nosync' )
        return;
        
    $property_ids_attached_to_properties = rentfetch_get_meta_values( 'property_id', 'properties' );
    $property_ids_attached_to_properties = array_unique( $property_ids_attached_to_properties );
    $property_ids_attached_to_properties = array_map('strtolower', $property_ids_attached_to_properties); // lowercase everything, as case mismatches can give us bad results
        
    // get the property ids from the setting
    $integration_creds = get_option( 'options_appfolio_integration_creds' );
    $properties_in_setting = get_option( 'options_appfolio_integration_creds_appfolio_property_ids' );
    
    //* bail if we're not using the setting
    if ( !$properties_in_setting )
        return;
    
    $properties_in_setting = preg_replace('/\s+/', '', $properties_in_setting);
    $properties_in_setting = explode( ',', $properties_in_setting );
    $properties_in_setting = array_unique( $properties_in_setting );
    $properties_in_setting = array_map('strtolower', $properties_in_setting); // lowercase everything, as case mismatches can give us bad results
            
    // get the ones that are in the database, but that aren't in the setting
    $properties = array_diff( $property_ids_attached_to_properties, $properties_in_setting );
            
    // bail if there's nothing to delete
    if ( empty( $properties ) ) 
        return;
            
    $args = array(
        'post_type' => 'properties',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'relation' => 'AND',
                array(
                    'key' => 'property_source',
                    'value' => 'appfolio',
                ),
                array(
                    'key'   => 'property_id',
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
            $property_wordpress_id = get_post_meta( $property_id, 'property_id', true );
            
            rentfetch_log( "Deleting property $property_id." );
                        
            wp_delete_post( $property_id, true );
        }
    }
}

/**
 * Remove unused floorplans (based on the properties setting)
 */
add_action( 'rentfetch_do_remove_old_data', 'rentfetch_appfolio_remove_unused_floorplans' );
function rentfetch_appfolio_remove_unused_floorplans() {
    
    $sync_term = get_option( 'options_sync_term' );
    $data_sync = get_option( 'options_data_sync' );
        
    // bail if we're paused
    if ( $sync_term == 'paused' || $data_sync == 'delete' || $data_sync == 'nosync' )
        return;
        
    $property_ids_attached_to_floorplans = rentfetch_get_meta_values( 'property_id', 'floorplans' );
    $property_ids_attached_to_floorplans = array_unique( $property_ids_attached_to_floorplans );
    $property_ids_attached_to_floorplans = array_map('strtolower', $property_ids_attached_to_floorplans); // lowercase everything, as case mismatches can give us bad results
        
    // get the property ids from the setting
    $integration_creds = get_option( 'options_appfolio_integration_creds' );
    $properties_in_setting = get_option( 'options_appfolio_integration_creds_appfolio_property_ids' );
    
    //* bail if we're not using the setting
    if ( !$properties_in_setting )
        return;
    
    $properties_in_setting = preg_replace('/\s+/', '', $properties_in_setting);
    $properties_in_setting = explode( ',', $properties_in_setting );
    $properties_in_setting = array_unique( $properties_in_setting );
    $properties_in_setting = array_map('strtolower', $properties_in_setting); // lowercase everything, as case mismatches can give us bad results
            
    // get the ones that are in the database, but that aren't in the setting
    $properties = array_diff( $property_ids_attached_to_floorplans, $properties_in_setting );
            
    // bail if there's nothing to delete
    if ( empty( $properties ) ) 
        return;
        
    //* loop through each property and delete the related floorplans 
    foreach( $properties as $property ) {
        
        $args = array(
            'post_type' => 'floorplans',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'floorplan_source',
                        'value' => 'appfolio',
                    ),
                    array(
                        'key'   => 'property_id',
                        'value' => $property,
                    ),
                ),
            ),
        );
        
        $floorplans_to_delete = new WP_Query($args);
        
        if ( $floorplans_to_delete->have_posts() ) {
            
            while ( $floorplans_to_delete->have_posts() ) {
                
                $floorplans_to_delete->the_post();
                            
                $property_id = get_the_ID();
                            
                wp_delete_post( $property_id, true );
            }
        }
    } 
}