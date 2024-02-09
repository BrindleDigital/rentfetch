<?php
/**
 * Single floorplans template
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

		global $post;

		echo '<div class="single-floorplans-container-outer">';
			echo '<div class="single-floorplans-container-inner">';
				echo '<div class="current-floorplan-info">';

					echo '<div class="images-column">';
						do_action( 'rentfetch_do_floorplan_images' );
					echo '</div>';
					echo '<div class="content-column">';

						// vars.
						$floorplan_title = rentfetch_get_floorplan_title();
						$beds            = rentfetch_get_floorplan_bedrooms();
						$baths           = rentfetch_get_floorplan_bathrooms();
						$square_feet     = rentfetch_get_floorplan_square_feet();
						$available_units = rentfetch_get_floorplan_available_units();
						$links           = rentfetch_get_floorplan_links();
						$pricing         = rentfetch_get_floorplan_pricing();
						$units_count     = rentfetch_get_floorplan_units_count_from_meta();

						if ( $floorplan_title ) {
							printf( '<h1>%s</h1>', esc_html( $floorplan_title ) );
						}

						if ( $pricing ) {
							printf( '<p class="pricing">%s</p>', wp_kses_post( $pricing ) );
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

						echo '</div>';

						if ( $units_count > 0 ) {
							// printf( '<p class="availability">%s</p>', $available_units );

							// typically there will be two things hooked to this, a desktop <table> and a mobile <details>.
							do_action( 'rentfetch_floorplan_do_unit_table' );
						}

					echo '</div>'; // .content-column
				echo '</div>'; // .current-floorplan-info
			echo '</div>'; // .container-inner
		echo '</div>'; // .container-outer
		// echo '<div class="single-floorplans-container-outer">';
		// echo '<div class="single-floorplans-container-inner">';
		// echo '<h2>Take a look around</h2>';
		// echo '</div>'; // .container-inner
		// echo '</div>'; // .container-outer
		// echo '<div class="single-floorplans-container-outer">';
		// echo '<div class="single-floorplans-container-inner">';
		// echo '<h2>Similar floorplans</h2>';
		// echo '</div>'; // .container-inner
		// echo '</div>'; // .container-outer


	} // end while
} else {
	echo 'So sorry! Nothing found.';
}



get_footer();
