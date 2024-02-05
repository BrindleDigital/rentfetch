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

	$menu_icon = '<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 143.8 150"><path d="M57.37,150H22.86a4.76,4.76,0,0,1-4.76-4.76V34.78A4.76,4.76,0,0,1,22.86,30h52.2c17.89,0,26.58,7.67,26.58,23.43,0,7-3,23.13-31,23.13H58.32a4.76,4.76,0,1,1,0-9.52H70.64c14.25,0,21.48-4.58,21.48-13.61,0-8.8-2.79-13.9-17.06-13.9H27.63V140.48h25V125.65a4.77,4.77,0,0,1,9.53,0v19.59A4.76,4.76,0,0,1,57.37,150Zm63-.23c-28.48,0-37.32-16.6-43.77-28.72-5.31-10-8.47-15.16-16.91-15.16a4.76,4.76,0,0,1-.05-9.52C89.31,96,102.18,90,102.3,89.93l.21-.1c15.09-6.49,22.11-17.58,22.11-34.89,0-31.41-16.86-45.42-54.66-45.42H4.76A4.76,4.76,0,0,1,4.76,0H70c21.28,0,36.83,4.32,47.55,13.19,11,9.14,16.63,23.19,16.63,41.75,0,21-9.34,35.63-27.77,43.6-1.3.61-10.18,4.53-28.65,6.39A67.2,67.2,0,0,1,85,116.58c6.48,12.17,12.61,23.67,35.37,23.67a88.58,88.58,0,0,0,13.63-.91l-1.43-19.15h0a21.53,21.53,0,0,1-15.09-5.7,4.76,4.76,0,0,1,6.4-7.06,11.89,11.89,0,0,0,8.69,3.23,20.09,20.09,0,0,0,3.49-.33,4.78,4.78,0,0,1,5.67,4.32l2.1,28.12a4.77,4.77,0,0,1-3.25,4.87C139.89,147.86,133.76,149.77,120.35,149.77Z" fill="#f0f6fc99"/></svg>';

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
