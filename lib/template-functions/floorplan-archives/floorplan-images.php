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
	wp_enqueue_script( 'rentfetch-floorplan-images-slider-init' );

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
	if ( ! $images ) {
		return;
	}

	wp_enqueue_script( 'blaze-script' );
	wp_enqueue_script( 'rentfetch-floorplan-images-slider-init' );
	wp_enqueue_style( 'blaze-style' );

	wp_enqueue_style( 'rentfetch-glightbox-style' );
	wp_enqueue_script( 'rentfetch-glightbox-script' );
	wp_enqueue_script( 'rentfetch-glightbox-init' );

	// random number.
	$rand = wp_rand( 10, 10000 );

	$is_single_floorplan = is_singular( 'floorplans' );
	$slider_classes      = $is_single_floorplan ? 'floorplan-images-slider blaze-slider has-floorplan-image-thumbnails' : 'floorplan-images-slider blaze-slider';

	printf( '<div class="%s">', esc_attr( $slider_classes ) );
		echo '<div class="blaze-container">';
			echo '<div class="blaze-track-container">';
				echo '<div class="blaze-track">';

	foreach ( $images as $index => $image ) {
		$alt         = $image['alt'] ?? get_the_title();
		$display_url = $image['display_url'] ?? ( function_exists( 'rentfetch_get_resized_rentcafe_image_url' ) ? rentfetch_get_resized_rentcafe_image_url( $image['url'], 600 ) : $image['url'] );

		// check if the image url includes "fallback".
		if ( strpos( $image['url'], 'fallback' ) !== false ) {
			printf( '<div class="floorplan-image-slide" data-floorplan-image-index="%s">', (int) $index );
				printf( '<img class="floorplan-image" src="%s" alt="%s" loading="lazy" decoding="async">', esc_url( $display_url ), esc_attr( $alt ) );
			echo '</div>';
		} else {
			printf( '<div class="floorplan-image-slide" data-floorplan-image-index="%s">', (int) $index );
				printf( '<img class="floorplan-image floorplan-image-gallery" data-gallery="gallery-%s" data-href="%s" src="%s" alt="%s" loading="lazy" decoding="async">', (int) $rand, esc_url( $image['url'] ), esc_url( $display_url ), esc_attr( $alt ) );
			echo '</div>';
		}
	}

				echo '</div>'; // .blaze-track.
			echo '</div>'; // .blaze-track-container.

	if ( count( $images ) > 1 ) {
		echo '<div class="blaze-buttons">';
			echo '<button class="blaze-prev" type="button" aria-label="Previous photo"></button>';
			echo '<button class="blaze-next" type="button" aria-label="Next photo"></button>';
		echo '</div>'; // .blaze-buttons.
	}

		echo '</div>'; // .blaze-container.

	if ( $is_single_floorplan && count( $images ) > 1 ) {
		echo '<div class="floorplan-image-thumbnails" aria-label="Choose a floorplan photo">';
		foreach ( $images as $index => $image ) {
			$thumbnail_url = $image['thumbnail_url'] ?? $image['url'];
			$current       = 0 === $index ? ' is-active' : '';
			$aria_current  = 0 === $index ? ' aria-current="true"' : '';
			printf(
				'<button class="floorplan-image-thumbnail%s" type="button" data-floorplan-image-index="%s" data-floorplan-sample-src="%s" aria-label="View photo %s"%s><img src="%s" alt="" loading="lazy" decoding="async"></button>',
				esc_attr( $current ),
				(int) $index,
				esc_url( $thumbnail_url ),
				(int) $index + 1,
				$aria_current, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_url( $thumbnail_url )
			);
		}
		echo '</div>';
	}
	echo '</div>'; // .blaze-slider.
}
