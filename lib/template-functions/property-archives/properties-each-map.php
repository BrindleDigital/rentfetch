<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function rentfetch_properties_each_map() {
	
	$title = rentfetch_get_property_title();
	$city_state = rentfetch_get_property_city_state();
	$permalink = apply_filters( 'rentfetch_filter_property_permalink', get_the_permalink() );
	$permalink_target = apply_filters( 'rentfetch_filter_property_permalink_target', get_the_permalink() );
	$permalink_label = apply_filters( 'rentfetch_filter_property_permalink_label', get_the_permalink() );
	
	if ( $city_state )
		printf( '<p class="city-state">%s</p>', esc_attr( $city_state ) );
	
	if ( $title )
		printf( '<h3>%s</h3>', esc_attr( $title ) );
		
	// if ( $permalink )
	//     printf( '<a class="permalink" target="%s" href="%s">%s</a>', esc_html( $permalink_target ), esc_url( $permalink ), esc_attr( $permalink_label ) );
			
}
add_action( 'rentfetch_do_properties_each_map', 'rentfetch_properties_each_map' );
