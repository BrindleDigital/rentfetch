<?php

/**
 * Set defaults on activation
 */
function rentfetch_settings_set_defaults_general() {
    
    // Add option if it doesn't exist
    add_option( 'rentfetch_options_apartment_site_type', 'multiple' );
    add_option( 'rentfetch_options_data_sync', 'nosync' );
    
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_general' );

/**
 * Adds the general settings section to the Rent Fetch settings page.
 */
function rent_fetch_settings_general() {    
	?>
	
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_apartment_site_type">Site type</label>
		</div>
		<div class="column">
			<select name="rentfetch_options_apartment_site_type" id="rentfetch_options_apartment_site_type" value="<?php echo esc_attr( get_option( 'rentfetch_options_apartment_site_type' ) ); ?>">
				<option value="single" <?php selected( get_option( 'rentfetch_options_apartment_site_type' ), 'single' ); ?>>This site is for a single property</option>
				<option value="multiple" <?php selected( get_option( 'rentfetch_options_apartment_site_type' ), 'multiple' ); ?>>This site is for multiple properties</option>
			</select>
		</div>
	</div>
	<?php
}
add_action( 'rent_fetch_do_settings_general', 'rent_fetch_settings_general' );

function rent_fetch_settings_sync_functionality_notice() {
	?>
	<div class="row">
		<div class="column">
			<label for="">Data Automation</label>
		</div>
		<div class="column">
			<div class="white-box" style="max-width: 400px;">
				<h2 style="margin-top: 0;">Our premium availability syncing addon</h3>
				<p class="description">You can already manually enter data for as many properties, floorplans, and units as you'd like, and all layouts are enabled for this information.</p><p>However, if you'd like to automate the addition of properties and sync availability information hourly, we offer the <strong>Rent Fetch Sync</strong> addon to sync data with Yardi/RentCafe, Realpage, Appfolio, and Entrata platforms. More information at <a href="https://rentfetch.io" target="_blank">rentfetch.io</a></p>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'rent_fetch_do_settings_general', 'rent_fetch_settings_sync_functionality_notice', 25 );

/**
 * Save the general settings
 */
function rent_fetch_save_settings_general() {
	
	// Get the tab and section
	$tab = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();
	
	// this particular settings page has no tab or section, and it's the only one that doesn't
	if ( $tab || $section )
		return;
	
	// Select field
	if ( isset( $_POST[ 'rentfetch_options_apartment_site_type']) ) {
		$options_apartment_site_type = sanitize_text_field( $_POST[ 'rentfetch_options_apartment_site_type'] );
		update_option( 'rentfetch_options_apartment_site_type', $options_apartment_site_type );
	}
	
	// Radio field
	if ( isset( $_POST[ 'rentfetch_options_data_sync'] ) ) {
		$options_data_sync = sanitize_text_field( $_POST[ 'rentfetch_options_data_sync'] );
		update_option( 'rentfetch_options_data_sync', $options_data_sync );
	}
	
	// Select field
	// if ( isset( $_POST[ 'rentfetch_options_sync_term'] ) ) {
	//     $options_sync_term = sanitize_text_field( $_POST[ 'rentfetch_options_sync_term'] );
	//     update_option( 'rentfetch_options_sync_term', $options_sync_term );
	// }
	
	// Checkboxes field
	if ( isset ( $_POST[ 'rentfetch_options_enabled_integrations'] ) ) {
		$enabled_integrations = array_map('sanitize_text_field', $_POST[ 'rentfetch_options_enabled_integrations']);
		update_option( 'rentfetch_options_enabled_integrations', $enabled_integrations);
	} else {
		update_option( 'rentfetch_options_enabled_integrations', array());
	}
	
	// Text field
	if ( isset( $_POST[ 'rentfetch_options_yardi_integration_creds_yardi_api_key'] ) ) {
		$options_yardi_integration_creds_yardi_api_key = sanitize_text_field( $_POST[ 'rentfetch_options_yardi_integration_creds_yardi_api_key'] );
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_api_key', $options_yardi_integration_creds_yardi_api_key );
	}
	
	// Textarea field
	if ( isset( $_POST[ 'rentfetch_options_yardi_integration_creds_yardi_voyager_code'] ) ) {
		$options_yardi_integration_creds_yardi_voyager_code = sanitize_text_field( $_POST[ 'rentfetch_options_yardi_integration_creds_yardi_voyager_code'] );
		
		// Remove all whitespace
		$options_yardi_integration_creds_yardi_voyager_code = preg_replace('/\s+/', '', $options_yardi_integration_creds_yardi_voyager_code);
		
		// Add a space after each comma
		$options_yardi_integration_creds_yardi_voyager_code = preg_replace('/,/', ', ', $options_yardi_integration_creds_yardi_voyager_code);
		
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_voyager_code', $options_yardi_integration_creds_yardi_voyager_code );
	}
	
	// Textarea field
	if ( isset( $_POST[ 'rentfetch_options_yardi_integration_creds_yardi_property_code'] ) ) {
		$options_yardi_integration_creds_yardi_property_code = sanitize_text_field( $_POST[ 'rentfetch_options_yardi_integration_creds_yardi_property_code'] );
		
		// Remove all whitespace
		$options_yardi_integration_creds_yardi_property_code = preg_replace('/\s+/', '', $options_yardi_integration_creds_yardi_property_code);
		
		// Add a space after each comma
		$options_yardi_integration_creds_yardi_property_code = preg_replace('/,/', ', ', $options_yardi_integration_creds_yardi_property_code);
		
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_property_code', $options_yardi_integration_creds_yardi_property_code );
	}
	
	// Single checkbox field
	if ( isset( $_POST[ 'rentfetch_options_yardi_integration_creds_enable_yardi_api_lead_generation'] ) ) {
		$options_yardi_integration_creds_enable_yardi_api_lead_generation = true;
	} else {
		$options_yardi_integration_creds_enable_yardi_api_lead_generation = false;
	}
	update_option( 'rentfetch_options_yardi_integration_creds_enable_yardi_api_lead_generation', $options_yardi_integration_creds_enable_yardi_api_lead_generation );

	
	// Text field
	if ( isset( $_POST[ 'rentfetch_options_yardi_integration_creds_yardi_username'] ) ) {
		$options_yardi_integration_creds_yardi_username = sanitize_text_field( $_POST[ 'rentfetch_options_yardi_integration_creds_yardi_username'] );
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_username', $options_yardi_integration_creds_yardi_username );
	}
	
	// Text field
	if ( isset( $_POST[ 'rentfetch_options_yardi_integration_creds_yardi_password'] ) ) {
		$options_yardi_integration_creds_yardi_password = sanitize_text_field( $_POST[ 'rentfetch_options_yardi_integration_creds_yardi_password'] );
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_password', $options_yardi_integration_creds_yardi_password );
	}
	
	// Text field
	if ( isset( $_POST[ 'rentfetch_options_entrata_integration_creds_entrata_user'] ) ) {
		$options_entrata_integration_creds_entrata_user = sanitize_text_field( $_POST[ 'rentfetch_options_entrata_integration_creds_entrata_user'] );
		update_option( 'rentfetch_options_entrata_integration_creds_entrata_user', $options_entrata_integration_creds_entrata_user );
	}
	
	// Text field
	if ( isset( $_POST[ 'rentfetch_options_entrata_integration_creds_entrata_pass'] ) ) {
		$options_entrata_integration_creds_entrata_pass = sanitize_text_field( $_POST[ 'rentfetch_options_entrata_integration_creds_entrata_pass'] );
		update_option( 'rentfetch_options_entrata_integration_creds_entrata_pass', $options_entrata_integration_creds_entrata_pass );
	}
	
	// Textarea field
	if ( isset( $_POST[ 'rentfetch_options_entrata_integration_creds_entrata_property_ids'] ) ) {
		$options_entrata_integration_creds_entrata_property_ids = sanitize_text_field( $_POST[ 'rentfetch_options_entrata_integration_creds_entrata_property_ids'] );
		
		// Remove all whitespace
		$options_entrata_integration_creds_entrata_property_ids = preg_replace('/\s+/', '', $options_entrata_integration_creds_entrata_property_ids);
		
		// Add a space after each comma
		$options_entrata_integration_creds_entrata_property_ids = preg_replace('/,/', ', ', $options_entrata_integration_creds_entrata_property_ids);
		
		update_option( 'rentfetch_options_entrata_integration_creds_entrata_property_ids', $options_entrata_integration_creds_entrata_property_ids );
	}
	
	// Text field
	if ( isset( $_POST[ 'rentfetch_options_realpage_integration_creds_realpage_user'] ) ) {
		$options_realpage_integration_creds_realpage_user = sanitize_text_field( $_POST[ 'rentfetch_options_realpage_integration_creds_realpage_user'] );
		update_option( 'rentfetch_options_realpage_integration_creds_realpage_user', $options_realpage_integration_creds_realpage_user );
	}
	
	// Text field
	if ( isset( $_POST[ 'rentfetch_options_realpage_integration_creds_realpage_pass'] ) ) {
		$options_realpage_integration_creds_realpage_pass = sanitize_text_field( $_POST[ 'rentfetch_options_realpage_integration_creds_realpage_pass'] );
		update_option( 'rentfetch_options_realpage_integration_creds_realpage_pass', $options_realpage_integration_creds_realpage_pass );
	}
	
	// Text field
	if ( isset( $_POST[ 'rentfetch_options_realpage_integration_creds_realpage_pmc_id'] ) ) {
		$options_realpage_integration_creds_realpage_pmc_id = sanitize_text_field( $_POST[ 'rentfetch_options_realpage_integration_creds_realpage_pmc_id'] );
		update_option( 'rentfetch_options_realpage_integration_creds_realpage_pmc_id', $options_realpage_integration_creds_realpage_pmc_id );
	}
	
	// Textarea field
	if ( isset( $_POST[ 'rentfetch_options_realpage_integration_creds_realpage_site_ids'] ) ) {
		$options_realpage_integration_creds_realpage_site_ids = sanitize_text_field( $_POST[ 'rentfetch_options_realpage_integration_creds_realpage_site_ids'] );
		
		// Remove all whitespace
		$options_realpage_integration_creds_realpage_site_ids = preg_replace('/\s+/', '', $options_realpage_integration_creds_realpage_site_ids);
		
		// Add a space after each comma
		$options_realpage_integration_creds_realpage_site_ids = preg_replace('/,/', ', ', $options_realpage_integration_creds_realpage_site_ids);
		
		update_option( 'rentfetch_options_realpage_integration_creds_realpage_site_ids', $options_realpage_integration_creds_realpage_site_ids );
	}
	
	// Text field
	if ( isset( $_POST[ 'rentfetch_options_appfolio_integration_creds_appfolio_database_name'] ) ) {
		$options_appfolio_integration_creds_appfolio_database_name = sanitize_text_field( $_POST[ 'rentfetch_options_appfolio_integration_creds_appfolio_database_name'] );
		
		// Remove .appfolio.com from the end of the database name
		$options_appfolio_integration_creds_appfolio_database_name = preg_replace('/.appfolio.com/', '', $options_appfolio_integration_creds_appfolio_database_name);
		
		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_database_name', $options_appfolio_integration_creds_appfolio_database_name );
	}
	
	// Text field
	if ( isset( $_POST[ 'rentfetch_options_appfolio_integration_creds_appfolio_client_id'] ) ) {
		$options_appfolio_integration_creds_appfolio_client_id = sanitize_text_field( $_POST[ 'rentfetch_options_appfolio_integration_creds_appfolio_client_id'] );
		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_client_id', $options_appfolio_integration_creds_appfolio_client_id );
	}
	
	// Text field
	if ( isset( $_POST[ 'rentfetch_options_appfolio_integration_creds_appfolio_client_secret'] ) ) {
		$options_appfolio_integration_creds_appfolio_client_secret = sanitize_text_field( $_POST[ 'rentfetch_options_appfolio_integration_creds_appfolio_client_secret'] );
		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_client_secret', $options_appfolio_integration_creds_appfolio_client_secret );
	}
	
	// Textarea field
	if ( isset( $_POST[ 'rentfetch_options_appfolio_integration_creds_appfolio_property_ids'] ) ) {
		$options_appfolio_integration_creds_appfolio_property_ids = sanitize_text_field( $_POST[ 'rentfetch_options_appfolio_integration_creds_appfolio_property_ids'] );
		
		// Remove all whitespace
		$options_appfolio_integration_creds_appfolio_property_ids = preg_replace('/\s+/', '', $options_appfolio_integration_creds_appfolio_property_ids);
		
		// Add a space after each comma
		$options_appfolio_integration_creds_appfolio_property_ids = preg_replace('/,/', ', ', $options_appfolio_integration_creds_appfolio_property_ids);
		
		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_property_ids', $options_appfolio_integration_creds_appfolio_property_ids );
	}
	
}
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_general' );