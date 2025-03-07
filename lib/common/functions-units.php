<?php
/**
 * This file has the Rent Fetch functions for getting unit data.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// * Title

/**
 * Get the unit title
 *
 * @return string the unit title.
 */
function rentfetch_get_unit_title() {
	$title = apply_filters( 'rentfetch_filter_unit_title', get_the_title() );
	return $title;
}

/**
 * Output the unit title
 *
 * @return void
 */
function rentfetch_unit_title() {
	$title = rentfetch_get_unit_title();
	if ( $title ) {
		echo esc_html( $title );
	}
}

// * Pricing

/**
 * Get the unit pricing
 *
 * @return string the unit pricing.
 */
function rentfetch_get_unit_pricing() {

	$minimum_rent = get_post_meta( get_the_ID(), 'minimum_rent', true );
	$maximum_rent = get_post_meta( get_the_ID(), 'maximum_rent', true );

	// bail if there's no rent value over $50 (this is junk data).
	if ( max( $minimum_rent, $maximum_rent ) < 50 ) {
		return apply_filters( 'rentfetch_filter_unit_pricing', null, $minimum_rent, $maximum_rent );
	}

	if ( $minimum_rent === $maximum_rent ) {
		$rent_range = sprintf( '$%s', number_format( $minimum_rent ) );
	} elseif ( $minimum_rent < $maximum_rent ) {
		$rent_range = sprintf( '$%s', number_format( $minimum_rent ) );
	} elseif ( $minimum_rent > $maximum_rent ) {
		$rent_range = sprintf( '$%s', number_format( $maximum_rent ) );
	} elseif ( $minimum_rent && ! $maximum_rent ) {
		$rent_range = sprintf( '$%s', number_format( $minimum_rent ) );
	} elseif ( ! $minimum_rent && $maximum_rent ) {
		$rent_range = sprintf( '$%s', number_format( $maximum_rent ) );
	}

	return apply_filters( 'rentfetch_filter_unit_pricing', $rent_range, $minimum_rent, $maximum_rent );
}

/**
 * Output the unit pricing
 *
 * @return void
 */
function rentfetch_unit_pricing() {
	$pricing = rentfetch_get_unit_pricing();
	if ( $pricing ) {
		echo esc_html( $pricing );
	}
}

// * Deposit

/**
 * Get the unit deposit
 *
 * @return string the unit deposit.
 */
function rentfetch_get_unit_deposit() {
	$deposit = (int) get_post_meta( get_the_ID(), 'deposit', true );

	if ( 0 === $deposit ) {
		$deposit = 'Please inquire';
	} else {
		$deposit = sprintf( '$%s', number_format( $deposit ) );
	}

	return apply_filters( 'rentfetch_filter_unit_deposit', $deposit );
}

/**
 * Output the unit deposit
 *
 * @return void
 */
function rentfetch_unit_deposit() {
	$deposit = rentfetch_get_unit_deposit();
	if ( $deposit ) {
		echo esc_html( $deposit );
	}
}

// * Date

/**
 * Get the unit availability date
 *
 * @return string the unit availability date.
 */
function rentfetch_get_unit_availability_date() {

	$availability_date = sanitize_text_field( get_post_meta( get_the_ID(), 'availability_date', true ) );

	if ( empty( $availability_date ) ) {
		return 'Please inquire';
	} elseif ( strtotime( $availability_date ) <= strtotime( 'today' ) ) {
		return 'Available now';
	} else {
		return gmdate( 'F j, Y', strtotime( $availability_date ) );
	}

	// TODO need to handle the case where there is no availability date. Need to see an example of this to do so.
}

/**
 * Output the unit amenities
 *
 * @return void
 */
function rentfetch_get_unit_amenities() {
	$amenities_array = get_post_meta( get_the_ID(), 'amenities', true );
	
	$amenities_array = apply_filters( 'rentfetch_filter_unit_amenities', $amenities_array );

	// bail if the amenities are not an array.
	if ( !is_array( $amenities_array ) ) { 
		return;
	}

	$amenities_array = array_map( 'esc_html', $amenities_array );
	$amenities_string = implode( ', ', $amenities_array );

	return $amenities_string;
}

/**
 * Get the specials for the unit
 *
 * @return  string the specials.
 */
function rentfetch_get_unit_specials() {
	$specials = get_post_meta( get_the_ID(), 'specials', true );
	return apply_filters( 'rentfetch_filter_unit_specials', $specials );
}

// * Units count

/**
 * Get the unit count from the floorplan meta
 *
 * @return int the unit count.
 */
function rentfetch_get_floorplan_units_count_from_meta() {
	$floorplan_wordpress_id = get_the_ID();
	$available_units        = get_post_meta( $floorplan_wordpress_id, 'available_units', true );
	return $available_units;
}

/**
 * Get the unit count from the units CPT
 *
 * @return int the unit count.
 */
function rentfetch_get_floorplan_units_count_from_cpt() {

	$floorplan_wordpress_id = get_the_ID();
	$floorplan_id           = get_post_meta( $floorplan_wordpress_id, 'floorplan_id', true );
	$property_id            = get_post_meta( $floorplan_wordpress_id, 'property_id', true );

	if ( ! $floorplan_id ) {
		return null;
	}
	
	// set up the args for the query. We need units that match both the property and floorplan.
	$args = array(
		'post_type'      => 'units',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'status'         => 'publish',
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'     => 'floorplan_id',
				'value'   => $floorplan_id,
				'compare' => '=',
			),
			array(
				'key'     => 'property_id',
				'value'   => $property_id,
				'compare' => '=',
			),
		),
	);
	
	$posts = get_posts( $args );

	$count = count( $posts );

	return (int) $count;
}

// * Buttons

/**
 * Apply button
 *
 * @return void
 */
function rentfetch_unit_button() {
	$apply_online_url = get_post_meta( get_the_ID(), 'apply_online_url', true );

	if ( $apply_online_url ) {
		$markup = sprintf( '<a href="%s" class="rentfetch-button rentfetch-button-small" target="_blank">Apply Online</a>', $apply_online_url );
		echo wp_kses_post( apply_filters( 'rentfetch_filter_unit_apply_button_markup', $markup ) );
	} else {
		rentfetch_unit_default_contact_button();
	}
}
add_action( 'rentfetch_do_unit_button', 'rentfetch_unit_button' );

/**
 * Default contact button
 *
 * @return void
 */
function rentfetch_unit_default_contact_button() {

	$button_enabled = (int) get_option( 'rentfetch_options_contact_button_enabled', false );

	// bail if the button is not enabled.
	if ( 1 !== $button_enabled ) {
		return;
	}

	echo wp_kses_post( apply_filters( 'rentfetch_filter_unit_default_contact_button_markup', null ) );
}

/**
 * Default contact button markup
 *
 * @return string the button markup.
 */
function rentfetch_unit_default_contact_button_markup() {

	$button_label = get_option( 'rentfetch_options_contact_button_button_label', 'Contact' );
	$external     = get_option( 'rentfetch_options_contact_button_link_target', false );
	$link         = get_option( 'rentfetch_options_contact_button_link', false );

	// bail if no link is set.
	if ( false === $link ) {
		return;
	}

	if ( true === $external ) {
		$target = 'target="_blank"';
	} else {
		$target = 'target="_self"';
	}

	$button_markup = sprintf( '<a href="%s" %s class="rentfetch-button rentfetch-button-small rentfetch-button-no-highlight">%s</a>', $link, $target, $button_label );
	return $button_markup;
}
add_filter( 'rentfetch_filter_unit_default_contact_button_markup', 'rentfetch_unit_default_contact_button_markup' );
