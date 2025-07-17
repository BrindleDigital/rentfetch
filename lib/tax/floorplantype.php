<?php
/**
 * The Floorplantype taxonomy
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the floorplantype taxonomy
 *
 * @return void
 */
function rentfetch_register_taxonomy_floorplantype() {
	register_taxonomy(
		'floorplantype',
		'floorplans',
		array(
			'label'        => __( 'Floor plan types' ),
			'rewrite'      => array( 'slug' => 'floorplantype' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'rentfetch_register_taxonomy_floorplantype' );
