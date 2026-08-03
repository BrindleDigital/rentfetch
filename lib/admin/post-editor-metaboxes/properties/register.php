<?php
/**
 * Register the tabbed property editor.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render the Rent Fetch-controlled property fields after the post title.
 *
 * Third-party meta boxes remain separate because Rent Fetch does not own their
 * save processes.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function rentfetch_render_properties_editor_after_title( $post ) {
	if ( 'properties' !== $post->post_type ) {
		return;
	}

	rentfetch_properties_editor_callback( $post );
}
add_action( 'edit_form_after_title', 'rentfetch_render_properties_editor_after_title' );

/**
 * Remove the default property taxonomy boxes now rendered in the Categories tab.
 *
 * @return void
 */
function rentfetch_remove_properties_taxonomy_metaboxes() {
	foreach ( array( 'propertytypes', 'propertycategories', 'amenities' ) as $taxonomy_name ) {
		remove_meta_box( $taxonomy_name . 'div', 'properties', 'side' );
	}
}
add_action( 'add_meta_boxes_properties', 'rentfetch_remove_properties_taxonomy_metaboxes', 20 );
