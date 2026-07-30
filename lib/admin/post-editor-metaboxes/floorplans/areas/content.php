<?php
/**
 * Floor plan content fields.
 *
 * @package rentfetch
 */

/**
 * Render the floor plan description field.
 *
 * @param WP_Post $post Floor plan post.
 * @return void
 */
function rentfetch_floorplans_description_callback( $post ) {
	$description = get_post_meta( $post->ID, 'floorplan_description', true );
	?>
	<div class="rf-metabox rf-metabox-floorplans">
		<div class="field">
			<div class="column">
				<label for="floorplan_description">Floor Plan Description</label>
			</div>
			<div class="column">
				<textarea
					id="floorplan_description"
					name="floorplan_description"
					class="wp-editor-area rf-property-lazy-editor"
					rows="3"
					data-rf-rich-editor
				><?php echo esc_textarea( $description ); ?></textarea>
			</div>
		</div>
	</div>
	<?php
}
