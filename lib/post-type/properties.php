<?php
/**
 * Register the properties content type
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register properties
 */
function rentfetch_register_properties_cpt() {

	// * Properties
	$name_plural   = 'Properties';
	$name_singular = 'Property';
	$post_type     = 'properties';
	$slug          = 'properties';
	$supports      = array( 'title', 'page-attributes' );
	$menu_icon     = 'PHN2ZyBpZD0iYSIgaGVpZ2h0PSIyMCIgd2lkdGg9IjIwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCI+PHBhdGggZD0iTTE1LjQ1LDguNjVsMS4xLTEuMS0xLjYtMS41VjIuMDVoLTEuOXYyTDkuMDUsLjA1LDEuNTUsNy41NWwxLDEuMUw5LjA1LDIuMTVsNi40LDYuNVoiIGZpbGw9IiNmMGY2ZmM5OSIvPjxwYXRoIGQ9Ik03LjI1LDEwLjE1YzAtLjgsLjctMS41LDEuNS0xLjVoNS41TDkuMDUsMy41NSwzLjA1LDkuNTV2NS45aDUuNHYtMy4zYzAtLjIsMC0uNCwuMS0uNi0uNywwLTEuMy0uNy0xLjMtMS40WiIgZmlsbD0iI2YwZjZmYzk5Ii8+PHBhdGggZD0iTTEyLjM1LDE4LjA1Yy0uMiwwLS4zLC4xLS4zLC4zdjFoLTEuOHYtNi45aDMuMmMxLDAsMS4yLC4zLDEuMiwxLDAsLjYtLjUsLjktMS41LC45aC0uOGMtLjIsMC0uMywuMS0uMywuMywwLC4xLDAsLjIsLjEsLjJxLjEsLjEsLjIsLjFoLjljLjQsMCwuNywwLC45LS4xLDEuMS0uMywxLjItMS4xLDEuMi0xLjUsMC0xLjEtLjYtMS42LTEuOC0xLjZoLTMuNmMtLjIsMC0uMywuMS0uMywuM3Y3LjZjMCwuMiwuMSwuMywuMywuM2gyLjRjLjIsMCwuMy0uMSwuMy0uM3YtMS4zYzAtLjItLjItLjMtLjMtLjNaIiBmaWxsPSIjZjBmNmZjOTkiLz48cGF0aCBkPSJNMTguMDUsMTcuNjVjMC0uMS0uMS0uMi0uMS0uMi0uMS0uMS0uMi0uMS0uMy0uMWgtLjJjLS4zLDAtLjQtLjEtLjYtLjItLjEtLjEtLjMtLjEtLjUsMC0uMSwuMS0uMSwuMywwLC41LC4zLC4zLC42LC40LDEsLjRsLjEsMS4zYy0uMiwwLS41LC4xLS45LC4xLTEuNiwwLTItLjgtMi40LTEuNi0uMS0uMy0uMy0uNi0uNS0uOCwxLjMtLjEsMS45LS40LDItLjQsMS4zLS42LDEuOS0xLjYsMS45LTMsMC0xLjMtLjQtMi4yLTEuMS0yLjktLjQtLjQtMS0uNi0xLjctLjgtLjQtLjEtLjgtLjEtMS4yLS4xaC00LjhjLS4yLDAtLjMsLjEtLjMsLjNzLjEsLjMsLjMsLjNoNC41Yy40LDAsLjgsMCwxLjIsLjEsMS44LC4zLDIuNiwxLjIsMi42LDMsMCwxLjItLjUsMS45LTEuNSwyLjQtMSwuNS0uOSwuNC0yLjksLjQtLjIsMC0uMywuMS0uMywuM3MuMSwuMywuMywuM2MuNiwwLC44LC40LDEuMiwxLC40LC44LDEsMiwzLDIsLjksMCwxLjMtLjEsMS40LS4xcy4yLS4yLC4yLS4zbC0uNC0xLjlaIiBmaWxsPSIjZjBmNmZjOTkiLz48L3N2Zz4=';

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
add_action( 'init', 'rentfetch_register_properties_cpt', 20 );
