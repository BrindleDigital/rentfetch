<?php
/**
 * The Propertytype taxonomy
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the propertytype taxonomy
 *
 * @return void
 */
function rentfetch_register_propertytype_taxonomy() {
	register_taxonomy(
		'propertytypes',
		'properties',
		array(
			'label'        => __( 'Property types' ),
			'rewrite'      => array( 'slug' => 'propertytypes' ),
			'hierarchical' => true,
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'rentfetch_register_propertytype_taxonomy', 20 );
