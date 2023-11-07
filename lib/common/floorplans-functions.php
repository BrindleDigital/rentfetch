<?php

//* Title

function rentfetch_get_floorplan_title() {
    $title = apply_filters( 'rentfetch_filter_floorplan_title', get_the_title() );
    return esc_html( $title );
}

function rentfetch_floorplan_title() {
    $title = rentfetch_get_floorplan_title();
    if ( $title )
        echo $title;
}

//* Bedrooms

function rentfetch_get_floorplan_bedrooms() {
    $beds_number = get_post_meta( get_the_ID(), 'beds', true );
    
    $beds_number = apply_filters( 'rentfetch_filter_floorplan_bedrooms', $beds_number );
    return apply_filters( 'rentfetch_get_bedroom_number_label', $beds_number );
}

function rentfetch_floorplan_bedrooms() {
    echo rentfetch_get_floorplan_bedrooms();
}

//* Bathrooms

function rentfetch_get_floorplan_bathrooms() {
    $baths_number = get_post_meta( get_the_ID(), 'baths', true );
        
    $baths_number = apply_filters( 'rentfetch_filter_floorplan_bathrooms', $baths_number );
    return apply_filters( 'rentfetch_get_bathroom_number_label', $baths_number );
}

function rentfetch_floorplan_bathrooms() {
    echo rentfetch_get_floorplan_bathrooms();
}

//* Square feet

function rentfetch_get_floorplan_square_feet() {
    $minimum_sqft = get_post_meta( get_the_ID(), 'minimum_sqft', true );
    $maximum_sqft = get_post_meta( get_the_ID(), 'maximum_sqft', true );
    
    if ( $minimum_sqft == $maximum_sqft ) {
        $square_feet = sprintf( '%s', number_format( $minimum_sqft ) );
    } elseif ( $minimum_sqft < $maximum_sqft ) {
        $square_feet = sprintf( '%s-%s', number_format( $minimum_sqft ), number_format( $maximum_sqft ) );
    } elseif ( $minimum_sqft > $maximum_sqft) {
        $square_feet = sprintf( '%s-%s', number_format( $maximum_sqft ), number_format( $minimum_sqft ) );
    } elseif ( $minimum_sqft && !$maximum_sqft ) {
        $square_feet = sprintf( '%s', number_format( $minimum_sqft ) );
    } elseif ( !$minimum_sqft && $maximum_sqft ) {
        $square_feet = sprintf( '%s', number_format( $maximum_sqft ) );
    }    
    
    $square_feet = apply_filters( 'rentfetch_filter_floorplan_square_feet', $square_feet );
    return apply_filters( 'rentfetch_get_square_feet_number_label', $square_feet );
}

function rentfetch_floorplan_square_feet() {
    echo rentfetch_get_floorplan_square_feet();
}

//* Number available

//* Pricing

//* Move in special

//* Tour

//* Buttons