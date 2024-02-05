<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Register the content stypes
 */

function rentfetch_register_properties_cpt() {

	//* Properties
	$name_plural = 'Properties';
	$name_singular = 'Property';
	$post_type = 'properties';
	$slug = 'properties';
	$supports = array( 'title', 'page-attributes' );
	
	$menu_icon = '<svg id="a" height="20" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M15.45,8.65l1.1-1.1-1.6-1.5V2.05h-1.9v2L9.05,.05,1.55,7.55l1,1.1L9.05,2.15l6.4,6.5Z" fill="#f0f6fc99"/><path d="M7.25,10.15c0-.8,.7-1.5,1.5-1.5h5.5L9.05,3.55,3.05,9.55v5.9h5.4v-3.3c0-.2,0-.4,.1-.6-.7,0-1.3-.7-1.3-1.4Z" fill="#f0f6fc99"/><path d="M12.35,18.05c-.2,0-.3,.1-.3,.3v1h-1.8v-6.9h3.2c1,0,1.2,.3,1.2,1,0,.6-.5,.9-1.5,.9h-.8c-.2,0-.3,.1-.3,.3,0,.1,0,.2,.1,.2q.1,.1,.2,.1h.9c.4,0,.7,0,.9-.1,1.1-.3,1.2-1.1,1.2-1.5,0-1.1-.6-1.6-1.8-1.6h-3.6c-.2,0-.3,.1-.3,.3v7.6c0,.2,.1,.3,.3,.3h2.4c.2,0,.3-.1,.3-.3v-1.3c0-.2-.2-.3-.3-.3Z" fill="#f0f6fc99"/><path d="M18.05,17.65c0-.1-.1-.2-.1-.2-.1-.1-.2-.1-.3-.1h-.2c-.3,0-.4-.1-.6-.2-.1-.1-.3-.1-.5,0-.1,.1-.1,.3,0,.5,.3,.3,.6,.4,1,.4l.1,1.3c-.2,0-.5,.1-.9,.1-1.6,0-2-.8-2.4-1.6-.1-.3-.3-.6-.5-.8,1.3-.1,1.9-.4,2-.4,1.3-.6,1.9-1.6,1.9-3,0-1.3-.4-2.2-1.1-2.9-.4-.4-1-.6-1.7-.8-.4-.1-.8-.1-1.2-.1h-4.8c-.2,0-.3,.1-.3,.3s.1,.3,.3,.3h4.5c.4,0,.8,0,1.2,.1,1.8,.3,2.6,1.2,2.6,3,0,1.2-.5,1.9-1.5,2.4-1,.5-.9,.4-2.9,.4-.2,0-.3,.1-.3,.3s.1,.3,.3,.3c.6,0,.8,.4,1.2,1,.4,.8,1,2,3,2,.9,0,1.3-.1,1.4-.1s.2-.2,.2-.3l-.4-1.9Z" fill="#f0f6fc99"/></svg>';
	$menu_icon = base64_encode( $menu_icon );

	$labels = array(
		'name' => $name_plural,
		'singular_name' => $name_singular,
		'add_new' => 'Add new',
		'add_new_item' => 'Add new ' . $name_singular,
		'edit_item' => 'Edit ' . $name_singular,
		'new_item' => 'New ' . $name_singular,
		'view_item' => 'View ' . $name_singular,
		'search_items' => 'Search ' . $name_plural,
		'not_found' =>  'No ' . $name_plural . ' found',
		'not_found_in_trash' => 'No ' . $name_plural . ' found in trash',
		'parent_item_colon' => '',
		'menu_name' => $name_plural,
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'rewrite' => array( 'slug' => $slug ),
		'has_archive' => false,
		'hierarchical' => false,
		'menu_position' => null,
		"menu_icon" => 'data:image/svg+xml;base64,' . $menu_icon,
		'show_in_rest' => true,
		'supports' => $supports,
	);

	register_post_type( $post_type, $args );

}
add_action( 'init', 'rentfetch_register_properties_cpt', 20 );
