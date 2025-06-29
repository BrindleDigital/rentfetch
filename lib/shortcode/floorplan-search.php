<?php
/**
 * Floorplans search
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the default layout for the floorplan search
 *
 * @param  array $atts  the attributes passed to the shortcode.
 * @return string       the markup for the floorplan search.
 */
function rentfetch_floorplan_search_default_layout( $atts ) {

	ob_start();

	// because these are loaded over ajax, we need to enqueue the lightbox scripts here (they're enqueue automatically when loaded normally).
	wp_enqueue_style( 'rentfetch-glightbox-style' );
	wp_enqueue_script( 'rentfetch-glightbox-script' );
	wp_enqueue_script( 'rentfetch-glightbox-init' );

	// get the attributes so that we can pass them to the child shortcodes.
	$string_atts = '';

	if ( $atts ) {
		foreach ( $atts as $key => $value ) {
			$string_atts .= ' ' . $key . '=' . $value;
		}
	}
	
	do_action( 'rentfetch_before_floorplans_search' );

	// * Our container markup for the results
	echo '<div class="rent-fetch-floorplan-search-default-layout">';

		// create the first shortcode.
		$floorplansearchfilters_shortcode = sprintf( '[rentfetch_floorplansearchfilters %s]', $string_atts );
		echo do_shortcode( $floorplansearchfilters_shortcode );

		// create the second shortcode.
		$floorplansearchresults_shortcode = sprintf( '[rentfetch_floorplansearchresults %s]', $string_atts );
		echo do_shortcode( $floorplansearchresults_shortcode );

		printf( '<form class="floorplan-search-filters" action="%s/wp-admin/admin-ajax.php" method="POST" id="filter">', esc_url( site_url() ) );

			// Add a nonce field so we can check for it later.
			$nonce = wp_create_nonce( 'rentfetch_frontend_nonce_action' );
			printf( '<input type="hidden" name="rentfetch_frontend_nonce_field" value="%s">', esc_attr( $nonce ) );

			echo '<input type="hidden" name="action" value="floorplansearch">';

			// This is the hook where we add all of our actions for the search filters.
			do_action( 'rentfetch_do_search_floorplans_filters' );

		echo '</form>';

	echo '</div>';
	
	do_action( 'rentfetch_after_floorplans_search' );

	return ob_get_clean();
}
add_shortcode( 'rentfetch_floorplansearch', 'rentfetch_floorplan_search_default_layout' );

/**
 * Output the floorplan search filters
 *
 * @param  array $atts  the attributes passed to the shortcode.
 * @return string       the markup for the floorplan search filters.
 */
function rentfetch_floorplansearchfilters( $atts ) {

	ob_start();

	// enqueue the search floorplans ajax script.
	wp_enqueue_script( 'rentfetch-search-floorplans-ajax' );

	if ( $atts ) {
		wp_localize_script( 'rentfetch-search-floorplans-ajax', 'shortcodeAttributes', $atts );
	}
	
	// remove the taxonomy filter if there's a shortcode attribute for it (which hard-sets it and should disable selection).
	if ( isset( $atts['taxonomy'] ) ) {
		if ( 'floorplancategory' === $atts['taxonomy'] ) {
			remove_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_floorplan_categories' );
		}
		if ( 'floorplantype' === $atts['taxonomy'] ) {
			remove_action( 'rentfetch_do_search_floorplans_filters', 'rentfetch_search_filters_floorplan_types' );
		}
	}

	// needed for toggling the featured filters on and off.
	wp_enqueue_script( 'rentfetch-floorplan-search-featured-filters-toggle' );

	echo '<div class="filters-wrap">';
		echo '<div id="featured-filters">';
			do_action( 'rentfetch_do_search_floorplans_filters' );
		echo '</div>';
		echo '<div id="filter-toggles"></div>';
	echo '</div>'; // .filters-wrap.

	return ob_get_clean();
}
add_shortcode( 'rentfetch_floorplansearchfilters', 'rentfetch_floorplansearchfilters' );

/**
 * Output the floorplan search results
 *
 * @return string  the markup for the floorplan search results.
 */
function rentfetch_floorplan_search_results() {

	ob_start();

	echo '<div id="response"></div>';

	return ob_get_clean();
}
add_shortcode( 'rentfetch_floorplansearchresults', 'rentfetch_floorplan_search_results' );

/**
 * Filter the floorplans
 *
 * @return void
 */
function rentfetch_filter_floorplans() {

	// * The base floorplan query
	$floorplan_args = array(
		'post_type'      => 'floorplans',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	);

	$floorplan_args = apply_filters( 'rentfetch_search_floorplans_query_args', $floorplan_args );

	$floorplanquery = new WP_Query( $floorplan_args );

	if ( $floorplanquery->have_posts() ) {

		$numberofposts = $floorplanquery->post_count;
		printf( '<div class="results-count"><span id="floorplans-results-count-number">%s</span> results</div>', (int) $numberofposts );

		echo '<div class="floorplans-loop">';

		while ( $floorplanquery->have_posts() ) {

			$floorplanquery->the_post();
			
			$classes_array = get_post_class();
			$classes_array = apply_filters( 'rentfetch_filter_floorplans_post_classes', $classes_array );
			$classes = implode( ' ', $classes_array );

			printf( '<div class="%s">', esc_attr( $classes ) );

				do_action( 'rentfetch_floorplans_search_do_floorplans_each' );

			echo '</div>'; // post_class.

		} // endwhile.

		echo '</div>';

		wp_reset_postdata();

	} else {
		echo 'No floorplans with availability were found matching the current search parameters.';
	}

	die();
}
add_action( 'wp_ajax_floorplansearch', 'rentfetch_filter_floorplans' );
add_action( 'wp_ajax_nopriv_floorplansearch', 'rentfetch_filter_floorplans' );
