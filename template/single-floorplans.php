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
		
		// vars.
		$specials        = rentfetch_get_floorplan_specials();
		$floorplan_title = rentfetch_get_floorplan_title();
		$beds            = rentfetch_get_floorplan_bedrooms();
		$baths           = rentfetch_get_floorplan_bathrooms();
		$square_feet     = rentfetch_get_floorplan_square_feet();
		$available_units = rentfetch_get_floorplan_available_units();
		$links           = rentfetch_get_floorplan_links();
		$pricing         = rentfetch_get_floorplan_pricing();
		$units_count     = rentfetch_get_floorplan_units_count_from_cpt();
		$description     = rentfetch_get_floorplan_description();
		$tracking_context = rentfetch_get_floorplan_tracking_context( get_the_ID() );
		$tracking_context_attrs = rentfetch_get_tracking_context_attributes( $tracking_context );

		printf( '<div class="single-floorplans-container-outer container-current-floorplan-info"%s>', $tracking_context_attrs );
			echo '<div class="single-floorplans-container-inner">';
				echo '<div class="current-floorplan-info">';

					echo '<div class="images-column">';
						do_action( 'rentfetch_do_floorplan_images' );
					echo '</div>';
					echo '<div class="content-column">';
						
						if ( $specials ) {
							printf( '<p class="specials">%s</p>', esc_html( $specials ) );
						}

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

						echo '<div class="floorplan-buttons">';
							do_action( 'rentfetch_do_floorplan_buttons' );
						echo '</div>';

						if ( $description ) {
							printf( '<div class="floorplan-description">%s</div>', wp_kses_post( $description ) );
						}

					echo '</div>'; // .content-column
				echo '</div>'; // .current-floorplan-info
			echo '</div>'; // .container-inner
		echo '</div>'; // .container-outer

		// if there are available units, show them.
		if ( $units_count > 0 ) {
			echo '<div class="single-floorplans-container-outer container-units">';
				echo '<div class="single-floorplans-container-inner">';
					echo '<div class="units">';

						echo wp_kses_post( apply_filters( 'rentfetch_single_floorplan_units_headline', '<h2>Units</h2>' ) );

						// typically there will be two things hooked to this, a desktop <table> and a mobile <details>.
						do_action( 'rentfetch_floorplan_do_unit_table' );

					echo '</div>'; // .units.
				echo '</div>'; // .container-inner.
			echo '</div>'; // .container-outer.
		}

		$embed = rentfetch_get_property_fee_embed_from_floorplan_id( get_the_ID() );
		
		if ( $embed ) {
			echo '<div class="single-floorplans-container-outer container-property-fees">';
				echo '<div class="single-floorplans-container-inner">';
					echo '<div class="property-fees">';

						echo wp_kses_post( apply_filters( 'rentfetch_single_floorplan_property_fees_headline', '<h2>Property Fees</h2>' ) );
						echo $embed;

					echo '</div>'; // .property-fees
				echo '</div>'; // .container-inner
			echo '</div>'; // .container-outer
		}

		$iframe = rentfetch_get_floorplan_tour_embed();

		if ( $iframe ) {
			echo '<div class="single-floorplans-container-outer container-tour">';
				echo '<div class="single-floorplans-container-inner">';
					echo '<div class="tour">';

					echo wp_kses_post( apply_filters( 'rentfetch_single_floorplan_tour_headline', '<h2>Take a look around</h2>' ) );
					rentfetch_floorplan_tour_embed();

					echo '</div>'; // .tour
				echo '</div>'; // .container-inner
			echo '</div>'; // .container-outer
		}

		// do a query for similar floorplans (which share a property_id and the number of beds).
		$similar_floorplans = rentfetch_get_similar_floorplans();

		if ( $similar_floorplans ) {

			// set a flag to tell the floorplan-images.php template to not use a slider.
			global $floorplan_images_use_slider;
			$floorplan_images_use_slider = false;

			echo '<div class="single-floorplans-container-outer container-similar-floorplans">';
				echo '<div class="single-floorplans-container-inner">';
					echo '<div class="similar-floorplans">';

						echo wp_kses_post( apply_filters( 'rentfetch_single_floorplan_more_floorplans_headline', '<h2>Similar Floor Plans</h2>' ) );
						rentfetch_similar_floorplans();

					echo '</div>'; // .similar-floorplans
				echo '</div>'; // .container-inner
			echo '</div>'; // .container-outer

			$floorplan_images_use_slider = null;
		}
	} // end while
} else {
	echo 'So sorry! Nothing found.';
}

get_footer();
