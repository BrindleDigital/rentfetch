<?php
/**
 * This file contains modifications to the search functionality for the Units custom post type.
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
function rentfetch_units_search_join( $join ) {

	global $pagenow, $wpdb;

	if ( empty( $_GET['s'] ) || empty( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		return $join;
	}

	// Apply only on the Units list table search page.
	if ( is_admin() && 'edit.php' === $pagenow && 'units' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		// Core may already have joined $wpdb->postmeta (inner join) for meta queries. Use a UNIQUE alias to avoid duplicate joins.
		if ( false === strpos( $join, 'searchmeta_units' ) ) {
			$join .= ' LEFT JOIN ' . $wpdb->postmeta . ' AS searchmeta_units ON ( ' . $wpdb->posts . '.ID = searchmeta_units.post_id ) ';
		}
	}

	return $join;
}
add_filter( 'posts_join', 'rentfetch_units_search_join' );

/**
 * Set up the admin search.
 *
 * @param string $where The SQL WHERE clause.
 *
 * @return string $where
 */
function rentfetch_units_search_where( $where ) {

	global $pagenow, $wpdb;

	if ( empty( $_GET['s'] ) || empty( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		return $where;
	}

	if ( is_admin() && 'edit.php' === $pagenow && 'units' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		// Replace the default (post_title LIKE 'term') segment with title OR meta (using our alias) search.
		$where = preg_replace(
			'/\(\s*' . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			'(' . $wpdb->posts . '.post_title LIKE $1) OR (searchmeta_units.meta_value LIKE $1)',
			$where
		);
	}

	return $where;
}
add_filter( 'posts_where', 'rentfetch_units_search_where' );

/**
 * Limit units shown in the results.
 *
 * @param string $groupby the groupby clause.
 *
 * @return string $groupby
 */
function rentfetch_units_limits( $groupby ) {

	if ( empty( $_GET['s'] ) || empty( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		return $groupby;
	}

	global $pagenow, $wpdb;

	if ( is_admin() && 'edit.php' === $pagenow && 'units' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		$groupby = "$wpdb->posts.ID"; // Prevent duplicate rows from meta join.
	}

	return $groupby;
}
add_filter( 'posts_groupby', 'rentfetch_units_limits' );
