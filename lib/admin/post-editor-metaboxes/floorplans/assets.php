<?php
/**
 * Floor plan editor assets.
 *
 * @package rentfetch
 */

/**
 * Enqueue the floor plan editor controls.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function rentfetch_enqueue_floorplan_editor_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'floorplans' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_script(
		'rentfetch-property-editor-tabs',
		RENTFETCH_PATH . 'js/property-editor-tabs.js',
		array(),
		RENTFETCH_VERSION,
		true
	);

	wp_localize_script(
		'rentfetch-property-editor-tabs',
		'rentfetchPropertyEditor',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'action'  => 'rentfetch_get_floorplan_editor_fragment',
			'nonce'   => wp_create_nonce( 'rentfetch_floorplan_editor_lazy' ),
		)
	);

	wp_enqueue_editor();
	wp_enqueue_media();
	wp_enqueue_script(
		'rentfetch-floorplan-identity',
		RENTFETCH_PATH . 'js/floorplan-identity.js',
		array( 'jquery', 'jquery-ui-autocomplete' ),
		RENTFETCH_VERSION,
		true
	);
	wp_enqueue_script( 'rentfetch-metabox-floorplans-images' );
	wp_enqueue_script( 'rentfetch-metabox-properties-tour' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'rentfetch-jquery-style' );
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_floorplan_editor_assets' );
