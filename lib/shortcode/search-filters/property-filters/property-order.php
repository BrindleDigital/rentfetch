<?php
/**
 * Property search ordering.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set the base order of the properties
 *
 * @param array $property_args Existing property query arguments.
 * @return array
 */
function rentfetch_search_property_order_options( $property_args ) {

	$orderby = get_option( 'rentfetch_options_property_orderby' );
	$order   = get_option( 'rentfetch_options_property_order' );

	$property_args['orderby'] = $orderby;
	$property_args['order']   = $order;

	return $property_args;
}
add_filter( 'rentfetch_search_property_map_properties_query_args', 'rentfetch_search_property_order_options' );
