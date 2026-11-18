<?php
/** Run with Cove WP-CLI eval-file --skip-plugins --skip-themes; no writes or outbound requests. */
require_once dirname( __DIR__ ) . '/lib/initialization/monitoring-status.php';
$fixture = array(
    910001 => array( 'property_source' => 'yardi', 'api_response' => array( 'floorplans_api' => array(
        'updated' => '2026-11-17 14:29:54', 'status_code' => 403, 'reason' => 'unexpected_response_code',
        'api_response' => '{"floorplans":null,"errorCode":403,"errorMessage":"No active property found.","apiToken":"must-not-export"}',
    ) ) ),
    910002 => array( 'last_synced_at' => '2026-11-09 10:51:53' ),
    910003 => array( 'last_synced_at' => '2026-11-09 10:51:54' ),
);
$metadata = static function( $value, $id, $key, $single ) use ( &$fixture ) {
    return isset( $fixture[$id] ) ? array( $fixture[$id][$key] ?? '' ) : $value;
};
$timezone = static function() { return 'America/New_York'; };
$check = static function( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } };
add_filter( 'get_post_metadata', $metadata, 10, 4 );
add_filter( 'pre_option_timezone_string', $timezone );
try {
    $get = static function() { return rentfetch_get_monitoring_floorplan_errors( 910001, array(910002, 910003) ); };
    $errors = $get();
    $error = $errors['floorplans_api'];
    $check( 403 === $error['status_code'] && 'No active property found.' === $error['message'] && 2 === $error['affected_records'], 'The property-wide error must identify its scope and upstream message.' );
    $check( '/floorplan/getfloorplans' === $error['endpoint'] && 'Yardi/RentCafe Floor Plans' === $error['api'], 'The specific failing API must be named.' );
    $check( '2026-11-17T19:29:54+00:00' === $error['last_attempt_at'] && '2026-11-09T15:51:53+00:00' === $error['last_success_at'], 'Site-local dates must be exported with a timezone.' );
    $check( false === strpos( wp_json_encode( $errors ), 'must-not-export' ), 'Raw API data must not leak into monitoring.' );
    $fixture[910002]['last_synced_at'] = '2026-11-18 10:00:00';
    $check( ! empty( $get() ), 'A single recovered plan must not hide an unresolved property-wide failure.' );
    $fixture[910003]['last_synced_at'] = '2026-11-18 10:00:01';
    $check( array() === $get(), 'A newer full floorplan refresh must suppress the old failure snapshot.' );
    $fixture[910003]['last_synced_at'] = '';
    $check( ! empty( $get() ), 'Unknown success dates must not hide the failure.' );
    $fixture[910001]['api_response']['floorplans_api']['status_code'] = 204;
    $fixture[910001]['api_response']['floorplans_api']['reason'] = 'blank_response';
    $check( array() === $get(), 'A valid 204 response is not a request failure.' );
    $fixture[910001]['api_response']['floorplans_api']['status_code'] = 200;
    $fixture[910001]['api_response']['floorplans_api']['api_response'] = '';
    $check( '' === $get()['floorplans_api']['message'], 'A blank 200 response must remain a failure without inventing an upstream message.' );
    $fixture[910001]['property_source'] = 'manual';
    $check( array() === $get(), 'Historical Yardi snapshots must not affect manual properties.' );
} finally {
    remove_filter( 'get_post_metadata', $metadata, 10 );
    remove_filter( 'pre_option_timezone_string', $timezone );
}
echo "Floorplan monitoring errors, recovery, timezone and data minimization passed.\n";
