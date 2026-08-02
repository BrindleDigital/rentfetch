<?php
/**
 * Lazy floor plan editor fragments.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render a requested floor plan editor fragment.
 *
 * @param string  $fragment Fragment identifier.
 * @param WP_Post $post     Floor plan post.
 * @return void
 */
function rentfetch_render_floorplan_editor_fragment( $fragment, $post ) {
	switch ( $fragment ) {
		case 'diagnostics':
			$tabs     = rentfetch_get_floorplan_editor_tabs();
			$sections = $tabs['diagnostics']['sections'] ?? array();

			foreach ( $sections as $section ) {
				rentfetch_render_floorplan_editor_section( $section, $post );
			}
			break;

		case 'synced-images':
			rentfetch_render_floorplan_synced_images_preview( $post );
			break;
	}
}

/**
 * AJAX handler for floor plan editor fragments.
 *
 * @return void
 */
function rentfetch_ajax_get_floorplan_editor_fragment() {
	check_ajax_referer( 'rentfetch_floorplan_editor_lazy', 'nonce' );

	$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$fragment = isset( $_POST['fragment'] ) ? sanitize_key( wp_unslash( $_POST['fragment'] ) ) : '';
	$allowed  = array( 'diagnostics', 'synced-images' );
	$post     = get_post( $post_id );

	if (
		! $post ||
		'floorplans' !== $post->post_type ||
		! current_user_can( 'edit_post', $post_id ) ||
		! in_array( $fragment, $allowed, true )
	) {
		wp_send_json_error( array( 'message' => 'This floor plan editor content could not be loaded.' ), 403 );
	}

	ob_start();
	rentfetch_render_floorplan_editor_fragment( $fragment, $post );
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_rentfetch_get_floorplan_editor_fragment', 'rentfetch_ajax_get_floorplan_editor_fragment' );
