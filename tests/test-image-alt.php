<?php
/**
 * Run: php -d zend.assertions=1 -d assert.exception=1 tests/test-image-alt.php
 *
 * @package rentfetch
 */
define( 'ABSPATH', __DIR__ );
define( 'RENTFETCH_PATH', '' );
function wp_parse_url( $value ) { return parse_url( $value ); }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function esc_url_raw( $url ) { return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : ''; }
function esc_url( $url ) { return htmlspecialchars( esc_url_raw( $url ), ENT_QUOTES ); }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function sanitize_html_class( $value ) { return $value; }
function add_action() {}
function add_filter() {}
function wp_enqueue_script() {}
function wp_enqueue_style() {}
function apply_filters( $hook, $value ) { return $value; }
function get_the_ID() { return 1; }
function get_the_title( $id = null ) { return 2 === $id ? 'Birch floor plan' : 'Apartment 101'; }
function get_posts() { return array( 2 ); }
function rentfetch_resolve_floorplan_post_id( $unit_id ) { return 2; }
function get_post_meta( $id, $key, $single = true ) { return $GLOBALS['image_test_meta'][ $key ] ?? ''; }

require dirname( __DIR__ ) . '/lib/common/functions-image-alt.php';
require dirname( __DIR__ ) . '/lib/common/functions-floorplan-images.php';
require dirname( __DIR__ ) . '/lib/common/functions-property-images.php';
require dirname( __DIR__ ) . '/lib/common/functions-units.php';
require dirname( __DIR__ ) . '/lib/template-functions/floorplan-archives/floorplan-images.php';

$first = 'https://example.com/kitchen.jpg';
$second = 'https://example.com/bedroom.jpg';
assert( 'Kitchen, dining room & balcony' === rentfetch_get_yardi_image_alts( $first, 'Kitchen, dining room & balcony' )[ $first ] );
assert( array( $first => 'Kitchen', $second => '' ) === rentfetch_get_yardi_image_alts( "$first,$second", 'Kitchen,' ) );
assert( array() === rentfetch_get_yardi_image_alts( "$first,$second", 'Kitchen, dining room,Bedroom' ) );
assert( array() === rentfetch_get_yardi_image_alts( array(), '' ) );
assert( 'Birch' === rentfetch_get_image_alt( '  ', 'Birch' ) );
assert( 'Apartment image' === rentfetch_get_image_alt( '', '' ) );
assert( 'Kitchen & dining room' === rentfetch_get_image_alt( '<b>Kitchen &amp; dining room</b>', 'Birch' ) );

$GLOBALS['post'] = (object) array( 'post_type' => 'floorplans' );
$GLOBALS['image_test_meta'] = array(
	'floorplan_source' => 'yardi',
	'floorplan_image_url' => "$first,$second",
	'floorplan_image_alt_text' => 'Kitchen & dining room,',
);
$images = rentfetch_get_floorplan_images_yardi();
assert( 'Kitchen & dining room' === $images[0]['alt'] );
assert( '' === $images[1]['alt'] );
ob_start();
rentfetch_floorplan_single_image();
assert( str_contains( ob_get_clean(), 'alt="Kitchen &amp; dining room"' ) );
$GLOBALS['image_test_meta']['floorplan_image_alt_text'] = '';
ob_start();
rentfetch_floorplan_single_image();
assert( str_contains( ob_get_clean(), 'alt="Apartment 101"' ) );

$GLOBALS['image_test_meta'] = array(
	'unit_source' => 'yardi',
	'floorplan_id' => 'birch',
	'property_id' => 'property',
	'unit_image_urls' => array( $second, $first ),
	'api_response' => array( 'apartmentavailability_api' => array( 'api_response' => json_encode( array(
		'unitImageURLsArray' => array( $first, $second ),
		'unitImageAltText' => 'Kitchen & dining room,',
	) ) ) ),
);
ob_start();
rentfetch_unit_image_gallery();
$html = ob_get_clean();
assert( str_contains( $html, 'src="https://example.com/kitchen.jpg" alt="Kitchen &amp; dining room"' ) );
assert( str_contains( $html, 'src="https://example.com/bedroom.jpg" alt="Birch floor plan"' ) );
// Missing or malformed responses must still produce a name fallback.
$GLOBALS['image_test_meta']['api_response'] = array( 'apartmentavailability_api' => array( 'api_response' => 'invalid JSON' ) );
ob_start();
rentfetch_unit_image_gallery();
assert( ! str_contains( ob_get_clean(), 'alt=""' ) );
$GLOBALS['image_test_meta']['unit_image_urls'] = array();
ob_start();
rentfetch_unit_image_gallery( null, 'table', array( array( 'url' => $first, 'alt' => 'Floorplan kitchen' ) ) );
assert( str_contains( ob_get_clean(), 'alt="Floorplan kitchen"' ) );
ob_start();
rentfetch_unit_image_gallery( null, 'table', array( $first ) );
assert( str_contains( ob_get_clean(), 'alt="Birch floor plan"' ) );
// Other providers retain image-specific descriptions before the name fallback.
$GLOBALS['image_test_meta'] = array(
	'floorplan_source' => 'rentmanager',
	'floorplan_image_url' => array( array( 'File' => array( 'DownloadURL' => $first, 'Description' => 'Kitchen window' ), 'Caption' => 'Caption' ) ),
);
assert( 'Kitchen window' === rentfetch_get_floorplan_images_rentmanager()[0]['alt'] );
$GLOBALS['image_test_meta']['floorplan_source'] = 'entrata';
$GLOBALS['image_test_meta']['floorplan_image_url'] = $first;
$GLOBALS['image_test_meta']['api_response'] = array( 'floorplans_api' => array( 'api_response' => array( 'File' => array( array( 'Src' => $first, 'Description' => 'Sunny kitchen' ) ) ) ) );
assert( 'Sunny kitchen' === rentfetch_get_floorplan_images_entrata()[0]['alt'] );
$GLOBALS['image_test_meta']['api_response']['floorplans_api']['api_response']['File'][0]['Description'] = ' ';
$GLOBALS['image_test_meta']['api_response']['floorplans_api']['api_response']['File'][0]['Caption'] = 'Sunny kitchen caption';
assert( 'Sunny kitchen caption' === rentfetch_get_floorplan_images_entrata()[0]['alt'] );
$GLOBALS['image_test_meta'] = array(
	'property_source' => 'rentmanager',
	'synced_property_images' => array( array(
		'File' => array( 'DownloadURL' => $first, 'Description' => 'Sunny kitchen' ),
		'ImageType' => array( 'Name' => 'Property Detail Images', 'Description' => 'Generic image category' ),
		'Caption' => 'Kitchen caption',
	) ),
);
assert( 'Sunny kitchen' === rentfetch_get_property_images_rentmanager( null )[0]['alt'] );
$GLOBALS['image_test_meta']['synced_property_images'][0]['File']['Description'] = ' ';
assert( 'Kitchen caption' === rentfetch_get_property_images_rentmanager( null )[0]['alt'] );
$GLOBALS['image_test_meta']['synced_property_images'][0]['Caption'] = '';
assert( '' === rentfetch_get_property_images_rentmanager( null )[0]['alt'] );
assert( 'Birch' === rentfetch_get_image_alt( rentfetch_get_property_images_rentmanager( null )[0]['alt'], 'Birch' ) );
$GLOBALS['image_test_meta'] = array(
	'property_source' => 'entrata',
	'synced_property_images' => array( array( 'Src' => $first, 'Description' => ' ', 'Caption' => 'Kitchen caption' ) ),
);
assert( 'Kitchen caption' === rentfetch_get_property_images_entrata( null )[0]['alt'] );
echo "Image alt checks passed.\n";
