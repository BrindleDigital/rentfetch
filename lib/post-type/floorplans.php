<?php
/**
 * Register the floorplans content type
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register floorplans
 */
function rentfetch_register_floorplans_cpt() {

	// * Floorplans
	$name_plural   = 'Floor Plans';
	$name_singular = 'Floor Plan';
	$post_type     = 'floorplans';
	$slug          = 'floorplans';
	$supports      = array( 'title' );
	$menu_icon     = 'PHN2ZyBpZD0iYSIgaGVpZ2h0PSIyMCIgd2lkdGg9IjIwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCI+PHBhdGggZD0iTTcuNzYxMTksMTAuODQ1Nzd2MS4zOTMwM2gxLjU5MjA0di0uNTk3MDJjMC0uMTk5LDAtLjM5ODAxLC4wOTk1LS41OTcwMi0uMjk4NTEsMC0uNDk3NTEtLjA5OTUtLjY5NjUyLS4xOTksMCwwLS45OTUwMywwLS45OTUwMywwWiIgZmlsbD0iI2YwZjZmYzk5Ii8+PHJlY3QgeD0iNy43NjExOSIgeT0iMTQuNDI3ODYiIHdpZHRoPSIxLjU5MjA0IiBoZWlnaHQ9IjEuMzkzMDMiIGZpbGw9IiNmMGY2ZmM5OSIvPjxwYXRoIGQ9Ik01Ljk3MDE1LDUuNDcyNjR2MS4yOTM1M2g0Ljk3NTEydi0xLjI5MzUzaC0xLjY5MTU0VjEuMzkzMDRoNS44NzA2NVYzLjY4MTU5aC0xLjc5MTA1djEuMjkzNTNoMS43OTEwNXYyLjE4OTA1aC0xLjc5MTA1di43OTYwMmgxLjA5NDUzYy43OTYwMiwwLDEuNDkyNTQsLjA5OTUsMi4wODk1NSwuMTk5VjBILjU5NzAxVjE1LjgyMDlINS41NzIxNHYtMS4zOTMwM0gxLjk5MDA1di0zLjk4MDFoMi4xODkwNXYxLjc5MTA1aDEuMzkzMDR2LTMuMTg0MDhIMS45OTAwNVYxLjM5MzAzSDcuNzYxMTlWNS40NzI2NGgtMS43OTEwNVoiIGZpbGw9IiNmMGY2ZmM5OSIvPjxwYXRoIGQ9Ik0xMy40MzI4NCwxNy44MTA5NWMtLjE5OSwwLS4yOTg1MSwuMTk5MDEtLjI5ODUxLC4yOTg1MXYxLjA5NDUzaC0xLjc5MTA1di03LjE2NDE4aDMuMzgzMDhjLjk5NTAyLDAsMS4xOTQwMywuMzk4MDEsMS4xOTQwMywuOTk1MDJzLS40OTc1MSwuOTk1MDItMS40OTI1NCwuOTk1MDJoLS44OTU1MmMtLjE5OSwwLS4yOTg1MSwuMTk5LS4yOTg1MSwuMjk4NTEsMCwuMTk5LC4xOTksLjI5ODUxLC4yOTg1MSwuMjk4NTFoLjg5NTUyYzEuOTkwMDUsMCwyLjE4OTA1LTEuMTk0MDMsMi4xODkwNS0xLjY5MTU0LDAtMS4wOTQ1My0uNTk3MDItMS42OTE1NC0xLjg5MDU1LTEuNjkxNTRoLTMuNjgxNTljLS4xOTksMC0uMjk4NTEsLjE5OS0uMjk4NTEsLjI5ODUxdjcuODYwN2MwLC4xOTkwMSwuMTk5LC4yOTg1MSwuMjk4NTEsLjI5ODUxaDIuNDg3NTZjLjE5OSwwLC4yOTg1MS0uMTk5MDEsLjI5ODUxLS4yOTg1MXYtMS4zOTMwM2MtLjA5OTUsMC0uMTk5LS4xOTkwMS0uMzk4MDEtLjE5OTAxWiIgZmlsbD0iI2YwZjZmYzk5Ii8+PHBhdGggZD0iTTE5LjQwMjk5LDE3LjQxMjk0YzAtLjA5OTUtLjA5OTUtLjE5OTAxLS4wOTk1LS4xOTkwMS0uMDk5NS0uMDk5NS0uMTk5MDEtLjA5OTUtLjI5ODUxLS4wOTk1aC0uMTk5MDFjLS4yOTg1MSwwLS4zOTgwMS0uMDk5NS0uNTk3MDItLjE5OTAxLS4wOTk1LS4wOTk1LS4zOTgwMS0uMDk5NS0uNDk3NTEsMHMtLjA5OTUsLjM5ODAxLDAsLjQ5NzUxYy4yOTg1MSwuMjk4NTEsLjY5NjUyLC4zOTgwMSwxLjA5NDUzLC4zOTgwMWgwbC4wOTk1LDEuMzkzMDNjLS4xOTkwMSwwLS40OTc1MSwuMDk5NS0uOTk1MDIsLjA5OTUtMS41OTIwNCwwLTIuMDg5NTUtLjc5NjAyLTIuNDg3NTYtMS42OTE1NC0uMTk5LS4yOTg1MS0uMjk4NTEtLjU5NzAyLS40OTc1MS0uNzk2MDIsMS4yOTM1My0uMDk5NSwxLjk5MDA1LS4zOTgwMSwyLjA4OTU1LS40OTc1MSwxLjI5MzUzLS41OTcwMiwxLjk5MDA1LTEuNTkyMDQsMS45OTAwNS0zLjA4NDU4LDAtMS4yOTM1My0uMzk4MDEtMi4yODg1Ni0xLjE5NDAzLTIuOTg1MDctLjc5NjAyLS41OTcwMi0xLjg5MDU1LS44OTU1Mi0zLjM4MzA4LS44OTU1MmgtNC43NzYxMmMtLjE5OSwwLS4yOTg1MSwuMTk5LS4yOTg1MSwuMjk4NTEsMCwuMTk5LC4xOTksLjI5ODUxLC4yOTg1MSwuMjk4NTFoNC42NzY2MmMyLjY4NjU3LDAsMy44ODA2LC45OTUwMiwzLjg4MDYsMy4yODM1OCwwLDEuMTk0MDMtLjQ5NzUxLDEuOTkwMDUtMS41OTIwNCwyLjQ4NzU2aDBzLS44OTU1MiwuMzk4MDEtMy4wODQ1OCwuNDk3NTFjLS4xOTksMC0uMjk4NTEsLjE5OTAxLS4yOTg1MSwuMjk4NTEsMCwuMTk5MDEsLjE5OSwuMjk4NTEsLjI5ODUxLC4yOTg1MSwuNTk3MDIsMCwuNzk2MDIsLjM5ODAxLDEuMTk0MDMsMS4wOTQ1MywuNDk3NTEsLjg5NTUyLDEuMDk0NTMsMi4wODk1NSwzLjA4NDU4LDIuMDg5NTUsLjk5NTAyLDAsMS4zOTMwMy0uMDk5NSwxLjM5MzAzLS4xOTkwMSwuMDk5NSwwLC4xOTkwMS0uMTk5MDEsLjE5OTAxLS4yOTg1MXYtMi4wODk1NVoiIGZpbGw9IiNmMGY2ZmM5OSIvPjwvc3ZnPg==';

	$labels = array(
		'name'               => $name_plural,
		'singular_name'      => $name_singular,
		'add_new'            => 'Add new',
		'add_new_item'       => 'Add new ' . $name_singular,
		'edit_item'          => 'Edit ' . $name_singular,
		'new_item'           => 'New ' . $name_singular,
		'view_item'          => 'View ' . $name_singular,
		'search_items'       => 'Search ' . $name_plural,
		'not_found'          => 'No ' . $name_plural . ' found',
		'not_found_in_trash' => 'No ' . $name_plural . ' found in trash',
		'parent_item_colon'  => '',
		'menu_name'          => $name_plural,
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'capability_type'    => 'post',
		'rewrite'            => array( 'slug' => $slug ),
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'menu_icon'          => 'data:image/svg+xml;base64,' . $menu_icon,
		'show_in_rest'       => true,
		'supports'           => $supports,
	);

	register_post_type( $post_type, $args );
}
add_action( 'init', 'rentfetch_register_floorplans_cpt', 25 );
