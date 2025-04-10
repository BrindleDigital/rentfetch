<?php
/**
 * Template detection
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Load templates from the plugin unless they are overridden by the theme.
 *
 * @param   array $template the template to be loaded.
 *
 * @return  array $template the template to be loaded.
 */
function rentfetch_load_single_templates( $template ) {

	global $post;

	// assign the floorplan template to any floorplan post type for normal use, allowing the theme to override.
	if ( 'floorplans' === $post->post_type && locate_template( array( 'single-floorplans.php' ) ) !== $template ) {
		return RENTFETCH_DIR . 'template/single-floorplans.php';
	}

	// if $template is empty, assign the floorplan template to any floorplan post type.
	if ( 'floorplans' === $post->post_type && empty( $template ) ) {
		return RENTFETCH_DIR . 'template/single-floorplans.php';
	}

	// assign the property template to any property post type for normal use, allowing the theme to override.
	if ( 'properties' === $post->post_type && locate_template( array( 'single-properties.php' ) ) !== $template ) {
		return RENTFETCH_DIR . 'template/single-properties.php';
	}

	// if $template is empty, assign the properties template to any floorplan post type.
	if ( 'properties' === $post->post_type && empty( $template ) ) {
		return RENTFETCH_DIR . 'template/single-properties.php';
	}

	return $template;
}
add_filter( 'single_template', 'rentfetch_load_single_templates', 99 );

/**
 * Redirect the properties template to the website if the option for that is enabled.
 * 
 * @return void.
 */
function rentfetch_maybe_redirect_properties_template() {

	if ( !is_singular( 'properties') ) {
		return;
	}

	$permalink_behavior = get_option( 'rentfetch_options_property_external_linking_behavior', 'internal' );
	$url = rentfetch_get_property_url();
	
	if ( $url && 'external' === $permalink_behavior ) {
		wp_redirect( $url );
	}
}
add_action('template_redirect', 'rentfetch_maybe_redirect_properties_template');