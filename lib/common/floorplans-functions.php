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
    
    if ( $minimum_rent == 0 && $maximum_rent == 0 )
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
    
    $floorplan_id = get_post_meta( get_the_ID(), 'floorplan_id', true );
    
    $args = array(
        'post_type' => 'units',
        'fields' => 'ids',
        'meta_query' => array(
            array(
                'key' => 'floorplan_id',
                'value' => $floorplan_id,
            )
        )
    );
    
    $units = get_posts( $args );
    
    ob_start();    
    
    if ( empty( $units ) ) {
        
        // if there are no units attached to this floorplan, then do the buttons
        echo '<div class="buttons-outer">';
            echo '<div class="buttons-inner">';
                do_action( 'rentfetch_do_floorplan_buttons' );
            echo '</div>';
        echo '</div>';
        
    } else {
        
        $overlay = sprintf( '<a href="%s" class="overlay-link">Overlay</a>', get_the_permalink() );
        
        // if there are units attached to this floorplan, then link to the permalink of the floorplan
        echo apply_filters( 'rentfetch_do_floorplan_overlay_link', $overlay );
        
    }
        
    return ob_get_clean();
    
}

function rentfetch_floorplan_buttons() {
    echo rentfetch_get_floorplan_links();
}

//* Add actions for each button to easily add or remove buttons

function rentfetch_floorplan_default_contact_button() {
    $contact_button = sprintf( '<a href="%s" class="rentfetch-button">Contact</a>', 'https://google.com/contact-button' ); 
    
    echo apply_filters( 'rentfetch_floorplan_default_contact_button', $contact_button );
}
add_action( 'rentfetch_do_floorplan_buttons', 'rentfetch_floorplan_default_contact_button' );

function rentfetch_floorplan_default_tour_button() {
    $contact_button = sprintf( '<a href="%s" class="rentfetch-button">Tour</a>', 'https://google.com/tour-button' ); 
    
    echo apply_filters( 'rentfetch_floorplan_default_tour_button', $contact_button );
}
add_action( 'rentfetch_do_floorplan_buttons', 'rentfetch_floorplan_default_tour_button' );
