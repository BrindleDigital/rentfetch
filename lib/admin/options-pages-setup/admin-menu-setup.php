<?php
/**
 * This file sets up admin area for Rent Fetch.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Adds Rent Fetch options page to the admin menu.
 */
function rentfetch_options_page() {
	// Get the contents of the Rent Fetch dashboard icon.
	$menu_icon = file_get_contents( RENTFETCH_DIR . 'images/rentfetch-dashboard-icon.svg' );
	
	// Add Rent Fetch options page to the admin menu.
	add_menu_page(
		'Rent Fetch Options', // Page title.
		'Rent Fetch', // Menu title.
		'manage_options', // Capability required to access the menu.
		'rentfetch-options', // Menu slug.
		'rentfetch_options_page_html', // Callback function to render the page.
		'data:image/svg+xml;base64,' . base64_encode( $menu_icon ), // Menu icon.
		58.99 // Menu position.
	);
	
	// Add Rent Fetch options sub-menu page to the admin menu.
	add_submenu_page(
		'rentfetch-options', // Parent menu slug.
		'Settings', // Page title.
		'Settings', // Menu title.
		'manage_options', // Capability required to access the menu.
		'rentfetch-options', // Menu slug.
		'rentfetch_options_page_html' // Callback function to render the page.
	);
	
	// Add Rent Fetch options sub-menu page to the admin menu.
	add_submenu_page(
		'rentfetch-options', // Parent menu slug.
		'Shortcodes', // Page title.
		'Shortcodes', // Menu title.
		'manage_options', // Capability required to access the menu.
		'rent-fetch-shortcodes', // Menu slug.
		'rentfetch_shortcodes_page_html' // Callback function to render the page.
	);
		
	// Add Documentation sub-menu page to the admin menu, linking to a third-party URL.
	add_submenu_page(
		'rentfetch-options', // Parent menu slug.
		'Documentation', // Page title.
		'Documentation', // Menu title.
		'manage_options', // Capability required to access the menu.
		'rentfetch-documentation', // Menu slug.
		'rentfetch_documentation_page_html' // Callback function to render the page.
	);	
}
add_action( 'admin_menu', 'rentfetch_options_page' );
