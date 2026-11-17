<?php
/**
 * Run with: php -d zend.assertions=1 -d assert.exception=1 tests/test-office-hours.php
 *
 * @package rentfetch
 */

define( 'ABSPATH', __DIR__ );

function add_action() {}
function add_filter() {}
function add_shortcode() {}
function apply_filters( $hook, $value ) {
	return $value;
}
function get_the_ID() {
	return 1;
}
function get_posts( $args ) {
	return 'test-property' === $args['meta_value'] ? array( 1 ) : array();
}
function get_post_meta( $post_id, $key ) {
	return $GLOBALS['rentfetch_test_office_hours'];
}
function shortcode_atts( $defaults, $atts ) {
	return array_merge( $defaults, $atts );
}
function esc_html( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES );
}
function rentfetch_get_tracking_data_attributes() {
	return '';
}
function rentfetch_get_property_tracking_context() {
	return array();
}

require_once dirname( __DIR__ ) . '/lib/common/functions-properties.php';
require_once dirname( __DIR__ ) . '/lib/common/property-component-shortcodes.php';

$GLOBALS['rentfetch_test_office_hours'] = array(
	'monday'    => array( 'start' => '09:30', 'end' => '17:30' ),
	'tuesday'   => array( 'start' => '09:00', 'end' => '17:00' ),
	'wednesday' => array( 'start' => '00:00', 'end' => '12:15' ),
	'thursday'  => array( 'start' => '11:45', 'end' => '12:00' ),
);

$outputs = array(
	rentfetch_get_property_office_hours(),
	rentfetch_get_property_office_hours_button( 'test-property' ),
	rentfetch_property_shortcode( array( 'property_id' => 'test-property', 'info' => 'office_hours' ) ),
);
foreach ( $outputs as $output ) {
	assert( false !== strpos( $output, '9:30am to 5:30pm' ), 'Half-hour minutes must be preserved.' );
	assert( false !== strpos( $output, '9am to 5pm' ), 'Whole hours should keep their compact format.' );
	assert( false !== strpos( $output, '12am to 12:15pm' ), 'Midnight and quarter hours must be preserved.' );
	assert( false !== strpos( $output, '11:45am to 12pm' ), 'Noon and three-quarter hours must be preserved.' );
	assert( false !== strpos( $output, '<span class="day-hours">Closed</span>' ), 'Unspecified days should remain closed.' );
}

$GLOBALS['rentfetch_test_office_hours'] = array();
assert( '' === rentfetch_get_property_office_hours() );
assert( '' === rentfetch_get_property_office_hours_button() );
assert( '' === rentfetch_property_shortcode( array( 'property_id' => 'test-property', 'info' => 'office_hours' ) ) );

echo "Office hours tests passed.\n";
