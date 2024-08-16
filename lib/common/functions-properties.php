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
	return $title;
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
	return $address;
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
	return $city;
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
	return $state;
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
	return $zipcode;
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
	return $location;
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

// * PROPERTY BUTTONS.
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_location_button' );
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_website_button' );
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_phone_button' );
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_contact_button' );

/**
 * Get the property location link
 *
 * @return string The property location link.
 */
function rentfetch_get_property_location_link() {
	$location = rentfetch_get_property_location();
	$title    = rentfetch_get_property_title();

	$location_link = sprintf( 'https://www.google.com/maps/search/?api=1&query=%s', $title . ' ' . $location );

	return $location_link;
}

// * PROPERTY LOCATION BUTTON.

/**
 * Get the property location button
 *
 * @return string The property location button.
 */
function rentfetch_get_property_location_button() {
	$location_link   = rentfetch_get_property_location_link();
	$location_button = sprintf( '<a class="location-link property-link" href="%s" target="_blank">Get Directions</a>', esc_url( $location_link ) );
	return apply_filters( 'rentfetch_filter_property_location_button', $location_button );
}

/**
 * Echo the property location button
 *
 * @return void.
 */
function rentfetch_property_location_button() {
	echo wp_kses_post( rentfetch_get_property_location_button() );
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

	return apply_filters( 'rentfetch_filter_property_city_state', $citystate );
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
 * Format the phone number for display
 *
 * @param   string $phone  the unformatted phone number.
 *
 * @return  string the formatted phone number
 */
function rentfetch_format_phone_number( $phone ) {
    // Remove all characters except digits and the plus sign.
    $cleaned = preg_replace( '/[^\d+]/', '', $phone );

    // Handle cases with a leading +1 or just 1 followed by 10 digits (standard US format).
    if ( preg_match( '/^\+?1?(\d{10})$/', $cleaned, $matches ) ) {
        // Format the number as +1 (XXX) XXX-XXXX
        return '+1 (' . substr($matches[1], 0, 3) . ') ' . substr($matches[1], 3, 3) . '-' . substr($matches[1], 6);
    } elseif ( preg_match( '/^\+(\d{3})(\d{3})(\d{4})$/', $cleaned, $matches ) ) {
        // Handle cases where the number starts with a country code and is not US
        return '+' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3];
    } elseif ( strlen( $cleaned ) === 10 ) {
        // Assume US number and add country code, then format it.
        return '+1 (' . substr($cleaned, 0, 3) . ') ' . substr($cleaned, 3, 3) . '-' . substr($cleaned, 6);
    } else {
        // Return the cleaned number as it is if it doesn't match known formats.
        return $cleaned;
    }
}




/**
 * Format the phone number for use in a tel: link
 *
 * @param   string $phone  the unformatted phone number.
 *
 * @return  string  the formatted phone number for use in a tel: link
 */
function rentfetch_format_phone_number_link( $phone ) {
	// Remove all characters except digits and the plus sign.
	$cleaned = preg_replace( '/[^\d+]/', '', $phone );

	// Check if the number starts with a plus sign and is at least 11 digits long.
	if ( substr( $cleaned, 0, 1 ) === '+' && strlen( $cleaned ) > 10 ) {
		return $cleaned; // Return the cleaned international number.
	} elseif ( strlen( $cleaned ) === 10 ) {
		return '+1' . $cleaned; // Assume US number and add country code.
	} elseif ( 11 === strlen( $cleaned ) && '1' === $cleaned[0] ) {
		return '+' . $cleaned; // Format as a US number with country code.
	} else {
		return ''; // Return an empty string if the phone number is not valid.
	}
}

/**
 * Get the property phone number
 *
 * @return string The property phone number.
 */
function rentfetch_get_property_phone() {
	$phone = sanitize_text_field( get_post_meta( get_the_ID(), 'phone', true ) );

	if ( $phone ) {

		$phone = rentfetch_format_phone_number( $phone );

	}

	return apply_filters( 'rentfetch_filter_property_phone', $phone );
}

/**
 * Echo the property phone number
 *
 * @return void
 */
function rentfetch_property_phone() {
	$phone = rentfetch_get_property_phone();

	if ( $phone ) {
		echo esc_html( $phone );
	}
}

/**
 * Get the property phone number
 *
 * @return string The property phone number.
 */
function rentfetch_get_property_phone_button() {
	$phone        = rentfetch_get_property_phone();
	$phone_link   = rentfetch_format_phone_number_link( $phone );
	$phone_button = sprintf( '<a class="phone-link property-link" href="tel:%s">%s</a>', esc_html( $phone_link ), esc_html( $phone ) );

	if ( $phone ) {
		return apply_filters( 'rentfetch_filter_property_phone_button', $phone_button );
	} else {
		return;
	}
}

/**
 * Echo the property phone number
 *
 * @return void
 */
function rentfetch_property_phone_button() {
	echo wp_kses_post( rentfetch_get_property_phone_button() );
}

// * PROPERTY URL.

/**
 * Get the property URL.
 *
 * @return string The property URL.
 */
function rentfetch_get_property_url() {

	$url = get_post_meta( get_the_ID(), 'url', true );

	return $url;
}

/**
 * Echo the property URL.
 *
 * @return void.
 */
function rentfetch_property_url() {
	echo esc_url( rentfetch_get_property_url() );
}

// * PROPERTY WEBSITE

/**
 * Get the property website.
 *
 * @return string The property website.
 */
function rentfetch_get_property_website_button() {
	$url            = apply_filters( 'rentfetch_filter_property_url', rentfetch_get_property_url() );
	$target         = rentfetch_get_link_target( $url );
	$website_button = sprintf( '<a class="url-link property-link" href="%s" target="%s">Visit Website</a>', esc_html( $url ), esc_attr( $target ) );

	if ( $url ) {
		return apply_filters( 'rentfetch_filter_property_website', $website_button );
	} else {
		return;
	}
}

/**
 * Echo the property website.
 *
 * @return void.
 */
function rentfetch_property_website_button() {
	if ( rentfetch_get_property_url() ) {
		echo wp_kses_post( rentfetch_get_property_website_button() );
	}
}

// * PROPERTY EMAIL.

/**
 * Get the property email.
 *
 * @return string The property email.
 */
function rentfetch_get_property_contact_button() {
	$email          = sanitize_email( apply_filters( 'rentfetch_filter_property_email_address', get_post_meta( get_the_ID(), 'email', true ) ) );
	$email_link     = 'mailto:' . $email;
	$contact_button = sprintf( '<a class="email-link property-link" href="%s">Reach out</a>', esc_html( $email_link ) );
	$email_button   = apply_filters( 'rentfetch_filter_property_contact_button', $contact_button );

	if ( $email ) {
		return $email_button;
	} else {
		return;
	}
}

/**
 * Echo the property email.
 *
 * @return void.
 */
function rentfetch_property_contact_button() {
	if ( rentfetch_get_property_contact_button() ) {
		echo wp_kses_post( rentfetch_get_property_contact_button() );
	}
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

	return apply_filters( 'rentfetch_get_bedroom_number_label', $floorplan_data['bedsrange'] );
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

	return apply_filters( 'rentfetch_get_bathroom_number_label', $floorplan_data['bathsrange'] );
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

	return apply_filters( 'rentfetch_get_square_feet_number_label', $floorplan_data['sqftrange'] );
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
function rentfetch_get_property_pricing() {
	$property_id = sanitize_text_field( get_post_meta( get_the_ID(), 'property_id', true ) );

	$floorplan_data  = rentfetch_get_floorplans( $property_id );
	$pricing_display = get_option( 'rentfetch_options_property_pricing_display', 'range' );

	if ( isset( $floorplan_data['rentrange'] ) ) {
		if ( null !== $floorplan_data['rentrange'] ) {
			$rent_range = '$' . $floorplan_data['rentrange'];
		} else {
			$rent_range = 'Call for Pricing';
		}
	}

	if ( isset( $floorplan_data['minimum_rent'] ) ) {
		if ( is_array( $floorplan_data['minimum_rent'] ) ) {
			$rent_min = 'From $' . number_format( min( $floorplan_data['minimum_rent'] ) );
		} else {
			$rent_min = 'Call for Pricing';
		}
	}

	if ( 'range' === $pricing_display ) {
		$rent = $rent_range;
	} elseif ( 'minimum' === $pricing_display ) {
		$rent = $rent_min;
	}

	return apply_filters( 'rentfetch_filter_property_pricing', $rent, $floorplan_data['rentrange'], $floorplan_data['minimum_rent'], $floorplan_data['maximum_rent'] );
}

/**
 * Echo the property rent.
 *
 * @return void.
 */
function rentfetch_property_pricing() {
	$rent = rentfetch_get_property_pricing();

	if ( $rent ) {
		echo esc_html( $rent );
	}
}

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
			return $units_available;
		}
	}

	if ( isset( $floorplan_data['available_date'] ) ) {
		$available_date = apply_filters( 'rentfetch_filter_property_availability_date', $floorplan_data['available_date'] );

		if ( $available_date ) {
			return $available_date;
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
		return $availability . ' unit available';
	} elseif ( 1 <= $availability ) {
		return $availability . ' units available';
	}

	return null;
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
		return 'Available ' . $availability_date;
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
	return $specials;
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
	return $property_description;
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
