<?php
/**
 * Run with: wp eval-file wp-content/plugins/rentfetch/tests/test-property-description.php
 *
 * @package rentfetch
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

$original_user    = get_current_user_id();
$original_post    = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Preserve CLI test state.
$test_property_id = 0;
$check            = static function ( $condition, $message ) {
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
			'post_type'  => 'properties',
			'post_title' => 'Rent Fetch description regression test',
		),
		true
	);
	$check( ! is_wp_error( $test_property_id ), 'Could not create the test property.' );
	update_post_meta( $test_property_id, 'property_id', 'rentfetch-description-test-' . $test_property_id );
	update_post_meta( $test_property_id, 'description', '<p>Synced property description</p>' );
	$nonce = wp_create_nonce( 'rentfetch_properties_metabox_nonce' );

	ob_start();
	rentfetch_properties_display_information_metabox_callback( get_post( $test_property_id ) );
	$html = ob_get_clean();
	$check( false !== strpos( $html, 'name="rentfetch_property_description"' ), 'Property description form name is not namespaced.' );
	$check( false !== strpos( $html, 'id="rentfetch_property_description"' ), 'Property description editor ID is not namespaced.' );
	$check( false !== strpos( $html, 'for="rentfetch_property_description"' ), 'Property description label does not target the editor.' );

	// SEOPress submits a generic description even when our field is absent or disabled.
	$_POST = array(
		'rentfetch_properties_metabox_nonce' => $nonce,
		'description'                        => 'SEO summary',
	);
	wp_update_post( array( 'ID' => $test_property_id ) );
	$check( '<p>Synced property description</p>' === get_post_meta( $test_property_id, 'description', true ), 'SEO field overwrote the synced description.' );

	$_POST['rentfetch_property_description'] = wp_slash( "<p>Property's full description</p><script>alert(1)</script>" );
	wp_update_post( array( 'ID' => $test_property_id ) );
	$check( "<p>Property's full description</p>alert(1)" === get_post_meta( $test_property_id, 'description', true ), 'Property description was not independently saved and sanitized to the existing meta key.' );
	$check( ! metadata_exists( 'post', $test_property_id, 'rentfetch_property_description' ), 'A new description meta key was created.' );

	$_POST['rentfetch_property_description'] = '';
	wp_update_post( array( 'ID' => $test_property_id ) );
	$check( '' === get_post_meta( $test_property_id, 'description', true ), 'An intentional empty property description was not saved.' );
	WP_CLI::success( 'Property description rendering, SEO isolation, existing metadata, sanitization, and clearing passed.' );
} finally {
	$_POST = array();
	if ( $test_property_id && ! is_wp_error( $test_property_id ) ) {
		wp_delete_post( $test_property_id, true );
	}
	$_POST = $original_post;
	wp_set_current_user( $original_user );
}
