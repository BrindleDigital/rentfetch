<?php

/**
 * Output the floorplans section
 *
 */
function rentfetch_single_properties_parts_floorplans() {
	
	$maybe_do_floorplans = apply_filters( 'rentfetch_maybe_do_property_part_floorplans', true );    
	if ( $maybe_do_floorplans !== true )
		return;
	
	echo '<div id="floorplans" class="single-properties-section">';
		echo '<div class="wrap">';
		
		echo '<h2>Floorplans</h2>';
		
		global $post;
		
		$id = esc_attr( get_the_ID() );
		$property_id = esc_attr( get_post_meta( $id, 'property_id', true ) );
		
		// grab the gravity forms lightbox, if enabled on this page
		// do_action( 'rentfetch_do_gform_lightbox' );
		
		// get the possible values for the beds
		$beds = rentfetch_get_meta_values( 'beds', 'floorplans' );
		$beds = array_unique( $beds );
		asort( $beds );
		
			// loop through each of the possible values, so that we can do markup around that
			foreach( $beds as $bed ) {
				
				$args = array(
					'post_type' => 'floorplans',
					'posts_per_page' => -1,
					'orderby' => 'meta_value_num',
					'meta_key' => 'beds',
					'order' => 'ASC',
					'meta_query' => array(
						array(
							'key'   => 'property_id',
							'value' => $property_id,
						),
						array(
							'key' => 'beds',
							'value' => $bed,
						),
					),
				);
				
				$floorplans_query = new WP_Query( $args );
					
				if ( $floorplans_query->have_posts() ) {
					// echo '<details open>';
						// echo '<summary><h3>';
						
						echo '<div class="floorplan-group">';
												
							echo '<h3>';                 
								echo apply_filters( 'rentfetch_get_bedroom_number_label', $bed );
							echo '</h3>';
							// echo '</h3></summary>';
							echo '<div class="floorplans-in-archive">';
							
								while ( $floorplans_query->have_posts() ) : $floorplans_query->the_post(); 
								
									$class = implode( ' ', get_post_class() );
							
									printf( '<div class="%s">', $class );
									
										do_action( 'rentfetch_single_properties_do_floorplans_each' );
									
									echo '</div>'; // post_class
									
								endwhile;
							echo '</div>'; // .floorplans
						
						echo '</div>';
					// echo '</details>';
					
				}
				
				wp_reset_postdata();
			}
		
		
		echo '</div>'; // .wrap
	echo '</div>'; // #floorplans
	
}

/**
 * Decide whether to output the floorplans section
 */
add_filter( 'rentfetch_maybe_do_property_part_floorplans', 'rentfetch_maybe_property_part_floorplans' );
function rentfetch_maybe_property_part_floorplans() {
	
	// bail if this section is not enabled
	$property_components = get_option( 'rentfetch_options_single_property_components' );
	if ( !is_array( $property_components ) || !in_array( 'floorplans_display', $property_components ) )
		return false;
		
	// bail if this property doesn't have any floorplans
	$floorplans = get_posts( array(
		'post_type' => 'floorplans',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => 'property_id',
				'value' => get_post_meta( get_the_ID(), 'property_id', true ),
				'compare' => '='
			)
		)
	) );
	
	if ( !$floorplans )
		return false;
		
	// bail if this property doesn't have an ID
	if ( !get_post_meta( get_the_ID(), 'property_id', true ) )
		return false;
		
	return true;
}

function rentfetch_single_properties_parts_subnav_floorplans() {
	$maybe_do_floorplans = apply_filters( 'rentfetch_maybe_do_property_part_floorplans', true );
	if ( $maybe_do_floorplans === true ) {
		$label = apply_filters( 'rentfetch_floorplans_display_subnav_label', 'Floorplans' );
		printf( '<li><a href="#floorplans">%s</a></li>', $label );
	}
}