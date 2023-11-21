<?php

//* Allow the floorplans search to find metadata values

function floorplans_search_join ( $join ) {
	global $pagenow, $wpdb;

	// I want the filter only when performing a search on edit page of Custom Post Type named "floorplans".
	if ( is_admin() && 'edit.php' === $pagenow && 'floorplans' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {    
		$join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
	}
	return $join;
}
add_filter( 'posts_join', 'floorplans_search_join' );

function floorplans_search_where( $where ) {
	global $pagenow, $wpdb;

	// I want the filter only when performing a search on edit page of Custom Post Type named "floorplans".
	if ( is_admin() && 'edit.php' === $pagenow && 'floorplans' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {
		$where = preg_replace(
			"/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			"(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );
		// $where.= " GROUP BY {$wpdb->posts}.id"; // Solves duplicated results
		
	}
	return $where;
}
add_filter( 'posts_where', 'floorplans_search_where' );


function floorplans_limits($groupby) {
	
	if ( !isset( $_GET['s']) )
		return;
	
	global $pagenow, $wpdb;
	if ( is_admin() && $pagenow == 'edit.php' && $_GET['post_type']=='floorplans' && $_GET['s'] != '' ) {
		$groupby = "$wpdb->posts.ID";
	}
	return $groupby;
}
add_filter( 'posts_groupby', 'floorplans_limits' );