<?php
/**
 * The Subnav section of the single property page
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the wrapper for the subnav
 *
 * @return void.
 */
function rentfetch_single_properties_parts_subnav() {

	$maybe_do_subnav = apply_filters( 'rentfetch_maybe_do_property_part_subnav', true );
	if ( true !== $maybe_do_subnav ) {
		return;
	}

	wp_enqueue_script( 'properties-single-collapse-subnav' );

	echo '<div id="subnav" class="single-properties-section no-padding">';
		echo '<div class="wrap">';

			echo '<a class="toggle-subnav" href="#">Quick links <span class="dashicons dashicons-arrow-down-alt2"></span></a>';

			echo '<ul class="subnav">';

				do_action( 'rentfetch_do_single_properties_subnav_parts' );

			echo '</ul>'; // .subnav.
		echo '</div>'; // .wrap.
	echo '</div>'; // #subnav.
}
