<?php

add_action( 'rentfetch_do_get_floorplans_realpage', 'rentfetch_get_floorplans_realpage' );
// add_action( 'wp_footer', 'rentfetch_get_floorplans_realpage' );
// add_action( 'admin_footer', 'rentfetch_get_floorplans_realpage' );
function rentfetch_get_floorplans_realpage() {
        
    // bail if credentials haven't been completed fully
    if ( rentfetch_check_creds_realpage() == false )
        return;
            
    $realpage_user = get_option( 'rentfetch_options_realpage_integration_creds_realpage_user' );
    $realpage_pass = get_option( 'rentfetch_options_realpage_integration_creds_realpage_pass' );
    $realpage_pmc_id = get_option( 'rentfetch_options_realpage_integration_creds_realpage_pmc_id' );
    $realpage_site_ids = get_option( 'rentfetch_options_realpage_integration_creds_realpage_site_ids' );
    
    // remove all whitespace from $realpage_site_ids
    $realpage_site_ids = preg_replace('/\s+/', '', $realpage_site_ids);
    $realpage_site_ids = explode( ',', $realpage_site_ids );
        
    foreach( $realpage_site_ids as $realpage_site_id ) {
                
        do_action( 'rentfetch_do_get_realpage_floorplans_from_api_save_transient', $realpage_site_id );
        do_action( 'rentfetch_do_save_realpage_floorplans_to_cpt', $realpage_site_id );
        
    }

}

/**
 * Check if there's already a transient saved for the floorplan
 * If not, get the floorplan from the RealPage API
 * Save the floorplan as a transient
 * 
 */
add_action( 'rentfetch_do_get_realpage_floorplans_from_api_save_transient', 'rentfetch_save_transient_realpage_floorplans', 10, 1 );
function rentfetch_save_transient_realpage_floorplans( $realpage_site_id ) {
    
    // check and see if there's a transient already
    $floorplans = get_transient( 'realpage_floorplans_site_id_' . $realpage_site_id );
    
    // bail if we have a transient already (don't need to grab it more than once an hour)
    if ( $floorplans )
        return;
    
    $realpage_integration_creds = get_option( 'rentfetch_options_realpage_integration_creds' );
    
    
    $realpage_user = get_option( 'rentfetch_options_realpage_integration_creds_realpage_user' );
    $realpage_pass = get_option( 'rentfetch_options_realpage_integration_creds_realpage_pass' );
    $realpage_pmc_id = get_option( 'rentfetch_options_realpage_integration_creds_realpage_pmc_id' );
            
    $curl = curl_init();
    
    $xml = sprintf(
        '<?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Header>
                <UserAuthInfo xmlns="http://realpage.com/webservices">
                <UserName>%s</UserName>
                <Password>%s</Password>
                <SiteID>%s</SiteID>
                <PmcID>%s</PmcID>
                <InternalUser>1</InternalUser>
                </UserAuthInfo>
            </soap12:Header>
            <soap12:Body>
                <List xmlns="http://realpage.com/webservices">
                    <!-- removed information here from the sample request -->
                </List>
            </soap12:Body>
        </soap12:Envelope>',
        $realpage_user,
        $realpage_pass,
        $realpage_site_id,
        $realpage_pmc_id,
    );

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://Onesite.RealPage.com/WebServices/CrossFire/AvailabilityAndPricing/Floorplan.asmx',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $xml,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/soap+xml; charset=utf-8'
        ),
    ));

    $response = curl_exec($curl);
    
    // SimpleXML seems to have problems with the colon ":" in the <xxx:yyy> response tags, so take them out
    $xml = preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$2$3', $response);
    $xml = simplexml_load_string($xml);
    $json = json_encode($xml);
    $responseArray = json_decode($json,true);
    $floorplans = $responseArray['soapBody']['ListResponse']['ListResult']['FloorPlanObject'];
    
    set_transient( 'realpage_floorplans_site_id_' . $realpage_site_id, $floorplans, HOUR_IN_SECONDS );
    
}

/**
 * Grab the transient
 * Process each floorplan
 */
add_action( 'rentfetch_do_save_realpage_floorplans_to_cpt', 'rentfetch_save_realpage_floorplans_to_cpt', 10, 1 );
function rentfetch_save_realpage_floorplans_to_cpt( $realpage_site_id ) {
    
    $floorplans = get_transient( 'realpage_floorplans_site_id_' . $realpage_site_id );
    
    // bail if we don't have any data to work with
    if ( !$floorplans )
        return;
        
    foreach( $floorplans as $floorplan ) {
        
        //* check and see if it already exists
        $args = array(
            'post_type' => 'floorplans',
            'meta_query' => array(
                array(
                    'key' => 'floorplan_id',
                    'value' => $floorplan['FloorPlanID'],
                    'compare' => '=',
                ),
                array(
                    'key' => 'property_id',
                    'value' => $realpage_site_id,
                    'compare' => '=',
                )
            )
        );
        $matchingposts = get_posts( $args );
        $count = count( $matchingposts );
        
        
        if ( !$matchingposts) {
            
            // if there's nothing found, save to cpt
            do_action( 'rentfetch_do_save_realpage_floorplan_to_cpt', $floorplan, $realpage_site_id );
            
        } elseif( $count == 1 ) {
            
            // if there's exactly one, then update the cpt
            do_action( 'rentfetch_do_update_realpage_floorplan_to_cpt', $floorplan, $realpage_site_id, $matchingposts );
            
        } elseif( $count > 1 ) {
            
            // if there's more than one, then delete them
            foreach ($matchingposts as $matchingpost) {
                wp_delete_post( $matchingpost->ID, true );
            }
            
            // then re-add fresh
            do_action( 'rentfetch_do_save_realpage_floorplan_to_cpt', $floorplan, $realpage_site_id );
            
        }
        
    }
    
}

/**
 * Fresh addition of a floorplan that doesn't already exist
 */
add_action( 'rentfetch_do_save_realpage_floorplan_to_cpt', 'rentfetch_save_realpage_floorplan_to_cpt', 10, 2 );
function rentfetch_save_realpage_floorplan_to_cpt( $floorplan, $realpage_site_id ) {
    
    //! $AvailabilityURL = 
    //! $AvailableUnitsCount = 
    $Baths = floatval( $floorplan['Bathrooms'] );
    $Beds = floatval( $floorplan['Bedrooms'] );
    //! $FloorplanHasSpecials = ;
    $FloorplanId = $floorplan['FloorPlanID'];
    //! $FloorplanImageAltText = ;
    //! $FloorplanImageName = ;
    //! $FloorplanImageURL = ;
    $FloorplanName = wp_strip_all_tags( $floorplan['FloorPlanNameMarketing'] );
    //! $MaximumDeposit = 
    $MaximumRent = floatval( $floorplan['RentMax'] );
    $MaximumSQFT = floatval( $floorplan['GrossSquareFootage'] );
    //! $MinimumDeposit = 
    $MinimumRent = floatval( $floorplan['RentMin'] );
    $MinimumSQFT = floatval( $floorplan['RentableSquareFootage'] );
    //! $PropertyId = 
    //! $PropertyShowsSpecials = 
    //! $UnitTypeMapping = 
    $FloorplanSource = 'realpage';
    
    // Create post object
    $floorplan_meta = array(
        'post_title'    => wp_strip_all_tags( $FloorplanName ),
        'post_status'   => 'publish',
        'post_type'     => 'floorplans',
        'meta_input'    => array(
            // 'availability_url'          => $AvailabilityURL,
            // 'available_units'           => $AvailableUnitsCount,
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
            'property_id'               => $realpage_site_id,
            // 'voyager_property_code'     => $voyagercode,
            // 'property_show_specials'    => $PropertyShowsSpecials,
            // 'unit_type_mapping'         => $UnitTypeMapping,
            'floorplan_source'          => $FloorplanSource,
        ),
    );
    
    $post_id = wp_insert_post( $floorplan_meta );
    
}

/**
 * Updating an existing floorplan in place with new data
 */
add_action( 'rentfetch_do_update_realpage_floorplan_to_cpt', 'rentfetch_do_update_realpage_floorplan_to_cpt', 10, 3 );
function rentfetch_do_update_realpage_floorplan_to_cpt( $floorplan, $realpage_site_id, $matchingposts ) {
    
    //! $AvailabilityURL = 
    //! $AvailableUnitsCount = 
    $Baths = floatval( $floorplan['Bathrooms'] );
    $Beds = floatval( $floorplan['Bedrooms'] );
    //! $FloorplanHasSpecials = ;
    $FloorplanId = $realpage_site_id . '_' . $floorplan['FloorPlanID'];
    //! $FloorplanImageAltText = ;
    //! $FloorplanImageName = ;
    //! $FloorplanImageURL = ;
    $FloorplanName = wp_strip_all_tags( $floorplan['FloorPlanNameMarketing'] );
    //! $MaximumDeposit = 
    $MaximumRent = floatval( $floorplan['RentMax'] );
    $MaximumSQFT = floatval( $floorplan['GrossSquareFootage'] );
    //! $MinimumDeposit = 
    $MinimumRent = floatval( $floorplan['RentMin'] );
    $MinimumSQFT = floatval( $floorplan['RentableSquareFootage'] );
    $PropertyId = $realpage_site_id;
    //! $PropertyShowsSpecials = 
    //! $UnitTypeMapping = 
    $FloorplanSource = 'realpage';
    
    $post_id = $matchingposts[0]->ID;
    
    //* Update the title
        
    if ( $FloorplanName != $matchingposts[0]->post_title ) {
            
        $arr = array( 
            'post_title' => $FloorplanName,
            'ID' => $post_id,
        );
        wp_update_post( $arr );
        
    }

    //* Update the post meta
    
    if ( $Baths )
        $success_baths = update_post_meta( $post_id, 'baths', $Baths );
    
    if ( $Beds )
        $success_beds = update_post_meta( $post_id, 'beds', $Beds );
        
    if ( $MaximumRent )
        $success_maximum_rent = update_post_meta( $post_id, 'maximum_rent', $MaximumRent );
        
    if ( $MaximumSQFT )
        $success_maximum_sqft = update_post_meta( $post_id, 'maximum_sqft', $MaximumSQFT );
    
    if ( $MinimumRent )
        $success_minimum_rent = update_post_meta( $post_id, 'minimum_rent', $MinimumRent );
        
    if ( $MinimumSQFT )
        $success_minimum_sqft = update_post_meta( $post_id, 'minimum_sqft', $MinimumSQFT );
        
    if ( $PropertyId )
        $success_property_id = update_post_meta( $post_id, 'property_id', $PropertyId );
        
    $success_floorplan_source = update_post_meta( $post_id, 'floorplan_source', 'realpage' );
    
}