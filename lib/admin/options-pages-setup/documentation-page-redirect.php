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
 * Force the documentation link to go to a third-party URL.
 */
function rentfetch_documentation_submenu_open_new_tab() {
	wp_enqueue_script( 'rentfetch-options-documentation-submenu' );
	
}
add_action( 'admin_footer', 'rentfetch_documentation_submenu_open_new_tab' );