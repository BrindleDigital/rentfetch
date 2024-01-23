<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * If the rentfetch_message parameter is set to 'success', display a success message.
 */
function rentfetch_options_page_notice() {
	if ( isset( $_GET['rentfetch_message'] ) && $_GET['rentfetch_message'] === 'success' ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Rent Fetch settings successfully saved.', 'rent-fetch' ); ?></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'rentfetch_options_page_notice' );