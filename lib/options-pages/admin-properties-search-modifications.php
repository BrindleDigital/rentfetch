<?php

//* Allow the properties search to find metadata values

add_filter( 'posts_join', 'properties_search_join' );
function properties_search_join ( $join ) {
    global $pagenow, $wpdb;

    // I want the filter only when performing a search on edit page of Custom Post Type named "properties".
    if ( is_admin() && 'edit.php' === $pagenow && 'properties' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {    
        $join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }
    return $join;
}

add_filter( 'posts_where', 'properties_search_where' );
function properties_search_where( $where ) {
    global $pagenow, $wpdb;

    // I want the filter only when performing a search on edit page of Custom Post Type named "properties".
    if ( is_admin() && 'edit.php' === $pagenow && 'properties' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {
        $where = preg_replace(
            "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );
        // $where.= " GROUP BY {$wpdb->posts}.id"; // Solves duplicated results
        
    }
    return $where;
}

add_filter( 'posts_groupby', 'properties_limits' );
function properties_limits($groupby) {
    global $pagenow, $wpdb;
    
    if ( !isset( $_GET['s']) )
        return;
    
    if ( is_admin() && $pagenow == 'edit.php' && $_GET['post_type']=='properties' && $_GET['s'] != '' ) {
        $groupby = "$wpdb->posts.ID";
    }
    return $groupby;
}