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
				<label for="tour">Tour Link (Youtube or Matterport)</label>
			</div>
			<div class="column">
				<input type="text" id="tour" name="tour" value="<?php echo esc_attr( $tour ); ?>">
				<p class="description">Example: https://my.matterport.com/show/?m=sc3ykepsN4s</p>
				<div id="tour-preview"></div>
			</div>
		</div>
	</div>
	<?php
}
