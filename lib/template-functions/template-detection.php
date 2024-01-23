<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function rentfetch_load_single_templates( $template ) {
	
	global $post;	
	
	// assign the floorplan template to any floorplan post type for normal use, allowing the theme to override
	if ( 'floorplans' === $post->post_type && locate_template( array( 'single-floorplans.php' ) ) !== $template )
		return RENTFETCH_DIR . 'template/single-floorplans.php';
	
	// if $template is empty, assign the floorplan template to any floorplan post type
	if ( 'floorplans' === $post->post_type && empty( $template ) )
		return RENTFETCH_DIR . 'template/single-floorplans.php';
	
	// assign the property template to any property post type for normal use, allowing the theme to override
	if ( 'properties' === $post->post_type && locate_template( array( 'single-properties.php' ) ) !== $template )
		return RENTFETCH_DIR . 'template/single-properties.php';
	
	// if $template is empty, assign the properties template to any floorplan post type
	if ( 'properties' === $post->post_type && empty( $template ) )
		return RENTFETCH_DIR . 'template/single-properties.php';

	return $template;
}
add_filter( 'single_template', 'rentfetch_load_single_templates', 99 );
