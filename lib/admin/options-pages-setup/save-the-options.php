<?php
/**
 * This file saves everything from the Rent Fetch options page.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Save the form data for ALL tabs on the Rent Fetch settings page
 */
function rentfetch_process_form_data() {

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_main_options_nonce_action' ) ) {
		die( 'Security check failed' );
	}

	// * Save the settings
	do_action( 'rentfetch_save_settings' );

	// * Redirect back to the current page with a success message
	$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';

	// remove the URL from the referrer.
	$referrer = preg_replace( '/https?:\/\/[^\/]+/', '', $referrer );

	// remove /wp-admin/ from the referrer.
	$referrer = preg_replace( '/\/wp-admin\//', '', $referrer );

	wp_safe_redirect( add_query_arg( 'rentfetch_message', 'success', $referrer ) );

	exit;
}
add_action( 'admin_post_rentfetch_process_form', 'rentfetch_process_form_data' );
