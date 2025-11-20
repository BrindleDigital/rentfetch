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

	$menu_icon = 'PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNDMuOCAxNTAiPjxwYXRoIGQ9Ik01Ny4zNywxNTBIMjIuODZhNC43Niw0Ljc2LDAsMCwxLTQuNzYtNC43NlYzNC43OEE0Ljc2LDQuNzYsMCwwLDEsMjIuODYsMzBoNTIuMmMxNy44OSwwLDI2LjU4LDcuNjcsMjYuNTgsMjMuNDMsMCw3LTMsMjMuMTMtMzEsMjMuMTNINTguMzJhNC43Niw0Ljc2LDAsMSwxLDAtOS41Mkg3MC42NGMxNC4yNSwwLDIxLjQ4LTQuNTgsMjEuNDgtMTMuNjEsMC04LjgtMi43OS0xMy45LTE3LjA2LTEzLjlIMjcuNjNWMTQwLjQ4aDI1VjEyNS42NWE0Ljc3LDQuNzcsMCwwLDEsOS41MywwdjE5LjU5QTQuNzYsNC43NiwwLDAsMSw1Ny4zNywxNTBabTYzLS4yM2MtMjguNDgsMC0zNy4zMi0xNi42LTQzLjc3LTI4LjcyLTUuMzEtMTAtOC40Ny0xNS4xNi0xNi45MS0xNS4xNmE0Ljc2LDQuNzYsMCwwLDEtLjA1LTkuNTJDODkuMzEsOTYsMTAyLjE4LDkwLDEwMi4zLDg5LjkzbC4yMS0uMWMxNS4wOS02LjQ5LDIyLjExLTE3LjU4LDIyLjExLTM0Ljg5LDAtMzEuNDEtMTYuODYtNDUuNDItNTQuNjYtNDUuNDJINC43NkE0Ljc2LDQuNzYsMCwwLDEsNC43NiwwSDcwYzIxLjI4LDAsMzYuODMsNC4zMiw0Ny41NSwxMy4xOSwxMSw5LjE0LDE2LjYzLDIzLjE5LDE2LjYzLDQxLjc1LDAsMjEtOS4zNCwzNS42My0yNy43Nyw0My42LTEuMy42MS0xMC4xOCw0LjUzLTI4LjY1LDYuMzlBNjcuMiw2Ny4yLDAsMCwxLDg1LDExNi41OGM2LjQ4LDEyLjE3LDEyLjYxLDIzLjY3LDM1LjM3LDIzLjY3YTg4LjU4LDg4LjU4LDAsMCwwLDEzLjYzLS45MWwtMS40My0xOS4xNWgwYTIxLjUzLDIxLjUzLDAsMCwxLTE1LjA5LTUuNyw0Ljc2LDQuNzYsMCwwLDEsNi40LTcuMDYsMTEuODksMTEuODksMCwwLDAsOC42OSwzLjIzLDIwLjA5LDIwLjA5LDAsMCwwLDMuNDktLjMzLDQuNzgsNC43OCwwLDAsMSw1LjY3LDQuMzJsMi4xLDI4LjEyYTQuNzcsNC43NywwLDAsMS0zLjI1LDQuODdDMTM5Ljg5LDE0Ny44NiwxMzMuNzYsMTQ5Ljc3LDEyMC4zNSwxNDkuNzdaIiBmaWxsPSIjZjBmNmZjOTkiLz48L3N2Zz4=';

	// Add Rent Fetch options page to the admin menu.
	add_menu_page(
		'Rent Fetch Options', // Page title.
		'Rent Fetch', // Menu title.
		'manage_options', // Capability required to access the menu.
		'rentfetch-options', // Menu slug.
		'rentfetch_options_page_html', // Callback function to render the page.
		'data:image/svg+xml;base64,' . $menu_icon, // Menu icon.
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

	// Add Documentation sub-menu page to the admin menu, linking to a third-party URL.
	add_submenu_page(
		'rentfetch-options', // Parent menu slug.
		'Documentation', // Page title.
		'Documentation', // Menu title.
		'manage_options', // Capability required to access the menu.
		'rentfetch-documentation', // Menu slug.
		'rentfetch_documentation_page_html' // Callback function to render the page.
	);

	// Add Floorplans sub-menu page to the admin menu.
	add_submenu_page(
		'rentfetch-options', // Parent menu slug.
		'Floor plan shortcodes', // Page title.
		'Floor plan shortcodes', // Menu title.
		'manage_options', // Capability required to access the menu.
		'admin.php?page=rentfetch-options&tab=floorplans&section=floorplan-embed', // Menu slug with parameters.
		'' // No callback since it's redirecting to the main options page.
	);

	// Add Properties Embed sub-menu page to the admin menu.
	add_submenu_page(
		'rentfetch-options', // Parent menu slug.
		'Property shortcodes', // Page title.
		'Property shortcodes', // Menu title.
		'manage_options', // Capability required to access the menu.
		'admin.php?page=rentfetch-options&tab=properties&section=property-settings-embed', // Menu slug with parameters.
		'' // No callback since it's redirecting to the main options page.
	);
}
add_action( 'admin_menu', 'rentfetch_options_page' );
