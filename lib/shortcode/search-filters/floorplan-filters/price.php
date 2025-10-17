<?php
/**
 * Price filter
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the form markup for the price
 *
 * @return void.
 */
function rentfetch_search_filters_price() {

	$value_small = null;
	$value_big   = null;

	// figure out our min/max values.
	$value_small = (int) get_option( 'rentfetch_options_price_filter_minimum', null );
	$value_big   = (int) get_option( 'rentfetch_options_price_filter_maximum', null );

	// if pricesmall isset, then use that value for $value_small.
	if ( isset( $_GET['pricesmall'] ) && $_GET['pricesmall'] > 0 ) {
		$value_small = intval( sanitize_text_field( wp_unslash( $_GET['pricesmall'] ) ) );
	}

	// if pricebig isset, then use that value for $value_big.
	if ( isset( $_GET['pricebig'] ) && $_GET['pricebig'] > 0 ) {
		$value_big = intval( sanitize_text_field( wp_unslash( $_GET['pricebig'] ) ) );
	}

	if ( 0 === intval( $value_small ) ) {
		$value_small = null;
	}

	if ( 0 === intval( $value_big ) ) {
		$value_big = null;
	}
	
	$label = apply_filters( 'rentfetch_search_filters_price_label', 'Price' );

	// * build the price search
	echo '<fieldset class="price number-range">';
		printf( '<legend>%s</legend>', esc_html( $label ) );
		printf( '<button type="button" class="toggle">%s</button>', esc_html( $label ) );
		echo '<div class="input-wrap slider inactive">';
			echo '<div>';
				echo '<div class="price-slider-wrap slider-wrap"><div id="price-slider" style="width:100%;"></div></div>';
				echo '<div class="inputs-prices inputs-slider">';
					printf( '<div class="input-price-wrap input-slider-wrap"><span class="input-group-addon-price">$</span><input type="number" min="1" name="pricesmall" data-default-value="%s" id="pricesmall" value="%s" /></div>', esc_html( $value_small ), esc_html( $value_small ) );
					echo '<div class="price-dash dash"></div>';
					printf( '<div class="input-price-wrap input-slider-wrap"><span class="input-group-addon-price">$</span><input type="number" min="1" name="pricebig" data-default-value="%s" id="pricebig" value="%s" /></div>', esc_html( $value_big ), esc_html( $value_big ) );
				echo '</div>'; // .inputs-prices
			echo '</div>';
		echo '</div>'; // .slider
	echo '</fieldset>';
}

/**
 * Add the price filter to the search filters
 *
 * @param   array $floorplans_args  The floorplan arguments.
 *
 * @return  array.
 */
function rentfetch_search_floorplans_args_price( $floorplans_args ) {

	// bail if we don't have a price search (neither are set).
	if ( ! isset( $_POST['pricesmall'] ) && ! isset( $_POST['pricebig'] ) ) {
		return $floorplans_args;
	}

	$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( $nonce, 'rentfetch_frontend_nonce_action' ) ) {
		die( 'Nonce verification failed (price)' );
	}

	$pricesmall = null;
	$pricebig   = null;

	// get the small value.
	if ( isset( $_POST['pricesmall'] ) ) {
		$pricesmall = intval( sanitize_text_field( wp_unslash( $_POST['pricesmall'] ) ) );
	}

	// get the big value.
	if ( isset( $_POST['pricebig'] ) ) {
		$pricebig = intval( sanitize_text_field( wp_unslash( $_POST['pricebig'] ) ) );
	}

	// let's block any values that are less than 1.
	if ( $pricesmall < 1 ) {
		$pricesmall = null;
	}

	// let's block any values that are less than 1.
	if ( $pricebig < 1 ) {
		$pricebig = null;
	}

	// if there are no values here, let's bail and return the original args.
	if ( null === $pricesmall && null === $pricebig ) {
		return $floorplans_args;
	}

	// if both values are set, then do a between query.
	elseif ( intval( $pricesmall ) > 0 && intval( $pricebig ) > 0 ) {
		// if both values are set, then do a between query.
		$floorplans_args['meta_query'][] = array(
			array(
				'key'     => 'minimum_rent',
				'value'   => array( $pricesmall, $pricebig ),
				'type'    => 'numeric',
				'compare' => 'BETWEEN',
			),
		);
	}

	// if only the small value is set, then do a greater than or equal to query.
	elseif ( intval( $pricesmall ) > 0 && null === $pricebig ) {

		$floorplans_args['meta_query'][] = array(
			'relation' => 'AND',
			array(
				'key'     => 'minimum_rent',
				'value'   => $pricesmall,
				'type'    => 'numeric',
				'compare' => '>=',
			),
			array(
				'key'     => 'minimum_rent',
				'value'   => '1',
				'type'    => 'numeric',
				'compare' => '>=',
			),
		);

	}

	// if only the big value is set, then do a between query between 1 and the big value.
	elseif ( intval( $pricebig ) > 0 && empty( $pricesmall ) ) {

		$floorplans_args['meta_query'][] = array(
			array(
				'key'     => 'minimum_rent',
				'value'   => array( 1, $pricebig ),
				'type'    => 'numeric',
				'compare' => 'BETWEEN',
			),
		);

	}

	return $floorplans_args;
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_price', 10, 1 );
