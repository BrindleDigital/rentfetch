<?php
/**
 * This file includes the options for the general section
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set defaults on activation
 */
function rentfetch_settings_set_defaults_general() {

	// Add option if it doesn't exist.
	add_option( 'rentfetch_options_data_sync', 'nosync' );
	add_option( 'rentfetch_options_sync_timeline', '3600' );
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_general' );

/**
 * Adds the general settings section to the Rent Fetch settings page.
 */
function rentfetch_settings_general() {

	// Silence is golden.
}
add_action( 'rentfetch_do_settings_general', 'rentfetch_settings_general' );

/**
 * Add the notice about the sync functionality (this will be removed by the sync plugin if it's installed)
 *
 * @return void
 */
function rentfetch_settings_sync_functionality_notice() {
	echo '<section id="rent-fetch-general-page" class="options-container">';
	?>
	<div class="row">
		<div class="section">
			<label for="">Data Automation</label>
		</div>
		<div class="section">
			<div class="white-box">
				<h2 style="margin-top: 0;">Our premium availability syncing addon</h3>
				<p class="description">You can already manually enter data for as many properties, floorplans, and units as you'd like, and all layouts are enabled for this information.</p><p>However, if you'd like to automate the addition of properties and sync availability information hourly, we offer the <strong>Rent Fetch Sync</strong> addon to sync data with the Yardi/RentCafe, Realpage, Appfolio, and Entrata platforms. More information at <a href="https://rentfetch.io" target="_blank">rentfetch.io</a></p>
			</div>
		</div>
	</div>
	<?php
	echo '</section><!-- #rent-fetch-general-page -->';
}
add_action( 'rentfetch_do_settings_general', 'rentfetch_settings_sync_functionality_notice', 25 );

/**
 * Save the general settings
 */
function rentfetch_save_settings_general() {

	// Get the tab and section.
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	// this particular settings page has no tab or section, and it's the only one that doesn't.
	if ( $tab || $section ) {
		return;
	}

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_main_options_nonce_action' ) ) {
		die( 'Security check failed' );
	}

	// * When we save this particular batch of settings, we want to re-check the license
	delete_transient( 'rentfetchsync_properties_limit' );

	// * When we save this particular batch of settings, we might be changing the sync settings, so we need to unschedule all the sync actions
	if ( function_exists( 'as_unschedule_all_actions' ) ) {
		as_unschedule_all_actions( 'rfs_do_sync' );
		as_unschedule_all_actions( 'rfs_yardi_do_delete_orphans' );
	}

	// Radio field.
	if ( isset( $_POST['rentfetch_options_data_sync'] ) ) {
		$options_data_sync = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_data_sync'] ) );
		update_option( 'rentfetch_options_data_sync', $options_data_sync );
	}

	// Select field.
	if ( isset( $_POST['rentfetch_options_sync_timeline'] ) ) {
		$property_display = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_sync_timeline'] ) );
		update_option( 'rentfetch_options_sync_timeline', $property_display );
	}

	// Checkboxes field.
	if ( isset( $_POST['rentfetch_options_enabled_integrations'] ) ) {
		$enabled_integrations = array_map( 'sanitize_text_field', wp_unslash( $_POST['rentfetch_options_enabled_integrations'] ) );
		update_option( 'rentfetch_options_enabled_integrations', $enabled_integrations );
	} else {
		update_option( 'rentfetch_options_enabled_integrations', array() );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_api_key'] ) ) {
		$options_yardi_integration_creds_yardi_api_key = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_api_key'] ) );
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_api_key', $options_yardi_integration_creds_yardi_api_key );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_company_code'] ) ) {
		$options_yardi_integration_creds_yardi_company_code = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_company_code'] ) );
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_company_code', $options_yardi_integration_creds_yardi_company_code );
	}

	// Textarea field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_voyager_code'] ) ) {
		$options_yardi_integration_creds_yardi_voyager_code = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_voyager_code'] ) );

		// Remove all whitespace.
		$options_yardi_integration_creds_yardi_voyager_code = preg_replace( '/\s+/', '', $options_yardi_integration_creds_yardi_voyager_code );

		// Add a space after each comma.
		$options_yardi_integration_creds_yardi_voyager_code = preg_replace( '/,/', ', ', $options_yardi_integration_creds_yardi_voyager_code );

		update_option( 'rentfetch_options_yardi_integration_creds_yardi_voyager_code', $options_yardi_integration_creds_yardi_voyager_code );
	}

	// Textarea field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_property_code'] ) ) {
		$options_yardi_integration_creds_yardi_property_code = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_property_code'] ) );

		// Remove all whitespace.
		$options_yardi_integration_creds_yardi_property_code = preg_replace( '/\s+/', '', $options_yardi_integration_creds_yardi_property_code );

		// Add a space after each comma.
		$options_yardi_integration_creds_yardi_property_code = preg_replace( '/,/', ', ', $options_yardi_integration_creds_yardi_property_code );

		update_option( 'rentfetch_options_yardi_integration_creds_yardi_property_code', $options_yardi_integration_creds_yardi_property_code );
	}

	// Single checkbox field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_enable_yardi_api_lead_generation'] ) ) {
		$options_yardi_integration_creds_enable_yardi_api_lead_generation = true;
	} else {
		$options_yardi_integration_creds_enable_yardi_api_lead_generation = false;
	}
	update_option( 'rentfetch_options_yardi_integration_creds_enable_yardi_api_lead_generation', $options_yardi_integration_creds_enable_yardi_api_lead_generation );

	// Text field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_username'] ) ) {
		$options_yardi_integration_creds_yardi_username = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_username'] ) );
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_username', $options_yardi_integration_creds_yardi_username );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_password'] ) ) {
		$options_yardi_integration_creds_yardi_password = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_password'] ) );
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_password', $options_yardi_integration_creds_yardi_password );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_entrata_integration_creds_entrata_subdomain'] ) ) {
		$options_entrata_integration_creds_entrata_subdomain = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_entrata_integration_creds_entrata_subdomain'] ) );
		update_option( 'rentfetch_options_entrata_integration_creds_entrata_subdomain', $options_entrata_integration_creds_entrata_subdomain );
	}

	// Textarea field.
	if ( isset( $_POST['rentfetch_options_entrata_integration_creds_entrata_property_ids'] ) ) {
		$options_entrata_integration_creds_entrata_property_ids = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_entrata_integration_creds_entrata_property_ids'] ) );

		// Remove all whitespace.
		$options_entrata_integration_creds_entrata_property_ids = preg_replace( '/\s+/', '', $options_entrata_integration_creds_entrata_property_ids );

		// Add a space after each comma.
		$options_entrata_integration_creds_entrata_property_ids = preg_replace( '/,/', ', ', $options_entrata_integration_creds_entrata_property_ids );

		update_option( 'rentfetch_options_entrata_integration_creds_entrata_property_ids', $options_entrata_integration_creds_entrata_property_ids );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_realpage_integration_creds_realpage_user'] ) ) {
		$options_realpage_integration_creds_realpage_user = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_realpage_integration_creds_realpage_user'] ) );
		update_option( 'rentfetch_options_realpage_integration_creds_realpage_user', $options_realpage_integration_creds_realpage_user );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_realpage_integration_creds_realpage_pass'] ) ) {
		$options_realpage_integration_creds_realpage_pass = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_realpage_integration_creds_realpage_pass'] ) );
		update_option( 'rentfetch_options_realpage_integration_creds_realpage_pass', $options_realpage_integration_creds_realpage_pass );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_realpage_integration_creds_realpage_pmc_id'] ) ) {
		$options_realpage_integration_creds_realpage_pmc_id = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_realpage_integration_creds_realpage_pmc_id'] ) );
		update_option( 'rentfetch_options_realpage_integration_creds_realpage_pmc_id', $options_realpage_integration_creds_realpage_pmc_id );
	}

	// Textarea field.
	if ( isset( $_POST['rentfetch_options_realpage_integration_creds_realpage_site_ids'] ) ) {
		$options_realpage_integration_creds_realpage_site_ids = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_realpage_integration_creds_realpage_site_ids'] ) );

		// Remove all whitespace.
		$options_realpage_integration_creds_realpage_site_ids = preg_replace( '/\s+/', '', $options_realpage_integration_creds_realpage_site_ids );

		// Add a space after each comma.
		$options_realpage_integration_creds_realpage_site_ids = preg_replace( '/,/', ', ', $options_realpage_integration_creds_realpage_site_ids );

		update_option( 'rentfetch_options_realpage_integration_creds_realpage_site_ids', $options_realpage_integration_creds_realpage_site_ids );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_rentmanager_integration_creds_rentmanager_companycode'] ) ) {
		// Remove ".api.rentmanager.com" and anything that follows it.
		$input_value   = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_rentmanager_integration_creds_rentmanager_companycode'] ) );
		$cleaned_value = preg_replace( '/\.api\.rentmanager\.com.*/', '', $input_value );

		update_option( 'rentfetch_options_rentmanager_integration_creds_rentmanager_companycode', $cleaned_value );
	}

	if ( function_exists( 'rfs_get_rentmanager_properties_from_setting' ) ) {
		// this function is defined in the rentfetch-sync plugin, and allows for prefilling the properties for Rent Manager, where there are multiple locations possible.
		// and it's not feasible to have the user enter them all manually.
		rfs_get_rentmanager_properties_from_setting();
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_database_name'] ) ) {
		$options_appfolio_integration_creds_appfolio_database_name = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_database_name'] ) );

		// Remove .appfolio.com from the end of the database name.
		$options_appfolio_integration_creds_appfolio_database_name = preg_replace( '/.appfolio.com/', '', $options_appfolio_integration_creds_appfolio_database_name );

		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_database_name', $options_appfolio_integration_creds_appfolio_database_name );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_client_id'] ) ) {
		$options_appfolio_integration_creds_appfolio_client_id = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_client_id'] ) );
		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_client_id', $options_appfolio_integration_creds_appfolio_client_id );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_client_secret'] ) ) {
		$options_appfolio_integration_creds_appfolio_client_secret = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_client_secret'] ) );
		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_client_secret', $options_appfolio_integration_creds_appfolio_client_secret );
	}

	// Textarea field.
	if ( isset( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_property_ids'] ) ) {
		$options_appfolio_integration_creds_appfolio_property_ids = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_property_ids'] ) );

		// Remove all whitespace.
		$options_appfolio_integration_creds_appfolio_property_ids = preg_replace( '/\s+/', '', $options_appfolio_integration_creds_appfolio_property_ids );

		// Add a space after each comma.
		$options_appfolio_integration_creds_appfolio_property_ids = preg_replace( '/,/', ', ', $options_appfolio_integration_creds_appfolio_property_ids );

		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_property_ids', $options_appfolio_integration_creds_appfolio_property_ids );
	}

	// * When we save this particular batch of settings, we want to always clear the transient that holds the API info.
	delete_transient( 'rentfetch_api_info' );
}
add_action( 'rentfetch_save_settings', 'rentfetch_save_settings_general' );