<?php
/**
 * Removing the floorplans and units custom post types from redirect detection from SEOPress.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Remove the floorplans and units custom post types from SEOPress redirect detection
 *
 * @param array $cpt Array of custom post types
 * @return array Modified array of custom post types
 */
function rentfetch_remove_cpts_from_seopress_alerts($cpt) {
	unset($cpt['units']);
	unset($cpt['floorplans']);
	return $cpt;
}
add_filter( 'seopress_automatic_redirect_cpt', 'rentfetch_remove_cpts_from_seopress_alerts');