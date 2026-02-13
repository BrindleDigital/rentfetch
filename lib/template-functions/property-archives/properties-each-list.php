<?php
/**
 * Display each property in the list
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Do the mrkup for each property in the list
 *
 * @return void.
 */
function rentfetch_properties_each_list() {

	$title             = rentfetch_get_property_title();
	$property_location = rentfetch_get_property_location();
	$bedrooms          = rentfetch_get_property_bedrooms();
	$bathrooms         = rentfetch_get_property_bathrooms();
	$square_feet       = rentfetch_get_property_square_feet();
	$rent              = rentfetch_get_property_pricing();
	$availability      = rentfetch_get_property_availability();
	$specials          = rentfetch_get_property_specials_from_meta();
	$tour              = rentfetch_get_property_tour();
	$allowed_tags      = array(
		'p'    => array(),
		'span' => array(
			'style' => array(),
		),
	);

	$permalink = rentfetch_get_property_permalink();
	$permalink_target = rentfetch_get_link_target( $permalink );

	if ( $permalink ) {
		printf( '<a class="overlay" href="%s" target="%s"></a>', esc_url( $permalink ), esc_attr( $permalink_target ) );
	}

	do_action( 'rentfetch_do_property_images' );

	if ( $specials ) {
		printf( '<p class="specials">%s</p>', esc_html( $specials ) );
	}
	
	if ( $tour ) {
		echo wp_kses_post( $tour );
	}

	edit_post_link();

	echo '<div class="property-details">';

		if ( $title ) {
			printf( '<h3>%s</h3>', esc_html( $title ) );
		}

		if ( $property_location ) {
			printf( '<p class="property-location">%s</p>', esc_html( $property_location ) );
		}

		echo '<div class="property-attributes">';

			if ( $bedrooms ) {
				printf( '<p class="bedsrange">%s</p>', wp_kses( $bedrooms, $allowed_tags ) );
			}

			if ( $bathrooms ) {
				printf( '<p class="bathsrange">%s</p>', wp_kses( $bathrooms, $allowed_tags ) );
			}

			if ( $square_feet ) {
				printf( '<p class="square-feet">%s</p>', wp_kses( $square_feet, $allowed_tags ) );
			}

		echo '</div>'; // .property-attributes.

		if ( $rent || $availability ) {

			echo '<div class="property-availability">';

				printf( '<p class="rent">%s</p>', wp_kses_post( $rent ) );

				$rentfetch_options_properties_hide_number_of_units = get_option( 'rentfetch_options_properties_hide_number_of_units' );
				if ( '1' !== $rentfetch_options_properties_hide_number_of_units ) {
					printf( '<p class="availability">%s</p>', esc_html( $availability ) );
				}

			echo '</div>'; // .property-availability.

		}

	echo '</div>'; // .property-details.
}
add_action( 'rentfetch_do_properties_each_list', 'rentfetch_properties_each_list' );
add_action( 'rentfetch_do_single_properties_each_property', 'rentfetch_properties_each_list' );
