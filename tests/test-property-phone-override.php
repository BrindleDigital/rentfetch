<?php
/**
 * Run with: php -d zend.assertions=1 -d assert.exception=1 tests/test-property-phone-override.php
 *
 * @package rentfetch
 */

define( 'ABSPATH', __DIR__ );

/** No-op action registration for this focused check. */
function add_action() {}

/** No-op filter registration for this focused check. */
function add_filter() {}

/**
 * Return the unmodified filtered value.
 *
 * @param string $hook  Filter name.
 * @param mixed  $value Filtered value.
 * @return mixed
 */
function apply_filters( $hook, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
	return $value;
}

/** Return the focused property post ID. */
function get_the_ID() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return 1;
}

/**
 * Return focused property metadata.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @return string
 */
function get_post_meta( $post_id, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
	return $GLOBALS['rentfetch_test_property_meta'][ $key ] ?? '';
}

/**
 * Minimal text sanitizer for this focused check.
 *
 * @param mixed $value Input value.
 * @return string
 */
function sanitize_text_field( $value ) {
	return trim( (string) $value );
}

/**
 * Return escaped visible text.
 *
 * @param mixed $value Visible text.
 * @return string
 */
function esc_html( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES );
}

/** Return no tracking attributes. */
function rentfetch_get_tracking_data_attributes() {
	return '';
}

/** Return an empty tracking context. */
function rentfetch_get_property_tracking_context() {
	return array();
}

require_once dirname( __DIR__ ) . '/lib/common/functions-properties.php';

$GLOBALS['rentfetch_test_property_meta'] = array(
	'phone'          => '5155550100',
	'phone_override' => '',
);
assert( '(515) 555-0100' === rentfetch_get_property_phone() );

$GLOBALS['rentfetch_test_property_meta']['phone_override'] = '312.555.0199';
assert( '(312) 555-0199' === rentfetch_get_property_phone() );
assert( false !== strpos( rentfetch_get_property_phone_button(), 'href="tel:+13125550199"' ) );
assert( false !== strpos( rentfetch_get_property_phone_button(), '(312) 555-0199' ) );

echo "Property phone override tests passed.\n";
