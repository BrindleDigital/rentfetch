<?php
/**
 * Run on a local site with Rent Fetch active:
 * wp eval-file wp-content/plugins/rentfetch/tests/test-property-logo.php
 *
 * @package rentfetch
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

$original_user = get_current_user_id();
$original_post = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Preserve CLI test state.
$test_ids      = array();
$check         = static function ( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( esc_html( $message ) );
	}
};

try {
	$admins = get_users(
		array(
			'role'   => 'administrator',
			'number' => 1,
			'fields' => 'ID',
		)
	);
	$check( ! empty( $admins ), 'An administrator is required.' );
	wp_set_current_user( $admins[0] );
	$_POST            = array();
	$test_property_id = wp_insert_post(
		array(
			'post_type'   => 'properties',
			'post_status' => 'publish',
			'post_title'  => 'Rent Fetch logo test',
		),
		true
	);
	$check( ! is_wp_error( $test_property_id ), 'Could not create the test property.' );
	$test_ids[] = $test_property_id;
	// Use an attachment record and a unique nonexistent file path; no files are created.
	$image_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => 'Rent Fetch logo attachment test',
		),
		false,
		0,
		true
	);
	$check( ! is_wp_error( $image_id ), 'Could not create the test attachment.' );
	$test_ids[] = $image_id;
	update_attached_file( $image_id, wp_upload_dir()['basedir'] . '/rentfetch-logo-test-' . $image_id . '.png' );
	$valid_post   = array(
		'rentfetch_property_logo_nonce' => wp_create_nonce( 'rentfetch_property_logo' ),
		'rentfetch_property_logo_id'    => (string) $image_id,
	);
	$save         = static function () use ( $test_property_id ) {
		wp_update_post( array( 'ID' => $test_property_id ) );
	};
	$verify_saved = static function () use ( $check, $test_property_id, $image_id ) {
		$check( (int) get_post_meta( $test_property_id, 'property_logo_id', true ) === $image_id, 'Logo did not persist.' );
	};

	update_post_meta( $test_property_id, 'property_id', 'rentfetch-logo-test-' . $test_property_id );
	update_post_meta( $test_property_id, '_land_co_property_logo_id', $image_id );
	$check( rentfetch_get_property_logo_id( $test_property_id ) === $image_id, 'Existing logo selection was lost.' );
	$_POST = $valid_post;
	$save();
	$verify_saved();
	$check( ! metadata_exists( 'post', $test_property_id, '_land_co_property_logo_id' ), 'Legacy logo was not migrated on save.' );

	// Check placement through the registered single-property sections.
	update_post_meta( $test_property_id, 'phone', '(616) 555-0101' );
	update_post_meta( $test_property_id, 'address', '123 Test Street' );
	$previous_global_post = $GLOBALS['post'] ?? null;
	$GLOBALS['post']      = get_post( $test_property_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restore the render context below.
	add_filter( 'rentfetch_maybe_do_property_part_details', '__return_true', 99 );
	add_filter( 'rentfetch_maybe_do_property_part_floorplans', '__return_false', 99 );
	try {
		ob_start();
		do_action( 'rentfetch_do_single_properties_parts' );
		$html        = ob_get_clean();
		$property_id = 'rentfetch-logo-test-' . $test_property_id;
		$shortcode   = '[rentfetch_property_info info="logo" property_id="' . $property_id . '" class="custom-logo"]';
		$check( false !== strpos( do_shortcode( $shortcode ), 'rentfetch-property-logo custom-logo' ), 'Logo shortcode or CSS class failed.' );
		$check( false !== strpos( do_shortcode( '[rentfetch_property_info info="logo"]' ), 'rentfetch-property-logo' ), 'Current-property shortcode context failed.' );
		$check( '' === rentfetch_get_property_logo( 'missing-property-logo-test' ), 'Unknown property returned a logo.' );
		$dom = new DOMDocument();
		$dom->loadHTML( $html, LIBXML_NOERROR | LIBXML_NOWARNING );
		$xpath = new DOMXPath( $dom );
		$check( 1 === $xpath->query( '//div[@id="details"]' )->length, 'The details section must render exactly once.' );
		$logo = $xpath->query( '//div[@class="property-links"]/*[1][@class="rentfetch-property-logo"]' );
		$check( 1 === $logo->length, 'Logo is not the first item in the sidebar.' );
		$check( ! $logo->item( 0 )->hasAttribute( 'style' ), 'Logo presentation must be controlled by CSS.' );
		$check( 1 === $xpath->query( '//img[@class="rentfetch-property-logo"]' )->length, 'Logo must render exactly once.' );
		$check( 1 === $xpath->query( '//div[@class="property-links"]/*[2][contains(@class,"property-sidebar-address")]' )->length, 'Logo is not immediately above the address.' );
		delete_post_meta( $test_property_id, 'property_logo_id' );
		ob_start();
		do_action( 'rentfetch_do_single_properties_parts' );
		$html = ob_get_clean();
		$check( false === strpos( $html, 'rentfetch-property-logo' ), 'An empty logo was rendered.' );
		$check( '' === do_shortcode( $shortcode ), 'A missing logo returned shortcode content.' );
		$check( '' === do_shortcode( '[rentfetch_property_info info="logo" before="<div>" after="</div>"]' ), 'A missing logo returned wrappers.' );
		$_POST = $valid_post;
		$save();
	} finally {
		$GLOBALS['post'] = $previous_global_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restore the original render context.
		remove_filter( 'rentfetch_maybe_do_property_part_details', '__return_true', 99 );
		remove_filter( 'rentfetch_maybe_do_property_part_floorplans', '__return_false', 99 );
	}

	$_POST = array();
	$save();
	$verify_saved();
	$_POST = array_merge(
		$valid_post,
		array(
			'rentfetch_property_logo_nonce' => 'invalid',
			'rentfetch_property_logo_id'    => '0',
		)
	);
	$save();
	$verify_saved();
	$_POST = array_merge(
		$valid_post,
		array(
			'rentfetch_property_logo_id' => array( $image_id ),
		)
	);
	$save();
	$verify_saved();
	foreach ( array( 'malformed', '-1', (string) $test_property_id ) as $invalid_id ) {
		$_POST = array_merge( $valid_post, array( 'rentfetch_property_logo_id' => $invalid_id ) );
		$save();
		$verify_saved();
	}
	wp_set_current_user( 0 );
	$_POST = array_merge(
		$valid_post,
		array(
			'rentfetch_property_logo_id' => '0',
		)
	);
	$save();
	$verify_saved();
	wp_set_current_user( $admins[0] );
	$_POST = $valid_post;
	rentfetch_save_property_logo( $image_id );
	$check( ! metadata_exists( 'post', $image_id, 'property_logo_id' ), 'Fields saved on the wrong post type.' );
	$_POST = array_merge(
		$valid_post,
		array(
			'rentfetch_property_logo_id' => '0',
		)
	);
	$save();
	$check( ! metadata_exists( 'post', $test_property_id, 'property_logo_id' ), 'Logo removal failed.' );
	WP_CLI::success( 'Property logo rendering, shortcode, compatibility, save, removal, and authorization checks passed.' );
} finally {
	$_POST = array();
	foreach ( array_reverse( $test_ids ) as $test_id ) {
		wp_delete_post( $test_id, true );
	}
	$_POST = $original_post;
	wp_set_current_user( $original_user );
}
