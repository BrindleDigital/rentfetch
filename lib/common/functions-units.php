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
 * Resolve a unit post to its connected property post ID via matching property_id meta.
 *
 * @param int|null $unit_post_id Optional unit post ID.
 * @return int|null Property post ID when found, null otherwise.
 */
function rentfetch_get_connected_property_post_id_for_unit( $unit_post_id = null ) {
	if ( ! $unit_post_id ) {
		$unit_post_id = get_the_ID();
	}

	$unit_post_id = (int) $unit_post_id;
	if ( $unit_post_id <= 0 ) {
		return null;
	}

	$property_id = trim( (string) get_post_meta( $unit_post_id, 'property_id', true ) );
	if ( '' === $property_id || ! function_exists( 'rentfetch_get_post_id_from_property_id' ) ) {
		return null;
	}

	$property_post_id = (int) rentfetch_get_post_id_from_property_id( $property_id );
	if ( $property_post_id <= 0 ) {
		return null;
	}

	return $property_post_id;
}

/**
 * Get property-level monthly required total fees for a unit.
 *
 * @param int|null $unit_post_id Optional unit post ID.
 * @return float
 */
function rentfetch_get_unit_property_monthly_required_fees_total( $unit_post_id = null ) {
	$property_post_id = rentfetch_get_connected_property_post_id_for_unit( $unit_post_id );
	if ( ! $property_post_id ) {
		return 0.0;
	}

	if ( function_exists( 'rentfetch_get_effective_monthly_required_total_fees_for_property' ) ) {
		$effective_total = rentfetch_get_effective_monthly_required_total_fees_for_property( $property_post_id );
		if ( is_numeric( $effective_total ) && (float) $effective_total > 0 ) {
			return (float) $effective_total;
		}
		return 0.0;
	}

	$property_raw = get_post_meta( $property_post_id, 'property_monthly_required_total_fees', true );
	if ( '' === (string) $property_raw ) {
		return 0.0;
	}

	$property_total = null;
	if ( function_exists( 'rentfetch_extract_first_numeric_fee_value' ) ) {
		$property_total = rentfetch_extract_first_numeric_fee_value( $property_raw );
	} elseif ( is_numeric( $property_raw ) ) {
		$property_total = (float) $property_raw;
	}

	if ( null === $property_total || $property_total <= 0 ) {
		return 0.0;
	}

	return (float) $property_total;
}

/**
 * Get the base unit rent value.
 *
 * Unit pricing displays the lower of min/max when both exist.
 *
 * @param mixed $minimum_rent Minimum rent value.
 * @param mixed $maximum_rent Maximum rent value.
 * @return float|null
 */
function rentfetch_get_unit_base_rent_value( $minimum_rent, $maximum_rent ) {
	$minimum_rent = is_numeric( $minimum_rent ) ? (float) $minimum_rent : null;
	$maximum_rent = is_numeric( $maximum_rent ) ? (float) $maximum_rent : null;
	$minimum_rent = ( null !== $minimum_rent && $minimum_rent > 0 ) ? $minimum_rent : null;
	$maximum_rent = ( null !== $maximum_rent && $maximum_rent > 0 ) ? $maximum_rent : null;

	if ( null !== $minimum_rent && null !== $maximum_rent ) {
		return (float) min( $minimum_rent, $maximum_rent );
	}

	if ( null !== $minimum_rent ) {
		return (float) $minimum_rent;
	}

	if ( null !== $maximum_rent ) {
		return (float) $maximum_rent;
	}

	return null;
}

/**
 * Format a numeric rent value as currency.
 *
 * @param float $value Rent value.
 * @return string
 */
function rentfetch_format_unit_rent_value( $value ) {
	return sprintf( '$%s', number_format( (float) $value ) );
}

/**
 * Get the unit pricing.
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

	$base_rent_value = rentfetch_get_unit_base_rent_value( $minimum_rent, $maximum_rent );
	if ( null === $base_rent_value ) {
		return apply_filters( 'rentfetch_filter_unit_pricing', null, $minimum_rent, $maximum_rent );
	}

	$base_rent_display = rentfetch_format_unit_rent_value( $base_rent_value );

	$monthly_required_fees = rentfetch_get_unit_property_monthly_required_fees_total( get_the_ID() );
	if ( $monthly_required_fees > 0 ) {
		$rent_with_fees_display = rentfetch_format_unit_rent_value( $base_rent_value + $monthly_required_fees );
		$tooltip_markup         = function_exists( 'rentfetch_get_total_monthly_leasing_pricing_tooltip_markup' ) ? rentfetch_get_total_monthly_leasing_pricing_tooltip_markup() : '';
		$rent_range             = sprintf(
			'<span class="rentfetch-unit-rent-lines"><span class="rentfetch-unit-rent-with-fees rentfetch-unit-rent-with-fees--inclusive"><span class="rentfetch-pricing-with-tooltip">%1$s/mo%3$s</span></span><span class="rentfetch-unit-base-rent">%2$s base rent</span></span>',
			esc_html( $rent_with_fees_display ),
			esc_html( $base_rent_display ),
			$tooltip_markup
		);
	} else {
		$rent_range = sprintf(
			'<span class="rentfetch-unit-rent-lines"><span class="rentfetch-unit-rent-with-fees">%s/mo</span></span>',
			esc_html( $base_rent_display )
		);
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
		echo wp_kses_post( $pricing );
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
	$amenities = esc_html( get_post_meta( get_the_ID(), 'amenities', true ) );

	$amenities = apply_filters( 'rentfetch_filter_unit_amenities', $amenities );

	return $amenities;
}

/**
 * Get the building name for the unit
 *
 * @return  string the building name.
 */
function rentfetch_get_unit_building_name() {
	$building_name = get_post_meta( get_the_ID(), 'building_name', true );
	return apply_filters( 'rentfetch_filter_unit_building_name', $building_name );
}

/**
 * Get the unit floor number
 *
 * @return  string the unit floor number.
 */
function rentfetch_get_unit_floor_number() {
	$floor_number = get_post_meta( get_the_ID(), 'floor_number', true );
	return apply_filters( 'rentfetch_filter_unit_floor_number', $floor_number );
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
		$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_applyonline_click', rentfetch_get_floorplan_tracking_context() );
		$markup = sprintf( '<a href="%s" class="rentfetch-button rentfetch-button-small" target="_blank"%s>Apply Online</a>', $apply_online_url, $tracking_attrs );
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
