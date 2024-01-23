<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The simple floorplans template
 *
 */
function rentfetch_floorplans( $atts ) {
	$a = shortcode_atts( array(
		'property_id' => null,
		'beds' => null,
		'sort' => null,
	), $atts );

	ob_start();
	
	$args = array(
		'post_type' => 'floorplans',
		'posts_per_page' => '-1',
		'orderby' => 'meta_value_num',
		'meta_key' => 'beds',
		'order' => 'ASC',
	);
	
	$args = apply_filters( 'rentfetch_floorplans_simple_grid_query_args', $args, $a );

	// The Query
	$custom_query = new WP_Query( $args );

	// The Loop
	if ( $custom_query->have_posts() ) {
		
		echo '<div class="floorplans-simple-grid">';

		while ( $custom_query->have_posts() ) {
			
			$custom_query->the_post();

			printf( '<div class="%s">', implode( ' ', get_post_class() ) );
			
				do_action( 'rentfetch_do_each_floorplan_in_archive' );

			echo '</div>';

		}
		
		echo '</div>';
		
		// Restore postdata
		wp_reset_postdata();

	}
	
	return ob_get_clean();
}
add_shortcode( 'floorplans', 'rentfetch_floorplans' );

/**
 * Filter the property ids
 */
function rentfetch_floorplans_simple_grid_query_args_property_id( $args, $atts ) {
	
	if ( isset( $atts['property_id'] ) ) {
		
		$array_property_ids = explode( ',', $atts['property_id'] );
		
		$args['meta_query'][] = array(
			'key' => 'property_id',
			'value' => $array_property_ids,
		);
		
	}
	
	return $args;
	
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_property_id', 10, 2 );

/**
 * Filter the beds
 */
function rentfetch_floorplans_simple_grid_query_args_beds( $args, $atts ) {
	
	if ( isset( $atts['beds'] ) ) {
		
		$array_beds = explode( ',', $atts['beds'] );
		
		$args['meta_query'][] = array(
			'key' => 'beds',
			'value' => $array_beds,
		);
		
	}
	
	return $args;
	
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_beds', 10, 2 );

/**
 * Filter the order and pass it to the query args appropriately
 */
function rentfetch_floorplans_simple_grid_query_args_order( $floorplans_args, $atts ) {
	
	if ( isset( $atts['sort'] ) ) {
		
		$sort = $atts['sort'];
		
		// if it's beds...
		if ( $sort == 'beds' ) {
			// console_log( 'Sorting by beds...' );
			$floorplans_args['orderby'] = 'meta_value_num';
			$floorplans_args['meta_key'] = 'beds';
			$floorplans_args['order'] = 'ASC';
		}
		
		// if it's baths
		if ( $sort == 'baths' ) {
			// console_log( 'Sorting by baths...' );
			$floorplans_args['orderby'] = 'meta_value_num';
			$floorplans_args['meta_key'] = 'baths';
			$floorplans_args['order'] = 'ASC';
		}
		
		// if it's available units
		if ( $sort == 'availability' ) {
			// console_log( 'Sorting by availability...' );
			$floorplans_args['orderby'] = 'meta_value_num';
			$floorplans_args['meta_key'] = 'available_units';
			$floorplans_args['order'] = 'DESC';
		}
		
	}
	
	return $floorplans_args;
	
}
add_filter( 'rentfetch_floorplans_simple_grid_query_args', 'rentfetch_floorplans_simple_grid_query_args_order', 10, 2 );