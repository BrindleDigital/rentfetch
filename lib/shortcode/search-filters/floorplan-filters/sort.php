<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function rentfetch_search_filters_sort_floorplans() {
					
	// get the sort parameter if it exists
	if ( isset( $_GET['sort'] ) ) {
		$sort = $_GET['sort'];
	} else {
		$sort = null;
	}
			
	// build the baths search
	echo '<fieldset class="sort">';
		echo '<legend>Sort by</legend>';
		echo '<button class="toggle">Sorting</button>';
		echo '<div class="input-wrap radio checkboxes inactive">';
		
				if ( $sort == 'availability' ) {
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
					$checked ? 'checked' : '', // Apply checked attribute 
				);
						
				if ( $sort == 'beds' ) {
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
					$checked ? 'checked' : '', // Apply checked attribute 
				);
				
				if ( $sort == 'baths' ) {
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
					$checked ? 'checked' : '', // Apply checked attribute 
				);
						
				
			echo '</div>'; // .checkboxes
	echo '</fieldset>';
		
}

function rentfetch_search_floorplans_args_sort_floorplans( $floorplans_args ) {
	
	// set $sort to null by default. If it's null, then we'll return the default $floorplans_args
	$sort = null;
	
	// get the sort value
	if ( isset( $_POST['sort'] ) ) {
		$sort = $_POST['sort'];
	} else {
		$default_order = get_option( 'rentfetch_options_floorplan_default_order' );
		if ( $default_order ) {
			$sort = $default_order;
		}
	}
	
	// console_log( $sort );
	
	// bail if we don't have a value for $sort
	if ( $sort == null )
		return $floorplans_args;
	
	// if it's beds...
	if ( $sort == 'beds' ) {
		// console_log( 'Sorting by beds...' );
		$floorplans_args['orderby'] = 'meta_value_num';
		$floorplans_args['meta_key'] = 'beds';
		$floorplans_args['order'] = 'ASC';
	}
	
	// if it's baths
	if ( $sort == 'baths' ) {
		// console_log( 'Sorting by baths...' );
		$floorplans_args['orderby'] = 'meta_value_num';
		$floorplans_args['meta_key'] = 'baths';
		$floorplans_args['order'] = 'ASC';
	}
	
	// if it's available units
	if ( $sort == 'availability' ) {
		// console_log( 'Sorting by availability...' );
		$floorplans_args['orderby'] = 'meta_value_num';
		$floorplans_args['meta_key'] = 'available_units';
		$floorplans_args['order'] = 'DESC';
	}
		
	// return the args
	return $floorplans_args;
		
	// 	// Get the values
	// 	$baths = $_POST['search-baths'];
		
	// 	// Escape the values
	// 	$baths = array_map( 'sanitize_text_field', $baths );
		
	// 	// Convert the baths query to a meta query
	// 	$meta_query = array(
	// 		array(
	// 			'key' => 'baths',
	// 			'value' => $baths,
	// 		),
	// 	);
				
	// 	// Add the meta query to the property args
	// 	$floorplans_args['meta_query'][] = $meta_query;
				
	// }
	
	// return $floorplans_args;
}
add_filter('rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_sort_floorplans', 10, 1);
