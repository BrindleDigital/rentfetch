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
		printf( '<button type="button" class="toggle">%s</button>', esc_html( $label ) );
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
					<span>Available Units</span>
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
					<span>Beds</span>
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
					<span>Baths</span>
				</label>',
				$checked ? 'checked' : '', // Apply checked attribute.
			);

			if ( 'pricelow' === $sort ) {
				$checked = 'checked';
			} else {
				$checked = null;
			}

			printf(
				'<label>
					<input type="radio" 
						name="sort"
						id="sort-pricelow"
						value="pricelow" 
						data-sort="pricelow" 
						%s />
					<span>Price (low to high)</span>
				</label>',
				$checked ? 'checked' : '', // Apply checked attribute.
			);

			if ( 'pricehigh' === $sort ) {
				$checked = 'checked';
			} else {
				$checked = null;
			}

			printf(
				'<label>
					<input type="radio" 
						name="sort"
						id="sort-pricehigh"
						value="pricehigh" 
						data-sort="Price (high to low)" 
						%s />
					<span>Price (high to low)</span>
				</label>',
				$checked ? 'checked' : '', // Apply checked attribute.
			);
			
			if ( 'alphabetical' === $sort ) {
				$checked = 'checked';
			} else {
				$checked = null;
			}

			printf(
				'<label>
					<input type="radio" 
						name="sort"
						id="sort-alphabetical"
						value="alphabetical" 
						data-sort="Alphabetical" 
						%s />
					<span>Alphabetical</span>
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
		$floorplans_args['meta_key'] = 'beds'; // phpcs:ignore
		$floorplans_args['order']    = 'ASC';
	}

	// if it's baths.
	if ( 'baths' === $sort ) {
		$floorplans_args['orderby']  = 'meta_value_num';
		$floorplans_args['meta_key'] = 'baths'; // phpcs:ignore
		$floorplans_args['order']    = 'ASC';
	}

	// if it's available units.
	if ( 'availability' === $sort ) {
		$floorplans_args['orderby']  = 'meta_value_num';
		$floorplans_args['meta_key'] = 'available_units'; // phpcs:ignore
		$floorplans_args['order']    = 'DESC';
	}

	// if it's price low to high.
	if ( 'pricelow' === $sort ) {
		$floorplans_args['orderby']    = 'meta_value_num';
		$floorplans_args['meta_key']   = 'minimum_rent'; // phpcs:ignore
		$floorplans_args['order']      = 'ASC';
		$floorplans_args['meta_query'][] = array( // phpcs:ignore
			'key'     => 'minimum_rent',
			'value'   => 100,
			'type'    => 'numeric',
			'compare' => '>',
		);
	}

	// if it's price high to low.
	if ( 'pricehigh' === $sort ) {
		$floorplans_args['orderby']    = 'meta_value_num';
		$floorplans_args['meta_key']   = 'minimum_rent'; // phpcs:ignore
		$floorplans_args['order']      = 'DESC';
		$floorplans_args['meta_query'][] = array( // phpcs:ignore
			'key'     => 'minimum_rent',
			'value'   => 100,
			'type'    => 'numeric',
			'compare' => '>',
		);
	}
	
	// if it's alphabetical...
	if ( 'alphabetical' === $sort ) {
		$floorplans_args['orderby']  = 'title';
		$floorplans_args['order']    = 'ASC';
	}
	
	// if it's menu_order...
	if ( 'menu_order' === $sort ) {
		$floorplans_args['orderby']  = 'menu_order';
		$floorplans_args['order']    = 'ASC';
	}

	// return the args.
	return $floorplans_args;
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_sort_floorplans', 10, 1 );
