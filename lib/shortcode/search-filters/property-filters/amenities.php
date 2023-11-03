<?php

function rentfetch_search_filters_amenities() {
					
	//* figure out how many amenities to show
	$number_of_amenities_to_show = get_option( 'options_number_of_amenities_to_show' );
	
	//* get information about amenities from the database
	$amenities = get_terms( 
		array(
			'taxonomy'      => 'amenities',
			'hide_empty'    => true,
			'number'        => $number_of_amenities_to_show,
			'orderby'       => 'count',
			'order'         => 'DESC',
		),
	);
		
	//* Build amenities search
	if (!empty($amenities) && taxonomy_exists('amenities')) {
		
		echo '<fieldset class="amenities">';
			echo '<legend>Amenities</legend>';
			echo '<button class="toggle">Amenities</button>';
			echo '<div class="input-wrap checkboxes">';

				foreach ($amenities as $amenity) {
					$name = $amenity->name;
					$amenity_term_id = $amenity->term_id;

					// Check if the amenity's term ID is in the GET parameter array
					$checked = in_array($amenity_term_id, $_GET['search-amenities'] ?? array());

					printf(
						'<label>
							<input type="checkbox" 
								name="search-amenities[]" 
								value="%s" 
								data-amenities="%s" 
								data-amenities-name="%s" 
								%s /> <!-- Add checked attribute if necessary -->
							<span>%s</span>
						</label>',
						$amenity_term_id,
						$amenity_term_id,
						$name,
						$checked ? 'checked' : '', // Apply checked attribute
						$name
					);
				}

			echo '</div>'; // .checkboxes
		echo '</fieldset>';
	}
}

add_filter( 'rentfetch_search_property_map_properties_query_args', 'rentfetch_search_properties_args_amenities', 10, 1 );
function rentfetch_search_properties_args_amenities( $property_args ) {
	
	if ( isset( $_POST['search-amenities'] ) && is_array( $_POST['search-amenities'] ) ) {
		
		// Get the values
		$amenities = $_POST['search-amenities'];
		
		// Escape the values
		$amenities = array_map( 'sanitize_text_field', $amenities );
		
		// This is an "AND" query, where we want posts to match ALL of the specified amenities
		$amenities_query = array(
			'relation' => 'AND',
		);

		foreach ( $amenities as $amenity ) {
			$amenities_query[] = array(
				'taxonomy' => 'amenities',
				'terms' => $amenity,
			);
		}
		
		// Add the amenities query to the property args tax query
		$property_args['tax_query'][] = $amenities_query;
	}
		
	return $property_args;
}
