<?php
/**
 * The Propertycategory taxonomy
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the propertycategory taxonomy
 *
 * @return void
 */
function rentfetch_register_propertycategory_taxonomy() {
	register_taxonomy(
		'propertycategories',
		'properties',
		array(
			'label'        => __( 'Property categories' ),
			'rewrite'      => array( 'slug' => 'propertycategories' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'rentfetch_register_propertycategory_taxonomy', 20 );
