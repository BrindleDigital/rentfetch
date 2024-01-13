<?php

function rentfetch_search_floorplans_args_shortcode( $floorplans_args ) {
		
	//! Property IDs
	if ( isset( $_POST['property_id'] ) ) {
				
		// Get the values
		$property_ids = $_POST['property_id'];
		
		// Escape the values
		$property_ids_array = explode( ',', $property_ids );
				
		// Convert the beds query to a meta query
		$meta_query = array(
			array(
				'key' => 'property_id',
				'value' => $property_ids_array,
			),
		);
				
		// Add the meta query to the property args
		$floorplans_args['meta_query'][] = $meta_query;
				
	}
		
	return $floorplans_args;
}
add_filter('rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_shortcode', 10, 1 );