<?php

/**
 * Set defaults on activation
 */
function rentfetch_settings_set_defaults_properties() {
    
    // Add option if it doesn't exist
    add_option( 'rentfetch_options_maximum_number_of_properties_to_show', -1 );
    add_option( 'rentfetch_options_property_availability_display', 'all' );
	
	$defaultarray = [
		'text_based_search',
		'beds_search',
		'price_search',
	];
    add_option( 'rentfetch_options_featured_filters', $defaultarray );
	
	$defaultarray = [
		'text_based_search',
		'beds_search',
		'baths_search',
		'squarefoot_search',
		'type_search',
		'date_search',
		'price_search',
		'squarefoot_search',
		'amenities_search',
		
	];
    add_option( 'rentfetch_options_dialog_filters', $defaultarray );
	
	add_option( 'rentfetch_options_number_of_amenities_to_show', 20 );
    
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_properties' );


/**
 * Adds the property search settings subsection to the Rent Fetch settings page
 */
function rent_fetch_settings_properties_property_search() {
	?>
	
	 <div class="row">
		<div class="column">
			<label for="rentfetch_options_maximum_number_of_properties_to_show">Maximum number of properties to show</label>
		</div>
		<div class="column">
			<p class="description">The most properties we should attempt to show while matching a search. We recommend for performance reasons that this number is not set above ~200 properties.</p>
			<input type="text" name="rentfetch_options_maximum_number_of_properties_to_show" id="rentfetch_options_maximum_number_of_properties_to_show" value="<?php echo esc_attr( get_option( 'rentfetch_options_maximum_number_of_properties_to_show' ), '-1' ); ?>">
		</div>
	</div>
	
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_property_availability_display">Property availability display</label>
			
		</div>
		<div class="column">
			<p class="description">Select whether you'd like to show properties that are available or all properties. This setting applies to the properties search and to the "nearby properties" listing on the properties single template.</p>
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
			
			// Get saved options
			$options_featured_filters = get_option( 'rentfetch_options_featured_filters');
			if ( !is_array($options_featured_filters) ) {
				$options_featured_filters = array();
			}
			
			?>
			<ul class="checkboxes">
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="text_based_search" <?php checked( in_array( 'text_based_search', $options_featured_filters ) ); ?>>
						Text-based search (this works best with the Relevanssi plugin enhancing your search)
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="beds_search" <?php checked( in_array( 'beds_search', $options_featured_filters ) ); ?>>
						Beds search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="baths_search" <?php checked( in_array( 'baths_search', $options_featured_filters ) ); ?>>
						Baths search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="squarefoot_search" <?php checked( in_array( 'squarefoot_search', $options_featured_filters ) ); ?>>
						Square footage search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="type_search" <?php checked( in_array( 'type_search', $options_featured_filters ) ); ?>>
						Type search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="date_search" <?php checked( in_array( 'date_search', $options_featured_filters ) ); ?>>
						Date search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="price_search" <?php checked( in_array( 'price_search', $options_featured_filters ) ); ?>>
						Price search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_featured_filters[]" value="amenities_search" <?php checked( in_array( 'amenities_search', $options_featured_filters ) ); ?>>
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
			
			// Get saved options
			$options_dialog_filters = get_option( 'rentfetch_options_dialog_filters');
			
			// Make it an array just in case it isn't (for example, if it's a new install)
			if (!is_array($options_dialog_filters)) {
				$options_dialog_filters = array();
			}
			
			?>
			<ul class="checkboxes">
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="text_based_search" <?php checked( in_array( 'text_based_search', $options_dialog_filters ) ); ?>>
						Text-based search (this works best with the Relevanssi plugin enhancing your search)
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="beds_search" <?php checked( in_array( 'beds_search', $options_dialog_filters ) ); ?>>
						Beds search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="baths_search" <?php checked( in_array( 'baths_search', $options_dialog_filters ) ); ?>>
						Baths search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="squarefoot_search" <?php checked( in_array( 'squarefoot_search', $options_featured_filters ) ); ?>>
						Square footage search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="type_search" <?php checked( in_array( 'type_search', $options_dialog_filters ) ); ?>>
						Type search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="date_search" <?php checked( in_array( 'date_search', $options_dialog_filters ) ); ?>>
						Date search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="price_search" <?php checked( in_array( 'price_search', $options_dialog_filters ) ); ?>>
						Price search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="rentfetch_options_dialog_filters[]" value="amenities_search" <?php checked( in_array( 'amenities_search', $options_dialog_filters ) ); ?>>
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
			<input type="text" name="rentfetch_options_number_of_amenities_to_show" id="rentfetch_options_number_of_amenities_to_show" value="<?php echo esc_attr( get_option( 'rentfetch_options_number_of_amenities_to_show' ), 10 ); ?>">
		</div>
	</div>        
	<?php
}
add_action( 'rent_fetch_do_settings_properties_property_search', 'rent_fetch_settings_properties_property_search' );

/**
 * Save the property search settings
 */
function rent_fetch_save_settings_property_search() {
	
	// Get the tab and section
	$tab = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();
	
	if ( $tab !== 'properties' || !empty( $section ) )
		return;
	
	// Number field
	if ( isset( $_POST[ 'rentfetch_options_maximum_number_of_properties_to_show'] ) ) {
		$max_properties = intval( $_POST[ 'rentfetch_options_maximum_number_of_properties_to_show'] );
		update_option( 'rentfetch_options_maximum_number_of_properties_to_show', $max_properties );
	}
	
	// Select field
	if ( isset( $_POST[ 'rentfetch_options_property_availability_display'] ) ) {
		$property_display = sanitize_text_field( $_POST[ 'rentfetch_options_property_availability_display'] );
		update_option( 'rentfetch_options_property_availability_display', $property_display );
	}
			
	// Checkboxes field
	if (isset($_POST[ 'rentfetch_options_dialog_filters'])) {
		$options_dialog_filters = array_map('sanitize_text_field', $_POST[ 'rentfetch_options_dialog_filters']);
		update_option( 'rentfetch_options_dialog_filters', $options_dialog_filters);
	} else {
		update_option( 'rentfetch_options_dialog_filters', array());
	}
	
	// Checkboxes field
	if (isset($_POST[ 'rentfetch_options_featured_filters'])) {
		$options_featured_filters = array_map('sanitize_text_field', $_POST[ 'rentfetch_options_featured_filters']);
		update_option( 'rentfetch_options_featured_filters', $options_featured_filters);
	} else {
		update_option( 'rentfetch_options_featured_filters', array());
	}
	
	// Number field
	if ( isset( $_POST[ 'rentfetch_options_maximum_bedrooms_to_search'] ) ) {
		$max_bedrooms = intval( $_POST[ 'rentfetch_options_maximum_bedrooms_to_search'] );
		update_option( 'rentfetch_options_maximum_bedrooms_to_search', $max_bedrooms );
	}
	
	// Number field
	if ( isset( $_POST[ 'rentfetch_options_price_filter_minimum'] ) ) {
		$price_filter_minimum = intval( $_POST[ 'rentfetch_options_price_filter_minimum'] );
		update_option( 'rentfetch_options_price_filter_minimum', $price_filter_minimum );
	} else {
		$price_filter_minimum = null;
		update_option( 'rentfetch_options_price_filter_minimum', $price_filter_minimum );
	}
	
	// Number field
	if ( isset( $_POST[ 'rentfetch_options_price_filter_maximum'] ) ) {
		$price_filter_maximum = intval( $_POST[ 'rentfetch_options_price_filter_maximum'] );
		update_option( 'rentfetch_options_price_filter_maximum', $price_filter_maximum );
	}
	
	// Number field
	if ( isset( $_POST[ 'rentfetch_options_price_filter_step'] ) ) {
		$price_filter_step = intval( $_POST[ 'rentfetch_options_price_filter_step'] );
		update_option( 'rentfetch_options_price_filter_step', $price_filter_step );
	}
	
	// Number field
	if ( isset( $_POST[ 'rentfetch_options_number_of_amenities_to_show'] ) ) {
		$number_of_amenities_to_show = intval( $_POST[ 'rentfetch_options_number_of_amenities_to_show'] );
		update_option( 'rentfetch_options_number_of_amenities_to_show', $number_of_amenities_to_show );
	}
	
}
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_property_search' );