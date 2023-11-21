<?php

function rentfetch_search_filters_beds() {
			
	// get info about beds from the database
	$beds = rentfetch_get_meta_values( 'beds', 'floorplans' );
	$beds = array_unique( $beds );
	asort( $beds );
			
	// build the beds search
	echo '<fieldset class="beds">';
		echo '<legend>Bedrooms</legend>';
		echo '<button class="toggle">Bedrooms</button>';
		echo '<div class="input-wrap checkboxes inactive">';
				
			foreach( $beds as $bed ) {
				
				// Check if the amenity's term ID is in the GET parameter array
				$checked = in_array($bed, $_GET['search-beds'] ?? array());
				
				// skip if there's a null value for bed
				if ( $bed === null )
					continue;
					
				$label = apply_filters( 'rentfetch_get_bedroom_number_label', $bed );
					
				printf( 
					'<label>
						<input type="checkbox" 
							name="search-beds[]"
							value="%s" 
							data-beds="%s" 
							%s />
						<span>%s</span>
					</label>', 
					$bed, 
					$bed,
					$checked ? 'checked' : '', // Apply checked attribute 
					$label
				);
			}
				
		echo '</div>'; // .checkboxes
	echo '</fieldset>';
}

function rentfetch_search_floorplans_args_beds( $floorplans_args ) {
		
	if ( isset( $_POST['search-beds'] ) && is_array( $_POST['search-beds'] ) ) {
		
		// Get the values
		$beds = $_POST['search-beds'];
		
		// Escape the values
		$beds = array_map( 'sanitize_text_field', $beds );
		
		// Convert the beds query to a meta query
		$meta_query = array(
			array(
				'key' => 'beds',
				'value' => $beds,
			),
		);
				
		// Add the meta query to the property args
		$floorplans_args['meta_query'][] = $meta_query;
				
	}
	
	return $floorplans_args;
}
add_filter('rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_beds', 10, 1);