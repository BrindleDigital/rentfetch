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
			'taxonomy'       => null,
			'terms'          => null,
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

			$classes_array = get_post_class();
			$classes_array = apply_filters( 'rentfetch_filter_floorplans_post_classes', $classes_array );
			$classes       = implode( ' ', $classes_array );

			printf( '<div class="%s">', esc_attr( $classes ) );

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
 * Apply the $atts for taxonoy to the query args
 *
 * @param   array $args the query args.
 *
 * @return  array  the modified query args.
 */
function rentfetch_floorplans_simple_grid_query_args_taxonomy( $args ) {

	if ( isset( $args['taxonomy'] ) && isset( $args['terms'] ) ) {

		$tax   = $args['taxonomy'];
		$terms = $args['terms'];

		$terms_array = explode( ',', $terms );

		$tax_query = array(
			'taxonomy' => $tax,
			'field'    => 'slug',
			'terms'    => $terms_array,
		);

		$args['tax_query'][] = $tax_query;

	}

	return $args;
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_taxonomy', 10, 2 );

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
function rentfetch_floorplans_simple_grid_query_args_order( $floorplans_args ) {

	if ( isset( $floorplans_args['sort'] ) ) {

		$sort = $floorplans_args['sort'];

		// if it's beds...
		if ( 'beds' === $sort ) {
			$floorplans_args['orderby']  = 'meta_value_num';
			$floorplans_args['meta_key'] = 'beds'; // phpcs:ignore
			$floorplans_args['order']    = 'ASC';
		}

		// if it's baths.
		if ( 'baths' === $sort ) {
			$floorplans_args['orderby']  = 'meta_value_num';
			$floorplans_args['meta_key'] = 'baths'; // phpcs:ignore
			$floorplans_args['order']    = 'ASC';
		}

		// if it's available units.
		if ( 'availability' === $sort ) {
			$floorplans_args['orderby']  = 'meta_value_num';
			$floorplans_args['meta_key'] = 'available_units'; // phpcs:ignore
			$floorplans_args['order']    = 'DESC';
		}

		// if it's price low to high.
		if ( 'pricelow' === $sort ) {
			$floorplans_args['orderby']    = 'meta_value_num';
			$floorplans_args['meta_key']   = 'minimum_rent'; // phpcs:ignore
			$floorplans_args['order']      = 'ASC';
			$floorplans_args['meta_query'][] = array( // phpcs:ignore
				'key'     => 'minimum_rent',
				'value'   => 100,
				'type'    => 'numeric',
				'compare' => '>',
			);
		}

		// if it's price high to low.
		if ( 'pricehigh' === $sort ) {
			$floorplans_args['orderby']    = 'meta_value_num';
			$floorplans_args['meta_key']   = 'minimum_rent'; // phpcs:ignore
			$floorplans_args['order']      = 'DESC';
			$floorplans_args['meta_query'][] = array( // phpcs:ignore
				'key'     => 'minimum_rent',
				'value'   => 100,
				'type'    => 'numeric',
				'compare' => '>',
			);
		}
		
		// if it's alphabetical...
		if ( 'alphabetical' === $sort ) {
			$floorplans_args['orderby']  = 'title';
			$floorplans_args['order']    = 'ASC';
		}
		
		// if it's menu_order...
		if ( 'menu_order' === $sort ) {
			$floorplans_args['orderby']  = 'menu_order';
			$floorplans_args['order']    = 'ASC';
		}
	}

	// reset the sort attribute.
	$floorplans_args['sort'] = null;

	return $floorplans_args;
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_order', 10, 2 );
