<?php
/**
 * Text-based search
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add the text-based search to the search filters.
 *
 * @return void.
 */
function rentfetch_search_filters_text_search() {

	// check the query to see if we have a text-based search.
	if ( isset( $_GET['textsearch'] ) ) {
		$searchtext = sanitize_text_field( wp_unslash( $_GET['textsearch'] ) );
	} else {
		$searchtext = null;
	}

	$placeholder = apply_filters( 'rentfetch_search_placeholder_text', 'Search...' );

	// build the text-based search.
	echo '<fieldset class="text-based-search">';
		echo '<legend>Search</legend>';
		echo '<div class="input-wrap text">';
	if ( $searchtext ) {
		printf( '<input type="text" name="textsearch" placeholder="%s" class="active" value="%s" />', esc_attr( $placeholder ), esc_attr( $searchtext ) );
	} else {
		printf( '<input type="text" name="textsearch" placeholder="%s" />', esc_attr( $placeholder ) );
	}
		echo '</div>'; // .text
	echo '</fieldset>';
}

/**
 * Add the text-based search to the search filters
 *
 * @param array $property_args The property arguments.
 *
 * @return array.
 */
function rentfetch_search_properties_args_text( $property_args ) {

	$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_frontend_nonce_action' ) ) {
		die( 'Nonce verification failed' );
	}

	// * Add text-based search into the query.
	$search = null;

	if ( isset( $_POST['textsearch'] ) ) {
		$search = sanitize_text_field( wp_unslash( $_POST['textsearch'] ) );
	}

	if ( null !== $search ) {
		$property_args['s'] = $search;

		// force the site to use relevanssi if it's installed.
		if ( function_exists( 'relevanssi_truncate_index_ajax_wrapper' ) ) {
			$property_args['relevanssi'] = true;
		}
	}

	return $property_args;
}
add_filter( 'rentfetch_search_property_map_properties_query_args', 'rentfetch_search_properties_args_text', 10, 1 );
