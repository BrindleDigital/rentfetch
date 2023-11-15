<?php

function rentfetch_search_filters_order() {
				
	// get info about baths from the database
	$baths = rentfetch_get_meta_values( 'baths', 'floorplans' );
	$baths = array_unique( $baths );
	asort( $baths );
			
	// build the baths search
	echo '<fieldset class="order">';
		echo '<legend>Order</legend>';
		echo '<button class="toggle">Baths</button>';
		echo '<div class="input-wrap checkboxes inactive">';
				
				foreach( $baths as $bath ) {
					
					// Check if the amenity's term ID is in the GET parameter array
					$checked = in_array($bath, $_GET['search-order'] ?? array());
					
					// skip if there's a null value for bath
					if ( $bath === null || $bath == 0 )
						continue;
						
					// $label = $bath . ' Bathroom';
					$label = apply_filters( 'rentfetch_get_bathroom_number_label', $bath );
						
					printf( 
						'<label>
							<input type="radio" 
								name="search-order[]"
								value="%s" 
								data-order="%s" 
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

add_filter('rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_order', 10, 1);
function rentfetch_search_floorplans_args_order( $floorplans_args ) {
		
	if ( isset( $_POST['search-order'] ) && is_array( $_POST['search-order'] ) ) {
		
		// Get the values
		$baths = $_POST['search-order'];
		
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
