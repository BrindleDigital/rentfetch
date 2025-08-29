<?php
/**
 * A city filter based on post meta
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the city filter
 *
 * @return void
 */
function rentfetch_search_filters_cities() {

	// the meta key for the city field.
	$meta_key = 'city';

	// the name of the search parameter.
	$search_parameter = 'search-' . $meta_key;

	// the label for the filter.
	$filter_label = 'Cities';

	// get unique city values from the database.
	// Retrieve city meta values via the helper (uses transient pseudocache).
	$all_cities = rentfetch_get_meta_values( $meta_key, 'properties', 'publish' );

	if ( empty( $all_cities ) ) {
		return;
	}

	// Normalize, remove empties and duplicates, then sort case-insensitively.
	$cities = array_map( 'trim', $all_cities );
	$cities = array_filter( $cities, function( $v ) { return '' !== $v; } );
	$cities = array_unique( $cities );
	if ( empty( $cities ) ) {
		return;
	}
	natcasesort( $cities );
	$cities = array_values( $cities );

	// bail if there aren't any cities.
	if ( empty( $cities ) ) {
		return;
	}

	// get the parameters.
	if ( isset( $_GET[ $search_parameter ] ) ) {
		$active_parameters = array_map( 'sanitize_text_field', wp_unslash( $_GET[ $search_parameter ] ) );
	} else {
		$active_parameters = array();
	}

	// build the search.
	if ( ! empty( $cities ) ) {
		echo '<fieldset class="meta-field">';
			printf( '<legend>%s</legend>', esc_attr( $filter_label ) );
			printf( '<button type="button" class="toggle">%s</button>', esc_attr( $filter_label ) );
			echo '<div class="input-wrap checkboxes">';

		foreach ( $cities as $city ) {
			// Check if the city is in the GET parameter array.
			$checked = in_array( $city, $active_parameters ?? array(), true );

			printf(
				'<label>
					<input type="checkbox" 
						name="search-%s[]" 
						value="%s" 
						data-type="meta"
						%s /> <!-- Add checked attribute if necessary -->
					<span>%s</span>
				</label>',
				esc_attr( $meta_key ),
				esc_attr( $city ),
				$checked ? 'checked' : '', // Apply checked attribute.
				esc_html( $city )
			);
		}

			echo '</div>'; // .checkboxes.
		echo '</fieldset>';
	}
}

/**
 * Apply the selected city filter to the search
 *
 * @param array $property_args The property arguments.
 *
 * @return array.
 */
function rentfetch_search_properties_args_cities( $property_args ) {

	$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_frontend_nonce_action' ) ) {
		die( 'Nonce verification failed' );
	}

	// the meta key for the city field.
	$meta_key = 'city';

	// the name of the search parameter.
	$search_parameter = 'search-' . $meta_key;

	if ( isset( $_POST[ $search_parameter ] ) && is_array( $_POST[ $search_parameter ] ) ) {

		// Get the values.
		$cities = array_map( 'sanitize_text_field', wp_unslash( $_POST[ $search_parameter ] ) );

		// This is an "OR" query, where we want posts to match ANY of the specified cities.
		$cities_query = array(
			'relation' => 'OR',
		);

		foreach ( $cities as $city ) {
			$cities_query[] = array(
				'key'   => $meta_key,
				'value' => $city,
			);
		}

		// Add the cities query to the property args meta query.
		if ( ! isset( $property_args['meta_query'] ) ) {
			$property_args['meta_query'] = array();
		}
		$property_args['meta_query'][] = $cities_query;
	}

	return $property_args;
}
add_filter( 'rentfetch_search_property_map_properties_query_args', 'rentfetch_search_properties_args_cities', 10, 1 );