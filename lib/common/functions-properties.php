<?php
/**
 * This file has the Rent Fetch functions for getting property data.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add a filter to the properties post classes.
 *
 * @param   [type]  $classes  [$classes description]
 *
 * @return  [type]            [return description]
 */
function rentfetch_properties_post_classes( $classes ) {
	
	$property_id = esc_html( get_post_meta( get_the_ID(), 'property_id', true ) );
	$floorplan_data = rentfetch_get_floorplans( $property_id );
	
	if ( isset( $floorplan_data['availability'] ) ) {
		$units_count = $floorplan_data['availability'];
	} else {
		$units_count = 0;
	}
	
	if ( $units_count > 0 ) {
		$classes[] = 'has-units-available';
	} else {
		$classes[] = 'no-units-available';
		
		$fade_out_unavailable = get_option( 'rentfetch_options_property_apply_styles_no_floorplans' );
		if ( $fade_out_unavailable === '1' ) {
			$classes[] = 'no-units-available-faded';
		}
	}

	return $classes;

}
add_filter( 'rentfetch_filter_properties_post_classes', 'rentfetch_properties_post_classes', 10, 1 );

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
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_tour_button' );

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

	// If the number is exactly 10 digits, format it as a US number without the country code.
	if ( strlen($cleaned) === 10 ) {
		return '(' . substr($cleaned, 0, 3) . ') ' . substr($cleaned, 3, 3) . '-' . substr($cleaned, 6);
	} 
	// Handle cases with a leading + and exactly 10 digits after the +.
	elseif ( preg_match( '/^\+(\d{10})$/', $cleaned, $matches ) ) {
		return '+1 (' . substr($matches[1], 0, 3) . ') ' . substr($matches[1], 3, 3) . '-' . substr($matches[1], 6);
	}
	// Handle cases with a leading +1 followed by 10 digits.
	elseif ( preg_match( '/^\+1(\d{10})$/', $cleaned, $matches ) ) {
		return '+1 (' . substr($matches[1], 0, 3) . ') ' . substr($matches[1], 3, 3) . '-' . substr($matches[1], 6);
	}
	// Handle cases with a leading 1 followed by 10 digits (without the +).
	elseif ( preg_match( '/^1(\d{10})$/', $cleaned, $matches ) ) {
		return '+1 (' . substr($matches[1], 0, 3) . ') ' . substr($matches[1], 3, 3) . '-' . substr($matches[1], 6);
	}
	// If the number has an international format (starts with a + and is not US)
	elseif ( preg_match( '/^\+(\d{1,3})(\d{3})(\d{4})$/', $cleaned, $matches ) ) {
		return '+' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3];
	}
	// Return the cleaned number as it is if it doesn't match known formats.
	else {
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
	$button = rentfetch_get_property_phone_button();
	
	if ( $button ) {
		echo wp_kses_post( $button );
	}
}

// * PROPERTY URL.

/**
 * Get the property URL.
 *
 * @return string The property URL.
 */
function rentfetch_get_property_url() {

	$url = get_post_meta( get_the_ID(), 'url', true );
	$url_override = get_post_meta( get_the_ID(), 'url_override', true );
	
	if ( $url_override ) {
		$url = $url_override;
	}
	
	return esc_url( apply_filters( 'rentfetch_filter_property_url', $url ) );
}

/**
 * For property archives, we might need to get (and modify) the property permalink.
 * 
 * @return string The property permalink.
 */
function rentfetch_get_property_permalink() {
	
	$permalink_behavior = get_option( 'rentfetch_options_property_external_linking_behavior', 'internal' );
	$url = rentfetch_get_property_url();
	
	if ( !$url ) {
		$url = get_the_permalink();		
	} else {
		if ( 'external' !== $permalink_behavior ) {
			$url = get_the_permalink();
		} else {
			$url = rentfetch_get_property_url();
		}
	}
	
	return esc_url( $url );
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
	$url            = rentfetch_get_property_url();
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
	$contact_button = sprintf( '<a class="email-link property-link" href="%s">Reach Out</a>', esc_html( $email_link ) );
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

// * PROPERTY TOUR BUTTON.

/**
 * Get the property email.
 *
 * @return string The property email.
 */
function rentfetch_get_property_tour_button() {
		
	$iframe    = get_post_meta( get_the_ID(), 'tour', true );
	$embedlink = null;
	$tour_link_text = 'Video Tour';
	
	// bail if we don't have anything to show.
	if ( ! $iframe ) {
		return;
	}
	
	wp_enqueue_style( 'rentfetch-glightbox-style' );
	wp_enqueue_script( 'rentfetch-glightbox-script' );
	wp_enqueue_script( 'rentfetch-glightbox-init' );

	// check against youtube.
	$youtube_pattern = '/src="https:\/\/www\.youtube\.com\/embed\/([^?"]+)\?/';
	preg_match( $youtube_pattern, $iframe, $youtube_matches );

	// if it's youtube and it's a full iframe.
	if ( isset( $youtube_matches[1] ) ) {
		$video_id   = $youtube_matches[1];
		$oembedlink = 'https://www.youtube.com/watch?v=' . $video_id;
		$embedlink  = sprintf( '<a class="tour-link property-link tour-link-youtube" data-gallery="post-%s" data-glightbox="type: video;" href="%s">%s</a>', get_the_ID(), $oembedlink, $tour_link_text );
	}

	$matterport_pattern = '/src="([^"]*matterport[^"]*)"/i'; // Added "matterport" to the pattern.
	preg_match( $matterport_pattern, $iframe, $matterport_matches );

	// if it's matterport and it's a full iframe.
	if ( isset( $matterport_matches[1] ) ) {
		$oembedlink = $matterport_matches[1];
		$embedlink  = sprintf( '<a class="tour-link property-link tour-link-matterport" data-gallery="post-%s" href="%s">%s</a>', get_the_ID(), $oembedlink, $tour_link_text );
	}

	// if it's anything else (like just an oembed, including an oembed for either matterport or youtube).
	if ( ! $embedlink ) {
		$oembedlink = $iframe;
		$embedlink  = sprintf( '<a class="tour-link property-link" target="_blank" data-gallery="post-%s" href="%s">%s</a>', get_the_ID(), $oembedlink, $tour_link_text );
	}
	
	return $embedlink;
}

/**
 * Echo the property email.
 *
 * @return void.
 */
function rentfetch_property_tour_button() {
	if ( rentfetch_get_property_tour_button() ) {
		echo wp_kses_post( rentfetch_get_property_tour_button() );
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
	$rent_range = null;
	$min_rent = null;
	
	// get the rent range if avail.
	if ( isset( $floorplan_data['rentrange'] ) ) {
		$rent_range = $floorplan_data['rentrange'];
	}
	
	// get the min rent array if avail.
	if ( isset( $floorplan_data['minimum_rent'] ) ) {
		$min_rent_array = $floorplan_data['minimum_rent'];
		
		// filter this array to remove any null values and any values below 100.
		$min_rent_array = array_filter( $min_rent_array, fn( $value ) => $value !== null && $value >= 100 );
		
		// if there's noting left in the array after filtering, set it to null.
		if ( !empty( $min_rent_array ) ) {
			
			// get the lowest remaining value in the array.
			$min_rent = number_format( (int) min( $min_rent_array ) );
		} else {
			$min_rent = null;
		}
	}

	// return the string for display.
	if ( 'range' === $pricing_display ) {
		if ( $rent_range ) {
			$rent = '$' . $rent_range;
		} else {
			$rent = apply_filters( 'rentfetch_filter_property_pricing_no_price_available', 'Call for Pricing' );
		}
	} elseif ( 'minimum' === $pricing_display ) {
		if ( $min_rent ) {
			$rent = 'From $' . $min_rent;
		} else {
			$rent = apply_filters( 'rentfetch_filter_property_pricing_no_price_available', 'Call for Pricing' );
		}
	}
	
	// make our variables a bit friendlier in case this filter is used, to make it easier to understand what's going on.
	if ( !isset( $floorplan_data['rentrange'] ) ) {
		$floorplan_data['rentrange'] = null;
	}
	
	if ( !isset( $floorplan_data['minimum_rent'] ) ) {
		$floorplan_data['minimum_rent'] = null;
	}
	
	if ( !isset( $floorplan_data['maximum_rent'] ) ) {
		$floorplan_data['maximum_rent'] = null;
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
 * Get property specials based on property-level meta fields (similar to floorplan specials).
 *
 * @return string|null The property specials text.
 */
function rentfetch_get_property_specials_from_meta() {

	$has_specials = get_post_meta( get_the_ID(), 'has_specials', true );
	$specials_override_text = get_post_meta( get_the_ID(), 'specials_override_text', true );
	
	if ( $has_specials && !$specials_override_text ) {
		$specials_text = 'Specials available';
	} elseif ( $specials_override_text ) {
		$specials_text = $specials_override_text;
	} else {
		$specials_text = null;
	}

	return apply_filters( 'rentfetch_filter_property_specials_from_meta', $specials_text );
}

/**
 * Echo property specials from meta fields.
 *
 * @return void.
 */
function rentfetch_property_specials_from_meta() {
	$specials = rentfetch_get_property_specials_from_meta();
	
	if ( $specials ) {
		echo wp_kses_post( $specials );
	}
}

/**
 * Property specials label filter (similar to floorplan specials label).
 *
 * @param string $specials_text The specials text.
 *
 * @return string|null The filtered specials text.
 */
function rentfetch_property_specials_label( $specials_text ) {

	if ( $specials_text ) {
		return $specials_text;
	}

	return null;
}
add_filter( 'rentfetch_filter_property_specials_from_meta', 'rentfetch_property_specials_label', 10, 1 );

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

//* PROPERTY TOUR

/**
 * Get the tour markup
 *
 * @return string the tour markup.
 */
function rentfetch_get_property_tour() {

	$iframe    = get_post_meta( get_the_ID(), 'tour', true );
	$embedlink = null;

	if ( $iframe ) {

		wp_enqueue_style( 'rentfetch-glightbox-style' );
		wp_enqueue_script( 'rentfetch-glightbox-script' );
		wp_enqueue_script( 'rentfetch-glightbox-init' );

		// check against youtube.
		$youtube_pattern = '/src="https:\/\/www\.youtube\.com\/embed\/([^?"]+)\?/';
		preg_match( $youtube_pattern, $iframe, $youtube_matches );

		// if it's youtube and it's a full iframe.
		if ( isset( $youtube_matches[1] ) ) {
			$video_id   = $youtube_matches[1];
			$oembedlink = 'https://www.youtube.com/watch?v=' . $video_id;
			$embedlink  = sprintf( '<div class="tour-link-wrapper"><a class="tour-link tour-link-youtube" data-gallery="post-%s" data-glightbox="type: video;" href="%s"></a></div>', get_the_ID(), $oembedlink );
		}

		$matterport_pattern = '/src="([^"]*matterport[^"]*)"/i'; // Added "matterport" to the pattern.
		preg_match( $matterport_pattern, $iframe, $matterport_matches );

		// if it's matterport and it's a full iframe.
		if ( isset( $matterport_matches[1] ) ) {
			$oembedlink = $matterport_matches[1];
			$embedlink  = sprintf( '<div class="tour-link-wrapper"><a class="tour-link tour-link-matterport" data-gallery="post-%s" href="%s"></a></div>', get_the_ID(), $oembedlink );
		}

		// if it's anything else (like just an oembed, including an oembed for either matterport or youtube).
		if ( ! $embedlink ) {
			$oembedlink = $iframe;
			$embedlink  = sprintf( '<div class="tour-link-wrapper"><a class="tour-link" target="_blank" data-gallery="post-%s" href="%s"></a></div>', get_the_ID(), $oembedlink );
		}
	}

	return apply_filters( 'rentfetch_filter_property_tour', $embedlink );
}

/**
 * Echoes the property fees embed code.
 *
 * @param int|null $post_id Post ID.
 * @return void
 */
function rentfetch_property_fees_embed( $post_id = null ) {
	echo rentfetch_get_property_fees_embed( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Gets the property fees embed code.
 *
 * @param int|null $post_id Post ID.
 * @return string The property fees embed code.
 */
function rentfetch_get_property_fees_embed( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$property_fees_embed = get_post_meta( $post_id, 'property_fees_embed', true );

	return apply_filters( 'rentfetch_filter_property_fees_embed', $property_fees_embed, $post_id );
}

function rentfetch_website_single_property_site_get_property_id() {
	// check if this is a single-property website by querying the 'properties' post type and checking the CPT to see if we have a single post or more than one (or zero).
	$property_query = new WP_Query( array(
		'post_type'      => 'properties',
		'posts_per_page' => 2,
		'post_status'    => 'publish',
	) );
	
	// if there's exactly one property, get the value of the property_id meta field.
	if ( $property_query->have_posts() && $property_query->found_posts === 1 ) {
		$property_query->the_post();
		$post_id = $property_query->posts[0]->ID;	
	} else {
		return;
	}
	
	// need to reset the query so that we don't mess up the main query.
	wp_reset_postdata();
	
	return $post_id;
}

function rentfetch_property_fees_embed_and_wrap() {
	
	// check to see if this is a single-property website. If it is, the property post_id will be returned.
	$post_id = rentfetch_website_single_property_site_get_property_id();
	
	if ( ! $post_id ) {
		return; // if we don't have a post ID, we can't output the fees embed.
	}
	
	$embed = rentfetch_get_property_fees_embed( $post_id );
	if ( ! $embed ) {
		return; // if we don't have an embed, we can't output the fees embed.
	}

	// output the property fees embed code.
	// Note: This function is used in both the simple grid and search results, so we need to pass the post ID.
	// rentfetch_property_fees_embed( $post_id );
	echo '<div class="rentfetch-after-floorplans-grid-search-property-fees-embed-wrapper">';
		echo $embed;
	echo '</div>';
	
}
add_action( 'rentfetch_after_floorplans_simple_grid', 'rentfetch_property_fees_embed_and_wrap' );
add_action( 'rentfetch_after_floorplans_search', 'rentfetch_property_fees_embed_and_wrap' );


function rentfetch_property_fees_notes() {
	
	// check to see if this is a single-property website. If it is, the property post_id will be returned.
	$post_id = rentfetch_website_single_property_site_get_property_id();
	
	if ( ! $post_id ) {
		return; // if we don't have a post ID, we can't output the fees notes.
	}
	
	$embed = rentfetch_get_property_fees_embed( $post_id );
	if ( ! $embed ) {
		return; // if we don't have an embed, we aren't going to output the notes about it.
	}

	// get the property fees notes with default text that can be filtered.
	$default_fees_notes = "Please note that prices shown are base rent. To help budget your monthly costs and make it easy to understand what your rent includes and what may be additional, we've included the list of potential fees below the floor plans, found at the bottom of the page.";
	$property_fees_notes = apply_filters( 'rentfetch_filter_property_fees_notes', $default_fees_notes, $post_id );

	if ( $property_fees_notes ) {
		echo '<div class="rentfetch-before-floorplans-grid-search-property-fees-notes">';
			echo wp_kses_post( wpautop( $property_fees_notes ) );
		echo '</div>';
	}
}
add_action( 'rentfetch_before_floorplans_simple_grid', 'rentfetch_property_fees_notes' );
add_action( 'rentfetch_before_floorplans_search', 'rentfetch_property_fees_notes' );