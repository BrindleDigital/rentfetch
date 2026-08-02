<?php
/**
 * Lazy unit editor fragments.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render a requested unit editor fragment.
 *
 * @param string  $fragment Fragment identifier.
 * @param WP_Post $post     Unit post.
 * @return void
 */
function rentfetch_render_unit_editor_fragment( $fragment, $post ) {
	if ( 'diagnostics' !== $fragment ) {
		return;
	}

	$tabs     = rentfetch_get_unit_editor_tabs();
	$sections = $tabs['diagnostics']['sections'] ?? array();

	foreach ( $sections as $section ) {
		rentfetch_render_unit_editor_section( $section, $post );
	}
}

/**
 * AJAX handler for unit editor fragments.
 *
 * @return void
 */
function rentfetch_ajax_get_unit_editor_fragment() {
	check_ajax_referer( 'rentfetch_unit_editor_lazy', 'nonce' );

	$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$fragment = isset( $_POST['fragment'] ) ? sanitize_key( wp_unslash( $_POST['fragment'] ) ) : '';
	$post     = get_post( $post_id );

	if (
		! $post ||
		'units' !== $post->post_type ||
		! current_user_can( 'edit_post', $post_id ) ||
		'diagnostics' !== $fragment
	) {
		wp_send_json_error( array( 'message' => 'This unit editor content could not be loaded.' ), 403 );
	}

	ob_start();
	rentfetch_render_unit_editor_fragment( $fragment, $post );
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_rentfetch_get_unit_editor_fragment', 'rentfetch_ajax_get_unit_editor_fragment' );
