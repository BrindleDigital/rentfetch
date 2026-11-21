<?php
/** Read-only WP-CLI regressions; metadata is intercepted, no provider requests. */
require_once dirname( __DIR__ ) . '/lib/initialization/monitoring-status.php';
require_once dirname( __DIR__ ) . '/lib/initialization/monitoring-endpoint-errors.php';

$fixture = array();
$metadata = static function( $value, $id, $key ) use ( &$fixture ) {
	return isset( $fixture[ $id ] ) ? array( $fixture[ $id ][ $key ] ?? '' ) : $value;
};
$check = static function( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
};
$timezone = static function() { return 'America/New_York'; };
add_filter( 'get_post_metadata', $metadata, 10, 3 );
add_filter( 'pre_option_timezone_string', $timezone );
try {
	$payloads = array(
		'yardi' => array( 'errorMessage' => 'Access denied.' ),
		'rentmanager' => array( 'Message' => 'Access denied.' ),
		'entrata' => array( 'response' => array( 'error' => array( 'message' => 'Access denied.' ) ) ),
		'realpage' => array( 'error' => array( 'message' => 'Access denied.' ) ),
		'engrain' => array( 'message' => 'Access denied.' ),
		'rentegi' => array(),
	);
	foreach ( $payloads as $source => $payload ) {
		$endpoint = array( 'engrain' => 'engrain_asset_api', 'rentegi' => 'rentegi_feed_api' )[ $source ] ?? 'properties_api';
		$fixture = array( 910001 => array(
			'property_source' => $source,
			'api_response' => array( $endpoint => array(
				'updated' => '2026-11-20 12:00:00', 'status_code' => 403, 'reason' => 'unexpected_response_code',
				'api_response' => wp_json_encode( $payload + array( 'apiToken' => 'do-not-export', 'tenants' => array( 'Private Person' ) ) ),
				'error_message' => 'rentegi' === $source ? 'Access denied.' : '',
			) ),
			'sync_status' => array( $endpoint => array( 'state' => 'failed', 'last_success_at' => '2026-11-19 10:00:00' ) ),
		) );
		$errors = rentfetch_get_monitoring_endpoint_errors( 910001, array(), array() );
		$error = $errors[ $endpoint ] ?? array();
		$check( 'Access denied.' === ( $error['message'] ?? '' ) && 403 === $error['status_code'] && 1 === $error['affected_records'], $source . ' diagnostics were not exported.' );
		$check( '2026-11-20T17:00:00+00:00' === $error['last_attempt_at'], 'Attempt timezone must be explicit.' );
		$encoded = wp_json_encode( $errors );
		$check( ! str_contains( $encoded, 'do-not-export' ) && ! str_contains( $encoded, 'Private Person' ) && ! str_contains( $encoded, 'api_response' ), 'Raw payloads must never leave the site.' );
		$fixture[910001]['sync_status'][$endpoint] = array( 'state' => 'success', 'last_success_at' => '2026-11-21 12:00:00' );
		$check( array() === rentfetch_get_monitoring_endpoint_errors( 910001, array(), array() ), 'Recovery must suppress an old failure.' );
	}
	$fixture = array(
		910001 => array( 'property_source' => 'rentmanager', 'api_response' => array( 'unit_types_api' => array( 'updated' => '2026-11-20 12:00:00', 'status_code' => 200, 'reason' => 'missing_unit_type_ids' ) ) ),
		910002 => array( 'floorplan_source' => 'rentmanager' ),
		910003 => array( 'floorplan_source' => 'rentmanager' ),
	);
	$error = rentfetch_get_monitoring_endpoint_errors( 910001, array( 910002, 910003 ), array() )['unit_types_api'];
	$check( 'floorplans' === $error['scope'] && 2 === $error['affected_records'] && 'missing_unit_type_ids' === $error['reason'], 'A malformed successful HTTP response must retain its explicit failure reason even without sync_status.' );
	unset( $fixture[910001]['api_response'] );
	foreach ( array( 910004, 910005 ) as $id ) {
		$fixture[$id] = array( 'unit_source' => 'rentmanager', 'sync_status' => array( 'units_api' => array( 'state' => 'failed', 'last_attempt_at' => '2026-11-20 12:01:00' ) ), 'api_response' => array( 'units_api' => array( 'status_code' => 502, 'error_message' => 'Network error. token=private-token Authorization: Bearer private-bearer https://user:pass@example.invalid/data' ) ) );
	}
	$error = rentfetch_get_monitoring_endpoint_errors( 910001, array(), array( 910004, 910005 ) )['units_api'];
	$check( 'units' === $error['scope'] && 2 === $error['affected_records'], 'Child errors must be counted and identified.' );
	$check( ! str_contains( $error['message'], 'private-token' ) && ! str_contains( $error['message'], 'private-bearer' ) && ! str_contains( $error['message'], 'user:pass' ), 'Credential assignments, bearer tokens, and URLs must be redacted from messages.' );
	$fixture[910005]['sync_status']['units_api']['last_attempt_at'] = '2026-11-20 12:02:00';
	$fixture[910005]['api_response']['units_api']['error_message'] = 'Latest failure.';
	$check( 'Latest failure.' === rentfetch_get_monitoring_endpoint_errors( 910001, array(), array( 910004, 910005 ) )['units_api']['message'], 'Grouping must retain the latest failure across site-local and exported UTC timestamps.' );
	$fixture[910005]['unit_source'] = 'entrata';
	$check( 1 === rentfetch_get_monitoring_endpoint_errors( 910001, array(), array( 910004, 910005 ) )['units_api']['affected_records'], 'Child diagnostics must stay with their integration.' );
	$fixture[910004]['sync_status']['units_api']['state'] = 'partial';
	$check( array() === rentfetch_get_monitoring_endpoint_errors( 910001, array(), array( 910004 ) ), 'Partial enrichment must not become a required API failure.' );
	$fixture[910001]['property_source'] = 'manual';
	$check( array() === rentfetch_get_monitoring_endpoint_errors( 910001, array( 910002 ), array( 910004 ) ), 'Manual properties must not export historical API failures.' );
} finally {
	remove_filter( 'get_post_metadata', $metadata, 10 );
	remove_filter( 'pre_option_timezone_string', $timezone );
}
echo "All-provider endpoint diagnostics, child scope, recovery, redaction and timezone checks passed.\n";
