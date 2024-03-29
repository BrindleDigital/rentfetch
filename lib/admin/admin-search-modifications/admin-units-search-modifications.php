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

	if ( ! isset( $_GET['s'] ) ) {
		return $join;
	}

	if ( ! isset( $_GET['post_type'] ) ) {
		return $join;
	}

	// I want the filter only when performing a search on edit page of Custom Post Type named "units".
	if ( is_admin() && 'edit.php' === $pagenow && 'units' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {
		$join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
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

	if ( ! isset( $_GET['s'] ) ) {
		return $where;
	}

	if ( ! isset( $_GET['post_type'] ) ) {
		return $where;
	}

	// I want the filter only when performing a search on edit page of Custom Post Type named "units".
	if ( is_admin() && 'edit.php' === $pagenow && 'units' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {

		$where = preg_replace(
			'/\(\s*' . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			'(' . $wpdb->posts . '.post_title LIKE $1) OR (' . $wpdb->postmeta . '.meta_value LIKE $1)',
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

	if ( ! isset( $_GET['s'] ) ) {
		return $groupby;
	}

	if ( ! isset( $_GET['post_type'] ) ) {
		return $groupby;
	}

	global $pagenow, $wpdb;

	if ( is_admin() && 'edit.php' === $pagenow && 'units' === $_GET['post_type'] && '' !== $_GET['s'] ) {
		$groupby = "$wpdb->posts.ID";
	}

	return $groupby;
}
add_filter( 'posts_groupby', 'rentfetch_units_limits' );
