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

	if ( ! isset( $_GET['s'] ) ) {
		return $join;
	}

	if ( ! isset( $_GET['post_type'] ) ) {
		return $join;
	}

	// I want the filter only when performing a search on edit page of Custom Post Type named "properties".
	if ( is_admin() && 'edit.php' === $pagenow && 'properties' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {
		$join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
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

	if ( ! isset( $_GET['s'] ) ) {
		return $where;
	}

	if ( ! isset( $_GET['post_type'] ) ) {
		return $where;
	}

	// I want the filter only when performing a search on edit page of Custom Post Type named "properties".
	if ( is_admin() && 'edit.php' === $pagenow && 'properties' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {

		$where = preg_replace(
			'/\(\s*' . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			'(' . $wpdb->posts . '.post_title LIKE $1) OR (' . $wpdb->postmeta . '.meta_value LIKE $1)',
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

	if ( ! isset( $_GET['s'] ) ) {
		return $groupby;
	}

	if ( ! isset( $_GET['post_type'] ) ) {
		return $groupby;
	}

	if ( is_admin() && 'edit.php' === $pagenow && 'properties' === $_GET['post_type'] && '' !== $_GET['s'] ) {
		$groupby = "$wpdb->posts.ID";
	}

	return $groupby;

}
add_filter( 'posts_groupby', 'rentfetch_properties_limits' );
