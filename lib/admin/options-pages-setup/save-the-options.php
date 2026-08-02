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
	$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
	if ( 'POST' !== strtoupper( $request_method ) ) {
		wp_die(
			esc_html__( 'Rent Fetch settings must be submitted with a POST request.', 'rentfetch' ),
			'',
			array( 'response' => 405 )
		);
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			esc_html__( 'You are not allowed to manage Rent Fetch settings.', 'rentfetch' ),
			'',
			array( 'response' => 403 )
		);
	}

	check_admin_referer( 'rentfetch_main_options_nonce_action', 'rentfetch_main_options_nonce_field' );

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
