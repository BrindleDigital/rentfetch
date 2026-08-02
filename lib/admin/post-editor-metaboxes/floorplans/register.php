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
 * WordPress, taxonomy, and third-party meta boxes remain separate because Rent
 * Fetch does not own their save processes.
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
