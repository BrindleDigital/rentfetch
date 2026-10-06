<?php
/**
 * Run with: php -d zend.assertions=1 -d assert.exception=1 tests/test-editor-tabs.php
 *
 * @package rentfetch
 */

define( 'ABSPATH', __DIR__ );

/**
 * Dispatch the test's extension filter with its post context.
 *
 * @param string      $hook  Filter name.
 * @param mixed       $value Filtered value.
 * @param object|null $post  Record context.
 */
function apply_filters( $hook, $value, $post = null ) {
	$callback = $GLOBALS['rentfetch_test_filters'][ $hook ] ?? null;
	return $callback ? $callback( $value, $post ) : $value;
}

/** Ignore AJAX registration in this standalone check. */
function add_action() {}

/**
 * Escape section labels and attributes for the rendering check.
 *
 * @param mixed $value Value to escape.
 */
function esc_html( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

/**
 * Escape attributes for the rendering check.
 *
 * @param mixed $value Value to escape.
 */
function esc_attr( $value ) {
	return esc_html( $value );
}

/**
 * Encode tab IDs for the editor bootstrap.
 *
 * @param mixed $value Value to encode.
 */
function wp_json_encode( $value ) {
	return json_encode( $value ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Implement the WordPress mock without calling itself.
}

/** Return a fixed user ID for the editor bootstrap. */
function get_current_user_id() {
	return 1;
}

/** Skip nonce markup in this rendering check. */
function wp_nonce_field() {}

/** Skip identity bars; the test concerns tab rendering. */
function rentfetch_render_property_identity_bar() {}

/** Skip identity bars; the test concerns tab rendering. */
function rentfetch_render_floorplan_identity_bar() {}

/** Skip identity bars; the test concerns tab rendering. */
function rentfetch_render_unit_identity_bar() {}

$rentfetch_test_editors = array(
	'property'  => 'properties',
	'floorplan' => 'floorplans',
	'unit'      => 'units',
);

foreach ( $rentfetch_test_editors as $singular => $plural ) {
	require_once dirname( __DIR__ ) . '/lib/admin/post-editor-metaboxes/' . $plural . '/editor.php';
	require_once dirname( __DIR__ ) . '/lib/admin/post-editor-metaboxes/' . $plural . '/lazy.php';

	$getter      = 'rentfetch_get_' . $singular . '_editor_tabs';
	$editor_tabs = $getter();
	assert( isset( $editor_tabs['overview'], $editor_tabs['diagnostics'] ) );

	$record = (object) array(
		'ID'        => 123,
		'post_type' => $plural,
	);
	$marker = 'extension-' . $singular . '-post-123';
	$GLOBALS['rentfetch_test_filters'][ 'rentfetch_' . $singular . '_editor_tabs' ] = static function ( $editor_tabs, $context ) use ( $record, $marker ) {
		assert( $record === $context );
		$section = array(
			'label'    => 'Settings <example>',
			'callback' => static function ( $rendered_record ) use ( $record, $marker ) {
				assert( $record === $rendered_record );
				echo esc_html( $marker );
			},
		);

		$editor_tabs['overview']['sections'][]    = $section;
		$editor_tabs['diagnostics']['sections'][] = $section;
		$editor_tabs['extension-settings']        = array(
			'label'    => 'Extension <settings>',
			'sections' => array( $section ),
		);
		return $editor_tabs;
	};

	// Verify the real editor renders an appended section and a new tab in PHP.
	$render = 'rentfetch_' . $plural . '_editor_callback';
	ob_start();
	$render( $record );
	$html = ob_get_clean();
	assert( 2 === substr_count( $html, $marker ) );
	assert( false !== strpos( $html, 'data-rf-property-tab="extension-settings"' ) );
	assert( false !== strpos( $html, 'data-rf-property-panel="extension-settings"' ) );
	assert( false !== strpos( $html, 'Extension &lt;settings&gt;' ) );
	assert( false !== strpos( $html, 'Settings &lt;example&gt;' ) );
	assert( false !== strpos( $html, 'data-rf-lazy-fragment="diagnostics"' ) );

	// Diagnostics must use the same extension filter and post in the AJAX renderer.
	$render_fragment = 'rentfetch_render_' . $singular . '_editor_fragment';
	ob_start();
	$render_fragment( 'diagnostics', $record );
	$html = ob_get_clean();
	assert( 1 === substr_count( $html, $marker ) );

	unset( $GLOBALS['rentfetch_test_filters'][ 'rentfetch_' . $singular . '_editor_tabs' ] );
	assert( $editor_tabs === $getter( $record ) );
}

echo "Editor tab extension tests passed.\n";
