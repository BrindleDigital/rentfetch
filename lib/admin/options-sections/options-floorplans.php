<?php
/**
 * This file includes the options for the floorplan buttons
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set defaults on activation
 */
function rentfetch_settings_set_defaults_floorplans() {

	// Add option if it doesn't exist.
	$default_values = array(
		'beds_search',
		'baths_search',
		'squarefoot_search',
	);
	add_option( 'rentfetch_options_floorplan_filters', $default_values );

	$default_values = 'beds';
	add_option( 'rentfetch_options_floorplan_default_order', $default_values );

	add_option( 'rentfetch_options_floorplan_pricing_display', 'range' );
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_floorplans' );

/**
 * Output floorplan settings
 */
function rentfetch_settings_floorplans_floorplan_search() {
	?>
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_floorplan_default_order">Floorplan default order</label>
		</div>
		<div class="column">
			<p class="description">The default order in which the floorplans search should display. (NOTE: the floorplans grid order can be set <a target="_blank" href="/wp-admin/admin.php?page=rentfetch-shortcodes">through shortcode parameters)</a>.</p>
			<select name="rentfetch_options_floorplan_default_order" id="rentfetch_options_floorplan_default_order" value="<?php echo esc_attr( get_option( 'rentfetch_options_floorplan_default_order' ) ); ?>">
				<option value="beds" <?php selected( get_option( 'rentfetch_options_floorplan_default_order' ), 'beds' ); ?>>Beds</option>
				<option value="baths" <?php selected( get_option( 'rentfetch_options_floorplan_default_order' ), 'baths' ); ?>>Baths</option>
				<option value="availability" <?php selected( get_option( 'rentfetch_options_floorplan_default_order' ), 'availability' ); ?>>Availability</option>
			</select>
		</div>
	</div>
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_floorplan_filters">Floorplan search filters</label>
			<p class="description">Which components should be shown floorplans search?</p>
		</div>
		<div class="column">
			<?php

			// Get saved options.
			$options_floorplan_filters = get_option( 'rentfetch_options_floorplan_filters' );

			// Make it an array just in case it isn't (for example, if it's a new install).
			if ( ! is_array( $options_floorplan_filters ) ) {
				$options_floorplan_filters = array();
			}

			?>
			<ul class="checkboxes">
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="beds_search" <?php checked( in_array( 'beds_search', $options_floorplan_filters, true ) ); ?>>
						Beds search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="baths_search" <?php checked( in_array( 'baths_search', $options_floorplan_filters, true ) ); ?>>
						Baths search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="price_search" <?php checked( in_array( 'price_search', $options_floorplan_filters, true ) ); ?>>
						Price search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="date_search" <?php checked( in_array( 'date_search', $options_floorplan_filters, true ) ); ?>>
						Date search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_floorplan_filters[]" value="squarefoot_search" <?php checked( in_array( 'squarefoot_search', $options_floorplan_filters, true ) ); ?>>
						Square footage search
					</label>
				</li>
			</ul>
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_floorplan_pricing_display">Floorplan Pricing Display</label>
			<p class="description">How should pricing be shown on floorplan archives?</p>
		</div>
		<div class="column">
			<ul class="radio">
				<li>
					<label>
						<input type="radio" name="rentfetch_options_floorplan_pricing_display" id="rentfetch_options_floorplan_pricing_display" value="range" <?php checked( get_option( 'rentfetch_options_floorplan_pricing_display' ), 'range' ); ?>>
						Range (e.g. "$1999 to $2999")
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_floorplan_pricing_display" id="rentfetch_options_floorplan_pricing_display" value="minimum" <?php checked( get_option( 'rentfetch_options_floorplan_pricing_display' ), 'minimum' ); ?>>
						Minimum (e.g. "from $1999")
					</label>
				</li>
			</ul>
		</div>
	</div>
	<?php
}
add_action( 'rentfetch_do_settings_floorplans_floorplan_search', 'rentfetch_settings_floorplans_floorplan_search' );

/**
 * Save the floorplan
 */
function rentfetch_save_settings_floorplan_search() {

	// Get the tab and section.
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ( 'floorplans' !== $tab || ! empty( $section ) ) {
		return;
	}

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_main_options_nonce_action' ) ) {
		die( 'Security check failed' );
	}

	// Select field.
	if ( isset( $_POST['rentfetch_options_floorplan_default_order'] ) ) {
		$floorplan_default_order = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_floorplan_default_order'] ) );
		update_option( 'rentfetch_options_floorplan_default_order', $floorplan_default_order );
	}

	// Checkboxes field.
	if ( isset( $_POST['rentfetch_options_floorplan_filters'] ) ) {
		$options_floorplan_filters = array_map( 'sanitize_text_field', wp_unslash( $_POST['rentfetch_options_floorplan_filters'] ) );
		update_option( 'rentfetch_options_floorplan_filters', $options_floorplan_filters );
	} else {
		update_option( 'rentfetch_options_floorplan_filters', array() );
	}
	
	// Select field.
	if ( isset( $_POST['rentfetch_options_floorplan_pricing_display'] ) ) {
		$property_display = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_floorplan_pricing_display'] ) );
		update_option( 'rentfetch_options_floorplan_pricing_display', $property_display );
	}
}
add_action( 'rentfetch_save_settings', 'rentfetch_save_settings_floorplan_search' );