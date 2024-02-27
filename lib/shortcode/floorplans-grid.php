<?php
/**
 * Floorplans grid
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the default layout for the floorplans grid
 *
 * @param  array $atts  the attributes passed to the shortcode.
 * @return string       the markup for the floorplans grid.
 */
function rentfetch_floorplans( $atts ) {
	$args = shortcode_atts(
		array(
			'property_id'    => null,
			'beds'           => null,
			'sort'           => null,
			'posts_per_page' => '-1',
			'post_type'      => 'floorplans',
		),
		$atts
	);

	ob_start();

	$args = apply_filters( 'rentfetch_floorplans_simple_grid_query_args', $args );

	// The Query.
	$custom_query = new WP_Query( $args );

	// The Loop.
	if ( $custom_query->have_posts() ) {

		echo '<div class="floorplans-simple-grid">';

		while ( $custom_query->have_posts() ) {

			$custom_query->the_post();

			printf( '<div class="%s">', esc_attr( implode( ' ', get_post_class() ) ) );

				do_action( 'rentfetch_do_each_floorplan_in_archive' );

			echo '</div>';

		}

		echo '</div>';

		// Restore postdata.
		wp_reset_postdata();

	}

	return ob_get_clean();
}
add_shortcode( 'rentfetch_floorplans', 'rentfetch_floorplans' );

/**
 * Apply the $atts for property_id to the query args
 *
 * @param   array $args the query args.
 *
 * @return array  the modified query args.
 */
function rentfetch_floorplans_simple_grid_query_args_property_id( $args ) {

	if ( isset( $args['property_id'] ) ) {

		$array_property_ids = explode( ',', $args['property_id'] );

		$args['meta_query'][] = array(
			'key'   => 'property_id',
			'value' => $array_property_ids,
		);

		$args['property_id'] = null;

	}

	return $args;
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_property_id', 10, 2 );

/**
 * Apply the $atts for beds to the query args
 *
 * @param   array $args the query args.
 *
 * @return array  the modified query args.
 */
function rentfetch_floorplans_simple_grid_query_args_beds( $args ) {

	if ( isset( $args['beds'] ) ) {

		$array_beds = explode( ',', $args['beds'] );

		// make the values of $array_beds integers.
		$array_beds = array_map( 'intval', $array_beds );

		$args['meta_query'][] = array(
			'key'   => 'beds',
			'value' => $array_beds,
		);

	}

	$args['beds'] = null;

	return $args;
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_beds', 10, 2 );

/**
 * Apply the $atts for sort to the query args
 *
 * @param   array $args arguments.
 *
 * @return array  the modified query args.
 */
function rentfetch_floorplans_simple_grid_query_args_order( $args ) {

	if ( isset( $args['sort'] ) ) {

		$sort = $args['sort'];

		// if it's beds.
		if ( null === $sort || 'beds' === $sort ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = 'beds';
			$args['order']    = 'ASC';
		}

		// if it's baths.
		if ( 'baths' === $sort ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = 'baths';
			$args['order']    = 'ASC';
		}

		// if it's available units.
		if ( 'availability' === $sort ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = 'available_units';
			$args['order']    = 'DESC';
		}
	}

	// reset the sort attribute.
	$args['sort'] = null;

	return $args;
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_order', 10, 2 );
