<?php
/**
 * This file includes the options for the maps
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set defaults on activation
 */
function rentfetch_settings_set_defaults_maps() {

	// Add options if they don't exist with default values (Denver, CO).
	add_option( 'rentfetch_options_google_maps_default_latitude', 39.7392 );
	add_option( 'rentfetch_options_google_maps_default_longitude', 104.9903 );
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_maps' );

/**
 * Adds the Maps settings section to the Rent Fetch settings page
 */
function rentfetch_settings_maps() {
	echo '<section id="rent-fetch-maps-page" class="options-container">';
	echo '<div class="container">';
	?>
	
	<div class="row">
		<div class="section">
			<label for="rentfetch_options_google_maps_api_key">Google Maps API Key</label>
		</div>
		<div class="section">
			<input type="text" name="rentfetch_options_google_maps_api_key" id="rentfetch_options_google_maps_api_key" value="<?php echo esc_attr( get_option( 'rentfetch_options_google_maps_api_key' ) ); ?>">
			<p class="description">Required for Google Maps.</p>
		</div>
	</div>
	
	<div class="row">
		<div class="section">
			<label for="rentfetch_options_google_map_marker">Google Maps Marker</label>
		</div>
		<div class="section">
			<input type="url" name="rentfetch_options_google_map_marker" id="rentfetch_options_google_map_marker" value="<?php echo esc_attr( get_option( 'rentfetch_options_google_map_marker' ) ); ?>">
			<p class="description">URL to a custom marker image. Leave blank to use the default marker.</p>
		</div>
	</div>
	
	<div class="row">
		<div class="section">
			<label for="rentfetch_options_google_maps_styles">Google Maps Styles</label>
		</div>
		<div class="section">
			<textarea name="rentfetch_options_google_maps_styles" id="rentfetch_options_google_maps_styles" rows="10" style="width: 100%;"><?php echo esc_attr( get_option( 'rentfetch_options_google_maps_styles' ) ); ?></textarea>
			<p class="description">JSON array of Google Maps styles. See <a href="https://snazzymaps.com/" target="_blank">Snazzy Maps</a> for examples.</p>
		</div>
	</div>
	
	<div class="row">
		<div class="section">
			<label>Google Maps default location</label>
			<p class="description">This serves as the map center in the event of a search with no results.</p>
		</div>
		<div class="section">
			<div class="white-box">
				<label for="rentfetch_options_google_maps_default_latitude">Latitude</label>
				<input type="text" name="rentfetch_options_google_maps_default_latitude" id="rentfetch_options_google_maps_default_latitude" value="<?php echo esc_attr( get_option( 'rentfetch_options_google_maps_default_latitude' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_google_maps_default_longitude">Longitude</label>
				<input type="text" name="rentfetch_options_google_maps_default_longitude" id="rentfetch_options_google_maps_default_longitude" value="<?php echo esc_attr( get_option( 'rentfetch_options_google_maps_default_longitude' ) ); ?>">
			</div>
		</div>
	</div>
	<?php
	
	submit_button();
	
	echo '</div><!-- .container -->';
	echo '</section><!-- #rent-fetch-maps-page -->';
}
add_action( 'rentfetch_do_settings_maps', 'rentfetch_settings_maps' );

/**
 * Save the Google settings
 */
function rentfetch_save_settings_maps() {

	// Get the tab and section.
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ( 'maps' !== $tab || ! empty( $section ) ) {
		return;
	}

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce.
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_main_options_nonce_action' ) ) {
		die( 'Security check failed' );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_google_maps_api_key'] ) ) {
		$options_google_maps_api_key = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_google_maps_api_key'] ) );
		update_option( 'rentfetch_options_google_maps_api_key', $options_google_maps_api_key );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_google_geocoding_api_key'] ) ) {
		$options_google_geocoding_api_key = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_google_geocoding_api_key'] ) );
		update_option( 'rentfetch_options_google_geocoding_api_key', $options_google_geocoding_api_key );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_google_map_marker'] ) ) {
		$options_google_map_marker = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_google_map_marker'] ) );
		update_option( 'rentfetch_options_google_map_marker', $options_google_map_marker );
	}

	// Textarea field.
	if ( isset( $_POST['rentfetch_options_google_maps_styles'] ) ) {
		$options_google_maps_styles = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_google_maps_styles'] ) );
		update_option( 'rentfetch_options_google_maps_styles', $options_google_maps_styles );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_google_maps_default_latitude'] ) ) {
		$options_google_maps_default_latitude = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_google_maps_default_latitude'] ) );
		update_option( 'rentfetch_options_google_maps_default_latitude', $options_google_maps_default_latitude );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_google_maps_default_longitude'] ) ) {
		$options_google_maps_default_longitude = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_google_maps_default_longitude'] ) );
		update_option( 'rentfetch_options_google_maps_default_longitude', $options_google_maps_default_longitude );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_google_recaptcha_google_recaptcha_v2_site_key'] ) ) {
		$options_google_recaptcha_google_recaptcha_v2_site_key = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_google_recaptcha_google_recaptcha_v2_site_key'] ) );
		update_option( 'rentfetch_options_google_recaptcha_google_recaptcha_v2_site_key', $options_google_recaptcha_google_recaptcha_v2_site_key );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_google_recaptcha_google_recaptcha_v2_secret'] ) ) {
		$options_google_recaptcha_google_recaptcha_v2_secret = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_google_recaptcha_google_recaptcha_v2_secret'] ) );
		update_option( 'rentfetch_options_google_recaptcha_google_recaptcha_v2_secret', $options_google_recaptcha_google_recaptcha_v2_secret );
	}
}
add_action( 'rentfetch_save_settings', 'rentfetch_save_settings_maps' );