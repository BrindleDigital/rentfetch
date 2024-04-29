<?php
/**
 * Set the order of the floorplan search filters
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set the order of the filters (on a hook so that someone could remove it and readd it in whatever order they want)
 *
 * @return void
 */
function rentfetch_search_floorplans_filters() {

	// get the options for which filters are enabled.
	$options_floorplan_filters = get_option( 'rentfetch_options_floorplan_filters' );

	if ( ! empty( $options_floorplan_filters ) && in_array( 'beds_search', $options_floorplan_filters, true ) ) {
		add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_beds' );
	}

	if ( ! empty( $options_floorplan_filters ) && in_array( 'baths_search', $options_floorplan_filters, true ) ) {
		add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_baths' );
	}
	
	if ( ! empty( $options_floorplan_filters ) && in_array( 'floorplan_category', $options_floorplan_filters, true ) ) {
		add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_floorplan_categories' );
	}
	
	if ( ! empty( $options_floorplan_filters ) && in_array( 'floorplan_type', $options_floorplan_filters, true ) ) {
		add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_floorplan_types' );
	}

	if ( ! empty( $options_floorplan_filters ) && in_array( 'squarefoot_search', $options_floorplan_filters, true ) ) {
		add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_squarefoot' );
	}

	if ( ! empty( $options_floorplan_filters ) && in_array( 'price_search', $options_floorplan_filters, true ) ) {
		add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_price' );
	}

	if ( ! empty( $options_floorplan_filters ) && in_array( 'date_search', $options_floorplan_filters, true ) ) {
		add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_date' );
	}

	add_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_sort_floorplans' );
}
add_action( 'wp', 'rentfetch_search_floorplans_filters' );
