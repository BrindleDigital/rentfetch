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
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_properties' );


/**
 * Adds the property search settings subsection to the Rent Fetch settings page
 */
function rentfetch_settings_properties_property_search() {
	?>
	
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_maximum_number_of_properties_to_show">Maximum number of properties to show</label>
		</div>
		<div class="column">
			<p class="description">The most properties we should attempt to show while matching a search. We recommend for performance reasons that this number is not set above ~200 properties.</p>
			<input type="text" name="rentfetch_options_maximum_number_of_properties_to_show" id="rentfetch_options_maximum_number_of_properties_to_show" value="<?php echo esc_attr( get_option( 'rentfetch_options_maximum_number_of_properties_to_show' ) ); ?>">
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_property_availability_display">Property availability display</label>
			
		</div>
		<div class="column">
			<p class="description">Select whether you'd like to show properties that are available or all properties. <strong>If this is set to ignore availability, all filters involving floorplans (e.g. beds, baths, price, move-in date, etc.) will not function. ALL availability information will be ignored.</strong> This setting applies to the properties search and to the "nearby properties" listing on the properties single template.</p>
			<select name="rentfetch_options_property_availability_display" id="rentfetch_options_property_availability_display" value="<?php echo esc_attr( get_option( 'rentfetch_options_property_availability_display' ) ); ?>">
				<option value="available" <?php selected( get_option( 'rentfetch_options_property_availability_display' ), 'available' ); ?>>Availability</option>
				<option value="all" <?php selected( get_option( 'rentfetch_options_property_availability_display' ), 'all' ); ?>>All properties ignoring availability</option>
			</select>
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_featured_filters">Featured property filters</label>
			<p class="description">Which components should be shown by default?</p>
		</div>
		<div class="column">
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
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="type_search" <?php checked( in_array( 'type_search', $options_featured_filters, true ) ); ?>>
						Property type search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="floorplan_category_search" <?php checked( in_array( 'floorplan_category_search', $options_featured_filters, true ) ); ?>>
						Floorplan category search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="floorplan_type_search" <?php checked( in_array( 'floorplan_type_search', $options_featured_filters, true ) ); ?>>
						Floorplan type search
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
	</div>
	
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_dialog_filters">All property filters</label>
			<p class="description">Which components should be shown in the filters lightbox? Typically all filters are shown here, even if they also appear in the featured filters area.</p>
		</div>
		<div class="column">
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
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="floorplan_category_search" <?php checked( in_array( 'floorplan_category_search', $options_dialog_filters, true ) ); ?>>
						Floorplan category search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="floorplan_type_search" <?php checked( in_array( 'floorplan_type_search', $options_dialog_filters, true ) ); ?>>
						Floorplan type search
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
		<div class="column">
			<label for="rentfetch_options_maximum_bedrooms_to_search">Maximum bedrooms to search</label>
		</div>
		<div class="column">
			<input type="text" name="rentfetch_options_maximum_bedrooms_to_search" id="rentfetch_options_maximum_bedrooms_to_search" value="<?php echo esc_attr( get_option( 'rentfetch_options_maximum_bedrooms_to_search' ) ); ?>">
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_price_filter_minimum">Price filter</label>
		</div>
		<div class="column">
			<div class="white-box">
				<label for="rentfetch_options_price_filter_minimum">Price filter minimum</label>
				<input type="text" name="rentfetch_options_price_filter_minimum" id="rentfetch_options_price_filter_minimum" value="<?php echo esc_attr( get_option( 'rentfetch_options_price_filter_minimum' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_price_filter_maximum">Price filter maximum</label>
				<input type="text" name="rentfetch_options_price_filter_maximum" id="rentfetch_options_price_filter_maximum" value="<?php echo esc_attr( get_option( 'rentfetch_options_price_filter_maximum' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_price_filter_step">Price filter step</label>
				<input type="text" name="rentfetch_options_price_filter_step" id="rentfetch_options_price_filter_step" value="<?php echo esc_attr( get_option( 'rentfetch_options_price_filter_step' ) ); ?>">
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_number_of_amenities_to_show">Number of amenities to show</label>
		</div>
		<div class="column">
			<input type="text" name="rentfetch_options_number_of_amenities_to_show" id="rentfetch_options_number_of_amenities_to_show" value="<?php echo esc_attr( get_option( 'rentfetch_options_number_of_amenities_to_show' ) ); ?>">
		</div>
	</div>

	<div class="row">
		<div class="column">
			<label for="rentfetch_options_properties_hide_number_of_units">Hide the number of units</label>
			<p class="description"><em>NOTE: The [rentfetch_properties] shortcode already does not show available units by default, but [rentfetch_propertysearch] does.</em></p>
		</div>
		<div class="column">
			<label for="rentfetch_options_properties_hide_number_of_units">
				<input type="checkbox" name="rentfetch_options_properties_hide_number_of_units" id="rentfetch_options_properties_hide_number_of_units" <?php checked( get_option( 'rentfetch_options_properties_hide_number_of_units' ), '1' ); ?>>
				Hide it in property archives
			</label>
		</div>
	</div>
	<?php
}
add_action( 'rentfetch_do_settings_properties_property_search', 'rentfetch_settings_properties_property_search' );

/**
 * Save the property search settings
 */
function rentfetch_save_settings_property_search() {

	// Get the tab and section.
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ( 'properties' !== $tab || ! empty( $section ) ) {
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

	// Checkboxes field.
	if ( isset( $_POST['rentfetch_options_dialog_filters'] ) ) {
		$options_dialog_filters = array_map( 'sanitize_text_field', wp_unslash( $_POST['rentfetch_options_dialog_filters'] ) );
		update_option( 'rentfetch_options_dialog_filters', $options_dialog_filters );
	} else {
		update_option( 'rentfetch_options_dialog_filters', array() );
	}

	// Checkboxes field.
	if ( isset( $_POST['rentfetch_options_featured_filters'] ) ) {
		$options_featured_filters = array_map( 'sanitize_text_field', wp_unslash( $_POST['rentfetch_options_featured_filters'] ) );
		update_option( 'rentfetch_options_featured_filters', $options_featured_filters );
	} else {
		update_option( 'rentfetch_options_featured_filters', array() );
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
}
add_action( 'rentfetch_save_settings', 'rentfetch_save_settings_property_search' );