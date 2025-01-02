<?php
/**
 * This file includes the options for the property archives
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set defaults on activation
 */
function rentfetch_settings_set_defaults_properties_propertyarchives() {

	// Add options if they don't exist with default values.
	add_option( 'rentfetch_options_property_footer_grid_number_properties', '9' );
	add_option( 'rentfetch_options_property_pricing_display', 'range' );
	add_option( 'rentfetch_options_property_orderby', 'menu_order' );
	add_option( 'rentfetch_options_property_order', 'ASC' );
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_properties_propertyarchives' );

/**
 * Adds the properties archives settings subsection to the Rent Fetch settings page
 */
function rentfetch_settings_properties_property_archives() {
	?>
	<div class="row">
		<div class="section">
			<label for="rentfetch_options_property_pricing_display">Property Pricing Display</label>
			<p class="description">How should pricing be shown on property archives?</p>
		</div>
		<div class="section">
			<ul class="radio">
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_pricing_display"
							id="rentfetch_options_property_pricing_display" value="range" <?php checked( get_option( 'rentfetch_options_property_pricing_display' ), 'range' ); ?>>
						Range (e.g. "$1999 to $2999")
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_pricing_display"
							id="rentfetch_options_property_pricing_display" value="minimum" <?php checked( get_option( 'rentfetch_options_property_pricing_display' ), 'minimum' ); ?>>
						Minimum (e.g. "from $1999")
					</label>
				</li>
			</ul>
		</div>
	</div>

	<div class="row">
		<div class="section">
			<label for="rentfetch_options_property_orderby">Order Properties By</label>
			<p class="description">In archives, what order would you like properties to be shown in by default?</p>
		</div>
		<div class="section">
			<ul class="radio">
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_orderby" id="rentfetch_options_property_orderby"
							value="menu_order" <?php checked( get_option( 'rentfetch_options_property_orderby' ), 'menu_order' ); ?>>
						Menu order (use this if utilizing drag/drop reordering)
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_orderby" id="rentfetch_options_property_orderby"
							value="date" <?php checked( get_option( 'rentfetch_options_property_orderby' ), 'date' ); ?>>
						Publish date
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_orderby" id="rentfetch_options_property_orderby"
							value="modified" <?php checked( get_option( 'rentfetch_options_property_orderby' ), 'modified' ); ?>>
						Last modified date
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_orderby" id="rentfetch_options_property_orderby"
							value="ID" <?php checked( get_option( 'rentfetch_options_property_orderby' ), 'ID' ); ?>>
						Post ID number
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_orderby" id="rentfetch_options_property_orderby"
							value="title" <?php checked( get_option( 'rentfetch_options_property_orderby' ), 'title' ); ?>>
						Alphabetical, based on property name
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_orderby" id="rentfetch_options_property_orderby"
							value="name" <?php checked( get_option( 'rentfetch_options_property_orderby' ), 'name' ); ?>>
						Alphabetical, based on property slug
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_orderby" id="rentfetch_options_property_orderby"
							value="rand" <?php checked( get_option( 'rentfetch_options_property_orderby' ), 'rand' ); ?>>
						Randomize
					</label>
				</li>
			</ul>
		</div>
	</div>

	<div class="row">
		<div class="section">
			<label for="rentfetch_options_property_order">Property Order Direction</label>
		</div>
		<div class="section">
			<ul class="radio">
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_order" id="rentfetch_options_property_order" value="ASC"
							<?php checked( get_option( 'rentfetch_options_property_order' ), 'ASC' ); ?>>
						Ascending
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="rentfetch_options_property_order" id="rentfetch_options_property_order" value="DESC"
							<?php checked( get_option( 'rentfetch_options_property_order' ), 'DESC' ); ?>>
						Descending
					</label>
				</li>
			</ul>
		</div>
	</div>

	<div class="row">
		<div class="section">
			<label for="rentfetch_options_property_footer_grid_number_properties">Property Footer Grid Maximum</label>
			<p class="description">By default, this shows on the individual properties template. If there are less than two
				properties that would show, this area simply does not appear.</p>
		</div>
		<div class="section">
			<p class="description">The maximum number of properties to show. By default, this will show all (-1) properties</p>
			<input type="number" name="rentfetch_options_property_footer_grid_number_properties"
				id="rentfetch_options_property_footer_grid_number_properties"
				value="<?php echo esc_attr( get_option( 'rentfetch_options_property_footer_grid_number_properties' ) ); ?>"
			/>
		</div>
	</div>
	<?php

	submit_button();
}
add_action( 'rentfetch_do_settings_properties_property_archives', 'rentfetch_settings_properties_property_archives' );

/**
 * Save the property archive settings
 */
function rentfetch_save_settings_property_archives() {

	// Get the tab and section.
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ( 'properties' !== $tab || 'property-archives' !== $section ) {
		return;
	}

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_main_options_nonce_action' ) ) {
		die( 'Security check failed' );
	}

	// Number field.
	if ( isset( $_POST['rentfetch_options_property_footer_grid_number_properties'] ) ) {
		$max_properties = intval( $_POST['rentfetch_options_property_footer_grid_number_properties'] );
		update_option( 'rentfetch_options_property_footer_grid_number_properties', $max_properties );
	}

	// Select field.
	if ( isset( $_POST['rentfetch_options_property_pricing_display'] ) ) {
		$property_display = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_property_pricing_display'] ) );
		update_option( 'rentfetch_options_property_pricing_display', $property_display );
	}

	// Select field.
	if ( isset( $_POST['rentfetch_options_property_orderby'] ) ) {
		$property_display = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_property_orderby'] ) );
		update_option( 'rentfetch_options_property_orderby', $property_display );
	}

	// Select field.
	if ( isset( $_POST['rentfetch_options_property_order'] ) ) {
		$property_display = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_property_order'] ) );
		update_option( 'rentfetch_options_property_order', $property_display );
	}
}
add_action( 'rentfetch_save_settings', 'rentfetch_save_settings_property_archives' );