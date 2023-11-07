<?php

add_action( 'rentfetch_do_get_properties_yardi', 'rentfetch_get_properties_yardi' );
function rentfetch_get_properties_yardi() {            
    
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
    $site_type = get_option( 'options_apartment_site_type' );    
        
    foreach( $properties as $property ) {
            
        // if syncing is paused or data dync is off or this is a single property, then bail, as we won't be restarting anything
        if ( $sync_term == 'paused' || $data_sync == 'delete' || $data_sync == 'nosync' || $site_type == 'single' ) {
            as_unschedule_action( 'rentfetch_do_get_yardi_property_from_api', array( $property, $yardi_api_key ), 'yardi' );
            as_unschedule_all_actions( 'rentfetch_do_get_yardi_property_from_api', array( $property, $yardi_api_key ), 'yardi' );
            rentfetch_verbose_log( "Sync term has changed for pulling property from API. Removing upcoming actions." );
            continue;
        }
                        
        if ( as_next_scheduled_action( 'rentfetch_do_get_yardi_property_from_api', array( $property, $yardi_api_key ), 'yardi' ) == false ) {
            rentfetch_verbose_log( "Upcoming actions not found. Scheduling tasks $sync_term to get Yardi property $property from API." );    
            as_enqueue_async_action( 'rentfetch_do_get_yardi_property_from_api', array( $property, $yardi_api_key ), 'yardi' );
            as_schedule_recurring_action( time(), rentfetch_get_sync_term_in_seconds(), 'rentfetch_do_get_yardi_property_from_api', array( $property, $yardi_api_key ), 'yardi' );
        }            
    }
}

// add_action( 'init', 'test_funct' );
// function test_funct() {
        
//     $voyager_id = '1002univ';
//     $yardi_api_key = '532b316d-fbcb-480c-b1bd-481fbe699360';
    
//     do_action( 'test_act', $voyager_id, $yardi_api_key );

// }


//* Get the property using the Properties API
// add_action( 'test_act', 'get_yardi_property_from_api', 10, 2 );
add_action( 'rentfetch_do_get_yardi_property_from_api', 'get_yardi_property_from_api', 10, 2 );
function get_yardi_property_from_api( $voyager_id, $yardi_api_key ) {
   
    rentfetch_verbose_log( "Pulling property data for $voyager_id from yardi API." );
            
    // Do the API request
    $url = sprintf( 'https://api.rentcafe.com/rentcafeapi.aspx?requestType=property&type=marketingData&apiToken=%s&VoyagerPropertyCode=%s', $yardi_api_key, $voyager_id ); // path to your JSON file
    
    $data = file_get_contents( $url ); // put the contents of the file into a variable        
    $propertydata = json_decode( $data, true ); // decode the JSON feed
    $errorcode = null;
        
    if ( !$errorcode && !empty( $propertydata ) ) {
        rentfetch_verbose_log( "Yardi returned property data for property $voyager_id successfully. New transient set: yardi_property_id_$voyager_id" );                
        
        do_action( 'rentfetch_do_save_property_data_to_cpt', $propertydata );
        
    } elseif( !$errorcode && empty( $propertydata ) ) {
        rentfetch_log( "No property data received from Yardi for property $voyager_id." );
    } else {
        rentfetch_log( "Property API query: Yardi returned error code $errorcode for property $voyager_id." );
    }
    
}

//* Get the property from the Images API and update the property
// add_action( 'test_act', 'get_yardi_property_images_from_api', 10, 2 );
add_action( 'rentfetch_do_get_yardi_property_from_api', 'get_yardi_property_images_from_api', 10, 2 );
function get_yardi_property_images_from_api( $voyager_id, $yardi_api_key ) {
                
    // Do the API request
    $url = sprintf( 'https://api.rentcafe.com/rentcafeapi.aspx?requestType=images&type=propertyImages&apiToken=%s&VoyagerPropertyCode=%s', $yardi_api_key, $voyager_id ); // path to your JSON file
    $propertydata = file_get_contents( $url ); // put the contents of the file into a variable        
        
    if ( empty( $propertydata ) )
        return;    
        
    // var_dump( $propertydata );
    
    // query to find out if there's already a post for this property
    $args = array(
        'post_type' => 'properties',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'relation' => 'AND',
                array(
                    array(
                        'key' => 'property_source',
                        'value' => 'yardi',
                    ),
                    array(
                        'key'   => 'voyager_property_code',
                        'value' => $voyager_id,
                    ),
                ),
            ),
        ),
    );
    
    $property_query = new WP_Query( $args );
    $properties = $property_query->posts;
    
    if ( $properties ) {
        foreach( $properties as $property ) {
            $success_update_photos = update_post_meta( $property->ID, 'property_images', $propertydata );
        }
    }
}

add_action( 'rentfetch_do_save_property_data_to_cpt', 'rentfetch_save_property_data_to_cpt', 10, 1 );
function rentfetch_save_property_data_to_cpt( $property_data ) {
    
    $property_data = $property_data[0];
    
    // bail if there's no property data
    if ( !isset( $property_data['PropertyData'] ) )
        return;

    // bail if there's no voyager property code
    if ( !isset( $property_data['PropertyData']['VoyagerPropertyCode'] ) )
        return;
        
    $voyager_property_code = $property_data['PropertyData']['VoyagerPropertyCode'];
            
    // query to find out if there's already a post for this property
    $args = array(
        'post_type' => 'properties',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'relation' => 'AND',
                array(
                    array(
                        'key' => 'property_source',
                        'value' => 'yardi',
                    ),
                    array(
                        'key'   => 'voyager_property_code',
                        'value' => $voyager_property_code,
                    ),
                ),
            ),
        ),
    );
    
    $property_query = new WP_Query( $args );
    $properties = $property_query->posts;    
    $count = count( $properties );
    
    // if there's no post, then add one
    if ( $count === 0 )
        do_action( 'rentfetch_do_insert_yardi_property', $property_data );
    
    // if there's a post, update it
    if ( $count === 1 )
        do_action( 'rentfetch_do_update_yardi_property', $property_data );
    
    // if we somehow got multiple posts, delete them
    if ( $count > 1 ) {
        foreach( $properties as $property ) {
            wp_delete_post( $property->ID, true );
        }
    }
}

add_action( 'rentfetch_do_insert_yardi_property', 'rentfetch_insert_yardi_property', 10, 1 );
function rentfetch_insert_yardi_property( $property_data ) {
            
    $title = $property_data['PropertyData']['name'];
    $address = $property_data['PropertyData']['address'];
    $city = $property_data['PropertyData']['city'];
    $state = $property_data['PropertyData']['state'];
    $zipcode = $property_data['PropertyData']['zipcode'];
    $url = $property_data['PropertyData']['url'];
    $description = $property_data['PropertyData']['description'];
    $email = $property_data['PropertyData']['email'];
    $phone = $property_data['PropertyData']['phone'];
    $latitude = $property_data['PropertyData']['Latitude'];
    $longitude = $property_data['PropertyData']['Longitude'];
    $propertycode = $property_data['PropertyData']['PropertyCode'];
    $property_id = $property_data['PropertyData']['PropertyId'];
    $voyager_property_code = $property_data['PropertyData']['VoyagerPropertyCode'];
    $property_source = 'yardi';
        
    //* bail if we don't have a title
    if ( !$title )
        return;
    
    // Create post object
    $property_meta = array(
        'post_title'  => wp_strip_all_tags( $title ),
        'post_status' => 'publish',
        'post_type'   => 'properties',
        'meta_input'  => array(
            'address'               => $address,
            'city'                  => $city,
            'state'                 => $state,
            'zipcode'               => $zipcode,
            'url'                   => $url,
            'description'           => $description,
            'email'                 => $email,
            'phone'                 => $phone,
            'latitude'              => $latitude,
            'longitude'             => $longitude,
            'property_code'         => $propertycode,
            'voyager_property_code' => $voyager_property_code,
            'property_id'           => $property_id,
            'property_source'       => $property_source,
        ),
    );
    
    // insert the post
    $post_id = wp_insert_post( $property_meta );

}

add_action( 'rentfetch_do_update_yardi_property', 'rentfetch_update_yardi_property', 10, 1 );
function rentfetch_update_yardi_property( $property_data ) {
    
    $voyager_property_code = $property_data['PropertyData']['VoyagerPropertyCode'];
        
    // this function doesn't have access to the post ID it's updating, so let's get that by querying the DB
    $args = array(
        'post_type' => 'properties',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'relation' => 'AND',
                array(
                    array(
                        'key' => 'property_source',
                        'value' => 'yardi',
                    ),
                    array(
                        'key'   => 'voyager_property_code',
                        'value' => $voyager_property_code,
                    ),
                ),
            ),
        ),
    );
    
    $property_query = new WP_Query( $args );
    $properties = $property_query->posts;
    $post_id = $properties[0]->ID;
    
    //* Amenities
    // now that we have the post ID, remove existing amenities
    wp_delete_object_term_relationships( $post_id, array( 'amenities' ) );

    // get the array of amenities
    $amenities = $property_data['Amenities'];
        
    // for each of those amenities, grab the name, then add it
    foreach ( $amenities as $amenity ) {
        
        // get the name
        $name = $amenity['CustomAmenityName'];
                
        // this function checks if the amenity exists, creates it if not, then adds it to the post
        rentfetch_set_post_term( $post_id, $name, 'amenities' );
    }
    
    //* Pets
    if ( !isset( $property_data['PetPolicy'] ) )
        return;
        
    if ( !isset( $property_data['PetPolicy'][0] ) )
        return;
        
    if ( !isset( $property_data['PetPolicy'][0]['PetType'] ) )
        return;
    
    $pets = $property_data['PetPolicy'][0]['PetType'];
        
    $success_pets = update_post_meta( $post_id, 'pets', $pets );
    if ( $success_pets == true )
        rentfetch_log( "Property $voyager_property_code meta updated: pets is now $pets." );
        
        
    //* Update the title
    $post_info = array(
        'ID' => $post_id,
        'post_title' => $property_data['PropertyData']['name'],
    );
    
    wp_update_post( $post_info );
    
    //* Update the meta
    $address = $property_data['PropertyData']['address'];
    $city = $property_data['PropertyData']['city'];
    $state = $property_data['PropertyData']['state'];
    $zipcode = $property_data['PropertyData']['zipcode'];
    $url = $property_data['PropertyData']['url'];
    $description = $property_data['PropertyData']['description'];
    $email = $property_data['PropertyData']['email'];
    $phone = $property_data['PropertyData']['phone'];
    $latitude = $property_data['PropertyData']['Latitude'];
    $longitude = $property_data['PropertyData']['Longitude'];
    $property_code = $property_data['PropertyData']['PropertyCode'];
    $property_id = $property_data['PropertyData']['PropertyId'];
    $voyager_property_code = $property_data['PropertyData']['VoyagerPropertyCode'];
    
    $success_address = update_post_meta( $post_id, 'address', $address );
    if ( $success_address == true )
        rentfetch_log( "Property $voyager_property_code meta updated: address is now $address." );
        
    $success_city = update_post_meta( $post_id, 'city', $city );
    if ( $success_city == true )
        rentfetch_log( "Property $voyager_property_code meta updated: city is now $city." );
        
    $success_state = update_post_meta( $post_id, 'state', $state );
    if ( $success_state == true )
        rentfetch_log( "Property $voyager_property_code meta updated: state is now $state." );
        
    $success_zipcode = update_post_meta( $post_id, 'zipcode', $zipcode );
    if ( $success_zipcode == true )
        rentfetch_log( "Property $voyager_property_code meta updated: zipcode is now $zipcode." );
        
    $success_url = update_post_meta( $post_id, 'url', $url );
    if ( $success_url == true )
        rentfetch_log( "Property $voyager_property_code meta updated: url is now $url." );
        
    $success_description = update_post_meta( $post_id, 'description', $description );
    if ( $success_description == true )
        rentfetch_log( "Property $voyager_property_code meta updated: description is now $description." );
        
    $success_email = update_post_meta( $post_id, 'email', $email );
    if ( $success_email == true )
        rentfetch_log( "Property $voyager_property_code meta updated: email is now $email." );
        
    $success_phone = update_post_meta( $post_id, 'phone', $phone );
    if ( $success_phone == true )
        rentfetch_log( "Property $voyager_property_code meta updated: phone is now $phone." );
        
    $success_latitude = update_post_meta( $post_id, 'latitude', $latitude );
    if ( $success_latitude == true )
        rentfetch_log( "Property $voyager_property_code meta updated: latitude is now $latitude." );
        
    $success_longitude = update_post_meta( $post_id, 'longitude', $longitude );
    if ( $success_longitude == true )
        rentfetch_log( "Property $voyager_property_code meta updated: longitude is now $longitude." );
        
    $success_property_code = update_post_meta( $post_id, 'property_code', $property_code );
    if ( $success_property_code == true )
        rentfetch_log( "Property $voyager_property_code meta updated: property_code is now $property_code." );
        
    $success_property_id = update_post_meta( $post_id, 'property_id', $property_id );
    if ( $success_property_id == true )
        rentfetch_log( "Property $voyager_property_code meta updated: property_id is now $property_id." );
        
    $success_city = update_post_meta( $post_id, 'city', $city );
    if ( $success_city == true )
        rentfetch_log( "Property $voyager_property_code meta updated: city is now $city." );
        
    $success_city = update_post_meta( $post_id, 'city', $city );
    if ( $success_city == true )
        rentfetch_log( "Property $voyager_property_code meta updated: city is now $city." );
        
}