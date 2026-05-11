<?php
/**
 * Baths filter
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the form markup for the baths
 *
 * @return void.
 */
function rentfetch_search_filters_baths() {

	$property_ids = rentfetch_get_floorplan_filter_property_ids();

	// Get info about baths from the database. Global searches use the cached helper;
	// property-scoped searches query only those property floorplans without a transient.
	if ( ! empty( $property_ids ) ) {
		$baths = rentfetch_get_floorplan_meta_values_for_property_ids( 'baths', $property_ids );
	} else {
		$baths = rentfetch_get_meta_values( 'baths', 'floorplans' );
	}

	$baths = array_unique( $baths );
	asort( $baths );
	$baths = array_values(
		array_filter(
			$baths,
			function( $bath ) {
				return null !== $bath && 0 !== $bath && '0' !== $bath;
			}
		)
	);

	if ( count( $baths ) < 2 ) {
		return;
	}
	
	$label = apply_filters( 'rentfetch_search_filters_baths_label', 'Baths' );

	// build the baths search.
	echo '<fieldset class="baths">';
		printf( '<legend>%s</legend>', esc_html( $label ) );
		printf( '<button type="button" class="toggle">%s</button>', esc_html( $label ) );
		echo '<div class="input-wrap checkboxes inactive">';

	foreach ( $baths as $bath ) {

		// Check if the amenity's term ID is in the GET parameter array.
		$checked = in_array( $bath, $_GET['search-baths'] ?? array(), true );

		// skip if there's a null value for bath.
		if ( null === $bath || 0 === $bath || '0' === $bath ) {
			continue;
		}

		$label = apply_filters( 'rentfetch_get_bathroom_number_label', $bath );

		printf(
			'<label>
				<input type="checkbox" 
					name="search-baths[]"
					value="%s" 
					data-baths="%s" 
					%s />
				<span>%s</span>
			</label>',
			esc_attr( $bath ),
			esc_attr( $bath ),
			$checked ? 'checked' : '', // Apply checked attribute.
			wp_kses_post( $label )
		);
	}
			echo '</div>'; // .checkboxes.
	echo '</fieldset>';
}

/**
 * Update the floorplans query with the baths search
 *
 * @param   array $floorplans_args  The floorplans query arguments.
 *
 * @return  array  $floorplans_args  The updated floorplans query arguments.
 */
function rentfetch_search_floorplans_args_baths( $floorplans_args ) {

	if ( isset( $_POST['search-baths'] ) && is_array( $_POST['search-baths'] ) ) {

		// Get the values.
		$baths = array_map( 'sanitize_text_field', wp_unslash( $_POST['search-baths'] ) );

		// Convert the baths query to a meta query.
		$meta_query = array(
			array(
				'key'   => 'baths',
				'value' => $baths,
			),
		);

		// Add the meta query to the property args.
		$floorplans_args['meta_query'][] = $meta_query;

	}

	return $floorplans_args;
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_baths', 10, 1 );
