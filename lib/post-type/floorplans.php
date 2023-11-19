<?php

/**
 * Register the content stypes
 */
add_action( 'init', 'rentfetch_register_floorplans_cpt', 25 );
function rentfetch_register_floorplans_cpt() {

	//* Floorplans
	$name_plural = 'Floorplans';
	$name_singular = 'Floorplan';
	$post_type = 'floorplans';
	$slug = 'floorplans';
	$supports = array( 'title' );
	$menu_icon = RENTFETCH_PATH . 'images/admin-icon-grayscale-floorplans.svg';
	
	$arrContextOptions=array(
      "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );  
	
	$menu_icon = file_get_contents( $menu_icon, false, stream_context_create($arrContextOptions));
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