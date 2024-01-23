<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Save the form data for ALL tabs on the Rent Fetch settings page
 */
function rentfetch_process_form_data() {
	
	//* Verify the nonce
	if ( ! wp_verify_nonce( $_POST['rentfetch_form_nonce'], 'rentfetch_nonce' ) ) {
		die( 'Security check failed' );
	}
	
	//* Save the settings
	do_action( 'rentfetch_save_settings' );
	
	//* Redirect back to the form page with a success message
	// wp_redirect( add_query_arg( 'rentfetch_message', 'success', 'admin.php?page=rentfetch-options' ) );
		
	//* Redirect back to the current page with a success message
	$referrer = $_SERVER['HTTP_REFERER'];
	
	// remove the URL from the referrer
	$referrer = preg_replace('/https?:\/\/[^\/]+/', '', $referrer);
	
	// remove /wp-admin/ from the referrer
	$referrer = preg_replace('/\/wp-admin\//', '', $referrer);
		
	wp_redirect( add_query_arg( 'rentfetch_message', 'success', $referrer ) );
	
	exit;

}
add_action( 'admin_post_rentfetch_process_form', 'rentfetch_process_form_data' );