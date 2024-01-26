<?php
/**
 * This file sets up the rentfetch_properties shortcode.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * This function sets up the rentfetch_properties shortcode.
 *
 * @param  array $atts  the shortcode attributes.
 * @return string       the shortcode output.
 */
function rentfetch_properties( $atts ) {
	$a = shortcode_atts(
		array(
			'foo' => 'something',
			'bar' => 'something else',
		),
		$atts
	);

	ob_start();

	$args = array(
		'post_type'      => 'properties',
		'posts_per_page' => '-1',
	);

	// Run the query.
	$custom_query = new WP_Query( $args );

	// Run the loop.
	if ( $custom_query->have_posts() ) {

		echo '<div class="properties-simple-grid">';

		while ( $custom_query->have_posts() ) {

			$custom_query->the_post();

			printf( '<div class="%s">', esc_attr( implode( ' ', get_post_class() ) ) );

				do_action( 'rentfetch_do_each_property_in_archive' );

			echo '</div>';

		}

		echo '</div>';

		// Restore postdata.
		wp_reset_postdata();

	}

	return ob_get_clean();
}
add_shortcode( 'properties', 'rentfetch_properties' );
add_shortcode( 'rentfetch_properties', 'rentfetch_properties' );
