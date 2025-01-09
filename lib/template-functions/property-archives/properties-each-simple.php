<?php
/**
 * Display the simple version of each property
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Do the markup for each property in the archive
 *
 * @return void.
 */
function rentfetch_each_property_in_archive_simple() {

	$title             = rentfetch_get_property_title();
	$property_location = rentfetch_get_property_location();

	$permalink        = rentfetch_get_property_permalink();
	$permalink_target = rentfetch_get_link_target( $permalink );

	if ( $permalink ) {
		printf( '<a class="overlay" href="%s" target="%s"></a>', esc_url( $permalink ), esc_attr( $permalink_target ) );
	}

	do_action( 'rentfetch_do_property_images' );
	
	edit_post_link();

	echo '<div class="property-content">';

	if ( $title ) {
		printf( '<h3>%s</h3>', esc_html( $title ) );
	}

	if ( $property_location ) {
		printf( '<p class="property-location">%s</p>', esc_html( $property_location ) );
	}

	echo '</div>';
}
add_action( 'rentfetch_do_each_property_in_archive', 'rentfetch_each_property_in_archive_simple' );
