<?php
/**
 * Property Component Shortcodes
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Shortcode to output property data
 *
 * @param array $atts Shortcode attributes.
 * @return string The output.
 */
function rentfetch_property_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'property_id' => null,
			'info'        => 'title',
			'before'      => '',
			'after'       => '',
			'class'       => '',
		),
		$atts,
		'rentfetch_property_info'
	);

	$property_id = $atts['property_id'];
	$info        = $atts['info'];
	$before      = $atts['before'];
	$after       = $atts['after'];
	$class       = $atts['class'];

	// If no property_id is passed, try to get it from context
	if ( ! $property_id ) {
		// First, check if we're on a single property page (get_the_ID() should work)
		$current_post_id = get_the_ID();
		if ( $current_post_id && get_post_type( $current_post_id ) === 'properties' ) {
			$property_id = get_post_meta( $current_post_id, 'property_id', true );
		}
		
		// If that didn't work, check if this is a single-property website
		if ( ! $property_id ) {
			$single_property_id = rentfetch_website_single_property_site_get_property_id();
			if ( $single_property_id ) {
				$property_id = get_post_meta( $single_property_id, 'property_id', true );
			}
		}
	}

	if ( ! $property_id ) {
		return '<!-- Property ID could not be determined -->';
	}

	$content = '';

	switch ( $info ) {
		case 'title':
			$content = rentfetch_get_property_title( $property_id );
			break;

		case 'address':
			$content = rentfetch_get_property_address( $property_id );
			break;

		case 'city':
			$content = rentfetch_get_property_city( $property_id );
			break;

		case 'state':
			$content = rentfetch_get_property_state( $property_id );
			break;

		case 'zipcode':
			$content = rentfetch_get_property_zipcode( $property_id );
			break;

		case 'location':
			$content = rentfetch_get_property_location( $property_id );
			break;

		case 'city_state':
			$content = rentfetch_get_property_city_state( $property_id );
			break;

		case 'phone':
			$content = rentfetch_get_property_phone( $property_id );
			break;

		case 'phone_link':
			$content = rentfetch_get_property_phone_button( $property_id, $class );
			break;

		case 'url':
			$content = rentfetch_get_property_url( $property_id );
			break;

		case 'tour_booking_url':
			$content = rentfetch_get_property_tour_booking_url( $property_id );
			break;

		case 'permalink':
			$content = rentfetch_get_property_permalink( $property_id );
			break;

		case 'website_link':
			$content = rentfetch_get_property_website_button( $property_id, $class );
			break;

		case 'tour_booking_link':
			$content = rentfetch_get_property_tour_booking_button( $property_id, $class );
			break;

		case 'contact_link':
			$content = rentfetch_get_property_contact_button( $property_id, $class );
			break;

		case 'email_link':
			$content = rentfetch_get_property_email_link( $property_id, $class );
			break;

		case 'email':
			$content = rentfetch_get_property_email( $property_id );
			break;

		case 'tour_link':
			$content = rentfetch_get_property_tour_button( $property_id, $class );
			break;

		case 'bedrooms':
			$content = rentfetch_get_property_bedrooms( $property_id );
			break;

		case 'bathrooms':
			$content = rentfetch_get_property_bathrooms( $property_id );
			break;

		case 'square_feet':
			$content = rentfetch_get_property_square_feet( $property_id );
			break;

		case 'pricing':
			$content = rentfetch_get_property_pricing( $property_id );
			break;

		case 'availability':
			$content = rentfetch_get_property_availability( $property_id );
			break;

		case 'specials':
			$content = rentfetch_get_property_specials_from_meta( $property_id );
			break;

		case 'description':
			$content = rentfetch_get_property_description( $property_id );
			break;

		case 'tour_embed':
			$content = rentfetch_get_property_tour( $property_id, true );
			break;

		case 'google_link':
			$content = rentfetch_get_property_location_button( $property_id, $class );
			break;

		case 'fees_embed':
			$content = rentfetch_get_property_fees_embed( $property_id );
			break;

		case 'office_hours':
			$content = rentfetch_get_property_office_hours( $property_id, false );
			break;

		default:
			return '<!-- Unknown output type: ' . esc_html( $info ) . ' -->';
	}

	// Only output before/after if we have content
	if ( ! empty( $content ) ) {
		return $before . $content . $after;
	}

	return '';
}
add_shortcode( 'rentfetch_property_info', 'rentfetch_property_shortcode' );
