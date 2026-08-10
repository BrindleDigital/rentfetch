<?php
/**
 * Floor plan virtual tour field.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render the floor plan virtual tour field.
 *
 * @param WP_Post $post Floor plan post.
 * @return void
 */
function rentfetch_floorplans_tour_callback( $post ) {
	$tour = get_post_meta( $post->ID, 'tour', true );
	?>
	<div class="rf-metabox rf-metabox-floorplans">
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
