<?php
/**
 * Unit images.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render the read-only synced unit image previews.
 *
 * @param WP_Post $post Unit post.
 * @return void
 */
function rentfetch_units_images_metabox_callback( $post ) {
	$image_urls = get_post_meta( $post->ID, 'unit_image_urls', true );
	if ( empty( $image_urls ) ) {
		$image_urls = get_post_meta( $post->ID, 'yardi_unit_image_urls', true );
	}

	$image_urls = array_filter(
		array_unique(
			array_map(
				'trim',
				explode( ',', implode( ',', (array) $image_urls ) )
			)
		)
	);
	?>
	<div class="rf-metabox rf-metabox-floorplans">
		<div class="field">
			<div class="column">
				<label>Synced Unit Images</label>
				<p class="description">These API images are read-only previews. Download any image that you want to reuse.</p>
			</div>
			<div class="column">
				<?php if ( empty( $image_urls ) ) : ?>
					<p class="description">No images available</p>
				<?php else : ?>
					<div class="floorplan_images">
						<?php foreach ( $image_urls as $image_url ) : ?>
							<div class="floorplan-image">
								<img width="150" height="82" loading="lazy" src="<?php echo esc_url( $image_url ); ?>" alt="">
								<a href="<?php echo esc_url( $image_url ); ?>" target="_blank" rel="noopener noreferrer" class="download" download>Download</a>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
