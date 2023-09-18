<?php

function rentfetch_register_areas_taxonomy() {
	register_taxonomy(
		'area',
		'neighborhoods',
		array(
			'label' 			=> __( 'Areas' ),
			'rewrite' 		=> array( 'slug' => 'area' ),
			'hierarchical' 	=> true,
			'show_in_rest' 	=> true,
		)
	);
}