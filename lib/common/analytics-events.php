<?php
/**
 * Analytics event helpers for Rent Fetch.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Build data attributes for analytics context (no event name).
 *
 * @param array $context Context values (property_name, property_city, floorplan_name).
 * @return string Data attributes.
 */
function rentfetch_get_tracking_context_attributes( $context = array() ) {
	$attributes = array();

	if ( ! empty( $context['property_id'] ) ) {
		$attributes['data-rentfetch-property-id'] = $context['property_id'];
	}

	if ( ! empty( $context['property_name'] ) ) {
		$attributes['data-rentfetch-property-name'] = $context['property_name'];
	}

	if ( ! empty( $context['property_city'] ) ) {
		$attributes['data-rentfetch-property-city'] = $context['property_city'];
	}

	if ( ! empty( $context['floorplan_id'] ) ) {
		$attributes['data-rentfetch-floorplan-id'] = $context['floorplan_id'];
	}

	if ( ! empty( $context['floorplan_name'] ) ) {
		$attributes['data-rentfetch-floorplan-name'] = $context['floorplan_name'];
	}

	$output = '';
	foreach ( $attributes as $key => $value ) {
		$output .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
	}

	return $output;
}

/**
 * Build data attributes for analytics events.
 *
 * @param string $event_name Event name (prefixed with rentfetch_).
 * @param array  $context Context values (property_name, property_city, floorplan_name).
 * @return string Data attributes.
 */
function rentfetch_get_tracking_data_attributes( $event_name, $context = array() ) {
	if ( empty( $event_name ) ) {
		return '';
	}

	if ( 0 !== strpos( $event_name, 'rentfetch_' ) ) {
		$event_name = 'rentfetch_' . ltrim( $event_name, '_' );
	}

	$attributes = array(
		'data-rentfetch-event' => $event_name,
	);

	if ( ! empty( $context['property_id'] ) ) {
		$attributes['data-rentfetch-property-id'] = $context['property_id'];
	}

	if ( ! empty( $context['property_name'] ) ) {
		$attributes['data-rentfetch-property-name'] = $context['property_name'];
	}

	if ( ! empty( $context['property_city'] ) ) {
		$attributes['data-rentfetch-property-city'] = $context['property_city'];
	}

	if ( ! empty( $context['floorplan_id'] ) ) {
		$attributes['data-rentfetch-floorplan-id'] = $context['floorplan_id'];
	}

	if ( ! empty( $context['floorplan_name'] ) ) {
		$attributes['data-rentfetch-floorplan-name'] = $context['floorplan_name'];
	}

	$output = '';
	foreach ( $attributes as $key => $value ) {
		$output .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
	}

	return $output;
}

/**
 * Build tracking context for a property.
 *
 * @param string|null $property_id Optional property_id meta value.
 * @param int|null    $post_id Optional property post ID.
 * @return array
 */
function rentfetch_get_property_tracking_context( $property_id = null, $post_id = null ) {
	if ( $property_id ) {
		$post_id = rentfetch_get_post_id_from_property_id( $property_id );
	}

	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		$post_id = get_queried_object_id();
	}

	if ( ! $post_id ) {
		return array();
	}

	$property_name = get_the_title( $post_id );
	$property_city = get_post_meta( $post_id, 'city', true );
	$property_id_meta = get_post_meta( $post_id, 'property_id', true );

	return array(
		'property_id'   => $property_id_meta ? sanitize_text_field( $property_id_meta ) : null,
		'property_name' => $property_name ? sanitize_text_field( $property_name ) : null,
		'property_city' => $property_city ? sanitize_text_field( $property_city ) : null,
	);
}

/**
 * Build tracking context for a floorplan.
 *
 * @param int|null $floorplan_id Optional floorplan post ID.
 * @return array
 */
function rentfetch_get_floorplan_tracking_context( $floorplan_id = null ) {
	if ( ! $floorplan_id ) {
		$floorplan_id = get_the_ID();
	}

	if ( ! $floorplan_id ) {
		$floorplan_id = get_queried_object_id();
	}

	if ( ! $floorplan_id ) {
		return array();
	}

	$floorplan_name   = get_the_title( $floorplan_id );
	$floorplan_id_meta = get_post_meta( $floorplan_id, 'floorplan_id', true );
	$property_id      = get_post_meta( $floorplan_id, 'property_id', true );
	$property_post    = $property_id ? rentfetch_get_post_id_from_property_id( $property_id ) : null;

	$property_name = $property_post ? get_the_title( $property_post ) : null;
	$property_city = $property_post ? get_post_meta( $property_post, 'city', true ) : null;

	return array(
		'property_id'   => $property_id ? sanitize_text_field( $property_id ) : null,
		'property_name' => $property_name ? sanitize_text_field( $property_name ) : null,
		'property_city' => $property_city ? sanitize_text_field( $property_city ) : null,
		'floorplan_id'  => $floorplan_id_meta ? sanitize_text_field( $floorplan_id_meta ) : null,
		'floorplan_name' => $floorplan_name ? sanitize_text_field( $floorplan_name ) : null,
	);
}

/**
 * Enqueue analytics events script on single property/floorplan pages.
 *
 * @return void
 */
function rentfetch_enqueue_analytics_events_script() {
	$enabled = get_option( 'rentfetch_options_enable_analytics', '1' );

	if ( '1' !== $enabled ) {
		return;
	}

	if ( is_singular( array( 'properties', 'floorplans' ) ) ) {
		$debug_option = get_option( 'rentfetch_options_enable_analytics_debug', '0' );
		$debug_enabled = in_array( $debug_option, array( '1', 1, true, 'true', 'yes' ), true );
		$debug_override = isset( $_GET['rentfetch_debug'] ) ? sanitize_text_field( wp_unslash( $_GET['rentfetch_debug'] ) ) : '';

		if ( $debug_override !== '' ) {
			$debug_enabled = in_array( $debug_override, array( '1', 'true', 'yes', 'on' ), true );
		}

		wp_enqueue_script( 'rentfetch-analytics-events' );
		$debug_allowed = is_user_logged_in() && current_user_can( 'manage_options' );

		wp_localize_script(
			'rentfetch-analytics-events',
			'rentfetchAnalyticsSettings',
			array(
				'enabled' => ( '1' === $enabled ),
				'debug'   => $debug_enabled,
				'debugAllowed' => $debug_allowed,
				'debugOptionRaw' => $debug_option,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'rentfetch_enqueue_analytics_events_script' );
