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
 * Get the Rent Fetch dashboard icon SVG.
 *
 * @param string $fill Icon fill color.
 * @return string SVG markup.
 */
function rentfetch_get_dashboard_icon_svg( $fill = '#f0f6fc99' ) {
	static $icon = null;

	if ( null === $icon ) {
		$icon_path = RENTFETCH_DIR . 'images/rentfetch-dashboard-icon.svg';
		$icon      = file_exists( $icon_path ) ? file_get_contents( $icon_path ) : '';
	}

	$fill = sanitize_hex_color( $fill );
	if ( ! $fill ) {
		$fill = '#f0f6fc99';
	}

	return str_replace( '#f0f6fc99', $fill, $icon );
}

/**
 * Adds Rent Fetch options page to the admin menu.
 */
function rentfetch_options_page() {

	$menu_icon           = base64_encode( rentfetch_get_dashboard_icon_svg() );
	$can_manage_settings = current_user_can( 'manage_options' );

	// Add Rent Fetch options page to the admin menu.
	add_menu_page(
		'Rent Fetch Options', // Page title.
		'Rent Fetch', // Menu title.
		'edit_pages', // Editors need access to content-type pages nested under this menu.
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
		'edit_pages', // Capability available to editors and administrators.
		'rentfetch-documentation', // Menu slug.
		'rentfetch_documentation_page_html' // Callback function to render the page.
	);

	// Add Floorplans sub-menu page to the admin menu.
	add_submenu_page(
		'rentfetch-options', // Parent menu slug.
		'Floor plan embeds', // Page title.
		'Floor plan embeds', // Menu title.
		'edit_pages', // Capability available to editors and administrators.
		$can_manage_settings ? 'admin.php?page=rentfetch-options&tab=floorplans&section=floorplan-embed' : 'rentfetch-floorplan-shortcodes', // Administrators use the settings tab.
		$can_manage_settings ? '' : 'rentfetch_floorplan_shortcodes_page_html' // Editors use the reference page.
	);

	// Add Properties Embed sub-menu page to the admin menu.
	add_submenu_page(
		'rentfetch-options', // Parent menu slug.
		'Property embeds', // Page title.
		'Property embeds', // Menu title.
		'edit_pages', // Capability available to editors and administrators.
		$can_manage_settings ? 'admin.php?page=rentfetch-options&tab=properties&section=property-settings-embed' : 'rentfetch-property-shortcodes', // Administrators use the settings tab.
		$can_manage_settings ? '' : 'rentfetch_property_shortcodes_page_html' // Editors use the reference page.
	);

	// WordPress can add a parent-page shortcut; keep settings out of editors' menus.
	if ( ! $can_manage_settings ) {
		remove_submenu_page( 'rentfetch-options', 'rentfetch-options' );
	}
}
add_action( 'admin_menu', 'rentfetch_options_page' );
