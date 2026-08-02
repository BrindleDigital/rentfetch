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
 * @param array $atts Shortcode attributes.
 * @return string  the markup for the property minisearch.
 */
function rentfetch_minisearch( $atts ) {
	ob_start();

	// get the args for the search properties shortcode.
	$args = shortcode_atts(
		array(
			'url' => null,
		),
		$atts
	);

	// needed for toggling the featured filters on and off.
	wp_enqueue_script( 'rentfetch-property-search-featured-filters-toggle' );

	$url = $args['url'];

	printf( '<form class="minisearch" action="%s">', esc_attr( $url ) );
		echo '<div class="filters-wrap">';
			echo '<div id="featured-filters">';
				do_action( 'rentfetch_do_search_properties_featured_filters' );
				echo '<button type="submit" id="minisearch-submit">Search</button>';
			echo '</div>';
		echo '</div>'; // .filters-wrap.
	echo '</form>'; // .minisearch.

	return ob_get_clean();
}
add_shortcode( 'rentfetch_minisearch', 'rentfetch_minisearch' );
