<?php
/**
 * Run with: php -d zend.assertions=1 -d assert.exception=1 tests/test-property-images.php
 *
 * @package rentfetch
 */

define( 'ABSPATH', __DIR__ );

/**
 * Minimal WordPress URL parser stub.
 *
 * @param string $url URL to parse.
 * @return array|false
 */
function wp_parse_url( $url ) {
	return parse_url( $url ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
}

/**
 * Minimal WordPress query-argument stub.
 *
 * @param array  $args Query arguments.
 * @param string $url Base URL.
 * @return string
 */
function add_query_arg( $args, $url ) {
	$separator = false === strpos( $url, '?' ) ? '?' : '&';

	return $url . $separator . http_build_query( $args );
}

require_once dirname( __DIR__ ) . '/lib/common/functions-property-images.php';

$rentcafe_url = 'https://cdn.rentcafe.com/dmslivecafe/2/85053/photo.jpg';
assert( 'https://cdn.rentcafe.com/dmslivecafe/2/85053/photo.jpg?width=640&height=-1&quality=80' === rentfetch_get_resized_rentcafe_image_url( $rentcafe_url, 640 ) );
assert( 'https://example.com/photo.jpg' === rentfetch_get_resized_rentcafe_image_url( 'https://example.com/photo.jpg', 640 ) );
assert( 'https://rentcafe.com.example.org/dmslivecafe/photo.jpg' === rentfetch_get_resized_rentcafe_image_url( 'https://rentcafe.com.example.org/dmslivecafe/photo.jpg', 640 ) );
assert( 'https://evilrentcafe.com/dmslivecafe/photo.jpg' === rentfetch_get_resized_rentcafe_image_url( 'https://evilrentcafe.com/dmslivecafe/photo.jpg', 640 ) );

echo "Property image tests passed.\n";
