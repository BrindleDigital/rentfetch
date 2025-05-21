<?php
/**
 * This file includes the options for the single properties template
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set defaults on activation.
 *
 * @return  void.
 */
function rentfetch_settings_set_defaults_properties_propertiessingle() {

	$default_options = array(
		'property_images',
		'section_navigation',
		'property_details',
		'floorplans_display',
		'amenities_display',
	);
	add_option( 'rentfetch_options_single_property_components', $default_options );
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_properties_propertiessingle' );

/**
 * Adds the properties single settings subsection to the Rent Fetch settings page.
 *
 * @return  void.
 */
function rentfetch_settings_properties_property_single() {
	?>
	
	<div class="row">
		<div class="section">
			<label class="label-large" for="rentfetch_options_single_property_components">Single Property Components</label>
			<p class="description">These settings control which default components of the page display. Please note that theme developers can also customize this display in several other ways. Each individual section can be replaced by removing the corresponding action, or you can simply add a single-properties.php file to the root of your theme.</p>
			<p class="description">Please note that each individual section will only display if there's enough information to meaningfully display it. A property with no images set will not output a blank "images" section, for example.</p>
			<?php

			// Get saved options.
			$options_single_property_components = get_option( 'rentfetch_options_single_property_components' );

			// Make it an array just in case it isn't (for example, if it's a new install).
			if ( ! is_array( $options_single_property_components ) ) {
				$options_single_property_components = array();
			}

			?>
			<ul class="checkboxes">
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_single_property_components[]" value="property_images" <?php checked( in_array( 'property_images', $options_single_property_components, true ) ); ?>>
						Property images
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_single_property_components[]" value="section_navigation" <?php checked( in_array( 'section_navigation', $options_single_property_components, true ) ); ?>>
						Section navigation
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_single_property_components[]" value="property_details" <?php checked( in_array( 'property_details', $options_single_property_components, true ) ); ?>>
						Property details 
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_single_property_components[]" value="floorplans_display" <?php checked( in_array( 'floorplans_display', $options_single_property_components, true ) ); ?>>
						Floorplan display
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_single_property_components[]" value="amenities_display" <?php checked( in_array( 'amenities_display', $options_single_property_components, true ) ); ?>>
						Amenities display
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_single_property_components[]" value="property_map" <?php checked( in_array( 'property_map', $options_single_property_components, true ) ); ?>>
						Property map
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_single_property_components[]" value="nearby_properties" <?php checked( in_array( 'nearby_properties', $options_single_property_components, true ) ); ?>>
						Nearby properties
					</label>
				</li>
			</ul>
		</div>
	</div>
	<?php
}
add_action( 'rentfetch_do_settings_properties_property_single', 'rentfetch_settings_properties_property_single' );

/**
 * Save the single property settings
 *
 * @return  void.
 */
function rentfetch_save_settings_property_single() {

	// Get the tab and section.
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ( 'properties' !== $tab || 'property-single' !== $section ) {
		return;
	}

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce.
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_main_options_nonce_action' ) ) {
		die( 'Security check failed' );
	}

	// Checkboxes field.
	if ( isset( $_POST['rentfetch_options_single_property_components'] ) ) {
		$enabled_integrations = array_map( 'sanitize_text_field', wp_unslash( $_POST['rentfetch_options_single_property_components'] ) );
		update_option( 'rentfetch_options_single_property_components', $enabled_integrations );
	} else {
		update_option( 'rentfetch_options_single_property_components', array() );
	}
}
add_action( 'rentfetch_save_settings', 'rentfetch_save_settings_property_single' );