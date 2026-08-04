<?php
/**
 * Property editor image fields.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Markup for the properties images metabox
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_images_metabox_callback( $post ) {
	wp_enqueue_media();
	wp_enqueue_script( 'rentfetch-metabox-properties-images' );
	?>
	<div class="rf-metabox rf-metabox-properties">
		
		<?php
		// * Property Images.
		?>
		<div class="field">
			<div class="column">
				<label for="images">Custom Images</label>
			</div>
			<div class="column"> 
				<p class="description">These are custom images added to the site, and are never synced. Any image here will override any synced images.</p>               
				<?php

				$images = get_post_meta( $post->ID, 'images', true );

				// convert to string.
				if ( is_array( $images ) ) {

					$images = array_filter(
						$images,
						function ( $image_id ) {
							return is_numeric( $image_id );
						}
					);

					$images = implode( ',', $images );
				}

				$images_ids_array = explode( ',', $images );

				echo '<input id="images" type="hidden" name="images" value="' . esc_attr( $images ) . '">';

				if ( $images ) {
					echo '<div id="gallery-container">';
					foreach ( $images_ids_array as $image_id ) {
						$attachment_url = wp_get_attachment_image_src( $image_id, 'thumbnail' );
						printf( '<div class="gallery-image" data-id="%s"><img loading="lazy" style="background-color: #f7f7f7; transform: translateZ(0); will-change: transform;" width="150" height="82" src="%s"><button class="remove-image">Remove</button></div>', (int) $image_id, esc_url( $attachment_url[0] ) );
					}
					echo '</div>';
				}

				echo '<div id="gallery-container"></div>';
				echo '<input type="button" id="images_button" class="button" value="Add Images">';

				?>
				
			</div>
		</div>
		
		<?php

		$property_source = get_post_meta( $post->ID, 'property_source', true );
		if ( in_array( $property_source, array( 'yardi', 'entrata', 'engrain' ), true ) ) {
			?>
			 
			<div class="field">
				<div class="column">
					<label for="property_images">Synced Property Images</label>
					<p class="description">These images are not editable, because they're from your API. This just shows you a preview so that you can see the images being provided. Feel free to click 'download' on any of these so that you can easily grab any that you want if you're adding more.</p>
				</div>
				<div class="column">
					<?php rentfetch_render_property_editor_lazy_fragment( 'synced-images', $post->ID ); ?>
				</div>
			</div>
			<?php
		}
		?>
		
	</div>
	
	<?php
}

/**
 * Render the read-only synced image preview.
 *
 * @param WP_Post $post Property post.
 * @return void
 */
function rentfetch_render_property_synced_images_preview( $post ) {
	$property_source = get_post_meta( $post->ID, 'property_source', true );
	$property_images = array();

	if ( 'yardi' === $property_source ) {
		$property_images = rentfetch_get_property_images_yardi( null );
	} elseif ( 'entrata' === $property_source ) {
		$property_images = rentfetch_get_property_images_entrata( null );
	} elseif ( 'engrain' === $property_source ) {
		$property_images = rentfetch_get_property_images_engrain( null );
	}

	if ( empty( $property_images ) ) {
		echo '<p class="description">No images available</p>';
		return;
	}

	echo '<div class="property_images">';
	foreach ( $property_images as $property_image ) {
		$property_image_url = isset( $property_image['url'] ) ? $property_image['url'] : '';
		if ( '' === $property_image_url ) {
			continue;
		}

		printf(
			'<div class="property-image"><img width="150" height="82" loading="lazy" style="transform: translateZ(0); will-change: transform;" src="%1$s" alt=""><a href="%1$s" target="_blank" rel="noopener noreferrer" class="download" download>Download</a></div>',
			esc_url( $property_image_url )
		);
	}
	echo '</div>';
}
