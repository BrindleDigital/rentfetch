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
			
	// enqueue date picker scripts
	// wp_enqueue_style( 'rentfetch-flatpickr-style' );
	// wp_enqueue_script( 'rentfetch-flatpickr-script' );
	// wp_enqueue_script( 'rentfetch-flatpickr-script-init' );
	
	$date = null;
	
	if ( isset( $_GET['dates'] ) && $_GET['dates'] > 0 )
		$date = sanitize_text_field( $_GET['dates'] );
	
	// build the date-based search
	echo '<fieldset class="move-in">';
		echo '<legend>Move-In Date</legend>';
		echo '<button class="toggle">Move-In Date</button>';
		echo '<div class="input-wrap inactive">';
			printf( '<input type="date" value="%s" name="dates" placeholder="Available date" style="width:auto;" data-input />', $date );
		echo '</div>'; // .input-wrap
	echo '</fieldset>';
}

function rentfetch_search_floorplans_args_date( $floorplans_args ) {
	
	// TODO: this filter currently controls whether we do an availability search or not; if possible this should maybe move to a separate filter
		
	if ( !isset( $_POST['dates'] ) )
		return $floorplans_args;
	
	if ( empty( $_POST['dates'] ) )
		return $floorplans_args;
	
	$date = $_POST['dates'];
	
	// get the dates, in a format like this: 'YYYYMMDD to YYYYMMDD'
	$yesterday = date('Ymd',strtotime("-30 days"));
	$datestring = date('Ymd', strtotime(sanitize_text_field($date)));
					
	// do a between query between yesterday and the date entered
	$floorplans_args['meta_query'][] = array(
		'relation' => 'OR', // we need to make sure that the date is between yesterday and the selected date OR that there are already units available
		array(
			'key' => 'availability_date',
			'value' => array( $yesterday, $datestring ),
			'type' => 'numeric',
			'compare' => 'BETWEEN',
		),
		array(
			'key' => 'available_units',
			'compare' => '>',
			'value' => 0,
		),
	);		
	
	return $floorplans_args;
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_date', 10, 1 );