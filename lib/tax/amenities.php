<?php
/**
 * The Amenities taxonomy
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the amenities taxonomy
 *
 * @return void
 */
function rentfetch_register_amenities_taxonomy() {
	register_taxonomy(
		'amenities',
		'properties',
		array(
			'label'        => __( 'Amenities' ),
			'rewrite'      => array( 'slug' => 'amenities' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'rentfetch_register_amenities_taxonomy', 20 );
