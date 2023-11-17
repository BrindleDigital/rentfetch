<?php


/**
 * Adds the properties single settings subsection to the Rent Fetch settings page
 */
function rent_fetch_settings_properties_property_single() {
	?>
	
	<div class="row">
		<div class="column">
			<label for="options_single_property_components">Single property components</label>
			<p class="description">These settings control which default components of the page display. Please note that theme developers can also customize this display in several other ways. Each individual section can be replaced by removing the corresponding action, or you can simply add a single-properties.php file to the root of your theme.</p>
			<p class="description">Please note that each individual section will only display if there's enough information to meaningfully display it. A property with no images set will not output a blank "images" section, for example.</p>
		</div>
		<div class="column">
			<?php
			
			// Get saved options
			$options_single_property_components = get_option('options_single_property_components');
			
			// Define default values
			$default_options = array(
				'enable_property_title',
				'enable_property_images',
				'enable_basic_info_display',
				'enable_property_description',
				'enable_floorplans_display',
				'enable_amenities_display',
				'enable_lease_details_display',
				'enable_property_map',
				'enable_nearby_properties',
			);
			
			// Make it an array just in case it isn't (for example, if it's a new install)
			if (!is_array($options_single_property_components)) {
				$options_single_property_components = $default_options;
			}
						
			?>
			<ul class="checkboxes">
				<li>
					<label>
						<input type="checkbox" name="options_single_property_components[]" value="property_images" <?php checked( in_array( 'property_images', $options_single_property_components ) ); ?>>
						Enable property images
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="options_single_property_components[]" value="section_navigation" <?php checked( in_array( 'section_navigation', $options_single_property_components ) ); ?>>
						Enable section navigation
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="options_single_property_components[]" value="property_details" <?php checked( in_array( 'property_details', $options_single_property_components ) ); ?>>
						Enable property details 
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="options_single_property_components[]" value="floorplans_display" <?php checked( in_array( 'floorplans_display', $options_single_property_components ) ); ?>>
						Enable floorplan display
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="options_single_property_components[]" value="amenities_display" <?php checked( in_array( 'amenities_display', $options_single_property_components ) ); ?>>
						Enable amenities display
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="options_single_property_components[]" value="property_map" <?php checked( in_array( 'property_map', $options_single_property_components ) ); ?>>
						Enable property map
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="options_single_property_components[]" value="nearby_properties" <?php checked( in_array( 'nearby_properties', $options_single_property_components ) ); ?>>
						Enable nearby properties
					</label>
				</li>
			</ul>
		</div>
	</div>
	<?php
}
add_action( 'rent_fetch_do_settings_properties_property_single', 'rent_fetch_settings_properties_property_single' );

/**
 * Save the property single settings
 */
function rent_fetch_save_settings_property_single() {
	
	// Get the tab and section
	$tab = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();
	
	if ( $tab !== 'properties' || $section !== 'property_single' )
		return;
	
	// Checkboxes field
	if ( isset ( $_POST['options_single_property_components'] ) ) {
		$enabled_integrations = array_map('sanitize_text_field', $_POST['options_single_property_components']);
		update_option('options_single_property_components', $enabled_integrations);
	} else {
		update_option('options_single_property_components', array());
	}
}
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_property_single' );