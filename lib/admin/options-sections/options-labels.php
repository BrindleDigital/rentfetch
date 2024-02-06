<?php
/**
 * This file includes the options for the labels
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Adds the labels settings section to the Rent Fetch settings page
 */
function rentfetch_settings_labels() {
	?>
		
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_bedroom_numbers_0_bedroom">0 Bedroom</label>
		</div>
		<div class="column">
			<input type="text" name="rentfetch_options_bedroom_numbers_0_bedroom" id="rentfetch_options_bedroom_numbers_0_bedroom" value="<?php echo esc_attr( get_option( 'rentfetch_options_bedroom_numbers_0_bedroom', 'Studio' ) ); ?>">
		</div>
	</div>
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_bedroom_numbers_1_bedroom">1 Bedroom</label>
		</div>
		<div class="column">
			<input type="text" name="rentfetch_options_bedroom_numbers_1_bedroom" id="rentfetch_options_bedroom_numbers_1_bedroom" value="<?php echo esc_attr( get_option( 'rentfetch_options_bedroom_numbers_1_bedroom', 'One Bedroom' ) ); ?>">
		</div>
	</div>
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_bedroom_numbers_2_bedroom">2 Bedroom</label>
		</div>
		<div class="column">
			<input type="text" name="rentfetch_options_bedroom_numbers_2_bedroom" id="rentfetch_options_bedroom_numbers_2_bedroom" value="<?php echo esc_attr( get_option( 'rentfetch_options_bedroom_numbers_2_bedroom', 'Two Bedroom' ) ); ?>">
		</div>
	</div>
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_bedroom_numbers_3_bedroom">3 Bedroom</label>
		</div>
		<div class="column">
			<input type="text" name="rentfetch_options_bedroom_numbers_3_bedroom" id="rentfetch_options_bedroom_numbers_3_bedroom" value="<?php echo esc_attr( get_option( 'rentfetch_options_bedroom_numbers_3_bedroom', 'Three Bedroom' ) ); ?>">            
		</div>
	</div>
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_bedroom_numbers_4_bedroom">4 Bedroom</label>
		</div>
		<div class="column">
			<input type="text" name="rentfetch_options_bedroom_numbers_4_bedroom" id="rentfetch_options_bedroom_numbers_4_bedroom" value="<?php echo esc_attr( get_option( 'rentfetch_options_bedroom_numbers_4_bedroom', 'Four Bedroom' ) ); ?>">
		</div>
	</div>
	<div class="row">
		<div class="column">
			<label for="rentfetch_options_bedroom_numbers_5_bedroom">5 Bedroom</label>
		</div>
		<div class="column">
			<input type="text" name="rentfetch_options_bedroom_numbers_5_bedroom" id="rentfetch_options_bedroom_numbers_5_bedroom" value="<?php echo esc_attr( get_option( 'rentfetch_options_bedroom_numbers_5_bedroom', 'Five Bedroom' ) ); ?>">
		</div>
	</div>
	<?php
}
add_action( 'rentfetch_do_settings_labels', 'rentfetch_settings_labels' );

/**
 * Save the label settings
 */
function rentfetch_save_settings_labels() {

	// Get the tab and section.
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ( 'labels' !== $tab || ! empty( $section ) ) {
		return;
	}

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_main_options_nonce_action' ) ) {
		die( 'Security check failed' );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_bedroom_numbers_0_bedroom'] ) ) {
		$options_bedroom_numbers_0_bedroom = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_bedroom_numbers_0_bedroom'] ) );
		update_option( 'rentfetch_options_bedroom_numbers_0_bedroom', $options_bedroom_numbers_0_bedroom );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_bedroom_numbers_1_bedroom'] ) ) {
		$options_bedroom_numbers_1_bedroom = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_bedroom_numbers_1_bedroom'] ) );
		update_option( 'rentfetch_options_bedroom_numbers_1_bedroom', $options_bedroom_numbers_1_bedroom );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_bedroom_numbers_2_bedroom'] ) ) {
		$options_bedroom_numbers_2_bedroom = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_bedroom_numbers_2_bedroom'] ) );
		update_option( 'rentfetch_options_bedroom_numbers_2_bedroom', $options_bedroom_numbers_2_bedroom );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_bedroom_numbers_3_bedroom'] ) ) {
		$options_bedroom_numbers_3_bedroom = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_bedroom_numbers_3_bedroom'] ) );
		update_option( 'rentfetch_options_bedroom_numbers_3_bedroom', $options_bedroom_numbers_3_bedroom );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_bedroom_numbers_4_bedroom'] ) ) {
		$options_bedroom_numbers_4_bedroom = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_bedroom_numbers_4_bedroom'] ) );
		update_option( 'rentfetch_options_bedroom_numbers_4_bedroom', $options_bedroom_numbers_4_bedroom );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_bedroom_numbers_5_bedroom'] ) ) {
		$options_bedroom_numbers_5_bedroom = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_bedroom_numbers_5_bedroom'] ) );
		update_option( 'rentfetch_options_bedroom_numbers_5_bedroom', $options_bedroom_numbers_5_bedroom );
	}
}
add_action( 'rentfetch_save_settings', 'rentfetch_save_settings_labels' );
