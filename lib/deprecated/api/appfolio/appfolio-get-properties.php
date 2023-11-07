<?php

/**
 * Get the properties from AppFolio and save them to a transient
 */
add_action( 'rentfetch_do_get_properties_appfolio', 'rentfetch_get_properties_appfolio' );
// add_action( 'wp_footer', 'rentfetch_get_properties_appfolio' );
// add_action( 'admin_footer', 'rentfetch_get_properties_appfolio' );
function rentfetch_get_properties_appfolio() {
    
    // bail if credentials haven't been completed fully
    if ( rentfetch_check_creds_appfolio() == false )
        return;
                
    // check to see if there's already a transient set
    $properties = get_transient( 'appfolio_properties' );
    
    // bail if we already have a transient
    if ( $properties )
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
            'https://%s:%s@%s.appfolio.com/api/v1/reports/property_directory.json?paginate_results=false', 
            $appfolio_client_id,
            $appfolio_client_secret,  
            $appfolio_database_name,  
        );
    } else {
        // if we do have a list of property ids
        $curlopt_url = sprintf( 
            'https://%s:%s@%s.appfolio.com/api/v1/reports/property_directory.json?paginate_results=false&properties=%s', 
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
    
    set_transient( 'appfolio_properties', $responseArray, 900 );
    
    // if ( !$appfolio_property_ids ) {
        
    //     // if there's no property ID set, the API returns the results inside [results]
    //     set_transient( 'appfolio_properties', $responseArray, HOUR_IN_SECONDS );
        
    // } else {
        
    //     // if there's property IDs set, the API returns the results without other information
    //     set_transient( 'appfolio_properties', $responseArray, HOUR_IN_SECONDS );
        
    // }
    
}

/**
 * Using the saved transient with all data, figure out whether to save or update each property, then trigger that
 */
add_action( 'rentfetch_do_save_appfolio_properties_to_cpt', 'rentfetch_check_appfolio_property_for_saving_or_syncing' );
// add_action( 'wp_footer', 'rentfetch_check_appfolio_property_for_saving_or_syncing' );
// add_action( 'admin_footer', 'rentfetch_check_appfolio_property_for_saving_or_syncing' );
function rentfetch_check_appfolio_property_for_saving_or_syncing() {
    
    $properties = get_transient( 'appfolio_properties' );
    
    if ( !$properties )
        return;
        
    foreach( $properties as $property ) {

        // skip any properties without ID numbers
        if ( !isset( $property['PropertyId'] ) )
            continue;
            
        $property_id = intval( $property['PropertyId'] );
        
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
                            'value' => 'appfolio',
                        ),
                        array(
                            'key'   => 'property_id',
                            'value' => $property_id,
                        ),
                    ),
                ),
            ),
        );
        
        $property_query = new WP_Query( $args );
        $property_posts = $property_query->posts;    
        $count = count( $property_posts );
        
        // if there's no post, then add one
        if ( $count === 0 )
            do_action( 'rentfetch_do_insert_appfolio_property', $property );
        
        // if there's a post, update it
        if ( $count === 1 )
            do_action( 'rentfetch_do_update_appfolio_property', $property );
        
        // if we somehow got multiple posts, delete them
        if ( $count > 1 ) {
            foreach( $property_posts as $property_post ) {
                wp_delete_post( $property_post->ID, true );
            }
        }
    }
}

/**
 * Save a brand new property, for stuff that's not already in the database
 */
add_action( 'rentfetch_do_insert_appfolio_property', 'rentfetch_insert_appfolio_property', 10, 1 );
function rentfetch_insert_appfolio_property( $property ) {
    
    $title = esc_html( $property['PropertyName'] );
    $address = esc_html($property['PropertyStreet1'] );
    $city = esc_html( $property['PropertyCity'] );
    $state = esc_html( $property['PropertyState'] );
    $zipcode = esc_html( $property['PropertyZip'] );
    // $url = $property['url'];
    $description = esc_html( $property['Description'] );
    // $email = $property['email'];
    $phone = esc_html( $property['SiteManagerPhoneNumber'] );
    // $latitude = $property['Latitude'];
    // $longitude = $property['Longitude'];
    // $propertycode = $property['PropertyCode'];
    $property_id = esc_html( $property['PropertyId'] );
    // $voyager_property_code = $property['VoyagerPropertyCode'];
    $property_source = 'appfolio';
        
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
            // 'url'                   => $url,
            'description'           => $description,
            // 'email'                 => $email,
            'phone'                 => $phone,
            // 'latitude'              => $latitude,
            // 'longitude'             => $longitude,
            // 'property_code'         => $propertycode,
            // 'voyager_property_code' => $voyager_property_code,
            'property_id'           => $property_id,
            'property_source'       => $property_source,
        ),
    );
    
    // insert the post
    $post_id = wp_insert_post( $property_meta );
    
} 

/**
 * Update an existing property, if we already have one
 */
add_action( 'rentfetch_do_update_appfolio_property', 'rentfetch_update_appfolio_property', 10, 1 );
function rentfetch_update_appfolio_property( $property ) {
    
    $property_id = esc_html( $property['PropertyId'] );
    
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
                        'value' => 'appfolio',
                    ),
                    array(
                        'key'   => 'property_id',
                        'value' => $property_id,
                    ),
                ),
            ),
        ),
    );
    
    $property_query = new WP_Query( $args );
    $post_properties = $property_query->posts;
    $post_id = $post_properties[0]->ID;
    
    // deal with the amenities
    wp_delete_object_term_relationships( $post_id, array( 'amenities' ) );

    // get the array of amenities
    $amenities = esc_html( $property['Amenities'] );
    $amenities = explode( ', ', $amenities );
        
    // for each of those amenities, grab the name, then add it
    foreach ( $amenities as $amenity ) {
                        
        // this function checks if the amenity exists, creates it if not, then adds it to the post
        rentfetch_set_post_term( $post_id, $amenity, 'amenities' );
        
    }
    
    // update the title if needed
    //* Update the title
    $post_info = array(
        'ID' => $post_id,
        'post_title' => esc_html( $property['PropertyName'] ),
    );
    
    wp_update_post( $post_info );
    
    // get the meta
    $property_meta = [
        'address' => esc_html( $property['PropertyStreet1'] ),
        'city' => esc_html( $property['PropertyCity'] ),
        'state' => esc_html( $property['PropertyState'] ),
        'zipcode' => esc_html( $property['PropertyZip'] ),
        'description' => esc_html( $property['Description'] ),
        'phone' => esc_html( $property['SiteManagerPhoneNumber'] ),
        'property_id' => esc_html( $property['PropertyId'] ),
    ];
    
    // update the meta
    foreach( $property_meta as $key => $value ) {
        $success = update_post_meta( $post_id, $key, $value );
    }
    
    // var_dump( $property );
}