<?php

add_action( 'init', 'rentfetch_register_taxonomy_floorplantype' );
function rentfetch_register_taxonomy_floorplantype() {
	register_taxonomy(
		'floorplantype',
		'floorplans',
		array(
			'label' 			=> __( 'Floorplan types' ),
			'rewrite' 		=> array( 'slug' => 'floorplantype' ),
			'hierarchical' 	=> true,
			'show_in_rest' 	=> true,
		)
	);
}