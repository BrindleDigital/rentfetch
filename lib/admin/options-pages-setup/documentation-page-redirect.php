<?php
/**
 * This file forces the documentation link to open in a new tab.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Provide a documentation link when the menu's JavaScript redirect is unavailable.
 */
function rentfetch_documentation_page_html() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	echo '<div class="wrap">';
	echo '<h1>Rent Fetch Documentation</h1>';
	echo '<p><a href="https://rentfetch.io/docs/getting-started/" target="_blank" rel="noopener noreferrer">Open the Rent Fetch documentation</a></p>';
	echo '</div>';
}

/**
 * Force the documentation link to go to a third-party URL.
 */
function rentfetch_documentation_submenu_open_new_tab() {
	wp_enqueue_script( 'rentfetch-options-documentation-submenu' );
}
add_action( 'admin_footer', 'rentfetch_documentation_submenu_open_new_tab' );
