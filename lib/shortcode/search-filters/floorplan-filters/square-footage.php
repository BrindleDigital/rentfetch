<?php

function rentfetch_search_filters_squarefoot() {
	
	$valueSmall = null;
	$valueBig = null;
	
	// if pricesmall isset, then use that value for $valueSmall
	if ( isset( $_GET['sqftsmall'] ) && $_GET['sqftsmall'] > 0 )
		$valueSmall = intval( sanitize_text_field( $_GET['sqftsmall'] ) );
		
	// if pricebig isset, then use that value for $valueBig
	if ( isset( $_GET['sqftbig'] ) && $_GET['sqftbig'] > 0 )
		$valueBig = intval( sanitize_text_field( $_GET['sqftbig'] ) );
	
	//* build the price search
	echo '<fieldset class="square-footage number-range">';
		echo '<legend>Square footage</legend>';
		echo '<button class="toggle">Square footage</button>';
		echo '<div class="input-wrap slider inactive">';
			echo '<div>';
				echo '<div class="price-slider-wrap slider-wrap"><div id="price-slider" style="width:100%;"></div></div>';
				echo '<div class="inputs-square-footage inputs-slider">';
					printf( '<div class="input-square-footage-wrap input-slider-wrap"><input type="number" min="1" name="sqftsmall" data-default-value="%s" id="sqftsmall" value="%s" /></div>', $valueSmall, $valueSmall );
					echo '<div class="price-dash dash"></div>';
					printf( '<div class="input-square-footage-wrap input-slider-wrap"><input type="number" min="1" name="sqftbig" data-default-value="%s" id="sqftbig" value="%s" /></div>', $valueBig, $valueBig );
				echo '</div>'; // .inputs-prices
			echo '</div>';
		echo '</div>'; // .slider
	echo '</fieldset>';
	
}

function rentfetch_search_floorplans_args_sqft( $floorplans_args ) {
	
	// bail if we don't have a srft search (neither are set)
	if ( !isset( $_POST['sqftsmall'] ) && !isset( $_POST['sqftbig'] ) )
		return $floorplans_args;
	
	// set the default values
	$sqft_small = null;
	$sqft_big = null;
	
	// get the small value
	if ( isset( $_POST['sqftsmall'] ) )
		$sqft_small = intval( sanitize_text_field( $_POST['sqftsmall'] ) );
	
	// get the big value
	if ( isset( $_POST['sqftbig'] ) )
		$sqft_big = intval( sanitize_text_field( $_POST['sqftbig'] ) );
	
	if ( $sqft_small == null && $sqft_big == null ) {
		// if no values are set, just make sure that the minimum rent is greater than 0
		$floorplans_args['meta_query'][] = array(
			array(
				'key' => 'minimum_sqft',
				'value' => '1',
				'type' => 'numeric',
				'compare' => '>=',
			),
		);
	}
	
	elseif ( intval( $sqft_small ) > 0 && intval( $sqft_big ) > 0 ) {
		// if both values are set, then do a between query
		$floorplans_args['meta_query'][] = array(
			array(
				'key' => 'minimum_sqft',
				'value' => array( $sqft_small, $sqft_big ),
				'type' => 'numeric',
				'compare' => 'BETWEEN',
			),
		);
	}
	
	elseif ( intval( $sqft_small ) > 0 && empty( $sqft_big)) {
		// if only the small value is set, then do a greater than or equal to query
		$floorplans_args['meta_query'][] = array(
			'relation' => 'AND',
			array(
				'key' => 'minimum_sqft',
				'value' => $sqft_small,
				'type' => 'numeric',
				'compare' => '>=',
			),
			array(
				'key' => 'minimum_sqft',
				'value' => '1',
				'type' => 'numeric',
				'compare' => '>=',
			),
		);
	} 
	
	elseif ( intval( $sqft_big ) > 0 && empty( $sqft_small ) ) {
		
		// if only the big value is set, then do a between query between 1 and the big value
		$floorplans_args['meta_query'][] = array(
			array(
				'key' => 'minimum_sqft',
				'value' => array( 1, $sqft_big ),
				'type' => 'numeric',
				'compare' => 'BETWEEN',
			),
		);
	}
	
	return $floorplans_args;
	
	
	
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_sqft', 10, 1 );