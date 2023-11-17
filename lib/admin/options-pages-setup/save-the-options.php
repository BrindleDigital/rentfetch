<?php

/**
 * Save the form data for ALL tabs on the Rent Fetch settings page
 */
add_action( 'admin_post_rent_fetch_process_form', 'rent_fetch_process_form_data' );
function rent_fetch_process_form_data() {
	
	//* Verify the nonce
	if ( ! wp_verify_nonce( $_POST['rent_fetch_form_nonce'], 'rent_fetch_nonce' ) ) {
		die( 'Security check failed' );
	}
	
	//* Save the settings
	do_action( 'rent_fetch_save_settings' );
	
	//* Redirect back to the form page with a success message
	// wp_redirect( add_query_arg( 'rent_fetch_message', 'success', 'admin.php?page=rent_fetch_options' ) );
		
	//* Redirect back to the current page with a success message
	$referrer = $_SERVER['HTTP_REFERER'];
	
	// remove the URL from the referrer
	$referrer = preg_replace('/https?:\/\/[^\/]+/', '', $referrer);
	
	// remove /wp-admin/ from the referrer
	$referrer = preg_replace('/\/wp-admin\//', '', $referrer);
		
	wp_redirect( add_query_arg( 'rent_fetch_message', 'success', $referrer ) );
	
	exit;

}