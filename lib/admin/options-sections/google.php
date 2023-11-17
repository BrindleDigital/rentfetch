<?php

/**
 * Adds the Google settings section to the Rent Fetch settings page
 */
function rent_fetch_settings_google() {    
	?>
	
	<div class="row">
		<div class="column">
			<label for="options_google_maps_api_key">Google Maps API Key</label>
		</div>
		<div class="column">
			<input type="text" name="options_google_maps_api_key" id="options_google_maps_api_key" value="<?php echo esc_attr( get_option( 'options_google_maps_api_key' ) ); ?>">
			<p class="description">Required for Google Maps.</p>
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<label for="options_google_map_marker">Google Maps Marker</label>
		</div>
		<div class="column">
			<input type="text" name="options_google_map_marker" id="options_google_map_marker" value="<?php echo esc_attr( get_option( 'options_google_map_marker' ) ); ?>">
			<p class="description">URL to a custom marker image. Leave blank to use the default marker.</p>
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<label for="options_google_maps_styles">Google Maps Styles</label>
		</div>
		<div class="column">
			<textarea name="options_google_maps_styles" id="options_google_maps_styles" rows="10" style="width: 100%;"><?php echo esc_attr( get_option( 'options_google_maps_styles' ) ); ?></textarea>
			<p class="description">JSON array of Google Maps styles. See <a href="https://snazzymaps.com/" target="_blank">Snazzy Maps</a> for examples.</p>
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<label>Google Maps default location</label>
			<p class="description">This serves as the map center in the event of a search with no results.</p>
		</div>
		<div class="column">
			<div class="white-box">
				<label for="options_google_maps_default_latitude">Latitude</label>
				<input type="text" name="options_google_maps_default_latitude" id="options_google_maps_default_latitude" value="<?php echo esc_attr( get_option( 'options_google_maps_default_latitude' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="options_google_maps_default_longitude">Longitude</label>
				<input type="text" name="options_google_maps_default_longitude" id="options_google_maps_default_longitude" value="<?php echo esc_attr( get_option( 'options_google_maps_default_longitude' ) ); ?>">
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<label>Google reCAPTCHA v2</label>
		</div>
		<div class="column">
			<div class="white-box">
				<label for="options_google_recaptcha_google_recaptcha_v2_site_key">reCAPTCHA key</label>
				<input type="text" name="options_google_recaptcha_google_recaptcha_v2_site_key" id="options_google_recaptcha_google_recaptcha_v2_site_key" value="<?php echo esc_attr( get_option( 'options_google_recaptcha_google_recaptcha_v2_site_key' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="options_google_recaptcha_google_recaptcha_v2_secret">reCAPTCHA key</label>
				<input type="text" name="options_google_recaptcha_google_recaptcha_v2_secret" id="options_google_recaptcha_google_recaptcha_v2_secret" value="<?php echo esc_attr( get_option( 'options_google_recaptcha_google_recaptcha_v2_secret' ) ); ?>">
			</div>
		</div>
	</div>
		   
	<?php
}
add_action( 'rent_fetch_do_settings_google', 'rent_fetch_settings_google' );

/**
 * Save the Google settings
 */
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_google' );
function rent_fetch_save_settings_google() {
	
	// Get the tab and section
	$tab = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();
	
	if ( $tab !== 'google' || !empty( $section ) )
		return;
		
	// Text field
	if ( isset( $_POST['options_google_maps_api_key'] ) ) {
		$options_google_maps_api_key = sanitize_text_field( $_POST['options_google_maps_api_key'] );
		update_option( 'options_google_maps_api_key', $options_google_maps_api_key );
	}
	
	// Text field
	if ( isset( $_POST['options_google_geocoding_api_key'] ) ) {
		$options_google_geocoding_api_key = sanitize_text_field( $_POST['options_google_geocoding_api_key'] );
		update_option( 'options_google_geocoding_api_key', $options_google_geocoding_api_key );
	}
	
	// Text field
	if ( isset( $_POST['options_google_map_marker'] ) ) {
		$options_google_map_marker = sanitize_text_field( $_POST['options_google_map_marker'] );
		update_option( 'options_google_map_marker', $options_google_map_marker );
	}
	
	// Textarea field
	if ( isset( $_POST['options_google_maps_styles'] ) ) {
		$options_google_maps_styles = sanitize_text_field( $_POST['options_google_maps_styles'] );
		update_option( 'options_google_maps_styles', $options_google_maps_styles );
	}
	
	// Text field
	if ( isset( $_POST['options_google_maps_default_latitude'] ) ) {
		$options_google_maps_default_latitude = sanitize_text_field( $_POST['options_google_maps_default_latitude'] );
		update_option( 'options_google_maps_default_latitude', $options_google_maps_default_latitude );
	}
	
	// Text field
	if ( isset( $_POST['options_google_maps_default_longitude'] ) ) {
		$options_google_maps_default_longitude = sanitize_text_field( $_POST['options_google_maps_default_longitude'] );
		update_option( 'options_google_maps_default_longitude', $options_google_maps_default_longitude );
	}
	
	// Text field
	if ( isset( $_POST['options_google_recaptcha_google_recaptcha_v2_site_key'] ) ) {
		$options_google_recaptcha_google_recaptcha_v2_site_key = sanitize_text_field( $_POST['options_google_recaptcha_google_recaptcha_v2_site_key'] );
		update_option( 'options_google_recaptcha_google_recaptcha_v2_site_key', $options_google_recaptcha_google_recaptcha_v2_site_key );
	}
	
	// Text field
	if ( isset( $_POST['options_google_recaptcha_google_recaptcha_v2_secret'] ) ) {
		$options_google_recaptcha_google_recaptcha_v2_secret = sanitize_text_field( $_POST['options_google_recaptcha_google_recaptcha_v2_secret'] );
		update_option( 'options_google_recaptcha_google_recaptcha_v2_secret', $options_google_recaptcha_google_recaptcha_v2_secret );
	}
	
}
