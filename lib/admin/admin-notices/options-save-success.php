<?php
/**
 * This file displays a success message when the rentfetch_message parameter is set to 'success'.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * If the rentfetch_message parameter is set to 'success', display a success message.
 */
function rentfetch_options_page_notice() {
	if ( isset( $_GET['rentfetch_message'] ) && 'success' === $_GET['rentfetch_message'] ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Rent Fetch settings successfully saved.', 'rent-fetch' ); ?></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'rentfetch_options_page_notice' );