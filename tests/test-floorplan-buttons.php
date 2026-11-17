<?php
/**
 * Focused regression checks for the single-floorplan unit-list button settings.
 * Run: php -d zend.assertions=1 -d assert.exception=1 tests/test-floorplan-buttons.php
 *
 * @package rentfetch
 */

define( 'ABSPATH', __DIR__ );
define( 'RENTFETCH_BASENAME', 'rentfetch/rentfetch.php' );
$GLOBALS['options'] = array();
$GLOBALS['units']   = 1;

function add_action() {}
function register_activation_hook() {}
function wp_enqueue_script() {}
function do_action() {}
function get_the_ID() { return 1; }
function get_post_meta() { return ''; }
function get_option( $key, $default = false ) { return $GLOBALS['options'][ $key ] ?? $default; }
function update_option( $key, $value ) { $GLOBALS['options'][ $key ] = $value; }
function add_option( $key, $value ) { if ( ! array_key_exists( $key, $GLOBALS['options'] ) ) { update_option( $key, $value ); } }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES ); }
function esc_html( $value ) { return esc_attr( $value ); }
function esc_url( $value ) { return esc_attr( $value ); }
function checked( $value, $expected ) { if ( (string) $value === (string) $expected ) { echo 'checked="checked"'; } }
function wp_count_posts() { return (object) array( 'publish' => 1 ); }
function rentfetch_get_floorplan_title() { return ''; }
function rentfetch_get_floorplan_pricing() { return ''; }
function rentfetch_get_floorplan_bedrooms() { return ''; }
function rentfetch_get_floorplan_bathrooms() { return ''; }
function rentfetch_get_floorplan_square_feet() { return ''; }
function rentfetch_get_floorplan_units_count_from_cpt() { return $GLOBALS['units']; }
function rentfetch_get_floorplan_specials() { return ''; }
function rentfetch_get_floorplan_specials_callout() { return ''; }
function rentfetch_get_connected_property_post_id_for_floorplan() { return null; }
function rentfetch_parse_tour_value() { return null; }
function apply_filters( $hook, $value ) { return $value; }
function rentfetch_settings_get_tab() { return 'floorplans'; }
function rentfetch_settings_get_section() { return 'floorplan-buttons'; }
function wp_unslash( $value ) { return stripslashes( $value ); }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function wp_verify_nonce( $nonce ) { return 'valid' === $nonce; }

require dirname( __DIR__ ) . '/lib/admin/options-sections/options-floorplan-buttons.php';
require dirname( __DIR__ ) . '/lib/template-functions/single-floorplans-parts/single-floorplans-parts.php';

function render_overview() {
	ob_start();
	rentfetch_single_floorplans_parts_overview();
	return ob_get_clean();
}

// An upgrade has no new options and must retain the existing button and destination.
$html = render_overview();
assert( false !== strpos( $html, 'href="#available-units">View Availability' ) );
assert( false !== strpos( $html, 'rentfetch-availability-arrow' ) );
assert( false !== strpos( $html, 'aria-hidden="true"' ) );
ob_start();
rentfetch_settings_floorplans_floorplan_buttons();
$settings = ob_get_clean();
assert( 1 === preg_match( '/id="rentfetch_options_view_availability_button_enabled" checked="checked"/', $settings ) );

// Saving an unchecked checkbox disables it; activation defaults must not override that choice.
$_POST = array( 'rentfetch_main_options_nonce_field' => 'valid' );
rentfetch_save_settings_floorplan_buttons();
assert( '0' === get_option( 'rentfetch_options_view_availability_button_enabled' ) );
rentfetch_settings_set_defaults_floorplans_buttons();
assert( false === strpos( render_overview(), 'href="#available-units"' ) );

// Saving enables it again and sanitizes its editable label, preserving other saved labels.
update_option( 'rentfetch_options_availability_button_button_label', 'Existing site label' );
$_POST['rentfetch_options_view_availability_button_enabled']      = 'on';
$_POST['rentfetch_options_view_availability_button_button_label'] = '<b>See units</b> & pricing';
rentfetch_save_settings_floorplan_buttons();
assert( '1' === get_option( 'rentfetch_options_view_availability_button_enabled' ) );
assert( 'See units & pricing' === get_option( 'rentfetch_options_view_availability_button_button_label' ) );
assert( false !== strpos( render_overview(), 'See units &amp; pricing' ) );
assert( 'Existing site label' === get_option( 'rentfetch_options_availability_button_button_label' ) );

$_POST['rentfetch_options_view_availability_button_button_label'] = '  ';
rentfetch_save_settings_floorplan_buttons();
assert( 'View Availability' === get_option( 'rentfetch_options_view_availability_button_button_label' ) );

// Enabling it never creates a link to a missing unit list.
$GLOBALS['units'] = 0;
assert( false === strpos( render_overview(), 'href="#available-units"' ) );
echo "Floor plan button checks passed.\n";
