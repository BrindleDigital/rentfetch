<?php
/**
 * Amenities filter
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add the amenities filter to the search filters
 *
 * @return void
 */
function rentfetch_search_filters_amenities() {

	// * figure out how many amenities to show
	$number_of_amenities_to_show = get_option( 'rentfetch_options_number_of_amenities_to_show' );

	// * get information about amenities from the database
	$amenities = get_terms(
		array(
			'taxonomy'   => 'amenities',
			'hide_empty' => true,
			'number'     => $number_of_amenities_to_show,
			'orderby'    => 'count',
			'order'      => 'DESC',
		),
	);

	// get the parameters.
	if ( isset( $_GET['search-amenities'] ) ) {
		$active_parameters = array_map( 'intval', wp_unslash( $_GET['search-amenities'] ) );
	} else {
		$active_parameters = array();
	}

	// * Build amenities search
	if ( ! empty( $amenities ) && taxonomy_exists( 'amenities' ) ) {

		$label = apply_filters( 'rentfetch_search_filters_amenities_label', 'Amenities' );

		echo '<fieldset class="amenities">';
			printf( '<legend>%s</legend>', esc_html( $label ) );
			printf( '<button type="button" class="toggle">%s</button>', esc_html( esc_html( $label ) ) );
			echo '<div class="input-wrap checkboxes">';

		foreach ( $amenities as $amenity ) {
			$name            = $amenity->name;
			$amenity_term_id = $amenity->term_id;

			// Check if the amenity's term ID is in the GET parameter array.
			$checked = in_array( $amenity_term_id, $active_parameters ?? array(), true );

			printf(
				'<label>
					<input type="checkbox" 
						name="search-amenities[]" 
						value="%s" 
						data-amenities="%s" 
						data-amenities-name="%s" 
						data-type="taxonomy" 
						%s /> <!-- Add checked attribute if necessary -->
					<span>%s</span>
				</label>',
				esc_html( $amenity_term_id ),
				esc_html( $amenity_term_id ),
				esc_html( $name ),
				$checked ? 'checked' : '', // Apply checked attribute.
				esc_html( $name )
			);
		}

			echo '</div>'; // .checkboxes.
		echo '</fieldset>';
	}
}

/**
 * Apply the amenities filter to the search filters
 *
 * @param array $property_args The property arguments.
 *
 * @return array.
 */
function rentfetch_search_properties_args_amenities( $property_args ) {

	$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_frontend_nonce_action' ) ) {
		die( 'Nonce verification failed' );
	}

	if ( isset( $_POST['search-amenities'] ) && is_array( $_POST['search-amenities'] ) ) {

		// Get the values.
		$amenities = array_map( 'sanitize_text_field', wp_unslash( $_POST['search-amenities'] ) );

		// This is an "AND" query, where we want posts to match ALL of the specified amenities.
		$amenities_query = array(
			'relation' => 'AND',
		);

		foreach ( $amenities as $amenity ) {
			$amenities_query[] = array(
				'taxonomy' => 'amenities',
				'terms'    => $amenity,
			);
		}

		// Add the amenities query to the property args tax query.
		$property_args['tax_query'][] = $amenities_query;
	}

	return $property_args;
}
add_filter( 'rentfetch_search_property_map_properties_query_args', 'rentfetch_search_properties_args_amenities', 10, 1 );
