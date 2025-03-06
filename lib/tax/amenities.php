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

/**
 * Remove the amentities from the post_class, as they are not generally useful and they triple the markup size.
 *
 * @param   array $classes  the default classes.
 * @param   array $class    the classes to add.
 * @param   string $post_id  the post id.
 *
 * @return  array the filtered classes.
 */
function rentfetch_remove_taxonomy_from_post_class($classes, $class, $post_id) {	
	return array_filter( $classes, 'rentfetch_filter_taxonomy_classes_remove_amenities');
}
add_filter( 'post_class', 'rentfetch_remove_taxonomy_from_post_class', 10, 3 );

/**
 * Filter the classes to remove the amenities
 *
 * @param   string  $class  each class.
 *
 * @return  string  the filtered class.
 */
function rentfetch_filter_taxonomy_classes_remove_amenities($class) {
	return !str_starts_with( $class, 'amenities-' );
}