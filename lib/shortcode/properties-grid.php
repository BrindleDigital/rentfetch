<?php
/**
 * Properties grid
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
		
	$args = shortcode_atts(
		array(
			'post_type'      => 'properties',
			'posts_per_page' => -1,
			'propertyids'    => null,
			'city'           => null,
		),
		$atts
	);

	ob_start();

	$args = apply_filters( 'rentfetch_properties_simple_grid_query_args', $args );
	
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
add_shortcode( 'rentfetch_properties', 'rentfetch_properties' );

/**
 * This function sets up the $args for the city parameter.
 *
 * @param  array $args  the query arguments.
 * @return array        the modified query arguments.
 */
function rentfetch_properties_simple_grid_query_args_city( $args ) {
	if ( !isset( $args['city'] ) ) {
		return $args;
	}

	$cities_array = explode( ',', $args['city'] );

	// Start with an empty array for the 'OR' part of the query
	$cities_meta_query = array(
		'relation' => 'OR'
	);

	// Loop through each city in the array and add it to the $cities_meta_query
	foreach ( $cities_array as $city ) {
		$cities_meta_query[] = array(
			'key'     => 'city',
			'value'   => $city,
			'compare' => '=',
		);
	}

	// If meta_query already exists, add to it; otherwise create new
	if ( isset( $args['meta_query'] ) ) {
		// ensure the top-level relation is AND
		if ( !isset( $args['meta_query']['relation'] ) ) {
			$args['meta_query']['relation'] = 'AND';
		}
		// add our new meta query
		$args['meta_query'][] = $cities_meta_query;
	} else {
		// create new meta query
		$args['meta_query'] = array(
			'relation' => 'AND',
			$cities_meta_query
		);
	}

	// reset the city to null
	$args['city'] = null;

	return $args;
}
add_filter( 'rentfetch_properties_simple_grid_query_args', 'rentfetch_properties_simple_grid_query_args_city' );

function rentfetch_properties_simple_grid_query_args_propertyids( $args ) {
	
	// bail if the propertyids parameter is not set.
	if ( !isset( $args['propertyids'] ) ) {
		return $args;
	}
	
	// remove spaces and commas, exploding this into an array.
	$propertyids = explode( ',', str_replace( ' ', '', $args['propertyids'] ) );
	
	// create the property_id meta query
	$propertyids_meta_query = array(
		'key'     => 'property_id',
		'value'   => $propertyids,
		'compare' => 'IN',
	);
	
	// if meta_query already exists, add to it; otherwise create new
	if ( isset( $args['meta_query'] ) ) {
		// ensure the top-level relation is AND
		if ( !isset( $args['meta_query']['relation'] ) ) {
			$args['meta_query']['relation'] = 'AND';
		}
		// add our new meta query
		$args['meta_query'][] = $propertyids_meta_query;
	} else {
		// create new meta query
		$args['meta_query'] = array(
			'relation' => 'AND',
			$propertyids_meta_query
		);
	}
	
	// reset the propertyids to null.
	$args['propertyids'] = null;
	
	return $args;
	
}
add_filter( 'rentfetch_properties_simple_grid_query_args', 'rentfetch_properties_simple_grid_query_args_propertyids' );