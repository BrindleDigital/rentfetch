<?php
/**
 * A taxonomy filter
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the taxonomy filter
 *
 * @return void
 */
function rentfetch_search_filters_floorplan_types() {

	// the slug for the taxonomy. If you're adding a new taxonomy,
	// you'll need to change this to the slug of the new taxonomy.
	$taxonomy_slug = 'floorplantype';

	// the name of the search parameter.
	$search_parameter = 'search-' . $taxonomy_slug;

	// get the label for the taxonomy.
	$taxonomy_label = get_taxonomy( $taxonomy_slug )->labels->name;

	// bail if taxonomy does not exist.
	if ( ! taxonomy_exists( $taxonomy_slug ) ) {
		return;
	}

	// get information about types from the database.
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy_slug,
			'hide_empty' => true,
		),
	);

	// bail if there aren't any terms.
	if ( empty( $terms ) ) {
		return;
	}

	// get the parameters.
	if ( isset( $_GET[ $search_parameter ] ) ) {
		$active_parameters = array_map( 'intval', wp_unslash( $_GET[ $search_parameter ] ) );
	} else {
		$active_parameters = array();
	}

	// build the search.
	if ( ! empty( $terms && taxonomy_exists( $taxonomy_slug ) ) ) {
		echo '<fieldset class="taxonomy">';
			printf( '<legend>%s</legend>', esc_attr( $taxonomy_label ) );
			printf( '<button type="button" class="toggle">%s</button>', esc_attr( $taxonomy_label ) );
			echo '<div class="input-wrap checkboxes">';

		foreach ( $terms as $term ) {
			$name   = $term->name;
			$tax_id = $term->term_id;

			// Check if the term ID is in the GET parameter array.
			$checked = in_array( $tax_id, $active_parameters ?? array(), true );

			printf(
				'<label>
					<input type="checkbox" 
						name="search-%s[]" 
						value="%s" 
						data-type="taxonomy"
						%s /> <!-- Add checked attribute if necessary -->
					<span>%s</span>
				</label>',
				esc_attr( $taxonomy_slug ),
				(int) $tax_id,
				$checked ? 'checked' : '', // Apply checked attribute.
				esc_html( $name )
			);
		}

			echo '</div>'; // .checkboxes.
		echo '</fieldset>';
	}
}

/**
 * Apply the selected taxonomy filter to the search
 *
 * @param array $property_args The property arguments.
 *
 * @return array.
 */
function rentfetch_search_floorplans_args_types( $property_args ) {

	// the slug for the taxonomy. If you're adding a new taxonomy,
	// you'll need to change this to the slug of the new taxonomy.
	$taxonomy_slug = 'floorplantype';

	// the name of the search parameter.
	$search_parameter = 'search-' . $taxonomy_slug;

	if ( isset( $_POST[ $search_parameter ] ) && is_array( $_POST[ $search_parameter ] ) ) {

		// Get the values.
		$terms = array_map( 'sanitize_text_field', wp_unslash( $_POST[ $search_parameter ] ) );

		// This is an "OR" query, where we want posts to match ANY of the specified taxonomy terms.
		$terms_query = array(
			'relation' => 'OR',
		);

		foreach ( $terms as $term ) {
			$terms_query[] = array(
				'taxonomy' => $taxonomy_slug,
				'terms'    => $term,
			);
		}

		// Add the amenities query to the property args tax query.
		$property_args['tax_query'][] = $terms_query;
	}

	return $property_args;
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_types', 10, 1 );
