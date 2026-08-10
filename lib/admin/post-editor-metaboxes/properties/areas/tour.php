<?php
/**
 * Property editor virtual tour fields.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render the property virtual tour field.
 *
 * @param WP_Post $post Current property.
 * @return void
 */
function rentfetch_properties_tour_callback( $post ) {
	$tour = get_post_meta( $post->ID, 'tour', true );

	wp_enqueue_script( 'rentfetch-metabox-properties-tour' );
	?>
	<div class="rf-metabox rf-metabox-properties">
		<div class="field">
			<div class="column">
				<label for="tour">Video or virtual tour URL or iframe code</label>
			</div>
			<div class="column">
				<input type="text" id="tour" name="tour" value="<?php echo esc_attr( $tour ); ?>">
				<p class="description">Paste a direct URL or iframe embed code.</p>
				<div id="tour-preview"></div>
			</div>
		</div>
	</div>
	<?php
}
