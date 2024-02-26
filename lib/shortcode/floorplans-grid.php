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
	$atts = shortcode_atts(
		array(
			'property_id' => null,
			'beds'        => null,
			'sort'        => null,
			'posts_per_page' => '-1',
		),
		$atts
	);

	ob_start();

	$args = array(
		'post_type'      => 'floorplans',
	);

	$args = apply_filters( 'rentfetch_floorplans_simple_grid_query_args', $args, $atts );

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
 * @param   array $atts the shortcode attributes.
 *
 * @return array  the modified query args.
 */
function rentfetch_floorplans_simple_grid_query_args_property_id( $args, $atts ) {

	if ( isset( $atts['property_id'] ) ) {

		$array_property_ids = explode( ',', $atts['property_id'] );

		$args['meta_query'][] = array(
			'key'   => 'property_id',
			'value' => $array_property_ids,
		);

	}

	return $args;
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_property_id', 10, 2 );

/**
 * Apply the $atts for beds to the query args
 *
 * @param   array $args the query args.
 * @param   array $atts the shortcode attributes.
 *
 * @return array  the modified query args.
 */
function rentfetch_floorplans_simple_grid_query_args_beds( $args, $atts ) {

	if ( isset( $atts['beds'] ) ) {

		$array_beds = explode( ',', $atts['beds'] );

		$args['meta_query'][] = array(
			'key'   => 'beds',
			'value' => $array_beds,
		);

	}

	return $args;
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_beds', 10, 2 );

/**
 * Apply the $atts for sort to the query args
 *
 * @param   array $floorplans_args the query args.
 * @param   array $atts the shortcode attributes.
 *
 * @return array  the modified query args.
 */
function rentfetch_floorplans_simple_grid_query_args_order( $floorplans_args, $atts ) {

	if ( isset( $atts['sort'] ) ) {

		$sort = $atts['sort'];

		// if it's beds.
		if ( 'sort' === $sort ) {
			$floorplans_args['orderby']  = 'meta_value_num';
			$floorplans_args['meta_key'] = 'beds';
			$floorplans_args['order']    = 'ASC';
		}

		// if it's baths.
		if ( 'baths' === $sort ) {
			$floorplans_args['orderby']  = 'meta_value_num';
			$floorplans_args['meta_key'] = 'baths';
			$floorplans_args['order']    = 'ASC';
		}

		// if it's available units.
		if ( 'availability' === $sort ) {
			$floorplans_args['orderby']  = 'meta_value_num';
			$floorplans_args['meta_key'] = 'available_units';
			$floorplans_args['order']    = 'DESC';
		}
	} else {
		$floorplans_args['orderby']  = 'meta_value_num';
		$floorplans_args['meta_key'] = 'beds';
		$floorplans_args['order']    = 'ASC';
	}

	return $floorplans_args;
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_order', 10, 2 );


function rentfetch_floorplans_simple_grid_query_args_postsperpage( $floorplans_args, $atts ) {

	if ( isset( $atts['posts_per_page'] ) ) {
		$floorplans_args['posts_per_page'] = (int) $atts['posts_per_page'];
	}	

	return $floorplans_args;
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_postsperpage', 10, 2 );
