<?php

function rentfetch_search_filters_date() {
			
	// enqueue date picker scripts
	wp_enqueue_style( 'rentfetch-flatpickr-style' );
	wp_enqueue_script( 'rentfetch-flatpickr-script' );
	wp_enqueue_script( 'rentfetch-flatpickr-script-init' );
	
	// build the date-based search
	echo '<fieldset class="move-in">';
		echo '<legend>Move-In Date</legend>';
		echo '<button class="toggle">Move-In Date</button>';
		echo '<div class="input-wrap text">';
			echo '<input type="date" name="dates" placeholder="Available date" style="width:auto;" data-input>';
		echo '</div>'; // .checkboxes
	echo '</fieldset>';
		
	
}

add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_date', 10, 1 );
function rentfetch_search_floorplans_args_date( $floorplans_args ) {
	
	// bail if we don't have a date search
	if ( !isset( $_POST['dates'] ) )
		return $floorplans_args;
	   
	// get the dates, in a format like this: 'YYYYMMDD to YYYYMMDD'
	$datestring = sanitize_text_field( $_POST['dates'] );

	// get the dates into an array
	$dates = explode( ' to ', $datestring  );
	
	// typical use, we have two dates, a start and end
	if ( count( $dates ) == 2 ) {
				
		// do a between query against the availability dates
		$floorplans_args['meta_query'][] = array(
			array(
				'key' => 'availability_date',
				'value' => array( $dates[0], $dates[1] ),
				'type' => 'numeric',
				'compare' => 'BETWEEN',
			)
		);
		
	// or we might just have one date, which we'll treat as an end
	} elseif ( count( $dates ) == 1 && !empty( $dates[0] ) ) {
		
		$yesterday = date('Ymd',strtotime("-1 days"));
					
		// do a between query between yesterday and the date entered
		$floorplans_args['meta_query'][] = array(
			array(
				'key' => 'availability_date',
				'value' => array( $yesterday, $dates[0] ),
				'type' => 'numeric',
				'compare' => 'BETWEEN',
			)
		);
		
	// no date is set, so let's not make that part of the query; fall back to available units
	} else {
					
		// if the date is anything else, then we need to only pick up floorplans that have more than 0 units available
		$property_availability_display = $price_settings = get_option( 'options_property_availability_display', 'options' );
		if ( $property_availability_display != 'all' ) {
			$floorplans_args['meta_query'][] = array(
				array(
					'key' => 'available_units',
					'value' => 0,
					'compare' => '>'
				)
			);
		}
		
	}
		
	
	return $floorplans_args;
}