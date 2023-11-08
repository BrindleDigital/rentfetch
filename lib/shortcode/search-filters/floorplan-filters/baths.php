<?php

function rentfetch_search_filters_baths() {
				
	// get info about baths from the database
	$baths = rentfetch_get_meta_values( 'baths', 'floorplans' );
	$baths = array_unique( $baths );
	asort( $baths );
			
	// build the baths search
	echo '<fieldset class="baths">';
		echo '<legend>Baths</legend>';
		echo '<button class="toggle">Baths</button>';
		echo '<div class="input-wrap checkboxes inactive">';
				
				foreach( $baths as $bath ) {
					
					// Check if the amenity's term ID is in the GET parameter array
					$checked = in_array($bath, $_GET['search-baths'] ?? array());
					
					// skip if there's a null value for bath
					if ( $bath === null || $bath == 0 )
						continue;
						
					// $label = $bath . ' Bathroom';
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
						$bath, 
						$bath,
						$checked ? 'checked' : '', // Apply checked attribute 
						$label
					);
				}
			echo '</div>'; // .checkboxes
	echo '</fieldset>';
		
}

add_filter('rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_baths', 10, 1);
function rentfetch_search_floorplans_args_baths( $floorplans_args ) {
		
	if ( isset( $_POST['search-baths'] ) && is_array( $_POST['search-baths'] ) ) {
		
		// Get the values
		$baths = $_POST['search-baths'];
		
		// Escape the values
		$baths = array_map( 'sanitize_text_field', $baths );
		
		// Convert the baths query to a meta query
		$meta_query = array(
			array(
				'key' => 'baths',
				'value' => $baths,
			),
		);
				
		// Add the meta query to the property args
		$floorplans_args['meta_query'][] = $meta_query;
				
	}
	
	return $floorplans_args;
}
