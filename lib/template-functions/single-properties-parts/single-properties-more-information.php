<?php
/**
 * The more information section of the single property page
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the more information section
 *
 * @return void.
 */
function rentfetch_single_properties_parts_more_information() {
	
	$content_area = get_post_meta( get_the_ID(), 'content_area', true );

	// bail if there is no content.
	if ( !$content_area ) {
		return;
	}

	echo '<div id="more-information" class="single-properties-section">';
		echo '<div class="wrap">';
			echo apply_filters( 'the_content', $content_area );
		echo '</div>'; // .wrap.
	echo '</div>'; // #more-information.
}

/**
 * Output the more information section in the subnav
 *
 * @return void.
 */
function rentfetch_single_properties_parts_subnav_more_information() {
	
	$content_area = get_post_meta( get_the_ID(), 'content_area', true );

	if ( $content_area ) {
		$label = apply_filters( 'rentfetch_more_information_display_subnav_label', 'More Information' );
		printf( '<li><a href="#more-information">%s</a></li>', esc_attr( $label ) );
	}
}
