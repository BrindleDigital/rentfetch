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

		echo '<form class="floorplan-search-filters" id="filter">';

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

	// Add inline script with REST API URL and shortcode attributes
	if ( ! wp_script_is( 'rentfetch-search-floorplans-ajax', 'done' ) ) {
		$inline_script = sprintf(
			'var rentfetchFloorplanSearch = { restUrl: %s, shortcodeAttributes: %s };',
			wp_json_encode( rest_url( 'rentfetch/v1/search/floorplans' ) ),
			wp_json_encode( $atts ?: array() )
		);
		wp_add_inline_script( 'rentfetch-search-floorplans-ajax', $inline_script, 'before' );
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
 * Render floorplan query results markup
 *
 * @param array $floorplan_args WP_Query args for floorplans.
 * @return string HTML markup for the floorplan results.
 */
function rentfetch_render_floorplan_query_results( $floorplan_args ) {
	ob_start();

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

	return ob_get_clean();
}

/**
 * Filter the floorplans
 *
 * @return void
 */
function rentfetch_filter_floorplans() {

	// Verify nonce for security
	$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'rentfetch_frontend_nonce_action' ) ) {
		wp_send_json_error( array( 'message' => 'Security verification failed. Please refresh the page and try again.' ) );
		wp_die();
	}

	// Get a list of the possible properties to show from the shortcode attributes.
	$referring_page_id = url_to_postid( wp_get_referer() );
	$atts = rentfetch_get_shortcode_attributes( 'rentfetch_floorplansearch', $referring_page_id );

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

	// Build a cache key from the floorplan args and shortcode atts so different filters cache separately.
	$cache_key = 'rentfetch_floorplansearch_markup_' . md5( wp_json_encode( array( 'args' => $floorplan_args, 'atts' => $atts ) ) );
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		$cached_markup = get_transient( $cache_key );
		if ( false !== $cached_markup && is_string( $cached_markup ) ) {
			echo $cached_markup;
			die();
		}
	}

	// Render and cache the results.
	$markup = rentfetch_render_floorplan_query_results( $floorplan_args );
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		set_transient( $cache_key, $markup, 30 * MINUTE_IN_SECONDS );
	}

	echo $markup;

	die();
}
add_action( 'wp_ajax_floorplansearch', 'rentfetch_filter_floorplans' );
add_action( 'wp_ajax_nopriv_floorplansearch', 'rentfetch_filter_floorplans' );
