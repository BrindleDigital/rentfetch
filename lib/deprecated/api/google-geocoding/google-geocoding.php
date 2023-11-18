<?php 

/**
 * Check if there's anything needing geocoded
 */
add_action( 'rentfetch_do_geocode', 'rentfetch_geocode' );
function rentfetch_geocode() {
    
    $google_geocoding_api_key = get_option( 'rentfetch_options_google_geocoding_api_key' );
    
    // bail if we don't have an API key, because then we won't be able to geocode anyway
    if ( !$google_geocoding_api_key )
        return;
    
    $args = array(
        'post_type' => 'properties',
        'posts_per_page' => '-1',
        'fields' => 'ids',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'latitude',
                'compare' => '<',
                'value' => '1',
            ),
            array(
                'key' => 'longitude',
                'compare' => '<',
                'value' => '1',
            ),
        ),
    );
    
    $posts = get_posts( $args );
    
    // bail if we don't have anything needing geocoded
    if ( !$posts )
        return;
    
    foreach( $posts as $post_id ) {
        if ( as_has_scheduled_action( 'rentfetch_geocoding_get_lat_long', array( $post_id ), 'rentfetch_geocoding' ) == false )
            as_enqueue_async_action( 'rentfetch_geocoding_do_get_lat_long', array( $post_id ), 'rentfetch_geocoding' );
    }
    
}

// add_action( 'init', 'test_geocoding' );
function test_geocoding() {
    $post_id = 2671;
    rentfetch_geocoding_get_lat_long( $post_id );
}

add_action( 'rentfetch_geocoding_do_get_lat_long', 'rentfetch_geocoding_get_lat_long' );
function rentfetch_geocoding_get_lat_long( $post_id ) {
        
    $google_geocoding_api_key = get_option( 'rentfetch_options_google_geocoding_api_key' );
        
    // bail if there's no maps api key set
    if ( !$google_geocoding_api_key )
        return;
                
    // get the address from the post
    $street = get_post_meta( $post_id, 'address', true );
    $city = get_post_meta( $post_id, 'city', true );
    $state = get_post_meta( $post_id, 'state', true );
    $zipcode = get_post_meta( $post_id, 'zipcode', true );
    
    if ( !$street || !$city || !$state || !$zipcode )
        return;
    
    $address = sprintf( '%s %s, %s %s', $street, $city, $state, $zipcode );
        
    // url encode the address
    $address = urlencode( $address );
      
    // google map geocode api url
    $url = sprintf( 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s', $address, $google_geocoding_api_key );
  
    // get the json response
    $resp_json = file_get_contents($url);
      
    // decode the json
    $resp = json_decode($resp_json, true);
  
    // response status will be 'OK', if able to geocode given address 
    if($resp['status']=='OK'){
  
        // get the important data
        $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
        $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";
        $formatted_address = isset($resp['results'][0]['formatted_address']) ? $resp['results'][0]['formatted_address'] : "";
          
        // verify if data is complete
        if( $lati && $longi && $formatted_address){
          
            // put the data in the array
            $location_data = array();            
              
            array_push(
                $location_data, 
                    $lati, 
                    $longi, 
                    $formatted_address
                );
              
            rentfetch_geocoding_save_lat_long( $post_id, $location_data );
                          
        }else{
            
            $location_data = array( 
                'error geocoding (failed to find the address), delete latitude and longitude to automatically try again',
                'error geocoding (failed to find the address), delete latitude and longitude to automatically try again',
            );
            
            rentfetch_geocoding_save_lat_long( $post_id, $location_data );
        }
          
    }
  
    else{
        
        $location_data = array( 
            'error geocoding (likely API key invalid), delete latitude and longitude to automatically try again',
            'error geocoding (likely API key invalid), delete latitude and longitude to automatically try again',
        );
        
       rentfetch_geocoding_save_lat_long( $post_id, $location_data );
    }
}

function rentfetch_geocoding_save_lat_long( $post_id, $location_data ) {

    $success = update_post_meta( $post_id, 'latitude', $location_data[0] );
    $success = update_post_meta( $post_id, 'longitude', $location_data[1] );
    
}
