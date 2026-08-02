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

	$timestamp = strtotime( (string) $raw_value );

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
	$timestamp    = strtotime( $timestamp );
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
 * Get tooltip content showing API names and dates.
 *
 * @param int $post_id The post ID.
 * @return string The tooltip HTML content.
 */
function rentfetch_get_sync_tooltip( $post_id ) {
	$api_response = get_post_meta( $post_id, 'api_response', true );

	if ( empty( $api_response ) || ! is_array( $api_response ) ) {
		return 'No API data available';
	}

	$tooltip_lines = array();

	foreach ( $api_response as $api_name => $response_data ) {
		if ( is_array( $response_data ) ) {
			// Look for timestamp fields in various possible formats.
			$timestamp       = null;
			$possible_fields = array( 'updated', 'timestamp', 'last_sync', 'sync_date', 'date' );

			foreach ( $possible_fields as $field ) {
				if ( isset( $response_data[ $field ] ) && ! empty( $response_data[ $field ] ) ) {
					$timestamp = $response_data[ $field ];
					break;
				}
			}

			if ( $timestamp ) {
				$relative_time   = rentfetch_get_relative_time( $timestamp );
				$tooltip_lines[] = esc_html( $api_name ) . ': ' . $relative_time;
			} else {
				// If no timestamp found, just show the API name.
				$tooltip_lines[] = esc_html( $api_name ) . ': No timestamp';
			}
		} else {
			// If response_data is not an array, just show the API name.
			$tooltip_lines[] = esc_html( $api_name ) . ': ' . esc_html( $response_data );
		}
	}

	if ( empty( $tooltip_lines ) ) {
		return 'No API data found';
	}

	$result = implode( '<br>', $tooltip_lines );
	return $result;
}
