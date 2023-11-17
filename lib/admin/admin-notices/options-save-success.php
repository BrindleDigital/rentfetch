<?php

/**
 * If the rent_fetch_message parameter is set to 'success', display a success message.
 */
add_action( 'admin_notices', 'rent_fetch_options_page_notice' );
function rent_fetch_options_page_notice() {
    if ( isset( $_GET['rent_fetch_message'] ) && $_GET['rent_fetch_message'] === 'success' ) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'Rent Fetch settings successfully saved.', 'rent-fetch' ); ?></p>
        </div>
        <?php
    }
}
