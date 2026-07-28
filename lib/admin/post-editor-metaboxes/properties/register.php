<?php
/**
 * Register the tabbed property editor.
 *
 * @package rentfetch
 */

/**
 * Render the Rent Fetch-controlled property fields after the post title.
 *
 * WordPress, taxonomy, and third-party meta boxes remain separate because Rent
 * Fetch does not own their save processes.
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
