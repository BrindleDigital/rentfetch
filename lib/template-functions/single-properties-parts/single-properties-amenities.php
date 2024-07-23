<?php
/**
 * The Amenities section of the single property page
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the amenities section
 *
 * @return void.
 */
function rentfetch_single_properties_parts_amenities() {

	$maybe_do_amenities = apply_filters( 'rentfetch_maybe_do_property_part_amenities', true );
	if ( true !== $maybe_do_amenities ) {
		return;
	}

	echo '<div id="amenities" class="single-properties-section">';
		echo '<div class="wrap">';

			echo '<h2>Amenities</h2>';

			$terms        = get_the_terms( get_the_ID(), 'amenities' );
			$count        = count( $terms );
			$even         = ( 0 === $count % 2 ) ? true : false;
			$number_class = ( $even ) ? 'even' : 'odd';

			printf( '<ul class="amenities %s">', esc_attr( $number_class ) );

				foreach ( $terms as $term ) {
					printf( '<li>%s</li>', esc_attr( $term->name ) );
				}

			echo '</ul>';
		echo '</div>'; // .wrap.
	echo '</div>'; // #amenities.
}

/**
 * Determine if the amenities section should be displayed
 *
 * @return bool.
 */
function rentfetch_maybe_property_part_amenities() {

	// bail if this section is not enabled.
	$property_components = get_option( 'rentfetch_options_single_property_components' );
	if ( ! is_array( $property_components ) || ! in_array( 'amenities_display', $property_components, true ) ) {
		return false;
	}

	$terms = get_the_terms( get_the_ID(), 'amenities' );
	if ( ! $terms ) {
		return false;
	}

	return true;
}
add_filter( 'rentfetch_maybe_do_property_part_amenities', 'rentfetch_maybe_property_part_amenities' );

/**
 * Output the amenities section in the subnav
 *
 * @return void.
 */
function rentfetch_single_properties_parts_subnav_amenities() {

	$maybe_do_amenities = apply_filters( 'rentfetch_maybe_do_property_part_amenities', true );

	if ( true === $maybe_do_amenities ) {
		$label = apply_filters( 'rentfetch_amenities_display_subnav_label', 'Amenities' );
		printf( '<li><a href="#amenities">%s</a></li>', esc_attr( $label ) );
	}
}
