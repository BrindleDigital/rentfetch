<?php
/**
 * Shared sync-status helpers.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get sync status color class based on API response timestamps.
 *
 * @param int $post_id The post ID to check.
 * @return string Color class (green/yellow/red/gray)
 */
function rentfetch_get_sync_status_class( $post_id ) {
	$sync_state = rentfetch_get_last_synced_state( $post_id );

	if ( 'failed' === $sync_state['state'] ) {
		return 'sync-red';
	}

	if ( 'partial' === $sync_state['state'] ) {
		return 'sync-orange';
	}

	if ( 'synced' !== $sync_state['state'] || $sync_state['timestamp'] <= 0 ) {
		return 'sync-gray'; // No valid timestamps.
	}

	$current_time = time();
	$hours_diff   = ( $current_time - $sync_state['timestamp'] ) / 3600;

	if ( $hours_diff <= 24 ) {
		return 'sync-green'; // Within 24 hours.
	} elseif ( $hours_diff <= 72 ) { // 3 days.
		return 'sync-yellow'; // Within 3 days.
	} else {
		return 'sync-red'; // Older than 3 days.
	}
}

/**
 * Convert a stored sync timestamp value into a Unix timestamp.
 *
 * @param mixed $raw_value Stored timestamp value.
 * @return int
 */
function rentfetch_parse_sync_timestamp( $raw_value ) {
	if ( empty( $raw_value ) ) {
		return 0;
	}

	if ( is_numeric( $raw_value ) ) {
		return (int) $raw_value;
	}

	$timestamp_string = trim( (string) $raw_value );
	$site_timezone    = wp_timezone();
	$local_datetime   = DateTimeImmutable::createFromFormat( '!Y-m-d H:i:s', $timestamp_string, $site_timezone );

	if ( $local_datetime && $local_datetime->format( 'Y-m-d H:i:s' ) === $timestamp_string ) {
		return $local_datetime->getTimestamp();
	}

	$local_date = DateTimeImmutable::createFromFormat( '!Y-m-d', $timestamp_string, $site_timezone );
	if ( $local_date && $local_date->format( 'Y-m-d' ) === $timestamp_string ) {
		return $local_date->getTimestamp();
	}

	$timestamp = strtotime( $timestamp_string );

	return false === $timestamp ? 0 : $timestamp;
}

/**
 * Get the latest known sync timestamp for a record.
 *
 * Prefer the derived aggregate sync fields, then fall back to legacy fields for
 * older records that have not been backfilled yet.
 *
 * @param int $post_id The post ID.
 * @return int
 */
function rentfetch_get_last_synced_timestamp( $post_id ) {
	$sync_state = rentfetch_get_last_synced_state( $post_id );

	return 'synced' === $sync_state['state'] ? $sync_state['timestamp'] : 0;
}

/**
 * Get the normalized sync state for a record.
 *
 * `last_sync_state` is authoritative when present:
 * - never: no endpoint has attempted
 * - success: all attempted endpoints succeeded
 * - failed: all attempted endpoints failed
 * - partial: some attempted endpoints failed and some succeeded
 *
 * Older records fall back to legacy `last_synced_at` and `updated`.
 *
 * @param int $post_id The post ID.
 * @return array{state:string,timestamp:int}
 */
function rentfetch_get_last_synced_state( $post_id ) {
	$aggregate_state = get_post_meta( $post_id, 'last_sync_state', true );

	if ( '' !== (string) $aggregate_state ) {
		if ( in_array( $aggregate_state, array( 'failed', 'partial' ), true ) ) {
			return array(
				'state'     => (string) $aggregate_state,
				'timestamp' => 0,
			);
		}

		if ( 'never' === $aggregate_state ) {
			return array(
				'state'     => 'never',
				'timestamp' => 0,
			);
		}

		$last_synced_at = get_post_meta( $post_id, 'last_synced_at', true );
		$timestamp      = rentfetch_parse_sync_timestamp( $last_synced_at );

		if ( $timestamp > 0 ) {
			return array(
				'state'     => 'synced',
				'timestamp' => $timestamp,
			);
		}

		$last_attempt_at = get_post_meta( $post_id, 'last_sync_attempt_at', true );
		$timestamp       = rentfetch_parse_sync_timestamp( $last_attempt_at );

		if ( $timestamp > 0 ) {
			return array(
				'state'     => 'synced',
				'timestamp' => $timestamp,
			);
		}
	}

	$last_synced_at = get_post_meta( $post_id, 'last_synced_at', true );

	if ( '' !== (string) $last_synced_at ) {
		if ( '0' === trim( (string) $last_synced_at ) ) {
			return array(
				'state'     => 'failed',
				'timestamp' => 0,
			);
		}

		$timestamp = rentfetch_parse_sync_timestamp( $last_synced_at );

		if ( $timestamp > 0 ) {
			return array(
				'state'     => 'synced',
				'timestamp' => $timestamp,
			);
		}

		return array(
			'state'     => 'failed',
			'timestamp' => 0,
		);
	}

	$legacy_updated = get_post_meta( $post_id, 'updated', true );
	$timestamp      = rentfetch_parse_sync_timestamp( $legacy_updated );

	if ( $timestamp > 0 ) {
		return array(
			'state'     => 'synced',
			'timestamp' => $timestamp,
		);
	}

	return array(
		'state'     => 'never',
		'timestamp' => 0,
	);
}

/**
 * Get relative time string (e.g., "Today", "1 day ago", "2 weeks ago").
 *
 * @param string $timestamp The timestamp to convert.
 * @return string The relative time string
 */
function rentfetch_get_relative_time( $timestamp ) {
	$timestamp    = rentfetch_parse_sync_timestamp( $timestamp );
	if ( ! $timestamp ) {
		return 'Unknown';
	}

	$current_time = time();
	$diff_seconds = $current_time - $timestamp;

	if ( $diff_seconds < 0 ) {
		return 'In the future';
	}

	$diff_minutes = floor( $diff_seconds / 60 );
	$diff_hours   = floor( $diff_seconds / 3600 );
	$diff_days    = floor( $diff_seconds / 86400 );
	$diff_weeks   = floor( $diff_seconds / 604800 );
	$diff_months  = floor( $diff_seconds / 2592000 );
	$diff_years   = floor( $diff_seconds / 31536000 );

	if ( $diff_seconds < 60 ) {
		return 'Just now';
	} elseif ( $diff_minutes < 60 ) {
		return $diff_minutes . ' minute' . ( 1 !== $diff_minutes ? 's' : '' ) . ' ago';
	} elseif ( $diff_hours < 24 ) {
		return $diff_hours . ' hour' . ( 1 !== $diff_hours ? 's' : '' ) . ' ago';
	} elseif ( $diff_days < 7 ) {
		if ( 0 === $diff_days ) {
			return 'Today';
		} elseif ( 1 === $diff_days ) {
			return 'Yesterday';
		} else {
			return $diff_days . ' days ago';
		}
	} elseif ( $diff_weeks < 4 ) {
		return $diff_weeks . ' week' . ( 1 !== $diff_weeks ? 's' : '' ) . ' ago';
	} elseif ( $diff_months < 12 ) {
		return $diff_months . ' month' . ( 1 !== $diff_months ? 's' : '' ) . ' ago';
	} else {
		return $diff_years . ' year' . ( 1 !== $diff_years ? 's' : '' ) . ' ago';
	}
}

/**
 * Format a sync timestamp with seconds and the site's timezone.
 *
 * @param mixed $timestamp Stored timestamp value.
 * @return string
 */
function rentfetch_format_sync_timestamp( $timestamp ) {
	$timestamp = rentfetch_parse_sync_timestamp( $timestamp );

	return $timestamp ? wp_date( 'M j, Y g:i:s A T', $timestamp, wp_timezone() ) : '';
}

/**
 * Get tooltip content showing API names and dates.
 *
 * @param int $post_id The post ID.
 * @return string The tooltip HTML content.
 */
function rentfetch_get_sync_tooltip( $post_id ) {
	global $wpdb;

	// Diagnostics must see a sync that completed after this request began.
	$api_response = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Deliberately bypass object cache for diagnostic accuracy.
		$wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC LIMIT 1",
			absint( $post_id ),
			'api_response'
		)
	);
	$api_response = maybe_unserialize( $api_response );

	if ( empty( $api_response ) || ! is_array( $api_response ) ) {
		return '<div class="rf-sync-tooltip"><div class="rf-sync-tooltip-heading">API responses</div><div class="rf-sync-tooltip-empty">No API data available</div></div>';
	}

	$tooltip_rows = array();

	foreach ( $api_response as $api_name => $response_data ) {
		$display_name = ucwords( str_replace( array( '_', '-' ), ' ', (string) $api_name ) );
		$display_name = str_ireplace( 'Api', 'API', $display_name );
		$status_class = 'is-neutral';
		$status_label = 'No timestamp';
		$time_label   = 'No timestamp recorded';
		$details      = array();

		if ( is_array( $response_data ) ) {
			$timestamp       = '';
			$possible_fields = array( 'updated', 'timestamp', 'last_sync', 'sync_date', 'date' );

			foreach ( $possible_fields as $field ) {
				if ( isset( $response_data[ $field ] ) && is_scalar( $response_data[ $field ] ) && ! empty( $response_data[ $field ] ) ) {
					$timestamp = $response_data[ $field ];
					break;
				}
			}

			$status_code  = isset( $response_data['status_code'] ) && is_numeric( $response_data['status_code'] ) ? (int) $response_data['status_code'] : 0;
			$status_value  = isset( $response_data['status'] ) && is_scalar( $response_data['status'] ) ? strtolower( (string) $response_data['status'] ) : '';
			$success_value = isset( $response_data['success'] ) && is_scalar( $response_data['success'] ) ? strtolower( (string) $response_data['success'] ) : '';
			$error_value   = isset( $response_data['error'] ) && is_scalar( $response_data['error'] ) ? trim( (string) $response_data['error'] ) : '';
			$error_code    = isset( $response_data['error_code'] ) && is_scalar( $response_data['error_code'] ) ? trim( (string) $response_data['error_code'] ) : '';
			$is_error      = $status_code >= 400 || in_array( $status_value, array( 'error', 'failed', 'failure' ), true ) || in_array( $success_value, array( '0', 'false', 'no', 'error', 'failed' ), true ) || ( '' !== $error_value && '0' !== $error_value ) || ( '' !== $error_code && '0' !== $error_code );

			if ( $is_error ) {
				$status_class = 'is-error';
				$status_label = 'Error';
			} elseif ( $timestamp && rentfetch_parse_sync_timestamp( $timestamp ) > 0 ) {
				$status_class = 'is-success';
				$status_label = 'Updated';
			} elseif ( $status_code > 0 && $status_code < 400 ) {
				$status_class = 'is-success';
				$status_label = 'Received';
			}

			if ( $timestamp && rentfetch_parse_sync_timestamp( $timestamp ) > 0 ) {
				$exact_time    = rentfetch_format_sync_timestamp( $timestamp );
				$relative_time = rentfetch_get_relative_time( $timestamp );
				$time_label    = $exact_time ? $exact_time . ' · ' . $relative_time : $relative_time;
			}

			if ( $status_code > 0 ) {
				$details[] = 'HTTP ' . $status_code;
			}

			foreach ( array( 'error' => 'Error', 'error_code' => 'Error code', 'message' => 'Message', 'reason' => 'Reason' ) as $field => $label ) {
				if ( ! isset( $response_data[ $field ] ) || is_array( $response_data[ $field ] ) || is_object( $response_data[ $field ] ) ) {
					continue;
				}

				$value = trim( wp_strip_all_tags( (string) $response_data[ $field ] ) );
				if ( '' === $value || ( 'error_code' === $field && '0' === $value ) ) {
					continue;
				}

				if ( strlen( $value ) > 120 ) {
					$value = substr( $value, 0, 117 ) . '…';
				}

				$details[] = $label . ': ' . $value;
			}
		} else {
			$value = trim( wp_strip_all_tags( (string) $response_data ) );
			if ( '' !== $value ) {
				$details[] = $value;
			}
		}

		$detail_markup = '';
		if ( ! empty( $details ) ) {
			$detail_markup = '<div class="rf-sync-tooltip-detail">' . esc_html( implode( ' · ', $details ) ) . '</div>';
		}

		$tooltip_rows[] = '<div class="rf-sync-tooltip-row ' . esc_attr( $status_class ) . '">' .
			'<span class="rf-sync-tooltip-status" aria-hidden="true"></span>' .
			'<div class="rf-sync-tooltip-record">' .
			'<div class="rf-sync-tooltip-api"><span class="rf-sync-tooltip-api-name">' . esc_html( $display_name ) . '</span><span class="rf-sync-tooltip-api-state">' . esc_html( $status_label ) . '</span></div>' .
			'<div class="rf-sync-tooltip-time">' . esc_html( $time_label ) . '</div>' .
			$detail_markup .
			'</div></div>';
	}

	if ( empty( $tooltip_rows ) ) {
		return '<div class="rf-sync-tooltip"><div class="rf-sync-tooltip-heading">API responses</div><div class="rf-sync-tooltip-empty">No API data found</div></div>';
	}

	return '<div class="rf-sync-tooltip"><div class="rf-sync-tooltip-heading">API responses</div><div class="rf-sync-tooltip-list">' . implode( '', $tooltip_rows ) . '</div></div>';
}

/**
 * Return fresh sync tooltip data for an editor hover.
 *
 * @return void
 */
function rentfetch_ajax_get_sync_tooltip() {
	check_ajax_referer( 'rentfetch_sync_tooltip', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$post    = get_post( $post_id );
	$allowed = array( 'properties', 'floorplans', 'units' );

	if (
		! $post ||
		! in_array( $post->post_type, $allowed, true ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		wp_send_json_error( array( 'message' => 'Sync data is unavailable.' ), 403 );
	}

	nocache_headers();
	$tooltip = function_exists( 'rentfetch_get_hierarchy_tooltip_for_post' )
		? rentfetch_get_hierarchy_tooltip_for_post( $post_id )
		: rentfetch_get_sync_tooltip( $post_id );
	wp_send_json_success( array( 'tooltip' => $tooltip ) );
}
add_action( 'wp_ajax_rentfetch_get_sync_tooltip', 'rentfetch_ajax_get_sync_tooltip' );
