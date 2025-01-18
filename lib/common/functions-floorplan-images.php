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
	$entrata_images    = rentfetch_get_floorplan_images_entrata();
	$rentmanager_images    = rentfetch_get_floorplan_images_rentmanager();
	$fallback_images = rentfetch_get_floorplan_images_fallback();

	if ( $manual_images ) {
		return $manual_images;
	} elseif ( $yardi_images ) {
		return $yardi_images;
	} elseif( $entrata_images ) {
		return $entrata_images;
	} elseif( $rentmanager_images) {
		return $rentmanager_images;
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
	global $post;
	
	$images_string = get_post_meta( get_the_ID(), 'floorplan_image_url', true );
	$floorplan_source = get_post_meta( get_the_ID(), 'floorplan_source', true );
	

	// bail if there's no yardi images.
	if ( ! $images_string ) {
		return;
	}
	
	// bail if this isn't a yardi floorplan.
	if ( 'yardi' !== $floorplan_source ) {
		return;
	}

	$images_array_source = explode( ',', $images_string );
	$images_array_return = array();

	foreach ( $images_array_source as $image ) {
		$images_array_return[] = array(
			'url' => esc_url( $image ),
		);
	}

	return $images_array_return;
}

/**
 * Get images that came from Entrata.
 *
 * @return array an array of images.
 */
function rentfetch_get_floorplan_images_entrata() {
	global $post;
	
	$images_string = get_post_meta( get_the_ID(), 'floorplan_image_url', true );
	$floorplan_source = get_post_meta( get_the_ID(), 'floorplan_source', true );
	

	// bail if there's no yardi images.
	if ( ! $images_string ) {
		return;
	}
	
	// bail if this isn't a yardi floorplan.
	if ( 'entrata' !== $floorplan_source ) {
		return;
	}

	$images_array_source = explode( ',', $images_string );
	$images_array_return = array();

	foreach ( $images_array_source as $image ) {
		$images_array_return[] = array(
			'url' => esc_url( $image ),
		);
	}

	return $images_array_return;
}

/**
 * Get images that came from RentManager.
 *
 * @return  array an array of images.
 */
function rentfetch_get_floorplan_images_rentmanager() {
	global $post;
		
	$images_source = get_post_meta( get_the_ID(), 'floorplan_image_url', true );
	$floorplan_source = get_post_meta( get_the_ID(), 'floorplan_source', true );
	
	// bail if there's no images.
	if ( ! $images_source || ! is_array( $images_source ) ) {
		return;
	}

	// bail if this isn't a rentmanager floorplan.
	if ( 'rentmanager' !== $floorplan_source ) {
		return;
	}

	$images_return = array();
		
	foreach ( $images_source as $image ) {
		$images_return[] = array(
			'url' => esc_url( $image['File']['DownloadURL'] ),
		);
	}

	return $images_return;
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
