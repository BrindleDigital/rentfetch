<?php
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
