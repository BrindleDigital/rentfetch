<?php

/**
 * Get the units from AppFolio (AppFolio doesn't have a concept of floorplans) and save them to a transient
 */
add_action( 'rentfetch_do_get_floorplans_appfolio', 'rentfetch_get_floorplans_appfolio' );
// add_action( 'wp_footer', 'rentfetch_get_floorplans_appfolio' );
// add_action( 'admin_footer', 'rentfetch_get_floorplans_appfolio' );
function rentfetch_get_floorplans_appfolio() {
    
    // bail if credentials haven't been completed fully
    if ( rentfetch_check_creds_appfolio() == false )
        return;
                
    // check to see if there's already a transient set
    $units = get_transient( 'appfolio_units' );
    
    // bail if we already have a transient
    if ( $units )
        return;
        
    // get all of the credentials
    $appfolio_database_name = get_option( 'options_appfolio_integration_creds_appfolio_database_name' );
    $appfolio_client_id = get_option( 'options_appfolio_integration_creds_appfolio_client_id' );
    $appfolio_client_secret = get_option( 'options_appfolio_integration_creds_appfolio_client_secret' );
    $appfolio_property_ids = get_option( 'options_appfolio_integration_creds_appfolio_property_ids' );
    
    // remove all whitespace from $appfolio_property_ids
    $appfolio_property_ids = preg_replace('/\s+/', '', $appfolio_property_ids);
        
    if ( !$appfolio_property_ids ) {
        // if we don't have a list of property ids
        $curlopt_url = sprintf( 
            'https://%s:%s@%s.appfolio.com/api/v1/reports/unit_directory.json?paginate_results=false', 
            $appfolio_client_id,
            $appfolio_client_secret,  
            $appfolio_database_name,  
        );
    } else {
        // if we do have a list of property ids
        $curlopt_url = sprintf( 
            'https://%s:%s@%s.appfolio.com/api/v1/reports/unit_directory.json?paginate_results=false&properties=%s', 
            $appfolio_client_id,
            $appfolio_client_secret,  
            $appfolio_database_name,  
            $appfolio_property_ids,
        );
    }
        
    $curl = curl_init();

    curl_setopt_array( $curl, array(
        CURLOPT_URL => $curlopt_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    
    $responseArray = json_decode( $response, true );
    
    set_transient( 'appfolio_units', $responseArray, 900 );
    
}

// add_action( 'wp_footer', 'rentfetch_appfolio_units_small_array' );
// add_action( 'admin_footer', 'rentfetch_appfolio_units_small_array' );
function rentfetch_appfolio_units_small_array() {
    
    $units = get_transient( 'appfolio_units' );
    
    // bail if there's no transient data yet
    if ( !$units )
        return;
        
    // console_log( $units );
    
    $units_processed = [];
    
    foreach ( $units as $unit ) {
        $unit_processed = [
            'property_id' => $unit['PropertyId'],
            'unit_id' => $unit['UnitId'],
            'unit_name' => $unit['UnitName'],
            'beds' => $unit['Bedrooms'],
            'baths' => $unit['Bathrooms'],
            'sqrft' => $unit['SquareFt'],
            'unittype' => $unit['UnitType'],
            'rent' => $unit['AdvertisedRent'],
            'visibility' => $unit['Visibility'],
            'rentready' => $unit['RentReady'],
        ];
        
        $units_processed[] = $unit_processed;
    }
    
    // console_log( $units_processed );
    
}

// add_action( 'wp_footer', 'rentfetch_units_to_floorplans' );
// add_action( 'admin_footer', 'rentfetch_units_to_floorplans' );
function rentfetch_units_to_floorplans() {
    $units = get_transient( 'appfolio_units' );
    
    // bail if there's no transient data yet
    if ( !$units )
        return;
        
    $floorplans = [];
    $property_ids = [];
    $unit_types = [];
    
    foreach( $units as $unit ) {
        if ( $unit['UnitType'] == null ) {
            do_action( 'rentfetch_appfolio_save_as_floorplan', $unit );
        } elseif ( !in_array( $unit['UnitType'], $unit_types ) ) {
            // $unit_types[] = 
            
        }
    }
    
    // var_dump( $floorplans );
}


add_action( 'rentfetch_do_save_appfolio_floorplans_to_cpt', 'rentfetch_check_appfolio_floorplans_for_saving_or_syncing' );
// add_action( 'wp_footer', 'rentfetch_check_appfolio_floorplans_for_saving_or_syncing' );
// add_action( 'admin_footer', 'rentfetch_check_appfolio_floorplans_for_saving_or_syncing' );
function rentfetch_check_appfolio_floorplans_for_saving_or_syncing() {
    
    $sync_term = get_option( 'options_sync_term' );
    $data_sync = get_option( 'options_data_sync' );
    
    $units = get_transient( 'appfolio_units' );
    // console_log( $units );
    
    // blank array for the property IDs that can be found in here
    $property_ids = [];
    
    // get a a unique array of the possible values for property_id
    foreach( $units as $unit ) {
        $property_ids[] = intval( $unit['PropertyId'] );
    }
    
    $property_ids = array_unique( $property_ids );    
    
    // get an array of the unit types for each property
    foreach ( $property_ids as $property_id ) {
        
        // do_action( 'rentfetch_appfolio_do_process_and_save_floorplans', $property_id );
        
        // if syncing is paused or data dync is off, then then bail, as we won't be restarting anything
        if ( $sync_term == 'paused' || $data_sync == 'delete' || $data_sync == 'nosync' ) {
            continue;
        }
                
        if ( as_next_scheduled_action( 'rentfetch_appfolio_do_process_and_save_floorplans', array( $property_id ), 'appfolio' ) == false ) {
            as_enqueue_async_action( 'rentfetch_appfolio_do_process_and_save_floorplans', array( $property_id ), 'appfolio' );
            as_schedule_recurring_action( time(), rentfetch_get_sync_term_in_seconds(), 'rentfetch_appfolio_do_process_and_save_floorplans', array( $property_id ), 'appfolio' );
        }
       
    }
}

add_action( 'rentfetch_appfolio_do_process_and_save_floorplans', 'rentfetch_appfolio_process_and_save_floorplans', 10, 1 );
function rentfetch_appfolio_process_and_save_floorplans( $property_id ) {
    
    $property_units = rentfetch_appfolio_get_units_by_property( $property_id );
    $property_units = rentfetch_appfolio_add_unit_types_if_missing( $property_units );
    $floorplans = rentfetch_appfolio_get_floorplans_from_units( $property_units );
    $floorplans = rentfetch_appfolio_flatten_floorplan_data( $floorplans );
    
    // if ( isset( $floorplans['1BR / 1 BA'] ) )
    //     var_dump( $floorplans['1BR / 1 BA'] );
    
    rentfetch_appfolio_update_or_create( $floorplans );
        
}

/**
 * Get just the part of the units array for a particular property and return that array
 */
function rentfetch_appfolio_get_units_by_property( $property_id ) {
    $units = get_transient( 'appfolio_units' );    
    $property_units = [];
    
    foreach( $units as $key => $value ){
        if ( is_array($value) && $value['PropertyId'] == $property_id ) {
            $property_units[] = $value;
        }
    }
    
    return $property_units;
    
}

/**
 * Add a unit type if missing, then return all of them
 */
function rentfetch_appfolio_add_unit_types_if_missing( $property_units ) {
    
    $property_units_with_unit_types = [];
    
    foreach( $property_units as $property_unit ) {
        if ( !$property_unit['UnitType'] ) {
            $property_unit['UnitType'] = $property_unit['UnitName'];
        }

        $property_units_with_unit_types[] = $property_unit;
    }
    
    return $property_units_with_unit_types;
}

/**
 * Start with the units, then associate them into named floorplans (named for the units if no type is set)
 */
function rentfetch_appfolio_get_floorplans_from_units( $property_units ) {
    
    $floorplans = [];
    $floorplan_ids = [];
    
    foreach( $property_units as $property_unit ) {
        $floorplan_ids[] = $property_unit['UnitType'];
        $floorplans[ $property_unit['UnitType'] ][] = $property_unit;
    }
    
    return $floorplans;
}

/**
 * Flatten the array of floorplans into one
 */
function rentfetch_appfolio_flatten_floorplan_data( $floorplans ) {
    
    $floorplans_flattened = [];
    
    foreach( $floorplans as $key => $floorplan ) {
        
        $rent = [];
        $sqrft = [];
        $AvailableUnitsCount = 0;
        
        foreach( $floorplan as $detail ) {
            if ( $detail['AdvertisedRent'] > 100 )
                $rent[] = (float)$detail['AdvertisedRent'] + 0;
            
            if ( $detail['SquareFt'] > 100 )
                $sqrft[] = (int)$detail['SquareFt'] + 0;
                
            if ( $detail['ReadyForShowingOn'] )
                $date[] = $detail['ReadyForShowingOn'];
                
            if ( $detail['PostedToWebsite'] == 'Yes' )
                $AvailableUnitsCount++;
                
            // echo $detail['RentReady'];
        }
        
        if ( is_array( $rent ) && !empty( $rent ) ) {
            $min_rent = min( $rent );
            $max_rent = max( $rent );
        } else {
            $min_rent = null;
            $max_rent = null;
        }
        
        if ( is_array( $sqrft ) && !empty( $sqrft ) ) {
            $min_sqrft = min( $sqrft );
            $max_sqrft = max( $sqrft );
        } else {
            $min_sqrft = null;
            $max_sqrft = null;
        }
                        
        $floorplan = array(
            'FloorplanId' => $key,
            'PropertyId' => $floorplan[0]['PropertyId'],
            'Bedrooms' => $floorplan[0]['Bedrooms'],
            'Bathrooms' => $floorplan[0]['Bathrooms'],
            'MaxRent' => $max_rent,
            'MinRent' => $min_rent,
            'MaxSqrft' => $max_sqrft,
            'MinSqrft' => $min_sqrft,
            'AvailableUnitsCount' => $AvailableUnitsCount,
        );        
        
        $floorplans_flattened[] = $floorplan;
        
    }
    
    return $floorplans_flattened;
    
} 

/**
 * Figure out whether to update or create, then trigger that
 */
function rentfetch_appfolio_update_or_create( $floorplans ) {
            
    foreach ( $floorplans as $floorplan ) {
                
        $floorplan_id = $floorplan['FloorplanId'];
        $property_id = $floorplan['PropertyId'];
        
        // do a query to check and see if a post already exists with this ID 
        $args = array(
            'post_type' => 'floorplans',
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'floorplan_source',
                    'value' => 'appfolio',
                ),
                array(
                    'key' => 'floorplan_id',
                    'value' => $floorplan_id,
                    'compare' => '=',
                ),
                array(
                    'key' => 'property_id',
                    'value' => $property_id,
                    'compare' => '=',
                )
            )
        );
        
        $matchingposts = get_posts( $args );
        $count = count( $matchingposts );
        
        // insert the post if there isn't one already (this prevents duplicates)
        if ( !$matchingposts ) {
            rentfetch_appfolio_insert_floorplan( $floorplan );
            
        // if there's exactly one post found, then update the meta for that
        } elseif ( $count == 1 ) {
            rentfetch_appfolio_update_floorplan( $floorplan );
            
        // if there are more than one found, delete all of those that match and add fresh, since we likely have some bad data
        } elseif( $count > 1 ) {
            foreach ($matchingposts as $matchingpost) {
                wp_delete_post( $matchingpost->ID, true );
            }
            rentfetch_appfolio_insert_floorplan( $floorplan );
        }
    }
}

function rentfetch_appfolio_insert_floorplan( $floorplan ) {
    
    // all of the available variables
    // $AvailabilityURL = $floorplan['AvailabilityURL'];
    $AvailableUnitsCount = $floorplan['AvailableUnitsCount'];
    $Baths = floatval( $floorplan['Bathrooms'] );
    $Beds = floatval( $floorplan['Bedrooms'] );
    // $FloorplanHasSpecials = $floorplan['FloorplanHasSpecials'];
    $FloorplanId = $floorplan['FloorplanId'];
    // $FloorplanImageAltText = $floorplan['FloorplanImageAltText'];
    // $FloorplanImageName = $floorplan['FloorplanImageName'];
    // $FloorplanImageURL = $floorplan['FloorplanImageURL'];
    $FloorplanName = $floorplan['FloorplanId'];
    // $MaximumDeposit = $floorplan['MaximumDeposit'];
    $MaximumRent = $floorplan['MaxRent'];
    $MaximumSQFT = $floorplan['MaxSqrft'];
    // $MinimumDeposit = $floorplan['MinimumDeposit'];
    $MinimumRent = $floorplan['MinRent'];
    $MinimumSQFT = $floorplan['MinSqrft'];
    $PropertyId = $floorplan['PropertyId'];
    // $PropertyShowsSpecials = $floorplan['PropertyShowsSpecials'];
    // $UnitTypeMapping = $floorplan['UnitTypeMapping'];
    $FloorplanSource = 'appfolio'; // this one doesn't come from the API. This is our identifier that says "this caame from the API."
    
    // Create post object
    $floorplan_meta = array(
        'post_title'    => wp_strip_all_tags( $FloorplanName ),
        'post_status'   => 'publish',
        'post_type'     => 'floorplans',
        'meta_input'    => array(
            // 'availability_url'          => $AvailabilityURL,
            'available_units'           => $AvailableUnitsCount,
            'baths'                     => $Baths,
            'beds'                      => $Beds,
            // 'has_specials'              => $FloorplanHasSpecials,
            'floorplan_id'              => $FloorplanId,
            // 'floorplan_image_alt_text'  => $FloorplanImageAltText,
            // 'floorplan_image_name'      => $FloorplanImageName,
            // 'floorplan_image_url'       => $FloorplanImageURL,
            // 'maximum_deposit'           => $MaximumDeposit,
            'maximum_rent'              => $MaximumRent,
            'maximum_sqft'              => $MaximumSQFT,
            // 'minimum_deposit'           => $MinimumDeposit,
            'minimum_rent'              => $MinimumRent,
            'minimum_sqft'              => $MinimumSQFT,
            'property_id'               => $PropertyId,
            // 'voyager_property_code'     => $voyagercode,
            // 'property_show_specials'    => $PropertyShowsSpecials,
            // 'unit_type_mapping'         => $UnitTypeMapping,
            'floorplan_source'          => $FloorplanSource,
        ),
    );
    
    $post_id = wp_insert_post( $floorplan_meta );
    
}

function rentfetch_appfolio_update_floorplan( $floorplan ) {
    
    // all of the available variables
    // $AvailabilityURL = $floorplan['AvailabilityURL'];
    $AvailableUnitsCount = $floorplan['AvailableUnitsCount'];
    $Baths = floatval( $floorplan['Bathrooms'] );
    $Beds = floatval( $floorplan['Bedrooms'] );
    // $FloorplanHasSpecials = $floorplan['FloorplanHasSpecials'];
    $FloorplanId = $floorplan['FloorplanId'];
    // $FloorplanImageAltText = $floorplan['FloorplanImageAltText'];
    // $FloorplanImageName = $floorplan['FloorplanImageName'];
    // $FloorplanImageURL = $floorplan['FloorplanImageURL'];
    $FloorplanName = $floorplan['FloorplanId'];
    // $MaximumDeposit = $floorplan['MaximumDeposit'];
    $MaximumRent = $floorplan['MaxRent'];
    $MaximumSQFT = $floorplan['MaxSqrft'];
    // $MinimumDeposit = $floorplan['MinimumDeposit'];
    $MinimumRent = $floorplan['MinRent'];
    $MinimumSQFT = $floorplan['MinSqrft'];
    $PropertyId = $floorplan['PropertyId'];
    // $PropertyShowsSpecials = $floorplan['PropertyShowsSpecials'];
    // $UnitTypeMapping = $floorplan['UnitTypeMapping'];
    $FloorplanSource = 'appfolio'; // this one doesn't come from the API. This is our identifier that says "this caame from the API."
    
    // Create post object
    $floorplan_meta = array(
        'post_title'    => wp_strip_all_tags( $FloorplanName ),
        'post_status'   => 'publish',
        'post_type'     => 'floorplans',
        'meta_input'    => array(
            // 'availability_url'          => $AvailabilityURL,
            'available_units'           => $AvailableUnitsCount,
            'baths'                     => $Baths,
            'beds'                      => $Beds,
            // 'has_specials'              => $FloorplanHasSpecials,
            'floorplan_id'              => $FloorplanId,
            // 'floorplan_image_alt_text'  => $FloorplanImageAltText,
            // 'floorplan_image_name'      => $FloorplanImageName,
            // 'floorplan_image_url'       => $FloorplanImageURL,
            // 'maximum_deposit'           => $MaximumDeposit,
            'maximum_rent'              => $MaximumRent,
            'maximum_sqft'              => $MaximumSQFT,
            // 'minimum_deposit'           => $MinimumDeposit,
            'minimum_rent'              => $MinimumRent,
            'minimum_sqft'              => $MinimumSQFT,
            'property_id'               => $PropertyId,
            // 'voyager_property_code'     => $voyagercode,
            // 'property_show_specials'    => $PropertyShowsSpecials,
            // 'unit_type_mapping'         => $UnitTypeMapping,
            'floorplan_source'          => $FloorplanSource,
        ),
    );
    
    // The Loop
    if ( $matchingposts ) {
        
        foreach ( $matchingposts as $matchingpost ) {
            
            $post_id = $matchingpost->ID;
            
            if ( $FloorplanName != $matchingpost->post_title ) {
                // update post title
                $arr = array( 
                    'post_title' => $FloorplanName,
                    'ID' => $post_id,
                );
                wp_update_post( $arr );
            }
            
            // update post meta (NOTE: update_post_meta returns false if it doesn't update, true if it does)
            // $success_availability_url = update_post_meta( $post_id, 'availability_url', $AvailabilityURL );
            
            $success_available_units = update_post_meta( $post_id, 'available_units', $AvailableUnitsCount );
            
            $success_baths = update_post_meta( $post_id, 'baths', $Baths );
            
            $success_beds = update_post_meta( $post_id, 'beds', $Beds );
                
            // $success_has_specials = update_post_meta( $post_id, 'has_specials', $FloorplanHasSpecials );
                                
            // $success_floorplan_image_alt_text = update_post_meta( $post_id, 'floorplan_image_alt_text', $FloorplanImageAltText );
                
            // $success_floorplan_image_name = update_post_meta( $post_id, 'floorplan_image_name', $FloorplanImageName );
                
            // $success_floorplan_image_url = update_post_meta( $post_id, 'floorplan_image_url', $FloorplanImageURL );
                
            // $success_maximum_deposit = update_post_meta( $post_id, 'maximum_deposit', $MaximumDeposit );
                
            $success_maximum_rent = update_post_meta( $post_id, 'maximum_rent', $MaximumRent );
                
            $success_maximum_sqft = update_post_meta( $post_id, 'maximum_sqft', $MaximumSQFT );
                
            // $success_minimum_deposit = update_post_meta( $post_id, 'minimum_deposit', $MinimumDeposit );
                
            $success_minimum_rent = update_post_meta( $post_id, 'minimum_rent', $MinimumRent );
                
            $success_minimum_sqft = update_post_meta( $post_id, 'minimum_sqft', $MinimumSQFT );
                
            $success_property_id = update_post_meta( $post_id, 'property_id', $PropertyId );
                
            // $success_property_show_specials = update_post_meta( $post_id, 'property_show_specials', $PropertyShowsSpecials );
                
            // $success_unit_type_mapping = update_post_meta( $post_id, 'unit_type_mapping', $UnitTypeMapping );
                
            $success_floorplan_source = update_post_meta( $post_id, 'floorplan_source', $FloorplanSource );
            
        }   
        
        wp_reset_postdata();
    }
}