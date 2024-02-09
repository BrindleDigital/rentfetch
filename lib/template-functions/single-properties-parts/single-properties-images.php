<?php
/**
 * The Images section of the single property page
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the images section
 *
 * @return void.
 */
function rentfetch_single_properties_parts_images() {

	$maybe_do_images = apply_filters( 'rentfetch_maybe_do_property_part_images', true );
	if ( true !== $maybe_do_images ) {
		return;
	}

	echo '<div id="images" class="single-properties-section no-padding full-width">';
		echo '<div class="wrap">';

		rentfetch_property_images_grid();

		echo '</div>';
	echo '</div>';
}

/**
 * Determine if the images section should be displayed
 *
 * @return bool.
 */
function rentfetch_maybe_property_part_images() {

	// bail if this section is not enabled.
	$property_components = get_option( 'rentfetch_options_single_property_components' );
	if ( ! is_array( $property_components ) || ! in_array( 'property_images', $property_components, true ) ) {
		return false;
	}

	// bail if there are no images.
	$images = rentfetch_get_property_images();
	if ( ! $images ) {
		return false;
	}

	return true;
}
add_filter( 'rentfetch_maybe_do_property_part_images', 'rentfetch_maybe_property_part_images' );

/**
 * Output the images section in the subnav if it should be displayed
 *
 * @return void.
 */
function rentfetch_single_properties_parts_subnav_images() {
	$maybe_do_images = apply_filters( 'rentfetch_maybe_do_property_part_images', true );
	if ( true === $maybe_do_images ) {
		$label = apply_filters( 'rentfetch_property_images_subnav_label', 'Images' );
		printf( '<li><a href="#images">%s</a></li>', esc_attr( $label ) );
	}
}
