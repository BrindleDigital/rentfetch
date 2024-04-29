<?php
/**
 * Set the order of the property search filters
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add the search filters for the properties
 *
 * @return void.
 */
function rentfetch_search_properties_dialog_filters() {

	// get the array of enabled filters.
	$options_dialog_filters = get_option( 'rentfetch_options_dialog_filters' );

	if ( ! empty( $options_dialog_filters ) && in_array( 'text_based_search', $options_dialog_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_text_search' );
	}

	// add a spot that's default for custom filters to be added.
	add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_do_search_properties_custom_filters' );

	if ( ! empty( $options_dialog_filters ) && in_array( 'beds_search', $options_dialog_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_beds' );
	}

	if ( ! empty( $options_dialog_filters ) && in_array( 'baths_search', $options_dialog_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_baths' );
	}

	if ( ! empty( $options_dialog_filters ) && in_array( 'squarefoot_search', $options_dialog_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_squarefoot' );
	}

	if ( ! empty( $options_dialog_filters ) && in_array( 'category_search', $options_dialog_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_property_categories' );
	}
	
	if ( ! empty( $options_dialog_filters ) && in_array( 'type_search', $options_dialog_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_property_types' );
	}

	if ( ! empty( $options_dialog_filters ) && in_array( 'floorplan_category_search', $options_dialog_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_floorplan_categories' );
	}

	if ( ! empty( $options_dialog_filters ) && in_array( 'floorplan_type_search', $options_dialog_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_floorplan_types' );
	}

	if ( ! empty( $options_dialog_filters ) && in_array( 'date_search', $options_dialog_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_date' );
	}

	if ( ! empty( $options_dialog_filters ) && in_array( 'price_search', $options_dialog_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_price' );
	}

	if ( ! empty( $options_dialog_filters ) && in_array( 'amenities_search', $options_dialog_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_amenities' );
	}
}
add_action( 'wp', 'rentfetch_search_properties_dialog_filters' );

/**
 * Set the order of the properties featured filters
 *
 * @return void.
 */
function rentfetch_search_properties_featured_filters() {

	// get the array of enabled filters.
	$options_featured_filters = get_option( 'rentfetch_options_featured_filters' );

	if ( ! empty( $options_featured_filters ) && in_array( 'text_based_search', $options_featured_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_filters_text_search' );
	}

	// add a spot that's default for custom filters to be added.
	add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_do_search_properties_custom_filters' );

	if ( ! empty( $options_featured_filters ) && in_array( 'beds_search', $options_featured_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_filters_beds' );
	}

	if ( ! empty( $options_featured_filters ) && in_array( 'baths_search', $options_featured_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_filters_baths' );
	}

	if ( ! empty( $options_featured_filters ) && in_array( 'squarefoot_search', $options_featured_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_filters_squarefoot' );
	}

	if ( ! empty( $options_featured_filters ) && in_array( 'category_search', $options_featured_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_filters_property_categories' );
	}

	if ( ! empty( $options_featured_filters ) && in_array( 'type_search', $options_featured_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_filters_property_types' );
	}
	
	if ( ! empty( $options_featured_filters ) && in_array( 'floorplan_category_search', $options_featured_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_filters_floorplan_categories' );
	}
	
	if ( ! empty( $options_featured_filters ) && in_array( 'floorplan_type_search', $options_featured_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_filters_floorplan_types' );
	}

	if ( ! empty( $options_featured_filters ) && in_array( 'date_search', $options_featured_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_filters_date' );
	}

	if ( ! empty( $options_featured_filters ) && in_array( 'price_search', $options_featured_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_filters_price' );
	}

	if ( ! empty( $options_featured_filters ) && in_array( 'amenities_search', $options_featured_filters, true ) ) {
		add_action( 'rentfetch_do_search_properties_featured_filters', 'rentfetch_search_filters_amenities' );
	}
}
add_action( 'wp', 'rentfetch_search_properties_featured_filters' );

/**
 * Add a location for custom filters to go by default.
 *
 * @return void.
 */
function rentfetch_do_search_properties_custom_filters() {
	do_action( 'rentfetch_do_search_properties_custom_filter_location' );
}
