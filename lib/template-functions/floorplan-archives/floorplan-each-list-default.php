<?php

function rentfetch_floorplans_each_list_default() {
	
	$title = rentfetch_get_floorplan_title();
	$beds = rentfetch_get_floorplan_bedrooms();
	$baths = rentfetch_get_floorplan_bathrooms();
	$square_feet = rentfetch_get_floorplan_square_feet();
	$available_units = rentfetch_get_floorplan_available_units();
	$links = rentfetch_get_floorplan_links();
	$pricing = rentfetch_get_floorplan_pricing();     
	$units_count = rentfetch_get_floorplan_units_count(); 
	
	echo '<div class="images-column">';
		do_action( 'rentfetch_do_floorplan_images' );
	echo '</div>';
	echo '<div class="content-column">';
		
		if ( $title )
			printf( '<h4>%s</h4>', $title );
		
		echo '<div class="floorplan-attributes">';
		
			if ( $beds )
				printf( '<p class="beds">%s</p>', $beds );
			
			if ( $baths )
				printf( '<p class="baths">%s</p>', $baths );
						
			if ( $square_feet )
				printf( '<p class="square-feet">%s</p>', $square_feet );
			
			if ( $pricing )
				printf( '<p class="pricing">%s</p>', $pricing );
					
		echo '</div>';
		
		if ( $units_count > 0 ) {
			printf( '<p class="availability">%s</p>', $available_units );
						
			$floorplan_id = get_post_meta( get_the_ID(), 'floorplan_id', true );
			$property_id = get_post_meta( get_the_ID(), 'property_id', true );
			
			$args = array(
				'post_type' => 'units',
				'posts_per_page' => -1,
				'orderby' => 'meta_value_num',
				'order' => 'ASC',
				'meta_query' => array(
					array(
						'key'   => 'property_id',
						'value' => $property_id,
					),
					array(
						'key'   => 'floorplan_id',
						'value' => $floorplan_id,
					),
				),
			);
			
			// The Query
			$units_query = new WP_Query( $args );

			// The Loop
			if ( $units_query->have_posts() ) {
				
				echo '<details>';
					echo '<summary class="rentfetch-button">View Availability <span class="dropdown"></span></summary>';
					
					 echo '<table class="unit-details">';
							echo '<tr>';
								echo '<th class="unit-title">Apt #</th>';
								// echo '<th class="unit-floor">Floor</th>';
								echo '<th class="unit-starting-at">Starting At</th>';
								echo '<th class="unit-deposit">Deposit</th>';
								echo '<th class="unit-availability">Date Available</th>';
								echo '<th class="unit-tour-video"></th>';
								echo '<th class="unit-buttons"></th>';
							echo '<tr>';

					while ( $units_query->have_posts() ) {
						
						$units_query->the_post();
						
						do_action( 'rentfetch_units_do_table_row' );

					}
					
					echo '</table>';
				
				echo '</details>';
				
			} else {
				// no posts found
			}
			
		}
	
	echo '</div>'; // .content-column
	
}
add_action( 'rentfetch_single_properties_do_floorplans_each', 'rentfetch_floorplans_each_list_default' );


function rentfetch_floorplan_list_units_each() {
	
	$title = rentfetch_get_floorplan_title();
	$pricing = rentfetch_get_unit_pricing();
	$deposit = rentfetch_get_unit_deposit();
	$availability_date = rentfetch_get_unit_availability_date();
	$floor = null;
	$tour_video = null;
	
	echo '<tr>';
		printf( '<td class="unit-title">%s</td>', $title );
		// printf( '<td class="unit-floor">%s</td>', $floor );
		printf( '<td class="unit-starting-at">%s</td>', $pricing );
		printf( '<td class="unit-deposit">%s</td>', $deposit );
		printf( '<td class="unit-availability">%s</td>', $availability_date );
		printf( '<td class="unit-tour-video">%s</td>', $tour_video );
		echo '<td class="unit-buttons">';
			do_action( 'rentfetch_do_unit_button' );
		echo '</td>';
	echo '<tr>';
	
	
	
}
add_action( 'rentfetch_units_do_table_row', 'rentfetch_floorplan_list_units_each' );