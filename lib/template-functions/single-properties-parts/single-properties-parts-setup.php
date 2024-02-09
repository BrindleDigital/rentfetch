<?php
/**
 * Set up the sections for the single property page
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add the sections to the single property page (for easy re-ordering these are on a hook)
 *
 * @return void.
 */
function rentfetch_single_properties_set_up_parts() {

	add_action( 'rentfetch_do_single_properties_parts', 'rentfetch_single_properties_parts_images' );
	add_action( 'rentfetch_do_single_properties_parts', 'rentfetch_single_properties_parts_subnav' );
	add_action( 'rentfetch_do_single_properties_parts', 'rentfetch_single_properties_parts_details' );
	add_action( 'rentfetch_do_single_properties_parts', 'rentfetch_single_properties_parts_floorplans' );
	add_action( 'rentfetch_do_single_properties_parts', 'rentfetch_single_properties_parts_amenities' );
	add_action( 'rentfetch_do_single_properties_parts', 'rentfetch_single_properties_parts_map' );
	add_action( 'rentfetch_do_single_properties_parts', 'rentfetch_single_properties_parts_more_properties' );
}
add_action( 'wp_loaded', 'rentfetch_single_properties_set_up_parts' );

/**
 * Set up the navigation components (for easy re-ordering these are on a hook)
 *
 * @return void.
 */
function rentfetch_single_properties_set_up_subnav_parts() {

	add_action( 'rentfetch_do_single_properties_subnav_parts', 'rentfetch_single_properties_parts_subnav_images' );
	add_action( 'rentfetch_do_single_properties_subnav_parts', 'rentfetch_single_properties_parts_subnav_details' );
	add_action( 'rentfetch_do_single_properties_subnav_parts', 'rentfetch_single_properties_parts_subnav_floorplans' );
	add_action( 'rentfetch_do_single_properties_subnav_parts', 'rentfetch_single_properties_parts_subnav_amenities' );
	add_action( 'rentfetch_do_single_properties_subnav_parts', 'rentfetch_single_properties_parts_subnav_maps' );
	add_action( 'rentfetch_do_single_properties_subnav_parts', 'rentfetch_single_properties_parts_subnav_more_properties' );
}
add_action( 'wp_loaded', 'rentfetch_single_properties_set_up_subnav_parts' );
