<?php
/**
 * Focused checks for single-floorplan value normalization.
 *
 * @package rentfetch
 */

define( 'ABSPATH', __DIR__ );
define( 'RENTFETCH_PATH', '' );
define( 'RENTFETCH_VERSION', 'test' );

/**
 * Record action changes for the focused compatibility check.
 *
 * @param string $hook Action hook.
 * @param string $callback Action callback.
 */
function add_action( $hook = '', $callback = '' ) {
	$GLOBALS['rentfetch_test_added_actions'][] = array( $hook, $callback );
}

/**
 * Record action removals for the focused compatibility check.
 *
 * @param string $hook Action hook.
 * @param string $callback Action callback.
 */
function remove_action( $hook = '', $callback = '' ) {
	$GLOBALS['rentfetch_test_removed_actions'][] = array( $hook, $callback );
}

/** No-op filter registration for this focused check. */
function add_filter() {}

/**
 * Output a marker for the focused media test.
 *
 * @param string $hook Action name.
 */
function do_action( $hook ) {
	if ( 'rentfetch_do_floorplan_images' === $hook ) {
		echo 'floorplan-images';
	}
}

/** Return the focused test post ID. */
function get_the_ID() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return 1;
}

/** Return a focused unit title. */
function get_the_title() {
	return 'Test unit';
}

/**
 * Return whether the focused request is a floorplan.
 *
 * @param string $post_type Post type.
 */
function is_singular( $post_type ) {
	return 'floorplans' === $post_type;
}

/** Return the focused theme-template override path. */
function locate_template() {
	return $GLOBALS['rentfetch_test_theme_template'] ?? '';
}

/**
 * Return focused post metadata.
 *
 * @param int    $post_id Post ID.
 * @param string $key Meta key.
 * @return mixed
 */
function get_post_meta( $post_id, $key ) {
	$meta = array(
		1 => array(
			'floorplan_id' => 'plan-1',
			'property_id'  => 'property-1',
			'tour'         => 'https://youtu.be/manual-video',
			'synced_tours' => array(
				array(
					'type' => 'video',
					'url'  => 'https://youtu.be/synced-video',
				),
				array(
					'type' => 'virtual_tour',
					'url'  => 'https://my.matterport.com/show/?m=tour-one',
				),
			),
		),
		2 => array( 'amenities' => 'Balcony, Quartz Countertops' ),
		3 => array( 'amenities' => 'balcony; Pool View' ),
		6 => array( 'tour' => 'https://theviewvr.com/tour/example' ),
		7 => array(
			'synced_tours' => array(
				array(
					'type' => 'virtual_tour',
					'url'  => 'https://www.youtube.com/embed/video-in-virtual-field',
				),
				array(
					'type' => 'video',
					'url'  => 'https://tour.theviewvr.com/?locationId=2555',
				),
			),
		),
		8 => array(
			'synced_tours' => array(
				array(
					'type' => 'video',
					'url'  => 'https://www.zillow.com/view-3d-home/017a85bd-6596-47d7-95c2-514bb27cacdc',
				),
			),
		),
		9 => array(
			'unit_image_urls' => array(
				'https://example.com/unit-1.jpg',
				'https://example.com/unit-2.jpg',
			),
		),
		10 => array(
			'yardi_unit_image_urls' => array( 'https://example.com/unit-3.jpg,https://example.com/unit-4.jpg' ),
		),
	);

	return $meta[ $post_id ][ $key ] ?? '';
}

/** Return the unit IDs used by the aggregation check. */
function get_posts() {
	return array( 2, 3 );
}

/**
 * Return the unmodified filtered value.
 *
 * @param string $hook Filter name.
 * @param mixed  $value Filtered value.
 * @return mixed
 */
function apply_filters( $hook, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
	return $value;
}

/**
 * Minimal URL parser.
 *
 * @param string $url       URL to parse.
 * @param int    $component Optional URL component.
 * @return array|false
 */
function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
}

/**
 * Minimal URL sanitizer.
 *
 * @param string $url URL to sanitize.
 * @return string
 */
function esc_url_raw( $url ) {
	return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
}

/** Minimal escaped URL output. */
function esc_url( $url ) {
	return esc_url_raw( $url );
}

/**
 * Return escaped attribute text.
 *
 * @param mixed $value Attribute text.
 */
function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES );
}

/**
 * Return escaped visible text.
 *
 * @param mixed $value Visible text.
 */
function esc_html( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES );
}

/** No-op script enqueue for the focused check. */
function wp_enqueue_script() {}

/** No-op style enqueue for the focused check. */
function wp_enqueue_style() {}

/** Minimal HTML class sanitizer. */
function sanitize_html_class( $value ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $value );
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

require_once dirname( __DIR__ ) . '/lib/common/functions-tours.php';
require_once dirname( __DIR__ ) . '/lib/common/functions-units.php';
require_once dirname( __DIR__ ) . '/lib/template-functions/single-floorplans-parts/single-floorplans-parts.php';

$GLOBALS['rentfetch_test_added_actions']   = array();
$GLOBALS['rentfetch_test_removed_actions'] = array();
$GLOBALS['rentfetch_test_theme_template']  = '/theme/single-floorplans.php';
rentfetch_single_floorplans_set_up_unit_table();
assert( array() === $GLOBALS['rentfetch_test_added_actions'] );
assert( array() === $GLOBALS['rentfetch_test_removed_actions'] );

$GLOBALS['rentfetch_test_theme_template'] = '';
rentfetch_single_floorplans_set_up_unit_table();
assert( in_array( array( 'rentfetch_floorplan_do_unit_table', 'rentfetch_single_floorplan_unit_table' ), $GLOBALS['rentfetch_test_added_actions'], true ) );
assert( in_array( array( 'rentfetch_floorplan_do_unit_table', 'rentfetch_floorplan_unit_table' ), $GLOBALS['rentfetch_test_removed_actions'], true ) );
assert( in_array( array( 'rentfetch_floorplan_do_unit_table', 'rentfetch_floorplan_unit_list' ), $GLOBALS['rentfetch_test_removed_actions'], true ) );

assert( array( 'Balcony', 'Quartz Countertops' ) === rentfetch_single_floorplan_get_unit_amenities( ' Balcony, Quartz Countertops, Balcony ' ) );
assert( array( 'Patio', 'Pool View' ) === rentfetch_single_floorplan_get_unit_amenities( array( 'Patio', '', 'Pool View' ) ) );
assert( array( 'Balcony', 'Quartz Countertops', 'Pool View' ) === rentfetch_single_floorplan_get_aggregated_unit_amenities() );

$featured_media = rentfetch_single_floorplan_get_featured_media();
assert( 'https://www.youtube.com/embed/manual-video' === $featured_media['video']['embed_url'] );
assert( 'https://my.matterport.com/show/?m=tour-one' === $featured_media['tour']['embed_url'] );
assert( 'virtual_tour' === rentfetch_single_floorplan_get_featured_media( 6 )['tour']['type'] );
assert( 'youtube' === rentfetch_single_floorplan_get_featured_media( 7 )['video']['type'] );
assert( 'virtual_tour' === rentfetch_single_floorplan_get_featured_media( 7 )['tour']['type'] );
assert( 'virtual_tour' === rentfetch_single_floorplan_get_featured_media( 8 )['tour']['type'] );
assert( array( 'https://example.com/unit-1.jpg', 'https://example.com/unit-2.jpg' ) === rentfetch_get_unit_image_urls( 9 ) );
assert( array( 'https://example.com/unit-3.jpg', 'https://example.com/unit-4.jpg' ) === rentfetch_get_unit_image_urls( 10 ) );

ob_start();
rentfetch_unit_image_gallery( 11, 'table', array( 'https://example.com/floorplan.jpg' ) );
$fallback_gallery = ob_get_clean();
assert( false !== strpos( $fallback_gallery, 'href="https://example.com/floorplan.jpg"' ) );

ob_start();
rentfetch_single_floorplan_media_tabs(
	array(
		'video' => null,
		'tour'  => null,
	)
);
$photos_only = ob_get_clean();
assert( 'floorplan-images' === $photos_only );
assert( false === strpos( $photos_only, 'floorplan-media-tabs' ) );
echo "Single floorplan part checks passed.\n";
