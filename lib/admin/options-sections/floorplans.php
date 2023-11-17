<?php

/**
 * Output floorplan settings
 */
function rent_fetch_settings_floorplans_floorplan_search() {
	?>
	<div class="row">
		<div class="column">
			<label for="options_floorplan_filters">Floorplan search filters</label>
			<p class="description">Which components should be shown floorplans search?</p>
		</div>
		<div class="column">
			<?php
			
			// Get saved options
			$options_floorplan_filters = get_option( 'options_floorplan_filters' );
			
			// Define default values
			$default_options = array(
				'beds_search',
				'baths_search',
				'price_search',
				'date_search',
				'squarefoot_search',
				'sort',
			);
			
			// Make it an array just in case it isn't (for example, if it's a new install)
			if (!is_array($options_floorplan_filters)) {
				$options_floorplan_filters = $default_options;
			}
			
			?>
			<ul class="checkboxes">
				<li>
					<label>
						<input type="checkbox" name="options_floorplan_filters[]" value="beds_search" <?php checked( in_array( 'beds_search', $options_floorplan_filters ) ); ?>>
						Beds search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="options_floorplan_filters[]" value="baths_search" <?php checked( in_array( 'baths_search', $options_floorplan_filters ) ); ?>>
						Baths search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="options_floorplan_filters[]" value="price_search" <?php checked( in_array( 'price_search', $options_floorplan_filters ) ); ?>>
						Price search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="options_floorplan_filters[]" value="date_search" <?php checked( in_array( 'date_search', $options_floorplan_filters ) ); ?>>
						Date search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="options_floorplan_filters[]" value="squarefoot_search" <?php checked( in_array( 'squarefoot_search', $options_floorplan_filters ) ); ?>>
						Square footage search
					</label>
				</li>
				<li>
					<label>
						<input type="checkbox" name="options_floorplan_filters[]" value="sort" <?php checked( in_array( 'sort', $options_floorplan_filters ) ); ?>>
						Sorting
					</label>
				</li>
			</ul>
		</div>
	</div>
	<?php
}
add_action( 'rent_fetch_do_settings_floorplans_floorplan_search', 'rent_fetch_settings_floorplans_floorplan_search' );

/**
 * Save the floorplan 
 */
function rent_fetch_save_settings_floorplan_search() {
	
	// Get the tab and section
	$tab = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();
	
	if ( $tab !== 'floorplans' || !empty( $section ) )
		return;
		
	// Checkboxes field
	if ( isset ( $_POST['options_floorplan_filters'] ) ) {
		$options_floorplan_filters = array_map('sanitize_text_field', $_POST['options_floorplan_filters']);
		update_option('options_floorplan_filters', $options_floorplan_filters);
	} else {
		update_option('options_floorplan_filters', array());
	}
	
}
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_floorplan_search' );