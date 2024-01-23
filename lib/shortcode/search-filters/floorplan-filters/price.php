<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function rentfetch_search_filters_price() {
	
	$valueSmall = null;
	$valueBig = null;
			
	// figure out our min/max values
	// $valueSmall = get_option( 'rentfetch_options_price_filter_minimum', null );
	// $valueBig = get_option( 'rentfetch_options_price_filter_maximum', null );	
	// $step = 50;
		
	// if pricesmall isset, then use that value for $valueSmall
	if ( isset( $_GET['pricesmall'] ) && $_GET['pricesmall'] > 0 )
		$valueSmall = intval( sanitize_text_field( $_GET['pricesmall'] ) );
		
	// if pricebig isset, then use that value for $valueBig
	if ( isset( $_GET['pricebig'] ) && $_GET['pricebig'] > 0 )
		$valueBig = intval( sanitize_text_field( $_GET['pricebig'] ) );
	
	// enqueue the noui slider
	// wp_enqueue_style( 'rentfetch-nouislider-style' );
	// wp_enqueue_script( 'rentfetch-nouislider-script' );
	// wp_localize_script( 'rentfetch-nouislider-init-script', 'settings', 
	// 	array(
	// 		'valueSmall' => $valueSmall,
	// 		'valueBig' => $valueBig,
	// 		'step' => $step,
	// 	)
	// );
		
	if ( intval( $valueSmall ) == 0 ) {
		$valueSmall = null;
	}
	
	if ( intval( $valueBig ) == 0 ) {
		$valueBig = null;
	}
		
	// wp_enqueue_script( 'rentfetch-nouislider-init-script' );
	
	//* build the price search
	echo '<fieldset class="price number-range">';
		echo '<legend>Price Range</legend>';
		echo '<button class="toggle">Price Range</button>';
		echo '<div class="input-wrap slider inactive">';
			echo '<div>';
				echo '<div class="price-slider-wrap slider-wrap"><div id="price-slider" style="width:100%;"></div></div>';
				echo '<div class="inputs-prices inputs-slider">';
					printf( '<div class="input-price-wrap input-slider-wrap"><span class="input-group-addon-price">$</span><input type="number" min="1" name="pricesmall" data-default-value="%s" id="pricesmall" value="%s" /></div>', $valueSmall, $valueSmall );
					echo '<div class="price-dash dash"></div>';
					printf( '<div class="input-price-wrap input-slider-wrap"><span class="input-group-addon-price">$</span><input type="number" min="1" name="pricebig" data-default-value="%s" id="pricebig" value="%s" /></div>', $valueBig, $valueBig );
				echo '</div>'; // .inputs-prices
			echo '</div>';
		echo '</div>'; // .slider
	echo '</fieldset>';		

}

function rentfetch_search_floorplans_args_price( $floorplans_args ) {
					
	// bail if we don't have a price search (neither are set)
	if ( !isset( $_POST['pricesmall'] ) && !isset( $_POST['pricebig'] ) )
		return $floorplans_args;
		
	$pricesmall = null;
	$pricebig = null;
	
	// get the small value
	if ( isset( $_POST['pricesmall'] ) )
		$pricesmall = intval( sanitize_text_field( $_POST['pricesmall'] ) );
	
	// get the big value
	if ( isset( $_POST['pricebig'] ) )
		$pricebig = intval( sanitize_text_field( $_POST['pricebig'] ) );
	
	// let's block any values that are less than 1
	if ( $pricesmall < 1 )
		$pricesmall = null;
	
	// let's block any values that are less than 1
	if ( $pricebig < 1 )
		$pricebig = null;
			
	// if there are no values here, let's bail and return the original args
	if ( $pricesmall == null && $pricebig == null ) {
		return $floorplans_args;
	}
	
	// if both values are set, then do a between query
	elseif ( intval( $pricesmall ) > 0 && intval( $pricebig ) > 0 ) {
		// if both values are set, then do a between query
		$floorplans_args['meta_query'][] = array(
			array(
				'key' => 'minimum_rent',
				'value' => array( $pricesmall, $pricebig ),
				'type' => 'numeric',
				'compare' => 'BETWEEN',
			),
		);
	} 
	
	// if only the small value is set, then do a greater than or equal to query
	elseif ( intval( $pricesmall ) > 0 && $pricebig == null ) {
		
		$floorplans_args['meta_query'][] = array(
			'relation' => 'AND',
			array(
				'key' => 'minimum_rent',
				'value' => $pricesmall,
				'type' => 'numeric',
				'compare' => '>=',
			),
			array(
				'key' => 'minimum_rent',
				'value' => '1',
				'type' => 'numeric',
				'compare' => '>=',
			),
		);
		
	} 
	
	// if only the big value is set, then do a between query between 1 and the big value
	elseif ( intval( $pricebig ) > 0 && empty( $pricesmall ) ) {
				
		$floorplans_args['meta_query'][] = array(
			array(
				'key' => 'minimum_rent',
				'value' => array( 1, $pricebig ),
				'type' => 'numeric',
				'compare' => 'BETWEEN',
			),
		);
		
	}
		
	return $floorplans_args;
	
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_price', 10, 1 );