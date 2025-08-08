<?php
/**
 * This file contains modifications to the search functionality for the Floorplans custom post type.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set up the admin search.
 *
 * @param string $join The SQL JOIN clause.
 *
 * @return string $join
 */
function rentfetch_floorplans_search_join( $join ) {

	global $pagenow, $wpdb;

	if ( empty( $_GET['s'] ) || empty( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		return $join;
	}

	if ( is_admin() && 'edit.php' === $pagenow && 'floorplans' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		if ( false === strpos( $join, 'searchmeta_floorplans' ) ) {
			$join .= ' LEFT JOIN ' . $wpdb->postmeta . ' AS searchmeta_floorplans ON ( ' . $wpdb->posts . '.ID = searchmeta_floorplans.post_id ) ';
		}
	}

	return $join;
}
add_filter( 'posts_join', 'rentfetch_floorplans_search_join' );

/**
 * Set up the admin search.
 *
 * @param string $where The SQL WHERE clause.
 *
 * @return string $where
 */
function rentfetch_floorplans_search_where( $where ) {

	global $pagenow, $wpdb;

	if ( empty( $_GET['s'] ) || empty( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		return $where;
	}

	if ( is_admin() && 'edit.php' === $pagenow && 'floorplans' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		$where = preg_replace(
			'/\(\s*' . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			'(' . $wpdb->posts . '.post_title LIKE $1) OR (searchmeta_floorplans.meta_value LIKE $1)',
			$where
		);
	}

	return $where;
}
add_filter( 'posts_where', 'rentfetch_floorplans_search_where' );

/**
 * Limit units shown in the results.
 *
 * @param string $groupby the groupby clause.
 *
 * @return string $groupby
 */
function rentfetch_floorplans_limits( $groupby ) {

	if ( empty( $_GET['s'] ) || empty( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		return $groupby;
	}

	global $pagenow, $wpdb;
	if ( is_admin() && 'edit.php' === $pagenow && 'floorplans' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		$groupby = "$wpdb->posts.ID";
	}

	return $groupby;
}
add_filter( 'posts_groupby', 'rentfetch_floorplans_limits' );
