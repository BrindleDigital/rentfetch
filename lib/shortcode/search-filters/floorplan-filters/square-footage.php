<?php
/**
 * Square footage filter
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add the square footage filter to the search filters
 *
 * @return void
 */
function rentfetch_search_filters_squarefoot() {

	$value_small = null;
	$value_big   = null;

	// if pricesmall isset, then use that value for $value_small.
	if ( isset( $_GET['sqftsmall'] ) && $_GET['sqftsmall'] > 0 ) {
		$value_small = intval( sanitize_text_field( wp_unslash( $_GET['sqftsmall'] ) ) );
	}

	// if pricebig isset, then use that value for $value_big.
	if ( isset( $_GET['sqftbig'] ) && $_GET['sqftbig'] > 0 ) {
		$value_big = intval( sanitize_text_field( wp_unslash( $_GET['sqftbig'] ) ) );
	}
	
	$label = apply_filters( 'rentfetch_search_filters_sqft_label', 'Square footage' );

	// * build the price search.
	echo '<fieldset class="square-footage number-range">';
		printf( '<legend>%s</legend>', esc_html( $label ) );
		printf( '<button class="toggle">%s</button>', esc_html( esc_html( $label ) ) );
		echo '<div class="input-wrap slider inactive">';
			echo '<div>';
				echo '<div class="price-slider-wrap slider-wrap"><div id="price-slider" style="width:100%;"></div></div>';
				echo '<div class="inputs-square-footage inputs-slider">';
					printf( '<div class="input-square-footage-wrap input-slider-wrap"><input type="number" min="1" name="sqftsmall" data-default-value="%s" id="sqftsmall" value="%s" /></div>', esc_html( $value_small ), esc_html( $value_small ) );
					echo '<div class="price-dash dash"></div>';
					printf( '<div class="input-square-footage-wrap input-slider-wrap"><input type="number" min="1" name="sqftbig" data-default-value="%s" id="sqftbig" value="%s" /></div>', esc_html( $value_big ), esc_html( $value_big ) );
				echo '</div>'; // .inputs-prices.
			echo '</div>'; // .slider.
		echo '</div>'; // .slider.
	echo '</fieldset>'; // .price.
}

/**
 * Add the square footage filter to the search filters
 *
 * @param array $floorplans_args The floorplan arguments.
 *
 * @return array.
 */
function rentfetch_search_floorplans_args_sqft( $floorplans_args ) {

	// bail if we don't have a srft search (neither are set).
	if ( ! isset( $_POST['sqftsmall'] ) && ! isset( $_POST['sqftbig'] ) ) {
		return $floorplans_args;
	}

	$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_frontend_nonce_action' ) ) {
		die( 'Nonce verification failed (square footage)' );
	}

	// set the default values.
	$sqft_small = null;
	$sqft_big   = null;

	// get the small value.
	if ( isset( $_POST['sqftsmall'] ) ) {
		$sqft_small = intval( sanitize_text_field( wp_unslash( $_POST['sqftsmall'] ) ) );
	}

	// get the big value.
	if ( isset( $_POST['sqftbig'] ) ) {
		$sqft_big = intval( sanitize_text_field( wp_unslash( $_POST['sqftbig'] ) ) );
	}

	if ( null === $sqft_small && null === $sqft_big ) {

		// if no values are set, just make sure that the minimum rent is greater than 0.
		$floorplans_args['meta_query'][] = array(
			array(
				'key'     => 'minimum_sqft',
				'value'   => '1',
				'type'    => 'numeric',
				'compare' => '>=',
			),
		);
	} elseif ( intval( $sqft_small ) > 0 && intval( $sqft_big ) > 0 ) {

		// if both values are set, then do a between query.
		$floorplans_args['meta_query'][] = array(
			array(
				'key'     => 'minimum_sqft',
				'value'   => array( $sqft_small, $sqft_big ),
				'type'    => 'numeric',
				'compare' => 'BETWEEN',
			),
		);
	} elseif ( intval( $sqft_small ) > 0 && empty( $sqft_big ) ) {

		// if only the small value is set, then do a greater than or equal to query.
		$floorplans_args['meta_query'][] = array(
			'relation' => 'AND',
			array(
				'key'     => 'minimum_sqft',
				'value'   => $sqft_small,
				'type'    => 'numeric',
				'compare' => '>=',
			),
			array(
				'key'     => 'minimum_sqft',
				'value'   => '1',
				'type'    => 'numeric',
				'compare' => '>=',
			),
		);
	} elseif ( intval( $sqft_big ) > 0 && empty( $sqft_small ) ) {

		// if only the big value is set, then do a between query between 1 and the big value.
		$floorplans_args['meta_query'][] = array(
			array(
				'key'     => 'minimum_sqft',
				'value'   => array( 1, $sqft_big ),
				'type'    => 'numeric',
				'compare' => 'BETWEEN',
			),
		);
	}

	return $floorplans_args;
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_sqft', 10, 1 );
