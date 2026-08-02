<?php
/**
 * Single properties template
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

// * Markup.
$tracking_context = rentfetch_get_property_tracking_context( null, get_queried_object_id() );
echo '<div class="single-properties-wrap"';
if ( ! empty( $tracking_context['property_id'] ) ) {
	printf( ' data-rentfetch-property-id="%s"', esc_attr( $tracking_context['property_id'] ) );
}
if ( ! empty( $tracking_context['property_name'] ) ) {
	printf( ' data-rentfetch-property-name="%s"', esc_attr( $tracking_context['property_name'] ) );
}
if ( ! empty( $tracking_context['property_city'] ) ) {
	printf( ' data-rentfetch-property-city="%s"', esc_attr( $tracking_context['property_city'] ) );
}
echo '>';

	do_action( 'rentfetch_do_single_properties_parts' );

echo '</div>'; // .single-properties-wrap.

get_footer();
