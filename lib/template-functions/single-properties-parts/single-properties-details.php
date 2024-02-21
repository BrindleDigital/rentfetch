<?php
/**
 * The Details section of the single property page
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the details section
 *
 * @return void.
 */
function rentfetch_single_properties_parts_details() {

	$maybe_do_details = apply_filters( 'rentfetch_maybe_do_property_part_details', true );
	if ( true !== $maybe_do_details ) {
		return;
	}

	echo '<div id="details" class="single-properties-section">';
		echo '<div class="wrap">';

			$title                = rentfetch_get_property_title();
			$location             = rentfetch_get_property_location();
			$property_description = rentfetch_get_property_description();
			$property_rent        = rentfetch_get_property_rent();
			$beds                 = rentfetch_get_property_bedrooms();
			$sqrft                = rentfetch_get_property_square_feet();

			echo '<div class="property-details-header">';
				echo '<div class="property-details-basic-info">';

					if ( $title ) {
						printf( '<h1 class="title">%s</h1>', esc_html( $title ) );
					}

					if ( $location ) {
						printf( '<p class="location">%s</p>', esc_html( $location ) );
					}

				echo '</div>';
				echo '<div class="property-details-buttons">';

				echo '</div>';
			echo '</div>'; // .property-details-header
			echo '<div class="property-details-body">';
				echo '<div class="property-links">';
				
					do_action( 'rentfetch_do_single_property_links' );

				echo '</div>'; // .property-links

				echo '<div class="property-basic-info">';
					echo '<div class="property-stats">';

						if ( $property_rent ) {
							printf( '<p class="rent">%s</p>', wp_kses_post( $property_rent ) );
						}

						if ( $beds ) {
							printf( '<p class="beds">%s</p>', wp_kses_post( $beds ) );
						}

						if ( $sqrft ) {
							printf( '<p class="sqrft">%s</p>', wp_kses_post( $sqrft ) );
						}

					echo '</div>'; // .property-stats.

					if ( $property_description ) {
						printf( '<div class="description">%s</div>', wp_kses_post( $property_description ) );
					}

				echo '</div>'; // .property-basic-info.
			echo '</div>'; // .property-details-body.

		echo '</div>'; // .wrap.
	echo '</div>'; // #details.
}

/**
 * Maybe output the details section
 *
 * @return bool the answer to the question: should we output the details section?
 */
function rentfetch_maybe_property_part_details() {

	// bail if this section is not enabled.
	$property_components = get_option( 'rentfetch_options_single_property_components' );
	if ( ! is_array( $property_components ) || ! in_array( 'property_details', $property_components, true ) ) {
		return false;
	}

	return true;
}
add_filter( 'rentfetch_maybe_do_property_part_details', 'rentfetch_maybe_property_part_details' );

/**
 * Output the details section in the subnav
 *
 * @return void.
 */
function rentfetch_single_properties_parts_subnav_details() {
	$maybe_do_details = apply_filters( 'rentfetch_maybe_do_property_part_details', true );
	if ( true === $maybe_do_details ) {
		$label = apply_filters( 'rentfetch_property_details_subnav_label', 'Details' );
		printf( '<li><a href="#details">%s</a></li>', esc_attr( $label ) );
	}
}
