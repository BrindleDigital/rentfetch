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
 * Determine whether property fees should be shown on the frontend.
 *
 * Missing options default to enabled so existing installs continue showing fees.
 *
 * @return bool
 */
function rentfetch_should_show_property_fees() {
	$show_property_fees = get_option( 'rentfetch_options_show_property_fees', '1' );
	$should_show        = ( '0' !== (string) $show_property_fees );

	return (bool) apply_filters( 'rentfetch_filter_should_show_property_fees', $should_show );
}

/**
 * Helper function to get the WordPress post ID from a property_id meta value.
 *
 * @param string $property_id The property_id meta value.
 * @return int|null The post ID if found, null otherwise.
 */
function rentfetch_get_post_id_from_property_id( $property_id ) {
	if ( ! $property_id ) {
		return null;
	}

	$args = array(
		'post_type'      => 'properties',
		'meta_key'       => 'property_id',
		'meta_value'     => $property_id,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	);

	$posts = get_posts( $args );

	if ( ! empty( $posts ) ) {
		return $posts[0];
	}

	return null;
}

/**
 * Add a filter to the properties post classes.
 *
 * @param array  $classes     The current classes array.
 * @param string $property_id Optional property_id meta value.
 *
 * @return  array               The modified classes array.
 */
function rentfetch_properties_post_classes( $classes, $property_id = null ) {

	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return $classes;
		}
	} else {
		$post_id = get_the_ID();
	}

	$property_id_meta = esc_html( get_post_meta( $post_id, 'property_id', true ) );
	$floorplan_data   = rentfetch_get_floorplans( $property_id_meta );

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
		if ( '1' === $fade_out_unavailable ) {
			$classes[] = 'no-units-available-faded';
		}
	}

	return $classes;
}
add_filter( 'rentfetch_filter_properties_post_classes', 'rentfetch_properties_post_classes', 10, 2 );

// * PROPERTY TITLE

/**
 * Get the property title.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property title.
 */
function rentfetch_get_property_title( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
		$title = get_the_title( $post_id );
	} else {
		$title = get_the_title();
	}
	$title = apply_filters( 'rentfetch_filter_property_title', $title );
	return $title;
}

/**
 * Echo the property title.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_title( $property_id = null ) {
	$title = rentfetch_get_property_title( $property_id );
	if ( $title ) {
		echo esc_html( $title );
	}
}

// * PROPERTY LOCATION

/**
 * Get the property address
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property address.
 */
function rentfetch_get_property_address( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}
	$address = get_post_meta( $post_id, 'address', true );
	return $address;
}

/**
 * Echo the property address.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_address( $property_id = null ) {
	$address = rentfetch_get_property_address( $property_id );

	if ( $address ) {
		echo esc_html( $address );
	}
}

/**
 * Get the city of the property.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property city.
 */
function rentfetch_get_property_city( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}
	$city = get_post_meta( $post_id, 'city', true );
	return $city;
}

/**
 * Echo the city of the property.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_city( $property_id = null ) {
	$city = rentfetch_get_property_city( $property_id );

	if ( $city ) {
		echo esc_html( $city );
	}
}

/**
 * Get the state of the property.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property state.
 */
function rentfetch_get_property_state( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}
	$state = get_post_meta( $post_id, 'state', true );
	return $state;
}

/**
 * Echo the state of the property.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_state( $property_id = null ) {
	$state = rentfetch_get_property_state( $property_id );

	if ( $state ) {
		echo esc_html( $state );
	}
}

/**
 * Get the property zipcode.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property zipcode.
 */
function rentfetch_get_property_zipcode( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}
	$zipcode = get_post_meta( $post_id, 'zipcode', true );
	return $zipcode;
}

/**
 * Echo the property zipcode.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_zipcode( $property_id = null ) {
	$zipcode = rentfetch_get_property_zipcode( $property_id );

	if ( $zipcode ) {
		echo esc_html( $zipcode );
	}
}

/**
 * Get the property location
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property location.
 */
function rentfetch_get_property_location( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}

	$address = sanitize_text_field( get_post_meta( $post_id, 'address', true ) );
	$city    = sanitize_text_field( get_post_meta( $post_id, 'city', true ) );
	$state   = sanitize_text_field( get_post_meta( $post_id, 'state', true ) );
	$zipcode = sanitize_text_field( get_post_meta( $post_id, 'zipcode', true ) );

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

	return apply_filters( 'rentfetch_filter_property_location', $location );
}

/**
 * Echo the property location.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_location( $property_id = null ) {
	$location = rentfetch_get_property_location( $property_id );

	if ( $location ) {
		echo esc_html( $location );
	}
}

// * PROPERTY BUTTONS.
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_location_button' );
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_website_button' );
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_phone_button' );
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_contact_button' );
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_tour_booking_button' );
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_apply_online_button' );
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_tour_button' );
add_action( 'rentfetch_do_single_property_links', 'rentfetch_property_office_hours_button' );

/**
 * Get the property location link
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property location link.
 */
function rentfetch_get_property_location_link( $property_id = null ) {
	$location = rentfetch_get_property_location( $property_id );
	$title    = rentfetch_get_property_title( $property_id );

	$location_link = sprintf( 'https://www.google.com/maps/search/?api=1&query=%s', $title . ' ' . $location );

	return $location_link;
}

// * PROPERTY LOCATION BUTTON.

/**
 * Get the property location button
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $css_class Optional additional CSS class.
 * @return string The property location button.
 */
function rentfetch_get_property_location_button( $property_id = null, $css_class = '' ) {
	$location_link = rentfetch_get_property_location_link( $property_id );
	$classes       = 'location-link property-link';
	if ( ! empty( $css_class ) ) {
		$classes .= ' ' . esc_attr( $css_class );
	}
	$tracking_attrs  = rentfetch_get_tracking_data_attributes( 'rentfetch_directions_click', rentfetch_get_property_tracking_context( $property_id ) );
	$svg             = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 location-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>';
	$location_button = sprintf( '<a class="%s" href="%s" target="_blank"%s>%sGet Directions</a>', $classes, esc_url( $location_link ), $tracking_attrs, $svg );
	return apply_filters( 'rentfetch_filter_property_location_button', $location_button, $property_id, $css_class );
}

/**
 * Echo the property location button
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_location_button( $property_id = null ) {
	$allowed_html = array_merge(
		wp_kses_allowed_html( 'post' ),
		array(
			'svg'  => array(
				'xmlns'        => true,
				'fill'         => true,
				'viewbox'      => true,
				'stroke-width' => true,
				'stroke'       => true,
				'class'        => true,
			),
			'path' => array(
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'd'               => true,
			),
		)
	);
	echo wp_kses( rentfetch_get_property_location_button( $property_id ), $allowed_html );
}

/**
 * Get the property city and state
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property city and state.
 */
function rentfetch_get_property_city_state( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}

	$city  = sanitize_text_field( get_post_meta( $post_id, 'city', true ) );
	$state = sanitize_text_field( get_post_meta( $post_id, 'state', true ) );

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
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_city_state( $property_id = null ) {
	$citystate = rentfetch_get_property_city_state( $property_id );

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
	if ( strlen( $cleaned ) === 10 ) {
		return '(' . substr( $cleaned, 0, 3 ) . ') ' . substr( $cleaned, 3, 3 ) . '-' . substr( $cleaned, 6 );
	} elseif ( preg_match( '/^\+(\d{10})$/', $cleaned, $matches ) ) {
		// Handle cases with a leading + and exactly 10 digits after the +.
		return '+1 (' . substr( $matches[1], 0, 3 ) . ') ' . substr( $matches[1], 3, 3 ) . '-' . substr( $matches[1], 6 );
	} elseif ( preg_match( '/^\+1(\d{10})$/', $cleaned, $matches ) ) {
		// Handle cases with a leading +1 followed by 10 digits.
		return '+1 (' . substr( $matches[1], 0, 3 ) . ') ' . substr( $matches[1], 3, 3 ) . '-' . substr( $matches[1], 6 );
	} elseif ( preg_match( '/^1(\d{10})$/', $cleaned, $matches ) ) {
		// Handle cases with a leading 1 followed by 10 digits (without the +).
		return '+1 (' . substr( $matches[1], 0, 3 ) . ') ' . substr( $matches[1], 3, 3 ) . '-' . substr( $matches[1], 6 );
	} elseif ( preg_match( '/^\+(\d{1,3})(\d{3})(\d{4})$/', $cleaned, $matches ) ) {
		// Handle cases with a leading + and more than 10 digits (international numbers).
		return '+' . $matches[1] . ' ' . $matches[2] . ' ' . $matches[3];
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
 * @param string $property_id Optional property_id meta value.
 * @return string The property phone number.
 */
function rentfetch_get_property_phone( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}
	$phone = sanitize_text_field( get_post_meta( $post_id, 'phone', true ) );

	if ( $phone ) {

		$phone = rentfetch_format_phone_number( $phone );

	}

	return apply_filters( 'rentfetch_filter_property_phone', $phone );
}

/**
 * Echo the property phone number
 *
 * @param string $property_id Optional property_id meta value.
 * @return void
 */
function rentfetch_property_phone( $property_id = null ) {
	$phone = rentfetch_get_property_phone( $property_id );

	if ( $phone ) {
		echo esc_html( $phone );
	}
}

/**
 * Get the property phone number
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $css_class Optional additional CSS class.
 * @return string The property phone number.
 */
function rentfetch_get_property_phone_button( $property_id = null, $css_class = '' ) {
	$phone      = rentfetch_get_property_phone( $property_id );
	$phone_link = rentfetch_format_phone_number_link( $phone );
	$classes    = 'phone-link property-link';
	if ( ! empty( $css_class ) ) {
		$classes .= ' ' . esc_attr( $css_class );
	}
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_phonecall_click', rentfetch_get_property_tracking_context( $property_id ) );
	$svg            = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 phone-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>';
	$phone_button   = sprintf( '<a class="%s" href="tel:%s"%s>%s%s</a>', $classes, esc_html( $phone_link ), $tracking_attrs, $svg, esc_html( $phone ) );

	if ( $phone ) {
		return apply_filters( 'rentfetch_filter_property_phone_button', $phone_button, $property_id, $css_class );
	} else {
		return;
	}
}

/**
 * Echo the property phone number
 *
 * @param string $property_id Optional property_id meta value.
 * @return void
 */
function rentfetch_property_phone_button( $property_id = null ) {
	$button = rentfetch_get_property_phone_button( $property_id );

	if ( $button ) {
		$allowed_html = array_merge(
			wp_kses_allowed_html( 'post' ),
			array(
				'svg'  => array(
					'xmlns'        => true,
					'fill'         => true,
					'viewbox'      => true,
					'stroke-width' => true,
					'stroke'       => true,
					'class'        => true,
				),
				'path' => array(
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
					'd'               => true,
				),
			)
		);
		echo wp_kses( $button, $allowed_html );
	}
}

// * PROPERTY URL.

/**
 * Get the property URL.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property URL.
 */
function rentfetch_get_property_url( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}

	$url          = get_post_meta( $post_id, 'url', true );
	$url_override = get_post_meta( $post_id, 'url_override', true );

	if ( $url_override ) {
		$url = $url_override;
	}

	return esc_url( apply_filters( 'rentfetch_filter_property_url', $url ) );
}

/**
 * For property archives, we might need to get (and modify) the property permalink.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property permalink.
 */
function rentfetch_get_property_permalink( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}

	$permalink_behavior = get_option( 'rentfetch_options_property_external_linking_behavior', 'internal' );
	$url                = rentfetch_get_property_url( $property_id );

	if ( ! $url ) {
		$url = get_the_permalink( $post_id );
	} elseif ( 'external' !== $permalink_behavior ) {
			$url = get_the_permalink( $post_id );
	} else {
		$url = rentfetch_get_property_url( $property_id );
	}

	return esc_url( $url );
}

/**
 * Echo the property URL.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_url( $property_id = null ) {
	echo esc_url( rentfetch_get_property_url( $property_id ) );
}

// * RESIDENT PORTAL URL.

/**
 * Get the property resident portal URL.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property resident portal URL.
 */
function rentfetch_get_property_resident_portal_url( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}

	$url = get_post_meta( $post_id, 'resident_portal_url', true );

	return esc_url( apply_filters( 'rentfetch_filter_property_resident_portal_url', $url, $property_id ) );
}

/**
 * Echo the property resident portal URL.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_resident_portal_url( $property_id = null ) {
	echo esc_url( rentfetch_get_property_resident_portal_url( $property_id ) );
}

// * PROPERTY WEBSITE

/**
 * Get the property website.
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $css_class Optional additional CSS class.
 * @return string The property website.
 */
function rentfetch_get_property_website_button( $property_id = null, $css_class = '' ) {
	$url     = rentfetch_get_property_url( $property_id );
	$target  = rentfetch_get_link_target( $url );
	$classes = 'url-link property-link';
	if ( ! empty( $css_class ) ) {
		$classes .= ' ' . esc_attr( $css_class );
	}
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_visitpropertywebsite_click', rentfetch_get_property_tracking_context( $property_id ) );
	$svg            = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 website-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>';
	$website_button = sprintf( '<a class="%s" href="%s" target="%s"%s>%sVisit Website</a>', $classes, esc_html( $url ), esc_attr( $target ), $tracking_attrs, $svg );

	if ( $url ) {
		return apply_filters( 'rentfetch_filter_property_website', $website_button, $property_id, $css_class );
	} else {
		return;
	}
}

/**
 * Echo the property website.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_website_button( $property_id = null ) {
	if ( rentfetch_get_property_url( $property_id ) ) {
		$allowed_html = array_merge(
			wp_kses_allowed_html( 'post' ),
			array(
				'svg'  => array(
					'xmlns'        => true,
					'fill'         => true,
					'viewbox'      => true,
					'stroke-width' => true,
					'stroke'       => true,
					'class'        => true,
				),
				'path' => array(
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
					'd'               => true,
				),
			)
		);
		echo wp_kses( rentfetch_get_property_website_button( $property_id ), $allowed_html );
	}
}

// * PROPERTY EMAIL.

/**
 * Get the property email.
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $css_class Optional additional CSS class.
 * @return string The property email.
 */
function rentfetch_get_property_contact_button( $property_id = null, $css_class = '' ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}
	$email      = sanitize_email( apply_filters( 'rentfetch_filter_property_email_address', get_post_meta( $post_id, 'email', true ) ) );
	$email_link = 'mailto:' . $email;
	$classes    = 'email-link property-link';
	if ( ! empty( $css_class ) ) {
		$classes .= ' ' . esc_attr( $css_class );
	}
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_emailus_click', rentfetch_get_property_tracking_context( $property_id ) );
	$svg            = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 email-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>';
	$contact_button = sprintf( '<a class="%s" href="%s"%s>%sEmail Us</a>', $classes, esc_html( $email_link ), $tracking_attrs, $svg );
	$email_button   = apply_filters( 'rentfetch_filter_property_contact_button', $contact_button, $property_id, $css_class );

	if ( $email ) {
		return $email_button;
	} else {
		return;
	}
}

/**
 * Echo the property email.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_contact_button( $property_id = null ) {
	if ( rentfetch_get_property_contact_button( $property_id ) ) {
		$allowed_html = array_merge(
			wp_kses_allowed_html( 'post' ),
			array(
				'svg'  => array(
					'xmlns'        => true,
					'fill'         => true,
					'viewbox'      => true,
					'stroke-width' => true,
					'stroke'       => true,
					'class'        => true,
				),
				'path' => array(
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
					'd'               => true,
				),
			)
		);
		echo wp_kses( rentfetch_get_property_contact_button( $property_id ), $allowed_html );
	}
}

/**
 * Get the property email address.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property email address.
 */
function rentfetch_get_property_email( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}
	$email = sanitize_email( apply_filters( 'rentfetch_filter_property_email_address', get_post_meta( $post_id, 'email', true ) ) );
	return $email;
}

/**
 * Echo the property email address.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_email( $property_id = null ) {
	$email = rentfetch_get_property_email( $property_id );
	if ( $email ) {
		echo esc_html( $email );
	}
}

/**
 * Get the property email link.
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $css_class Optional additional CSS class.
 * @return string The property email link.
 */
function rentfetch_get_property_email_link( $property_id = null, $css_class = '' ) {
	$email      = rentfetch_get_property_email( $property_id );
	$email_link = 'mailto:' . $email;
	$classes    = 'email-link';
	if ( ! empty( $css_class ) ) {
		$classes .= ' ' . esc_attr( $css_class );
	}
	$email_button = sprintf( '<a class="%s" href="%s">%s</a>', $classes, esc_html( $email_link ), esc_html( $email ) );

	if ( $email ) {
		return apply_filters( 'rentfetch_filter_property_email_link', $email_button, $property_id, $css_class );
	} else {
		return;
	}
}

/**
 * Echo the property email link.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_email_link( $property_id = null ) {
	$link = rentfetch_get_property_email_link( $property_id );
	if ( $link ) {
		echo wp_kses_post( $link );
	}
}

// * PROPERTY TOUR BUTTON.

/**
 * Get the property email.
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $css_class Optional additional CSS class.
 * @return string The property email.
 */
function rentfetch_get_property_tour_button( $property_id = null, $css_class = '' ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}

	$tours          = rentfetch_get_property_tours( $post_id );
	$embedlink      = null;
	$tour_link_text = 'Video Tour';
	$svg            = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 tour-icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />
</svg>';

	// bail if we don't have anything to show.
	if ( ! $tours ) {
		return;
	}

	wp_enqueue_style( 'rentfetch-glightbox-style' );
	wp_enqueue_script( 'rentfetch-glightbox-script' );
	wp_enqueue_script( 'rentfetch-glightbox-init' );

	$tour = $tours[0];

	$classes = 'tour-link property-link';
	if ( in_array( $tour['type'], array( 'youtube', 'matterport' ), true ) ) {
		$classes .= ' tour-link-' . $tour['type'];
	}
	if ( ! empty( $css_class ) ) {
		$classes .= ' ' . esc_attr( $css_class );
	}

	$target         = in_array( $tour['type'], array( 'youtube', 'matterport' ), true ) ? '' : ' target="_blank" rel="noopener noreferrer"';
	$lightbox       = 'youtube' === $tour['type'] ? ' data-glightbox="type: video;"' : '';
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_tour_click', rentfetch_get_property_tracking_context( $property_id, $post_id ) );
	$embedlink      = sprintf( '<a class="%s"%s%s data-gallery="post-%s" href="%s"%s>%s%s</a>', $classes, $target, $lightbox, $post_id, esc_url( $tour['link_url'] ), $tracking_attrs, $svg, $tour_link_text );

	return apply_filters( 'rentfetch_filter_property_tour_button', $embedlink, $property_id, $css_class );
}

/**
 * Echo the property email.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_tour_button( $property_id = null ) {
	if ( rentfetch_get_property_tour_button( $property_id ) ) {
		$allowed_html = array_merge(
			wp_kses_allowed_html( 'post' ),
			array(
				'svg'  => array(
					'xmlns'        => true,
					'fill'         => true,
					'viewbox'      => true,
					'stroke-width' => true,
					'stroke'       => true,
					'class'        => true,
				),
				'path' => array(
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
					'd'               => true,
				),
			)
		);
		echo wp_kses( rentfetch_get_property_tour_button( $property_id ), $allowed_html );
	}
}

/**
 * Get the property tour booking URL.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property tour booking URL.
 */
function rentfetch_get_property_tour_booking_url( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}

	$url = get_post_meta( $post_id, 'tour_booking_link', true );

	return esc_url( apply_filters( 'rentfetch_filter_property_tour_booking_url', $url ) );
}

/**
 * Get the property tour booking button.
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $css_class Optional additional CSS class.
 * @return string The property tour booking button.
 */
function rentfetch_get_property_tour_booking_button( $property_id = null, $css_class = '' ) {
	$url     = rentfetch_get_property_tour_booking_url( $property_id );
	$target  = rentfetch_get_link_target( $url );
	$classes = 'tour-booking-link property-link';
	if ( ! empty( $css_class ) ) {
		$classes .= ' ' . esc_attr( $css_class );
	}
	$tracking_attrs      = rentfetch_get_tracking_data_attributes( 'rentfetch_scheduletour_click', rentfetch_get_property_tracking_context( $property_id ) );
	$svg                 = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 tour-booking-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" /></svg>';
	$tour_booking_button = sprintf( '<a class="%s" href="%s" target="%s"%s>%sBook Tour</a>', $classes, esc_html( $url ), esc_attr( $target ), $tracking_attrs, $svg );

	if ( $url ) {
		return apply_filters( 'rentfetch_filter_property_tour_booking', $tour_booking_button, $property_id, $css_class );
	} else {
		return;
	}
}

/**
 * Echo the property tour booking button.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_tour_booking_button( $property_id = null ) {
	if ( rentfetch_get_property_tour_booking_url( $property_id ) ) {
		$allowed_html = array_merge(
			wp_kses_allowed_html( 'post' ),
			array(
				'svg'  => array(
					'xmlns'        => true,
					'fill'         => true,
					'viewbox'      => true,
					'stroke-width' => true,
					'stroke'       => true,
					'class'        => true,
				),
				'path' => array(
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
					'd'               => true,
				),
			)
		);
		echo wp_kses( rentfetch_get_property_tour_booking_button( $property_id ), $allowed_html );
	}
}

// * PROPERTY APPLY ONLINE BUTTON.

/**
 * Get the property apply online URL.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property apply online URL.
 */
function rentfetch_get_property_apply_online_url( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}

	$url = get_post_meta( $post_id, 'apply_online_url', true );

	return esc_url( apply_filters( 'rentfetch_filter_property_apply_online_url', $url ) );
}

/**
 * Get the property apply online button.
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $css_class Optional additional CSS class.
 * @return string|null The property apply online button.
 */
function rentfetch_get_property_apply_online_button( $property_id = null, $css_class = '' ) {
	$url     = rentfetch_get_property_apply_online_url( $property_id );
	$target  = rentfetch_get_link_target( $url );
	$classes = 'apply-online-link property-link property-link-highlight';

	if ( ! empty( $css_class ) ) {
		$classes .= ' ' . esc_attr( $css_class );
	}

	$tracking_attrs      = rentfetch_get_tracking_data_attributes( 'rentfetch_applyonline_click', rentfetch_get_property_tracking_context( $property_id ) );
	$apply_online_button = sprintf( '<a class="%s" href="%s" target="%s"%s>Apply Online</a>', esc_attr( $classes ), esc_url( $url ), esc_attr( $target ), $tracking_attrs );

	if ( $url ) {
		return apply_filters( 'rentfetch_filter_property_apply_online_button', $apply_online_button, $property_id, $css_class );
	}

	return null;
}

/**
 * Echo the property apply online button.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_apply_online_button( $property_id = null ) {
	$button = rentfetch_get_property_apply_online_button( $property_id );

	if ( $button ) {
		echo wp_kses_post( $button );
	}
}

// * PROPERTY OFFICE HOURS BUTTON.

/**
 * Get the property office hours button.
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $css_class Optional additional CSS class.
 * @return string The property office hours button.
 */
function rentfetch_get_property_office_hours_button( $property_id = null, $css_class = '' ) {
	$office_hours = rentfetch_get_property_office_hours_array( $property_id );

	if ( empty( $office_hours ) ) {
		return '';
	}

	$classes = 'office-hours-link property-link';
	if ( ! empty( $css_class ) ) {
		$classes .= ' ' . esc_attr( $css_class );
	}
	$svg            = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 office-hours-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>';
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_officehours_click', rentfetch_get_property_tracking_context( $property_id ) );

	// Get office hours markup without heading and wrapper.
	$days                 = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
	$office_hours_content = '';
	foreach ( $days as $day ) {
		$office_hours_content     .= '<div class="office-hours-day">';
			$office_hours_content .= '<span class="day-name">' . esc_html( ucfirst( $day ) . ':' ) . '</span> ';
		if ( isset( $office_hours[ $day ] ) && ! empty( $office_hours[ $day ]['start'] ) && ! empty( $office_hours[ $day ]['end'] ) ) {
			$start_time            = gmdate( 'ga', strtotime( $office_hours[ $day ]['start'] ) );
			$end_time              = gmdate( 'ga', strtotime( $office_hours[ $day ]['end'] ) );
			$office_hours_content .= '<span class="day-hours">' . esc_html( $start_time . ' to ' . $end_time ) . '</span>';
		} else {
			$office_hours_content .= '<span class="day-hours">Closed</span>';
		}
		$office_hours_content .= '</div>';
	}

	$office_hours_button = sprintf(
		'<details class="office-hours-details">
			<summary class="%s"%s>%sOffice Hours</summary>
			<div class="office-hours-content">%s</div>
		</details>',
		$classes,
		$tracking_attrs,
		$svg,
		$office_hours_content
	);

	return apply_filters( 'rentfetch_filter_property_office_hours_button', $office_hours_button, $property_id, $css_class );
}

/**
 * Echo the property office hours button.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_office_hours_button( $property_id = null ) {
	$button = rentfetch_get_property_office_hours_button( $property_id );

	if ( $button ) {
		$allowed_html = array_merge(
			wp_kses_allowed_html( 'post' ),
			array(
				'svg'     => array(
					'xmlns'        => true,
					'fill'         => true,
					'viewbox'      => true,
					'stroke-width' => true,
					'stroke'       => true,
					'class'        => true,
				),
				'path'    => array(
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
					'd'               => true,
				),
				'details' => array(
					'class' => true,
					'open'  => true,
				),
				'summary' => array(
					'class'                        => true,
					'data-rentfetch-event'         => true,
					'data-rentfetch-property-id'   => true,
					'data-rentfetch-property-name' => true,
					'data-rentfetch-property-city' => true,
				),
				'div'     => array(
					'class' => true,
					'style' => true,
				),
				'span'    => array(
					'class' => true,
				),
			)
		);
		echo wp_kses( $button, $allowed_html );
	}
}

// * PROPERTY BEDROOMS.

/**
 * Get the property bedrooms.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property bedrooms.
 */
function rentfetch_get_property_bedrooms( $property_id = null ) {
	if ( ! $property_id ) {
		$property_id = sanitize_text_field( get_post_meta( get_the_ID(), 'property_id', true ) );
	}

	$floorplan_data = rentfetch_get_floorplans( $property_id );

	if ( ! isset( $floorplan_data['bedsrange'] ) ) {
		return;
	}

	return apply_filters( 'rentfetch_get_bedroom_number_label', $floorplan_data['bedsrange'] );
}

/**
 * Echo the property bedrooms.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_bedrooms( $property_id = null ) {
	$bedrooms = rentfetch_get_property_bedrooms( $property_id );

	if ( $bedrooms ) {
		echo wp_kses_post( $bedrooms );
	}
}

// * PROPERTY BATHROOMS

/**
 * Get the property bathrooms.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property bathrooms.
 */
function rentfetch_get_property_bathrooms( $property_id = null ) {
	if ( ! $property_id ) {
		$property_id = esc_html( get_post_meta( get_the_ID(), 'property_id', true ) );
	}

	$floorplan_data = rentfetch_get_floorplans( $property_id );

	if ( ! isset( $floorplan_data['bathsrange'] ) ) {
		return;
	}

	return apply_filters( 'rentfetch_get_bathroom_number_label', $floorplan_data['bathsrange'] );
}

/**
 * Echo the property bathrooms.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_bathrooms( $property_id = null ) {
	$bathrooms = rentfetch_get_property_bathrooms( $property_id );

	if ( $bathrooms ) {
		echo wp_kses_post( $bathrooms );
	}
}

// * PROPERTY SQUARE FEET

/**
 * Get the property square feet.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property square feet.
 */
function rentfetch_get_property_square_feet( $property_id = null ) {
	if ( ! $property_id ) {
		$property_id = sanitize_text_field( get_post_meta( get_the_ID(), 'property_id', true ) );
	}

	$floorplan_data = rentfetch_get_floorplans( $property_id );

	if ( ! isset( $floorplan_data['sqftrange'] ) ) {
		return;
	}

	return apply_filters( 'rentfetch_get_square_feet_number_label', $floorplan_data['sqftrange'] );
}

/**
 * Echo the property square feet.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_square_feet( $property_id = null ) {
	$square_feet = rentfetch_get_property_square_feet( $property_id );

	if ( $square_feet ) {
		echo wp_kses_post( $square_feet );
	}
}

// * PROPERTY RENT

/**
 * Normalize rent values into floats >= 100.
 *
 * @param mixed $values Raw rent value or values.
 * @return float[] Normalized rent values.
 */
function rentfetch_get_normalized_property_rent_values( $values ) {
	if ( ! is_array( $values ) ) {
		$values = array( $values );
	}

	$normalized = array();

	foreach ( $values as $value ) {
		if ( ! is_numeric( $value ) ) {
			continue;
		}

		$float_value = (float) $value;
		if ( $float_value >= 100 ) {
			$normalized[] = $float_value;
		}
	}

	return $normalized;
}

/**
 * Get floorplan values only where the corresponding floorplan has availability.
 *
 * @param array  $floorplan_data Aggregated floorplan data.
 * @param string $key            Value key to return.
 * @return array
 */
function rentfetch_get_available_property_floorplan_values( $floorplan_data, $key ) {
	$values          = $floorplan_data[ $key ] ?? array();
	$available_units = $floorplan_data['available_units'] ?? array();
	$available       = array();

	foreach ( $values as $index => $value ) {
		if ( (int) ( $available_units[ $index ] ?? 0 ) > 0 ) {
			$available[] = $value;
		}
	}

	return $available;
}

/**
 * Format a rent value as a currency string.
 *
 * @param float $value Rent value.
 * @return string Formatted value, e.g. "$1,500".
 */
function rentfetch_format_property_rent_value( $value ) {
	return '$' . number_format( (float) $value );
}

/**
 * Format property rent display for configured mode.
 *
 * @param float  $min_rent Minimum rent.
 * @param float  $max_rent Maximum rent.
 * @param string $pricing_display Display mode, usually "range" or "minimum".
 * @return string Formatted rent display.
 */
function rentfetch_format_property_rent_display( $min_rent, $max_rent, $pricing_display ) {
	if ( 'minimum' === $pricing_display ) {
		return rentfetch_format_property_rent_value( $min_rent );
	}

	if ( null !== $max_rent && $max_rent > $min_rent ) {
		return rentfetch_format_property_rent_value( $min_rent ) . '-' . rentfetch_format_property_rent_value( $max_rent );
	}

	return rentfetch_format_property_rent_value( $min_rent );
}

/**
 * Determine whether a synced Yardi lease-fee payload contains any actual fees.
 *
 * Empty-but-successful payloads should not become authoritative over manual/global
 * fee sources.
 *
 * @param array $payload Synced lease-fee payload.
 * @return bool
 */
function rentfetch_yardi_synced_property_lease_fees_payload_has_actual_fees( $payload ) {
	if ( ! is_array( $payload ) ) {
		return false;
	}

	foreach ( array( 'propertyFees', 'propertyCustomFees', 'rentableItemTypeFees' ) as $key ) {
		if ( ! empty( $payload[ $key ] ) && is_array( $payload[ $key ] ) ) {
			return true;
		}
	}

	$unit_types = isset( $payload['unitTypes'] ) && is_array( $payload['unitTypes'] ) ? $payload['unitTypes'] : array();
	foreach ( $unit_types as $unit_type ) {
		if ( ! is_array( $unit_type ) ) {
			continue;
		}

		foreach ( array( 'unitTypeFees', 'unitTypeCustomFees' ) as $unit_fee_key ) {
			if ( ! empty( $unit_type[ $unit_fee_key ] ) && is_array( $unit_type[ $unit_fee_key ] ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Get the authoritative synced lease-fee payload for a property.
 *
 * @param int|null $property_post_id The property post ID.
 * @return array|null
 */
function rentfetch_get_yardi_synced_property_lease_fees_payload( $property_post_id = null ) {
	$property_post_id = (int) $property_post_id;
	if ( $property_post_id <= 0 ) {
		return null;
	}

	$payload = get_post_meta( $property_post_id, 'synced_property_lease_fees', true );
	if ( ! is_array( $payload ) ) {
		return null;
	}

	if ( ! isset( $payload['errorCode'] ) || 200 !== (int) $payload['errorCode'] ) {
		return null;
	}

	if ( rentfetch_yardi_synced_property_lease_fees_payload_has_actual_fees( $payload ) ) {
		return $payload;
	}

	return null;
}

/**
 * Format an API fee amount for display.
 *
 * @param mixed $amount Numeric fee amount.
 * @return string
 */
function rentfetch_format_yardi_api_fee_currency( $amount ) {
	if ( ! is_numeric( $amount ) ) {
		return '';
	}

	$formatted = number_format( (float) $amount, 2, '.', '' );
	$formatted = preg_replace( '/\.00$/', '', $formatted );

	return '$' . $formatted;
}

/**
 * Resolve the most relevant numeric value from a Yardi lease fee payload row.
 *
 * @param array $fee Fee payload.
 * @return float|null
 */
function rentfetch_get_yardi_api_fee_numeric_price( $fee ) {
	if ( ! is_array( $fee ) ) {
		return null;
	}

	$cost     = isset( $fee['feeCost'] ) && is_numeric( $fee['feeCost'] ) ? (float) $fee['feeCost'] : null;
	$cost_max = isset( $fee['feeCostMax'] ) && is_numeric( $fee['feeCostMax'] ) ? (float) $fee['feeCostMax'] : null;

	if ( null !== $cost && null !== $cost_max && $cost_max > $cost ) {
		return $cost_max;
	}

	if ( null !== $cost && $cost > 0 ) {
		return $cost;
	}

	if ( null !== $cost_max && $cost_max > 0 ) {
		return $cost_max;
	}

	if ( ! empty( $fee['feeCostText'] ) && function_exists( 'rentfetch_extract_first_numeric_fee_value' ) ) {
		return rentfetch_extract_first_numeric_fee_value( $fee['feeCostText'] );
	}

	return null;
}

/**
 * Format a Yardi lease fee row into a manual-fee-style price string.
 *
 * @param array $fee Fee payload.
 * @return string
 */
function rentfetch_get_yardi_api_fee_price_display( $fee ) {
	if ( ! is_array( $fee ) ) {
		return '';
	}

	$cost     = isset( $fee['feeCost'] ) && is_numeric( $fee['feeCost'] ) ? (float) $fee['feeCost'] : null;
	$cost_max = isset( $fee['feeCostMax'] ) && is_numeric( $fee['feeCostMax'] ) ? (float) $fee['feeCostMax'] : null;

	if ( null !== $cost && null !== $cost_max && $cost_max > $cost ) {
		return rentfetch_format_yardi_api_fee_currency( $cost ) . '-' . rentfetch_format_yardi_api_fee_currency( $cost_max );
	}

	if ( null !== $cost && $cost > 0 ) {
		return rentfetch_format_yardi_api_fee_currency( $cost );
	}

	if ( null !== $cost_max && $cost_max > 0 ) {
		return rentfetch_format_yardi_api_fee_currency( $cost_max );
	}

	return sanitize_text_field( (string) ( $fee['feeCostText'] ?? '' ) );
}

/**
 * Build a lowercase searchable text blob for API fee classification.
 *
 * @param array $fee Fee payload.
 * @return string
 */
function rentfetch_get_yardi_api_fee_search_text( $fee ) {
	if ( ! is_array( $fee ) ) {
		return '';
	}

	$parts = array(
		(string) ( $fee['feeName'] ?? '' ),
		(string) ( $fee['feeDescription'] ?? '' ),
		(string) ( $fee['feeCostText'] ?? '' ),
		(string) ( $fee['feeAmountType'] ?? '' ),
		(string) ( $fee['feeMethod'] ?? '' ),
		(string) ( $fee['feePaymentTo'] ?? '' ),
		(string) ( $fee['feeFrequency'] ?? '' ),
		(string) ( $fee['feeTiming'] ?? '' ),
		(string) ( $fee['feeChargeClass'] ?? '' ),
		(string) ( $fee['feeChargeType'] ?? '' ),
		(string) ( $fee['feeChargeCode']['code'] ?? '' ),
		(string) ( $fee['feeChargeCode']['description'] ?? '' ),
	);

	return strtolower( trim( implode( ' ', array_filter( $parts ) ) ) );
}

/**
 * Derive a display frequency for an API fee row.
 *
 * @param array $fee Fee payload.
 * @return string
 */
function rentfetch_get_yardi_api_fee_frequency_label( $fee ) {
	$search_text = rentfetch_get_yardi_api_fee_search_text( $fee );
	$label       = '';

	$monthly_markers = array( 'month', 'monthly', 'per month', '/mo', 'mo.' );
	foreach ( $monthly_markers as $marker ) {
		if ( false !== strpos( $search_text, $marker ) ) {
			$label = 'Monthly';
			break;
		}
	}

	if ( '' === $label ) {
		$one_time_markers = array(
			'one-time',
			'one time',
			'once',
			'move in',
			'move-in',
			'application',
			'app fee',
			'deposit',
			'holding',
			'reservation',
			'setup',
		);

		foreach ( $one_time_markers as $marker ) {
			if ( false !== strpos( $search_text, $marker ) ) {
				$label = 'One-Time';
				break;
			}
		}
	}

	if ( '' === $label ) {
		$monthly_keywords = array(
			'utility',
			'insurance',
			'liability',
			'pet rent',
			'water',
			'sewer',
			'trash',
			'gas',
			'electric',
			'internet',
			'cable',
			'parking',
			'storage',
			'amenity',
			'pest',
		);

		foreach ( $monthly_keywords as $keyword ) {
			if ( false !== strpos( $search_text, $keyword ) ) {
				$label = 'Monthly';
				break;
			}
		}
	}

	if ( '' === $label ) {
		$label = 'One-Time';
	}

	return apply_filters( 'rentfetch_filter_yardi_api_fee_frequency_label', $label, $fee );
}

/**
 * Determine whether an API fee should count toward monthly required pricing.
 *
 * @param array $fee Fee payload.
 * @return bool
 */
function rentfetch_is_yardi_api_fee_monthly_required( $fee ) {
	$is_required      = ! empty( $fee['isRequired'] );
	$frequency_label  = rentfetch_get_yardi_api_fee_frequency_label( $fee );
	$numeric_price    = rentfetch_get_yardi_api_fee_numeric_price( $fee );
	$is_monthly_match = ( 'Monthly' === $frequency_label );

	$result = $is_required && $is_monthly_match && null !== $numeric_price && $numeric_price > 0;

	return (bool) apply_filters( 'rentfetch_filter_yardi_api_fee_is_monthly_required', $result, $fee, $frequency_label, $numeric_price );
}

/**
 * Categorize an API fee into the existing property-fees table grouping style.
 *
 * @param array  $fee             Fee payload.
 * @param string $frequency_label Derived frequency label.
 * @return string
 */
function rentfetch_get_yardi_api_fee_category_label( $fee, $frequency_label ) {
	$is_required = ! empty( $fee['isRequired'] );
	$is_monthly  = ( 'Monthly' === $frequency_label );

	if ( $is_required && $is_monthly ) {
		$category = 'Required Monthly Fees';
	} elseif ( $is_required ) {
		$category = 'Required One-Time Fees';
	} elseif ( $is_monthly ) {
		$category = 'Optional Monthly Fees';
	} else {
		$category = 'Optional One-Time Fees';
	}

	return apply_filters( 'rentfetch_filter_yardi_api_fee_category_label', $category, $fee, $frequency_label );
}

/**
 * Build client-facing longnotes for a synced Yardi fee row.
 *
 * This intentionally avoids exposing internal charge-code metadata in the
 * frontend/admin tooltip content. At the moment, `feeDescription` is the
 * only clearly client-facing descriptive field in the Yardi payload.
 *
 * @param array $fee                 Fee payload.
 * @param array $applies_to          Floorplans this fee applies to.
 * @param int   $all_floorplan_count Total number of floorplans on the property.
 * @return string
 */
function rentfetch_get_yardi_api_fee_longnotes( $fee, $applies_to = array(), $all_floorplan_count = 0 ) {
	$tooltip_parts   = array();
	$fee_description = trim( (string) ( $fee['feeDescription'] ?? '' ) );

	if ( '' !== $fee_description ) {
		$tooltip_parts[] = sanitize_text_field( $fee_description );
	}

	if ( ! empty( $applies_to ) && $all_floorplan_count > 0 && count( $applies_to ) < $all_floorplan_count ) {
		$tooltip_parts[] = 'Applies to: ' . implode( ', ', array_map( 'sanitize_text_field', $applies_to ) );
	}

	$longnotes = implode( "\n\n", $tooltip_parts );

	return (string) apply_filters(
		'rentfetch_filter_yardi_api_fee_longnotes',
		$longnotes,
		$fee,
		$applies_to,
		$all_floorplan_count
	);
}

/**
 * Build a stable signature for fee deduplication across unit types.
 *
 * @param array $fee Fee payload.
 * @return string
 */
function rentfetch_get_yardi_api_fee_signature( $fee ) {
	$signature_payload = array(
		'feeName'           => (string) ( $fee['feeName'] ?? '' ),
		'feeCost'           => isset( $fee['feeCost'] ) ? (float) $fee['feeCost'] : null,
		'feeCostMax'        => isset( $fee['feeCostMax'] ) ? (float) $fee['feeCostMax'] : null,
		'feeCostText'       => (string) ( $fee['feeCostText'] ?? '' ),
		'isRequired'        => ! empty( $fee['isRequired'] ),
		'feeChargeCodeId'   => isset( $fee['feeChargeCode']['id'] ) ? (int) $fee['feeChargeCode']['id'] : 0,
		'feeChargeCodeCode' => (string) ( $fee['feeChargeCode']['code'] ?? '' ),
		'feeChargeCodeDesc' => (string) ( $fee['feeChargeCode']['description'] ?? '' ),
		'frequency'         => rentfetch_get_yardi_api_fee_frequency_label( $fee ),
	);

	return md5( wp_json_encode( $signature_payload ) );
}

/**
 * Translate synced API lease fees into the flat row structure used by manual fees.
 *
 * @param int|null $property_post_id The property post ID.
 * @return array
 */
function rentfetch_get_yardi_api_property_fees_data( $property_post_id = null ) {
	$payload = rentfetch_get_yardi_synced_property_lease_fees_payload( $property_post_id );
	if ( ! is_array( $payload ) ) {
		return array();
	}

	$aggregated          = array();
	$all_floorplan_names = array();

	$add_fee = static function ( $fee, $applies_to = array() ) use ( &$aggregated ) {
		if ( ! is_array( $fee ) ) {
			return;
		}

		$signature = rentfetch_get_yardi_api_fee_signature( $fee );
		if ( ! isset( $aggregated[ $signature ] ) ) {
			$aggregated[ $signature ] = array(
				'fee'        => $fee,
				'applies_to' => array(),
			);
		}

		foreach ( $applies_to as $floorplan_name ) {
			$floorplan_name = sanitize_text_field( (string) $floorplan_name );
			if ( '' !== $floorplan_name ) {
				$aggregated[ $signature ]['applies_to'][ $floorplan_name ] = true;
			}
		}
	};

	foreach ( array( 'propertyFees', 'propertyCustomFees', 'rentableItemTypeFees' ) as $key ) {
		$fees = isset( $payload[ $key ] ) && is_array( $payload[ $key ] ) ? $payload[ $key ] : array();
		foreach ( $fees as $fee ) {
			$add_fee( $fee );
		}
	}

	$unit_types = isset( $payload['unitTypes'] ) && is_array( $payload['unitTypes'] ) ? $payload['unitTypes'] : array();
	foreach ( $unit_types as $unit_type ) {
		if ( ! is_array( $unit_type ) ) {
			continue;
		}

		$floorplan_name = sanitize_text_field( (string) ( $unit_type['floorPlanName'] ?? $unit_type['unitTypeName'] ?? '' ) );
		if ( '' !== $floorplan_name ) {
			$all_floorplan_names[ $floorplan_name ] = true;
		}

		foreach ( array( 'unitTypeFees', 'unitTypeCustomFees' ) as $unit_fee_key ) {
			$fees = isset( $unit_type[ $unit_fee_key ] ) && is_array( $unit_type[ $unit_fee_key ] ) ? $unit_type[ $unit_fee_key ] : array();
			foreach ( $fees as $fee ) {
				$add_fee( $fee, '' !== $floorplan_name ? array( $floorplan_name ) : array() );
			}
		}
	}

	$all_floorplan_names = array_keys( $all_floorplan_names );
	sort( $all_floorplan_names );
	$all_floorplan_count = count( $all_floorplan_names );

	$rows = array();
	foreach ( $aggregated as $entry ) {
		$fee = $entry['fee'];

		$description = sanitize_text_field( (string) ( $fee['feeName'] ?? '' ) );
		if ( '' === $description ) {
			$description = sanitize_text_field( (string) ( $fee['feeChargeCode']['description'] ?? 'Fee' ) );
		}

		$applies_to = array_keys( $entry['applies_to'] );
		sort( $applies_to );

		if ( ! empty( $applies_to ) && $all_floorplan_count > 0 && count( $applies_to ) < $all_floorplan_count ) {
			$description .= ' (' . implode( ', ', array_map( 'sanitize_text_field', $applies_to ) ) . ')';
		}

		$frequency_label = rentfetch_get_yardi_api_fee_frequency_label( $fee );
		$price_display   = rentfetch_get_yardi_api_fee_price_display( $fee );
		$is_required     = ! empty( $fee['isRequired'] );
		$notes           = $is_required ? 'required' : 'optional';

		if ( ! empty( $applies_to ) && $all_floorplan_count > 0 && count( $applies_to ) < $all_floorplan_count ) {
			$notes .= ' on select floorplans';
		}

		$rows[] = array(
			'description' => $description,
			'price'       => $price_display,
			'frequency'   => $frequency_label,
			'notes'       => $notes,
			'category'    => rentfetch_get_yardi_api_fee_category_label( $fee, $frequency_label ),
			'longnotes'   => rentfetch_get_yardi_api_fee_longnotes( $fee, $applies_to, $all_floorplan_count ),
		);
	}

	$category_order = array(
		'Required Monthly Fees'  => 0,
		'Required One-Time Fees' => 1,
		'Optional Monthly Fees'  => 2,
		'Optional One-Time Fees' => 3,
	);

	usort(
		$rows,
		static function ( $left, $right ) use ( $category_order ) {
			$left_category_order  = $category_order[ $left['category'] ?? '' ] ?? 999;
			$right_category_order = $category_order[ $right['category'] ?? '' ] ?? 999;

			if ( $left_category_order !== $right_category_order ) {
				return $left_category_order <=> $right_category_order;
			}

			return strcasecmp( (string) ( $left['description'] ?? '' ), (string) ( $right['description'] ?? '' ) );
		}
	);

	return $rows;
}

/**
 * Compute the Yardi API monthly required-fees summary for a property.
 *
 * Only property-scoped fees plus unit-type fees shared by every unit type are counted
 * toward the property-wide pricing override.
 *
 * @param int|null $property_post_id The property post ID.
 * @return array|null
 */
function rentfetch_get_yardi_api_monthly_required_fees_summary_for_property( $property_post_id = null ) {
	$payload = rentfetch_get_yardi_synced_property_lease_fees_payload( $property_post_id );
	if ( ! is_array( $payload ) ) {
		return null;
	}

	$total        = 0.0;
	$contributors = array();

	foreach ( array( 'propertyFees', 'propertyCustomFees', 'rentableItemTypeFees' ) as $key ) {
		$fees = isset( $payload[ $key ] ) && is_array( $payload[ $key ] ) ? $payload[ $key ] : array();
		foreach ( $fees as $fee ) {
			if ( ! rentfetch_is_yardi_api_fee_monthly_required( $fee ) ) {
				continue;
			}

			$numeric_price = rentfetch_get_yardi_api_fee_numeric_price( $fee );
			if ( null !== $numeric_price && $numeric_price > 0 ) {
				$total         += (float) $numeric_price;
				$contributors[] = array(
					'description'   => sanitize_text_field( (string) ( $fee['feeName'] ?? 'Fee' ) ),
					'applied_price' => (float) $numeric_price,
				);
			}
		}
	}

	$unit_types      = isset( $payload['unitTypes'] ) && is_array( $payload['unitTypes'] ) ? $payload['unitTypes'] : array();
	$unit_type_count = count( $unit_types );

	if ( $unit_type_count > 0 ) {
		$shared_monthly_required_fees = array();

		foreach ( $unit_types as $unit_type ) {
			if ( ! is_array( $unit_type ) ) {
				continue;
			}

			$seen_this_unit_type = array();
			foreach ( array( 'unitTypeFees', 'unitTypeCustomFees' ) as $unit_fee_key ) {
				$fees = isset( $unit_type[ $unit_fee_key ] ) && is_array( $unit_type[ $unit_fee_key ] ) ? $unit_type[ $unit_fee_key ] : array();
				foreach ( $fees as $fee ) {
					if ( ! rentfetch_is_yardi_api_fee_monthly_required( $fee ) ) {
						continue;
					}

					$signature = rentfetch_get_yardi_api_fee_signature( $fee );
					if ( isset( $seen_this_unit_type[ $signature ] ) ) {
						continue;
					}

					$numeric_price = rentfetch_get_yardi_api_fee_numeric_price( $fee );
					if ( null === $numeric_price || $numeric_price <= 0 ) {
						continue;
					}

					$seen_this_unit_type[ $signature ] = true;

					if ( ! isset( $shared_monthly_required_fees[ $signature ] ) ) {
						$shared_monthly_required_fees[ $signature ] = array(
							'count'       => 0,
							'price'       => (float) $numeric_price,
							'description' => sanitize_text_field( (string) ( $fee['feeName'] ?? 'Fee' ) ),
						);
					}

					++$shared_monthly_required_fees[ $signature ]['count'];
				}
			}
		}

		foreach ( $shared_monthly_required_fees as $shared_fee ) {
			if ( (int) $shared_fee['count'] === $unit_type_count ) {
				$total         += (float) $shared_fee['price'];
				$contributors[] = array(
					'description'   => sanitize_text_field( (string) $shared_fee['description'] ) . ' (all floorplans)',
					'applied_price' => (float) $shared_fee['price'],
				);
			}
		}
	}

	usort(
		$contributors,
		static function ( $left, $right ) {
			return strcasecmp( (string) ( $left['description'] ?? '' ), (string) ( $right['description'] ?? '' ) );
		}
	);

	return array(
		'total'        => round( $total, 2 ),
		'contributors' => $contributors,
	);
}

/**
 * Get authoritative provider-neutral synced fee data for a property.
 *
 * New integrations can write the normalized meta contract while the existing
 * Yardi adapter remains supported without changing its raw payload shape.
 *
 * @param int|null $property_post_id Property post ID.
 * @return array|null
 */
function rentfetch_get_synced_property_fee_context( $property_post_id = null ) {
	$property_post_id = (int) $property_post_id;
	if ( $property_post_id <= 0 ) {
		return null;
	}

	$source                  = sanitize_key( (string) get_post_meta( $property_post_id, 'synced_property_fee_source', true ) );
	$rows                    = get_post_meta( $property_post_id, 'synced_property_fee_rows', true );
	$summary                 = get_post_meta( $property_post_id, 'synced_property_fee_monthly_summary', true );
	$current_property_source = sanitize_key( (string) get_post_meta( $property_post_id, 'property_source', true ) );

	if ( '' !== $source && $source === $current_property_source && is_array( $rows ) && ! empty( $rows ) ) {
		if ( ! is_array( $summary ) ) {
			$summary = array(
				'total'        => 0.0,
				'contributors' => array(),
			);
		}

		$labels = array(
			'engrain' => 'Synced Engrain expenses API',
		);

		return array(
			'source'       => $source,
			'source_key'   => $source . '_api',
			'source_label' => $labels[ $source ] ?? sprintf( 'Synced %s fees API', ucfirst( $source ) ),
			'rows'         => $rows,
			'summary'      => $summary,
		);
	}

	$yardi_payload = rentfetch_get_yardi_synced_property_lease_fees_payload( $property_post_id );
	if ( ! is_array( $yardi_payload ) ) {
		return null;
	}

	return array(
		'source'       => 'yardi',
		'source_key'   => 'yardi_api',
		'source_label' => 'Synced Yardi lease fees API',
		'rows'         => rentfetch_get_yardi_api_property_fees_data( $property_post_id ),
		'summary'      => rentfetch_get_yardi_api_monthly_required_fees_summary_for_property( $property_post_id ),
	);
}

/**
 * Get fixed mandatory monthly fees scoped to a synced floorplan or unit.
 *
 * @param int|null $post_id Floorplan or unit post ID.
 * @return float
 */
function rentfetch_get_synced_scoped_monthly_required_fee_total( $post_id = null ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return 0.0;
	}

	$source  = sanitize_key( (string) get_post_meta( $post_id, 'synced_scoped_fee_source', true ) );
	$summary = get_post_meta( $post_id, 'synced_scoped_fee_monthly_summary', true );
	if ( '' === $source || ! is_array( $summary ) || ! isset( $summary['total'] ) || ! is_numeric( $summary['total'] ) ) {
		return 0.0;
	}

	$source_meta_keys = array(
		'floorplans' => 'floorplan_source',
		'units'      => 'unit_source',
	);
	$post_type        = get_post_type( $post_id );
	if ( isset( $source_meta_keys[ $post_type ] ) ) {
		$current_source = sanitize_key( (string) get_post_meta( $post_id, $source_meta_keys[ $post_type ], true ) );
		if ( $current_source !== $source ) {
			return 0.0;
		}
	}

	return max( 0.0, (float) $summary['total'] );
}

/**
 * Get monthly required total fees for a property, with global fallback.
 *
 * @param int|null $property_post_id The property post ID.
 * @return float The monthly required total fees.
 */
function rentfetch_get_effective_monthly_required_total_fees_for_property( $property_post_id = null ) {
	if ( ! rentfetch_should_show_property_fees() ) {
		return 0.0;
	}

	$property_total = null;

	if ( $property_post_id ) {
		$synced_fee_context = rentfetch_get_synced_property_fee_context( $property_post_id );
		$synced_summary     = is_array( $synced_fee_context ) ? ( $synced_fee_context['summary'] ?? null ) : null;
		if ( is_array( $synced_summary ) && isset( $synced_summary['total'] ) ) {
			return (float) $synced_summary['total'];
		}

		$property_raw   = get_post_meta( $property_post_id, 'property_monthly_required_total_fees', true );
		$property_total = rentfetch_extract_first_numeric_fee_value( $property_raw );
	}

	if ( null !== $property_total && $property_total > 0 ) {
		return (float) $property_total;
	}

	$global_raw   = get_option( 'rentfetch_options_global_monthly_required_total_fees', '' );
	$global_total = rentfetch_extract_first_numeric_fee_value( $global_raw );

	if ( null !== $global_total && $global_total > 0 ) {
		return (float) $global_total;
	}

	return 0.0;
}

/**
 * Get the effective monthly-fees preview context used for frontend pricing.
 *
 * @param int|null $property_post_id          The property post ID.
 * @param bool     $respect_frontend_visibility Whether to return an empty context when fees are globally hidden.
 * @return array
 */
function rentfetch_get_effective_monthly_required_fees_preview_context_for_property( $property_post_id = null, $respect_frontend_visibility = true ) {
	$property_post_id = (int) $property_post_id;
	$empty_context    = array(
		'source_key'   => 'none',
		'source_label' => 'No active fees source',
		'total'        => 0.0,
		'contributors' => array(),
		'detail_label' => '',
		'detail_value' => '',
		'description'  => 'No synced API fees, property-level monthly fees, or global monthly fees are currently affecting frontend pricing.',
	);

	if ( $respect_frontend_visibility && ! rentfetch_should_show_property_fees() ) {
		$empty_context['description'] = 'Property fees are globally disabled, so no fee totals are currently affecting frontend pricing.';
		return $empty_context;
	}

	if ( $property_post_id <= 0 ) {
		return $empty_context;
	}

	$synced_fee_context = rentfetch_get_synced_property_fee_context( $property_post_id );
	if ( is_array( $synced_fee_context ) ) {
		$synced_summary = $synced_fee_context['summary'] ?? array();
		$contributors   = is_array( $synced_summary ) && isset( $synced_summary['contributors'] ) && is_array( $synced_summary['contributors'] )
			? $synced_summary['contributors']
			: array();
		$total          = is_array( $synced_summary ) && isset( $synced_summary['total'] )
			? (float) $synced_summary['total']
			: 0.0;

		return array(
			'source_key'   => $synced_fee_context['source_key'],
			'source_label' => $synced_fee_context['source_label'],
			'total'        => $total,
			'contributors' => $contributors,
			'detail_label' => '',
			'detail_value' => '',
			'description'  => $total > 0
				? sprintf( 'Frontend pricing is currently using monthly required fees from %s. These take precedence over property-level and global manual fee totals.', $synced_fee_context['source_label'] )
				: sprintf( '%s is active for this property, but it does not currently contribute any required monthly fees to frontend pricing.', $synced_fee_context['source_label'] ),
		);
	}

	$property_raw          = get_post_meta( $property_post_id, 'property_monthly_required_total_fees', true );
	$property_total        = rentfetch_extract_first_numeric_fee_value( $property_raw );
	$property_rows         = get_post_meta( $property_post_id, 'property_monthly_required_total_fees_rows', true );
	$property_csv_url      = trim( (string) get_post_meta( $property_post_id, 'property_fees_csv_url', true ) );
	$property_last_checked = (int) get_post_meta( $property_post_id, 'property_monthly_required_total_fees_last_checked', true );

	if ( ! is_array( $property_rows ) ) {
		$property_rows = array();
	}

	if ( null !== $property_total && $property_total > 0 ) {
		$is_csv_backed = ! empty( $property_rows ) && '' !== $property_csv_url;

		return array(
			'source_key'   => $is_csv_backed ? 'property_csv' : 'property_manual',
			'source_label' => $is_csv_backed ? 'Property fees CSV' : 'Property monthly required fees field',
			'total'        => (float) $property_total,
			'contributors' => $property_rows,
			'detail_label' => $is_csv_backed && $property_last_checked > 0 ? 'Last property CSV check' : '',
			'detail_value' => $is_csv_backed && $property_last_checked > 0 ? wp_date( 'M j, Y g:ia', $property_last_checked ) : '',
			'description'  => $is_csv_backed
				? 'Frontend pricing is currently using the property-specific monthly required fees derived from this property\'s CSV.'
				: 'Frontend pricing is currently using the property-level monthly required total stored on this property.',
		);
	}

	$global_raw          = get_option( 'rentfetch_options_global_monthly_required_total_fees', '' );
	$global_total        = rentfetch_extract_first_numeric_fee_value( $global_raw );
	$global_rows         = get_option( 'rentfetch_options_global_monthly_required_total_fees_rows', array() );
	$global_csv_url      = trim( (string) get_option( 'rentfetch_options_global_property_fees_csv_url', '' ) );
	$global_last_checked = (int) get_option( 'rentfetch_options_global_monthly_required_total_fees_last_checked', 0 );

	if ( ! is_array( $global_rows ) ) {
		$global_rows = array();
	}

	if ( null !== $global_total && $global_total > 0 ) {
		$is_csv_backed = ! empty( $global_rows ) && '' !== $global_csv_url;

		return array(
			'source_key'   => $is_csv_backed ? 'global_csv' : 'global_manual',
			'source_label' => $is_csv_backed ? 'Global fees CSV fallback' : 'Global monthly required fees fallback',
			'total'        => (float) $global_total,
			'contributors' => $global_rows,
			'detail_label' => $is_csv_backed && $global_last_checked > 0 ? 'Last global CSV check' : '',
			'detail_value' => $is_csv_backed && $global_last_checked > 0 ? wp_date( 'M j, Y g:ia', $global_last_checked ) : '',
			'description'  => $is_csv_backed
				? 'Frontend pricing is currently falling back to the global monthly required fees derived from the global fees CSV.'
				: 'Frontend pricing is currently falling back to the global monthly required fees setting.',
		);
	}

	return $empty_context;
}

/**
 * Get the tooltip text for fee-inclusive monthly leasing pricing.
 *
 * @return string
 */
function rentfetch_get_total_monthly_leasing_pricing_tooltip_text() {
	$default_text = 'Total Monthly Leasing Pricing';
	$text         = trim( (string) get_option( 'rentfetch_options_total_monthly_leasing_pricing_tooltip_text', '' ) );

	if ( '' === $text ) {
		$text = $default_text;
	}

	return apply_filters( 'rentfetch_filter_total_monthly_leasing_pricing_tooltip_text', $text );
}

/**
 * Get the tooltip markup for fee-inclusive pricing lines.
 *
 * @return string
 */
function rentfetch_get_total_monthly_leasing_pricing_tooltip_markup() {
	$tooltip_text = rentfetch_get_total_monthly_leasing_pricing_tooltip_text();
	if ( '' === trim( (string) $tooltip_text ) ) {
		return '';
	}

	$tooltip_html = nl2br( esc_html( $tooltip_text ) );

	// Reuse the shared Rent Fetch tooltip behavior and styling.
	wp_enqueue_script( 'rentfetch-tooltip' );

	return sprintf(
		'<span class="fee-description-with-tooltip rentfetch-tooltip-trigger rentfetch-pricing-tooltip-trigger" data-tooltip-content="%1$s" tabindex="0"><span class="fee-info-icon rentfetch-tooltip-icon" aria-label="%2$s"></span></span>',
		esc_attr( $tooltip_html ),
		esc_attr( $tooltip_text )
	);
}

/**
 * Get the property rent markup.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property rent markup.
 */
function rentfetch_get_property_pricing( $property_id = null ) {
	if ( ! $property_id ) {
		$property_id = sanitize_text_field( get_post_meta( get_the_ID(), 'property_id', true ) );
	}

	$floorplan_data  = rentfetch_get_floorplans( $property_id );
	$pricing_display = get_option( 'rentfetch_options_property_pricing_display', 'range' );
	$available_units = array_filter( array_map( 'intval', $floorplan_data['available_units'] ?? array() ), fn( $units ) => $units > 0 );
	if ( empty( $available_units ) ) {
		return null;
	}

	$min_rent_values  = rentfetch_get_normalized_property_rent_values( rentfetch_get_available_property_floorplan_values( $floorplan_data, 'minimum_rent' ) );
	$max_rent_values  = rentfetch_get_normalized_property_rent_values( rentfetch_get_available_property_floorplan_values( $floorplan_data, 'maximum_rent' ) );
	$min_total_values = rentfetch_get_normalized_property_rent_values( rentfetch_get_available_property_floorplan_values( $floorplan_data, 'minimum_total_monthly_price' ) );
	$max_total_values = rentfetch_get_normalized_property_rent_values( rentfetch_get_available_property_floorplan_values( $floorplan_data, 'maximum_total_monthly_price' ) );

	$min_rent = ! empty( $min_rent_values ) ? min( $min_rent_values ) : null;
	$max_rent = ! empty( $max_rent_values ) ? max( $max_rent_values ) : null;

	// Fallback to parsing rentrange when API min/max arrays are not available.
	if ( null === $min_rent && null !== $max_rent ) {
		$min_rent = $max_rent;
	}
	if ( null === $max_rent && null !== $min_rent ) {
		$max_rent = $min_rent;
	}
	if ( null !== $min_rent && null !== $max_rent && $max_rent < $min_rent ) {
		$temp     = $min_rent;
		$min_rent = $max_rent;
		$max_rent = $temp;
	}

	if ( null === $min_rent ) {
		$rent = apply_filters( 'rentfetch_filter_property_pricing_no_price_available', 'Call for Pricing' );
		return apply_filters( 'rentfetch_filter_property_pricing', $rent, $floorplan_data['rentrange'] ?? null, $floorplan_data['minimum_rent'] ?? null, $floorplan_data['maximum_rent'] ?? null );
	}

	$property_post_id = rentfetch_get_post_id_from_property_id( $property_id );
	if ( ! $property_post_id && is_singular( 'properties' ) ) {
		$property_post_id = get_the_ID();
	}
	$monthly_required_fees = rentfetch_get_effective_monthly_required_total_fees_for_property( $property_post_id );

	$base_rent_display           = rentfetch_format_property_rent_display( $min_rent, $max_rent, $pricing_display );
	$minimum_total_monthly_price = ! empty( $min_total_values ) ? min( $min_total_values ) : null;
	$maximum_total_monthly_price = ! empty( $max_total_values ) ? max( $max_total_values ) : null;
	if ( null === $minimum_total_monthly_price ) {
		$minimum_total_monthly_price = $maximum_total_monthly_price;
	}
	if ( null === $maximum_total_monthly_price ) {
		$maximum_total_monthly_price = $minimum_total_monthly_price;
	}

	if ( null !== $minimum_total_monthly_price ) {
		$including_fees_rent_display = rentfetch_format_property_rent_display( $minimum_total_monthly_price, $maximum_total_monthly_price, $pricing_display );
		$tooltip_markup              = rentfetch_get_total_monthly_leasing_pricing_tooltip_markup();
		$rent                        = sprintf(
			'<span class="rentfetch-property-rent-lines"><span class="rentfetch-property-rent-with-fees"><span class="rentfetch-pricing-with-tooltip">%1$s/mo%3$s</span></span><span class="rentfetch-property-base-rent">%2$s base rent</span></span>',
			esc_html( $including_fees_rent_display ),
			esc_html( $base_rent_display ),
			$tooltip_markup
		);

		return apply_filters( 'rentfetch_filter_property_pricing', $rent, $floorplan_data['rentrange'] ?? null, $floorplan_data['minimum_rent'] ?? null, $floorplan_data['maximum_rent'] ?? null );
	}

	if ( $monthly_required_fees > 0 ) {
		$including_fees_min_rent     = $min_rent + $monthly_required_fees;
		$including_fees_max_rent     = ( null !== $max_rent ? $max_rent : $min_rent ) + $monthly_required_fees;
		$including_fees_rent_display = rentfetch_format_property_rent_display( $including_fees_min_rent, $including_fees_max_rent, $pricing_display );
		$tooltip_markup              = rentfetch_get_total_monthly_leasing_pricing_tooltip_markup();

		$rent = sprintf(
			'<span class="rentfetch-property-rent-lines"><span class="rentfetch-property-rent-with-fees"><span class="rentfetch-pricing-with-tooltip">%1$s/mo%3$s</span></span><span class="rentfetch-property-base-rent">%2$s base rent</span></span>',
			esc_html( $including_fees_rent_display ),
			esc_html( $base_rent_display ),
			$tooltip_markup
		);
	} else {
		$rent = sprintf(
			'<span class="rentfetch-property-rent-lines"><span class="rentfetch-property-rent-with-fees">%1$s/mo</span></span>',
			esc_html( $base_rent_display )
		);
	}

	return apply_filters( 'rentfetch_filter_property_pricing', $rent, $floorplan_data['rentrange'] ?? null, $floorplan_data['minimum_rent'] ?? null, $floorplan_data['maximum_rent'] ?? null );
}

/**
 * Echo the property rent.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_pricing( $property_id = null ) {
	$rent = rentfetch_get_property_pricing( $property_id );

	if ( $rent ) {
		echo wp_kses_post( $rent );
	}
}

// * PROPERTY AVAILABILITY

/**
 * Get the property availability.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string|null The property availability.
 */
function rentfetch_get_property_availability( $property_id = null ) {
	if ( ! $property_id ) {
		$property_id = esc_html( get_post_meta( get_the_ID(), 'property_id', true ) );
	}

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
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_availability( $property_id = null ) {
	$availability = rentfetch_get_property_availability( $property_id );

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
 * @param string $property_id Optional property_id meta value.
 * @return string The property specials.
 */
function rentfetch_get_property_specials( $property_id = null ) {
	$special       = rentfetch_get_effective_property_special( $property_id );
	$specials_text = $special ? $special['heading'] : null;

	return apply_filters( 'rentfetch_filter_property_specials', $specials_text );
}

/**
 * Echo the property specials.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_specials( $property_id = null ) {
	$specials = rentfetch_get_property_specials( $property_id );

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
 * Check whether property specials are currently inside their optional date window.
 *
 * @param int $post_id Property post ID.
 * @return bool Whether the specials should be shown based on date settings.
 */
function rentfetch_property_specials_are_active_by_date( $post_id ) {
	$start_date = get_post_meta( $post_id, 'specials_start_date', true );
	$end_date   = get_post_meta( $post_id, 'specials_end_date', true );

	if ( ! $start_date && ! $end_date ) {
		return true;
	}

	$today = current_time( 'Y-m-d' );

	if ( $start_date && $today < $start_date ) {
		return false;
	}

	if ( $end_date && $today > $end_date ) {
		return false;
	}

	return true;
}

/**
 * Get the effective special for a property or its active floor plans.
 *
 * Property-level specials take precedence. When no active property special
 * exists, a single active floor-plan special keeps its title and content;
 * multiple active floor-plan specials use a generic label.
 *
 * @param string $property_id Optional property_id meta value.
 * @return array<string, mixed>|null
 */
function rentfetch_get_effective_property_special( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
	} else {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return null;
	}

	$property_id_meta      = trim( (string) get_post_meta( $post_id, 'property_id', true ) );
	$has_property_special  = get_post_meta( $post_id, 'has_specials', true );
	$property_heading      = sanitize_text_field( get_post_meta( $post_id, 'specials_override_text', true ) );
	$property_heading      = function_exists( 'mb_substr' ) ? mb_substr( $property_heading, 0, 25 ) : substr( $property_heading, 0, 25 );
	$property_content      = sanitize_textarea_field( get_post_meta( $post_id, 'specials_content', true ) );
	$property_special_live = in_array( $has_property_special, array( '1', 1, true ), true ) && rentfetch_property_specials_are_active_by_date( $post_id );

	if ( $property_special_live ) {
		return array(
			'source'           => 'property',
			'post_id'          => (int) $post_id,
			'property_post_id' => (int) $post_id,
			'heading'          => $property_heading ? $property_heading : 'Specials available',
			'content'          => $property_content,
		);
	}

	if ( '' === $property_id_meta ) {
		return null;
	}

	$floorplan_data     = rentfetch_get_floorplans( $property_id_meta );
	$floorplan_specials = isset( $floorplan_data['property_specials'] ) && is_array( $floorplan_data['property_specials'] )
		? array_values( array_filter( $floorplan_data['property_specials'] ) )
		: array();

	if ( empty( $floorplan_specials ) ) {
		return null;
	}

	if ( count( $floorplan_specials ) > 1 ) {
		return array(
			'source'           => 'floorplans',
			'post_id'          => 0,
			'property_post_id' => (int) $post_id,
			'heading'          => 'Specials available',
			'content'          => '',
		);
	}

	$floorplan_special = $floorplan_specials[0];

	return array(
		'source'           => 'floorplan',
		'post_id'          => isset( $floorplan_special['post_id'] ) ? (int) $floorplan_special['post_id'] : 0,
		'property_post_id' => (int) $post_id,
		'heading'          => ! empty( $floorplan_special['heading'] ) ? $floorplan_special['heading'] : 'Specials available',
		'content'          => isset( $floorplan_special['content'] ) ? $floorplan_special['content'] : '',
	);
}

/**
 * Get the effective property special from property or floor-plan meta fields.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string|null The property specials text.
 */
function rentfetch_get_property_specials_from_meta( $property_id = null ) {
	$specials      = rentfetch_get_effective_property_special( $property_id );
	$specials_text = $specials ? $specials['heading'] : null;

	return apply_filters( 'rentfetch_filter_property_specials_from_meta', $specials_text );
}

/**
 * Echo property specials from meta fields.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_specials_from_meta( $property_id = null ) {
	$specials = rentfetch_get_property_specials_from_meta( $property_id );

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
 * Get effective property special content from property or floor-plan meta fields.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string|null The property specials content.
 */
function rentfetch_get_property_specials_content_from_meta( $property_id = null ) {
	$special = rentfetch_get_effective_property_special( $property_id );

	if ( ! $special || empty( $special['content'] ) ) {
		return null;
	}

	return apply_filters( 'rentfetch_filter_property_specials_content_from_meta', $special['content'], $special['property_post_id'] );
}

/**
 * Get minimally marked-up property specials.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string The property specials markup.
 */
function rentfetch_get_property_specials_markup_from_meta( $property_id = null ) {
	$special = rentfetch_get_effective_property_special( $property_id );

	if ( ! $special ) {
		return '';
	}

	$output  = '<span class="specials">';
	$output .= '<span class="specials-heading">' . esc_html( $special['heading'] ) . '</span>';
	if ( ! empty( $special['content'] ) ) {
		$output .= '<span class="specials-content">' . esc_html( $special['content'] ) . '</span>';
	}
	$output .= '</span>';

	return apply_filters( 'rentfetch_filter_property_specials_markup_from_meta', $output, $property_id );
}

/**
 * Get the allowed HTML for a special callout.
 *
 * @return array<string, mixed>
 */
function rentfetch_get_specials_callout_allowed_html() {
	return array_merge(
		wp_kses_allowed_html( 'post' ),
		array(
			'svg'    => array(
				'xmlns'        => true,
				'fill'         => true,
				'viewbox'      => true,
				'stroke-width' => true,
				'stroke'       => true,
				'aria-hidden'  => true,
				'focusable'    => true,
			),
			'path'   => array(
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'd'               => true,
			),
			'circle' => array(
				'cx' => true,
				'cy' => true,
				'r'  => true,
			),
		)
	);
}

/**
 * Build the shared special callout markup.
 *
 * @param string $specials_heading Special heading.
 * @param string $specials_content Special content.
 * @return string
 */
function rentfetch_get_specials_callout_markup( $specials_heading, $specials_content ) {
	$specials_icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 100 100" aria-hidden="true" focusable="false"><path d="m83.332 50c0-1.7422 0.91797-4.1953 1.8008-6.5703 1.6914-4.5391 3.6094-9.6797 0.95313-14.27-2.7305-4.7266-8.2461-5.6289-13.109-6.4258-2.4219-0.3945-4.9258-0.8047-6.3086-1.6094-1.2734-0.7383-2.8125-2.6367-4.2969-4.4805-2.9922-3.7031-6.7188-8.3125-12.371-8.3125-5.6602 0-9.3789 4.6094-12.371 8.3125-1.4883 1.8438-3.0234 3.7461-4.3008 4.4844-1.3828 0.8008-3.8867 1.207-6.3086 1.6055-4.8594 0.79297-10.371 1.6992-13.102 6.4258-2.6602 4.5898-0.7422 9.7305 0.94922 14.27 0.8828 2.375 1.8008 4.8281 1.8008 6.5703s-0.91797 4.1953-1.8008 6.5664c-1.6914 4.543-3.6094 9.6836-0.95313 14.27 2.7344 4.7305 8.2461 5.6328 13.113 6.4297 2.4219 0.3945 4.9258 0.8047 6.3008 1.6055 1.2734 0.7383 2.8086 2.6406 4.2969 4.4805 2.9922 3.707 6.7148 8.3164 12.375 8.3164 5.6523 0 9.3828-4.6094 12.375-8.3164 1.4883-1.8359 3.0234-3.7422 4.2969-4.4805 1.3789-0.7969 3.8828-1.207 6.3047-1.6055 4.8672-0.7969 10.379-1.6992 13.109-6.4258 2.6602-4.5898 0.7422-9.7344-0.95313-14.273-0.8828-2.3711-1.8008-4.8242-1.8008-6.5664zm-50-10.418c0-3.4492 2.8008-6.25 6.25-6.25 3.4531 0 6.25 2.8008 6.25 6.25 0 3.4531-2.7969 6.25-6.25 6.25-3.4492 0-6.25-2.7969-6.25-6.25zm7.1133 25.863-5.8906-5.8906 25-25 5.8906 5.8906zm19.973 1.2227c-3.4492 0-6.25-2.8008-6.25-6.25s2.8008-6.25 6.25-6.25 6.25 2.8008 6.25 6.25-2.8008 6.25-6.25 6.25z" /></svg>';

	ob_start();
	?>
	<div class="property-specials-callout">
		<span class="property-specials-icon"><?php echo $specials_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<div class="property-specials-text">
			<?php if ( $specials_heading ) : ?>
				<p class="property-specials-heading"><?php echo esc_html( $specials_heading ); ?></p>
			<?php endif; ?>

			<?php if ( $specials_content ) : ?>
				<div class="property-specials-content">
					<?php echo wp_kses_post( wpautop( esc_html( $specials_content ) ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Get the single-property specials callout markup.
 *
 * @param string $property_id Optional property_id meta value.
 * @return string|null The property specials callout markup.
 */
function rentfetch_get_property_specials_callout_from_meta( $property_id = null ) {
	$specials = rentfetch_get_effective_property_special( $property_id );

	if ( ! $specials ) {
		return null;
	}

	return apply_filters( 'rentfetch_filter_property_specials_callout_from_meta', rentfetch_get_specials_callout_markup( $specials['heading'], $specials['content'] ), $property_id );
}

/**
 * Echo the single-property specials callout markup.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_specials_callout_from_meta( $property_id = null ) {
	$specials_callout = rentfetch_get_property_specials_callout_from_meta( $property_id );

	if ( $specials_callout ) {
		echo wp_kses( $specials_callout, rentfetch_get_specials_callout_allowed_html() );
	}
}

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
 * @param string $property_id Optional property_id meta value.
 * @return  string The property description.
 */
function rentfetch_get_property_description( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}
	$property_description = get_post_meta( $post_id, 'description', true );
	$property_description = apply_filters( 'the_content', $property_description );
	$property_description = apply_filters( 'rentfetch_filter_property_description', $property_description );
	return $property_description;
}

/**
 * Echo the property description
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_description( $property_id = null ) {
	$property_description = rentfetch_get_property_description( $property_id );
	if ( $property_description ) {
		echo wp_kses_post( $property_description );
	}
}

// * PROPERTY TOUR.

/**
 * Get the property tour embed or link.
 *
 * @param string $property_id Optional property_id meta value.
 * @param bool   $embed_direct Whether to return direct embed or link. Default false (link).
 * @return string the tour markup.
 */
function rentfetch_get_property_tour( $property_id = null, $embed_direct = false ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}

	$tours = rentfetch_get_property_tours( $post_id );
	if ( ! $tours ) {
		return apply_filters( 'rentfetch_filter_property_tour', null );
	}

	$tour = $tours[0];
	if ( $embed_direct ) {
		return apply_filters( 'rentfetch_filter_property_tour', rentfetch_get_tour_embed_html( $tour['url'] ) );
	}

	wp_enqueue_style( 'rentfetch-glightbox-style' );
	wp_enqueue_script( 'rentfetch-glightbox-script' );
	wp_enqueue_script( 'rentfetch-glightbox-init' );

	$class    = 'tour-link';
	$lightbox = '';
	$target   = ' target="_blank" rel="noopener noreferrer"';
	if ( in_array( $tour['type'], array( 'youtube', 'matterport' ), true ) ) {
		$class .= ' tour-link-' . $tour['type'];
		$target = '';
	}
	if ( 'youtube' === $tour['type'] ) {
		$lightbox = ' data-glightbox="type: video;"';
	}

	$embedlink = sprintf( '<div class="tour-link-wrapper"><a class="%s"%s%s data-gallery="post-%s" href="%s"></a></div>', $class, $target, $lightbox, $post_id, esc_url( $tour['link_url'] ) );

	return apply_filters( 'rentfetch_filter_property_tour', $embedlink );
}

/**
 * Echoes the property fees embed code.
 *
 * @param string|int|null $property_id_or_post_id Property ID meta value or Post ID.
 * @return void
 */
function rentfetch_property_fees_embed( $property_id_or_post_id = null ) {
	echo rentfetch_get_property_fees_embed( $property_id_or_post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Resolve a property meta ID first, then fall back to a property post ID.
 *
 * @param string|int|null $property_id_or_post_id Property ID meta value or Post ID.
 * @return int|null The property post ID.
 */
function rentfetch_resolve_property_post_id( $property_id_or_post_id = null ) {
	if ( ! $property_id_or_post_id ) {
		return get_the_ID();
	}

	$post_id = rentfetch_get_post_id_from_property_id( $property_id_or_post_id );
	if ( ! $post_id && is_numeric( $property_id_or_post_id ) && 'properties' === get_post_type( (int) $property_id_or_post_id ) ) {
		$post_id = (int) $property_id_or_post_id;
	}

	return $post_id;
}

/**
 * Get the active fees-display source context for a property.
 *
 * Mirrors the same precedence used by the frontend fees embed renderer.
 *
 * @param string|int|null $property_id_or_post_id     Property ID meta value or Post ID.
 * @param bool            $respect_frontend_visibility Whether to honor the global frontend visibility setting.
 * @return array
 */
function rentfetch_get_property_fees_display_source_context( $property_id_or_post_id = null, $respect_frontend_visibility = true ) {
	$post_id = rentfetch_resolve_property_post_id( $property_id_or_post_id );

	$context = array(
		'source_key'   => 'none',
		'source_label' => 'No active fees source',
	);

	if ( $respect_frontend_visibility && ! rentfetch_should_show_property_fees() ) {
		return $context;
	}

	if ( $post_id ) {
		$synced_fee_context    = rentfetch_get_synced_property_fee_context( $post_id );
		$property_fees_data    = get_post_meta( $post_id, 'property_fees_data', true );
		$property_fees_csv_url = get_post_meta( $post_id, 'property_fees_csv_url', true );
		$property_fees_embed   = get_post_meta( $post_id, 'property_fees_embed', true );

		if ( is_array( $synced_fee_context ) ) {
			return array(
				'source_key'   => $synced_fee_context['source_key'],
				'source_label' => $synced_fee_context['source_label'],
			);
		}

		if ( ! empty( $property_fees_csv_url ) ) {
			$csv_content = rentfetch_get_cached_fees_csv_content( $property_fees_csv_url );
			if ( false !== $csv_content ) {
				$fees_data = rentfetch_process_csv_content_to_fees_array( $csv_content );
				if ( ! empty( $fees_data ) ) {
					return array(
						'source_key'   => 'property_csv',
						'source_label' => 'Property fees CSV',
					);
				}
			}
		}

		if ( ! empty( $property_fees_data ) && is_array( $property_fees_data ) ) {
			return array(
				'source_key'   => 'property_data',
				'source_label' => 'Property fees data',
			);
		}

		if ( ! empty( $property_fees_embed ) ) {
			return array(
				'source_key'   => 'property_embed',
				'source_label' => 'Property fees embed code',
			);
		}
	}

	$global_fees_csv_url = get_option( 'rentfetch_options_global_property_fees_csv_url' );
	$global_fees_data    = get_option( 'rentfetch_options_global_property_fees_data' );
	$global_fees_embed   = get_option( 'rentfetch_options_global_property_fees_embed' );

	if ( ! empty( $global_fees_csv_url ) ) {
		$csv_content = rentfetch_get_cached_fees_csv_content( $global_fees_csv_url );
		if ( false !== $csv_content ) {
			$fees_data = rentfetch_process_csv_content_to_fees_array( $csv_content );
			if ( ! empty( $fees_data ) ) {
				return array(
					'source_key'   => 'global_csv',
					'source_label' => 'Global fees CSV fallback',
				);
			}
		}
	}

	if ( ! empty( $global_fees_data ) && is_array( $global_fees_data ) ) {
		return array(
			'source_key'   => 'global_data',
			'source_label' => 'Global fees data fallback',
		);
	}

	if ( ! empty( $global_fees_embed ) ) {
		return array(
			'source_key'   => 'global_embed',
			'source_label' => 'Global fees embed fallback',
		);
	}

	return $context;
}

/**
 * Gets the property fees embed code.
 *
 * @param string|int|null $property_id_or_post_id     Property ID meta value or Post ID.
 * @param bool            $respect_frontend_visibility Whether to honor the global frontend visibility setting.
 * @return string The property fees embed code.
 */
function rentfetch_get_property_fees_embed( $property_id_or_post_id = null, $respect_frontend_visibility = true ) {
	if ( $respect_frontend_visibility && ! rentfetch_should_show_property_fees() ) {
		return '';
	}

	$post_id = rentfetch_resolve_property_post_id( $property_id_or_post_id );

	$property_fees_markup       = '';
	$api_fees_are_authoritative = false;

	// If we have a valid post_id, try property-specific fees first.
	if ( $post_id ) {
		$synced_fee_context    = rentfetch_get_synced_property_fee_context( $post_id );
		$api_fees_data         = is_array( $synced_fee_context ) && isset( $synced_fee_context['rows'] ) && is_array( $synced_fee_context['rows'] )
			? $synced_fee_context['rows']
			: array();
		$property_fees_data    = get_post_meta( $post_id, 'property_fees_data', true );
		$property_fees_csv_url = get_post_meta( $post_id, 'property_fees_csv_url', true );
		$property_fees_embed   = get_post_meta( $post_id, 'property_fees_embed', true );

		// Priority 0: synced lease-fee API data is authoritative when present.
		if ( is_array( $synced_fee_context ) ) {
			$api_fees_are_authoritative = true;
			if ( ! empty( $api_fees_data ) ) {
				$property_fees_json   = wp_json_encode( $api_fees_data );
				$property_fees_markup = rentfetch_get_property_fees_markup( $property_fees_json, $respect_frontend_visibility );
			}
		}

		// Priority 1: Use property_fees_csv_url if available.
		if ( ! $api_fees_are_authoritative && ! empty( $property_fees_csv_url ) ) {
			$csv_content = rentfetch_get_cached_fees_csv_content( $property_fees_csv_url );
			if ( false !== $csv_content ) {
				$fees_data = rentfetch_process_csv_content_to_fees_array( $csv_content );
				if ( ! empty( $fees_data ) ) {
					$property_fees_json   = wp_json_encode( $fees_data );
					$property_fees_markup = rentfetch_get_property_fees_markup( $property_fees_json, $respect_frontend_visibility );
				}
			}
		}

		// Priority 2: Use property_fees_data (this is the json) if it's a non-empty array.
		// This is also a fallback if the CSV URL exists but fails to fetch/parse.
		if ( ! $api_fees_are_authoritative && empty( $property_fees_markup ) && ! empty( $property_fees_data ) && is_array( $property_fees_data ) ) {
			$property_fees_json   = wp_json_encode( $property_fees_data );
			$property_fees_markup = rentfetch_get_property_fees_markup( $property_fees_json, $respect_frontend_visibility );
		}

		// Priority 3: Fallback to property_fees_embed.
		if ( ! $api_fees_are_authoritative && empty( $property_fees_markup ) && ! empty( $property_fees_embed ) ) {
			$property_fees_markup = $property_fees_embed;
		}
	}

	if ( $api_fees_are_authoritative && empty( $property_fees_markup ) ) {
		return '';
	}

	// If no property-specific fees or no post_id, try global fallbacks.
	if ( empty( $property_fees_markup ) ) {
		$global_fees_csv_url = get_option( 'rentfetch_options_global_property_fees_csv_url' );
		$global_fees_data    = get_option( 'rentfetch_options_global_property_fees_data' );
		$global_fees_embed   = get_option( 'rentfetch_options_global_property_fees_embed' );

		// Priority 1: Use global_fees_csv_url if available.
		if ( ! empty( $global_fees_csv_url ) ) {
			$csv_content = rentfetch_get_cached_fees_csv_content( $global_fees_csv_url );
			if ( false !== $csv_content ) {
				$fees_data = rentfetch_process_csv_content_to_fees_array( $csv_content );
				if ( ! empty( $fees_data ) ) {
					$global_fees_json     = wp_json_encode( $fees_data );
					$property_fees_markup = rentfetch_get_property_fees_markup( $global_fees_json, $respect_frontend_visibility );
				}
			}
		}

		// Priority 2: Use global_fees_data if it's a non-empty array.
		// This is also a fallback if the CSV URL exists but fails to fetch/parse.
		if ( empty( $property_fees_markup ) && ! empty( $global_fees_data ) && is_array( $global_fees_data ) ) {
			$global_fees_json     = wp_json_encode( $global_fees_data );
			$property_fees_markup = rentfetch_get_property_fees_markup( $global_fees_json, $respect_frontend_visibility );
		}

		// Priority 3: Fallback to global_fees_embed.
		if ( empty( $property_fees_markup ) && ! empty( $global_fees_embed ) ) {
			$property_fees_markup = $global_fees_embed;
		}
		// Return an empty string when none of the fee sources has content.
		if ( empty( $property_fees_markup ) ) {
			return '';
		}
	}

	// Add description text before the fees markup (filterable, accepts HTML)
	// The filtered text is run through the_content to auto-add paragraphs.
	$fees_description = apply_filters(
		'rentfetch_property_fees_description',
		'Please note that prices shown are base rent. To help budget your monthly costs and make it easy to understand what your rent includes and what may be additional, we\'ve included the list of potential fees below.',
		$post_id
	);

	// Prepend description to markup if not empty, wrapped in a styled container.
	if ( ! empty( $fees_description ) ) {
		$fees_description_html = apply_filters( 'the_content', $fees_description );
		$property_fees_markup  = '<div class="property-fees-description">' . $fees_description_html . '</div>' . $property_fees_markup;
	}

	return apply_filters( 'rentfetch_filter_property_fees_embed', $property_fees_markup, $post_id );
}

/**
 * Get the transient key for cached fees CSV content.
 *
 * @param string $csv_url CSV URL.
 * @return string|null
 */
function rentfetch_get_fees_csv_cache_key( $csv_url ) {
	$csv_url = trim( (string) $csv_url );
	if ( '' === $csv_url ) {
		return null;
	}

	return 'rentfetch_fees_csv_' . md5( $csv_url );
}

/**
 * Get the transient key for cached monthly required fee calculations by CSV URL.
 *
 * @param string $csv_url CSV URL.
 * @return string|null
 */
function rentfetch_get_fees_csv_monthly_required_calc_cache_key( $csv_url ) {
	$csv_url = trim( (string) $csv_url );
	if ( '' === $csv_url ) {
		return null;
	}

	return 'rentfetch_fees_csv_calc_' . md5( $csv_url );
}

/**
 * Clear cached fees CSV content for a URL.
 *
 * @param string $csv_url CSV URL.
 * @return void
 */
function rentfetch_clear_cached_fees_csv_content( $csv_url ) {
	$cache_key = rentfetch_get_fees_csv_cache_key( $csv_url );
	if ( ! $cache_key ) {
		return;
	}

	delete_transient( $cache_key );
}

/**
 * Clear cached monthly required fee calculation results for a CSV URL.
 *
 * @param string $csv_url CSV URL.
 * @return void
 */
function rentfetch_clear_cached_monthly_required_fees_calculation( $csv_url ) {
	$cache_key = rentfetch_get_fees_csv_monthly_required_calc_cache_key( $csv_url );
	if ( ! $cache_key ) {
		return;
	}

	delete_transient( $cache_key );
}

/**
 * Fetch fees CSV content with short-term URL-keyed caching.
 *
 * Shared CSV URLs share the same transient across properties.
 *
 * @param string $csv_url CSV URL.
 * @return string|false CSV content on success, false on failure.
 */
function rentfetch_get_cached_fees_csv_content( $csv_url ) {
	$csv_url = trim( (string) $csv_url );
	if ( '' === $csv_url ) {
		return false;
	}

	$cache_key      = rentfetch_get_fees_csv_cache_key( $csv_url );
	$disable_caches = get_option( 'rentfetch_options_disable_query_caching', '1' ) === '1';

	if ( ! $disable_caches && $cache_key ) {
		$cached_content = get_transient( $cache_key );
		if ( is_string( $cached_content ) && '' !== $cached_content ) {
			return $cached_content;
		}
	}

	$response = wp_remote_get(
		$csv_url,
		array(
			'timeout'   => 15,
			'sslverify' => false, // Allow self-signed certs for local development.
		)
	);

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	$csv_content = (string) wp_remote_retrieve_body( $response );
	if ( '' === $csv_content ) {
		return false;
	}

	if ( ! $disable_caches && $cache_key ) {
		set_transient( $cache_key, $csv_content, 30 * MINUTE_IN_SECONDS );
	}

	return $csv_content;
}

/**
 * Get monthly required fee calculation results for a CSV URL with 12-hour URL-keyed caching.
 *
 * Shared CSV URLs share a single validation/recalculation cadence.
 *
 * @param string $csv_url         CSV URL.
 * @param bool   $force_recompute Whether to force recomputing now.
 * @return array{
 *   checked_at:int,
 *   has_positive_total:bool,
 *   total:float,
 *   contributors:array
 * }
 */
function rentfetch_get_cached_monthly_required_fees_calculation( $csv_url, $force_recompute = false ) {
	$csv_url = trim( (string) $csv_url );
	$result  = array(
		'checked_at'         => time(),
		'has_positive_total' => false,
		'total'              => 0.0,
		'contributors'       => array(),
	);

	if ( '' === $csv_url ) {
		return $result;
	}

	$cache_key = rentfetch_get_fees_csv_monthly_required_calc_cache_key( $csv_url );
	$now       = time();

	if ( ! $force_recompute && $cache_key ) {
		$cached_result = get_transient( $cache_key );
		if ( is_array( $cached_result ) ) {
			$checked_at = isset( $cached_result['checked_at'] ) ? (int) $cached_result['checked_at'] : 0;
			if ( $checked_at > 0 && ( $now - $checked_at ) < ( 12 * HOUR_IN_SECONDS ) ) {
				$cached_has_positive_total = ! empty( $cached_result['has_positive_total'] );
				$cached_total              = isset( $cached_result['total'] ) ? (float) $cached_result['total'] : 0.0;
				$cached_contributors       = isset( $cached_result['contributors'] ) && is_array( $cached_result['contributors'] ) ? $cached_result['contributors'] : array();

				return array(
					'checked_at'         => $checked_at,
					'has_positive_total' => $cached_has_positive_total,
					'total'              => $cached_total,
					'contributors'       => $cached_contributors,
				);
			}
		}
	}

	$csv_content = rentfetch_get_cached_fees_csv_content( $csv_url );
	if ( false === $csv_content ) {
		if ( $cache_key ) {
			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
		}
		return $result;
	}

	$fees_data = rentfetch_process_csv_content_to_fees_array( $csv_content );
	if ( empty( $fees_data ) ) {
		if ( $cache_key ) {
			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
		}
		return $result;
	}

	$contributors = rentfetch_get_monthly_required_fee_contributors( $fees_data );
	$total        = rentfetch_calculate_monthly_required_total_fees( $fees_data );

	if ( $total > 0 ) {
		$result['has_positive_total'] = true;
		$result['total']              = (float) round( $total, 2 );
		$result['contributors']       = $contributors;
	}

	if ( $cache_key ) {
		set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
	}

	return $result;
}

/**
 * Normalize fee text so duplicate tooltip content can be detected reliably.
 *
 * @param string $text Raw fee text.
 * @return string
 */
function rentfetch_normalize_property_fee_tooltip_text( $text ) {
	$text = wp_strip_all_tags( html_entity_decode( (string) $text, ENT_QUOTES, 'UTF-8' ) );
	$text = strtolower( trim( preg_replace( '/\s+/', ' ', $text ) ) );
	$text = trim( $text, " \t\n\r\0\x0B.:;,-" );

	return $text;
}

/**
 * Prepare tooltip HTML for a fee row, suppressing duplicate description-only notes.
 *
 * @param array $fee Fee row payload.
 * @return string
 */
function rentfetch_get_property_fee_tooltip_html( $fee ) {
	$raw_longnotes = isset( $fee['longnotes'] ) ? (string) $fee['longnotes'] : '';
	if ( '' === trim( $raw_longnotes ) ) {
		return '';
	}

	$longnotes_html       = wp_kses_post( apply_filters( 'the_content', $raw_longnotes ) );
	$normalized_longnotes = rentfetch_normalize_property_fee_tooltip_text( $longnotes_html );
	if ( '' === $normalized_longnotes ) {
		return '';
	}

	$description            = isset( $fee['description'] ) ? (string) $fee['description'] : '';
	$normalized_description = rentfetch_normalize_property_fee_tooltip_text( $description );

	if ( '' !== $normalized_description && $normalized_description === $normalized_longnotes ) {
		return '';
	}

	return $longnotes_html;
}

/**
 * Convert normalized property-fee JSON into grouped table markup.
 *
 * @param string $property_fees_json         Normalized property-fee JSON.
 * @param bool   $respect_frontend_visibility Whether to honor the global frontend visibility setting.
 * @return string
 */
function rentfetch_get_property_fees_markup( $property_fees_json, $respect_frontend_visibility = true ) {
	if ( $respect_frontend_visibility && ! rentfetch_should_show_property_fees() ) {
		return '';
	}

	// Start output buffering.
	ob_start();

	// Decode the JSON.
	$fees_data = json_decode( $property_fees_json, true );

	// If JSON is invalid or empty, return empty string.
	if ( ! is_array( $fees_data ) || empty( $fees_data ) ) {
		return '';
	}

	// Check if any fee has longnotes (for tooltip functionality).
	$has_tooltip_content = false;
	foreach ( $fees_data as $fee ) {
		if ( '' !== rentfetch_get_property_fee_tooltip_html( $fee ) ) {
			$has_tooltip_content = true;
			break;
		}
	}

	// Enqueue tooltip script if we have content to display.
	if ( $has_tooltip_content ) {
		wp_enqueue_script( 'rentfetch-tooltip' );
	}
	// Extract unique categories.
	$categories = array();
	foreach ( $fees_data as $fee ) {
		if ( ! empty( $fee['category'] ) ) {
			$categories[] = $fee['category'];
		}
	}
	$categories = array_unique( $categories );

	// If we have categories, group by category.
	if ( ! empty( $categories ) ) {
		foreach ( $categories as $category ) {
			// Get fees for this category.
			$category_fees = array_filter(
				$fees_data,
				function ( $fee ) use ( $category ) {
					return isset( $fee['category'] ) && $fee['category'] === $category;
				}
			);

			// Output category header.
			echo '<h3>' . esc_html( $category ) . '</h3>';

			// Start table.
			echo '<table class="property-fees-table" style="width:100%; table-layout:fixed;">';
			echo '<colgroup>';
			echo '<col style="width:42%;">';
			echo '<col style="width:33%;">';
			echo '<col style="width:25%;">';
			echo '</colgroup>';
			echo '<tbody>';

			// Output table rows.
			foreach ( $category_fees as $fee ) {
				$longnotes_html = rentfetch_get_property_fee_tooltip_html( $fee );
				$has_longnotes  = '' !== $longnotes_html;
				echo '<tr>';
				echo '<td class="fee-description">';
				if ( $has_longnotes ) {
					echo '<span class="fee-description-with-tooltip rentfetch-tooltip-trigger" data-tooltip-content="' . esc_attr( $longnotes_html ) . '">';
					echo esc_html( $fee['description'] ?? '' );
					echo '<span class="fee-info-icon rentfetch-tooltip-icon" aria-label="More information"></span>';
					echo '</span>';
				} else {
					echo esc_html( $fee['description'] ?? '' );
				}
				echo '</td>';
				echo '<td class="fee-price-frequency">';
				echo '<span class="fee-price">' . esc_html( $fee['price'] ?? '' ) . '</span> ';
				echo '<span class="fee-frequency">' . esc_html( $fee['frequency'] ?? '' ) . '</span>';
				echo '</td>';
				echo '<td class="fee-notes">' . esc_html( $fee['notes'] ?? '' ) . '</td>';
				echo '</tr>';
			}

			// End table.
			echo '</tbody>';
			echo '</table>';
		}
	} else {
		// No categories, output single table.
		echo '<table class="property-fees-table" style="width:100%; table-layout:fixed;">';
		echo '<colgroup>';
		echo '<col style="width:42%;">';
		echo '<col style="width:33%;">';
		echo '<col style="width:25%;">';
		echo '</colgroup>';
		echo '<tbody>';

		foreach ( $fees_data as $fee ) {
			$longnotes_html = rentfetch_get_property_fee_tooltip_html( $fee );
			$has_longnotes  = '' !== $longnotes_html;
			echo '<tr>';
			echo '<td class="fee-description">';
			if ( $has_longnotes ) {
				echo '<span class="fee-description-with-tooltip rentfetch-tooltip-trigger" data-tooltip-content="' . esc_attr( $longnotes_html ) . '">';
				echo esc_html( $fee['description'] ?? '' );
				echo '<span class="fee-info-icon rentfetch-tooltip-icon" aria-label="More information"></span>';
				echo '</span>';
			} else {
				echo esc_html( $fee['description'] ?? '' );
			}
			echo '</td>';
			echo '<td class="fee-price-frequency">';
			echo '<span class="fee-price">' . esc_html( $fee['price'] ?? '' ) . '</span> ';
			echo '<span class="fee-frequency">' . esc_html( $fee['frequency'] ?? '' ) . '</span>';
			echo '</td>';
			echo '<td class="fee-notes">' . esc_html( $fee['notes'] ?? '' ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';
	}

	// Return the buffered output.
	return ob_get_clean();
}

/**
 * Get the property post ID when the site contains exactly one property.
 *
 * @return int|null The property post ID, or null when this is not a single-property site.
 */
function rentfetch_website_single_property_site_get_property_id() {
	// Query at most two properties; a single result identifies a single-property site.
	$property_query = new WP_Query(
		array(
			'post_type'      => 'properties',
			'posts_per_page' => 2,
			'post_status'    => 'publish',
		)
	);

	// If there's exactly one property, get its post ID.
	if ( $property_query->have_posts() && 1 === $property_query->found_posts ) {
		$property_query->the_post();
		$post_id = $property_query->posts[0]->ID;
	} else {
		return;
	}

	// Reset the query so that we don't affect the main query.
	wp_reset_postdata();

	return $post_id;
}

/**
 * Output the property fees embed for a single-property site.
 *
 * @return void.
 */
function rentfetch_property_fees_embed_and_wrap() {
	// A single-property site provides the property post ID.
	$post_id = rentfetch_website_single_property_site_get_property_id();

	if ( ! $post_id ) {
		return;
	}

	$embed = rentfetch_get_property_fees_embed( $post_id );
	if ( ! $embed ) {
		return;
	}

	echo '<div class="rentfetch-after-floorplans-grid-search-property-fees-embed-wrapper">';
	echo wp_kses( $embed, rentfetch_get_allowed_embed_html() );
	echo '</div>';
}
add_action( 'rentfetch_after_floorplans_simple_grid', 'rentfetch_property_fees_embed_and_wrap' );
add_action( 'rentfetch_after_floorplans_search', 'rentfetch_property_fees_embed_and_wrap' );

/**
 * Extract the first numeric value from a fee price string.
 *
 * Examples:
 * "$85" => 85
 * "$42-$65" => 65
 * "$10-20" => 20
 * "2.5% of total payment" => 2.5
 *
 * @param string $price The raw price string.
 * @return float|null A numeric value or null if none is found.
 */
function rentfetch_extract_first_numeric_fee_value( $price ) {
	$price_string = trim( (string) $price );
	if ( '' === $price_string ) {
		return null;
	}

	// For explicit ranges like "$10-20" or "$10-$20", use the higher bound.
	if ( preg_match( '/\$?\s*(-?\d[\d,]*(?:\.\d+)?)\s*-\s*\$?\s*(-?\d[\d,]*(?:\.\d+)?)/', $price_string, $range_matches ) ) {
		$range_start = str_replace( ',', '', $range_matches[1] );
		$range_end   = str_replace( ',', '', $range_matches[2] );

		if ( is_numeric( $range_start ) && is_numeric( $range_end ) ) {
			return (float) max( (float) $range_start, (float) $range_end );
		}
	}

	if ( ! preg_match( '/-?\d[\d,]*(?:\.\d+)?/', $price_string, $matches ) ) {
		return null;
	}

	$normalized = str_replace( ',', '', $matches[0] );
	if ( ! is_numeric( $normalized ) ) {
		return null;
	}

	return (float) $normalized;
}

/**
 * Get rows that contribute to monthly required total fees.
 *
 * @param array $fees_data Parsed fee rows.
 * @return array[] Contributing rows with 'description' and 'applied_price'.
 */
function rentfetch_get_monthly_required_fee_contributors( $fees_data ) {
	$contributors = array();

	if ( ! is_array( $fees_data ) || empty( $fees_data ) ) {
		return $contributors;
	}

	foreach ( $fees_data as $fee ) {
		if ( ! is_array( $fee ) ) {
			continue;
		}

		$notes     = strtolower( trim( (string) ( $fee['notes'] ?? '' ) ) );
		$frequency = strtolower( (string) ( $fee['frequency'] ?? '' ) );

		if ( 'required' !== $notes ) {
			continue;
		}

		if ( false === strpos( $frequency, 'month' ) ) {
			continue;
		}

		$numeric_price = rentfetch_extract_first_numeric_fee_value( $fee['price'] ?? '' );
		if ( null === $numeric_price || $numeric_price <= 0 ) {
			continue;
		}

		$contributors[] = array(
			'description'   => sanitize_text_field( (string) ( $fee['description'] ?? '' ) ),
			'applied_price' => (float) $numeric_price,
		);
	}

	return $contributors;
}

/**
 * Calculate monthly required total fees from parsed CSV fee rows.
 *
 * Rules:
 * - notes must exactly match "required" (case-insensitive)
 * - frequency must fuzzy-match "month" (case-insensitive)
 * - price contributes a parsed numeric value from the price column
 *   (for explicit numeric ranges, the higher bound is used)
 *
 * @param array $fees_data Parsed fee rows.
 * @return float Total monthly required fees.
 */
function rentfetch_calculate_monthly_required_total_fees( $fees_data ) {
	$contributors = rentfetch_get_monthly_required_fee_contributors( $fees_data );
	$total        = 0.0;

	foreach ( $contributors as $contributor ) {
		$total += (float) ( $contributor['applied_price'] ?? 0 );
	}

	return round( $total, 2 );
}

/**
 * Update stored monthly required total fees for a property from its fees CSV URL.
 *
 * @param int  $property_post_id The property post ID.
 * @param bool $force_recompute  Whether to bypass the cached calculation.
 * @return bool True when a positive total is saved, false otherwise.
 */
function rentfetch_update_property_monthly_required_total_fees_from_csv( $property_post_id, $force_recompute = false ) {
	$property_post_id = (int) $property_post_id;
	if ( $property_post_id <= 0 ) {
		return false;
	}

	$csv_url = trim( (string) get_post_meta( $property_post_id, 'property_fees_csv_url', true ) );

	// No CSV URL present: do not modify the stored value here.
	// Explicit removal clearing is handled in the property save flow.
	if ( '' === $csv_url ) {
		return false;
	}

	$calculation = rentfetch_get_cached_monthly_required_fees_calculation( $csv_url, (bool) $force_recompute );
	$checked_at  = isset( $calculation['checked_at'] ) ? (int) $calculation['checked_at'] : time();
	update_post_meta( $property_post_id, 'property_monthly_required_total_fees_last_checked', $checked_at );

	$has_positive_total = ! empty( $calculation['has_positive_total'] );
	$total              = isset( $calculation['total'] ) ? (float) $calculation['total'] : 0.0;
	$contributors       = isset( $calculation['contributors'] ) && is_array( $calculation['contributors'] ) ? $calculation['contributors'] : array();

	// Requirement: don't save if total is zero (or missing/invalid).
	if ( ! $has_positive_total || $total <= 0 ) {
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees' );
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees_rows' );
		return false;
	}

	update_post_meta( $property_post_id, 'property_monthly_required_total_fees', number_format( $total, 2, '.', '' ) );
	update_post_meta( $property_post_id, 'property_monthly_required_total_fees_rows', $contributors );
	return true;
}

/**
 * Update stored monthly required total fees from the global fees CSV URL.
 *
 * @param bool $force_recompute Whether to bypass the cached calculation.
 * @return bool True when a positive total is saved, false otherwise.
 */
function rentfetch_update_global_monthly_required_total_fees_from_csv( $force_recompute = false ) {
	$csv_url = trim( (string) get_option( 'rentfetch_options_global_property_fees_csv_url', '' ) );

	// No CSV URL present: do not modify the stored value here.
	// Explicit removal clearing is handled in the options save flow.
	if ( '' === $csv_url ) {
		return false;
	}

	$calculation = rentfetch_get_cached_monthly_required_fees_calculation( $csv_url, (bool) $force_recompute );
	$checked_at  = isset( $calculation['checked_at'] ) ? (int) $calculation['checked_at'] : time();
	update_option( 'rentfetch_options_global_monthly_required_total_fees_last_checked', $checked_at );

	$has_positive_total = ! empty( $calculation['has_positive_total'] );
	$total              = isset( $calculation['total'] ) ? (float) $calculation['total'] : 0.0;
	$contributors       = isset( $calculation['contributors'] ) && is_array( $calculation['contributors'] ) ? $calculation['contributors'] : array();

	// Don't save if total is zero (or missing/invalid).
	if ( ! $has_positive_total || $total <= 0 ) {
		delete_option( 'rentfetch_options_global_monthly_required_total_fees' );
		delete_option( 'rentfetch_options_global_monthly_required_total_fees_rows' );
		return false;
	}

	update_option( 'rentfetch_options_global_monthly_required_total_fees', number_format( $total, 2, '.', '' ) );
	update_option( 'rentfetch_options_global_monthly_required_total_fees_rows', $contributors );
	return true;
}

/**
 * Resolve the current singular context to a property post ID for fee refreshing.
 *
 * @return int|null Property post ID if available, null otherwise.
 */
function rentfetch_get_property_post_id_for_monthly_fees_refresh() {
	if ( ! is_singular() ) {
		return null;
	}

	if ( is_singular( 'properties' ) ) {
		return get_queried_object_id();
	}

	if ( is_singular( 'floorplans' ) ) {
		$floorplan_post_id = get_queried_object_id();
		$property_id       = get_post_meta( $floorplan_post_id, 'property_id', true );
		if ( ! $property_id ) {
			return null;
		}

		return rentfetch_get_post_id_from_property_id( $property_id );
	}

	return null;
}

/**
 * Refresh monthly required total fees for the current property context.
 *
 * Runs at most once every 12 hours per property, on single property/floorplan page loads.
 * Hooked early enough in the request lifecycle so rendered pricing can use refreshed values.
 *
 * @return void
 */
function rentfetch_maybe_refresh_property_monthly_required_total_fees() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	$property_post_id = rentfetch_get_property_post_id_for_monthly_fees_refresh();
	if ( ! $property_post_id ) {
		return;
	}

	$csv_url = trim( (string) get_post_meta( $property_post_id, 'property_fees_csv_url', true ) );
	if ( '' === $csv_url ) {
		// No CSV URL: leave current stored value unchanged.
		return;
	}

	$last_checked = (int) get_post_meta( $property_post_id, 'property_monthly_required_total_fees_last_checked', true );
	if ( $last_checked > 0 && ( time() - $last_checked ) < ( 12 * HOUR_IN_SECONDS ) ) {
		return;
	}

	rentfetch_update_property_monthly_required_total_fees_from_csv( $property_post_id );
}
add_action( 'wp', 'rentfetch_maybe_refresh_property_monthly_required_total_fees', 999 );

/**
 * Refresh global monthly required total fees at most once every 12 hours.
 *
 * Keeps the global fallback current even when no one manually triggers an admin refresh.
 *
 * @return void
 */
function rentfetch_maybe_refresh_global_monthly_required_total_fees() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	$csv_url = trim( (string) get_option( 'rentfetch_options_global_property_fees_csv_url', '' ) );
	if ( '' === $csv_url ) {
		return;
	}

	$last_checked = (int) get_option( 'rentfetch_options_global_monthly_required_total_fees_last_checked', 0 );
	if ( $last_checked > 0 && ( time() - $last_checked ) < ( 12 * HOUR_IN_SECONDS ) ) {
		return;
	}

	rentfetch_update_global_monthly_required_total_fees_from_csv();
}
add_action( 'wp', 'rentfetch_maybe_refresh_global_monthly_required_total_fees', 998 );

// * OFFICE HOURS

/**
 * Get the property office hours array
 *
 * @param string $property_id Optional property_id meta value.
 * @return array The property office hours array.
 */
function rentfetch_get_property_office_hours_array( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return array();
		}
		$office_hours = get_post_meta( $post_id, 'office_hours', true );
	} else {
		$office_hours = get_post_meta( get_the_ID(), 'office_hours', true );
	}

	if ( ! is_array( $office_hours ) ) {
		$office_hours = array();
	}

	$office_hours = apply_filters( 'rentfetch_filter_property_office_hours_array', $office_hours, $property_id );
	return $office_hours;
}

/**
 * Get the property office hours
 *
 * @param string $property_id Optional property_id meta value.
 * @param bool   $include_heading Whether to include the heading. Default true.
 * @return string The property office hours HTML markup.
 */
function rentfetch_get_property_office_hours( $property_id = null, $include_heading = true ) {
	$office_hours = rentfetch_get_property_office_hours_array( $property_id );

	if ( empty( $office_hours ) ) {
		return '';
	}

	$days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );

	$output = '';
	if ( $include_heading ) {
		$output .= '<h3>Office Hours</h3>';
	}
	$output .= '<div class="rentfetch-property-office-hours">';
	foreach ( $days as $day ) {
		$output     .= '<div class="office-hours-day">';
			$output .= '<span class="day-name">' . esc_html( ucfirst( $day ) . ':' ) . '</span> ';
		if ( isset( $office_hours[ $day ] ) && ! empty( $office_hours[ $day ]['start'] ) && ! empty( $office_hours[ $day ]['end'] ) ) {
			$start_time = gmdate( 'ga', strtotime( $office_hours[ $day ]['start'] ) );
			$end_time   = gmdate( 'ga', strtotime( $office_hours[ $day ]['end'] ) );
			$output    .= '<span class="day-hours">' . esc_html( $start_time . ' to ' . $end_time ) . '</span>';
		} else {
			$output .= '<span class="day-hours">Closed</span>';
		}
		$output .= '</div>';
	}
	$output .= '</div>';

	return apply_filters( 'rentfetch_filter_property_office_hours', $output, $property_id );
}

/**
 * Parse property fees CSV content into normalized fee rows.
 *
 * @param string $csv_content Raw CSV content.
 * @return array Parsed fee rows.
 */
function rentfetch_process_csv_content_to_fees_array( $csv_content ) {
	$fees_data = array();
	if ( ! is_string( $csv_content ) || '' === trim( $csv_content ) ) {
		return $fees_data;
	}

	$csv_file = new SplTempFileObject();
	$csv_file->fwrite( $csv_content );
	$csv_file->rewind();

	$header = $csv_file->fgetcsv( ',', '"', '\\' );
	if ( false === $header || ! is_array( $header ) ) {
		return $fees_data;
	}

	// Normalize header: trim and lowercase.
	$header = array_map(
		function ( $col ) {
			$clean_col = str_replace( "\xEF\xBB\xBF", '', (string) $col ); // Strip UTF-8 BOM if present.
			return strtolower( trim( $clean_col ) );
		},
		$header
	);

	$expected_columns = array( 'description', 'price', 'frequency', 'notes', 'category', 'longnotes' );

	// Find column indices - only require 'description' to be present.
	$column_indices = array();
	foreach ( $expected_columns as $col ) {
		$index                  = array_search( $col, $header, true );
		$column_indices[ $col ] = ( false !== $index ) ? $index : -1;
	}

	// Must have at least 'description' column.
	if ( -1 === $column_indices['description'] ) {
		return $fees_data;
	}

	while ( ! $csv_file->eof() ) {
		$data = $csv_file->fgetcsv( ',', '"', '\\' );
		if ( ! is_array( $data ) ) {
			continue;
		}

		// Get value from column index, or empty string if column doesn't exist.
		$get_value = function ( $col ) use ( $column_indices, $data ) {
			$index = $column_indices[ $col ];
			if ( -1 === $index || ! isset( $data[ $index ] ) ) {
				return '';
			}
			return sanitize_text_field( $data[ $index ] );
		};

		// Skip rows where description is empty.
		$description = $get_value( 'description' );
		if ( empty( $description ) ) {
			continue;
		}

		// Get longnotes value - allow HTML so use wp_kses_post instead of sanitize_text_field.
		$longnotes_index = $column_indices['longnotes'];
		$longnotes_value = '';
		if ( -1 !== $longnotes_index && isset( $data[ $longnotes_index ] ) ) {
			$longnotes_value = wp_kses_post( $data[ $longnotes_index ] );
		}

		$fees_data[] = array(
			'description' => $description,
			'price'       => $get_value( 'price' ),
			'frequency'   => $get_value( 'frequency' ),
			'notes'       => $get_value( 'notes' ),
			'category'    => $get_value( 'category' ),
			'longnotes'   => $longnotes_value,
		);
	}

	return $fees_data;
}

/**
 * Echo the property office hours.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_office_hours( $property_id = null ) {
	$office_hours = rentfetch_get_property_office_hours( $property_id );

	if ( $office_hours ) {
		echo wp_kses_post( $office_hours );
	}
}
