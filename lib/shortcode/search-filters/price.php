<?php

function rentfetch_search_filters_price() {
			
	// figure out our min/max values
	$valueSmall = get_option( 'options_price_filter_minimum', 0 );
	$valueBig = get_option( 'options_price_filter_maximum', 5000 );	
	$step = 50;
	
	// if pricesmall isset, then use that value for $valueSmall
	if ( isset( $_POST['pricesmall'] ) && $_POST['pricesmall'] > 0 )
		$valueSmall = intval( sanitize_text_field( $_POST['pricesmall'] ) );
		
	// if pricebig isset, then use that value for $valueBig
	if ( isset( $_POST['pricebig'] ) && $_POST['pricebig'] > 0 )
		$valueBig = intval( sanitize_text_field( $_POST['pricebig'] ) );
	
	// enqueue the noui slider
	wp_enqueue_style( 'rentfetch-nouislider-style' );
	wp_enqueue_script( 'rentfetch-nouislider-script' );
	wp_localize_script( 'rentfetch-nouislider-init-script', 'settings', 
		array(
			'valueSmall' => $valueSmall,
			'valueBig' => $valueBig,
			'step' => $step,
		)
	);
	
	// wp_enqueue_script( 'rentfetch-nouislider-init-script' );
	
	//* build the price search
	echo '<fieldset class="price">';
		echo '<legend>Price Range</legend>';
		echo '<button class="toggle">Price Range</button>';
		echo '<div class="input-wrap slider">';
			echo '<div class="price-slider-wrap"><div id="price-slider" style="width:100%;"></div></div>';
			echo '<div class="inputs-prices">';
				printf( '<input type="number" name="pricesmall" data-default-value="%s" id="pricesmall" value="%s" />', $valueSmall, $valueSmall );
				echo '<div class="price-dash"></div>';
				printf( '<input type="number" name="pricebig" data-default-value="%s" id="pricebig" value="%s" />', $valueBig, $valueBig );
			echo '</div>';
		echo '</div>'; // .slider
	echo '</fieldset>';		

}

add_filter( 'rentfetch_search_property_map_floorplans_query_args', 'rentfetch_search_property_map_floorplans_args_price', 10, 1 );
function rentfetch_search_property_map_floorplans_args_price( $floorplans_args ) {
	
	// bail if we don't have a price search
	if ( !isset( $_POST['pricesmall'] ) && !isset( $_POST['pricebig'] ) )
		return $floorplans_args;
	
	$defaultpricesmall = get_option( 'options_price_filter_minimum', 0 );
	$defaultpricebig = get_option( 'options_price_filter_maximum', 5000 );
	
	// get the small value
	if ( isset( $_POST['pricesmall'] ) && $_POST['pricebig'] > 0 ){
		$pricesmall = intval( sanitize_text_field( $_POST['pricesmall'] ) );
	} else {
		$pricesmall = $defaultpricesmall;
	}
	
	// get the big value
	if ( isset( $_POST['pricebig'] ) && $_POST['pricebig'] > 0 ) {
		$pricebig = intval( sanitize_text_field( $_POST['pricebig'] ) );
	} else {
		$pricebig = $defaultpricebig;
	}
	
	// // if neither are set, then bail
	// if ( $pricesmall == $defaultpricesmall && $pricebig == $defaultpricebig )
	// 	return $floorplans_args;
								
	$floorplans_args['meta_query'][] = array(
		array(
			'key' => 'minimum_rent',
			'value' => array( $pricesmall, $pricebig ),
			'type' => 'numeric',
			'compare' => 'BETWEEN',
		)
	);
		
	return $floorplans_args;
	
}