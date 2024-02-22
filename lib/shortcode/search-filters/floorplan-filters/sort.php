<?php
/**
 * Sorting parameters
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the form markup for the sorting option
 *
 * @return void.
 */
function rentfetch_search_filters_sort_floorplans() {

	// get the sort parameter if it exists.
	if ( isset( $_GET['sort'] ) ) {
		$sort = sanitize_text_field( wp_unslash( $_GET['sort'] ) );
	} else {
		$sort = null;
	}
	
	$label = apply_filters( 'rentfetch_search_filters_sort_label', 'Sorting' );

	// build the baths search.
	echo '<fieldset class="sort">';
		printf( '<legend>%s</legend>', esc_html( $label ) );
		printf( '<button class="toggle">%s</button>', esc_html( esc_html( $label ) ) );
		echo '<div class="input-wrap radio checkboxes inactive">';
			if ( 'availability' === $sort ) {
				$checked = 'checked';
			} else {
				$checked = null;
			}

			printf(
				'<label>
					<input type="radio" 
						name="sort"
						id="sort-availability"
						value="availability"
						data-sort="availability" 
						%s />
					<span>Sort by Available Units</span>
				</label>',
				$checked ? 'checked' : '', // Apply checked attribute.
			);

			if ( 'beds' === $sort ) {
				$checked = 'checked';
			} else {
				$checked = null;
			}

			printf(
				'<label>
					<input type="radio" 
						name="sort"
						id="sort-beds"
						value="beds" 
						data-sort="beds" 
						%s />
					<span>Sort by Beds</span>
				</label>',
				$checked ? 'checked' : '', // Apply checked attribute.
			);

			if ( 'baths' === $sort ) {
				$checked = 'checked';
			} else {
				$checked = null;
			}

			printf(
				'<label>
					<input type="radio" 
						name="sort"
						id="sort-baths"
						value="baths" 
						data-sort="baths" 
						%s />
					<span>Sort by Baths</span>
				</label>',
				$checked ? 'checked' : '', // Apply checked attribute.
			);

		echo '</div>'; // .checkboxes
	echo '</fieldset>';
}

/**
 * Add the sorting filter to the search filters
 *
 * @param array $floorplans_args The floorplan arguments.
 *
 * @return array.
 */
function rentfetch_search_floorplans_args_sort_floorplans( $floorplans_args ) {

	// set $sort to null by default. If it's null, then we'll return the default $floorplans_args.
	$sort = null;

	// get the sort value.
	if ( isset( $_POST['sort'] ) ) {

		$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';

		// * Verify the nonce
		if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_frontend_nonce_action' ) ) {
			die( 'Nonce verification failed (sort)' );
		}

		$sort = sanitize_text_field( wp_unslash( $_POST['sort'] ) );
	} else {
		$default_order = get_option( 'rentfetch_options_floorplan_default_order' );
		if ( $default_order ) {
			$sort = $default_order;
		}
	}

	// bail if we don't have a value for $sort.
	if ( null === $sort ) {
		return $floorplans_args;
	}

	// if it's beds...
	if ( 'beds' === $sort ) {
		$floorplans_args['orderby']  = 'meta_value_num';
		$floorplans_args['meta_key'] = 'beds';
		$floorplans_args['order']    = 'ASC';
	}

	// if it's baths.
	if ( 'baths' === $sort ) {
		$floorplans_args['orderby']  = 'meta_value_num';
		$floorplans_args['meta_key'] = 'baths';
		$floorplans_args['order']    = 'ASC';
	}

	// if it's available units.
	if ( 'availability' === $sort ) {
		$floorplans_args['orderby']  = 'meta_value_num';
		$floorplans_args['meta_key'] = 'available_units';
		$floorplans_args['order']    = 'DESC';
	}

	// return the args.
	return $floorplans_args;

}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_sort_floorplans', 10, 1 );
