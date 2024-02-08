<?php
/**
 * Property types filter
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the property types filter
 *
 * @return void
 */
function rentfetch_search_filters_property_types() {

	// bail if propertytypes taxonomy does not exist.
	if ( ! taxonomy_exists( 'propertytypes' ) ) {
		return;
	}

	// get information about types from the database.
	$propertytypes = get_terms(
		array(
			'taxonomy'   => 'propertytypes',
			'hide_empty' => true,
		),
	);

	// build the search.
	if ( ! empty( $propertytypes && taxonomy_exists( 'propertytypes' ) ) ) {
		echo '<fieldset class="property-type">';
			echo '<legend>Property type</legend>';
			echo '<button class="toggle">Property type</button>';
			echo '<div class="input-wrap checkboxes">';

		foreach ( $propertytypes as $propertytype ) {
			$name                 = $propertytype->name;
			$propertytype_term_id = $propertytype->term_id;

			// Check if the term ID is in the GET parameter array.
			$checked = in_array( $propertytype_term_id, $_GET['search-property-types'] ?? array(), true );

			printf(
				'<label>
					<input type="checkbox" 
						name="search-property-types[]" 
						value="%s" 
						data-propertytypes="%s" 
						data-propertytypesname="%s" 
						%s /> <!-- Add checked attribute if necessary -->
					<span>%s</span>
				</label>',
				(int) $propertytype_term_id,
				(int) $propertytype_term_id,
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
 * Apply the selected property types filter to the search
 *
 * @param array $property_args The property arguments.
 *
 * @return array.
 */
function rentfetch_search_properties_args_types( $property_args ) {

	if ( isset( $_POST['search-property-types'] ) && is_array( $_POST['search-property-types'] ) ) {

		// Get the values.
		$property_types = array_map( 'sanitize_text_field', wp_unslash( $_POST['search-property-types'] ) );

		// This is an "OR" query, where we want posts to match ANY of the specified property types.
		$property_types_query = array(
			'relation' => 'OR',
		);

		foreach ( $property_types as $property_type ) {
			$property_types_query[] = array(
				'taxonomy' => 'propertytypes',
				'terms'    => $property_type,
			);
		}

		// Add the amenities query to the property args tax query.
		$property_args['tax_query'][] = $property_types_query;
	}

	return $property_args;
}
add_filter( 'rentfetch_search_property_map_properties_query_args', 'rentfetch_search_properties_args_types', 10, 1 );
