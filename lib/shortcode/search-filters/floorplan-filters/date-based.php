<?php
/**
 * Date-based filter
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the form markup for the availability date
 *
 * @return void.
 */
function rentfetch_search_filters_date() {

	$date = null;

	if ( isset( $_GET['dates'] ) && $_GET['dates'] > 0 ) {
		$date = sanitize_text_field( wp_unslash( $_GET['dates'] ) );
	}

	// build the date-based search.
	echo '<fieldset class="move-in">';
		echo '<legend>Move-In Date</legend>';
		echo '<button class="toggle">Move-In Date</button>';
		echo '<div class="input-wrap inactive">';
			printf( '<input type="date" value="%s" name="dates" placeholder="Available date" style="width:auto;" data-input />', esc_html( $date ) );
		echo '</div>'; // .input-wrap.
	echo '</fieldset>';
}

/**
 * Add the date-based filter to the search filters
 *
 * @param array $floorplans_args The floorplan arguments.
 *
 * @return array.
 */
function rentfetch_search_floorplans_args_date( $floorplans_args ) {

	if ( ! isset( $_POST['dates'] ) ) {
		return $floorplans_args;
	}

	if ( empty( $_POST['dates'] ) ) {
		return $floorplans_args;
	}

	$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_frontend_nonce_action' ) ) {
		die( 'Nonce verification failed (date-based search)' );
	}

	$date = sanitize_text_field( wp_unslash( $_POST['dates'] ) );

	// get the dates, in a format like this: 'YYYYMMDD to YYYYMMDD'.
	$yesterday  = gmdate( 'Ymd', strtotime( '-30 days' ) );
	$datestring = gmdate( 'Ymd', strtotime( sanitize_text_field( $date ) ) );

	// do a between query between yesterday and the date entered.
	$floorplans_args['meta_query'][] = array(
		array(
			'key'     => 'availability_date',
			'value'   => array( $yesterday, $datestring ),
			'type'    => 'numeric',
			'compare' => 'BETWEEN',
		),
	);

	return $floorplans_args;
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_date', 10, 1 );
