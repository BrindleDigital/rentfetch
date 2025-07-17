<?php
/**
 * This file includes the options for the floorplan buttons
 *
 * @package rentfetch
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Set defaults on activation
 */
function rentfetch_settings_set_defaults_floorplans_buttons()
{

	add_option('rentfetch_options_availability_button_enabled', true);
	add_option('rentfetch_options_availability_button_button_label', 'Lease now');
	add_option('rentfetch_options_contact_button_button_label', 'Contact');
	add_option('rentfetch_options_tour_button_button_label', 'Schedule a tour');
}
register_activation_hook(RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_floorplans_buttons');

/**
 * Output floorplan button settings
 */
function rentfetch_settings_floorplans_floorplan_buttons()
{

	wp_enqueue_script('rentfetch-options-floorplan-buttons');
	echo '<section id="floorplans-buttons-section">';
	?>

	<div class="row floorplan-archive-buttons availability">
		<div class="section" style="display: none;"></div>
		<div class="section">
			<div class="always-visible">
				<ul class="checkboxes">
					<li>
						<label class="label-large" for="rentfetch_options_availability_button_enabled">
							<input type="checkbox" name="rentfetch_options_availability_button_enabled" id="rentfetch_options_availability_button_enabled" <?php checked(get_option('rentfetch_options_availability_button_enabled'), '1'); ?>>
							Availability Button
						</label>
						<p class="description pt-2">A button which can pull in the availability link for each individual floorplan. This button
							will display by default whenever there's an availability URL, whether units are available or not.
						</p>
						
					</li>
				</ul>
			</div>
			<div class="white-box">
				<label for="rentfetch_options_availability_button_button_label">Button label</label>
				<input type="text" name="rentfetch_options_availability_button_button_label" id="rentfetch_options_availability_button_button_label" value="<?php echo esc_attr(get_option('rentfetch_options_availability_button_button_label', 'Lease now')); ?>">
				<ul class="checkboxes" style="margin-top: 20px;">
					<li>
						<label for="rentfetch_options_availability_button_enabled_hide_when_unavailable">
							<input type="checkbox" name="rentfetch_options_availability_button_enabled_hide_when_unavailable" id="rentfetch_options_availability_button_enabled_hide_when_unavailable" <?php checked(get_option('rentfetch_options_availability_button_enabled_hide_when_unavailable'), '1' ); ?>>
							Hide unavailable
						</label>
						<p>Only show this button when the floor plan *also* has units available or a date when they'll become avialable.</p>
					</li>
				</ul>
				
			</div>
		</div>
	</div>

	<div class="row floorplan-archive-buttons unavailability">
		<div class="section" style="display: none;"></div>
		<div class="section">
			<div class="always-visible">
				<ul class="checkboxes">
					<li>
						<label class="label-large" for="rentfetch_options_unavailability_button_enabled">
							<input type="checkbox" name="rentfetch_options_unavailability_button_enabled" id="rentfetch_options_unavailability_button_enabled" <?php checked(get_option('rentfetch_options_unavailability_button_enabled'), '1'); ?>>
							Unavailability Button
						</label>
						<p class="description pt-2">A button which only displays on floor plans without any availability. This will be the same link for every floor plan (it is not dynamic).
						</p>
					</li>
				</ul>
			</div>
			<div class="white-box">
				<label for="rentfetch_options_unavailability_button_button_label">Button label</label>
				<input type="text" name="rentfetch_options_unavailability_button_button_label"
					id="rentfetch_options_unavailability_button_button_label"
					value="<?php echo esc_attr(get_option('rentfetch_options_unavailability_button_button_label', 'Reach out')); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_unavailability_button_link">Link</label>
				<input type="url" name="rentfetch_options_unavailability_button_link"
					id="rentfetch_options_unavailability_button_link"
					value="<?php echo esc_url(get_option('rentfetch_options_unavailability_button_link')); ?>">
			</div>
		</div>
	</div>

	<div class="row floorplan-archive-buttons contact">
		<div class="section" style="display: none;"></div>
		<div class="section">
			<div class="always-visible">
				<ul class="checkboxes">
					<li>
						<label class="label-large" for="rentfetch_options_contact_button_enabled">
							<input type="checkbox" name="rentfetch_options_contact_button_enabled" id="rentfetch_options_contact_button_enabled" <?php checked(get_option('rentfetch_options_contact_button_enabled', 'Contact'), true); ?>>
							Contact Button
						</label>
						<p class="description pt-2">A button linking either to a static page on the site or to a single third-party location.
							This will be the same link for every floor plan (it is not dynamic).
						</p>
					</li>
				</ul>
			</div>
			<div class="white-box">
				<label for="rentfetch_options_contact_button_button_label">Button label</label>
				<input type="text" name="rentfetch_options_contact_button_button_label"
					id="rentfetch_options_contact_button_button_label"
					value="<?php echo esc_attr(get_option('rentfetch_options_contact_button_button_label')); ?>">
				<p class="description">Required for syncing any data down from an API.</p>
			</div>
			<div class="white-box">
				<label for="rentfetch_options_contact_button_link">Link</label>
				<input type="url" name="rentfetch_options_contact_button_link" id="rentfetch_options_contact_button_link"
					value="<?php echo esc_url(get_option('rentfetch_options_contact_button_link')); ?>">
			</div>
		</div>
	</div>

	<div class="row floorplan-archive-buttons tour">
		<div class="section" style="display: none;"></div>
		<div class="section">
			<div class="always-visible">
				<ul class="checkboxes">
					<li>
						<label class="label-large" for="rentfetch_options_tour_button_enabled">
							<input type="checkbox" name="rentfetch_options_tour_button_enabled" id="rentfetch_options_tour_button_enabled" <?php checked(get_option('rentfetch_options_tour_button_enabled'), true); ?> />
							Tour Button
						</label>
						<p class="description pt-2">A typically-external link to schedule a tour. You can set a global link below.</p>
					</li>
				</ul>
			</div>
			<div class="white-box">
				<label for="rentfetch_options_tour_button_button_label">Button label</label>
				<input type="text" name="rentfetch_options_tour_button_button_label"
					id="rentfetch_options_tour_button_button_label"
					value="<?php echo esc_attr(get_option('rentfetch_options_tour_button_button_label')); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_tour_button_fallback_link">Link</label>
				<input type="url" name="rentfetch_options_tour_button_fallback_link"
					id="rentfetch_options_tour_button_fallback_link"
					value="<?php echo esc_attr(get_option('rentfetch_options_tour_button_fallback_link')); ?>">
			</div>
		</div>
	</div>

	<?php
	echo '</section>';
}
add_action('rentfetch_do_settings_floorplans_floorplan_buttons', 'rentfetch_settings_floorplans_floorplan_buttons');

/**
 * Save the floorplan button settings
 */
function rentfetch_save_settings_floorplan_buttons()
{

	// Get the tab and section.
	$tab = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ('floorplans' !== $tab ) {
		return;
	}

	$nonce = isset($_POST['rentfetch_main_options_nonce_field']) ? sanitize_text_field(wp_unslash($_POST['rentfetch_main_options_nonce_field'])) : '';

	// * Verify the nonce
	if (!wp_verify_nonce(wp_unslash($nonce), 'rentfetch_main_options_nonce_action')) {
		die('Security check failed');
	}

	// Checkbox field - Enable the contact button.
	$options_contact_button_enabled = isset($_POST['rentfetch_options_contact_button_enabled']) ? '1' : '0';
	update_option('rentfetch_options_contact_button_enabled', $options_contact_button_enabled);

	// Text field - Button label.
	if (isset($_POST['rentfetch_options_contact_button_button_label'])) {
		$options_contact_button_button_label = sanitize_text_field(wp_unslash($_POST['rentfetch_options_contact_button_button_label']));
		update_option('rentfetch_options_contact_button_button_label', $options_contact_button_button_label);
	}

	// Text field - Link.
	if (isset($_POST['rentfetch_options_contact_button_link'])) {
		$options_contact_button_link = sanitize_text_field(wp_unslash($_POST['rentfetch_options_contact_button_link']));
		update_option('rentfetch_options_contact_button_link', $options_contact_button_link);
	}

	// Checkbox field - Enable the availability button.
	$options_availability_button_enabled = isset($_POST['rentfetch_options_availability_button_enabled']) ? '1' : '0';
	update_option('rentfetch_options_availability_button_enabled', $options_availability_button_enabled);
	
	// Checkbox field - Hide the availability button if there's nothing available.
	$options_availability_button_enabled_hide_when_unavailable = isset($_POST['rentfetch_options_availability_button_enabled_hide_when_unavailable']) ? '1' : '0';
	update_option('rentfetch_options_availability_button_enabled_hide_when_unavailable', $options_availability_button_enabled_hide_when_unavailable);

	// Text field - Button label.
	if (isset($_POST['rentfetch_options_availability_button_button_label'])) {
		$options_availability_button_button_label = sanitize_text_field(wp_unslash($_POST['rentfetch_options_availability_button_button_label']));
		update_option('rentfetch_options_availability_button_button_label', $options_availability_button_button_label);
	}

	// Checkbox field - Enable the unavailability button.
	$options_unavailability_button_enabled = isset($_POST['rentfetch_options_unavailability_button_enabled']) ? '1' : '0';
	update_option('rentfetch_options_unavailability_button_enabled', $options_unavailability_button_enabled);

	// Text field - Button label.
	if (isset($_POST['rentfetch_options_unavailability_button_button_label'])) {
		$options_unavailability_button_button_label = sanitize_text_field(wp_unslash($_POST['rentfetch_options_unavailability_button_button_label']));
		update_option('rentfetch_options_unavailability_button_button_label', $options_unavailability_button_button_label);
	}

	// Text field - Link.
	if (isset($_POST['rentfetch_options_unavailability_button_link'])) {
		$options_contact_button_link = sanitize_text_field(wp_unslash($_POST['rentfetch_options_unavailability_button_link']));
		update_option('rentfetch_options_unavailability_button_link', $options_contact_button_link);
	}

	// Checkbox field - Enable the tour button.
	$options_tour_button_enabled = isset($_POST['rentfetch_options_tour_button_enabled']) ? '1' : '0';
	update_option('rentfetch_options_tour_button_enabled', $options_tour_button_enabled);

	// Text field - Tour button label.
	if (isset($_POST['rentfetch_options_tour_button_button_label'])) {
		$options_tour_button_button_label = sanitize_text_field(wp_unslash($_POST['rentfetch_options_tour_button_button_label']));
		update_option('rentfetch_options_tour_button_button_label', $options_tour_button_button_label);
	}

	// Text field - Tour button fallback link.
	if (isset($_POST['rentfetch_options_tour_button_fallback_link'])) {
		$options_tour_button_fallback_link = sanitize_text_field(wp_unslash($_POST['rentfetch_options_tour_button_fallback_link']));
		update_option('rentfetch_options_tour_button_fallback_link', $options_tour_button_fallback_link);
	}
}
add_action('rentfetch_save_settings', 'rentfetch_save_settings_floorplan_buttons');