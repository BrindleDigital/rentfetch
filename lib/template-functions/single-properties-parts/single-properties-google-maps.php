<?php
/**
 * The Google Maps section of the single property page
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the Google Maps section
 *
 * @return void.
 */
function rentfetch_single_properties_parts_map() {

	$maybe_do_map = apply_filters( 'rentfetch_maybe_do_property_part_maps', true );
	if ( true !== $maybe_do_map ) {
		return;
	}

	$id = esc_attr( get_the_ID() );

	$latitude  = floatval( get_post_meta( $id, 'latitude', true ) );
	$longitude = floatval( get_post_meta( $id, 'longitude', true ) );

	// * bail if there's not a lat or longitude
	if ( empty( $latitude ) || empty( $longitude ) ) {
		return;
	}

	echo '<div id="googlemaps" class="single-properties-section no-padding full-width">';
		echo '<div class="wrap">';

			$title    = esc_attr( rentfetch_get_property_title() );
			$phone    = esc_attr( rentfetch_get_property_phone() );
			$location = esc_attr( rentfetch_get_property_location() );

			$content = sprintf( '<div class="map-marker"><p class="title">%s</p><p class="location">%s</p></div>', $title, $location );
			$content = esc_attr( apply_filters( 'rentfetch_property_single_map_marker_content', $content ) );

			// the map itself.
			$key = apply_filters( 'rentfetch_get_google_maps_api_key', null );
			wp_enqueue_script( 'rentfetch-google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $key . '&loading=async&callback=rentfetchGoogleMapsLoaded', array(), RENTFETCH_VERSION, true );
			wp_script_add_data( 'rentfetch-google-maps', 'strategy', 'async' );
			wp_add_inline_script(
				'rentfetch-google-maps',
				'window.rentfetchGoogleMapsLoaded = window.rentfetchGoogleMapsLoaded || function () { window.rentfetchGoogleMapsReadyFired = true; if (window.jQuery) { jQuery(document).trigger("rentfetchGoogleMapsReady"); } };',
				'before'
			);

			// Localize the google maps script, then enqueue that.
			$maps_options = array(
				'json_style' => json_decode( get_option( 'rentfetch_options_google_maps_styles' ) ),
				'marker_url' => get_option( 'rentfetch_options_google_map_marker' ),
				'latitude'   => $latitude,
				'longitude'  => $longitude,
				'content'    => $content,
				// 'nonce'      => wp_create_nonce('rentfetch_frontend_nonce' ),
			);
			wp_localize_script( 'rentfetch-single-property-map', 'options', $maps_options );
			wp_enqueue_script( 'rentfetch-single-property-map' );

			echo '<div id="single-property-map"></div>';

			echo '</div>'; // .wrap
			echo '</div>'; // #googlemaps
}

/**
 * Determine if the Google Maps section should be displayed
 *
 * @return bool.
 */
function rentfetch_maybe_property_part_maps() {

	// bail if this section is not enabled.
	$property_components = get_option( 'rentfetch_options_single_property_components' );

	if ( ! is_array( $property_components ) || ! in_array( 'property_map', $property_components, true ) ) {
		return false;
	}

	return true;
}
add_filter( 'rentfetch_maybe_do_property_part_maps', 'rentfetch_maybe_property_part_maps' );

/**
 * Output the Google Maps section in the subnav
 *
 * @return void.
 */
function rentfetch_single_properties_parts_subnav_maps() {
	$maybe_do_map = apply_filters( 'rentfetch_maybe_do_property_part_maps', true );
	if ( true === $maybe_do_map ) {
		$label = apply_filters( 'rentfetch_property_map_subnav_label', 'Map' );
		printf( '<li><a href="#googlemaps">%s</a></li>', esc_attr( $label ) );
	}
}
