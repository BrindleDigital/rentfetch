<?php
/**
 * This file includes the options for the properties search
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Set defaults on activation
 */
function rentfetch_settings_set_defaults_properties() {

	// Add option if it doesn't exist.
	add_option( 'rentfetch_options_maximum_number_of_properties_to_show', -1 );
	add_option( 'rentfetch_options_property_availability_display', 'all' );

	$defaultarray = array(
		'text_based_search',
		'beds_search',
		'price_search',
	);
	add_option( 'rentfetch_options_featured_filters', $defaultarray );

	add_option( 'rentfetch_options_disable_school_year_date_range', '0' );

	$defaultarray = array(
		'text_based_search',
		'beds_search',
		'baths_search',
		'squarefoot_search',
		'type_search',
		'category_search',
		'floorplan_type_search',
		'floorplan_category_search',
		'date_search',
		'price_search',
		'amenities_search',

	);
	add_option( 'rentfetch_options_dialog_filters', $defaultarray );

	add_option( 'rentfetch_options_number_of_amenities_to_show', 20 );
	add_option( 'rentfetch_options_maximum_bedrooms_to_search', 99 );

	// Maps API default options.
	add_option( 'rentfetch_options_google_maps_default_latitude', 39.7392 );
	add_option( 'rentfetch_options_google_maps_default_longitude', -104.9903 );

	// Global property fees defaults.
	add_option( 'rentfetch_options_global_property_fees_data', array() );
	add_option( 'rentfetch_options_global_property_fees_json_url', '' );
	add_option( 'rentfetch_options_global_property_fees_embed', '' );
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_properties' );

/**
 * Adds the property search settings subsection to the Rent Fetch settings page
 */
function rentfetch_settings_properties_property_maps() {
	?>
	<div class="header">
		<h2 class="title">Maps API</h2>
		<p class="description">The settings configured for a multi-property website showcasing properties alongside Google
			Maps.</p>
	</div>
	<div class="rows">
		<div class="row">
		<div class="section">
			<label for="rentfetch_options_google_maps_api_key">Google Maps API Key</label>
		</div>
		<div class="section">
			<input type="text" name="rentfetch_options_google_maps_api_key" id="rentfetch_options_google_maps_api_key"
				value="<?php echo esc_attr( get_option( 'rentfetch_options_google_maps_api_key' ) ); ?>">
				<p class="description">Required for Google Maps.</p>
			</div>
		</div>
		
		<div class="row">
			<div class="section">
				<label for="rentfetch_options_google_map_marker">Google Maps Marker</label>
			</div>
			<div class="section">
				<input type="url" name="rentfetch_options_google_map_marker" id="rentfetch_options_google_map_marker"
					value="<?php echo esc_attr( get_option( 'rentfetch_options_google_map_marker' ) ); ?>">
				<p class="description">URL to a custom marker image. Leave blank to use the default marker.</p>
			</div>
		</div>
		
		<div class="row">
			<div class="section">
				<label for="rentfetch_options_google_maps_styles">Google Maps Styles</label>
			</div>
			<div class="section">
				<textarea name="rentfetch_options_google_maps_styles" id="rentfetch_options_google_maps_styles" rows="10"
					style="width: 100%;"><?php echo esc_attr( get_option( 'rentfetch_options_google_maps_styles' ) ); ?></textarea>
				<p class="description">JSON array of Google Maps styles. See <a href="https://snazzymaps.com/"
						target="_blank">Snazzy Maps</a> for examples.</p>
			</div>
		</div>
		
		<div class="row">
			<div class="section">
				<label>Google Maps default location</label>
				<p class="description">This serves as the map center in the event of a search with no results.</p>
			</div>
			
			<br />

			<div class="section">
				<label for="rentfetch_options_google_maps_default_latitude">Latitude</label>
				<input type="text" name="rentfetch_options_google_maps_default_latitude"
					id="rentfetch_options_google_maps_default_latitude"
					value="<?php echo esc_attr( get_option( 'rentfetch_options_google_maps_default_latitude' ) ); ?>"
				/>

				<br />
				<br />

				<label for="rentfetch_options_google_maps_default_longitude">Longitude</label>
				<input type="text" name="rentfetch_options_google_maps_default_longitude"
					id="rentfetch_options_google_maps_default_longitude"
					value="<?php echo esc_attr( get_option( 'rentfetch_options_google_maps_default_longitude' ) ); ?>"
				/>
			</div>
		</div>
	</div>

	<?php
}
add_action( 'rentfetch_do_settings_properties_property_maps', 'rentfetch_settings_properties_property_maps' );

/**
 * Output property search settings
 */
function rentfetch_settings_properties_property_search_settings() {
	?>
	<div class="header">
		<h2 class="title">Property Search</h2>
		<p class="description">The settings configured for the property search capabilities on a multi-property website.</p>
	</div>

	<div class="row">
		<div class="section">
			<label for="" class="label-large">Search filters</label>
		</div>
		<div class="separator"></div>
		<div class="section">
			<label class="label-large" for="rentfetch_options_featured_filters">Featured property filters</label>
			<p class="description">Which components should be shown by default?</p>
			<?php

			// Get saved options.
			$options_featured_filters = get_option( 'rentfetch_options_featured_filters' );
			if ( ! is_array( $options_featured_filters ) ) {
				$options_featured_filters = array();
			}

			?>
			<ul class="checkboxes">
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="text_based_search" <?php checked( in_array( 'text_based_search', $options_featured_filters, true ) ); ?>>
						Text-based search (this works best with the Relevanssi plugin enhancing your search)
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="beds_search" <?php checked( in_array( 'beds_search', $options_featured_filters, true ) ); ?>>
						Beds search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="baths_search" <?php checked( in_array( 'baths_search', $options_featured_filters, true ) ); ?>>
						Baths search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="squarefoot_search" <?php checked( in_array( 'squarefoot_search', $options_featured_filters, true ) ); ?>>
						Square footage search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="category_search" <?php checked( in_array( 'category_search', $options_featured_filters, true ) ); ?>>
						Property category search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="city_search" <?php checked( in_array( 'city_search', $options_featured_filters, true ) ); ?>>
						City search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="type_search" <?php checked( in_array( 'type_search', $options_featured_filters, true ) ); ?>>
						Property type search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="date_search" <?php checked( in_array( 'date_search', $options_featured_filters, true ) ); ?>>
						Date search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="price_search" <?php checked( in_array( 'price_search', $options_featured_filters, true ) ); ?>>
						Price search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="amenities_search" <?php checked( in_array( 'amenities_search', $options_featured_filters, true ) ); ?>>
						Amenities search
					</label>
				</li>
			</ul>
		</div>
		<div class="separator"></div>
		<div class="section">
			<ul class="checkboxes">
				<li>
					<label for="rentfetch_options_disable_school_year_date_range">
						<input type="checkbox" name="rentfetch_options_disable_school_year_date_range" id="rentfetch_options_disable_school_year_date_range" value="1" <?php checked( '1', get_option( 'rentfetch_options_disable_school_year_date_range' ) ); ?>>
						Disable school-year based date range searches
					</label>
				</li>
			</ul>
		</div>
		<div class="separator"></div>
		<div class="section">
			<label class="label-large" for="rentfetch_options_dialog_filters">Additional Search Filters</label>
			<p class="description">Which components should be shown in the filters lightbox? (Please note that you cannot enable "Featured property filters" above without having at LEAST the same ones selected here).</p>
			<?php

			// Get saved options.
			$options_dialog_filters = get_option( 'rentfetch_options_dialog_filters' );

			// Make it an array just in case it isn't (for example, if it's a new install).
			if ( ! is_array( $options_dialog_filters ) ) {
				$options_dialog_filters = array();
			}

			?>
			<ul class="checkboxes">
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="text_based_search" <?php checked( in_array( 'text_based_search', $options_dialog_filters, true ) ); ?>>
						Text-based search (this works best with the Relevanssi plugin enhancing your search)
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="beds_search" <?php checked( in_array( 'beds_search', $options_dialog_filters, true ) ); ?>>
						Beds search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="baths_search" <?php checked( in_array( 'baths_search', $options_dialog_filters, true ) ); ?>>
						Baths search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="squarefoot_search" <?php checked( in_array( 'squarefoot_search', $options_dialog_filters, true ) ); ?>>
						Square footage search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="category_search" <?php checked( in_array( 'category_search', $options_dialog_filters, true ) ); ?>>
						Property category search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="type_search" <?php checked( in_array( 'type_search', $options_dialog_filters, true ) ); ?>>
						Property type search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="city_search" <?php checked( in_array( 'city_search', $options_dialog_filters, true ) ); ?>>
						City search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="date_search" <?php checked( in_array( 'date_search', $options_dialog_filters, true ) ); ?>>
						Date search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="price_search" <?php checked( in_array( 'price_search', $options_dialog_filters, true ) ); ?>>
						Price search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="amenities_search" <?php checked( in_array( 'amenities_search', $options_dialog_filters, true ) ); ?>>
						Amenities search
					</label>
				</li>
			</ul>
		</div>
	</div>

	<div class="row">
		<div class="section">
			<label class="label-large" for="rentfetch_options_maximum_number_of_properties_to_show">Search Display Settings</label>
		</div>
		<div class="separator"></div>
		<div class="section">
			<label>Property Availability Display</label>
			<ul class="radios">
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_availability_display"
							id="rentfetch_options_property_availability_display" value="all" <?php checked( get_option( 'rentfetch_options_property_availability_display' ), 'all' ); ?>>
						Show all properties (ignoring availability)
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_availability_display"
							id="rentfetch_options_property_availability_display" value="available" <?php checked( get_option( 'rentfetch_options_property_availability_display' ), 'available' ); ?>>
						Only show properties with availability
					</label>
				</li>
			</ul>
		</div>
		<div class="separator"></div>
		<div class="section">
			<ul class="checkboxes">
				<li>
					<label for="rentfetch_options_properties_hide_number_of_units">
						<input type="checkbox" name="rentfetch_options_properties_hide_number_of_units" id="rentfetch_options_properties_hide_number_of_units" <?php checked( get_option( 'rentfetch_options_properties_hide_number_of_units' ), '1' ); ?>>
						Hide the number of units
					</label>
				</li>
				<li>
					<label for="rentfetch_options_property_apply_styles_no_floorplans">
						<input type="checkbox" name="rentfetch_options_property_apply_styles_no_floorplans" id="rentfetch_options_property_apply_styles_no_floorplans" <?php checked( get_option( 'rentfetch_options_property_apply_styles_no_floorplans' ), '1' ); ?>>
						Apply faded styles to properties without availability
					</label>
				</li>
			</ul>
		</div>
		<div class="separator"></div>
		<div class="section">
			<label for="rentfetch_options_maximum_bedrooms_to_search">Max Bedrooms To Search</label>
			<input type="text" name="rentfetch_options_maximum_bedrooms_to_search"
				id="rentfetch_options_maximum_bedrooms_to_search"
				value="<?php echo esc_attr( get_option( 'rentfetch_options_maximum_bedrooms_to_search' ) ); ?>">
		</div>
		<div class="section">
			<label for="rentfetch_options_number_of_amenities_to_show">Max Number of amenities to show</label>
			<input type="text" name="rentfetch_options_number_of_amenities_to_show"
				id="rentfetch_options_number_of_amenities_to_show"
				value="<?php echo esc_attr( get_option( 'rentfetch_options_number_of_amenities_to_show' ) ); ?>">
		</div>
		<div class="section">
			<label for="rentfetch_options_maximum_number_of_properties_to_show">Maximum Number of Properties Displayed</label>
			<p class="description">
				The most properties we should attempt to show while matching a search. We recommend for performance reasons that
				this number is not set above ~200 properties.
			</p>
			<input type="text" name="rentfetch_options_maximum_number_of_properties_to_show"
				id="rentfetch_options_maximum_number_of_properties_to_show"
				value="<?php echo esc_attr( get_option( 'rentfetch_options_maximum_number_of_properties_to_show' ) ); ?>">
		</div>
	</div>

	<div class="row">
		<div class="section">
			<label class="label-large" for="rentfetch_options_price_filter_minimum">Price filter</label>
			<p class="description">
				Set a pre-determined minimum and maximum price filter to be displayed. If left empty, a user can type in any
				desired price range.
			</p>
		</div>
		<div class="section">
			<div class="columns columns-3">
				<div>
					<label for="rentfetch_options_price_filter_minimum">Price filter minimum</label>
					<input type="text" name="rentfetch_options_price_filter_minimum" id="rentfetch_options_price_filter_minimum"
						value="<?php echo esc_attr( get_option( 'rentfetch_options_price_filter_minimum' ) ); ?>">
				</div>
				<div>
					<label for="rentfetch_options_price_filter_maximum">Price filter maximum</label>
					<input type="text" name="rentfetch_options_price_filter_maximum" id="rentfetch_options_price_filter_maximum"
						value="<?php echo esc_attr( get_option( 'rentfetch_options_price_filter_maximum' ) ); ?>">
				</div>
				<div>
					<label for="rentfetch_options_price_filter_step">Price filter step</label>
					<input type="text" name="rentfetch_options_price_filter_step" id="rentfetch_options_price_filter_step"
						value="<?php echo esc_attr( get_option( 'rentfetch_options_price_filter_step' ) ); ?>">
				</div>
			</div>
		</div>

	</div>
	<?php
}
add_action( 'rentfetch_do_settings_properties_property_search', 'rentfetch_settings_properties_property_search_settings' );

/**
 * Save the property search settings
 */
function rentfetch_save_settings_property_search() {
	// Get the tab and section.
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ( 'properties' !== $tab || 'property-search' !== $section ) {
		return;
	}

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_main_options_nonce_action' ) ) {
		die( 'Security check failed' );
	}

	// Number field.
	if ( isset( $_POST['rentfetch_options_maximum_number_of_properties_to_show'] ) ) {
		$max_properties = intval( $_POST['rentfetch_options_maximum_number_of_properties_to_show'] );
		update_option( 'rentfetch_options_maximum_number_of_properties_to_show', $max_properties );
	}

	// Select field.
	if ( isset( $_POST['rentfetch_options_property_availability_display'] ) ) {
		$property_display = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_property_availability_display'] ) );
		update_option( 'rentfetch_options_property_availability_display', $property_display );
	}

	// Checkboxes field - Featured filters.
	if ( isset( $_POST['rentfetch_options_featured_filters'] ) ) {
		$options_featured_filters = array_map( 'sanitize_text_field', wp_unslash( $_POST['rentfetch_options_featured_filters'] ) );
	} else {
		$options_featured_filters = array();
	}
	update_option( 'rentfetch_options_featured_filters', $options_featured_filters );

	// Checkboxes field - Dialog filters (must include all featured filters).
	if ( isset( $_POST['rentfetch_options_dialog_filters'] ) ) {
		$options_dialog_filters = array_map( 'sanitize_text_field', wp_unslash( $_POST['rentfetch_options_dialog_filters'] ) );
	} else {
		$options_dialog_filters = array();
	}
	// Ensure dialog filters include all featured filters.
	$options_dialog_filters = array_unique( array_merge( $options_dialog_filters, $options_featured_filters ) );
	update_option( 'rentfetch_options_dialog_filters', $options_dialog_filters );

	// Checkbox field.
	if ( isset( $_POST['rentfetch_options_disable_school_year_date_range'] ) ) {
		update_option( 'rentfetch_options_disable_school_year_date_range', '1' );
	} else {
		update_option( 'rentfetch_options_disable_school_year_date_range', '0' );
	}

	// Number field.
	if ( isset( $_POST['rentfetch_options_maximum_bedrooms_to_search'] ) ) {
		$max_bedrooms = intval( $_POST['rentfetch_options_maximum_bedrooms_to_search'] );
		update_option( 'rentfetch_options_maximum_bedrooms_to_search', $max_bedrooms );
	}

	// Number field.
	if ( isset( $_POST['rentfetch_options_price_filter_minimum'] ) ) {
		$price_filter_minimum = intval( $_POST['rentfetch_options_price_filter_minimum'] );
		update_option( 'rentfetch_options_price_filter_minimum', $price_filter_minimum );
	} else {
		$price_filter_minimum = null;
		update_option( 'rentfetch_options_price_filter_minimum', $price_filter_minimum );
	}

	// Number field.
	if ( isset( $_POST['rentfetch_options_price_filter_maximum'] ) ) {
		$price_filter_maximum = intval( $_POST['rentfetch_options_price_filter_maximum'] );
		update_option( 'rentfetch_options_price_filter_maximum', $price_filter_maximum );
	}

	// Number field.
	if ( isset( $_POST['rentfetch_options_price_filter_step'] ) ) {
		$price_filter_step = intval( $_POST['rentfetch_options_price_filter_step'] );
		update_option( 'rentfetch_options_price_filter_step', $price_filter_step );
	}

	// Number field.
	if ( isset( $_POST['rentfetch_options_number_of_amenities_to_show'] ) ) {
		$number_of_amenities_to_show = intval( $_POST['rentfetch_options_number_of_amenities_to_show'] );
		update_option( 'rentfetch_options_number_of_amenities_to_show', $number_of_amenities_to_show );
	}

	// Checkbox field - Enable the availability button.
	$hide_number_of_units = isset( $_POST['rentfetch_options_properties_hide_number_of_units'] ) ? '1' : '0';
	update_option( 'rentfetch_options_properties_hide_number_of_units', $hide_number_of_units );
	
	// Checkbox field - Fade properties without availability.
	$hide_number_of_units = isset( $_POST['rentfetch_options_property_apply_styles_no_floorplans'] ) ? '1' : '0';
	update_option( 'rentfetch_options_property_apply_styles_no_floorplans', $hide_number_of_units );
}
add_action( 'rentfetch_save_settings', 'rentfetch_save_settings_property_search' );

/**
 * Save the Google settings
 */
function rentfetch_save_settings_maps() {
	// Get the tab and section.
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ( 'properties' !== $tab || 'property-maps' !== $section ) {
		return;
	}

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce
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

/**
 * Adds the global property fees settings subsection to the Rent Fetch settings page
 */
function rentfetch_settings_properties_global_property_fees() {
	?>
	<div class="header">
		<h2 class="title">Property Fees</h2>
		<p class="description">Set global fallback fees that will be used when property-specific fees are not available (you can set individual fees when editing each property as well).</p>
	</div>
	<div class="row">
		<div class="section">
			<label for="rentfetch_options_global_property_fees_csv">OPTION 1: CSV Upload or Link</label>
			<p class="description">Upload or link to a CSV file with global property fees data.</p>
			<div class="csv-input-group" style="display: flex; align-items: center; gap: 0; margin-bottom: 10px;">
				<input type="file" id="rentfetch_options_global_property_fees_csv" name="rentfetch_options_global_property_fees_csv" accept=".csv" style="display: none;" />
				<label for="rentfetch_options_global_property_fees_csv" style="display: inline-block; padding: 14px 14px; background: #f7f7f7; border: 1px solid #8c8f94; border-radius: 4px 0 0 4px; cursor: pointer; font-size: 13px; margin: 0; white-space: nowrap; min-width: 0; width: auto;">Choose File</label>
				<input type="url" id="rentfetch_options_global_property_fees_csv_url" name="rentfetch_options_global_property_fees_csv_url" value="<?php echo esc_attr( get_option( 'rentfetch_options_global_property_fees_csv_url' ) ); ?>" placeholder="or paste in a link to a .csv file" style="flex: 1; border-left: none; border-radius: 0 4px 4px 0;" />
			</div>
			<p class="description"><a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=rentfetch_download_global_fees_csv_sample' ) ); ?>" download="global_property_fees_sample.csv">Download sample CSV</a>
				<?php $csv_url = get_option( 'rentfetch_options_global_property_fees_csv_url' ); ?>
				<?php if ( ! empty( $csv_url ) ) : ?>
					or <a href="#" id="download-global-current-fees">download current data</a>
				<?php endif; ?>
			</p>
		</div>
	</div>
	
	<div class="row">
		<div class="section">
			<label for="rentfetch_options_global_property_fees_embed">OPTION 2: Embed Code</label>
			<p class="description">This option allows you to add a PDF embed via Canva or similar.</p>
			<textarea name="rentfetch_options_global_property_fees_embed" id="rentfetch_options_global_property_fees_embed" rows="5" style="width:100%;"><?php echo esc_textarea( get_option( 'rentfetch_options_global_property_fees_embed' ) ); ?></textarea>
			<p class="description">Paste in your embed code for property fees. This can include script tags, iframes, etc. Please ensure the code is from a trusted source.</p>
		</div>
	</div>
	
	<?php
}
add_action( 'rentfetch_do_settings_properties_global_property_fees', 'rentfetch_settings_properties_global_property_fees' );

/**
 * Save the global property fees settings
 */
function rentfetch_save_settings_global_property_fees() {
	// Get the tab and section.
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ( 'properties' !== $tab || 'global-property-fees' !== $section ) {
		return;
	}

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_main_options_nonce_action' ) ) {
		die( 'Security check failed' );
	}

	// JSON data
	if ( isset( $_POST['rentfetch_options_global_property_fees_data'] ) ) {
		$json_data = sanitize_textarea_field( wp_unslash( $_POST['rentfetch_options_global_property_fees_data'] ) );
		$decoded = json_decode( $json_data, true );
		if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
			update_option( 'rentfetch_options_global_property_fees_data', $decoded );
		} else {
			update_option( 'rentfetch_options_global_property_fees_data', array() );
		}
	}

	// CSV URL
	if ( isset( $_POST['rentfetch_options_global_property_fees_csv_url'] ) ) {
		$url = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_global_property_fees_csv_url'] ) );
		update_option( 'rentfetch_options_global_property_fees_csv_url', $url );
	}

	// Embed code
	if ( isset( $_POST['rentfetch_options_global_property_fees_embed'] ) ) {
		$embed = sanitize_textarea_field( wp_unslash( $_POST['rentfetch_options_global_property_fees_embed'] ) );
		update_option( 'rentfetch_options_global_property_fees_embed', $embed );
	}
}
add_action( 'rentfetch_save_settings', 'rentfetch_save_settings_global_property_fees' );

/**
 * Enqueue scripts for global property fees options page
 */
function rentfetch_enqueue_global_property_fees_scripts( $hook ) {
	// Only load on admin.php page
	if ( 'toplevel_page_rentfetch-options' !== $hook ) {
		return;
	}

	// Check if we're on the properties tab and global-property-fees section
	if ( isset( $_GET['tab'] ) && 'properties' === $_GET['tab'] && isset( $_GET['section'] ) && 'global-property-fees' === $_GET['section'] ) {
		// Ensure wp.codeEditor is available and get settings so WP can enqueue required addons.
		$settings = wp_enqueue_code_editor( array( 'type' => 'application/json' ) );

		// Fallback settings if enqueue didn't return anything.
		if ( false === $settings ) {
			$settings = array(
				'codemirror' => array(
					'mode' => 'application/json',
				),
			);
		}

		// Enqueue the script handle registered in lib/initialization/enqueue.php and localize the settings.
		wp_enqueue_script( 'rentfetch-api-response-editor' );

		// Make the settings available to our script so it uses the same assets/addons WP enqueued.
		wp_localize_script( 'rentfetch-api-response-editor', 'rentfetchCodeEditorSettings', $settings );
		
		// Enqueue JSON handling script
		wp_enqueue_script( 'rentfetch-properties-fees-json-handling', plugins_url( 'js/rentfetch-properties-fees-json-handling.js', dirname( __FILE__, 3 ) ), array( 'rentfetch-api-response-editor' ), '1.0.0', true );
		
		// Localize settings for the JSON handling script as well
		wp_localize_script( 'rentfetch-properties-fees-json-handling', 'rentfetchCodeEditorSettings', $settings );
		
		// Enqueue CSV upload script for global property fees
		wp_enqueue_script( 'rentfetch-global-properties-fees-csv-upload', plugins_url( 'js/rentfetch-global-properties-fees-csv-upload.js', dirname( __FILE__, 3 ) ), array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'rentfetch-global-properties-fees-csv-download-current', plugins_url( 'js/rentfetch-global-properties-fees-csv-download-current.js', dirname( __FILE__, 3 ) ), array( 'jquery' ), '1.0.0', true );
	}
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_global_property_fees_scripts' );
