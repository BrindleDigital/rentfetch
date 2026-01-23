<?php
/**
 * Enqueue scripts and stylesheets
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register and enqueue scripts and stylesheets
 *
 * @return void.
 */
function rentfetch_enqueue_scripts_stylesheets() {

	// Enqueue dashicons, since we use them on the frontend.
	wp_enqueue_style( 'dashicons' );

	// Plugin styles.
	wp_enqueue_style( 'rentfetch-style', RENTFETCH_PATH . 'css/rentfetch-style.css', array(), RENTFETCH_VERSION, 'screen' );

	// NoUISlider (MIT license, for dropdown double range slider).
	wp_register_style( 'rentfetch-nouislider-style', RENTFETCH_PATH . 'vendor/nouislider/nouislider.min.css', array(), RENTFETCH_VERSION, 'screen' );
	wp_register_script( 'rentfetch-nouislider-script', RENTFETCH_PATH . 'vendor/nouislider/nouislider.min.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-nouislider-init-script', RENTFETCH_PATH . 'js/rentfetch-search-map-nouislider-init.js', array( 'jquery' ), RENTFETCH_VERSION, true );

	// glightbox (MIT license): https://biati-digital.github.io/glightbox/.
	wp_register_style( 'rentfetch-glightbox-style', RENTFETCH_PATH . 'vendor/glightbox/dist/css/glightbox.min.css', array(), RENTFETCH_VERSION, 'screen' );
	wp_register_script( 'rentfetch-glightbox-script', RENTFETCH_PATH . 'vendor/glightbox/dist/js/glightbox.min.js', '', RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-glightbox-init', RENTFETCH_PATH . 'js/rentfetch-glightbox-init.js', array( 'rentfetch-glightbox-script', 'jquery' ), RENTFETCH_VERSION, true );

	// Properties search.
	wp_register_script( 'rentfetch-search-properties-ajax', RENTFETCH_PATH . 'js/rentfetch-search-properties-ajax.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-property-search-featured-filters-toggle', RENTFETCH_PATH . 'js/rentfetch-property-search-featured-filters-toggle.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-property-search-filters-dialog', RENTFETCH_PATH . 'js/rentfetch-property-search-filters-dialog.js', array( 'jquery' ), RENTFETCH_VERSION, true );

	// Floorplans search.
	wp_register_script( 'rentfetch-search-floorplans-ajax', RENTFETCH_PATH . 'js/rentfetch-search-floorplans-ajax.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-floorplan-search-featured-filters-toggle', RENTFETCH_PATH . 'js/rentfetch-floorplan-search-featured-filters-toggle.js', array( 'jquery' ), RENTFETCH_VERSION, true );

	// Google Maps script.
	// we must enqueue this script here instead of within the shortcode because doing it in the shortcode breaks in FSE themes.
	$key = apply_filters( 'rentfetch_get_google_maps_api_key', null );
	wp_enqueue_script( 'rentfetch-google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $key . '&loading=async&callback=rentfetchGoogleMapsLoaded', array(), RENTFETCH_VERSION, true );

	// Properties map (the map itself).
	wp_register_script( 'rentfetch-property-map', RENTFETCH_PATH . 'js/rentfetch-property-map.js', array( 'jquery', 'rentfetch-google-maps' ), RENTFETCH_VERSION, true );

	// Localize the google maps script, then enqueue that.
	$maps_options = array(
		'json_style'                    => json_decode( get_option( 'rentfetch_options_google_maps_styles' ) ),
		'marker_url'                    => get_option( 'rentfetch_options_google_map_marker' ),
		'google_maps_default_latitude'  => get_option( 'rentfetch_options_google_maps_default_latitude' ),
		'google_maps_default_longitude' => get_option( 'rentfetch_options_google_maps_default_longitude' ),
	);

	// we must localize and enqueue this script here instead of within the shortcode because doing it in the shortcode breaks in FSE themes.
	wp_localize_script( 'rentfetch-property-map', 'options', $maps_options );

	wp_register_script( 'rentfetch-single-property-map', RENTFETCH_PATH . 'js/rentfetch-single-property-map.js', array( 'jquery', 'rentfetch-google-maps' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-property-search-scroll-to-active-property', RENTFETCH_PATH . 'js/rentfetch-property-search-scroll-to-active-property.js', array( 'jquery' ), RENTFETCH_VERSION, true );

	// Properties searchbar.
	wp_register_script( 'rentfetch-search-filters-general', RENTFETCH_PATH . 'js/rentfetch-search-filters-general.js', array( 'jquery' ), RENTFETCH_VERSION, true );

	// Properties in archive.
	wp_register_script( 'rentfetch-property-images-slider-init', RENTFETCH_PATH . 'js/rentfetch-property-images-slider-init.js', array( 'jquery' ), RENTFETCH_VERSION, true );

	// Single properties.
	wp_register_script( 'properties-single-collapse-subnav', RENTFETCH_PATH . 'js/rentfetch-property-single-collapse-subnav.js', array( 'jquery' ), RENTFETCH_VERSION, true );

	// Blaze slider (MIT license).
	wp_register_style( 'blaze-style', RENTFETCH_PATH . 'vendor/blaze-slider/blaze.css', array(), RENTFETCH_VERSION );
	wp_register_script( 'blaze-script', RENTFETCH_PATH . 'vendor/blaze-slider/blaze-slider.min.js', array(), RENTFETCH_VERSION, true );
	wp_register_script( 'blaze-more-properties-init', RENTFETCH_PATH . 'js/rentfetch-blaze-more-properties-init.js', array( 'blaze-script' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-floorplan-images-slider-init', RENTFETCH_PATH . 'js/rentfetch-blaze-floorplan-images-init.js', array( 'blaze-script' ), RENTFETCH_VERSION, true );

	// Property fees tooltip.
	wp_register_script( 'rentfetch-property-fees-tooltip', RENTFETCH_PATH . 'js/rentfetch-property-fees-tooltip.js', array( 'jquery' ), RENTFETCH_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'rentfetch_enqueue_scripts_stylesheets' );

/**
 * Admin enqueues
 */
function rentfetch_enqueue_in_admin_metabox_properties() {

	// The main admin stylesheet.
	wp_enqueue_style( 'rentfetch-admin', RENTFETCH_PATH . 'css/admin.css', array(), RENTFETCH_VERSION );

	// Metabox scripts.
	wp_register_script( 'rentfetch-metabox-properties-images', RENTFETCH_PATH . 'js/metabox-properties-images.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-metabox-properties-tour', RENTFETCH_PATH . 'js/metabox-properties-tour.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-metabox-properties-video', RENTFETCH_PATH . 'js/metabox-properties-video.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-metabox-floorplans-images', RENTFETCH_PATH . 'js/metabox-floorplans-images.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-metabox-properties', RENTFETCH_PATH . 'js/metabox-properties.js', array( 'jquery' ), RENTFETCH_VERSION, true );

	// API response editor (CodeMirror initializer). Registered here; enqueued where needed.
	// Note: don't include 'wp-code-editor' here as a dependency because we call wp_enqueue_code_editor()
	// at enqueue time in the metabox; including it here may surface a 'missing dependency' warning
	// in some environments.
	wp_register_script( 'rentfetch-api-response-editor', RENTFETCH_PATH . 'js/rentfetch-api-response-editor.js', array( 'jquery', 'underscore' ), RENTFETCH_VERSION, true );

	// WordPress doesn't include any jQuery UI styles by default, so we need to include them.
	wp_register_style( 'rentfetch-jquery-style', RENTFETCH_PATH . 'vendor/jquery-theme-smoothness/jquery-ui.css', array(), RENTFETCH_VERSION );

	// Admin options page scripts.
	wp_register_script( 'rentfetch-options-floorplan-buttons', RENTFETCH_PATH . 'js/rentfetch-options-floorplan-buttons.js', array( 'jquery' ), RENTFETCH_VERSION, true );
	wp_register_script( 'rentfetch-options-documentation-submenu', RENTFETCH_PATH . 'js/rentfetch-options-documentation-submenu.js', array( 'jquery' ), RENTFETCH_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_in_admin_metabox_properties' );
