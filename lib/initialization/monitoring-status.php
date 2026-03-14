<?php
/**
 * Monitoring status endpoint and payload helpers.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the monitoring status route.
 *
 * @return void
 */
function rentfetch_register_monitoring_status_route() {
	register_rest_route(
		'rentfetch/v1',
		'/monitoring/status',
		array(
			'methods'             => 'GET',
			'callback'            => 'rentfetch_rest_monitoring_status',
			'permission_callback' => 'rentfetch_rest_monitoring_permission_check',
		)
	);
}
add_action( 'rest_api_init', 'rentfetch_register_monitoring_status_route' );

/**
 * Handle the monitoring status request.
 *
 * @param WP_REST_Request $request The REST request.
 * @return WP_REST_Response
 */
function rentfetch_rest_monitoring_status( $request ) {
	unset( $request );

	return rest_ensure_response( rentfetch_get_monitoring_status_payload() );
}

/**
 * Verify signed monitoring requests from the Rent Fetch API.
 *
 * @param WP_REST_Request $request The REST request.
 * @return true|WP_Error
 */
function rentfetch_rest_monitoring_permission_check( $request ) {
	if ( ! function_exists( 'openssl_verify' ) ) {
		return new WP_Error(
			'rest_forbidden',
			'Monitoring authentication requires OpenSSL.',
			array( 'status' => 500 )
		);
	}

	$key_id    = sanitize_text_field( (string) $request->get_header( 'x-rf-monitoring-key-id' ) );
	$timestamp = trim( (string) $request->get_header( 'x-rf-monitoring-timestamp' ) );
	$signature = trim( (string) $request->get_header( 'x-rf-monitoring-signature' ) );

	if ( '' === $key_id || '' === $timestamp || '' === $signature ) {
		return new WP_Error(
			'rest_forbidden',
			'Missing monitoring authentication headers.',
			array( 'status' => 403 )
		);
	}

	if ( ! ctype_digit( $timestamp ) ) {
		return new WP_Error(
			'rest_forbidden',
			'Invalid monitoring timestamp.',
			array( 'status' => 403 )
		);
	}

	$max_age = (int) apply_filters( 'rentfetch_monitoring_signature_max_age', 5 * MINUTE_IN_SECONDS );
	$age     = abs( time() - (int) $timestamp );

	if ( $age > $max_age ) {
		return new WP_Error(
			'rest_forbidden',
			'Expired monitoring signature.',
			array( 'status' => 403 )
		);
	}

	$public_key = rentfetch_get_monitoring_public_key( $key_id );

	if ( '' === $public_key ) {
		return new WP_Error(
			'rest_forbidden',
			'Unknown monitoring key.',
			array( 'status' => 403 )
		);
	}

	$decoded_signature = base64_decode( $signature, true );

	if ( false === $decoded_signature ) {
		return new WP_Error(
			'rest_forbidden',
			'Invalid monitoring signature encoding.',
			array( 'status' => 403 )
		);
	}

	$canonical_path = wp_parse_url( rest_url( ltrim( $request->get_route(), '/' ) ), PHP_URL_PATH );
	$canonical      = rentfetch_build_monitoring_signature_payload(
		'GET',
		is_string( $canonical_path ) ? $canonical_path : '/wp-json/rentfetch/v1/monitoring/status',
		rentfetch_get_monitoring_site_host(),
		$timestamp
	);

	$verified = openssl_verify( $canonical, $decoded_signature, $public_key, OPENSSL_ALGO_SHA256 );

	if ( 1 !== $verified ) {
		return new WP_Error(
			'rest_forbidden',
			'Invalid monitoring signature.',
			array( 'status' => 403 )
		);
	}

	return true;
}

/**
 * Get the configured public key for a monitoring key id.
 *
 * Supports either a single `RENTFETCH_MONITORING_PUBLIC_KEY` constant or a
 * keyed `RENTFETCH_MONITORING_PUBLIC_KEYS` array for future rotation.
 *
 * @param string $key_id The key identifier.
 * @return string
 */
function rentfetch_get_monitoring_public_key( $key_id ) {
	$keys = array();

	$stored_keys = get_option( 'rentfetch_monitoring_public_keys', array() );
	if ( is_array( $stored_keys ) ) {
		$keys = array_merge( $keys, $stored_keys );
	}

	$filtered_keys = apply_filters( 'rentfetch_monitoring_public_keys', array() );
	if ( is_array( $filtered_keys ) ) {
		$keys = array_merge( $keys, $filtered_keys );
	}

	if ( defined( 'RENTFETCH_MONITORING_PUBLIC_KEYS' ) && is_array( constant( 'RENTFETCH_MONITORING_PUBLIC_KEYS' ) ) ) {
		$keys = array_merge( $keys, constant( 'RENTFETCH_MONITORING_PUBLIC_KEYS' ) );
	}

	if ( defined( 'RENTFETCH_MONITORING_PUBLIC_KEY' ) ) {
		$default_key_id = defined( 'RENTFETCH_MONITORING_KEY_ID' ) ? (string) constant( 'RENTFETCH_MONITORING_KEY_ID' ) : 'monitoring-v1';
		$keys[ $default_key_id ] = (string) constant( 'RENTFETCH_MONITORING_PUBLIC_KEY' );
	}

	$keys = array_merge( rentfetch_get_monitoring_fallback_public_keys(), $keys );

	return isset( $keys[ $key_id ] ) ? trim( (string) $keys[ $key_id ] ) : '';
}

/**
 * Provide built-in fallback public keys for monitoring verification.
 *
 * This lets API-initiated polling work even before a client site has refreshed
 * its normal Rent Fetch Sync bootstrap payload and stored the monitoring key
 * in the local options table.
 *
 * @return array
 */
function rentfetch_get_monitoring_fallback_public_keys() {
	$fallback_keys = array(
		'monitoring-v1' => <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAsNLB+MwUpnNM1dhB6kE3
46XJ0HGRFC8+FcFYbANytbM7Q6KEMy3gP4k/4ZMIwuYRXMnHVCoE1XiISJq6gS1k
ZZuL1ZFz6AsP/rUdYpgtldtyXWsLpuAarRXVChPBD2tEQBmG/mqNG946ZIdZ4H3i
LPtTFMnzo5eNj4QE+FoDD8cQd3iAIQGMSn1f/pdwPVTXInE2PBDVhU1/uW4SvZUd
0AaLwBVngADvZGvcVulQj4ZCyvOSzotVFr1Epluozx9QVAaMm3pp3Arpy7U9bzwn
HplGHkYuZY5praoI+nBVIdyhIF59LtmYHLJzrdy6fgYLDyJVFvk19CFBxI+x7aHq
oYEQvbxsbbM7RJEAUoC/1Zwzl4gnDSmbHKCP5PRtO6w8HKDs4B9vILdcypSft3HY
Fg5LRoyEJm6K6kkgLT53eZf3e5lmWMBua4qp5ae19j1a1+EUmfZ7u8tNg/9trr3m
9MRSdwHmQXG6bZVElosdESy2/vfQ2zZIrlgKqfSMA6hGe7SsApkn6XC1D5sL3UP2
U1SxP/9og7ejLnW+mZECC7PpU1i4TRjWmL7V78//a64c1wEbmoc7zHIVRLXqXZT7
tqUIF5U0z2Q2qtxyUwxYXdeaqUeb7/SX4EcymcisVbvg8zRqFkXrPLqkHBL85Pqu
oufCffD+pLXe55uvp3M0zYUCAwEAAQ==
-----END PUBLIC KEY-----
PEM,
	);

	return apply_filters( 'rentfetch_monitoring_fallback_public_keys', $fallback_keys );
}

/**
 * Build the canonical signature payload.
 *
 * @param string $method    The HTTP method.
 * @param string $route     The REST route.
 * @param string $site_host The current site host.
 * @param string $timestamp The request timestamp.
 * @return string
 */
function rentfetch_build_monitoring_signature_payload( $method, $route, $site_host, $timestamp ) {
	return implode(
		"\n",
		array(
			strtoupper( trim( (string) $method ) ),
			'/' . ltrim( trim( (string) $route ), '/' ),
			strtolower( trim( (string) $site_host ) ),
			trim( (string) $timestamp ),
		)
	);
}

/**
 * Build the monitoring payload consumed by the central API.
 *
 * @return array
 */
function rentfetch_get_monitoring_status_payload() {
	return array(
		'site'             => array(
			'site_url'              => untrailingslashit( get_site_url() ),
			'site_host'             => rentfetch_get_monitoring_site_host(),
			'site_icon_url'         => function_exists( 'get_site_icon_url' ) ? get_site_icon_url( 64 ) : '',
			'rentfetch_version'     => defined( 'RENTFETCH_VERSION' ) ? RENTFETCH_VERSION : 'unknown',
			'rentfetchsync_version' => defined( 'RENTFETCHSYNC_VERSION' ) ? RENTFETCHSYNC_VERSION : 'not-installed',
			'wordpress_version'     => get_bloginfo( 'version' ),
			'generated_at'          => gmdate( 'c', current_time( 'timestamp', true ) ),
		),
		'summary'          => array(
			'properties' => rentfetch_get_monitoring_summary_for_post_type( 'properties' ),
			'floorplans' => rentfetch_get_monitoring_summary_for_post_type( 'floorplans' ),
			'units'      => rentfetch_get_monitoring_summary_for_post_type( 'units' ),
		),
		'endpoint_summary' => rentfetch_get_monitoring_endpoint_summary(),
		'property_rollups' => rentfetch_get_monitoring_property_rollups(),
		'exceptions'       => rentfetch_get_monitoring_exceptions(),
	);
}

/**
 * Get summary counts for a monitored post type.
 *
 * @param string $post_type The post type to summarize.
 * @return array
 */
function rentfetch_get_monitoring_summary_for_post_type( $post_type ) {
	$post_ids = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	$summary = array(
		'total'   => count( $post_ids ),
		'success' => 0,
		'partial' => 0,
		'failed'  => 0,
		'never'   => 0,
		'health'  => rentfetch_get_monitoring_empty_health_summary(),
	);

	foreach ( $post_ids as $post_id ) {
		$state = rentfetch_get_monitoring_sync_state_label( $post_id );
		$health = rentfetch_get_monitoring_record_health( $post_id, $state );

		if ( isset( $summary[ $state ] ) ) {
			++$summary[ $state ];
		}

		if ( isset( $summary['health'][ $health ] ) ) {
			++$summary['health'][ $health ];
		}
	}

	$summary['worst_health'] = rentfetch_get_monitoring_worst_health_state( $summary['health'] );

	return $summary;
}

/**
 * Convert the internal sync state into the monitoring labels.
 *
 * @param int $post_id The post ID.
 * @return string
 */
function rentfetch_get_monitoring_sync_state_label( $post_id ) {
	if ( function_exists( 'rentfetch_get_last_synced_state' ) ) {
		$sync_state = rentfetch_get_last_synced_state( $post_id );
		$state      = isset( $sync_state['state'] ) ? (string) $sync_state['state'] : 'never';

		if ( 'synced' === $state ) {
			return 'success';
		}

		if ( in_array( $state, array( 'success', 'partial', 'failed', 'never' ), true ) ) {
			return $state;
		}
	}

	return 'never';
}

/**
 * Aggregate endpoint success and failure counts across monitored content.
 *
 * @return array
 */
function rentfetch_get_monitoring_endpoint_summary() {
	$summary = array();

	foreach ( array( 'properties', 'floorplans', 'units' ) as $post_type ) {
		$post_ids = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		foreach ( $post_ids as $post_id ) {
			$sync_status = rentfetch_get_monitoring_sync_status_map( $post_id );

			foreach ( $sync_status as $endpoint => $endpoint_state ) {
				if ( ! is_array( $endpoint_state ) ) {
					continue;
				}

				$state = isset( $endpoint_state['state'] ) ? (string) $endpoint_state['state'] : '';

				if ( ! isset( $summary[ $endpoint ] ) ) {
					$summary[ $endpoint ] = array(
						'success' => 0,
						'failed'  => 0,
					);
				}

				if ( 'success' === $state ) {
					++$summary[ $endpoint ]['success'];
				} elseif ( 'failed' === $state ) {
					++$summary[ $endpoint ]['failed'];
				}
			}
		}
	}

	ksort( $summary );

	return $summary;
}

/**
 * Return a compact list of failed and partial records.
 *
 * @return array
 */
function rentfetch_get_monitoring_exceptions() {
	$exceptions   = array();
	$per_type_max = (int) apply_filters( 'rentfetch_monitoring_max_exceptions_per_type', 10 );

	foreach ( array( 'properties', 'floorplans', 'units' ) as $post_type ) {
		$post_ids  = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		$collected = 0;

		foreach ( $post_ids as $post_id ) {
			$state = rentfetch_get_monitoring_sync_state_label( $post_id );

			if ( ! in_array( $state, array( 'partial', 'failed' ), true ) ) {
				continue;
			}

			$exceptions[] = rentfetch_get_monitoring_exception_entry( $post_id, $post_type, $state );
			++$collected;

			if ( $collected >= $per_type_max ) {
				break;
			}
		}
	}

	return $exceptions;
}

/**
 * Build a single exception entry.
 *
 * @param int    $post_id   The post ID.
 * @param string $post_type The post type.
 * @param string $state     The aggregate sync state.
 * @return array
 */
function rentfetch_get_monitoring_exception_entry( $post_id, $post_type, $state ) {
	$sync_status        = rentfetch_get_monitoring_sync_status_map( $post_id );
	$failing_endpoints  = rentfetch_get_monitoring_failing_endpoints( $sync_status );
	$record_type_labels = array(
		'properties' => 'property',
		'floorplans' => 'floorplan',
		'units'      => 'unit',
	);

	return array(
		'record_type'       => isset( $record_type_labels[ $post_type ] ) ? $record_type_labels[ $post_type ] : $post_type,
		'post_id'           => (int) $post_id,
		'source'            => rentfetch_get_monitoring_source_for_post( $post_id, $post_type ),
		'external_id'       => rentfetch_get_monitoring_external_id_for_post( $post_id, $post_type ),
		'title'             => get_the_title( $post_id ),
		'sync_state'        => $state,
		'last_attempt_at'   => (string) get_post_meta( $post_id, 'last_sync_attempt_at', true ),
		'last_success_at'   => (string) get_post_meta( $post_id, 'last_synced_at', true ),
		'health'            => rentfetch_get_monitoring_record_health( $post_id, $state ),
		'failing_endpoints' => array_values( $failing_endpoints ),
	);
}

/**
 * Build property-level rollups for a graphical site dashboard.
 *
 * Each property rollup summarizes the property itself, plus aggregate floorplan
 * and unit health. It intentionally does not inline every child record so the
 * snapshot remains reasonably compact for central polling.
 *
 * @return array
 */
function rentfetch_get_monitoring_property_rollups() {
	$property_ids     = get_posts(
		array(
			'post_type'      => 'properties',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	$floorplan_groups = rentfetch_get_monitoring_child_posts_grouped_by_property( 'floorplans' );
	$unit_groups      = rentfetch_get_monitoring_child_posts_grouped_by_property( 'units' );
	$rollups          = array();

	foreach ( $property_ids as $post_id ) {
		$property_id       = (string) get_post_meta( $post_id, 'property_id', true );
		$sync_state        = rentfetch_get_monitoring_sync_state_label( $post_id );
		$sync_status       = rentfetch_get_monitoring_sync_status_map( $post_id );
		$floorplan_ids     = isset( $floorplan_groups[ $property_id ] ) ? $floorplan_groups[ $property_id ] : array();
		$unit_ids          = isset( $unit_groups[ $property_id ] ) ? $unit_groups[ $property_id ] : array();
		$property_health   = rentfetch_get_monitoring_record_health( $post_id, $sync_state );
		$rollups[]         = array(
			'post_id'           => (int) $post_id,
			'property_id'       => $property_id,
			'title'             => get_the_title( $post_id ),
			'source'            => rentfetch_get_monitoring_source_for_post( $post_id, 'properties' ),
			'sync_state'        => $sync_state,
			'health'            => $property_health,
			'last_attempt_at'   => (string) get_post_meta( $post_id, 'last_sync_attempt_at', true ),
			'last_success_at'   => (string) get_post_meta( $post_id, 'last_synced_at', true ),
			'failing_endpoints' => rentfetch_get_monitoring_property_related_failing_endpoints( $sync_status, $floorplan_ids, $unit_ids ),
			'floorplans'        => rentfetch_get_monitoring_summary_for_post_ids( $floorplan_ids ),
			'units'             => rentfetch_get_monitoring_summary_for_post_ids( $unit_ids ),
		);
	}

	usort(
		$rollups,
		function( $left, $right ) {
			$left_score  = rentfetch_get_monitoring_health_severity( isset( $left['health'] ) ? (string) $left['health'] : 'gray' );
			$right_score = rentfetch_get_monitoring_health_severity( isset( $right['health'] ) ? (string) $right['health'] : 'gray' );

			if ( $left_score !== $right_score ) {
				return $right_score <=> $left_score;
			}

			return strcmp(
				isset( $left['title'] ) ? (string) $left['title'] : '',
				isset( $right['title'] ) ? (string) $right['title'] : ''
			);
		}
	);

	return $rollups;
}

/**
 * Group child posts by their parent property_id meta value.
 *
 * @param string $post_type The child post type.
 * @return array
 */
function rentfetch_get_monitoring_child_posts_grouped_by_property( $post_type ) {
	$post_ids = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	$grouped = array();

	foreach ( $post_ids as $post_id ) {
		$property_id = trim( (string) get_post_meta( $post_id, 'property_id', true ) );

		if ( '' === $property_id ) {
			continue;
		}

		if ( ! isset( $grouped[ $property_id ] ) ) {
			$grouped[ $property_id ] = array();
		}

		$grouped[ $property_id ][] = (int) $post_id;
	}

	return $grouped;
}

/**
 * Summarize a set of monitored post IDs.
 *
 * @param array $post_ids The post IDs to summarize.
 * @return array
 */
function rentfetch_get_monitoring_summary_for_post_ids( $post_ids ) {
	$summary = array(
		'total'   => count( $post_ids ),
		'success' => 0,
		'partial' => 0,
		'failed'  => 0,
		'never'   => 0,
		'health'  => rentfetch_get_monitoring_empty_health_summary(),
	);

	foreach ( $post_ids as $post_id ) {
		$state  = rentfetch_get_monitoring_sync_state_label( $post_id );
		$health = rentfetch_get_monitoring_record_health( $post_id, $state );

		if ( isset( $summary[ $state ] ) ) {
			++$summary[ $state ];
		}

		if ( isset( $summary['health'][ $health ] ) ) {
			++$summary['health'][ $health ];
		}
	}

	$summary['worst_health'] = rentfetch_get_monitoring_worst_health_state( $summary['health'] );

	return $summary;
}

/**
 * Get the concrete health color for a monitored record.
 *
 * Sync states remain useful for diagnostics, but the dashboard wants a single
 * render-friendly health state:
 * - green: successful and fresh
 * - yellow: successful but stale
 * - orange: partial
 * - red: failed or very stale
 * - gray: never synced / no signal
 *
 * @param int         $post_id     The post ID.
 * @param string|null $sync_state  Optional precomputed sync state.
 * @return string
 */
function rentfetch_get_monitoring_record_health( $post_id, $sync_state = null ) {
	$sync_state = null === $sync_state ? rentfetch_get_monitoring_sync_state_label( $post_id ) : (string) $sync_state;

	if ( 'partial' === $sync_state ) {
		return 'orange';
	}

	if ( 'failed' === $sync_state ) {
		return 'red';
	}

	if ( 'never' === $sync_state ) {
		return 'gray';
	}

	$timestamp = rentfetch_get_monitoring_last_synced_timestamp( $post_id );

	if ( $timestamp <= 0 ) {
		return 'gray';
	}

	$hours_diff = ( current_time( 'timestamp' ) - $timestamp ) / HOUR_IN_SECONDS;

	if ( $hours_diff <= 24 ) {
		return 'green';
	}

	if ( $hours_diff <= 72 ) {
		return 'yellow';
	}

	return 'red';
}

/**
 * Get the latest successful sync timestamp for a monitored record.
 *
 * @param int $post_id The post ID.
 * @return int
 */
function rentfetch_get_monitoring_last_synced_timestamp( $post_id ) {
	if ( function_exists( 'rentfetch_get_last_synced_timestamp' ) ) {
		return (int) rentfetch_get_last_synced_timestamp( $post_id );
	}

	$last_synced_at = get_post_meta( $post_id, 'last_synced_at', true );
	$timestamp      = rentfetch_get_monitoring_parsed_timestamp( $last_synced_at );

	if ( $timestamp > 0 ) {
		return $timestamp;
	}

	$last_attempt_at = get_post_meta( $post_id, 'last_sync_attempt_at', true );
	$timestamp       = rentfetch_get_monitoring_parsed_timestamp( $last_attempt_at );

	return $timestamp > 0 ? $timestamp : 0;
}

/**
 * Parse a stored timestamp into a unix timestamp.
 *
 * @param mixed $raw_value The stored timestamp.
 * @return int
 */
function rentfetch_get_monitoring_parsed_timestamp( $raw_value ) {
	if ( empty( $raw_value ) ) {
		return 0;
	}

	if ( is_numeric( $raw_value ) ) {
		return (int) $raw_value;
	}

	$timestamp = strtotime( (string) $raw_value );

	return false === $timestamp ? 0 : $timestamp;
}

/**
 * Return the endpoint keys currently failing on a record.
 *
 * @param array $sync_status The endpoint sync status map.
 * @return array
 */
function rentfetch_get_monitoring_failing_endpoints( $sync_status ) {
	$failing_endpoints = array();

	foreach ( $sync_status as $endpoint => $endpoint_state ) {
		if ( is_array( $endpoint_state ) && isset( $endpoint_state['state'] ) && 'failed' === $endpoint_state['state'] ) {
			$failing_endpoints[] = $endpoint;
		}
	}

	return array_values( $failing_endpoints );
}

/**
 * Return all failing endpoints associated with a property, including child records.
 *
 * @param array $property_sync_status The property's endpoint sync status map.
 * @param array $floorplan_ids        Floorplan post IDs for the property.
 * @param array $unit_ids             Unit post IDs for the property.
 * @return array
 */
function rentfetch_get_monitoring_property_related_failing_endpoints( $property_sync_status, $floorplan_ids, $unit_ids ) {
	$failing_endpoints = rentfetch_get_monitoring_failing_endpoints( $property_sync_status );

	foreach ( array_merge( $floorplan_ids, $unit_ids ) as $child_post_id ) {
		$child_sync_status   = rentfetch_get_monitoring_sync_status_map( (int) $child_post_id );
		$failing_endpoints   = array_merge( $failing_endpoints, rentfetch_get_monitoring_failing_endpoints( $child_sync_status ) );
	}

	$failing_endpoints = array_values( array_unique( $failing_endpoints ) );
	sort( $failing_endpoints );

	return $failing_endpoints;
}

/**
 * Create an empty health summary map.
 *
 * @return array
 */
function rentfetch_get_monitoring_empty_health_summary() {
	return array(
		'green'  => 0,
		'yellow' => 0,
		'orange' => 0,
		'red'    => 0,
		'gray'   => 0,
	);
}

/**
 * Get the worst health state present in a summary.
 *
 * @param array $health_summary The health summary map.
 * @return string
 */
function rentfetch_get_monitoring_worst_health_state( $health_summary ) {
	$ordered_states = array( 'red', 'orange', 'yellow', 'green', 'gray' );

	foreach ( $ordered_states as $state ) {
		if ( isset( $health_summary[ $state ] ) && (int) $health_summary[ $state ] > 0 ) {
			return $state;
		}
	}

	return 'gray';
}

/**
 * Get a sortable severity score for a health state.
 *
 * @param string $health_state The health state.
 * @return int
 */
function rentfetch_get_monitoring_health_severity( $health_state ) {
	$scores = array(
		'red'    => 5,
		'orange' => 4,
		'yellow' => 3,
		'green'  => 2,
		'gray'   => 1,
	);

	return isset( $scores[ $health_state ] ) ? (int) $scores[ $health_state ] : 0;
}

/**
 * Get the source used for a monitored post.
 *
 * @param int    $post_id   The post ID.
 * @param string $post_type The post type.
 * @return string
 */
function rentfetch_get_monitoring_source_for_post( $post_id, $post_type ) {
	$meta_key_map = array(
		'properties' => 'property_source',
		'floorplans' => 'floorplan_source',
		'units'      => 'unit_source',
	);

	$meta_key = isset( $meta_key_map[ $post_type ] ) ? $meta_key_map[ $post_type ] : '';
	$source   = '' !== $meta_key ? trim( (string) get_post_meta( $post_id, $meta_key, true ) ) : '';

	if ( '' === $source && 'units' === $post_type ) {
		$source = trim( (string) get_post_meta( $post_id, 'floorplan_source', true ) );
	}

	return $source;
}

/**
 * Get the primary external identifier for a monitored post.
 *
 * @param int    $post_id   The post ID.
 * @param string $post_type The post type.
 * @return string
 */
function rentfetch_get_monitoring_external_id_for_post( $post_id, $post_type ) {
	$meta_key_map = array(
		'properties' => 'property_id',
		'floorplans' => 'floorplan_id',
		'units'      => 'unit_id',
	);

	$meta_key = isset( $meta_key_map[ $post_type ] ) ? $meta_key_map[ $post_type ] : '';

	return '' !== $meta_key ? (string) get_post_meta( $post_id, $meta_key, true ) : '';
}

/**
 * Get the endpoint-level sync status map for a post.
 *
 * @param int $post_id The post ID.
 * @return array
 */
function rentfetch_get_monitoring_sync_status_map( $post_id ) {
	if ( function_exists( 'rfs_prune_sync_status_to_registry' ) ) {
		$sync_status = rfs_prune_sync_status_to_registry( $post_id );
		return is_array( $sync_status ) ? $sync_status : array();
	}

	if ( function_exists( 'rfs_get_sync_status_map' ) ) {
		$sync_status = rfs_get_sync_status_map( $post_id );
		return is_array( $sync_status ) ? $sync_status : array();
	}

	$sync_status = get_post_meta( $post_id, 'sync_status', true );

	return is_array( $sync_status ) ? $sync_status : array();
}

/**
 * Get the current site host for canonical signatures.
 *
 * @return string
 */
function rentfetch_get_monitoring_site_host() {
	$site_url = get_site_url();
	$host     = wp_parse_url( $site_url, PHP_URL_HOST );

	return is_string( $host ) ? $host : '';
}
