<?php
/**
 * This file has the Rent Fetch functions for getting property times.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get the property images
 *
 * @param   array $args  the image size (optional).
 *
 * @return  array the property images.
 */
function rentfetch_get_property_images( $args = null ) {
	global $post;

	// bail if this isn't a property.
	if ( 'properties' !== $post->post_type ) {
		return;
	}

	$manual_images   = rentfetch_get_property_images_manual( $args );
	$yardi_images    = rentfetch_get_property_images_yardi( $args );
	$fallback_images = rentfetch_get_property_images_fallback( $args );

	if ( $manual_images ) {
		return apply_filters( 'rentfetch_filter_property_images', $manual_images );
	} elseif ( $yardi_images ) {
		return apply_filters( 'rentfetch_filter_property_images', $yardi_images );
	} elseif ( $fallback_images ) {
		return apply_filters( 'rentfetch_filter_property_images', $fallback_images );
	} else {
		return;
	}
}

/**
 * Get the manual property images
 *
 * @param   array $args  the image size (optional).
 *
 * @return  array the property images.
 */
function rentfetch_get_property_images_manual( $args ) {
	global $post;

	if ( isset( $args['size'] ) ) {
		$size = $args['size'];
	} else {
		$size = 'large';
	}

	$manual_image_ids = get_post_meta( get_the_ID(), 'images', true );

	// bail if we don't have any.
	if ( ! $manual_image_ids ) {
		return;
	}

	// bail if we only have one empty string in the manual images array.
	if ( 1 === count( $manual_image_ids ) && '' === $manual_image_ids[0] ) {
		return;
	}

	$manual_images = array();

	foreach ( $manual_image_ids as $manual_image_id ) {

		$manual_images[] = array(
			'url'     => wp_get_attachment_image_url( $manual_image_id, $size ),
			'title'   => get_the_title( $manual_image_id ),
			'alt'     => get_post_meta( $manual_image_id, '_wp_attachment_image_alt', true ),
			'caption' => get_the_excerpt( $manual_image_id ),
		);
	}

	return $manual_images;
}

/**
 * Get the yardi property images
 *
 * @param   array $args  the image size (optional).
 *
 * @return  array the property images.
 */
function rentfetch_get_property_images_yardi( $args ) {
	global $post;

	$args; // phpcs:ignore

	$yardi_images_string = get_post_meta( get_the_ID(), 'yardi_property_images', true );

	// bail if there's no yardi images.
	if ( ! $yardi_images_string ) {
		return;
	}

	// rarely, an error might get saved here (typically 1050 or 1020). if so, bail.
	if ( strpos( $yardi_images_string, 'Error' ) !== false ) {
		return;
	}

	$yardi_images_json = json_decode( $yardi_images_string );
	$yardi_images      = array();

	foreach ( $yardi_images_json as $yardi_image_json ) {

		$yardi_images[] = array(
			'url'     => esc_url( $yardi_image_json->ImageURL ),
			'title'   => $yardi_image_json->Title,
			'alt'     => $yardi_image_json->AltText,
			'caption' => $yardi_image_json->Caption,
		);
	}

	return $yardi_images;
}

/**
 * Get the fallback property images
 *
 * @param   array $args  the image size (optional).
 *
 * @return  array the property images.
 */
function rentfetch_get_property_images_fallback( $args ) {

	$args; // phpcs:ignore

	$fallback_images[] = array(
		'url'     => apply_filters( 'rentfetch_sample_image', RENTFETCH_PATH . 'images/fallback-property.svg' ),
		'title'   => 'Sample image',
		'alt'     => 'Sample image',
		'caption' => null,
	);

	return $fallback_images;
}

/**
 * Output the property images grid
 *
 * @param   array $args the image size (optional).
 *
 * @return void.
 */
function rentfetch_property_images_grid( $args = null ) {

	$args; // phpcs:ignore

	$images = rentfetch_get_property_images();

	if ( ! $images ) {
		return;
	}
	
	wp_enqueue_style( 'rentfetch-glightbox-style' );
	wp_enqueue_script( 'rentfetch-glightbox-script' );
	wp_enqueue_script( 'rentfetch-glightbox-init' );

	$number_of_images = count( $images );

	// bail if we only have the sample image; we don't want to show that here.
	if ( 1 === $number_of_images && apply_filters( 'rentfetch_sample_image', RENTFETCH_PATH . 'images/fallback-property.svg' ) === $images[0]['url'] ) {
		return;
	}

	// set up our classes.
	if ( $number_of_images < 5 ) {
		$count_class = 'single-image';
	} else {
		$count_class = 'multiple-images';
	}

	printf( '<div class="property-images-grid %s">', esc_attr( $count_class ) );

	foreach ( $images as $image ) {
		printf( '<div class="image-item"><a class="property-image-grid-link" data-gallery="property-images-grid" href="%s"><img src="%s" alt="%s" title="%s" /></a></div>', esc_url( $image['url'] ), esc_url( $image['url'] ), esc_html( $image['alt'] ), esc_html( $image['title'] ) );
	}

	if ( $number_of_images > 1 ) {
		printf( '<a href="#" class="view-all-images">View %s images</a>', (int) $number_of_images );
	}

	echo '</div>';
}
