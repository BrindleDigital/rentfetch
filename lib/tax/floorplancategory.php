<?php
/**
 * The Floorplancategory taxonomy
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the floorplancategory taxonomy
 *
 * @return void
 */
function rentfetch_register_taxonomy_floorplancategory() {
	register_taxonomy(
		'floorplancategory',
		'floorplans',
		array(
			'label'        => __( 'Floor plan categories' ),
			'rewrite'      => array( 'slug' => 'floorplancategory' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'rentfetch_register_taxonomy_floorplancategory' );
