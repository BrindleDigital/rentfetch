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

	// get info about baths from the database.
	$baths = rentfetch_get_meta_values( 'baths', 'floorplans' );
	$baths = array_unique( $baths );
	asort( $baths );
	
	$label = apply_filters( 'rentfetch_search_filters_baths_label', 'Baths' );

	// build the baths search.
	echo '<fieldset class="baths">';
		printf( '<legend>%s</legend>', esc_html( $label ) );
		printf( '<button class="toggle">%s</button>', esc_html( esc_html( $label ) ) );
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
			wp_kses_post( $bath ),
			wp_kses_post( $bath ),
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

		$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';

		// * Verify the nonce
		if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_frontend_nonce_action' ) ) {
			die( 'Nonce verification failed (baths)' );
		}

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
