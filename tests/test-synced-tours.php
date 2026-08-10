<?php
/**
 * Run with: php -d zend.assertions=1 -d assert.exception=1 tests/test-synced-tours.php
 *
 * @package rentfetch
 */

define( 'ABSPATH', __DIR__ );

/**
 * Minimal WordPress URL sanitizer stub for this standalone check.
 *
 * @param string $url URL to validate.
 * @return string
 */
function esc_url_raw( $url ) {
	return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
}

/**
 * Minimal WordPress URL escaping stub for this standalone check.
 *
 * @param string $url URL to escape.
 * @return string
 */
function esc_url( $url ) {
	return esc_url_raw( $url );
}

/**
 * Minimal WordPress URL parser stub for this standalone check.
 *
 * @param string $url URL to parse.
 * @return array|false
 */
function wp_parse_url( $url ) {
	return parse_url( $url ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
}

/**
 * Minimal current-post stub.
 *
 * @return int
 */
function get_the_ID() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return 1;
}

/**
 * Minimal post-meta stub.
 *
 * @param int    $post_id Post ID.
 * @param string $key Meta key.
 * @return mixed
 */
function get_post_meta( $post_id, $key ) {
	$meta = array(
		1 => array(
			'tour'         => 'https://youtu.be/manual',
			'synced_tours' => array(
				array(
					'url'  => 'https://youtu.be/video',
					'type' => 'video',
				),
				array(
					'url'  => 'https://my.matterport.com/show/?m=virtual',
					'type' => 'virtual_tour',
				),
			),
		),
	);

	return $meta[ $post_id ][ $key ] ?? '';
}

/**
 * Return the unmodified filtered value.
 *
 * @param string $hook  Filter name.
 * @param mixed  $value Filtered value.
 */
function apply_filters( $hook, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
	return $value;
}

require_once dirname( __DIR__ ) . '/lib/common/functions-tours.php';
require_once dirname( __DIR__ ) . '/lib/admin/post-editor-metaboxes/shared/videos-tours.php';

assert(
	'Yardi floor plan video embed code' === rentfetch_get_synced_tour_source_label(
		array(
			'source'       => 'yardi',
			'source_field' => 'fpVideoEmbedCode',
		)
	)
);
assert( 'API synced video or tour' === rentfetch_get_synced_tour_source_label( array() ) );
assert( 'https://my.matterport.com/show/?m=RbRMQVyf2sL' === rentfetch_parse_tour_value( 'https://my.matterport.com/show/?m=RbRMQVyf2sL' )['embed_url'] );
assert( 'https://www.youtube.com/embed/abc123' === rentfetch_parse_tour_value( 'https://www.youtube.com/watch?v=abc123' )['embed_url'] );
assert( 'https://player.vimeo.com/video/123456' === rentfetch_parse_tour_value( 'https://vimeo.com/123456' )['embed_url'] );
assert( 'https://www.youtube.com/embed/abc123' === rentfetch_parse_tour_value( '<iframe src="https://www.youtube.com/embed/abc123"></iframe>' )['embed_url'] );
assert( 'youtube' === rentfetch_parse_tour_value( 'https://www.youtube-nocookie.com/embed/abc123' )['type'] );
assert( 'virtual_tour' === rentfetch_parse_tour_value( 'https://www.zillow.com/view-3d-home/017a85bd-6596-47d7-95c2-514bb27cacdc' )['type'] );
assert( 'link' === rentfetch_parse_tour_value( 'https://youtube.com.example.org/watch?v=abc123' )['type'] );
assert( 'link' === rentfetch_parse_tour_value( 'https://zillow.com.example.org/view-3d-home/example' )['type'] );
assert( 'iframe' === rentfetch_parse_tour_value( '<iframe src="https://custom-tour.example/embed/123"></iframe>' )['type'] );
assert( false !== strpos( rentfetch_get_tour_embed_html( '<iframe src="https://custom-tour.example/embed/123"></iframe>' ), 'src="https://custom-tour.example/embed/123"' ) );
assert( false !== strpos( rentfetch_get_tour_embed_html( 'https://youtu.be/abc123' ), 'src="https://www.youtube.com/embed/abc123"' ) );
assert(
	1 === count(
		rentfetch_parse_tour_values(
			array(
				'https://youtu.be/abc123',
				'<iframe src="https://www.youtube.com/embed/abc123"></iframe>',
			)
		)
	)
);
assert(
	array(
		'https://www.youtube.com/embed/manual',
		'https://my.matterport.com/show/?m=virtual',
		'https://www.youtube.com/embed/video',
	) === array_column( rentfetch_get_property_tours(), 'embed_url' )
);
assert( array_column( rentfetch_get_property_tours(), 'embed_url' ) === array_column( rentfetch_get_floorplan_tours(), 'embed_url' ) );
assert( 'RbRMQVyf2sL' === rentfetch_get_synced_tour_identifier( 'https://my.matterport.com/show/?m=RbRMQVyf2sL' ) );
assert( 'yQUgBW5iKmU' === rentfetch_get_synced_tour_identifier( 'https://my.matterport.com/models/yQUgBW5iKmU?section=media' ) );
assert( 'abc123' === rentfetch_get_synced_tour_identifier( 'https://youtu.be/abc123' ) );
assert( 'abc123' === rentfetch_get_synced_tour_identifier( 'https://www.youtube-nocookie.com/embed/abc123' ) );
assert( '123456' === rentfetch_get_synced_tour_identifier( 'https://vimeo.com/123456' ) );
assert( 'drive-file-id' === rentfetch_get_synced_tour_identifier( 'https://drive.google.com/file/d/drive-file-id/view' ) );

echo "Synced tour tests passed.\n";
