<?php
/**
 * Single floorplans template.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();

		$tracking_context = rentfetch_get_floorplan_tracking_context( get_the_ID() );

		echo '<div class="single-floorplans-wrap"';
		foreach ( array( 'property_id', 'property_name', 'property_city', 'floorplan_id', 'floorplan_name' ) as $tracking_key ) {
			if ( ! empty( $tracking_context[ $tracking_key ] ) ) {
				printf( ' data-rentfetch-%s="%s"', esc_attr( str_replace( '_', '-', $tracking_key ) ), esc_attr( $tracking_context[ $tracking_key ] ) );
			}
		}
		echo '>';

			do_action( 'rentfetch_do_single_floorplans_parts' );

		echo '</div>';
	}
} else {
	echo 'So sorry! Nothing found.';
}

get_footer();
