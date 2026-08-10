<?php
/**
 * Property video and virtual-tour section.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get every unique manual or synced tour for the current property.
 *
 * @return array[] Parsed tours.
 */
function rentfetch_get_single_property_tours() {
	return rentfetch_get_property_tours( get_the_ID() );
}

/**
 * Get safe, displayable tour embeds for the current property.
 *
 * @return string[]
 */
function rentfetch_get_single_property_tour_embeds() {
	$tours  = rentfetch_get_single_property_tours();
	$embeds = array_filter( array_map( 'rentfetch_get_tour_embed_html', array_column( $tours, 'url' ) ) );

	$embeds = apply_filters( 'rentfetch_single_property_tour_embeds', array_values( $embeds ), $tours );

	return is_array( $embeds ) ? $embeds : array();
}

/**
 * Output the property video and virtual-tour section.
 *
 * @return void
 */
function rentfetch_single_properties_parts_tours() {
	$embeds = rentfetch_get_single_property_tour_embeds();
	if ( ! $embeds ) {
		return;
	}
	$tour_count_class = count( $embeds ) >= 5 ? 'tour-count-5-plus' : 'tour-count-' . count( $embeds );
	?>
	<div id="tours" class="single-properties-section property-tours-section">
		<div class="wrap">
			<?php echo wp_kses_post( apply_filters( 'rentfetch_single_property_tours_headline', '<h2>Take a look around</h2>' ) ); ?>
			<div class="property-tours-grid <?php echo esc_attr( $tour_count_class ); ?>">
				<?php foreach ( $embeds as $embed ) : ?>
					<div class="property-tour-embed">
						<?php echo wp_kses( $embed, rentfetch_get_allowed_embed_html() ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Output the tours link in the property subnavigation.
 *
 * @return void
 */
function rentfetch_single_properties_parts_subnav_tours() {
	if ( rentfetch_get_single_property_tour_embeds() ) {
		$label = apply_filters( 'rentfetch_property_tours_subnav_label', 'Tours' );
		printf( '<li><a href="#tours">%s</a></li>', esc_html( $label ) );
	}
}
