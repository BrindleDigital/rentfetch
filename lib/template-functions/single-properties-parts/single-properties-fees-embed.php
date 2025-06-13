<?php
/**
 * The Fees Embed section of the single property page
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the fees embed section
 *
 * @return void.
 */
function rentfetch_single_properties_parts_fees_embed() {

	$maybe_do_fees_embed = apply_filters( 'rentfetch_maybe_do_property_part_fees_embed', true );
	if ( true !== $maybe_do_fees_embed ) {
		return;
	}

	echo '<div id="fees-embed" class="single-properties-section">';
		echo '<div class="wrap">';

			$section_title = apply_filters( 'rentfetch_fees_embed_section_title', 'Property Fees' );
			printf( '<h2>%s</h2>', esc_html( $section_title ) );

			rentfetch_property_fees_embed();

		echo '</div>'; // .wrap.
		echo '</div>'; // #fees-embed.
}

/**
 * Determine if the fees embed section should be displayed
 *
 * @return bool.
 */
function rentfetch_maybe_property_part_fees_embed() {

	// bail if this section is not enabled.
	$property_components = get_option( 'rentfetch_options_single_property_components' );
	if ( ! is_array( $property_components ) || ! in_array( 'fees_embed_display', $property_components, true ) ) {
		return false;
	}

	$fees_embed_content = rentfetch_get_property_fees_embed();
	if ( empty( trim( $fees_embed_content ) ) ) {
		return false;
	}

	return true;
}
add_filter( 'rentfetch_maybe_do_property_part_fees_embed', 'rentfetch_maybe_property_part_fees_embed' );

/**
 * Output the fees embed section in the subnav
 *
 * @return void.
 */
function rentfetch_single_properties_parts_subnav_fees_embed() {

	$maybe_do_fees_embed = apply_filters( 'rentfetch_maybe_do_property_part_fees_embed', true );

	if ( true === $maybe_do_fees_embed ) {
		$label = apply_filters( 'rentfetch_fees_embed_display_subnav_label', 'Fees' );
		printf( '<li><a href="#fees-embed">%s</a></li>', esc_html( $label ) );
	}
}
