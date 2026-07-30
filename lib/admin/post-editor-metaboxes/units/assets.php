<?php
/**
 * Unit editor assets.
 *
 * @package rentfetch
 */

/**
 * Enqueue unit editor controls.
 *
 * @param string $hook Current admin page hook.
 * @return void
 */
function rentfetch_enqueue_unit_editor_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'units' !== $screen->post_type ) {
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
			'action'  => 'rentfetch_get_unit_editor_fragment',
			'nonce'   => wp_create_nonce( 'rentfetch_unit_editor_lazy' ),
		)
	);

	wp_enqueue_script(
		'rentfetch-unit-identity',
		RENTFETCH_PATH . 'js/unit-identity.js',
		array( 'jquery', 'jquery-ui-autocomplete' ),
		RENTFETCH_VERSION,
		true
	);
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'rentfetch-jquery-style' );
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_unit_editor_assets' );
