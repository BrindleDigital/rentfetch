<?php
/**
 * Register the tabbed floor plan editor.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render the Rent Fetch-controlled floor plan fields after the post title.
 *
 * Third-party meta boxes remain separate because Rent Fetch does not own their
 * save processes.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function rentfetch_render_floorplans_editor_after_title( $post ) {
	if ( 'floorplans' !== $post->post_type ) {
		return;
	}

	rentfetch_floorplans_editor_callback( $post );
}
add_action( 'edit_form_after_title', 'rentfetch_render_floorplans_editor_after_title' );

/**
 * Remove the default floor plan taxonomy boxes now rendered in the Categories tab.
 *
 * @return void
 */
function rentfetch_remove_floorplan_taxonomy_metaboxes() {
	foreach ( array( 'floorplancategory', 'floorplantype' ) as $taxonomy_name ) {
		remove_meta_box( $taxonomy_name . 'div', 'floorplans', 'side' );
	}
}
add_action( 'add_meta_boxes_floorplans', 'rentfetch_remove_floorplan_taxonomy_metaboxes', 20 );
