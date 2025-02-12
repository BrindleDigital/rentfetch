<?php
/**
 * Property search
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Do the default layout for the property search
 *
 * @param  array $atts  the attributes passed to the shortcode.
 *
 * @return string       the markup for the property search.
 */
function rentfetch_propertysearch_default_layout( $atts ) {
	
	ob_start();

	$a = shortcode_atts(
		array(
			'propertyids' => '',
		),
		$atts,
		'rentfetch_propertysearch'
	);

	// Get the attributes so that we can pass them to the child shortcodes.
	$string_atts = '';

	if ( $a ) {
		foreach ( $a as $key => $value ) {
			$string_atts .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
		}
	}

	// this script is for scrolling specifically in the context of a full-height map.
	wp_enqueue_script( 'rentfetch-property-search-scroll-to-active-property' );
	
	// because these are loaded over ajax, we need to enqueue the lightbox scripts here (they're enqueue automatically when loaded normally).
	wp_enqueue_style( 'rentfetch-glightbox-style' );
	wp_enqueue_script( 'rentfetch-glightbox-script' );
	wp_enqueue_script( 'rentfetch-glightbox-init' );
	
	// * Our container markup for the results
	echo '<div class="rent-fetch-property-search-default-layout">';
		echo '<div class="filters-and-properties-container">';
			echo do_shortcode( '[rentfetch_propertysearchfilters ' . $string_atts . ']' );
			echo do_shortcode( '[rentfetch_propertysearchresults ' . $string_atts . ']' );
		echo '</div>';
		echo '<div class="map-container">';
			echo do_shortcode( '[rentfetch_propertysearchmap ' . $string_atts . ']' );
		echo '</div>';
	echo '</div>';

	return ob_get_clean();
}
add_shortcode( 'rentfetch_propertysearch', 'rentfetch_propertysearch_default_layout' );

/**
 * Add the [propertysearchfilters] shortcode
 *
 * @return string  the markup for the property search filters.
 */
function rentfetch_propertysearchfilters() {

	ob_start();

	// enqueue the search properties ajax script.
	wp_enqueue_script( 'rentfetch-search-properties-ajax' );

	// needed for toggling the featured filters on and off.
	wp_enqueue_script( 'rentfetch-property-search-featured-filters-toggle' );

	// script for opening and closing the dialog element.
	wp_enqueue_script( 'rentfetch-property-search-filters-dialog' );

	// we need to do output the dialog when we're outputting this, but we don't want to do that inside this container.
	add_action( 'wp_footer', 'rentfetch_propertysearch_filters_dialog' );

	echo '<div class="filters-wrap">';
		echo '<div id="featured-filters">';
			do_action( 'rentfetch_do_search_properties_featured_filters' );
			echo '<button type="button" id="open-search-filters">Filters</button>';
		echo '</div>';
		echo '<div id="filter-toggles"></div>';
	echo '</div>'; // .filters-wrap.

	return ob_get_clean();
}
add_shortcode( 'rentfetch_propertysearchfilters', 'rentfetch_propertysearchfilters' );

/**
 * Output the dialog for the property search filters
 *
 * @return void.
 */
function rentfetch_propertysearch_filters_dialog() {
	echo '<dialog id="search-filters">';

		echo '<header class="property-search-filters-header">';
			echo '<h2>Search Filters</h2>';
		echo '</header>';
		printf( '<form class="property-search-filters" action="%s/wp-admin/admin-ajax.php" method="POST" id="filter">', esc_url( site_url() ) );

			// Add the action to the form.
			echo '<input type="hidden" name="action" value="propertysearch">';

			// Add a nonce field so we can check for it later.
			$nonce = wp_create_nonce( 'rentfetch_frontend_nonce_action' );
			printf( '<input type="hidden" name="rentfetch_frontend_nonce_field" value="%s">', esc_attr( $nonce ) );

			// This is the hook where we add all of our actions for the search filters.
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
 *
 * @return string  the markup for the property search map.
 */
function rentfetch_propertysearchmap() {

	ob_start();

	wp_enqueue_script( 'rentfetch-property-map' );

	echo '<div id="map"></div>';

	return ob_get_clean();
}
add_shortcode( 'rentfetch_propertysearchmap', 'rentfetch_propertysearchmap' );

/**
 * Add the [propertysearchresults] shortcode
 *
 * @return string  the markup for the property search results.
 */
function rentfetch_propertysearchresults() {
	ob_start();

	echo '<div id="response"></div>';

	return ob_get_clean();
}
add_shortcode( 'rentfetch_propertysearchresults', 'rentfetch_propertysearchresults' );

/**
 * AJAX handler for the property search
 *
 * @return void
 */
function rentfetch_filter_properties() {

	$floorplans = rentfetch_get_floorplans_array();

	$property_ids = array_keys( $floorplans );
	if ( empty( $property_ids ) ) {
		$property_ids = array( '1' ); // if there aren't any properties, we shouldn't find anything – empty array will let us find everything, so let's pass nonsense to make the search find nothing.
	}
	
	// Get a list of the possible properties to show from the shortcode attributes.
	$referring_page_id = url_to_postid( wp_get_referer() );
	$atts = rentfetch_get_shortcode_attributes( 'rentfetch_propertysearch', $referring_page_id );
	
	
	// set -1 for $properties_posts_per_page if it's not set.
	$properties_maximum_per_page = get_option( 'rentfetch_options_maximum_number_of_properties_to_show' );
	if ( 0 === $properties_maximum_per_page ) {
		$properties_maximum_per_page = -1;
	}

	// * The base property query.
	$property_args = array(
		'post_type'      => 'properties',
		'posts_per_page' => $properties_maximum_per_page,
		'no_found_rows'  => true,
		'post_status' => 'publish',
	);

	$display_availability = get_option( 'rentfetch_options_property_availability_display' );
	if ( 'all' !== $display_availability ) {
		
		// If we have a propertyids attribute, use the intersection of that and the $property_ids array.
		if ( isset( $atts['propertyids'] ) ) {
			$property_ids = array_intersect( $property_ids, explode( ',', $atts['propertyids'] ) );
		}

		// * Add all of our property IDs into the property search
		$property_args['meta_query'] = array(
			array(
				'key'   => 'property_id',
				'value' => $property_ids,
			),
		);

	} else {
		if ( isset( $atts['propertyids'] ) ) {
			$property_ids = explode( ',', $atts['propertyids'] );
		}
		
		// * Add all of our property IDs into the property search
		$property_args['meta_query'] = array(
			array(
				'key'   => 'property_id',
				'value' => $property_ids,
			),
		);
	}

	$property_args = apply_filters( 'rentfetch_search_property_map_properties_query_args', $property_args );

	$propertyquery = new WP_Query( $property_args );

	if ( $propertyquery->have_posts() ) {

		$count = 0;

		$numberofposts = $propertyquery->post_count;
		printf( '<div class="results-count"><span id="properties-results-count-number">%s</span> results</div>', (int) $numberofposts );

		echo '<div class="properties-loop">';

		while ( $propertyquery->have_posts() ) {

			$propertyquery->the_post();

			$latitude  = get_post_meta( get_the_ID(), 'latitude', true );
			$longitude = get_post_meta( get_the_ID(), 'longitude', true );

			// skip if there's no latitude or longitude.
			if ( ! $latitude || ! $longitude ) {
				continue;
			}

			$class = implode( ' ', get_post_class() );

			printf(
				'<div class="%s" data-latitude="%s" data-longitude="%s" data-id="%s" data-marker-id="%s">',
				esc_attr( $class ),
				esc_attr( $latitude ),
				esc_attr( $longitude ),
				(int) $count,
				(int) get_the_ID(),
			);

				echo '<div class="property-in-list">';
					do_action( 'rentfetch_do_properties_each_list' );
				echo '</div>';
				echo '<div class="property-in-map" style="display:none;">';
					do_action( 'rentfetch_do_properties_each_map' );
				echo '</div>';

			echo '</div>'; // post_class.

			++$count;

		} // endwhile.

		echo '</div>';

		wp_reset_postdata();

	} else {
		echo 'No properties with availability were found matching the current search parameters.';
	}

	die();
}
add_action( 'wp_ajax_propertysearch', 'rentfetch_filter_properties' ); // wp_ajax_{ACTION HERE}.
add_action( 'wp_ajax_nopriv_propertysearch', 'rentfetch_filter_properties' );
