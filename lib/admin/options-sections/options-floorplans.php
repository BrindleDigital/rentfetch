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
function rentfetch_settings_set_defaults_floorplans()
{

	// Add option if it doesn't exist.
	$default_values = array(
		'beds_search',
		'baths_search',
		'floorplan_category',
		'floorplan_type',
		'squarefoot_search',
	);
	add_option('rentfetch_options_floorplan_filters', $default_values);

	$default_values = 'beds';
	add_option('rentfetch_options_floorplan_default_order', $default_values);

	add_option('rentfetch_options_floorplan_pricing_display', 'range');
	add_option('rentfetch_options_floorplan_force_single_template_link', 'disabled');
}
register_activation_hook(RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_floorplans');

/**
 * Output floorplan settings
 */
function rentfetch_settings_floorplans_floorplan_search()
{
	?>
<div class="header">
  <h2 class="title">Floor Plan Search Settings</h2>
  <p class="description">The settings configured for the property search capabilities on a multi-property website.</p>
</div>

<div class="row">
	<div class="section">
		<label class="label-large" for="rentfetch_options_floorplan_default_order">Floor Plan Default order</label>
		<p class="description">
		The default order in which the floor plans search should display. (NOTE: the floor plans grid
		order can be set <a target="_blank" href="/wp-admin/admin.php?page=rentfetch-shortcodes">through shortcode
		parameters)</a>
		</p>
		<ul class="radio">
			<li>
				<label>
					<input type="radio" name="rentfetch_options_floorplan_default_order"
						id="rentfetch_options_floorplan_default_order" value="beds" <?php checked(get_option('rentfetch_options_floorplan_default_order'), 'beds'); ?> />
						Beds
				</label>
			</li>
			<li>
				<label>
					<input type="radio" name="rentfetch_options_floorplan_default_order"
						id="rentfetch_options_floorplan_default_order" value="baths" <?php checked(get_option('rentfetch_options_floorplan_default_order'), 'baths'); ?> />
					Baths
				</label>
			</li>
			<li>
				<label>
					<input type="radio" name="rentfetch_options_floorplan_default_order"
						id="rentfetch_options_floorplan_default_order" value="availability" <?php checked(get_option('rentfetch_options_floorplan_default_order'), 'availability'); ?> />
					Availability
				</label>
			</li>
			<li>
				<label>
					<input type="radio" name="rentfetch_options_floorplan_default_order"
						id="rentfetch_options_floorplan_default_order" value="pricehigh" <?php checked(get_option('rentfetch_options_floorplan_default_order'), 'pricehigh'); ?> />
					Price (high to low)
				</label>
			</li>
			<li>
				<label>
					<input type="radio" name="rentfetch_options_floorplan_default_order"
						id="rentfetch_options_floorplan_default_order" value="alphabetical" <?php checked(get_option('rentfetch_options_floorplan_default_order'), 'alphabetical'); ?> />
					Alphabetical (A-Z) by title
				</label>
			</li>
			<li>
				<label>
					<input type="radio" name="rentfetch_options_floorplan_default_order"
						id="rentfetch_options_floorplan_default_order" value="menu_order" <?php checked(get_option('rentfetch_options_floorplan_default_order'), 'menu_order'); ?> />
					<span>Menu order (this is a <a target="_blank" href="https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters">default WordPress ordering scheme</a> that can be used in combination with other plugins like <a href="https://wordpress.org/plugins/simple-custom-post-order/" target="_blank">this one</a> that let you manually set the order of your posts)</span>
				</label>
			</li>
		</ul>
  </div>

  <div class="separator"></div>

  <div class="section pb-0">
    <label class="label-large" for="rentfetch_options_floorplan_pricing_display">Floor Plan Pricing Display</label>
    <p class="description">How should pricing be shown on floor plan archives?</p>
    <ul class="radio">
      <li>
        <label>
          <input type="radio" name="rentfetch_options_floorplan_pricing_display"
            id="rentfetch_options_floorplan_pricing_display" value="range"
            <?php checked(get_option('rentfetch_options_floorplan_pricing_display'), 'range'); ?> />
          Range (e.g. "$1999 to $2999")
        </label>
      </li>
      <li>
        <label>
          <input type="radio" name="rentfetch_options_floorplan_pricing_display"
            id="rentfetch_options_floorplan_pricing_display" value="minimum"
            <?php checked(get_option('rentfetch_options_floorplan_pricing_display'), 'minimum'); ?>>
          Minimum (e.g. "from $1999")
        </label>
      </li>
    </ul>
  </div>

  <div class="separator"></div>

  <div class="section pb-0">
    <label class="label-large" for="rentfetch_options_floorplan_filters">Floor Plan Search Filters</label>
    <p class="description">Which components should be shown floor plans search?</p>
    <?php
	
			// Get saved options.
			$options_floorplan_filters = get_option('rentfetch_options_floorplan_filters');
	
			// Make it an array just in case it isn't (for example, if it's a new install).
			if (!is_array($options_floorplan_filters)) {
				$options_floorplan_filters = array();
			}
	
			?>
    <ul class="checkboxes">
      <li>
        <label>
          <input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="beds_search"
            <?php checked(in_array('beds_search', $options_floorplan_filters, true)); ?>>
          Beds search
        </label>
      </li>
      <li>
        <label>
          <input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="baths_search"
            <?php checked(in_array('baths_search', $options_floorplan_filters, true)); ?>>
          Baths search
        </label>
      </li>
      <li>
        <label>
          <input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="floorplan_category"
            <?php checked(in_array('floorplan_category', $options_floorplan_filters, true)); ?>>
		  <?php
		  $taxonomy = get_taxonomy('floorplancategory');
		  echo $taxonomy ? $taxonomy->label : 'Floorplan category';
		  ?>
        </label>
      </li>
      <li>
        <label>
          <input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="floorplan_type"
            <?php checked(in_array('floorplan_type', $options_floorplan_filters, true)); ?>>
		<?php
				  $taxonomy = get_taxonomy('floorplantype');
				  echo $taxonomy ? $taxonomy->label : 'Floorplan type';
				  ?>
        </label>
      </li>
      <li>
        <label>
          <input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="price_search"
            <?php checked(in_array('price_search', $options_floorplan_filters, true)); ?>>
          Price search
        </label>
      </li>
      <li>
        <label>
          <input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="date_search"
            <?php checked(in_array('date_search', $options_floorplan_filters, true)); ?>>
          Date search
        </label>
      </li>
      <li>
        <label>
			<input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="squarefoot_search" <?php checked(in_array('squarefoot_search', $options_floorplan_filters, true)); ?>>
			Square footage search
        </label>
      </li>
    </ul>
  </div>

</div>

<?php

}
add_action('rentfetch_do_settings_floorplans_floorplan_search', 'rentfetch_settings_floorplans_floorplan_search');

/**
 * Output floorplan buttons settings page
 */
function rentfetch_settings_floorplans_floorplan_buttons_page()
{
	?>
<div class="header">
  <h2 class="title">Floor Plan Buttons</h2>
  <p class="description">The settings configured for the buttons shown on the floor plans grid when you hover on a
    single floor plan.</p>
</div>
<?php

	do_action('rentfetch_do_settings_floorplans_floorplan_buttons');

}
add_action('rentfetch_do_settings_floorplans_floorplan_buttons_page', 'rentfetch_settings_floorplans_floorplan_buttons_page');

/**
 * Output floorplan display settings page
 */
function rentfetch_settings_floorplans_floorplan_display()
{
	?>
<div class="header">
  <h2 class="title">Floor Plan Display Settings</h2>
  <p class="description">Settings that control how floor plans are displayed on your website.</p>
</div>

<div class="row">
  <div class="section pb-0">
    <label class="label-large" for="rentfetch_options_floorplan_hide_number_of_units">Hide the number of units</label>
    <p class="description">There are a number of reasons you might want to hide the number of units. </p>

    <ul class="checkboxes">
      <li>
        <label for="rentfetch_options_floorplan_hide_number_of_units">
			<input type="checkbox" name="rentfetch_options_floorplan_hide_number_of_units" id="rentfetch_options_floorplan_hide_number_of_units" <?php checked(get_option('rentfetch_options_floorplan_hide_number_of_units'), '1'); ?>>
			Hide it in floor plan archives
        </label>
      </li>
    </ul>
  </div>

	<div class="separator"></div>

	<div class="section pb-0">
		<label class="label-large">Always link to the floor plans single template?</label>
		<p class="description">Force enable the single-floorplan page regardless of whether there are units</p>
		<ul class="radio">
			<li>
				<label for="rentfetch_options_floorplan_force_single_template_link">
					<input type="radio" name="rentfetch_options_floorplan_force_single_template_link"
						id="rentfetch_options_floorplan_force_single_template_link" value="enabled" <?php checked( get_option( 'rentfetch_options_floorplan_force_single_template_link' ), 'enabled' ); ?>>
					Yes, always link to the single-floorplan template
				</label>
			</li>
			<li>
				<label for="rentfetch_options_floorplan_force_single_template_link">
					<input type="radio" name="rentfetch_options_floorplan_force_single_template_link"
						id="rentfetch_options_floorplan_force_single_template_link" value="disabled" <?php checked( get_option( 'rentfetch_options_floorplan_force_single_template_link' ), 'disabled' ); ?>>
					No, use the default behavior (link to the single-floorplan template only if there are units)
				</label>
			</li>
		</ul>
	</div>

  <div class="separator"></div>

  <div class="section">
  <label class="label-large" for="rentfetch_options_floorplan_apply_styles_no_floorplans">Fade out unavailable floor plans</label>
    <ul class="checkboxes">
      <li>
        <label for="rentfetch_options_floorplan_apply_styles_no_floorplans">
			<input type="checkbox" name="rentfetch_options_floorplan_apply_styles_no_floorplans" id="rentfetch_options_floorplan_apply_styles_no_floorplans" <?php checked(get_option('rentfetch_options_floorplan_apply_styles_no_floorplans'), '1'); ?>>
			Apply faded styles to floor plans with no units available
        </label>
      </li>
    </ul>
  </div>
</div>

<?php

}
add_action('rentfetch_do_settings_floorplans_floorplan_display', 'rentfetch_settings_floorplans_floorplan_display');

/**
 * Save the floorplan
 */
function rentfetch_save_settings_floorplan_search()
{

	// Get the tab and section.
	$tab = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ('floorplans' !== $tab || (!empty($section) && 'floorplan-search' !== $section)) {
		return;
	}

	$nonce = isset($_POST['rentfetch_main_options_nonce_field']) ? sanitize_text_field(wp_unslash($_POST['rentfetch_main_options_nonce_field'])) : '';

	// * Verify the nonce
	if (!wp_verify_nonce(wp_unslash($nonce), 'rentfetch_main_options_nonce_action')) {
		die('Security check failed');
	}

	// Select field.
	if (isset($_POST['rentfetch_options_floorplan_default_order'])) {
		$floorplan_default_order = sanitize_text_field(wp_unslash($_POST['rentfetch_options_floorplan_default_order']));
		update_option('rentfetch_options_floorplan_default_order', $floorplan_default_order);
	}

	// Checkboxes field.
	if (isset($_POST['rentfetch_options_floorplan_filters'])) {
		$options_floorplan_filters = array_map('sanitize_text_field', wp_unslash($_POST['rentfetch_options_floorplan_filters']));
		update_option('rentfetch_options_floorplan_filters', $options_floorplan_filters);
	} else {
		update_option('rentfetch_options_floorplan_filters', array());
	}

	// Select field.
	if (isset($_POST['rentfetch_options_floorplan_pricing_display'])) {
		$property_display = sanitize_text_field(wp_unslash($_POST['rentfetch_options_floorplan_pricing_display']));
		update_option('rentfetch_options_floorplan_pricing_display', $property_display);
	}
}
add_action('rentfetch_save_settings', 'rentfetch_save_settings_floorplan_search');

/**
 * Save the floorplan display settings
 */
function rentfetch_save_settings_floorplan_display()
{

	// Get the tab and section.
	$tab = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ('floorplans' !== $tab || 'floorplan-display' !== $section) {
		return;
	}

	$nonce = isset($_POST['rentfetch_main_options_nonce_field']) ? sanitize_text_field(wp_unslash($_POST['rentfetch_main_options_nonce_field'])) : '';

	// * Verify the nonce
	if (!wp_verify_nonce(wp_unslash($nonce), 'rentfetch_main_options_nonce_action')) {
		die('Security check failed');
	}

	// Checkbox field - Hide the number of units.
	$hide_number_of_units = isset($_POST['rentfetch_options_floorplan_hide_number_of_units']) ? '1' : '0';
	update_option('rentfetch_options_floorplan_hide_number_of_units', $hide_number_of_units);

	// Checkbox field - Fade out unavailable floorplans
	$fade_out_unavailable_floorplans = isset($_POST['rentfetch_options_floorplan_apply_styles_no_floorplans']) ? '1' : '0';
	update_option('rentfetch_options_floorplan_apply_styles_no_floorplans', $fade_out_unavailable_floorplans);

	// Select field.
	if (isset($_POST['rentfetch_options_floorplan_force_single_template_link'])) {
		$force_floorplans_single_template = sanitize_text_field(wp_unslash($_POST['rentfetch_options_floorplan_force_single_template_link']));
		update_option('rentfetch_options_floorplan_force_single_template_link', $force_floorplans_single_template);
	}
}
add_action('rentfetch_save_settings', 'rentfetch_save_settings_floorplan_display');