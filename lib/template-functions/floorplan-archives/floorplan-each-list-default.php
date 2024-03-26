<?php
/**
 * Display each floorplan in the list
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Display each floorplan in the list
 *
 * @return void
 */
function rentfetch_floorplans_each_list_default() {

	$title           = rentfetch_get_floorplan_title();
	$beds            = rentfetch_get_floorplan_bedrooms();
	$baths           = rentfetch_get_floorplan_bathrooms();
	$square_feet     = rentfetch_get_floorplan_square_feet();
	$available_units = rentfetch_get_floorplan_available_units();
	$links           = rentfetch_get_floorplan_links();
	$pricing         = rentfetch_get_floorplan_pricing();
	$units_count     = rentfetch_get_floorplan_units_count_from_meta();
	$specials        = rentfetch_get_floorplan_specials();
	$tour            = rentfetch_get_floorplan_tour();

	echo '<div class="images-column">';
		do_action( 'rentfetch_do_floorplan_images' );

		edit_post_link();

		if ( $specials ) {
			printf( '<p class="specials">%s</p>', esc_html( $specials ) );
		}

		if ( $tour ) {
			echo wp_kses_post( $tour );
		}

	echo '</div>';
	echo '<div class="content-column">';

		if ( $title ) {
			printf( '<h4>%s</h4>', esc_html( $title ) );
		}

		echo '<div class="floorplan-attributes">';

			if ( $beds ) {
				printf( '<p class="beds">%s</p>', wp_kses_post( $beds ) );
			}

			if ( $baths ) {
				printf( '<p class="baths">%s</p>', wp_kses_post( $baths ) );
			}

			if ( $square_feet ) {
				printf( '<p class="square-feet">%s</p>', wp_kses_post( $square_feet ) );
			}

			if ( $pricing ) {
				printf( '<p class="pricing">%s</p>', wp_kses_post( $pricing ) );
			}

		echo '</div>'; // .floorplan-attributes.

		if ( $units_count > 0 ) {
			
			$rentfetch_options_floorplan_hide_number_of_units = get_option( 'rentfetch_options_floorplan_hide_number_of_units' );
			if ( '1' !== $rentfetch_options_floorplan_hide_number_of_units ) {
				printf( '<p class="availability">%s</p>', wp_kses_post( $available_units ) );
			}

			echo '<details>';
				echo '<summary class="rentfetch-button">View Availability <span class="dropdown"></span></summary>';

				// typically there will be two things hooked to this, a desktop <table> and a mobile <details>.
				do_action( 'rentfetch_floorplan_do_unit_table' );

			echo '</details>';
		}

	echo '</div>'; // .content-column
}
add_action( 'rentfetch_single_properties_do_floorplans_each', 'rentfetch_floorplans_each_list_default' );
