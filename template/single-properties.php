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
$tracking_context_attrs = rentfetch_get_tracking_context_attributes( $tracking_context );
printf( '<div class="single-properties-wrap"%s>', $tracking_context_attrs );

	do_action( 'rentfetch_do_single_properties_parts' );

echo '</div>'; // .single-properties-wrap.

get_footer();
