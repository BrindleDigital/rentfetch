<?php
/**
 * This file contains modifications to the search functionality for the Properties custom post type.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Allow the properties search to find metadata values
 *
 * @param string $join The SQL JOIN clause.
 *
 * @return string $join.
 */
function rentfetch_properties_search_join( $join ) {

	global $pagenow, $wpdb;

	if ( empty( $_GET['s'] ) || empty( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		return $join;
	}

	if ( is_admin() && 'edit.php' === $pagenow && 'properties' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		if ( false === strpos( $join, 'searchmeta_properties' ) ) {
			$join .= ' LEFT JOIN ' . $wpdb->postmeta . ' AS searchmeta_properties ON ( ' . $wpdb->posts . '.ID = searchmeta_properties.post_id ) ';
		}
	}

	return $join;
}
add_filter( 'posts_join', 'rentfetch_properties_search_join' );

/**
 * Filters the WHERE clause of the query
 *
 * @param string $where The SQL WHERE clause.
 *
 * @return string $where.
 */
function rentfetch_properties_search_where( $where ) {

	global $pagenow, $wpdb;

	if ( empty( $_GET['s'] ) || empty( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		return $where;
	}

	if ( is_admin() && 'edit.php' === $pagenow && 'properties' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		$where = preg_replace(
			'/\(\s*' . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			'(' . $wpdb->posts . '.post_title LIKE $1) OR (searchmeta_properties.meta_value LIKE $1)',
			$where
		);
	}

	return $where;
}
add_filter( 'posts_where', 'rentfetch_properties_search_where' );

/**
 * Filter he GROUP BY clause of the query.
 *
 * @param string $groupby The SQL GROUP BY clause.
 *
 * @return string $groupby.
 */
function rentfetch_properties_limits( $groupby ) {

	global $pagenow, $wpdb;

	if ( empty( $_GET['s'] ) || empty( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		return $groupby;
	}

	if ( is_admin() && 'edit.php' === $pagenow && 'properties' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read only.
		$groupby = "$wpdb->posts.ID";
	}

	return $groupby;
}
add_filter( 'posts_groupby', 'rentfetch_properties_limits' );
