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
 * @param   array   $classes    The current classes array.
 * @param   string  $property_id Optional property_id meta value.
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
	$floorplan_data = rentfetch_get_floorplans( $property_id_meta );
	
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
 * @param string $class Optional additional CSS class.
 * @return string The property location button.
 */
function rentfetch_get_property_location_button( $property_id = null, $class = '' ) {
	$location_link   = rentfetch_get_property_location_link( $property_id );
	$classes         = 'location-link property-link';
	if ( ! empty( $class ) ) {
		$classes .= ' ' . esc_attr( $class );
	}
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_directions_click', rentfetch_get_property_tracking_context( $property_id ) );
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 location-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>';
	$location_button = sprintf( '<a class="%s" href="%s" target="_blank"%s>%sGet Directions</a>', $classes, esc_url( $location_link ), $tracking_attrs, $svg );
	return apply_filters( 'rentfetch_filter_property_location_button', $location_button, $property_id, $class );
}

/**
 * Echo the property location button
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_location_button( $property_id = null ) {
	$allowed_html = array_merge( wp_kses_allowed_html( 'post' ), array(
		'svg' => array(
			'xmlns' => true,
			'fill' => true,
			'viewbox' => true,
			'stroke-width' => true,
			'stroke' => true,
			'class' => true,
		),
		'path' => array(
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'd' => true,
		),
	) );
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
	// Handle cases with a leading + and more than 10 digits (international numbers).
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
 * @param string $class Optional additional CSS class.
 * @return string The property phone number.
 */
function rentfetch_get_property_phone_button( $property_id = null, $class = '' ) {
	$phone        = rentfetch_get_property_phone( $property_id );
	$phone_link   = rentfetch_format_phone_number_link( $phone );
	$classes      = 'phone-link property-link';
	if ( ! empty( $class ) ) {
		$classes .= ' ' . esc_attr( $class );
	}
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_phonecall_click', rentfetch_get_property_tracking_context( $property_id ) );
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 phone-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>';
	$phone_button = sprintf( '<a class="%s" href="tel:%s"%s>%s%s</a>', $classes, esc_html( $phone_link ), $tracking_attrs, $svg, esc_html( $phone ) );

	if ( $phone ) {
		return apply_filters( 'rentfetch_filter_property_phone_button', $phone_button, $property_id, $class );
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
		$allowed_html = array_merge( wp_kses_allowed_html( 'post' ), array(
			'svg' => array(
				'xmlns' => true,
				'fill' => true,
				'viewbox' => true,
				'stroke-width' => true,
				'stroke' => true,
				'class' => true,
			),
			'path' => array(
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'd' => true,
			),
		) );
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

	$url = get_post_meta( $post_id, 'url', true );
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
	$url = rentfetch_get_property_url( $property_id );
	
	if ( !$url ) {
		$url = get_the_permalink( $post_id );		
	} else {
		if ( 'external' !== $permalink_behavior ) {
			$url = get_the_permalink( $post_id );
		} else {
			$url = rentfetch_get_property_url( $property_id );
		}
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

// * PROPERTY WEBSITE

/**
 * Get the property website.
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $class Optional additional CSS class.
 * @return string The property website.
 */
function rentfetch_get_property_website_button( $property_id = null, $class = '' ) {
	$url            = rentfetch_get_property_url( $property_id );
	$target         = rentfetch_get_link_target( $url );
	$classes        = 'url-link property-link';
	if ( ! empty( $class ) ) {
		$classes .= ' ' . esc_attr( $class );
	}
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_visitpropertywebsite_click', rentfetch_get_property_tracking_context( $property_id ) );
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 website-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>';
	$website_button = sprintf( '<a class="%s" href="%s" target="%s"%s>%sVisit Website</a>', $classes, esc_html( $url ), esc_attr( $target ), $tracking_attrs, $svg );

	if ( $url ) {
		return apply_filters( 'rentfetch_filter_property_website', $website_button, $property_id, $class );
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
		$allowed_html = array_merge( wp_kses_allowed_html( 'post' ), array(
			'svg' => array(
				'xmlns' => true,
				'fill' => true,
				'viewbox' => true,
				'stroke-width' => true,
				'stroke' => true,
				'class' => true,
			),
			'path' => array(
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'd' => true,
			),
		) );
		echo wp_kses( rentfetch_get_property_website_button( $property_id ), $allowed_html );
	}
}

// * PROPERTY EMAIL.

/**
 * Get the property email.
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $class Optional additional CSS class.
 * @return string The property email.
 */
function rentfetch_get_property_contact_button( $property_id = null, $class = '' ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}
	$email          = sanitize_email( apply_filters( 'rentfetch_filter_property_email_address', get_post_meta( $post_id, 'email', true ) ) );
	$email_link     = 'mailto:' . $email;
	$classes        = 'email-link property-link';
	if ( ! empty( $class ) ) {
		$classes .= ' ' . esc_attr( $class );
	}
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_emailus_click', rentfetch_get_property_tracking_context( $property_id ) );
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 email-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>';
	$contact_button = sprintf( '<a class="%s" href="%s"%s>%sEmail Us</a>', $classes, esc_html( $email_link ), $tracking_attrs, $svg );
	$email_button   = apply_filters( 'rentfetch_filter_property_contact_button', $contact_button, $property_id, $class );

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
		$allowed_html = array_merge( wp_kses_allowed_html( 'post' ), array(
			'svg' => array(
				'xmlns' => true,
				'fill' => true,
				'viewbox' => true,
				'stroke-width' => true,
				'stroke' => true,
				'class' => true,
			),
			'path' => array(
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'd' => true,
			),
		) );
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
 * @param string $class Optional additional CSS class.
 * @return string The property email link.
 */
function rentfetch_get_property_email_link( $property_id = null, $class = '' ) {
	$email = rentfetch_get_property_email( $property_id );
	$email_link = 'mailto:' . $email;
	$classes = 'email-link';
	if ( ! empty( $class ) ) {
		$classes .= ' ' . esc_attr( $class );
	}
	$email_button = sprintf( '<a class="%s" href="%s">%s</a>', $classes, esc_html( $email_link ), esc_html( $email ) );

	if ( $email ) {
		return apply_filters( 'rentfetch_filter_property_email_link', $email_button, $property_id, $class );
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
 * @param string $class Optional additional CSS class.
 * @return string The property email.
 */
function rentfetch_get_property_tour_button( $property_id = null, $class = '' ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return '';
		}
	} else {
		$post_id = get_the_ID();
	}
		
	$iframe    = get_post_meta( $post_id, 'tour', true );
	$embedlink = null;
	$tour_link_text = 'Video Tour';
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 tour-icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />
</svg>';
	
	// bail if we don't have anything to show.
	if ( ! $iframe ) {
		return;
	}
	
	wp_enqueue_style( 'rentfetch-glightbox-style' );
	wp_enqueue_script( 'rentfetch-glightbox-script' );
	wp_enqueue_script( 'rentfetch-glightbox-init' );

	// check against youtube - handle both iframe HTML and direct URLs
	$youtube_pattern = '/src="https:\/\/www\.youtube\.com\/embed\/([^?"]+)\?/';
	preg_match( $youtube_pattern, $iframe, $youtube_matches );
	
	// Also check for direct YouTube URLs
	if ( ! isset( $youtube_matches[1] ) ) {
		preg_match( '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $iframe, $youtube_matches );
	}

	// if it's youtube and it's a full iframe.
	if ( isset( $youtube_matches[1] ) ) {
		$video_id   = $youtube_matches[1];
		$oembedlink = 'https://www.youtube.com/watch?v=' . $video_id;
		$classes    = 'tour-link property-link tour-link-youtube';
		if ( ! empty( $class ) ) {
			$classes .= ' ' . esc_attr( $class );
		}
		$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_tour_click', rentfetch_get_property_tracking_context( $property_id, $post_id ) );
		$embedlink  = sprintf( '<a class="%s" data-gallery="post-%s" data-glightbox="type: video;" href="%s"%s>%s%s</a>', $classes, $post_id, $oembedlink, $tracking_attrs, $svg, $tour_link_text );
	}

	$matterport_pattern = '/src="([^"]*matterport[^"]*)"/i'; // Added "matterport" to the pattern.
	preg_match( $matterport_pattern, $iframe, $matterport_matches );
	
	// Also check for direct Matterport URLs
	if ( ! isset( $matterport_matches[1] ) && strpos( $iframe, 'matterport.com' ) !== false ) {
		$matterport_matches[1] = $iframe;
	}

	// if it's matterport and it's a full iframe.
	if ( isset( $matterport_matches[1] ) ) {
		$oembedlink = $matterport_matches[1];
		$classes    = 'tour-link property-link tour-link-matterport';
		if ( ! empty( $class ) ) {
			$classes .= ' ' . esc_attr( $class );
		}
		$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_tour_click', rentfetch_get_property_tracking_context( $property_id, $post_id ) );
		$embedlink  = sprintf( '<a class="%s" data-gallery="post-%s" href="%s"%s>%s%s</a>', $classes, $post_id, $oembedlink, $tracking_attrs, $svg, $tour_link_text );
	}

	// if it's anything else (like just an oembed, including an oembed for either matterport or youtube).
	if ( ! $embedlink ) {
		$oembedlink = $iframe;
		$classes    = 'tour-link property-link';
		if ( ! empty( $class ) ) {
			$classes .= ' ' . esc_attr( $class );
		}
		$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_tour_click', rentfetch_get_property_tracking_context( $property_id, $post_id ) );
		$embedlink  = sprintf( '<a class="%s" target="_blank" data-gallery="post-%s" href="%s"%s>%s%s</a>', $classes, $post_id, $oembedlink, $tracking_attrs, $svg, $tour_link_text );
	}
	
	return apply_filters( 'rentfetch_filter_property_tour_button', $embedlink, $property_id, $class );
}

/**
 * Echo the property email.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_tour_button( $property_id = null ) {
	if ( rentfetch_get_property_tour_button( $property_id ) ) {
		$allowed_html = array_merge( wp_kses_allowed_html( 'post' ), array(
			'svg' => array(
				'xmlns' => true,
				'fill' => true,
				'viewbox' => true,
				'stroke-width' => true,
				'stroke' => true,
				'class' => true,
			),
			'path' => array(
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'd' => true,
			),
		) );
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
 * @param string $class Optional additional CSS class.
 * @return string The property tour booking button.
 */
function rentfetch_get_property_tour_booking_button( $property_id = null, $class = '' ) {
	$url = rentfetch_get_property_tour_booking_url( $property_id );
	$target = rentfetch_get_link_target( $url );
	$classes = 'tour-booking-link property-link';
	if ( ! empty( $class ) ) {
		$classes .= ' ' . esc_attr( $class );
	}
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_scheduletour_click', rentfetch_get_property_tracking_context( $property_id ) );
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 tour-booking-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" /></svg>';
	$tour_booking_button = sprintf( '<a class="%s" href="%s" target="%s"%s>%sBook Tour</a>', $classes, esc_html( $url ), esc_attr( $target ), $tracking_attrs, $svg );

	if ( $url ) {
		return apply_filters( 'rentfetch_filter_property_tour_booking', $tour_booking_button, $property_id, $class );
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
		$allowed_html = array_merge( wp_kses_allowed_html( 'post' ), array(
			'svg' => array(
				'xmlns' => true,
				'fill' => true,
				'viewbox' => true,
				'stroke-width' => true,
				'stroke' => true,
				'class' => true,
			),
			'path' => array(
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'd' => true,
			),
		) );
		echo wp_kses( rentfetch_get_property_tour_booking_button( $property_id ), $allowed_html );
	}
}

// * PROPERTY OFFICE HOURS BUTTON.

/**
 * Get the property office hours button.
 *
 * @param string $property_id Optional property_id meta value.
 * @param string $class Optional additional CSS class.
 * @return string The property office hours button.
 */
function rentfetch_get_property_office_hours_button( $property_id = null, $class = '' ) {
	$office_hours = rentfetch_get_property_office_hours_array( $property_id );

	if ( empty( $office_hours ) ) {
		return '';
	}

	$classes = 'office-hours-link property-link';
	if ( ! empty( $class ) ) {
		$classes .= ' ' . esc_attr( $class );
	}
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 office-hours-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>';
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_officehours_click', rentfetch_get_property_tracking_context( $property_id ) );

	// Get office hours markup without heading and wrapper
	$days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
	$office_hours_content = '';
	foreach ( $days as $day ) {
		$office_hours_content .= '<div class="office-hours-day">';
			$office_hours_content .= '<span class="day-name">' . esc_html( ucfirst( $day ) . ':' ) . '</span> ';
			if ( isset( $office_hours[ $day ] ) && ! empty( $office_hours[ $day ]['start'] ) && ! empty( $office_hours[ $day ]['end'] ) ) {
				$start_time = date( 'ga', strtotime( $office_hours[ $day ]['start'] ) );
				$end_time = date( 'ga', strtotime( $office_hours[ $day ]['end'] ) );
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

	return apply_filters( 'rentfetch_filter_property_office_hours_button', $office_hours_button, $property_id, $class );
}/**
 * Echo the property office hours button.
 *
 * @param string $property_id Optional property_id meta value.
 * @return void.
 */
function rentfetch_property_office_hours_button( $property_id = null ) {
	$button = rentfetch_get_property_office_hours_button( $property_id );
	
	if ( $button ) {
		$allowed_html = array_merge( wp_kses_allowed_html( 'post' ), array(
			'svg' => array(
				'xmlns' => true,
				'fill' => true,
				'viewbox' => true,
				'stroke-width' => true,
				'stroke' => true,
				'class' => true,
			),
			'path' => array(
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'd' => true,
			),
			'details' => array(
				'class' => true,
				'open' => true,
			),
			'summary' => array(
				'class' => true,
				'data-rentfetch-event' => true,
				'data-rentfetch-property-id' => true,
				'data-rentfetch-property-name' => true,
				'data-rentfetch-property-city' => true,
			),
			'div' => array(
				'class' => true,
				'style' => true,
			),
			'span' => array(
				'class' => true,
			),
		) );
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
 * Get monthly required total fees for a property, with global fallback.
 *
 * @param int|null $property_post_id The property post ID.
 * @return float The monthly required total fees.
 */
function rentfetch_get_effective_monthly_required_total_fees_for_property( $property_post_id = null ) {
	$property_total = null;

	if ( $property_post_id ) {
		$property_raw = get_post_meta( $property_post_id, 'property_monthly_required_total_fees', true );
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
	$rent_range      = $floorplan_data['rentrange'] ?? null;

	$min_rent_values = rentfetch_get_normalized_property_rent_values( $floorplan_data['minimum_rent'] ?? array() );
	$max_rent_values = rentfetch_get_normalized_property_rent_values( $floorplan_data['maximum_rent'] ?? array() );

	$min_rent = ! empty( $min_rent_values ) ? min( $min_rent_values ) : null;
	$max_rent = ! empty( $max_rent_values ) ? max( $max_rent_values ) : null;

	// Fallback to parsing rentrange when API min/max arrays are not available.
	if ( null === $min_rent && ! empty( $rent_range ) ) {
		preg_match_all( '/\d[\d,]*(?:\.\d+)?/', (string) $rent_range, $matches );
		$range_numbers = array_map(
			function( $number ) {
				return (float) str_replace( ',', '', $number );
			},
			$matches[0] ?? array()
		);
		$range_numbers = rentfetch_get_normalized_property_rent_values( $range_numbers );
		if ( ! empty( $range_numbers ) ) {
			$min_rent = min( $range_numbers );
			$max_rent = max( $range_numbers );
		}
	}

	if ( null === $min_rent && null !== $max_rent ) {
		$min_rent = $max_rent;
	}
	if ( null === $max_rent && null !== $min_rent ) {
		$max_rent = $min_rent;
	}
	if ( null !== $min_rent && null !== $max_rent && $max_rent < $min_rent ) {
		$temp = $min_rent;
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

	$base_rent_display = rentfetch_format_property_rent_display( $min_rent, $max_rent, $pricing_display );

	if ( $monthly_required_fees > 0 ) {
		$including_fees_min_rent     = $min_rent + $monthly_required_fees;
		$including_fees_max_rent     = ( null !== $max_rent ? $max_rent : $min_rent ) + $monthly_required_fees;
		$including_fees_rent_display = rentfetch_format_property_rent_display( $including_fees_min_rent, $including_fees_max_rent, $pricing_display );

		$rent = sprintf(
			'<span class="rentfetch-property-rent-lines"><span class="rentfetch-property-rent-with-fees">%1$s/mo</span><span class="rentfetch-property-base-rent">%2$s base rent</span></span>',
			esc_html( $including_fees_rent_display ),
			esc_html( $base_rent_display )
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
	if ( ! $property_id ) {
		$property_id = sanitize_text_field( get_post_meta( get_the_ID(), 'property_id', true ) );
	}

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
 * Get property specials based on property-level meta fields (similar to floorplan specials).
 *
 * @param string $property_id Optional property_id meta value.
 * @return string|null The property specials text.
 */
function rentfetch_get_property_specials_from_meta( $property_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
		if ( ! $post_id ) {
			return null;
		}
	} else {
		$post_id = get_the_ID();
	}

	$has_specials = get_post_meta( $post_id, 'has_specials', true );
	$specials_override_text = get_post_meta( $post_id, 'specials_override_text', true );
	
	// Sanitize the override text to plain text to prevent HTML from being output
	$specials_override_text = sanitize_text_field( $specials_override_text );
	
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

//* PROPERTY TOUR

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

	$iframe    = get_post_meta( $post_id, 'tour', true );
	$embedlink = null;

	if ( $iframe ) {

		if ( $embed_direct ) {
			// Return the iframe directly - convert URLs to iframe HTML if needed
			if ( strpos( $iframe, '<iframe' ) === 0 ) {
				// Already iframe HTML, use as-is
				$embedlink = $iframe;
			} elseif ( preg_match( '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $iframe, $youtube_matches ) ) {
				// YouTube URL, convert to embed iframe
				$video_id = $youtube_matches[1];
				$embedlink = '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
			} elseif ( strpos( $iframe, 'matterport.com' ) !== false ) {
				// Matterport URL, convert to embed iframe
				$embedlink = '<iframe width="853" height="480" src="' . esc_url( $iframe ) . '" frameborder="0" allowfullscreen allow="xr-spatial-tracking"></iframe>';
			} else {
				// Fallback: assume it's already iframe HTML or wrap as iframe
				$embedlink = $iframe;
			}
		} else {
			// Return links for lightbox/modal (existing behavior)
			wp_enqueue_style( 'rentfetch-glightbox-style' );
			wp_enqueue_script( 'rentfetch-glightbox-script' );
			wp_enqueue_script( 'rentfetch-glightbox-init' );

			// check against youtube - handle both iframe HTML and direct URLs
			$youtube_pattern = '/src="https:\/\/www\.youtube\.com\/embed\/([^?"]+)\?/';
			preg_match( $youtube_pattern, $iframe, $youtube_matches );
			
			// Also check for direct YouTube URLs
			if ( ! isset( $youtube_matches[1] ) ) {
				preg_match( '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $iframe, $youtube_matches );
			}

			// if it's youtube and it's a full iframe.
			if ( isset( $youtube_matches[1] ) ) {
				$video_id   = $youtube_matches[1];
				$oembedlink = 'https://www.youtube.com/watch?v=' . $video_id;
				$embedlink  = sprintf( '<div class="tour-link-wrapper"><a class="tour-link tour-link-youtube" data-gallery="post-%s" data-glightbox="type: video;" href="%s"></a></div>', $post_id, $oembedlink );
			}

			$matterport_pattern = '/src="([^"]*matterport[^"]*)"/i'; // Added "matterport" to the pattern.
			preg_match( $matterport_pattern, $iframe, $matterport_matches );
			
			// Also check for direct Matterport URLs
			if ( ! isset( $matterport_matches[1] ) && strpos( $iframe, 'matterport.com' ) !== false ) {
				$matterport_matches[1] = $iframe;
			}

			// if it's matterport and it's a full iframe.
			if ( isset( $matterport_matches[1] ) ) {
				$oembedlink = $matterport_matches[1];
				$embedlink  = sprintf( '<div class="tour-link-wrapper"><a class="tour-link tour-link-matterport" data-gallery="post-%s" href="%s"></a></div>', $post_id, $oembedlink );
			}

			// if it's anything else (like just an oembed, including an oembed for either matterport or youtube).
			if ( ! $embedlink ) {
				$oembedlink = $iframe;
				$embedlink  = sprintf( '<div class="tour-link-wrapper"><a class="tour-link" target="_blank" data-gallery="post-%s" href="%s"></a></div>', $post_id, $oembedlink );
			}
		}
	}

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
 * Gets the property fees embed code.
 *
 * @param string|int|null $property_id_or_post_id Property ID meta value or Post ID.
 * @return string The property fees embed code.
 */
function rentfetch_get_property_fees_embed( $property_id_or_post_id = null ) {
	
	// Figure out the post ID to use for getting the fees.
	$post_id = null;
	
	if ( $property_id_or_post_id ) {
		if ( is_numeric( $property_id_or_post_id ) ) {
			$post_id = $property_id_or_post_id;
		} else {
			$post_id = rentfetch_get_post_id_from_property_id( $property_id_or_post_id );
		}
	} else {
		$post_id = get_the_ID();
	}

	$property_fees_markup = '';

	// If we have a valid post_id, try property-specific fees first
	if ( $post_id ) {
		$property_fees_data    = get_post_meta( $post_id, 'property_fees_data', true );
		$property_fees_csv_url = get_post_meta( $post_id, 'property_fees_csv_url', true );
		$property_fees_embed   = get_post_meta( $post_id, 'property_fees_embed', true );

		// Priority 1: Use property_fees_csv_url if available
		if ( ! empty( $property_fees_csv_url ) ) {
			$response = wp_remote_get( $property_fees_csv_url );
			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$csv_content = wp_remote_retrieve_body( $response );
				$fees_data = rentfetch_process_csv_content_to_fees_array( $csv_content );
				if ( ! empty( $fees_data ) ) {
					$property_fees_json   = wp_json_encode( $fees_data );
					$property_fees_markup = rentfetch_get_property_fees_markup( $property_fees_json );
				}
			}
		}

		// Priority 2: Use property_fees_data (this is the json) if it's a non-empty array.
		// This is also a fallback if the CSV URL exists but fails to fetch/parse.
		if ( empty( $property_fees_markup ) && ! empty( $property_fees_data ) && is_array( $property_fees_data ) ) {
			$property_fees_json   = wp_json_encode( $property_fees_data );
			$property_fees_markup = rentfetch_get_property_fees_markup( $property_fees_json );
		}

		// Priority 3: Fallback to property_fees_embed.
		if ( empty( $property_fees_markup ) && ! empty( $property_fees_embed ) ) {
			$property_fees_markup = $property_fees_embed;
		}
	}

	// If no property-specific fees or no post_id, try global fallbacks
	if ( empty( $property_fees_markup ) ) {
		$global_fees_csv_url  = get_option( 'rentfetch_options_global_property_fees_csv_url' );
		$global_fees_data     = get_option( 'rentfetch_options_global_property_fees_data' );
		$global_fees_embed    = get_option( 'rentfetch_options_global_property_fees_embed' );

		// Priority 1: Use global_fees_csv_url if available
		if ( ! empty( $global_fees_csv_url ) ) {
			$response = wp_remote_get( $global_fees_csv_url );
			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$csv_content = wp_remote_retrieve_body( $response );
				$fees_data = rentfetch_process_csv_content_to_fees_array( $csv_content );
				if ( ! empty( $fees_data ) ) {
					$global_fees_json     = wp_json_encode( $fees_data );
					$property_fees_markup = rentfetch_get_property_fees_markup( $global_fees_json );
				}
			}
		}

		// Priority 2: Use global_fees_data if it's a non-empty array.
		// This is also a fallback if the CSV URL exists but fails to fetch/parse.
		if ( empty( $property_fees_markup ) && ! empty( $global_fees_data ) && is_array( $global_fees_data ) ) {
			$global_fees_json     = wp_json_encode( $global_fees_data );
			$property_fees_markup = rentfetch_get_property_fees_markup( $global_fees_json );
		}

		// Priority 3: Fallback to global_fees_embed
		if ( empty( $property_fees_markup ) && ! empty( $global_fees_embed ) ) {
			$property_fees_markup = $global_fees_embed;
		}
		// If none, return empty
		if ( empty( $property_fees_markup ) ) {
			return '';
		}
	}

	// Add description text before the fees markup (filterable, accepts HTML)
	// The filtered text is run through the_content to auto-add paragraphs
	$fees_description = apply_filters(
		'rentfetch_property_fees_description',
		'Please note that prices shown are base rent. To help budget your monthly costs and make it easy to understand what your rent includes and what may be additional, we\'ve included the list of potential fees below.',
		$post_id
	);

	// Prepend description to markup if not empty, wrapped in a styled container
	if ( ! empty( $fees_description ) ) {
		$fees_description_html = apply_filters( 'the_content', $fees_description );
		$property_fees_markup  = '<div class="property-fees-description">' . $fees_description_html . '</div>' . $property_fees_markup;
	}

	return apply_filters( 'rentfetch_filter_property_fees_embed', $property_fees_markup, $post_id );
}

function rentfetch_get_property_fees_markup( $property_fees_json ) {
	
	// Start output buffering
	ob_start();
	
	// Decode the JSON
	$fees_data = json_decode( $property_fees_json, true );
	
	// If JSON is invalid or empty, return empty string
	if ( ! is_array( $fees_data ) || empty( $fees_data ) ) {
		return '';
	}

	// Check if any fee has longnotes (for tooltip functionality)
	$has_tooltip_content = false;
	foreach ( $fees_data as $fee ) {
		if ( ! empty( $fee['longnotes'] ) ) {
			$has_tooltip_content = true;
			break;
		}
	}
	
	// Enqueue tooltip script if we have content to display
	if ( $has_tooltip_content ) {
		wp_enqueue_script( 'rentfetch-property-fees-tooltip' );
	}
	// Extract unique categories
	$categories = array();
	foreach ( $fees_data as $fee ) {
		if ( ! empty( $fee['category'] ) ) {
			$categories[] = $fee['category'];
		}
	}
	$categories = array_unique( $categories );
	
	// If we have categories, group by category
	if ( ! empty( $categories ) ) {
		foreach ( $categories as $category ) {
			// Output category header
			echo '<h3>' . esc_html( $category ) . '</h3>';
			
			// Start table
			echo '<table class="property-fees-table">';
			
			// Get fees for this category
			$category_fees = array_filter( $fees_data, function( $fee ) use ( $category ) {
				return isset( $fee['category'] ) && $fee['category'] === $category;
			} );
			
			// Output table rows
			foreach ( $category_fees as $fee ) {
			$has_longnotes = ! empty( $fee['longnotes'] );
				echo '<tr>';
				echo '<td class="fee-description">';
				if ( $has_longnotes ) {
					// Apply the_content filter for consistent HTML output, then sanitize
					$longnotes_html = wp_kses_post( apply_filters( 'the_content', $fee['longnotes'] ) );
					echo '<span class="fee-description-with-tooltip" data-tooltip-content="' . esc_attr( $longnotes_html ) . '">';
					echo esc_html( $fee['description'] ?? '' );
					echo '<span class="fee-info-icon" aria-label="More information">?</span>';
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
			
			// End table
			echo '</table>';
		}
	} else {
		// No categories, output single table
		echo '<table class="property-fees-table">';
		
		foreach ( $fees_data as $fee ) {
			$has_longnotes = ! empty( $fee['longnotes'] );
			echo '<tr>';
			echo '<td class="fee-description">';
			if ( $has_longnotes ) {
				// Apply the_content filter for consistent HTML output, then sanitize
				$longnotes_html = wp_kses_post( apply_filters( 'the_content', $fee['longnotes'] ) );
				echo '<span class="fee-description-with-tooltip" data-tooltip-content="' . esc_attr( $longnotes_html ) . '">';
				echo esc_html( $fee['description'] ?? '' );
				echo '<span class="fee-info-icon" aria-label="More information">?</span>';
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
		
		echo '</table>';
	}
	
	// Return the buffered output
	return ob_get_clean();
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
			'description'  => sanitize_text_field( (string) ( $fee['description'] ?? '' ) ),
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
	$total = 0.0;

	foreach ( $contributors as $contributor ) {
		$total += (float) ( $contributor['applied_price'] ?? 0 );
	}

	return round( $total, 2 );
}

/**
 * Update stored monthly required total fees for a property from its fees CSV URL.
 *
 * @param int $property_post_id The property post ID.
 * @return bool True when a positive total is saved, false otherwise.
 */
function rentfetch_update_property_monthly_required_total_fees_from_csv( $property_post_id ) {
	$property_post_id = (int) $property_post_id;
	if ( $property_post_id <= 0 ) {
		return false;
	}

	$csv_url = trim( (string) get_post_meta( $property_post_id, 'property_fees_csv_url', true ) );

	// Requirement: if there's no CSV, don't save this meta.
	if ( '' === $csv_url ) {
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees' );
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees_last_checked' );
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees_rows' );
		return false;
	}

	$response = wp_remote_get(
		$csv_url,
		array(
			'timeout'   => 15,
			'sslverify' => false, // Allow self-signed certs for local development.
		)
	);

	// Record that we attempted a CSV check so we can enforce the ~12 hour cadence.
	update_post_meta( $property_post_id, 'property_monthly_required_total_fees_last_checked', time() );

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees' );
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees_rows' );
		return false;
	}

	$csv_content = wp_remote_retrieve_body( $response );
	$fees_data   = rentfetch_process_csv_content_to_fees_array( $csv_content );

	if ( empty( $fees_data ) ) {
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees' );
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees_rows' );
		return false;
	}

	$contributors = rentfetch_get_monthly_required_fee_contributors( $fees_data );
	$total = rentfetch_calculate_monthly_required_total_fees( $fees_data );

	// Requirement: don't save if total is zero (or missing/invalid).
	if ( $total <= 0 ) {
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
 * @return bool True when a positive total is saved, false otherwise.
 */
function rentfetch_update_global_monthly_required_total_fees_from_csv() {
	$csv_url = trim( (string) get_option( 'rentfetch_options_global_property_fees_csv_url', '' ) );

	// If there's no CSV, don't save this option.
	if ( '' === $csv_url ) {
		delete_option( 'rentfetch_options_global_monthly_required_total_fees' );
		delete_option( 'rentfetch_options_global_monthly_required_total_fees_last_checked' );
		delete_option( 'rentfetch_options_global_monthly_required_total_fees_rows' );
		return false;
	}

	$response = wp_remote_get(
		$csv_url,
		array(
			'timeout'   => 15,
			'sslverify' => false, // Allow self-signed certs for local development.
		)
	);

	// Record that we attempted a CSV check.
	update_option( 'rentfetch_options_global_monthly_required_total_fees_last_checked', time() );

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		delete_option( 'rentfetch_options_global_monthly_required_total_fees' );
		delete_option( 'rentfetch_options_global_monthly_required_total_fees_rows' );
		return false;
	}

	$csv_content = wp_remote_retrieve_body( $response );
	$fees_data   = rentfetch_process_csv_content_to_fees_array( $csv_content );

	if ( empty( $fees_data ) ) {
		delete_option( 'rentfetch_options_global_monthly_required_total_fees' );
		delete_option( 'rentfetch_options_global_monthly_required_total_fees_rows' );
		return false;
	}

	$contributors = rentfetch_get_monthly_required_fee_contributors( $fees_data );
	$total        = rentfetch_calculate_monthly_required_total_fees( $fees_data );

	// Don't save if total is zero (or missing/invalid).
	if ( $total <= 0 ) {
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
		// Requirement: if there's no CSV, don't save this meta.
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees' );
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees_last_checked' );
		delete_post_meta( $property_post_id, 'property_monthly_required_total_fees_rows' );
		return;
	}

	$last_checked = (int) get_post_meta( $property_post_id, 'property_monthly_required_total_fees_last_checked', true );
	if ( $last_checked > 0 && ( time() - $last_checked ) < ( 12 * HOUR_IN_SECONDS ) ) {
		return;
	}

	rentfetch_update_property_monthly_required_total_fees_from_csv( $property_post_id );
}
add_action( 'wp', 'rentfetch_maybe_refresh_property_monthly_required_total_fees', 999 );

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
		$output .= '<div class="office-hours-day">';
			$output .= '<span class="day-name">' . esc_html( ucfirst( $day ) . ':' ) . '</span> ';
			if ( isset( $office_hours[ $day ] ) && ! empty( $office_hours[ $day ]['start'] ) && ! empty( $office_hours[ $day ]['end'] ) ) {
				$start_time = date( 'ga', strtotime( $office_hours[ $day ]['start'] ) );
				$end_time = date( 'ga', strtotime( $office_hours[ $day ]['end'] ) );
				$output .= '<span class="day-hours">' . esc_html( $start_time . ' to ' . $end_time ) . '</span>';
			} else {
				$output .= '<span class="day-hours">Closed</span>';
			}
		$output .= '</div>';
	}
	$output .= '</div>';
	
	return apply_filters( 'rentfetch_filter_property_office_hours', $output, $property_id );
}

function rentfetch_process_csv_content_to_fees_array( $csv_content ) {
	$fees_data = array();
	if ( ! is_string( $csv_content ) || '' === trim( $csv_content ) ) {
		return $fees_data;
	}

	$handle = fopen( 'php://temp', 'r+' );
	if ( false === $handle ) {
		return $fees_data;
	}

	fwrite( $handle, $csv_content );
	rewind( $handle );

	$header = fgetcsv( $handle, 100000, ',', '"', '\\' );
	if ( false === $header || ! is_array( $header ) ) {
		fclose( $handle );
		return $fees_data;
	}

	// Normalize header: trim and lowercase
	$header = array_map( function( $col ) {
		$clean_col = str_replace( "\xEF\xBB\xBF", '', (string) $col ); // Strip UTF-8 BOM if present.
		return strtolower( trim( $clean_col ) );
	}, $header );
	
	$expected_columns = array( 'description', 'price', 'frequency', 'notes', 'category', 'longnotes' );
	
	// Find column indices - only require 'description' to be present
	$column_indices = array();
	foreach ( $expected_columns as $col ) {
		$index = array_search( $col, $header, true );
		$column_indices[ $col ] = ( $index !== false ) ? $index : -1;
	}
	
	// Must have at least 'description' column
	if ( $column_indices['description'] === -1 ) {
		fclose( $handle );
		return $fees_data;
	}
	
	while ( ( $data = fgetcsv( $handle, 100000, ',', '"', '\\' ) ) !== false ) {
		if ( ! is_array( $data ) ) {
			continue;
		}
		
		// Get value from column index, or empty string if column doesn't exist
		$get_value = function( $col ) use ( $column_indices, $data ) {
			$index = $column_indices[ $col ];
			if ( $index === -1 || ! isset( $data[ $index ] ) ) {
				return '';
			}
			return sanitize_text_field( $data[ $index ] );
		};
		
		// Skip rows where description is empty
		$description = $get_value( 'description' );
		if ( empty( $description ) ) {
			continue;
		}
		
		// Get longnotes value - allow HTML so use wp_kses_post instead of sanitize_text_field
		$longnotes_index = $column_indices['longnotes'];
		$longnotes_value = '';
		if ( $longnotes_index !== -1 && isset( $data[ $longnotes_index ] ) ) {
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

	fclose( $handle );
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
