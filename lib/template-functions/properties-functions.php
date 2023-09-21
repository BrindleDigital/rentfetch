<?php

//* PROPERTY TITLE

function rentfetch_get_property_title() {
    $title = apply_filters( 'rentfetch_filter_property_title', get_the_title() );
    return esc_html( $title );
}

function rentfetch_property_title() {
    $title = rentfetch_get_property_title();
    if ( $title )
        echo $title;
}

// add_filter( 'rentfetch_filter_property_title', 'my_custom_title', 10, 1 );
// function my_custom_title( $title ) {
//     return 'My Custom Title';
// }

//* PROPERTY LOCATION

function rentfetch_get_property_address() {
    $address = get_post_meta( get_the_ID(), 'address', true );
    return esc_html( $address );
}

function rentfetch_property_address() {
    $address = get_post_meta( get_the_ID(), 'address', true );
    
    if ( $address )
        echo esc_html( $address );
}

function rentfetch_get_property_city() {
    $city = get_post_meta( get_the_ID(), 'city', true );
    return esc_html( $city );
}

function rentfetch_property_city() {
    $city = get_post_meta( get_the_ID(), 'city', true );
    
    if ( $city )
        echo esc_html( $city );
}

function rentfetch_get_property_state() {
    $state = get_post_meta( get_the_ID(), 'state', true );
    return esc_html( $state );
}

function rentfetch_property_state() {
    $state = get_post_meta( get_the_ID(), 'state', true );
    
    if ( $state )
        echo esc_html( $state );
}

function rentfetch_get_property_zipcode() {
    $zipcode = get_post_meta( get_the_ID(), 'zipcode', true );
    return esc_html( $zipcode );
}

function rentfetch_property_zipcode() {
    $zipcode = get_post_meta( get_the_ID(), 'zipcode', true );
    
    if ( $zipcode )
        echo esc_html( $zipcode );
}

function rentfetch_get_property_location() {
        
    $address = get_post_meta( get_the_ID(), 'address', true );
    $city = get_post_meta( get_the_ID(), 'city', true );
    $state = get_post_meta( get_the_ID(), 'state', true );
    $zipcode = get_post_meta( get_the_ID(), 'zipcode', true );
    
    $location = '';

    // Concatenate address components with commas and spaces
    if (!empty($address)) {
        $location .= $address;
    }

    if (!empty($city)) {
        if (!empty($location)) {
            $location .= ', ';
        }
        $location .= $city;
    }

    if (!empty($state)) {
        if (!empty($location)) {
            $location .= ', ';
        }
        $location .= $state;
    }

    if (!empty($zipcode)) {
        if (!empty($location)) {
            $location .= ' ';
        }
        $location .= $zipcode;
    }
    
    $location = apply_filters( 'rentfetch_filter_property_location', $location );
    return esc_html( $location );
    
}

function rentfetch_property_location() {
    $location = rentfetch_get_property_location();
    
    if ( $location )
        echo $location;
}

// add_filter( 'rentfetch_filter_property_location', 'my_custom_location' );
// function my_custom_location( $location ) {
//     return 'My Custom Location ' . $location;
// }

function rentfetch_get_property_location_link() {
    $location = rentfetch_get_property_location();
    $title = rentfetch_get_property_title();
    $location_link = sprintf( 'https://www.google.com/maps/place/%s', urlencode( $title . ' ' . $location ) );
    return esc_url( $location_link );
}

function rentfetch_get_property_city_state() {
    
    $city = esc_attr( get_post_meta( get_the_ID(), 'city', true ) );
    $state = esc_attr( get_post_meta( get_the_ID(), 'state', true ) );
    
    if ( $city && $state ) {
        $citystate = sprintf( '%s, %s', $city, $state );
    } elseif ( $city && !$state ) {
        $citystate = $city;
    } elseif ( !$city && $state ) {
        $citystate = $state;
    } else {
        $citystate = null;
    }
        
    return apply_filters( 'rentfetch_filter_property_city_state', $citystate );
}

function rentfetch_property_city_state() {
    $citystate = rentfetch_get_property_city_state();
    
    if ( $citystate )
        echo $citystate;
}

// add_filter( 'rentfetch_filter_property_city_state', 'my_custom_city_state', 10, 1 );
// function my_custom_city_state( $location ) {
//     return 'My Custom City State';
// }

//* PROPERTY PHONE
function rentfetch_get_property_phone() {
    $phone = get_post_meta( get_the_ID(), 'phone', true );
    return esc_html( $phone );
    
    apply_filters( 'rentfetch_filter_property_phone', $phone );
}

function rentfetch_property_phone() {
    echo rentfetch_get_property_phone();
}

//* PROPERTY URL
function rentfetch_get_property_url() {
    $url = get_post_meta( get_the_ID(), 'url', true );
    return esc_html( $url );
    
    apply_filters( 'rentfetch_filter_property_url', $url );
}

function rentfetch_property_url() {
    echo rentfetch_get_property_url();
}

//* PROPERTY BEDROOMS

function rentfetch_get_property_bedrooms() {
    
    $property_id = esc_html( get_post_meta( get_the_ID(), 'property_id', true ) );
    
    $floorplan_data = rentfetch_get_floorplans( $property_id );
    
    console_log( $floorplan_data );
    
    if ( !isset( $floorplan_data['bedsrange'] ) )
        return;
        
    $bedrooms = apply_filters( 'rentfetch_filter_property_bedrooms', $floorplan_data['bedsrange'] );
    return esc_html( $bedrooms );
}

function rentfetch_property_bedrooms() {
    $bedrooms = rentfetch_get_property_bedrooms();
    
    if ( $bedrooms )
        echo $bedrooms;
}

add_filter( 'rentfetch_filter_property_bedrooms', 'rentfetch_default_property_bedrooms_label', 10, 1 );
function rentfetch_default_property_bedrooms_label( $bedrooms ) {
    return $bedrooms . ' Bed';
}

//* PROPERTY BATHROOMS

function rentfetch_get_property_bathrooms() {
    
    $property_id = esc_html( get_post_meta( get_the_ID(), 'property_id', true ) );
    
    $floorplan_data = rentfetch_get_floorplans( $property_id );
    
    if ( !isset( $floorplan_data['bathsrange'] ) )
        return;
        
    $bathrooms = apply_filters( 'rentfetch_filter_property_bathrooms', $floorplan_data['bathsrange'] );
    return esc_html( $bathrooms );
    
}

function rentfetch_property_bathrooms() {
    $bathrooms = rentfetch_get_property_bathrooms();
    
    if ( $bathrooms )
        echo $bathrooms;
}

add_filter( 'rentfetch_filter_property_bathrooms', 'rentfetch_default_property_bathrooms_label', 10, 1 );
function rentfetch_default_property_bathrooms_label( $bathrooms ) {
    return $bathrooms . ' Bath';
}

//* PROPERTY SQUARE FEET

function rentfetch_get_property_square_feet() {
    $property_id = esc_html( get_post_meta( get_the_ID(), 'property_id', true ) );
    
    $floorplan_data = rentfetch_get_floorplans( $property_id );
    
    if ( !isset( $floorplan_data['sqftrange'] ) )
        return;
    
    $square_feet = apply_filters( 'rentfetch_filter_property_square_feet', $floorplan_data['sqftrange'] );
    return esc_html( $square_feet );
}

function rentfetch_property_square_feet() {
    $square_feet = rentfetch_get_property_square_feet();
    
    if ( $square_feet )
        echo $square_feet;
}

add_filter( 'rentfetch_filter_property_square_feet', 'rentfetch_default_property_square_feet_label', 10, 1 );
function rentfetch_default_property_square_feet_label( $square_feet ) {
    return $square_feet . ' sqft';
}

//* PROPERTY RENT

function rentfetch_get_property_rent() {
    $property_id = esc_html( get_post_meta( get_the_ID(), 'property_id', true ) );
    
    $floorplan_data = rentfetch_get_floorplans( $property_id );
    
     if ( !isset( $floorplan_data['rentrange'] ) )
        return;
        
    $rent = apply_filters( 'rentfetch_filter_property_rent', $floorplan_data['rentrange'] );
    return esc_html( $rent );
    
}

function rentfetch_property_rent() {
    $rent = rentfetch_get_property_rent();
    
    if ( $rent )
        echo $rent;
}

add_filter( 'rentfetch_filter_property_rent', 'rentfetch_default_property_rent_label', 10, 1 );
function rentfetch_default_property_rent_label( $rent ) {
    
    if ( $rent )
        return '$' . esc_html( $rent );
        
    // This could return 'Call for Pricing' or 'Pricing unavailable' if pricing isn't available
    return null;
}

//* PROPERTY AVAILABILITY

function rentfetch_get_property_availability() {
    $property_id = esc_html( get_post_meta( get_the_ID(), 'property_id', true ) );
    
    $floorplan_data = rentfetch_get_floorplans( $property_id );
    
    if ( isset( $floorplan_data['availability'] ) ) {
    
        $units_available = apply_filters( 'rentfetch_filter_property_availabile_units', $floorplan_data['availability'] );
    
        if ( $units_available > 0 )
            return $units_available;
            
    }
    
    if ( isset( $floorplan_data['available_date'] ) ) {
        $available_date = apply_filters( 'rentfetch_filter_property_availability_date', $floorplan_data['available_date'] );
            
        if ( $available_date )
            return $available_date;
    }
    
    return null;
    
}

function rentfetch_property_availability() {
    $availability = rentfetch_get_property_availability();
    
    if ( $availability )
        echo $availability;
}

add_filter( 'rentfetch_filter_property_availabile_units', 'rentfetch_default_property_available_units_label', 10, 1 );
function rentfetch_default_property_available_units_label( $availability ) {
    
    if ( $availability == 1 ) {
        return esc_html( $availability ) . ' unit available';
    } elseif ( $availability >= 1 ) {
        return esc_html( $availability ) . ' units available';
    }        
}

add_filter( 'rentfetch_filter_property_availability_date', 'rentfetch_default_property_availability_date', 10, 1 );
function rentfetch_default_property_availability_date( $availability_date ) {
    
    if ( $availability_date )
        return 'Available ' . esc_html( $availability_date );
        
    return null;
}

//* PROPERTY SPECIALS

function rentfetch_get_property_specials() {
    $property_id = esc_html( get_post_meta( get_the_ID(), 'property_id', true ) );
    
    $floorplan_data = rentfetch_get_floorplans( $property_id );
    
    if ( !isset( $floorplan_data['property_has_specials'] ) )
        return;
        
    $specials = apply_filters( 'rentfetch_filter_property_specials', $floorplan_data['property_has_specials'] );
    return esc_html( $specials );
    
}

function rentfetch_property_specials() {
    $specials = rentfetch_get_property_specials();
    
    if ( $specials )
        echo $specials;
}

add_filter( 'rentfetch_filter_property_specials', 'rentfetch_default_property_specials_label', 10, 1 );
function rentfetch_default_property_specials_label( $specials ) {
    
    if ( $specials )
        return 'Specials available';
        
    return null;
}

add_filter( 'rentfetch_filter_property_permalink', 'rentfetch_default_property_permalink', 10, 1 );
function rentfetch_default_property_permalink( $url ) {
    
    $url = get_the_permalink();
    
    return $url;
}

add_filter( 'rentfetch_filter_property_permalink_label', 'rentfetch_default_property_permalink_label', 10, 1 );
function rentfetch_default_property_permalink_label( $url ) {
    
    return 'View Property';
    
}

add_filter( 'rentfetch_filter_property_permalink_target', 'rentfetch_default_property_permalink_target', 10, 1 );
function rentfetch_default_property_permalink_target( $url ) {
    
    return '_self';
    
}

//* PROPERTY DESCRIPTION

function rentfetch_get_property_description() {
    $property_description = get_post_meta( get_the_ID(), 'description', true );
    $property_description = apply_filters( 'the_content', esc_attr( $property_description ) );
    $property_description = apply_filters( 'rentfetch_filter_property_description', $property_description );
    return $property_description;
}

function rentfetch_property_description() {
    $property_description = rentfetch_get_property_description();
    if ( $property_description )
        echo $property_description;
}