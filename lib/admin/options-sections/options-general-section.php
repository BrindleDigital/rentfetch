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
add_action( 'rent_fetch_do_settings_general', 'rent_fetch_settings_general' );
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
	
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_data_sync">Data Sync</label>
			<p class="description">When you start syncing from data from your management software, it generally takes 5-15 seconds per property to sync. <strong>Rome wasn't built in a day.</strong></p>
		</div>
		<div class="column">
			<ul class="radio">
				<li>
					<label>
						<input type="radio" name="rentfetch_options_data_sync" id="rentfetch_options_data_sync" value="nosync" <?php checked( get_option( 'rentfetch_options_data_sync' ), 'nosync' ); ?>>
						Pause all syncing from all APIs
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_data_sync" id="rentfetch_options_data_sync" value="updatesync" <?php checked( get_option( 'rentfetch_options_data_sync' ), 'updatesync' ); ?>>
						Update data on this site with data from the API. This option should never modify manually-added properties/floorplans, nor should it overwrite any custom data you've added to otherwise synced properties/floorplans.
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_data_sync" id="rentfetch_options_data_sync" value="delete" <?php checked( get_option( 'rentfetch_options_data_sync' ), 'delete' ); ?>>
						<span style="color: red;">Delete all data that's been pulled from a third-party API. <strong style="color: white; background-color: red; padding: 3px 5px; border-radius: 3px;">This will take place immediately upon saving. There is no undo.</strong></span>
					</label>
				</li>
			</ul>
		</div>
	</div>
	
	<!-- <div class="row">
		<div class="column">
			<label for="rentfetch_options_sync_term">Sync Term</label>
		</div>
		<div class="column">
			<p class="description">If you're seeing repeated API failures, pausing this temporarily can often clean up zombie tasks that apply to properties no longer listed in your API settings. It can also serve to reset overdue tasks in the event that API functionality was unavailable for a period (for example, on a staging site behind basic authentication).</p>
			<select name="rentfetch_options_sync_term" id="rentfetch_options_sync_term" value="<?php echo esc_attr( get_option( 'rentfetch_options_sync_term' ) ); ?>">
				<option value="paused" <?php selected( get_option( 'rentfetch_options_sync_term' ), 'paused' ); ?>>Paused</option>
				<option value="hourly" <?php selected( get_option( 'rentfetch_options_sync_term' ), 'hourly' ); ?>>Hourly</option>
			</select>
		</div>
	</div> -->
	
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_enabled_integrations">Enabled Integrations</label>
		</div>
		<div class="column">
			<script type="text/javascript">
				jQuery(document).ready(function( $ ) {
	
					$( '.integration' ).hide();
					
					// on load and on change of input[name="rentfetch_options_enabled_integrations[]"], show/hide the integration options
					$( 'input[name="rentfetch_options_enabled_integrations[]"]' ).on( 'change', function() {
						
						// hide all the integration options
						$( '.integration' ).hide();
						
						// show the integration options for the checked integrations
						$( 'input[name="rentfetch_options_enabled_integrations[]"]:checked' ).each( function() {
							$( '.integration.' + $( this ).val() ).show();
						});
						
					}).trigger( 'change' );
					
				});
				
			</script>
			<ul class="checkboxes">
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_enabled_integrations[]" value="yardi" <?php checked( in_array( 'yardi', get_option( 'rentfetch_options_enabled_integrations', array() ) ) ); ?>>
						Yardi/RentCafe
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_enabled_integrations[]" value="entrata" <?php checked( in_array( 'entrata', get_option( 'rentfetch_options_enabled_integrations', array() ) ) ); ?>>
						Entrata
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_enabled_integrations[]" value="realpage" <?php checked( in_array( 'realpage', get_option( 'rentfetch_options_enabled_integrations', array() ) ) ); ?>>
						RealPage
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_enabled_integrations[]" value="appfolio" <?php checked( in_array( 'appfolio', get_option( 'rentfetch_options_enabled_integrations', array() ) ) ); ?>>
						Appfolio
					</label>
				</li>
			</ul>
		</div>
	</div>
	
	<div class="row integration yardi">
		<div class="column">
			<label>Yardi/RentCafe</label>
		</div>
		<div class="column">
			<div class="white-box">
				<label for="rentfetch_options_yardi_integration_creds_yardi_api_key">Yardi API Key</label>
				<input type="text" name="rentfetch_options_yardi_integration_creds_yardi_api_key" id="rentfetch_options_yardi_integration_creds_yardi_api_key" value="<?php echo esc_attr( get_option( 'rentfetch_options_yardi_integration_creds_yardi_api_key' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_yardi_integration_creds_yardi_voyager_code">Yardi Voyager Codes</label>
				<textarea rows="10" style="width: 100%;" name="rentfetch_options_yardi_integration_creds_yardi_voyager_code" id="rentfetch_options_yardi_integration_creds_yardi_voyager_code"><?php echo esc_attr( get_option( 'rentfetch_options_yardi_integration_creds_yardi_voyager_code' ) ); ?></textarea>
				<p class="description">Multiple property codes should be entered separated by commas</p>
			</div>
			<div class="white-box">
				<label for="rentfetch_options_yardi_integration_creds_yardi_property_code">Yardi Property Codes</label>
				<textarea rows="10" style="width: 100%;" name="rentfetch_options_yardi_integration_creds_yardi_property_code" id="rentfetch_options_yardi_integration_creds_yardi_property_code"><?php echo esc_attr( get_option( 'rentfetch_options_yardi_integration_creds_yardi_property_code' ) ); ?></textarea>
				<p class="description">Multiple property codes should be entered separated by commas</p>
			</div>
			<!-- <div class="white-box">
				<label for="rentfetch_options_yardi_integration_creds_enable_yardi_api_lead_generation">
					<input type="checkbox" name="rentfetch_options_yardi_integration_creds_enable_yardi_api_lead_generation" id="rentfetch_options_yardi_integration_creds_enable_yardi_api_lead_generation" <?php checked( get_option( 'rentfetch_options_yardi_integration_creds_enable_yardi_api_lead_generation' ), true ); ?>>
					Enable Yardi API Lead Generation
				</label>
				<p class="description">Adds a lightbox form on the single properties template which can send leads directly to the Yardi API.</p>
			</div> -->
			<div class="white-box">
				<label for="rentfetch_options_yardi_integration_creds_yardi_username">Yardi Username</label>
				<input type="text" name="rentfetch_options_yardi_integration_creds_yardi_username" id="rentfetch_options_yardi_integration_creds_yardi_username" value="<?php echo esc_attr( get_option( 'rentfetch_options_yardi_integration_creds_yardi_username' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_yardi_integration_creds_yardi_password">Yardi Password</label>
				<input type="text" name="rentfetch_options_yardi_integration_creds_yardi_password" id="rentfetch_options_yardi_integration_creds_yardi_password" value="<?php echo esc_attr( get_option( 'rentfetch_options_yardi_integration_creds_yardi_password' ) ); ?>">
			</div>
		</div>
	</div>
	
	<div class="row integration entrata">
		<div class="column">
			<label>Entrata</label>
		</div>
		<div class="column">
			<div class="white-box">
				<label for="rentfetch_options_entrata_integration_creds_entrata_user">Entrata Username</label>
				<input type="text" name="rentfetch_options_entrata_integration_creds_entrata_user" id="rentfetch_options_entrata_integration_creds_entrata_user" value="<?php echo esc_attr( get_option( 'rentfetch_options_entrata_integration_creds_entrata_user' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_entrata_integration_creds_entrata_pass">Entrata Password</label>
				<input type="text" name="rentfetch_options_entrata_integration_creds_entrata_pass" id="rentfetch_options_entrata_integration_creds_entrata_pass" value="<?php echo esc_attr( get_option( 'rentfetch_options_entrata_integration_creds_entrata_pass' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_entrata_integration_creds_entrata_property_ids">Entrata Property IDs</label>
				<textarea rows="10" style="width: 100%;" name="rentfetch_options_entrata_integration_creds_entrata_property_ids" id="rentfetch_options_entrata_integration_creds_entrata_property_ids"><?php echo esc_attr( get_option( 'rentfetch_options_entrata_integration_creds_entrata_property_ids' ) ); ?></textarea>
				<p class="description">If there are multiple properties to be pulled in, enter those separated by commas</p>
			</div>
		</div>
	</div>
	
	<div class="row integration realpage">
		<div class="column">
			<label>RealPage</label>
		</div>
		<div class="column">
			<div class="white-box">
				<label for="rentfetch_options_realpage_integration_creds_realpage_user">RealPage Username</label>
				<input type="text" name="rentfetch_options_realpage_integration_creds_realpage_user" id="rentfetch_options_realpage_integration_creds_realpage_user" value="<?php echo esc_attr( get_option( 'rentfetch_options_realpage_integration_creds_realpage_user' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_realpage_integration_creds_realpage_pass">RealPage Password</label>
				<input type="text" name="rentfetch_options_realpage_integration_creds_realpage_pass" id="rentfetch_options_realpage_integration_creds_realpage_pass" value="<?php echo esc_attr( get_option( 'rentfetch_options_realpage_integration_creds_realpage_pass' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_realpage_integration_creds_realpage_pmc_id">RealPage PMC ID</label>
				<input type="text" name="rentfetch_options_realpage_integration_creds_realpage_pmc_id" id="rentfetch_options_realpage_integration_creds_realpage_pmc_id" value="<?php echo esc_attr( get_option( 'rentfetch_options_realpage_integration_creds_realpage_pmc_id' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_realpage_integration_creds_realpage_site_ids">RealPage Site IDs</label>
				<textarea rows="10" style="width: 100%;" name="rentfetch_options_realpage_integration_creds_realpage_site_ids" id="rentfetch_options_realpage_integration_creds_realpage_site_ids"><?php echo esc_attr( get_option( 'rentfetch_options_realpage_integration_creds_realpage_site_ids' ) ); ?></textarea>
				<p class="description">If there are multiple properties to be pulled in, enter those separated by commas</p>
			</div>
		</div>
	</div>
	
	<div class="row integration appfolio">
		<div class="column">
			<label>AppFolio</label>
		</div>
		<div class="column">
			<div class="white-box">
				<label for="rentfetch_options_appfolio_integration_creds_appfolio_database_name">Appfolio Database Name</label>
				<input type="text" name="rentfetch_options_appfolio_integration_creds_appfolio_database_name" id="rentfetch_options_appfolio_integration_creds_appfolio_database_name" value="<?php echo esc_attr( get_option( 'rentfetch_options_appfolio_integration_creds_appfolio_database_name' ) ); ?>">
				<p class="description">Typically this is xxxxxxxxxxx.appfolio.com</p>
			</div>
			<div class="white-box">
				<label for="rentfetch_options_appfolio_integration_creds_appfolio_client_id">Appfolio Client ID</label>
				<input type="text" name="rentfetch_options_appfolio_integration_creds_appfolio_client_id" id="rentfetch_options_appfolio_integration_creds_appfolio_client_id" value="<?php echo esc_attr( get_option( 'rentfetch_options_appfolio_integration_creds_appfolio_client_id' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_appfolio_integration_creds_appfolio_client_secret">Appfolio Client Secret</label>
				<input type="text" name="rentfetch_options_appfolio_integration_creds_appfolio_client_secret" id="rentfetch_options_appfolio_integration_creds_appfolio_client_secret" value="<?php echo esc_attr( get_option( 'rentfetch_options_appfolio_integration_creds_appfolio_client_secret' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_appfolio_integration_creds_appfolio_property_ids">Appfolio Property IDs</label>
				<textarea rows="10" style="width: 100%;" name="rentfetch_options_appfolio_integration_creds_appfolio_property_ids" id="rentfetch_options_appfolio_integration_creds_appfolio_property_ids"><?php echo esc_attr( get_option( 'rentfetch_options_appfolio_integration_creds_appfolio_property_ids' ) ); ?></textarea>
				<p class="description">For AppFolio, this is an optional field. If left blank, Rent Fetch will simply fetch all of the properties in the account, which may or not be your preference. Please note that if property IDs are present here, all *other* synced properties through AppFolio will be deleted when the site next syncs.</p>
			</div>
		</div>
	</div>
		 
   
		 
	<?php
}

/**
 * Save the general settings
 */
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_general' );
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