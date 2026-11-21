<?php
/** Provider-neutral, minimized diagnostics for the signed monitoring snapshot. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Export only selected diagnostic text, never URLs or common credential assignments. */
function rentfetch_monitoring_error_text( $value ) {
	$text = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
	$text = preg_replace( '~https?://\S+~i', '[URL omitted]', $text );
	$text = preg_replace( '/\bBearer\s+\S+/i', 'Bearer [redacted]', $text );
	$text = preg_replace( '/\b(password|(?:api[_ -]?)?token|secret|authorization|api[_ -]?key)["\']?\s*[:=]\s*(?:"[^"]*"|\'[^\']*\'|[^\s,}]+)/i', '$1=[redacted]', $text );
	return mb_substr( $text, 0, 500 );
}

/** Read error fields from supported provider response shapes without returning payloads. */
function rentfetch_get_monitoring_response_error_message( $response ) {
	$payload = $response['api_response'] ?? array();
	$payload = is_string( $payload ) ? json_decode( $payload, true ) : $payload;
	$candidates = array(
		$response['error_message'] ?? '',
		$response['message'] ?? '',
	);
	if ( is_array( $payload ) ) {
		$candidates = array_merge( $candidates, array(
			$payload['errorMessage'] ?? '',
			$payload['error_message'] ?? '',
			$payload['message'] ?? '',
			$payload['Message'] ?? '',
			$payload['error']['message'] ?? '',
			$payload['response']['error']['message'] ?? '',
			is_string( $payload['error'] ?? null ) ? $payload['error'] : '',
		) );
	}
	foreach ( $candidates as $candidate ) {
		if ( is_scalar( $candidate ) && '' !== trim( (string) $candidate ) ) {
			return rentfetch_monitoring_error_text( $candidate );
		}
	}
	return '';
}

/**
 * Export current failures across a property's saved property, floor plan and unit responses.
 *
 * Keep the legacy Yardi request-wide recovery logic and the existing endpoint_errors
 * shape. Raw API bodies, request headers and credentials stay on the client site.
 *
 * @param int   $post_id       Property post ID.
 * @param int[] $floorplan_ids Related floor plan IDs.
 * @param int[] $unit_ids      Related unit IDs.
 * @return array
 */
function rentfetch_get_monitoring_endpoint_errors( $post_id, $floorplan_ids, $unit_ids ) {
	$source = strtolower( trim( (string) get_post_meta( $post_id, 'property_source', true ) ) );
	if ( '' === $source || 'manual' === $source ) {
		return array();
	}
	$labels = array( 'yardi' => 'Yardi/RentCafe', 'rentmanager' => 'Rent Manager', 'entrata' => 'Entrata', 'realpage' => 'RealPage', 'engrain' => 'Engrain', 'rentegi' => 'RentEGI' );
	$errors = rentfetch_get_monitoring_floorplan_errors( $post_id, $floorplan_ids );
	$affected = array();
	$records = array( 'properties' => array( $post_id ), 'floorplans' => $floorplan_ids, 'units' => $unit_ids );
	$expected = array();
	foreach ( $errors as $endpoint => &$error ) {
		$error['message'] = rentfetch_monitoring_error_text( $error['message'] );
		$error['scope'] = 'floorplans';
		$affected[ $endpoint ] = $floorplan_ids;
	}
	unset( $error );
	if ( function_exists( 'rfs_get_expected_sync_endpoints' ) ) {
		foreach ( array_keys( $records ) as $type ) {
			$expected = array_merge( $expected, (array) rfs_get_expected_sync_endpoints( $type, $source ) );
		}
	}
	foreach ( $records as $type => $ids ) {
		foreach ( $ids as $id ) {
			$meta_key = array( 'properties' => 'property_source', 'floorplans' => 'floorplan_source', 'units' => 'unit_source' )[ $type ];
			$record_source = (string) get_post_meta( $id, $meta_key, true );
			if ( 'units' === $type && '' === $record_source ) {
				$record_source = (string) get_post_meta( $id, 'floorplan_source', true );
			}
			if ( $source !== strtolower( trim( $record_source ) ) ) {
				continue;
			}
			$responses = get_post_meta( $id, 'api_response', true );
			$responses = is_array( $responses ) ? $responses : array();
			$states = get_post_meta( $id, 'sync_status', true );
			$states = is_array( $states ) ? $states : array();
			foreach ( array_unique( array_merge( array_keys( $responses ), array_keys( $states ) ) ) as $endpoint ) {
				if ( ! is_string( $endpoint ) || ( $expected && ! in_array( $endpoint, $expected, true ) ) || ( 'yardi' === $source && 'properties' === $type && 'floorplans_api' === $endpoint ) ) {
					continue;
				}
				$response = is_array( $responses[ $endpoint ] ?? null ) ? $responses[ $endpoint ] : array();
				$state = is_array( $states[ $endpoint ] ?? null ) ? $states[ $endpoint ] : array();
				$status = is_numeric( $response['status_code'] ?? null ) ? (int) $response['status_code'] : 0;
				$reason = is_scalar( $response['reason'] ?? null ) ? (string) $response['reason'] : '';
				$attempt = (string) ( $response['updated'] ?? $state['last_attempt_at'] ?? '' );
				$success = (string) ( $state['last_success_at'] ?? '' );
				$attempt_utc = '' !== $attempt ? get_gmt_from_date( $attempt, 'c' ) : '';
				if ( 'partial' === ( $state['state'] ?? '' ) || ( 'success' === ( $state['state'] ?? '' ) && ( '' === $attempt || strtotime( $success ) >= strtotime( $attempt ) ) ) ) {
					continue;
				}
				$failed_reason = preg_match( '/^(missing_|invalid_|unexpected_|blank_response|request_error|availability_(?:request_)?failed)/', $reason );
				if ( 'failed' !== ( $state['state'] ?? '' ) && ( 204 === $status || ( $status < 400 && ! $failed_reason && empty( $response['error_message'] ) ) ) ) {
					continue;
				}
				$scope = $type;
				if ( 'properties' === $type ) {
					if ( preg_match( '/floorplan|unit_types/', $endpoint ) ) {
						$scope = 'floorplans';
					} elseif ( in_array( $endpoint, array( 'units_api', 'unit_availability_api', 'apartmentavailability_api', 'getUnitsAvailabilityAndPricing' ), true ) ) {
						$scope = 'units';
					}
				}
				if ( 'rentegi_feed_api' === $endpoint ) {
					$scope = 'inventory';
				}
				$scope_ids = 'properties' === $type && isset( $records[ $scope ] ) ? $records[ $scope ] : array( $id );
				$affected[ $endpoint ] = array_unique( array_merge( $affected[ $endpoint ] ?? array(), $scope_ids ) );
				if ( ! isset( $errors[ $endpoint ] ) || strtotime( $attempt_utc ) >= strtotime( $errors[ $endpoint ]['last_attempt_at'] ) ) {
					$errors[ $endpoint ] = array(
						'api'              => ( $labels[ $source ] ?? ucfirst( $source ) ) . ' ' . ucwords( str_replace( '_', ' ', $endpoint ) ),
						'endpoint'         => sanitize_key( $endpoint ),
						'status_code'      => $status,
						'message'          => rentfetch_get_monitoring_response_error_message( $response ),
						'reason'           => sanitize_key( $reason ),
						'last_attempt_at'  => $attempt_utc,
						'last_success_at'  => '' !== $success ? get_gmt_from_date( $success, 'c' ) : '',
						'affected_records' => 0,
						'scope'            => $scope,
					);
				}
				$errors[ $endpoint ]['affected_records'] = count( $affected[ $endpoint ] );
			}
		}
	}
	return $errors;
}
