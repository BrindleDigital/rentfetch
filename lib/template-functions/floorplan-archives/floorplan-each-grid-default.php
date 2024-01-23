<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function rentfetch_floorplans_each_grid_default() {
	
	$title = rentfetch_get_floorplan_title();
	$beds = rentfetch_get_floorplan_bedrooms();
	$baths = rentfetch_get_floorplan_bathrooms();
	$square_feet = rentfetch_get_floorplan_square_feet();
	$available_units = rentfetch_get_floorplan_available_units();
	$links = rentfetch_get_floorplan_links();
	$pricing = rentfetch_get_floorplan_pricing();
	$units_count = rentfetch_get_floorplan_units_count_from_meta();
	$specials = rentfetch_get_floorplan_specials();
	$tour = rentfetch_get_floorplan_tour();
	
	do_action( 'rentfetch_do_floorplan_images' );
	
	if ( $specials )
		printf( '<p class="specials">%s</p>', esc_html( $specials ) );
	
	if ( $tour )
		echo $tour;
	
	echo '<div class="floorplan-details">';
		echo '<div class="floorplan-content">';
		
			if ( $title )
				printf( '<h3>%s</h3>', $title );
			
			echo '<div class="floorplan-attributes">';
			
				if ( $beds )
					printf( '<p class="beds">%s</p>', $beds );
				
				if ( $baths )
					printf( '<p class="baths">%s</p>', $baths );
							
				if ( $square_feet )
					printf( '<p class="square-feet">%s</p>', $square_feet );
						
			echo '</div>';
		
		echo '</div>'; // .floorplan-content
		
		// show this if there are units or pricing (if there's nothing at all to say, don't show this section)
		if ( $units_count > 0 || $pricing ) {
			
			echo '<div class="floorplan-availability">';
			
				printf( '<p class="pricing">%s</p>', $pricing );
				printf( '<p class="availability">%s</p>', $available_units );
					
			echo '</div>'; // .floorplan-availability
			
		}
		
		if ( $links )
			echo $links;
		
		edit_post_link( 'Edit floorplan' );
		
	echo '</div>'; // .floorplan-details
	
}
add_action( 'rentfetch_floorplans_search_do_floorplans_each', 'rentfetch_floorplans_each_grid_default' );
add_action( 'rentfetch_do_each_floorplan_in_archive', 'rentfetch_floorplans_each_grid_default' );