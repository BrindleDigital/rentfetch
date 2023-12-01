<?php

//* Title

function rentfetch_get_unit_title() {
	$title = apply_filters( 'rentfetch_filter_unit_title', get_the_title() );
	return esc_html( $title );
}

function rentfetch_unit_title() {
	$title = rentfetch_get_unit_title();
	if ( $title )
		echo $title;
}

//* Pricing

function rentfetch_get_unit_pricing() {
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
	
	return apply_filters( 'rentfetch_filter_unit_pricing', $rent_range );
}

function rentfetch_unit_pricing() {
	$pricing = rentfetch_get_unit_pricing();
	if ( $pricing )
		echo $pricing;

}

//* Deposit

function rentfetch_get_unit_deposit() {
	$deposit = get_post_meta( get_the_ID(), 'deposit', true );
	
	if ( $deposit == 0 || $deposit == '0' || empty( $deposit ) ) {
		$deposit = 'Please inquire';
	} else {
		$deposit = sprintf( '$%s', number_format( $deposit ) );
	}
	
	return apply_filters( 'rentfetch_filter_unit_deposit', $deposit );
}

function rentfetch_unit_deposit() {
	$deposit = rentfetch_get_unit_deposit();
	if ( $deposit )
		echo $deposit;
}

//* Date 

function rentfetch_get_unit_availability_date() {
	
	$availability_date = get_post_meta( get_the_ID(), 'availability_date', true );

	if (strtotime( $availability_date ) <= strtotime( 'today' )) {
		return 'Available now';
	} else {
		return date('F j, Y', strtotime($availability_date));
	}
	
	//TODO need to handle the case where there is no availability date. Need to see an example of this to do so.
}

//* Units count

function rentfetch_get_floorplan_units_count_from_meta() {
	$floorplan_wordpress_id = get_the_ID();
	$available_units = get_post_meta( $floorplan_wordpress_id, 'available_units', true );
	return intval( $available_units );    
}

function rentfetch_get_floorplan_units_count_from_cpt() {
	
	$floorplan_wordpress_id = get_the_ID();
	$floorplan_id = get_post_meta( $floorplan_wordpress_id, 'floorplan_id', true );
	
	if ( !$floorplan_id )
		return null;
	
	$args = array(
		'post_type' => 'units', // Replace 'your_custom_post_type' with the actual post type name
		'meta_key' => 'floorplan_id',
		'meta_value' => $floorplan_id,
	);

	$query = new WP_Query( $args );

	$count = $query->found_posts;

	wp_reset_postdata();

	return $count;
}

//* Buttons

function rentfetch_unit_button() {
	$apply_online_url = get_post_meta( get_the_ID(), 'apply_online_url', true );
	
	if ( $apply_online_url ) {
		$markup = sprintf( '<a href="%s" class="rentfetch-button rentfetch-button-small" target="_blank">Apply Online</a>', $apply_online_url );
		echo apply_filters( 'rentfetch_filter_unit_apply_button_markup', $markup );
	} else {
	   rentfetch_unit_default_contact_button();
	}
}
add_action( 'rentfetch_do_unit_button', 'rentfetch_unit_button' );

// Contact button
function rentfetch_unit_default_contact_button() {
	
	$button_enabled = get_option( 'rentfetch_options_contact_button_enabled', false );

	// bail if the button is not enabled
	if ( $button_enabled != 1 )
		return;
		
	echo apply_filters( 'rentfetch_filter_unit_default_contact_button_markup', null );
}

function rentfetch_unit_default_contact_button_markup() {
	
	$button_label = get_option( 'rentfetch_options_contact_button_button_label', 'Contact' );
	$external = get_option( 'rentfetch_options_contact_button_link_target', false );
	$link = get_option( 'rentfetch_options_contact_button_link', false );
	
	// bail if no link is set
	if ( $link == false )
		return;
	
	if ( $external == true ) {
		$target = 'target="_blank"';
	} else {
		$target = 'target="_self"';
	}
	
	$button_markup = sprintf( '<a href="%s" %s class="rentfetch-button rentfetch-button-small rentfetch-button-no-highlight">%s</a>', $link, $target, $button_label );
	return $button_markup;
}
add_filter( 'rentfetch_filter_unit_default_contact_button_markup', 'rentfetch_unit_default_contact_button_markup' );