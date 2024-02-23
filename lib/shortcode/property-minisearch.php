<?php
/**
 * Mini search
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the minisearch
 *
 * @return string  the markup for the property minisearch.
 */
function rentfetch_minisearch( $atts ) {
	ob_start();
	
	// get the args for the search properties shortcode.
	$args = shortcode_atts( array(
		'url' => null,
	), $atts );

	// enqueue the search properties ajax script.
	// wp_enqueue_script( 'rentfetch-search-properties-ajax' );

	// needed for toggling the featured filters on and off.
	wp_enqueue_script( 'rentfetch-property-search-featured-filters-toggle' );

	// script for opening and closing the dialog element.
	// wp_enqueue_script( 'rentfetch-property-search-filters-dialog' );

	// we need to do output the dialog when we're outputting this, but we don't want to do that inside this container.
	// add_action( 'wp_footer', 'rentfetch_propertysearch_filters_dialog' );
	
	
	$url = $args['url'];

	printf( '<form class="minisearch" action="%s">', esc_attr( $url ) );
		echo '<div class="filters-wrap">';
			echo '<div id="featured-filters">';
				do_action( 'rentfetch_do_search_properties_featured_filters' );
				// echo '<button type="button" id="open-search-filters">Filters</button>';
				echo '<button type="submit" id="minisearch-submit">Search</button>';
			echo '</div>';
			// echo '<div id="filter-toggles"></div>';
		echo '</div>'; // .filters-wrap.
	echo '</form>'; // .minisearch.
	
	return ob_get_clean();
}
add_shortcode( 'rentfetch_minisearch', 'rentfetch_minisearch');
