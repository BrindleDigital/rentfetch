<?php
/**
 * Normalize time input to 24-hour format (HH:MM)
 *
 * @param string $time_input The time input (e.g., '9', '9:30', '09:00').
 * @return string Normalized time in HH:MM format, or empty string if invalid.
 */
function rentfetch_normalize_time_input( $time_input ) {
	$time_input = trim( $time_input );
	if ( empty( $time_input ) ) {
		return '';
	}
	
	// If it's already in HH:MM format, validate it
	if ( preg_match( '/^(\d{1,2}):(\d{2})$/', $time_input, $matches ) ) {
		$hour = (int) $matches[1];
		$minute = (int) $matches[2];
		if ( $hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59 ) {
			return sprintf( '%02d:%02d', $hour, $minute );
		}
	}
	
	// If it's just a number (hour), assume :00
	if ( is_numeric( $time_input ) ) {
		$hour = (int) $time_input;
		if ( $hour >= 0 && $hour <= 23 ) {
			return sprintf( '%02d:00', $hour );
		}
	}
	
	// Try to parse with strtotime as fallback
	$timestamp = strtotime( $time_input );
	if ( $timestamp !== false ) {
		return date( 'H:i', $timestamp );
	}
	
	return '';
}
