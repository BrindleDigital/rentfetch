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

	// Ensure tooltip behavior is available for pricing/fees in AJAX-loaded results.
	wp_enqueue_script( 'rentfetch-tooltip' );
	
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
 * @param  array $atts  the attributes passed to the shortcode.
 * @return string  the markup for the property search filters.
 */
function rentfetch_propertysearchfilters( $atts ) {

	ob_start();

	// enqueue the search properties ajax script
	wp_enqueue_script( 'rentfetch-search-properties-ajax' );

	// Add inline script with REST API URL and shortcode attributes
	if ( ! wp_script_is( 'rentfetch-search-properties-ajax', 'done' ) ) {
		$inline_script = sprintf(
			'var rentfetchPropertySearch = { restUrl: %s, shortcodeAttributes: %s };',
			wp_json_encode( rest_url( 'rentfetch/v1/search/properties' ) ),
			wp_json_encode( $atts ?: array() )
		);
		wp_add_inline_script( 'rentfetch-search-properties-ajax', $inline_script, 'before' );
	}

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
		printf( '<form class="property-search-filters" id="filter">' );

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
 * Render the property query results and return the markup as a string.
 *
 * @param array $property_args WP_Query args for properties.
 * @return string HTML markup for the properties results.
 */
function rentfetch_render_property_query_results( $property_args ) {
	ob_start();

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

			$classes_array = get_post_class();
			$classes_array = apply_filters( 'rentfetch_filter_properties_post_classes', $classes_array );
			$class = implode( ' ', $classes_array );

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

	return ob_get_clean();
}
