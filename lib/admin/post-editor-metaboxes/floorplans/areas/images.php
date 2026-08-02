<?php
/**
 * Floor plan images.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render the manual and synced floor plan image fields.
 *
 * @param WP_Post $post Floor plan post.
 * @return void
 */
function rentfetch_floorplans_images_metabox_callback( $post ) {
	$images = get_post_meta( $post->ID, 'manual_images', true );

	if ( is_array( $images ) ) {
		$images = array_filter( $images, 'is_numeric' );
		$images = implode( ',', $images );
	}

	$image_ids = array_filter( explode( ',', (string) $images ), 'is_numeric' );
	?>
	<div class="rf-metabox rf-metabox-floorplans">
		<div class="field">
			<div class="column">
				<label for="images">Manual Images</label>
			</div>
			<div class="column">
				<p class="description">These custom images never sync. Any image here overrides the synced images.</p>
				<input type="hidden" id="images" name="images" value="<?php echo esc_attr( $images ); ?>">
				<div id="gallery-container">
					<?php
					foreach ( $image_ids as $image_id ) {
						$attachment_url = wp_get_attachment_image_src( $image_id, 'thumbnail' );
						if ( ! $attachment_url ) {
							continue;
						}

						printf(
							'<div class="gallery-image" data-id="%1$d"><img loading="lazy" width="150" height="82" src="%2$s" alt=""><button class="remove-image">Remove</button></div>',
							(int) $image_id,
							esc_url( $attachment_url[0] )
						);
					}
					?>
				</div>
				<input type="button" id="images_button" class="button" value="Add Images">
			</div>
		</div>

		<?php
		$floorplan_source = get_post_meta( $post->ID, 'floorplan_source', true );
		if ( in_array( $floorplan_source, array( 'yardi', 'entrata' ), true ) ) :
			?>
			<div class="field">
				<div class="column">
					<label for="floorplan_images">Synced Floor Plan Images</label>
					<p class="description">These API images are read-only previews. Download any image that you want to reuse as a manual image.</p>
				</div>
				<div class="column">
					<?php rentfetch_render_floorplan_editor_lazy_fragment( 'synced-images', $post->ID ); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render the read-only synced floor plan image preview.
 *
 * @param WP_Post $post Floor plan post.
 * @return void
 */
function rentfetch_render_floorplan_synced_images_preview( $post ) {
	$image_urls = array_filter(
		array_map(
			'trim',
			explode( ',', (string) get_post_meta( $post->ID, 'floorplan_image_url', true ) )
		)
	);

	if ( empty( $image_urls ) ) {
		echo '<p class="description">No images available</p>';
		return;
	}

	echo '<div class="floorplan_images">';
	foreach ( $image_urls as $image_url ) {
		printf(
			'<div class="floorplan-image"><img width="150" height="82" loading="lazy" src="%1$s" alt=""><a href="%1$s" target="_blank" rel="noopener noreferrer" class="download" download>Download</a></div>',
			esc_url( $image_url )
		);
	}
	echo '</div>';
}
