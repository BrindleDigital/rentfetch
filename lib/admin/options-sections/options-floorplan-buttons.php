<?php 

/**
 * Set defaults on activation
 */
function rentfetch_settings_set_defaults_floorplans_buttons() {
    
	add_option( 'rentfetch_options_availability_button_enabled', true  );	
	add_option( 'rentfetch_options_availability_button_button_label', 'Lease now' );
	add_option( 'rentfetch_options_contact_button_button_label', 'Contact' );
	add_option( 'rentfetch_options_tour_button_button_label', 'Schedule a tour' );
    
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_floorplans_buttons' );

/**
 * Output floorplan button settings
 */
function rent_fetch_settings_floorplans_floorplan_buttons() {
	?>
	
	<script type="text/javascript">
		jQuery(document).ready(function( $ ) {
	
			// $( '.contact .white-box:not(.always-visible)' ).hide();
			
			// on change of input[name="rentfetch_options_enabled_integrations[]"], show/hide the integration options
			$( 'input[name="rentfetch_options_availability_button_enabled"]' ).on( 'change', function() {
				
				// console.log( this );
								
				if( this.checked ) {
					$( '.availability .white-box:not(.always-visible)' ).show();
				} else {
					$( '.availability .white-box:not(.always-visible)' ).hide();
				}
												
			}).trigger( 'change' );
			
		});
	</script>
	<div class="row floorplan-archive-buttons availability">
		<div class="column">
			<label>Availability button</label>
			<p class="description">A button which can pull in the availability link for each individual floorplan. If an availability URL is unavailable, this button will not display.</p>
		</div>
		<div class="column">
			<div class="white-box always-visible">
				<label for="rentfetch_options_availability_button_enabled">
					<input type="checkbox" name="rentfetch_options_availability_button_enabled" id="rentfetch_options_availability_button_enabled" <?php checked( get_option( 'rentfetch_options_availability_button_enabled' ), '1' ); ?>>
					Enable the availability button
				</label>
			</div>
			<div class="white-box">
				<label for="rentfetch_options_availability_button_button_label">Button label</label>
				<input type="text" name="rentfetch_options_availability_button_button_label" id="rentfetch_options_availability_button_button_label" value="<?php echo esc_attr( get_option( 'rentfetch_options_availability_button_button_label', 'Lease now' ) ); ?>">
			</div>
		</div>
	</div>
	
	<script type="text/javascript">
		jQuery(document).ready(function( $ ) {
	
			// $( '.contact .white-box:not(.always-visible)' ).hide();
			
			// on change of input[name="rentfetch_options_enabled_integrations[]"], show/hide the integration options
			$( 'input[name="rentfetch_options_contact_button_enabled"]' ).on( 'change', function() {
				
				// console.log( this );
								
				if( this.checked ) {
					$( '.contact .white-box:not(.always-visible)' ).show();
				} else {
					$( '.contact .white-box:not(.always-visible)' ).hide();
				}
												
			}).trigger( 'change' );
			
		});
	</script>
	<div class="row floorplan-archive-buttons contact">
		<div class="column">
			<label>Contact button</label>
			<p class="description">A button linking either to a static page on the site or to a single third-party location. This will be the same link for every floorplan (it is not dynamic).</p>
		</div>
		<div class="column">
			<div class="white-box always-visible">
				<label for="rentfetch_options_contact_button_enabled">
					<input type="checkbox" name="rentfetch_options_contact_button_enabled" id="rentfetch_options_contact_button_enabled" <?php checked( get_option( 'rentfetch_options_contact_button_enabled', 'Contact' ), true ); ?>>
					Enable the contact button
				</label>
			</div>
			<div class="white-box">
				<label for="rentfetch_options_contact_button_button_label">Button label</label>
				<input type="text" name="rentfetch_options_contact_button_button_label" id="rentfetch_options_contact_button_button_label" value="<?php echo esc_attr( get_option( 'rentfetch_options_contact_button_button_label' ) ); ?>">
				<p class="description">Required for syncing any data down from an API.</p>
			</div>
			<div class="white-box">
				<label for="rentfetch_options_contact_button_link">Link</label>
				<input type="url" name="rentfetch_options_contact_button_link" id="rentfetch_options_contact_button_link" value="<?php echo esc_url( get_option( 'rentfetch_options_contact_button_link' ) ); ?>">
			</div>
		</div>
	</div>
	
	<script type="text/javascript">
		jQuery(document).ready(function( $ ) {
	
			// $( '.contact .white-box:not(.always-visible)' ).hide();
			
			// on change of input[name="rentfetch_options_enabled_integrations[]"], show/hide the integration options
			$( 'input[name="rentfetch_options_tour_button_enabled"]' ).on( 'change', function() {
				
				// console.log( this );
								
				if( this.checked ) {
					$( '.tour .white-box:not(.always-visible)' ).show();
				} else {
					$( '.tour .white-box:not(.always-visible)' ).hide();
				}
												
			}).trigger( 'change' );
			
		});
	</script>
	<div class="row floorplan-archive-buttons tour">
		<div class="column">
			<label>Tour button</label>
			<p class="description">A typically-external link to schedule a tour. You can set a global link below.</p>
		</div>
		<div class="column">
			<div class="white-box always-visible">
				<label for="rentfetch_options_tour_button_enabled">
					<input type="checkbox" name="rentfetch_options_tour_button_enabled" id="rentfetch_options_tour_button_enabled" <?php checked( get_option( 'rentfetch_options_tour_button_enabled' ), true ); ?>>
					Enable the tour button
				</label>
			</div>
			<div class="white-box">
				<label for="rentfetch_options_tour_button_button_label">Button label</label>
				<input type="text" name="rentfetch_options_tour_button_button_label" id="rentfetch_options_tour_button_button_label" value="<?php echo esc_attr( get_option( 'rentfetch_options_tour_button_button_label' ) ); ?>">
			</div>
			<div class="white-box">
				<label for="rentfetch_options_tour_button_fallback_link">Link</label>
				<input type="url" name="rentfetch_options_tour_button_fallback_link" id="rentfetch_options_tour_button_fallback_link" value="<?php echo esc_attr( get_option( 'rentfetch_options_tour_button_fallback_link' ) ); ?>">
			</div>
		</div>
	</div>

	<?php
}
add_action( 'rent_fetch_do_settings_floorplans_floorplan_buttons', 'rent_fetch_settings_floorplans_floorplan_buttons' );

/**
 * Save the floorplan button settings
 */
function rent_fetch_save_settings_floorplan_buttons() {
	
	// Get the tab and section
	$tab = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();
	
	if ( $tab !== 'floorplans' || $section !== 'floorplan_buttons' )
		return;
	
	// Checkbox field - Enable the contact button
	$options_contact_button_enabled = isset( $_POST[ 'rentfetch_options_contact_button_enabled'] ) ? '1' : '0';
	update_option( 'rentfetch_options_contact_button_enabled', $options_contact_button_enabled );

	// Text field - Button label
	if ( isset( $_POST[ 'rentfetch_options_contact_button_button_label'] ) ) {
		$options_contact_button_button_label = sanitize_text_field( $_POST[ 'rentfetch_options_contact_button_button_label'] );
		update_option( 'rentfetch_options_contact_button_button_label', $options_contact_button_button_label );
	}

	// Text field - Link
	if ( isset( $_POST[ 'rentfetch_options_contact_button_link'] ) ) {
		$options_contact_button_link = sanitize_text_field( $_POST[ 'rentfetch_options_contact_button_link'] );
		update_option( 'rentfetch_options_contact_button_link', $options_contact_button_link );
	}

	// Checkbox field - Enable the availability button
	$options_availability_button_enabled = isset( $_POST[ 'rentfetch_options_availability_button_enabled'] ) ? '1' : '0';
	update_option( 'rentfetch_options_availability_button_enabled', $options_availability_button_enabled );

	// Text field - Button label
	if ( isset( $_POST[ 'rentfetch_options_availability_button_button_label'] ) ) {
		$options_availability_button_button_label = sanitize_text_field( $_POST[ 'rentfetch_options_availability_button_button_label'] );
		update_option( 'rentfetch_options_availability_button_button_label', $options_availability_button_button_label );
	}

	// Checkbox field - Enable the tour button
	$options_tour_button_enabled = isset( $_POST[ 'rentfetch_options_tour_button_enabled'] ) ? '1' : '0';
	update_option( 'rentfetch_options_tour_button_enabled', $options_tour_button_enabled );

	// Text field - Tour button label
	if ( isset( $_POST[ 'rentfetch_options_tour_button_button_label'] ) ) {
		$options_tour_button_button_label = sanitize_text_field( $_POST[ 'rentfetch_options_tour_button_button_label'] );
		update_option( 'rentfetch_options_tour_button_button_label', $options_tour_button_button_label );
	}
	
	// Text field - Tour button fallback link
	if ( isset( $_POST[ 'rentfetch_options_tour_button_fallback_link'] ) ) {
		$options_tour_button_fallback_link = sanitize_text_field( $_POST[ 'rentfetch_options_tour_button_fallback_link'] );
		update_option( 'rentfetch_options_tour_button_fallback_link', $options_tour_button_fallback_link );
	}
	
}
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_floorplan_buttons' );