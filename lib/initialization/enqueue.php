<?php

//////////////
// ENQUEUES //
//////////////

function rentfetch_enqueue_scripts_stylesheets() {
	
	// Enqueue dashicons, since we use them on the frontend
	wp_enqueue_style( 'dashicons' );
	
	// Plugin styles
	wp_enqueue_style( 'rent-fetch-style', RENTFETCH_PATH . 'css/rent-fetch-style.css', array(), RENTFETCH_VERSION, 'screen' );
	
	// NoUISlider (for dropdown double range slider)
	wp_register_style( 'rentfetch-nouislider-style', RENTFETCH_PATH . 'vendor/nouislider/nouislider.min.css', array(), RENTFETCH_VERSION, 'screen' );
	wp_register_script( 'rentfetch-nouislider-script', RENTFETCH_PATH . 'vendor/nouislider/nouislider.min.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-nouislider-init-script', RENTFETCH_PATH . 'js/rentfetch-search-map-nouislider-init.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	
	// Flatpickr
	wp_register_style( 'rentfetch-flatpickr-style', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), RENTFETCH_VERSION, 'screen' );
	wp_register_script( 'rentfetch-flatpickr-script', 'https://cdn.jsdelivr.net/npm/flatpickr', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-flatpickr-script-init', RENTFETCH_PATH . 'js/rentfetch-search-map-flatpickr-init.js', array( 'rentfetch-flatpickr-script' ), RENTFETCH_VERSION, true );
	
	// Properties search
	wp_register_script( 'rentfetch-search-properties-ajax', RENTFETCH_PATH . 'js/rentfetch-search-properties-ajax.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-property-search-featured-filters-toggle', RENTFETCH_PATH . 'js/rentfetch-property-search-featured-filters-toggle.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-property-search-filters-dialog', RENTFETCH_PATH . 'js/rentfetch-property-search-filters-dialog.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	
	// Floorplans search
	wp_register_script( 'rentfetch-search-floorplans-ajax', RENTFETCH_PATH . 'js/rentfetch-search-floorplans-ajax.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-floorplan-search-featured-filters-toggle', RENTFETCH_PATH . 'js/rentfetch-floorplan-search-featured-filters-toggle.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	
	// Properties map (the map itself)
	wp_register_script( 'rentfetch-property-map', RENTFETCH_PATH . 'js/rentfetch-property-map.js', array( 'jquery', 'rentfetch-google-maps' ), RENTFETCH_VERSION, true );
	
	// Localize the google maps script, then enqueue that
	$maps_options = array(
		'json_style' => json_decode( get_option( 'rentfetch_options_google_maps_styles' ) ),
		'marker_url' => get_option( 'rentfetch_options_google_map_marker' ),
		'google_maps_default_latitude' => get_option( 'rentfetch_options_google_maps_default_latitude' ),
		'google_maps_default_longitude' => get_option( 'rentfetch_options_google_maps_default_longitude' ),
	);
		
	wp_localize_script( 'rentfetch-property-map', 'options', $maps_options );
	wp_enqueue_script( 'rentfetch-property-map');
	
	
	wp_register_script( 'rentfetch-single-property-map', RENTFETCH_PATH . 'js/rentfetch-single-property-map.js', array( 'jquery', 'rentfetch-google-maps' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-property-search-scroll-to-active-property', RENTFETCH_PATH . 'js/rentfetch-property-search-scroll-to-active-property.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	
	// Properties searchbar
	wp_register_script( 'rentfetch-search-filters-general', RENTFETCH_PATH . 'js/rentfetch-search-filters-general.js', array( 'jquery' ), RENTFETCH_VERSION, true );
		
	// Properties in archive
	wp_register_script( 'rentfetch-property-images-slider-init', RENTFETCH_PATH . 'js/rentfetch-property-images-slider-init.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	
	
	// Single properties
	wp_register_script( 'properties-single-collapse-subnav', RENTFETCH_PATH . 'js/rentfetch-property-single-collapse-subnav.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	
	// Fancybox
	wp_register_style( 'rentfetch-fancybox-style', RENTFETCH_PATH . 'vendor/fancybox/jquery.fancybox.min.css', array(), RENTFETCH_VERSION, 'screen' );
	wp_register_script( 'rentfetch-fancybox-script', RENTFETCH_PATH . 'vendor/fancybox/jquery.fancybox.min.js', array( 'jquery' ), RENTFETCH_VERSION, true );
		
	// Slick
	// wp_register_script( 'rentfetch-slick-main-script', RENTFETCH_PATH . 'vendor/slick/slick.min.js', array('jquery'), RENTFETCH_VERSION, true );
	// wp_register_style( 'rentfetch-slick-main-styles', RENTFETCH_PATH . 'vendor/slick/slick.css', array(), RENTFETCH_VERSION );
	// wp_register_style( 'rentfetch-slick-main-theme', RENTFETCH_PATH . 'vendor/slick/slick-theme.css', array(), RENTFETCH_VERSION );
	
	// Google reCAPTCHA
	wp_register_script( 'rentfetch-google-recaptcha', 'https://www.google.com/recaptcha/api.js', array('jquery'), RENTFETCH_VERSION, true );
	
	wp_register_style( 
		'blaze-style', 
		'https://unpkg.com/blaze-slider@1.9.3/dist/blaze.css',
		array(), 
		RENTFETCH_VERSION
	);
	
	wp_register_script(
		'blaze-script',
		'https://unpkg.com/blaze-slider@1.9.3/dist/blaze-slider.min.js',
		array(),
		RENTFETCH_VERSION 
	);
	
	wp_register_script(
		'blaze-more-properties-init',
		RENTFETCH_PATH . 'js/rentfetch-blaze-more-properties-init.js', 
		array( 'blaze-script' ),
		RENTFETCH_VERSION 
	);
	
		
}
add_action( 'wp_enqueue_scripts', 'rentfetch_enqueue_scripts_stylesheets' );

function rentfetch_enqueue_in_admin_metabox_properties() {
	
	wp_register_script( 
		'rentfetch-metabox-properties-images', 
		RENTFETCH_PATH . 'js/metabox-properties-images.js', 
		array( 'jquery' ),
		RENTFETCH_VERSION
	);
	
	wp_register_script( 
		'rentfetch-metabox-properties-matterport', 
		RENTFETCH_PATH . 'js/metabox-properties-matterport.js', 
		array( 'jquery' ),
		RENTFETCH_VERSION
	);
	
	wp_register_script( 
		'rentfetch-metabox-properties-video', 
		RENTFETCH_PATH . 'js/metabox-properties-video.js', 
		array( 'jquery' ),
		RENTFETCH_VERSION
	);
	
	wp_register_script( 
		'rentfetch-metabox-floorplans-images', 
		RENTFETCH_PATH . 'js/metabox-floorplans-images.js', 
		array( 'jquery' ),
		RENTFETCH_VERSION
	);
	
	wp_enqueue_style( 
		'rentfetch-admin', 
		RENTFETCH_PATH . 'css/admin.css',
		array(), 
		RENTFETCH_VERSION
	);
		
	wp_register_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css');
	
	
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_in_admin_metabox_properties' );