<?php
/**
 * The Floorplans section of the single property page
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the floorplans section
 *
 * @return void.
 */
function rentfetch_single_properties_parts_floorplans() {

	$maybe_do_floorplans = apply_filters( 'rentfetch_maybe_do_property_part_floorplans', true );
	if ( true !== $maybe_do_floorplans ) {
		return;
	}

	echo '<div id="floorplans" class="single-properties-section">';
		echo '<div class="wrap">';

			echo '<h2>Floorplans</h2>';

			global $post;

			$id          = esc_attr( get_the_ID() );
			$property_id = esc_attr( get_post_meta( $id, 'property_id', true ) );

			// get the possible values for the beds.
			$beds = rentfetch_get_meta_values( 'beds', 'floorplans' );
			$beds = array_unique( $beds );
			asort( $beds );

			// loop through each of the possible values, so that we can do markup around that.
			foreach ( $beds as $bed ) {

				$args = array(
					'post_type'      => 'floorplans',
					'posts_per_page' => -1,
					'orderby'        => 'meta_value_num',
					'meta_key'       => 'beds',
					'order'          => 'ASC',
					'meta_query'     => array(
						array(
							'key'   => 'property_id',
							'value' => $property_id,
						),
						array(
							'key'   => 'beds',
							'value' => $bed,
						),
					),
				);

				$floorplans_query = new WP_Query( $args );

				if ( $floorplans_query->have_posts() ) {

					echo '<div class="floorplan-group">';

						echo '<h3>';
							echo wp_kses_post( apply_filters( 'rentfetch_get_bedroom_number_label', $bed ) );
						echo '</h3>';
						echo '<div class="floorplans-in-archive">';

							while ( $floorplans_query->have_posts() ) {

								$floorplans_query->the_post();

								$classes_array = get_post_class();
								$classes_array = apply_filters( 'rentfetch_filter_floorplans_post_classes', $classes_array );
								$classes = implode( ' ', $classes_array );

								printf( '<div class="%s">', esc_attr( $classes ) );

									do_action( 'rentfetch_single_properties_do_floorplans_each' );

								echo '</div>'; // post_class.

							}

						echo '</div>'; // .floorplans-in-archive.

					echo '</div>'; // .floorplan-group.

					wp_reset_postdata();
				}
			}

		echo '</div>'; // .wrap.
	echo '</div>'; // #floorplans.
}

/**
 * Decide whether to output the floorplans section
 */
function rentfetch_maybe_property_part_floorplans() {

	// bail if this section is not enabled.
	$property_components = get_option( 'rentfetch_options_single_property_components' );
	if ( ! is_array( $property_components ) || ! in_array( 'floorplans_display', $property_components, true ) ) {
		return false;
	}

	// bail if this property doesn't have any floorplans.
	$floorplans = get_posts(
		array(
			'post_type'      => 'floorplans',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'property_id',
					'value'   => get_post_meta( get_the_ID(), 'property_id', true ),
					'compare' => '=',
				),
			),
		)
	);

	if ( ! $floorplans ) {
		return false;
	}

	// bail if this property doesn't have an ID.
	if ( ! get_post_meta( get_the_ID(), 'property_id', true ) ) {
		return false;
	}

	return true;
}
add_filter( 'rentfetch_maybe_do_property_part_floorplans', 'rentfetch_maybe_property_part_floorplans' );

/**
 * Output the floorplans section in the subnav if it should be displayed 
 *
 * @return void.
 */
function rentfetch_single_properties_parts_subnav_floorplans() {
	$maybe_do_floorplans = apply_filters( 'rentfetch_maybe_do_property_part_floorplans', true );
	if ( true === $maybe_do_floorplans ) {
		$label = apply_filters( 'rentfetch_floorplans_display_subnav_label', 'Floorplans' );
		printf( '<li><a href="#floorplans">%s</a></li>', esc_attr( $label ) );
	}
}
