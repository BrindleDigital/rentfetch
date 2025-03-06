<?php
/**
 * The More Properties section of the single property page
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the more properties section
 *
 * @return void.
 */
function rentfetch_single_properties_parts_more_properties() {

	$maybe_do_more_properties = apply_filters( 'rentfetch_maybe_do_property_part_more_properties', true );
	if ( true !== $maybe_do_more_properties ) {
		return;
	}

	global $post;

	

	$city = get_post_meta( get_the_ID(), 'city', true );

	$args = array(
		'post_type'      => 'properties',
		'posts_per_page' => 9,
		'meta_key'       => 'city',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'   => 'city',
				'value' => $city,
			),
		),
	);

	$args = apply_filters( 'rentfetch_filter_more_properties_query_args', $args );

	// The Query.
	$custom_query = new WP_Query( $args );

	// The Loop.
	if ( $custom_query->found_posts >= 2 ) {
		
		echo '<div id="moreproperties" class="single-properties-section">';
			echo '<div class="wrap">';

			echo '<h2>More Properties Nearby</h2>';

				wp_enqueue_script( 'blaze-script' );
				wp_enqueue_script( 'blaze-more-properties-init' );
				wp_enqueue_style( 'blaze-style' );

				echo '<div class="more-properties-slider blaze-slider">';
					echo '<div class="blaze-container">';
						echo '<div class="blaze-track-container">';
							echo '<div class="blaze-track">';

								while ( $custom_query->have_posts() ) {

									$custom_query->the_post();
									
									$classes_array = get_post_class();
									$classes_array = apply_filters( 'rentfetch_filter_properties_post_classes', $classes_array );
									$class = implode( ' ', $classes_array );

									printf( '<div class="%s">', esc_attr( $class ) );
										do_action( 'rentfetch_do_each_property_in_archive' );
									echo '</div>';

								}

							echo '</div>'; // .blaze-track.
						echo '</div>'; // .blaze-track-container.

						echo '<div class="blaze-buttons">';
							echo '<button class="blaze-prev"></button>';
							echo '<button class="blaze-next"></button>';
						echo '</div>';

					echo '</div>'; // .blaze-container.
				echo '</div>'; // .blaze-slider.
			echo '</div>'; // .wrap.
		echo '</div>'; // #moreproperties.

		// Restore postdata.
		wp_reset_postdata();

	} else {
		// silence is golden.
	}

	
}

/**
 * Maybe show the more properties section
 *
 * @return bool.
 */
function rentfetch_maybe_property_part_more_properties() {

	// bail if this section is not enabled.
	$property_components = get_option( 'rentfetch_options_single_property_components' );
	if ( ! is_array( $property_components ) || ! in_array( 'nearby_properties', $property_components, true ) ) {
		return false;
	}

	return true;
}
add_filter( 'rentfetch_maybe_do_property_part_more_properties', 'rentfetch_maybe_property_part_more_properties' );

/**
 * Display the more properties section in the subnav
 *
 * @return void.
 */
function rentfetch_single_properties_parts_subnav_more_properties() {
	$maybe_do_more_properties = apply_filters( 'rentfetch_maybe_do_property_part_more_properties', true );
	if ( true === $maybe_do_more_properties ) {
		$label = apply_filters( 'rentfetch_nearby_properties_subnav_label', 'Nearby Properties' );
		printf( '<li><a href="#moreproperties">%s</a></li>', esc_attr( $label ) );
	}
}
