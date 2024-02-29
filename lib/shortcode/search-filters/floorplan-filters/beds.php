<?php
/**
 * Beds filter
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the form markup for the beds
 *
 * @return void.
 */
function rentfetch_search_filters_beds() {

	// get info about beds from the database.
	$beds = rentfetch_get_meta_values( 'beds', 'floorplans' );
	$beds = array_map( 'intval', $beds );
	$beds = array_unique( $beds );
	asort( $beds );

	// get the parameters.
	if ( isset( $_GET['search-beds'] ) ) {
		$active_parameters = array_map( 'intval', wp_unslash( $_GET['search-beds'] ) );
	} else {
		$active_parameters = array();
	}

	$beds = apply_filters( 'rentfetch_filter_beds_in_dropdown', $beds );
	$label = apply_filters( 'rentfetch_search_filters_beds_label', 'Bedrooms' );

	// build the beds search.
	echo '<fieldset class="beds">';
		printf( '<legend>%s</legend>', esc_html( $label ) );
		printf( '<button type="button" class="toggle">%s</button>', esc_html( esc_html( $label ) ) );
		echo '<div class="input-wrap checkboxes inactive">';

	foreach ( $beds as $bed ) {

		// Check if the amenity's term ID is in the GET parameter array.
		$checked = in_array( $bed, $active_parameters ?? array(), true );

		// skip if there's a null value for bed.
		if ( null === $bed ) {
			continue;
		}

		$label = apply_filters( 'rentfetch_get_bedroom_number_label', $bed );

		printf(
			'<label>
				<input type="checkbox" 
					name="search-beds[]"
					value="%s" 
					data-beds="%s" 
					%s />
				<span>%s</span>
			</label>',
			(int) $bed,
			(int) $bed,
			$checked ? 'checked' : '', // Apply checked attribute.
			wp_kses_post( $label )
		);
	}

		echo '</div>'; // .checkboxes.
	echo '</fieldset>';
}

/**
 * Detect if the beds search is set and add it to the floorplans args
 *
 * @param   array $floorplans_args The floorplans query arguments.
 *
 * @return  array $floorplans_args The updated floorplans query arguments.
 */
function rentfetch_search_floorplans_args_beds( $floorplans_args ) {

	if ( isset( $_POST['search-beds'] ) && is_array( $_POST['search-beds'] ) ) {

		$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';

		// * Verify the nonce
		if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_frontend_nonce_action' ) ) {
			die( 'Nonce verification failed (beds)' );
		}

		// Get the values.
		$beds = array_map( 'sanitize_text_field', wp_unslash( $_POST['search-beds'] ) );

		// Convert the beds query to a meta query.
		$meta_query = array(
			array(
				'key'   => 'beds',
				'value' => $beds,
			),
		);

		// Add the meta query to the property args.
		$floorplans_args['meta_query'][] = $meta_query;

	}

	return $floorplans_args;
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_beds', 10, 1 );

/**
 * Filter the number of beds in the dropdown
 * Allows for customizing which bedroom numbers are shown in the dropdown
 *
 * @param   array $beds  a unique array of the numbers of beds that are floorplan meta from the database.
 *
 * @return  array  $beds  a unique array of the numbers of beds that are floorplan meta from the database.
 */
function rentfetch_beds_in_dropdown( $beds ) {

	// set this array so that if we don't have any beds we don't cause a php error.
	$filtered_beds = array();

	// get the setting for the max number of beds to show in the dropdown.
	$max_beds = get_option( 'rentfetch_options_maximum_bedrooms_to_search' );

	// if the setting is not set, or is not a number, return the original array.
	if ( ! is_numeric( $max_beds ) ) {
		return $beds;
	}

	// drop anything from the array that's above $max_beds.
	foreach ( $beds as $bed ) {
		if ( $bed <= $max_beds ) {
			$filtered_beds[] = $bed;
		}
	}

	return $filtered_beds;
}
add_filter( 'rentfetch_filter_beds_in_dropdown', 'rentfetch_beds_in_dropdown' );
