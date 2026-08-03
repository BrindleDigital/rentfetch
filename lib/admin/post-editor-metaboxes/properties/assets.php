<?php
/**
 * Property editor assets.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Enqueue admin scripts/styles for API response code editor.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function rentfetch_enqueue_api_response_editor_assets( $hook ) {
	// Only load on post edit screens.
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	// Only enqueue on properties, floorplans, or units edit screens.
	if ( ! in_array( $screen->post_type, array( 'properties', 'floorplans', 'units' ), true ) ) {
		return;
	}

	// Ensure wp.codeEditor is available and get settings so WP can enqueue required addons.
	$settings = wp_enqueue_code_editor( array( 'type' => 'application/json' ) );

	// Fallback settings if enqueue didn't return anything.
	if ( false === $settings ) {
		$settings = array();
	}

	// Enqueue the script handle registered in lib/initialization/enqueue.php and localize the settings.
	wp_enqueue_script( 'rentfetch-api-response-editor' );

	// Make the settings available to our script so it uses the same assets/addons WP enqueued.
	wp_localize_script( 'rentfetch-api-response-editor', 'rentfetchCodeEditorSettings', $settings );

	// Enqueue JSON handling script.
	wp_enqueue_script( 'rentfetch-properties-fees-json-handling', RENTFETCH_PATH . 'js/rentfetch-properties-fees-json-handling.js', array( 'rentfetch-api-response-editor' ), RENTFETCH_VERSION, true );

	// Localize settings for the JSON handling script as well.
	wp_localize_script( 'rentfetch-properties-fees-json-handling', 'rentfetchCodeEditorSettings', $settings );
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_api_response_editor_assets' );

/**
 * Enqueue CSV fees admin scripts for property fees.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function rentfetch_enqueue_csv_upload_script( $hook ) {
	// Only load on post edit screens.
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'properties' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_script( 'rentfetch-properties-fees-csv-url-validation', RENTFETCH_PATH . 'js/rentfetch-properties-fees-csv-url-validation.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_localize_script(
		'rentfetch-properties-fees-csv-url-validation',
		'rentfetchCsvValidation',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'rentfetch_validate_csv_url' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_csv_upload_script' );

/**
 * Enqueue the property editor tab controller.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function rentfetch_enqueue_property_editor_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'properties' !== $screen->post_type ) {
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
			'action'  => 'rentfetch_get_property_editor_fragment',
			'nonce'   => wp_create_nonce( 'rentfetch_property_editor_lazy' ),
		)
	);

	wp_enqueue_editor();
	wp_enqueue_script(
		'rentfetch-property-editor-fees',
		RENTFETCH_PATH . 'js/property-editor-fees.js',
		array( 'jquery' ),
		RENTFETCH_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_property_editor_assets' );

/**
 * Enqueue hierarchy interactions wherever the hierarchy renderer is used.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function rentfetch_enqueue_hierarchy_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'properties', 'floorplans', 'units' ), true ) ) {
		return;
	}

	$hierarchy_script_version = file_exists( RENTFETCH_DIR . 'js/property-hierarchy.js' )
		? filemtime( RENTFETCH_DIR . 'js/property-hierarchy.js' )
		: RENTFETCH_VERSION;

	wp_enqueue_script(
		'rentfetch-property-hierarchy',
		RENTFETCH_PATH . 'js/property-hierarchy.js',
		array(),
		$hierarchy_script_version,
		true
	);
	wp_localize_script(
		'rentfetch-property-hierarchy',
		'rentfetchHierarchy',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'action'  => 'rentfetch_get_sync_tooltip',
			'nonce'   => wp_create_nonce( 'rentfetch_sync_tooltip' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_hierarchy_assets' );
