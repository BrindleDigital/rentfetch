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

function rentfetch_get_floorplan_available_units() {
    $available_units = get_post_meta( get_the_ID(), 'available_units', true );
        
    return apply_filters( 'rentfetch_get_available_units_label', $available_units );
}

function rentfetch_floorplan_available_units() {
    echo rentfetch_get_floorplan_available_units();
}

//* Pricing

function rentfetch_get_floorplan_pricing() {
    $minimum_rent = get_post_meta( get_the_ID(), 'minimum_rent', true );
    $maximum_rent = get_post_meta( get_the_ID(), 'maximum_rent', true );
    
    // bail if there's no rent value over $50 (this is junk data)    
    if ( max($minimum_rent, $maximum_rent) < 50 )
        return null;
    
    if ( $minimum_rent == $maximum_rent ) {
        $rent_range = sprintf( '$%s', number_format( $minimum_rent ) );
    } elseif ( $minimum_rent < $maximum_rent ) {
        $rent_range = sprintf( '$%s-$%s', number_format( $minimum_rent ), number_format( $maximum_rent ) );
    } elseif ( $minimum_rent > $maximum_rent) {
        $rent_range = sprintf( '$%s-$%s', number_format( $maximum_rent ), number_format( $minimum_rent ) );
    } elseif ( $minimum_rent && !$maximum_rent ) {
        $rent_range = sprintf( '$%s', number_format( $minimum_rent ) );
    } elseif ( !$minimum_rent && $maximum_rent ) {
        $rent_range = sprintf( '$%s', number_format( $maximum_rent ) );
    }    
    
    return $rent_range;
}

function rentfetch_floorplan_pricing() {
    echo rentfetch_get_floorplan_pricing();
}

//* Move in special

//* Tour

//* Buttons

function rentfetch_get_floorplan_links() {
    
    $units_count = rentfetch_get_floorplan_units_count();
    
    ob_start();    
    
    if ( $units_count > 0 ) {
        
        // if there are units attached to this floorplan, then link to the permalink of the floorplan
        $overlay = sprintf( '<a href="%s" class="overlay-link"></a>', get_the_permalink() );
        echo apply_filters( 'rentfetch_do_floorplan_overlay_link', $overlay );
        
    } else {
        
        // if there are no units attached to this floorplan, then do the buttons
        echo '<div class="buttons-outer">';
            echo '<div class="buttons-inner">';
                do_action( 'rentfetch_do_floorplan_buttons' );
            echo '</div>';
        echo '</div>';
    }
        
    return ob_get_clean();
    
}

function rentfetch_floorplan_buttons() {
    echo rentfetch_get_floorplan_links();
}

//* Add actions for each button to easily add or remove buttons

// Availability button
function rentfetch_floorplan_default_availability_button() {
    $availability_button = sprintf( '<a href="%s" class="rentfetch-button">Availability</a>', 'https://google.com/availability-button' ); 
    
    echo apply_filters( 'rentfetch_floorplan_default_availability_button', $availability_button );
}
add_action( 'rentfetch_do_floorplan_buttons', 'rentfetch_floorplan_default_availability_button' );

// Contact button
function rentfetch_floorplan_default_contact_button() {
    
    $button_enabled = get_option( 'options_contact_button_enabled', false );

    // bail if the contact button is not enabled
    if ( $button_enabled != 1 )
        return;
    
    $button = sprintf( '<a href="%s" class="rentfetch-button">Contact</a>', 'https://google.com/contact-button' ); 
    
    echo apply_filters( 'rentfetch_floorplan_default_contact_button_markup', $button );
}
add_action( 'rentfetch_do_floorplan_buttons', 'rentfetch_floorplan_default_contact_button' );


function rentfetch_floorplan_default_contact_button_markup() {
    
    $button_label = get_option( 'options_contact_button_button_label', 'Contact' );
    $external = get_option( 'options_contact_button_link_target', false );
    $link = get_option( 'options_contact_button_link', false );
    
    // bail if no link is set
    if ( $link == false )
        return;
    
    if ( $external == true ) {
        $target = 'target="_blank"';
    } else {
        $target = 'target="_self"';
    }
    
    $button_markup = sprintf( '<a href="%s" %s class="rentfetch-button">%s</a>', $link, $target, $button_label );
    return $button_markup;
}
add_filter( 'rentfetch_floorplan_default_contact_button_markup', 'rentfetch_floorplan_default_contact_button_markup' );


// Tour button
function rentfetch_floorplan_default_tour_button() {
    $contact_button = sprintf( '<a href="%s" class="rentfetch-button">Tour</a>', 'https://google.com/tour-button' ); 
    
    echo apply_filters( 'rentfetch_floorplan_default_tour_button', $contact_button );
}
add_action( 'rentfetch_do_floorplan_buttons', 'rentfetch_floorplan_default_tour_button' );