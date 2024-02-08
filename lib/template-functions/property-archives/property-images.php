<?php
/**
 * Property images
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the property images
 *
 * @return void.
 */
function rentfetch_property_images() {

	// get the single image.
	rentfetch_property_single_image();

	// TODO - add an option for a slider or other layout; this function would disambiguate for which one is being used.
}
add_action( 'rentfetch_do_property_images', 'rentfetch_property_images' );

/**
 * Output the single property image
 *
 * @return void.
 */
function rentfetch_property_single_image() {
	$images = rentfetch_get_property_images();

	echo '<div class="property-single-image-wrap">';
		printf( '<img class="property-single-image" src="%s" loading="lazy" />', esc_url( $images[0]['url'] ) );
	echo '</div>';
}
