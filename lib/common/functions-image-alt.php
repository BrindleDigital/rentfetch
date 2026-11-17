<?php
/**
 * Frontend image descriptions.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Use an image description, then its floorplan/property/unit name.
 *
 * @param string $alt Image-specific description.
 * @param string $fallback Name used when the description is blank.
 * @return string Unescaped plain text for an HTML attribute.
 */
function rentfetch_get_image_alt( $alt, $fallback ) {
	foreach ( array( $alt, $fallback ) as $value ) {
		$value = trim( wp_strip_all_tags( html_entity_decode( (string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
		if ( '' !== $value ) {
			return $value;
		}
	}

	return 'Apartment image';
}

/**
 * Match RentCafe descriptions to their original URLs before filtering/deduping.
 *
 * @param string|array $urls Original API image URLs.
 * @param string       $alt API description or comma-separated descriptions.
 * @return array Descriptions keyed by URL.
 */
function rentfetch_get_yardi_image_alts( $urls, $alt ) {
	$urls = is_array( $urls ) ? $urls : preg_split( '/\s*,\s*(?=https?:\/\/)/', (string) $urls, -1, PREG_SPLIT_NO_EMPTY );
	$alts = 1 === count( $urls ) ? array( (string) $alt ) : explode( ',', (string) $alt );

	// Commas inside descriptions make a mismatched list unsafe to assign to photos.
	if ( count( $urls ) !== count( $alts ) ) {
		return array();
	}

	$descriptions = array();
	foreach ( $urls as $index => $url ) {
		$url = esc_url_raw( trim( $url ) );
		if ( $url && empty( $descriptions[ $url ] ) ) {
			$descriptions[ $url ] = trim( $alts[ $index ] );
		}
	}

	return $descriptions;
}

/**
 * Read one saved image API response without making another API request.
 *
 * @param int    $post_id Post ID.
 * @param string $section Saved response section.
 * @return array
 */
function rentfetch_get_image_api_response( $post_id, $section ) {
	$response = get_post_meta( $post_id, 'api_response', true );
	$data     = is_array( $response ) ? ( $response[ $section ]['api_response'] ?? array() ) : array();
	$data     = is_string( $data ) ? json_decode( $data, true ) : $data;
	return is_array( $data ) ? $data : array();
}
