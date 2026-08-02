<?php
/**
 * Register the tabbed unit editor.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render Rent Fetch-controlled unit fields after the post title.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function rentfetch_render_units_editor_after_title( $post ) {
	if ( 'units' !== $post->post_type ) {
		return;
	}

	rentfetch_units_editor_callback( $post );
}
add_action( 'edit_form_after_title', 'rentfetch_render_units_editor_after_title' );

/**
 * Preserve compatibility with Rent Fetch Sync versions that register the old
 * unit metabox callback on add_meta_boxes.
 *
 * The unit fields now render in the tabbed editor after the title, so this
 * callback intentionally does not register duplicate legacy metaboxes.
 *
 * @return void
 */
function rentfetch_register_units_details_metabox() {
	// Intentionally empty.
}
