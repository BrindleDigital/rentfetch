<?php

add_action( 'rentfetch_do_save_yardi_floorplans_to_cpt', 'rentfetch_save_yardi_floorplans_to_cpt' );
function rentfetch_save_yardi_floorplans_to_cpt() {
            
    $properties = get_option( 'options_yardi_integration_creds_yardi_property_code' );
    $properties = preg_replace('/\s+/', '', $properties);
    $properties = explode( ',', $properties );
    $sync_term = get_option( 'options_sync_term' );
    $data_sync = get_option( 'options_data_sync' );
    
    foreach( $properties as $property ) {
          
        // if syncing is paused or data dync is off, then then bail, as we won't be restarting anything
        if ( $sync_term == 'paused' || $data_sync == 'delete' || $data_sync == 'nosync' ) {
            // as_unschedule_action( 'rentfetch_do_fetch_yardi_floorplans', array( $property ), 'yardi' );
            // as_unschedule_all_actions( 'rentfetch_do_fetch_yardi_floorplans', array( $property ), 'yardi' );
            // rentfetch_verbose_log( "Sync term has changed for saving to CPT. Removing upcoming actions." );
            continue;
        }
                
        if ( as_next_scheduled_action( 'rentfetch_do_fetch_yardi_floorplans', array( $property ), 'yardi' ) == false ) {
            rentfetch_verbose_log( "Upcoming actions not found. Scheduling tasks $sync_term to save Yardi floorplans for property $property as posts." );    
            as_enqueue_async_action( 'rentfetch_do_fetch_yardi_floorplans', array( $property ), 'yardi' );
            as_schedule_recurring_action( time(), rentfetch_get_sync_term_in_seconds(), 'rentfetch_do_fetch_yardi_floorplans', array( $property ), 'yardi' );
        }
        
    }    
}

/**
 * For a particular property in Yardi, grab the transient, then start processing floorplans
 */
add_action( 'rentfetch_do_fetch_yardi_floorplans', 'rentfetch_fetch_yardi_floorplans', 10, 1 );
function rentfetch_fetch_yardi_floorplans( $property ) {
    
    $floorplans = get_transient( 'yardi_floorplans_property_id_' . $property );
            
    // bail if we do not have a transient with this data in it
    if ( $floorplans == false ) {
        rentfetch_verbose_log( "No transient currently set for property $property (transient should be named yardi_floorplans_property_id_$property), so we're ending the process." );
        return;
    }
        
    rentfetch_verbose_log( "Transient found for Yardi property $property (named yardi_floorplans_property_id_$property). Looping through data." );
    
    foreach( $floorplans as $floorplan ) {
        
        // save to cpt
        rentfetch_sync_yardi_floorplan_to_cpt( $floorplan, $property );
        
        // grab and insert the availability information
        rentfetch_get_availability_information( $floorplan, $property );
        
    }
    
    // Enable removing availability from orphan floorplans
    rentfetch_delete_orphan_yardi_floorplans( $floorplans, $property );
}

/**
 * For a particular floorplan, perform a sync
 */
function rentfetch_sync_yardi_floorplan_to_cpt( $floorplan, $voyagercode ) {
    
    $FloorplanId = $floorplan['FloorplanId'];
    $FloorplanName = $floorplan['FloorplanName'];
    
    // do a query to check and see if a post already exists with this ID 
    $args = array(
        'post_type' => 'floorplans',
        'meta_query' => array(
            array(
                'key' => 'floorplan_id',
                'value' => $FloorplanId,
                'compare' => '=',
            )
        )
    );
    $matchingposts = get_posts( $args );
    $count = count( $matchingposts );
    
    // insert the post if there isn't one already (this prevents duplicates)
    if ( !$matchingposts ) {
        rentfetch_verbose_log( "Floorplan $FloorplanId, $FloorplanName, does not exist yet in the database. Inserting." );
        rentfetch_insert_yardi_floorplan( $floorplan, $voyagercode );
        
    // if there's exactly one post found, then update the meta for that
    } elseif ( $count == 1 ) {
        rentfetch_verbose_log( "Floorplan $FloorplanId, $FloorplanName, already exists in the database. Checking post meta." );
        rentfetch_update_yardi_floorplan( $floorplan, $matchingposts, $voyagercode );
        
    // if there are more than one found, delete all of those that match and add fresh, since we likely have some bad data
    } elseif( $count > 1 ) {
        rentfetch_verbose_log( "$count posts for floorplan $FloorplanId found. Removing duplicates and reinserting fresh." );
        foreach ($matchingposts as $matchingpost) {
            wp_delete_post( $matchingpost->ID, true );
        }
        rentfetch_insert_yardi_floorplan( $floorplan, $voyagercode );
    }
}

/**
 * Insert a new floorplan into the database
 *
 * @param   array  $floorplan  provided from the Yardi transient
 *
 * @return  none              
 */
function rentfetch_insert_yardi_floorplan( $floorplan, $voyagercode ) {
    
    // all of the available variables
    $AvailabilityURL = $floorplan['AvailabilityURL'];
    $AvailableUnitsCount = $floorplan['AvailableUnitsCount'];
    $Baths = floatval( $floorplan['Baths'] );
    $Beds = floatval( $floorplan['Beds'] );
    $FloorplanHasSpecials = $floorplan['FloorplanHasSpecials'];
    $FloorplanId = $floorplan['FloorplanId'];
    $FloorplanImageAltText = $floorplan['FloorplanImageAltText'];
    $FloorplanImageName = $floorplan['FloorplanImageName'];
    $FloorplanImageURL = $floorplan['FloorplanImageURL'];
    $FloorplanName = $floorplan['FloorplanName'];
    $MaximumDeposit = $floorplan['MaximumDeposit'];
    $MaximumRent = $floorplan['MaximumRent'];
    $MaximumSQFT = $floorplan['MaximumSQFT'];
    $MinimumDeposit = $floorplan['MinimumDeposit'];
    $MinimumRent = $floorplan['MinimumRent'];
    $MinimumSQFT = $floorplan['MinimumSQFT'];
    $PropertyId = $floorplan['PropertyId'];
    $PropertyShowsSpecials = $floorplan['PropertyShowsSpecials'];
    $UnitTypeMapping = $floorplan['UnitTypeMapping'];
    $FloorplanSource = 'yardi'; // this one doesn't come from the API. This is our identifier that says "this caame from the API."
    
    // Create post object
    $floorplan_meta = array(
        'post_title'    => wp_strip_all_tags( $FloorplanName ),
        'post_status'   => 'publish',
        'post_type'     => 'floorplans',
        'meta_input'    => array(
            'availability_url'          => $AvailabilityURL,
            'available_units'           => $AvailableUnitsCount,
            'baths'                     => $Baths,
            'beds'                      => $Beds,
            'has_specials'              => $FloorplanHasSpecials,
            'floorplan_id'              => $FloorplanId,
            'floorplan_image_alt_text'  => $FloorplanImageAltText,
            'floorplan_image_name'      => $FloorplanImageName,
            'floorplan_image_url'       => $FloorplanImageURL,
            'maximum_deposit'           => $MaximumDeposit,
            'maximum_rent'              => $MaximumRent,
            'maximum_sqft'              => $MaximumSQFT,
            'minimum_deposit'           => $MinimumDeposit,
            'minimum_rent'              => $MinimumRent,
            'minimum_sqft'              => $MinimumSQFT,
            'property_id'               => $PropertyId,
            'voyager_property_code'     => $voyagercode,
            'property_show_specials'    => $PropertyShowsSpecials,
            'unit_type_mapping'         => $UnitTypeMapping,
            'floorplan_source'          => $FloorplanSource,
        ),
    );
    
    $post_id = wp_insert_post( $floorplan_meta );
    
}

/**
 * Update an individual Yardi floorplan in place
 *
 * @param   array  $floorplan      the floorplan data from the transient
 * @param   object  $matchingposts  an object comprised of posts from get_posts expected to contain only one post
 *
 * @return  none  
 */
function rentfetch_update_yardi_floorplan( $floorplan, $matchingposts, $voyagercode ) {
    
    // all of the available variables
    $AvailabilityURL = $floorplan['AvailabilityURL'];
    $AvailableUnitsCount = $floorplan['AvailableUnitsCount'];
    $Baths = floatval( $floorplan['Baths'] );
    $Beds = floatval( $floorplan['Beds'] );
    $FloorplanHasSpecials = $floorplan['FloorplanHasSpecials'];
    $FloorplanId = $floorplan['FloorplanId'];
    $FloorplanImageAltText = $floorplan['FloorplanImageAltText'];
    $FloorplanImageName = $floorplan['FloorplanImageName'];
    $FloorplanImageURL = $floorplan['FloorplanImageURL'];
    $FloorplanName = wp_strip_all_tags( $floorplan['FloorplanName'] );
    $MaximumDeposit = $floorplan['MaximumDeposit'];
    $MaximumRent = $floorplan['MaximumRent'];
    $MaximumSQFT = $floorplan['MaximumSQFT'];
    $MinimumDeposit = $floorplan['MinimumDeposit'];
    $MinimumRent = $floorplan['MinimumRent'];
    $MinimumSQFT = $floorplan['MinimumSQFT'];
    $PropertyId = $floorplan['PropertyId'];
    $PropertyShowsSpecials = $floorplan['PropertyShowsSpecials'];
    $UnitTypeMapping = $floorplan['UnitTypeMapping'];
    $FloorplanSource = 'yardi'; // this one doesn't come from the API. This is our identifier that says "this caame from the API."
    
    // Create post object
    $floorplan_meta = array(
        'post_title'    => wp_strip_all_tags( $FloorplanName ),
        'post_status'   => 'publish',
        'post_type'     => 'floorplans',
        'meta_input'    => array(
            'availability_url'          => $AvailabilityURL,
            'available_units'           => $AvailableUnitsCount,
            'baths'                     => $Baths,
            'beds'                      => $Beds,
            'has_specials'              => $FloorplanHasSpecials,
            'floorplan_id'              => $FloorplanId,
            'floorplan_image_alt_text'  => $FloorplanImageAltText,
            'floorplan_image_name'      => $FloorplanImageName,
            'floorplan_image_url'       => $FloorplanImageURL,
            'maximum_deposit'           => $MaximumDeposit,
            'maximum_rent'              => $MaximumRent,
            'maximum_sqft'              => $MaximumSQFT,
            'minimum_deposit'           => $MinimumDeposit,
            'minimum_rent'              => $MinimumRent,
            'minimum_sqft'              => $MinimumSQFT,
            'property_id'               => $PropertyId,
            'voyager_property_code'     => $voyagercode,
            'property_show_specials'    => $PropertyShowsSpecials,
            'unit_type_mapping'         => $UnitTypeMapping,
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
                rentfetch_log( "Floorplan $FloorplanId title updated: post_title is now $FloorplanName." );
            }
            
            // update post meta (NOTE: update_post_meta returns false if it doesn't update, true if it does)
            $success_availability_url = update_post_meta( $post_id, 'availability_url', $AvailabilityURL );
            if ( $success_availability_url == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: availability_url is now $AvailabilityURL." );
            
            $success_available_units = update_post_meta( $post_id, 'available_units', $AvailableUnitsCount );
            if ( $success_available_units == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: available_units is now $AvailableUnitsCount." );
            
            $success_baths = update_post_meta( $post_id, 'baths', $Baths );
            if ( $success_baths == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: baths is now $Baths." );
            
            $success_beds = update_post_meta( $post_id, 'beds', $Beds );
            if ( $success_beds == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: beds is now $Beds." );
                
            $success_has_specials = update_post_meta( $post_id, 'has_specials', $FloorplanHasSpecials );
            if ( $success_has_specials == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: has_specials is now $FloorplanHasSpecials." );
                                
            $success_floorplan_image_alt_text = update_post_meta( $post_id, 'floorplan_image_alt_text', $FloorplanImageAltText );
            if ( $success_floorplan_image_alt_text == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: floorplan_image_alt_text is now $FloorplanImageAltText." );
                
            $success_floorplan_image_name = update_post_meta( $post_id, 'floorplan_image_name', $FloorplanImageName );
            if ( $success_floorplan_image_name == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: floorplan_image_name is now $FloorplanImageName." );
                
            $success_floorplan_image_url = update_post_meta( $post_id, 'floorplan_image_url', $FloorplanImageURL );
            if ( $success_floorplan_image_url == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: floorplan_image_url is now $FloorplanImageURL." );
                
            $success_maximum_deposit = update_post_meta( $post_id, 'maximum_deposit', $MaximumDeposit );
            if ( $success_maximum_deposit == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: maximum_deposit is now $MaximumDeposit." );
                
            $success_maximum_rent = update_post_meta( $post_id, 'maximum_rent', $MaximumRent );
            if ( $success_maximum_rent == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: maximum_rent is now $MaximumRent." );
                
            $success_maximum_sqft = update_post_meta( $post_id, 'maximum_sqft', $MaximumSQFT );
            if ( $success_maximum_sqft == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: maximum_sqft is now $MaximumSQFT." );
                
            $success_minimum_deposit = update_post_meta( $post_id, 'minimum_deposit', $MinimumDeposit );
            if ( $success_minimum_deposit == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: minimum_deposit is now $MinimumDeposit." );
                
            $success_minimum_rent = update_post_meta( $post_id, 'minimum_rent', $MinimumRent );
            if ( $success_minimum_rent == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: minimum_rent is now $MinimumRent." );
                
            $success_minimum_sqft = update_post_meta( $post_id, 'minimum_sqft', $MinimumSQFT );
            if ( $success_minimum_sqft == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: minimum_sqft is now $MinimumSQFT." );
                
            $success_property_id = update_post_meta( $post_id, 'property_id', $PropertyId );
            if ( $success_property_id == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: property_id is now $PropertyId." );
                
            $success_property_show_specials = update_post_meta( $post_id, 'property_show_specials', $PropertyShowsSpecials );
            if ( $success_property_show_specials == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: property_show_specials is now $PropertyShowsSpecials." );
                
            $success_unit_type_mapping = update_post_meta( $post_id, 'unit_type_mapping', $UnitTypeMapping );
            if ( $success_unit_type_mapping == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: unit_type_mapping is now $UnitTypeMapping." );
                
            $success_floorplan_source = update_post_meta( $post_id, 'floorplan_source', $FloorplanSource );
            if ( $success_floorplan_source == true )
                rentfetch_log( "Floorplan $FloorplanId meta updated: floorplan_source is now $FloorplanSource." );
            
            
        }   
        
        wp_reset_postdata();
    }
    
}

// add_action( 'init', 'rentfetch_get_availability_information' );
function rentfetch_get_availability_information( $floorplan = 'hello', $voyager_property_code = 'world' ) {
    
    $yardi_integration_creds = get_option( 'options_yardi_integration_creds' );
    $yardi_api_key = get_option( 'options_yardi_integration_creds_yardi_api_key' );
    
    $floorplan_Id = $floorplan['FloorplanId'];
    
    // // //TODO TESTING
    // $voyager_property_code = '7200evan';
    // $floorplan_Id = '3957491';
                    
    // Do the API request
    $url = sprintf( 'https://api.rentcafe.com/rentcafeapi.aspx?requestType=apartmentavailability&floorplanId=%s&apiToken=%s&VoyagerPropertyCode=%s', $floorplan_Id, $yardi_api_key, $voyager_property_code ); // path to your JSON file
    $datas = file_get_contents( $url ); // put the contents of the file into a variable        
    
    // process the data to get the date in yardi's format
    $datas = json_decode( $datas );  
            
    $available_dates = array();
    $soonest_date = null;
    $today = date('Ymd');
    
    foreach( $datas as $data ) {
        
        if ( isset( $data->AvailableDate ) ) {
            
            $date = $data->AvailableDate;
            $date = date('Ymd', strtotime($date));
            $available_dates[] = $date;
        }            
    }
        
    sort( $available_dates );
        
    if ( isset( $available_dates[0] ) )
        $soonest_date = $available_dates[0];   
        
        
    // if the soonest date is before today, just set the available date to today
    if ( $soonest_date == null ) {
        $available_date = null;
    }
    elseif ( $today > $soonest_date ) {
        $available_date = $today;
    } else {
        $available_date = $soonest_date;
    }
    
    // query to find any posts for this floorplan
    $args = array(
        'post_type' => 'floorplans',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'relation' => 'AND',
                array(
                    array(
                        'key' => 'floorplan_source',
                        'value' => 'yardi',
                    ),
                    array(
                        'key'   => 'floorplan_id',
                        'value' => $floorplan_Id,
                    ),
                ),
            ),
        ),
    );
    
    $floorplan_query = new WP_Query( $args );
    $floorplans = $floorplan_query->posts;
        
    if ( $floorplans ) {
        foreach( $floorplans as $floorplan ) {   
            $success_update_photos = update_post_meta( $floorplan->ID, 'availability_date', $available_date );
        }
    }
}

// add_action( 'init', 'rentfetch_delete_orphan_yardi_floorplans' ); // for testing only
function rentfetch_delete_orphan_yardi_floorplans( $floorplans, $property ) {
// function rentfetch_delete_orphan_yardi_floorplans( $floorplans ) {
    
    // // sample data for testing
    // $property = '175wbell';
    // $floorplans = get_transient( 'yardi_floorplans_property_id_' . $property );
    
    //* get a list of floorplans that show up in the API
    $floorplan_ids_from_api = array();
    foreach( $floorplans as $floorplan ) {
        $floorplan_ids_from_api[] = $floorplan[ 'FloorplanId' ];
    }
    
    //* get a list of floorplans currently in WordPress
    $floorplan_query_args = array(
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
        
    $floorplans_in_wordpress = get_posts( $floorplan_query_args );
        
    // //* Testing
    // echo 'From API: <br/>';
    // var_dump( $floorplan_ids_from_api );
    
    // echo 'From WordPress: <br/>';
    // foreach( $floorplans_in_wordpress as $floorplan_in_wordpress ) {
    //     $floorplan_id_in_wordpress = get_post_meta( $floorplan_in_wordpress->ID, 'floorplan_id', true );
    //     echo $floorplan_id_in_wordpress . '<br/>';
    // }
    
    //* loop through each of those in WordPress and delete any that aren't in the API
    foreach ( $floorplans_in_wordpress as $floorplan_in_wordpress ) {
        $floorplan_id_in_wordpress = get_post_meta( $floorplan_in_wordpress->ID, 'floorplan_id', true );
        $floorplan_ids_in_wordpress[] = $floorplan_id_in_wordpress;
        
        if ( !in_array( $floorplan_id_in_wordpress, $floorplan_ids_from_api ) ) {
            
            $success_floorplan_no_date = update_post_meta( $floorplan_in_wordpress->ID, 'availability_date', null );
            $success_floorplan_no_units = update_post_meta( $floorplan_in_wordpress->ID, 'available_units', '0' );
            
            rentfetch_log( "Removed availability information from WordPress post ID $floorplan_in_wordpress->ID (property No. $floorplan_in_wordpress->property_id, floorplan No. $floorplan_in_wordpress->floorplan_id), as this floorplan no longer appears in the Yardi API." );
        }
    }
        
}
