<?php

/**
 * Add markup for a default property search layout. 
 * We're basically just doing three shortcodes here, 
 * so this can be easily replicated if you want to customize the layout
 */
function rentfetch_propertysearch_default_layout( $atts ) {
	
	ob_start();
	
	// this script is for scrolling specifically in the context of a full-height map
	wp_enqueue_script( 'rentfetch-property-search-scroll-to-active-property' );
	
	//* Our container markup for the results
	echo '<div class="rent-fetch-property-search-default-layout">';	
		echo '<div class="filters-and-properties-container">';
			echo do_shortcode('[propertysearchfilters]');
			echo do_shortcode('[propertysearchresults]');
		echo '</div>';
		echo '<div class="map-container">';
			echo do_shortcode('[propertysearchmap]');
		echo '</div>';
	echo '</div>';

	return ob_get_clean();
}
add_shortcode( 'propertysearch', 'rentfetch_propertysearch_default_layout' );


/**
 * Add the [propertysearchfilters] shortcode
 */
function rentfetch_propertysearchfilters() {
	
	ob_start();
	
	// enqueue the search properties ajax script
	wp_enqueue_script( 'rentfetch-search-properties-ajax' );
	
	// needed for toggling the featured filters on and off
	wp_enqueue_script( 'rentfetch-property-search-featured-filters-toggle' );
	
	// script for opening and closing the dialog element
	wp_enqueue_script( 'rentfetch-property-search-filters-dialog' );
	
	// we need to do output the dialog when we're outputting this, but we don't want to do that inside this container
	add_action( 'wp_footer', 'rentfetch_propertysearch_filters_dialog' );
	
	// // Localize the search filters general script, then enqueue that
	// $search_options = array(
	// 	'maximum_bedrooms_to_search' => intval( get_option( 'options_maximum_bedrooms_to_search' ) ),
	// );
	// wp_localize_script( 'rentfetch-search-filters-general', 'searchoptions', $search_options );
	// wp_enqueue_script( 'rentfetch-search-filters-general' );
	
	echo '<div class="filters-wrap">';
		echo '<div id="featured-filters">';
			do_action( 'rentfetch_do_search_properties_featured_filters' );
			echo '<button id="open-search-filters">Filters</button>';
		echo '</div>';
		echo '<div id="filter-toggles"></div>';
   echo '</div>'; // .filters-wrap
   
	return ob_get_clean();
}
add_shortcode( 'propertysearchfilters', 'rentfetch_propertysearchfilters' );

/**
 * Outupt the dialog element for the search filters
 */
function rentfetch_propertysearch_filters_dialog() {
	echo '<dialog id="search-filters">';

		echo '<header class="property-search-filters-header">'; 
			echo '<h2>Search Filters</h2>';
		echo '</header>';
		printf( '<form class="property-search-filters" action="%s/wp-admin/admin-ajax.php" method="POST" id="filter">', site_url() );
		
			echo '<input type="hidden" name="action" value="propertysearch">';
			
			// This is the hook where we add all of our actions for the search filters
			do_action( 'rentfetch_do_search_properties_dialog_filters' );
					
		echo '</form>';
		echo '<footer class="property-search-filters-footer">';
			echo '<button id="reset">Clear All</button>';
			echo '<button id="show-properties">Show <span id="properties-found"></span> Places</button>';
		echo '</footer>';
	echo '</dialog>';
}

/**
 * Add the [propertysearchmap] shortcode
 */
function rentfetch_propertysearchmap() {
	
	ob_start();
		
	// the map itself
	$key = apply_filters( 'rentfetch_get_google_maps_api_key', null );
	wp_enqueue_script( 'rentfetch-google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $key, array(), null, true );
				
	// Localize the google maps script, then enqueue that
	$maps_options = array(
		'json_style' => json_decode( get_option( 'options_google_maps_styles' ) ),
		'marker_url' => get_option( 'options_google_map_marker' ),
		'google_maps_default_latitude' => get_option( 'options_google_maps_default_latitude' ),
		'google_maps_default_longitude' => get_option( 'options_google_maps_default_longitude' ),
	);
	
	wp_localize_script( 'rentfetch-property-map', 'options', $maps_options );
	wp_enqueue_script( 'rentfetch-property-map');
	
	echo '<div id="map"></div>';
	
	return ob_get_clean();
}
add_shortcode( 'propertysearchmap', 'rentfetch_propertysearchmap' );

/**
 * Add the [propertysearchresults] shortcode
 */
function rentfetch_propertysearchresults() {
	ob_start();
		
	echo '<div id="response"></div>';
	
	return ob_get_clean();
}
add_shortcode( 'propertysearchresults', 'rentfetch_propertysearchresults' );

/**
 * Do the property query and render outer markup for each property found in the search (if there is a location set)
 * We do this separately from the inner markup to make it easy to customize the inner markup
 */
function rentfetch_filter_properties(){
			
	$floorplans = rentfetch_get_floorplans_array();
		
	$property_ids = array_keys( $floorplans );
	if ( empty( $property_ids ) )
		$property_ids = array( '1' ); // if there aren't any properties, we shouldn't find anything – empty array will let us find everything, so let's pass nonsense to make the search find nothing
		
	// set null for $properties_posts_per_page
	$properties_maximum_per_page = get_option( 'options_maximum_number_of_properties_to_show', 100 );
	
	$orderby = apply_filters( 'rentfetch_get_property_orderby', $orderby = 'menu_order' );
	$order = apply_filters( 'rentfetch_get_property_order', $order = 'ASC' );
	
	//* The base property query
	$property_args = array(
		'post_type' => 'properties',
		'posts_per_page' => $properties_maximum_per_page,
		'orderby' => $orderby,
		'order'	=> $order, // ASC or DESC
		'no_found_rows' => true,
	);
	
	//* Add all of our property IDs into the property search
	$property_args['meta_query'] = array(
		array(
			'key' => 'property_id',
			'value' => $property_ids,
		),
	);
	
	$property_args = apply_filters( 'rentfetch_search_property_map_properties_query_args', $property_args );
	
	// console_log( 'Property search args:' );
	// console_log( $property_args );
		
	$propertyquery = new WP_Query( $property_args );
		
	if( $propertyquery->have_posts() ) {
		
		$count = 0;
				
		$numberofposts = $propertyquery->post_count;				
		printf( '<div class="results-count"><span id="properties-results-count-number">%s</span> results</div>', $numberofposts );
		
		echo '<div class="properties-loop">';

			while( $propertyquery->have_posts() ) {
				
				$propertyquery->the_post();
				
				$latitude = get_post_meta( get_the_ID(), 'latitude', true );
				$longitude = get_post_meta( get_the_ID(), 'longitude', true );
				
				// skip if there's no latitude or longitude
				if ( !$latitude || !$longitude )
					continue;
				
				$class = implode( ' ', get_post_class() );
								
				printf( 
					'<div class="%s" data-latitude="%s" data-longitude="%s" data-id="%s" data-marker-id="%s">', 
					$class, 
					$latitude, 
					$longitude,
					$count, 
					get_the_ID(), 
				);
				
					echo '<div class="property-in-list">';
						do_action( 'rentfetch_do_properties_each_list' );
					echo '</div>';
					echo '<div class="property-in-map" style="display:none;">';
						do_action( 'rentfetch_do_properties_each_map' );
					echo '</div>';
				
				echo '</div>'; // post_class
				
				$count++;
			
			} // endwhile
			
		echo '</div>';
		
		wp_reset_postdata();
		
	} else {
		echo 'No properties with availability were found matching the current search parameters.';
	}
		 
	die();
}
add_action( 'wp_ajax_propertysearch', 'rentfetch_filter_properties' ); // wp_ajax_{ACTION HERE} 
add_action( 'wp_ajax_nopriv_propertysearch', 'rentfetch_filter_properties' );

add_filter( 'rentfetch_get_property_search_query_parameter_name', 'rentfetch_property_search_query_parameter_name' ); 
function rentfetch_property_search_query_parameter_name( $query_param_id, $query_param ) {
	
	$name = $query_param_id;
	
	return $name;	
}
