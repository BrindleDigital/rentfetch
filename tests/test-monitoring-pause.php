<?php
/** Run through Cove WP-CLI eval-file with --skip-plugins --skip-themes; no settings or outbound requests. */
if ( ! defined( 'ABSPATH' ) ) {
    throw new RuntimeException( 'Run through WP-CLI eval-file.' );
}
require_once dirname( __DIR__ ) . '/lib/initialization/monitoring-status.php';
function rentfetch_pause_check( $condition, $message ) {
    if ( ! $condition ) { throw new RuntimeException( $message ); }
}
$original = get_option( 'rentfetch_options_data_sync' );
$key = openssl_pkey_new( array( 'private_key_bits' => 2048 ) );
rentfetch_pause_check( false !== $key, 'Could not generate a test signing key.' );
$public = openssl_pkey_get_details( $key )['key'];
$keys = function( $keys ) use ( $public ) { $keys['release-test'] = $public; return $keys; };
$no_posts = function() { return array(); };
$block_http = function() { throw new RuntimeException( 'Unexpected outbound request.' ); };
add_filter( 'rentfetch_monitoring_public_keys', $keys );
add_filter( 'posts_pre_query', $no_posts );
add_filter( 'pre_http_request', $block_http, PHP_INT_MAX );
$request_for = function( $timestamp, $host = null ) use ( $key ) {
    $request = new WP_REST_Request( 'GET', '/rentfetch/v1/monitoring/status' );
    $path = wp_parse_url( rest_url( 'rentfetch/v1/monitoring/status' ), PHP_URL_PATH );
    $canonical = rentfetch_build_monitoring_signature_payload( 'GET', $path, $host ?? rentfetch_get_monitoring_site_host(), (string) $timestamp );
    openssl_sign( $canonical, $signature, $key, OPENSSL_ALGO_SHA256 );
    $request->set_header( 'x-rf-monitoring-key-id', 'release-test' );
    $request->set_header( 'x-rf-monitoring-timestamp', (string) $timestamp );
    $request->set_header( 'x-rf-monitoring-signature', base64_encode( $signature ) );
    return $request;
};
try {
    foreach ( array( 'nosync' => true, 'updatesync' => false ) as $setting => $paused ) {
        $override = function() use ( $setting ) { return $setting; };
        add_filter( 'pre_option_rentfetch_options_data_sync', $override );
        try {
            $response = rest_do_request( $request_for( time() ) );
            $payload = $response->get_data();
            rentfetch_pause_check( 200 === $response->get_status() && $payload['site']['sync_paused'] === $paused, 'A signed monitoring request must report pause and resume as booleans.' );
        } finally { remove_filter( 'pre_option_rentfetch_options_data_sync', $override ); }
    }
    $unsigned = new WP_REST_Request( 'GET', '/rentfetch/v1/monitoring/status' );
    $unknown = $request_for( time() ); $unknown->set_header( 'x-rf-monitoring-key-id', 'unknown-release-test' );
    $invalid = $request_for( time() ); $invalid->set_header( 'x-rf-monitoring-signature', 'invalid-base64!' );
    foreach ( array( $unsigned, $request_for( time() - 601 ), $request_for( time() + 601 ), $request_for( time(), 'wrong-site.invalid' ), $unknown, $invalid ) as $request ) {
        rentfetch_pause_check( 403 === rest_do_request( $request )->get_status(), 'Unsigned, stale, future, wrong-host, unknown-key, and malformed signatures must be denied.' );
    }
} finally {
    remove_filter( 'rentfetch_monitoring_public_keys', $keys );
    remove_filter( 'posts_pre_query', $no_posts );
    remove_filter( 'pre_http_request', $block_http, PHP_INT_MAX );
}
rentfetch_pause_check( get_option( 'rentfetch_options_data_sync' ) === $original, 'The real sync setting must remain unchanged.' );
echo "Monitoring pause and signature security tests passed.\n";
