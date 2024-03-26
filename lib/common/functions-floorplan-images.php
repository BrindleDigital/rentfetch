<?php
/**
 * This file adds functionality to automatically get images from several sources and put them into common formats.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get floorplan images
 *
 * @return array an array of images.
 */
function rentfetch_get_floorplan_images() {
	global $post;

	// bail if this isn't a floorplan.
	if ( 'floorplans' !== $post->post_type ) {
		return;
	}

	$manual_images   = rentfetch_get_floorplan_images_manual();
	$yardi_images    = rentfetch_get_floorplan_images_yardi();
	$fallback_images = rentfetch_get_floorplan_images_fallback();

	if ( $manual_images ) {
		return $manual_images;
	} elseif ( $yardi_images ) {
		return $yardi_images;
	} elseif ( $fallback_images ) {
		return $fallback_images;
	} else {
		return;
	}
}

/**
 * Get images that were manually added.
 *
 * @return array an array of images.
 */
function rentfetch_get_floorplan_images_manual() {
	global $post;

	$manual_image_ids = get_post_meta( get_the_ID(), 'manual_images', true );

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
			'url'     => wp_get_attachment_image_url( $manual_image_id, 'large' ),
			'title'   => get_the_title( $manual_image_id ),
			'alt'     => get_post_meta( $manual_image_id, '_wp_attachment_image_alt', true ),
			'caption' => get_the_excerpt( $manual_image_id ),
		);
	}

	return $manual_images;
}

/**
 * Get images that came from Yardi.
 *
 * @return array an array of images.
 */
function rentfetch_get_floorplan_images_yardi() {

	$yardi_images_string = get_post_meta( get_the_ID(), 'floorplan_image_url', true );

	// bail if there's no yardi images.
	if ( ! $yardi_images_string ) {
		return;
	}

	$yardi_images_array = explode( ',', $yardi_images_string );

	foreach ( $yardi_images_array as $yardi_image ) {
		$yardi_images[] = array(
			'url' => esc_url( $yardi_image ),
		);
	}

	return $yardi_images;
}

/**
 * Add the fallback image if needed.
 *
 * @return array an array of images.
 */
function rentfetch_get_floorplan_images_fallback() {

	$fallback_images[] = array(
		'url'     => apply_filters( 'rentfetch_sample_image', RENTFETCH_PATH . 'images/fallback-property.svg' ),
		'title'   => 'Sample image',
		'alt'     => 'Sample image',
		'caption' => null,
	);

	return $fallback_images;
}
