<?php
/**
 * This file has the Rent Fetch functions for getting property data.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// * PROPERTY TITLE

/**
 * Get the property title.
 *
 * @return string The property title.
 */
function rentfetch_get_property_title() {
	$title = apply_filters( 'rentfetch_filter_property_title', get_the_title() );
	return esc_html( $title );
}

/**
 * Echo the property title.
 *
 * @return void.
 */
function rentfetch_property_title() {
	$title = rentfetch_get_property_title();
	if ( $title ) {
		echo esc_html( $title );
	}
}

// * PROPERTY LOCATION

/**
 * Get the property address
 *
 * @return string The property address.
 */
function rentfetch_get_property_address() {
	$address = get_post_meta( get_the_ID(), 'address', true );
	return esc_html( $address );
}

/**
 * Echo the property address.
 *
 * @return void.
 */
function rentfetch_property_address() {
	$address = get_post_meta( get_the_ID(), 'address', true );

	if ( $address ) {
		echo esc_html( $address );
	}
}

/**
 * Get the city of the property.
 *
 * @return string The property city.
 */
function rentfetch_get_property_city() {
	$city = get_post_meta( get_the_ID(), 'city', true );
	return esc_html( $city );
}

/**
 * Echo the city of the property.
 *
 * @return void.
 */
function rentfetch_property_city() {
	$city = get_post_meta( get_the_ID(), 'city', true );

	if ( $city ) {
		echo esc_html( $city );
	}
}

/**
 * Get the state of the property.
 *
 * @return string The property state.
 */
function rentfetch_get_property_state() {
	$state = get_post_meta( get_the_ID(), 'state', true );
	return esc_html( $state );
}

/**
 * Echo the state of the property.
 *
 * @return void.
 */
function rentfetch_property_state() {
	$state = get_post_meta( get_the_ID(), 'state', true );

	if ( $state ) {
		echo esc_html( $state );
	}
}

/**
 * Get the property zipcode.
 *
 * @return string The property zipcode.
 */
function rentfetch_get_property_zipcode() {
	$zipcode = get_post_meta( get_the_ID(), 'zipcode', true );
	return esc_html( $zipcode );
}

/**
 * Echo the property zipcode.
 *
 * @return void.
 */
function rentfetch_property_zipcode() {
	$zipcode = get_post_meta( get_the_ID(), 'zipcode', true );

	if ( $zipcode ) {
		echo esc_html( $zipcode );
	}
}

/**
 * Get the property location
 *
 * @return string The property location.
 */
function rentfetch_get_property_location() {

	$address = sanitize_text_field( get_post_meta( get_the_ID(), 'address', true ) );
	$city    = sanitize_text_field( get_post_meta( get_the_ID(), 'city', true ) );
	$state   = sanitize_text_field( get_post_meta( get_the_ID(), 'state', true ) );
	$zipcode = sanitize_text_field( get_post_meta( get_the_ID(), 'zipcode', true ) );

	$location = '';

	// Concatenate address components with commas and spaces.
	if ( ! empty( $address ) ) {
		$location .= $address;
	}

	if ( ! empty( $city ) ) {
		if ( ! empty( $location ) ) {
			$location .= ', ';
		}
		$location .= $city;
	}

	if ( ! empty( $state ) ) {
		if ( ! empty( $location ) ) {
			$location .= ', ';
		}
		$location .= $state;
	}

	if ( ! empty( $zipcode ) ) {
		if ( ! empty( $location ) ) {
			$location .= ' ';
		}
		$location .= $zipcode;
	}

	$location = apply_filters( 'rentfetch_filter_property_location', $location );
	return esc_html( $location );
}

/**
 * Echo the property location.
 *
 * @return void.
 */
function rentfetch_property_location() {
	$location = rentfetch_get_property_location();

	if ( $location ) {
		echo esc_html( $location );
	}
}

/**
 * Get the property location link
 *
 * @return string The property location link.
 */
function rentfetch_get_property_location_link() {
	$location      = rentfetch_get_property_location();
	$title         = rentfetch_get_property_title();
	$location_link = sprintf( 'https://www.google.com/maps/place/%s', $title . ' ' . $location );
	return esc_url( $location_link );
}

/**
 * Get the property city and state
 *
 * @return string The property city and state.
 */
function rentfetch_get_property_city_state() {

	$city  = sanitize_text_field( get_post_meta( get_the_ID(), 'city', true ) );
	$state = sanitize_text_field( get_post_meta( get_the_ID(), 'state', true ) );

	if ( $city && $state ) {
		$citystate = sprintf( '%s, %s', $city, $state );
	} elseif ( $city && ! $state ) {
		$citystate = $city;
	} elseif ( ! $city && $state ) {
		$citystate = $state;
	} else {
		$citystate = null;
	}

	return esc_html( apply_filters( 'rentfetch_filter_property_city_state', $citystate ) );
}

/**
 * Echo the property city and state
 *
 * @return void.
 */
function rentfetch_property_city_state() {
	$citystate = rentfetch_get_property_city_state();

	if ( $citystate ) {
		echo esc_html( $citystate );
	}
}

// * PROPERTY PHONE.

/**
 * Get the property phone number
 *
 * @return string The property phone number.
 */
function rentfetch_get_property_phone() {
	$phone = sanitize_text_field( get_post_meta( get_the_ID(), 'phone', true ) );

	return esc_html( apply_filters( 'rentfetch_filter_property_phone', $phone ) );
}

/**
 * Echo the property phone number
 *
 * @return void
 */
function rentfetch_property_phone() {
	echo esc_html( rentfetch_get_property_phone() );
}

// * PROPERTY URL.

/**
 * Get the property URL.
 *
 * @return string The property URL.
 */
function rentfetch_get_property_url() {

	$url = get_post_meta( get_the_ID(), 'url', true );

	return esc_url( apply_filters( 'rentfetch_filter_property_url', $url ) );
}

/**
 * Echo the property URL.
 *
 * @return void.
 */
function rentfetch_property_url() {
	echo esc_url( rentfetch_get_property_url() );
}

// * PROPERTY BEDROOMS.

/**
 * Get the property bedrooms.
 *
 * @return string The property bedrooms.
 */
function rentfetch_get_property_bedrooms() {

	$property_id = sanitize_text_field( get_post_meta( get_the_ID(), 'property_id', true ) );

	$floorplan_data = rentfetch_get_floorplans( $property_id );

	if ( ! isset( $floorplan_data['bedsrange'] ) ) {
		return;
	}

	return wp_kses_post( apply_filters( 'rentfetch_get_bedroom_number_label', $floorplan_data['bedsrange'] ) );
}

/**
 * Echo the property bedrooms.
 *
 * @return void.
 */
function rentfetch_property_bedrooms() {
	$bedrooms = rentfetch_get_property_bedrooms();

	if ( $bedrooms ) {
		echo wp_kses_post( $bedrooms );
	}
}

// * PROPERTY BATHROOMS

/**
 * Get the property bathrooms.
 *
 * @return string The property bathrooms.
 */
function rentfetch_get_property_bathrooms() {

	$property_id = esc_html( get_post_meta( get_the_ID(), 'property_id', true ) );

	$floorplan_data = rentfetch_get_floorplans( $property_id );

	if ( ! isset( $floorplan_data['bathsrange'] ) ) {
		return;
	}

	return wp_kses_post( apply_filters( 'rentfetch_get_bathroom_number_label', $floorplan_data['bathsrange'] ) );
}

/**
 * Echo the property bathrooms.
 *
 * @return void.
 */
function rentfetch_property_bathrooms() {
	$bathrooms = rentfetch_get_property_bathrooms();

	if ( $bathrooms ) {
		echo wp_kses_post( $bathrooms );
	}
}

// * PROPERTY SQUARE FEET

/**
 * Get the property square feet.
 *
 * @return string The property square feet.
 */
function rentfetch_get_property_square_feet() {
	$property_id = sanitize_text_field( get_post_meta( get_the_ID(), 'property_id', true ) );

	$floorplan_data = rentfetch_get_floorplans( $property_id );

	if ( ! isset( $floorplan_data['sqftrange'] ) ) {
		return;
	}

	return wp_kses_post( apply_filters( 'rentfetch_get_square_feet_number_label', $floorplan_data['sqftrange'] ) );
}

/**
 * Echo the property square feet.
 *
 * @return void.
 */
function rentfetch_property_square_feet() {
	$square_feet = rentfetch_get_property_square_feet();

	if ( $square_feet ) {
		echo wp_kses_post( $square_feet );
	}
}

// * PROPERTY RENT

/**
 * Get the property rent.
 *
 * @return string The property rent.
 */
function rentfetch_get_property_rent() {
	$property_id = sanitize_text_field( get_post_meta( get_the_ID(), 'property_id', true ) );

	$floorplan_data = rentfetch_get_floorplans( $property_id );

	if ( ! isset( $floorplan_data['rentrange'] ) ) {
		return;
	}

	$rent = apply_filters( 'rentfetch_filter_property_rent', $floorplan_data['rentrange'] );
	return esc_html( $rent );
}

/**
 * Echo the property rent.
 *
 * @return void.
 */
function rentfetch_property_rent() {
	$rent = rentfetch_get_property_rent();

	if ( $rent ) {
		echo esc_html( $rent );
	}
}

/**
 * Echo the property rent with a default label.
 *
 * @param   string $rent The property rent.
 *
 * @return  string The property rent with a default label.
 */
function rentfetch_default_property_rent_label( $rent ) {

	if ( $rent ) {
		return '$' . esc_html( $rent );
	}

	// This could return 'Call for Pricing' or 'Pricing unavailable' if pricing isn't available.
	return 'Call for Pricing';
}
add_filter( 'rentfetch_filter_property_rent', 'rentfetch_default_property_rent_label', 10, 1 );

// * PROPERTY AVAILABILITY

/**
 * Get the property availability.
 *
 * @return string|null The property availability.
 */
function rentfetch_get_property_availability() {
	$property_id = esc_html( get_post_meta( get_the_ID(), 'property_id', true ) );

	$floorplan_data = rentfetch_get_floorplans( $property_id );

	if ( isset( $floorplan_data['availability'] ) ) {

		$units_available = apply_filters( 'rentfetch_filter_property_availabile_units', $floorplan_data['availability'] );

		if ( $units_available > 0 ) {
			return esc_html( $units_available );
		}
	}

	if ( isset( $floorplan_data['available_date'] ) ) {
		$available_date = apply_filters( 'rentfetch_filter_property_availability_date', $floorplan_data['available_date'] );

		if ( $available_date ) {
			return esc_html( $available_date );
		}
	}

	return null;
}

/**
 * Echo the property availability.
 *
 * @return void.
 */
function rentfetch_property_availability() {
	$availability = rentfetch_get_property_availability();

	if ( $availability ) {
		echo esc_html( $availability );
	}
}

/**
 * Get the property available units label.
 *
 * @param   int $availability The property availability.
 *
 * @return  string The property available units label.
 */
function rentfetch_default_property_available_units_label( $availability ) {

	$availability = intval( $availability );

	if ( 1 === $availability ) {
		return esc_html( $availability ) . ' unit available';
	} elseif ( 1 >= $availability ) {
		return esc_html( $availability ) . ' units available';
	}
}
add_filter( 'rentfetch_filter_property_availabile_units', 'rentfetch_default_property_available_units_label', 10, 1 );

/**
 * Get the property availability with a default label.
 *
 * @param   string $availability_date The property availability date.
 *
 * @return  string The property availability date with a default label.
 */
function rentfetch_default_property_availability_date( $availability_date ) {

	if ( $availability_date ) {
		return 'Available ' . esc_html( $availability_date );
	}

	return null;
}
add_filter( 'rentfetch_filter_property_availability_date', 'rentfetch_default_property_availability_date', 10, 1 );

// * PROPERTY SPECIALS

/**
 * Get the property specials.
 *
 * @return string The property specials.
 */
function rentfetch_get_property_specials() {
	$property_id = sanitize_text_field( get_post_meta( get_the_ID(), 'property_id', true ) );

	$floorplan_data = rentfetch_get_floorplans( $property_id );

	if ( ! isset( $floorplan_data['property_has_specials'] ) ) {
		return;
	}

	$specials = apply_filters( 'rentfetch_filter_property_specials', $floorplan_data['property_has_specials'] );
	return wp_kses_post( $specials );
}

/**
 * Echo the property specials.
 *
 * @return void.
 */
function rentfetch_property_specials() {
	$specials = rentfetch_get_property_specials();

	if ( $specials ) {
		echo wp_kses_post( $specials );
	}
}

/**
 * Get the property specials label.
 *
 * @param   string $specials The property specials.
 *
 * @return  string The property specials label.
 */
function rentfetch_default_property_specials_label( $specials ) {

	if ( $specials ) {
		return 'Specials available';
	}

	return null;
}
add_filter( 'rentfetch_filter_property_specials', 'rentfetch_default_property_specials_label', 10, 1 );

/**
 * Get the property permalink.
 *
 * @param   string $url The property permalink.
 *
 * @return  string The property permalink.
 */
function rentfetch_default_property_permalink( $url ) {

	$url = get_the_permalink();

	return $url;
}
add_filter( 'rentfetch_filter_property_permalink', 'rentfetch_default_property_permalink', 10, 1 );

/**
 * Get the property permalink label.
 *
 * @param   string $url The property permalink label.
 *
 * @return  string The property permalink label.
 */
function rentfetch_default_property_permalink_label( $url ) {

	$url; // we don't need this variable for this use case, but it's required to be passed in the function.

	return 'View Property';
}
add_filter( 'rentfetch_filter_property_permalink_label', 'rentfetch_default_property_permalink_label', 10, 1 );

/**
 * Get the property permalink target.
 *
 * @param   string $url The property permalink target.
 *
 * @return  string The property permalink target.
 */
function rentfetch_default_property_permalink_target( $url ) {

	$url; // we don't need this variable for this use case, but it's required to be passed in the function.

	return '_self';
}
add_filter( 'rentfetch_filter_property_permalink_target', 'rentfetch_default_property_permalink_target', 10, 1 );

// * PROPERTY DESCRIPTION

/**
 * Get the property description
 *
 * @return  string The property description.
 */
function rentfetch_get_property_description() {
	$property_description = get_post_meta( get_the_ID(), 'description', true );
	$property_description = apply_filters( 'the_content', $property_description );
	$property_description = apply_filters( 'rentfetch_filter_property_description', $property_description );
	return wp_kses_post( $property_description );
}

/**
 * Echo the property description
 *
 * @return void.
 */
function rentfetch_property_description() {
	$property_description = rentfetch_get_property_description();
	if ( $property_description ) {
		echo wp_kses_post( $property_description );
	}
}
