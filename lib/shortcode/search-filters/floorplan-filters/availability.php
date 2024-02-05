<?php

/**
 * Output the availability filter
 *
 * @return array $floorplans_args
 */
function rentfetch_search_filters_availability() {
	
	$display_availability = get_option( 'rentfetch_options_property_availability_display' );
	
	// bail if the option isn't set to 'availability'
	if ( 'available' !== $display_availability )
		return;
	
	echo '<input type="hidden" name="availability" value="1" />';
}
add_action( 'rentfetch_do_search_properties_dialog_filters', 'rentfetch_search_filters_availability' );

/**
 * Force the availability search to only show properties with available floorplans
 *
 * @param array $floorplans_args
 *
 * @return array $floorplans_args
 */
function rentfetch_search_floorplans_args_availability( $floorplans_args ) {
	
	// bail if we don't have a price search (neither are set)
	if ( !isset( $_POST['availability'] ) )
		return $floorplans_args;
					
	$floorplans_args['meta_query'][] = array(
		array(
			'key' => 'available_units',
			'value' => 1,
			'type' => 'numeric',
			'compare' => '>=',
		),
	);
		
	return $floorplans_args;
	
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_availability', 10, 1 );