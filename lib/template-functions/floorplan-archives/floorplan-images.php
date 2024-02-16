<?php
/**
 * Flooroplan images
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Select how to display the floorplan images
 *
 * @return void.
 */
function rentfetch_floorplan_images() {
	
	// read the flag for whether we should use a slider.
	global $floorplan_images_use_slider;
	
	if ( false === $floorplan_images_use_slider ) {
		rentfetch_floorplan_single_image();
	} elseif ( is_singular( 'floorplans' ) ) {
		rentfetch_floorplan_image_slider();
	} elseif ( is_singular( 'properties' ) ) {
		rentfetch_floorplan_image_slider();
	} else {
		rentfetch_floorplan_single_image();
	}
}
add_action( 'rentfetch_do_floorplan_images', 'rentfetch_floorplan_images' );

/**
 * Do the single floorplan image
 *
 * @return void.
 */
function rentfetch_floorplan_single_image() {

	$images = rentfetch_get_floorplan_images();

	echo '<div class="floorplan-single-image-wrap">';
		printf( '<img class="floorplan-single-image" src="%s" loading="lazy">', esc_url( $images[0]['url'] ) );
	echo '</div>';

}

/**
 * Do the floorplan image slider
 *
 * @return void.
 */
function rentfetch_floorplan_image_slider() {

	$images = rentfetch_get_floorplan_images();

	wp_enqueue_script( 'blaze-script' );
	wp_enqueue_script( 'rentfetch-floorplan-images-slider-init' );
	wp_enqueue_style( 'blaze-style' );

	wp_enqueue_style( 'rentfetch-glightbox-style' );
	wp_enqueue_script( 'rentfetch-glightbox-script' );
	wp_enqueue_script( 'rentfetch-glightbox-init' );

	// random number.
	$rand = wp_rand( 10, 10000 );

	echo '<div class="floorplan-images-slider blaze-slider">';
		echo '<div class="blaze-container">';
			echo '<div class="blaze-track-container">';
				echo '<div class="blaze-track">';

					foreach ( $images as $image ) {

						// check if the image url includes "fallback".
						if ( strpos( $image['url'], 'fallback' ) !== false ) {
							echo '<div class="floorplan-image-slide">';
								printf( '<img class="floorplan-image" src="%s" loading="lazy">', esc_url( $image['url'] ) );
							echo '</div>';
						} else {
							echo '<div class="floorplan-image-slide">';
								printf( '<img class="floorplan-image floorplan-image-gallery" data-dallery="gallery-%s" src="%s" loading="lazy">', (int) $rand, esc_url( $image['url'] ) );
							echo '</div>';
						}
					}

				echo '</div>'; // .blaze-track.
			echo '</div>'; // .blaze-track-container.

			if ( count( $images ) > 1 ) {
				echo '<div class="blaze-buttons">';
					echo '<button class="blaze-prev"></button>';
					echo '<button class="blaze-next"></button>';
				echo '</div>'; // .blaze-buttons.
			}

		echo '</div>'; // .blaze-container.
	echo '</div>'; // .blaze-slider.
}
