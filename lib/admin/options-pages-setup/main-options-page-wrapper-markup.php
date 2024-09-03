<?php
/**
 * This file outputs the main options page wrapper markup.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Callback for the Rent Fetch options page
 */
function rentfetch_options_page_html() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';

	echo '<div class="wrap" id="rent-fetch-wrap-page">';

		$action = esc_url( admin_url( 'admin-post.php' ) );

		printf( '<form method="post" class="rent-fetch-options" action="%s">', esc_url( $action ) );
			echo ' <div class="top-right-submit">';
				submit_button();
			echo '</div>';

			echo '<h1>Rent Fetch Options</h1>';
			echo '<nav class="nav-tab-wrapper">';

				$active = ( 'general' === $tab ) ? 'nav-tab-active' : '';
				printf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( admin_url( 'admin.php?page=rentfetch-options' ) ), esc_html( $active ), esc_html( 'Sync Settings' ) );

				$active = ( 'maps' === $tab ) ? 'nav-tab-active' : '';
				printf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( admin_url( 'admin.php?page=rentfetch-options&tab=maps' ) ), esc_html( $active ), esc_html( 'Maps' ) );

				$active = ( 'properties' === $tab ) ? 'nav-tab-active' : '';
				printf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( admin_url( 'admin.php?page=rentfetch-options&tab=properties' ) ), esc_html( $active ), esc_html( 'Property Settings' ) );

				$active = ( 'floorplans' === $tab ) ? 'nav-tab-active' : '';
				printf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( admin_url( 'admin.php?page=rentfetch-options&tab=floorplans' ) ), esc_html( $active ), esc_html( 'Floor Plan Settings' ) );

				// TODO Removing this settings tab temporarily; we need to implement in a more flexible way.
				// $active = ( 'labels' === $tab ) ? 'nav-tab-active' : '';
				// printf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( admin_url( 'admin.php?page=rentfetch-options&tab=labels' ) ), esc_html( $active ), esc_html( 'Labels' ) );

			echo '</nav>';

			echo '<input type="hidden" name="action" value="rentfetch_process_form">';

			// * Create the nonce field
			wp_nonce_field( 'rentfetch_main_options_nonce_action', 'rentfetch_main_options_nonce_field' );

			rentfetch_settings_output_each_page_fields();

		echo '</form>';
	echo '</div>';
}

/**
 * Output the settings fields for each tab
 *
 * @return void
 */
function rentfetch_settings_output_each_page_fields() {

	$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';

	if ( 'general' === $tab ) {
		do_action( 'rentfetch_do_settings_general' );
	} elseif ( 'maps' === $tab ) {
		do_action( 'rentfetch_do_settings_maps' );
	} elseif ( 'properties' === $tab ) {
		do_action( 'rentfetch_do_settings_properties' );
	} elseif ( 'property-search' === $tab ) {
		do_action( 'rentfetch_do_settings_property_search' );
	} elseif ( 'property-archives' === $tab ) {
		do_action( 'rentfetch_do_settings_property_archives' );
	} elseif ( 'single-property-template' === $tab ) {
		do_action( 'rentfetch_do_settings_single_property_template' );
	} elseif ( 'floorplans' === $tab ) {
		do_action( 'rentfetch_do_settings_floorplans' );
	} elseif ( 'labels' === $tab ) {
		do_action( 'rentfetch_do_settings_labels' );
	} else {
		do_action( 'rentfetch_do_settings_general' );
	}
}

/**
 * Adds the properties settings section to the Rent Fetch settings page
 */
function rentfetch_settings_properties() {

	// set the tab to 'properties' if it's not set.
	$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'properties';

	// set the section to 'property-search' if it's not set.
	$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'property-search';

	echo '<section id="rent-fetch-property-settings-page" class="options-container">';

	echo '<ul class="rent-fetch-options-submenu">';

		$active = ( 'property-search' === $section ) ? 'tab-active' : '';
		printf( '<li><a href="?page=rentfetch-options&tab=properties&section=property-search" class="tab %s">Property Search</a></li>', esc_html( $active ) );

		$active = ( 'property-archives' === $section ) ? 'tab-active' : '';
		printf( '<li><a href="?page=rentfetch-options&tab=properties&section=property-archives" class="tab %s">Property Archives</a></li>', esc_html( $active ) );

		$active = ( 'property-single' === $section ) ? 'tab-active' : '';
		printf( '<li><a href="?page=rentfetch-options&tab=properties&section=property-single" class="tab %s">Property Single Template</a></li>', esc_html( $active ) );

	echo '</ul>';
	
	echo '<div class="container">';

	if ( 'property-search' === $section ) {
		do_action( 'rentfetch_do_settings_properties_property_search' );
	} elseif ( 'property-archives' === $section ) {
		do_action( 'rentfetch_do_settings_properties_property_archives' );
	} elseif ( 'property-single' === $section ) {
		do_action( 'rentfetch_do_settings_properties_property_single' );
	} else {
		do_action( 'rentfetch_do_settings_properties_property_search' );
	}

	echo '</div><!-- .container -->';
	echo '</section><!-- #rent-fetch-property-settings-page -->';

}
add_action( 'rentfetch_do_settings_properties', 'rentfetch_settings_properties' );

/**
 * Adds the floorplans settings section to the Rent Fetch settings page
 *
 * @return void.
 */
function rentfetch_settings_floorplans() {

	// set the tab to 'properties' if it's not set.
	$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'floorplans';

	// set the section to 'property-search' if it's not set.
	$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'floorplan-search';

	echo '<section id="rent-fetch-floorplans-page" class="options-container">';
	echo '<ul class="rent-fetch-options-submenu">';

		$active = ( 'floorplan-search' === $section ) ? 'tab-active' : '';
		printf( '<li><a href="?page=rentfetch-options&tab=floorplans&section=floorplan-search" class="tab %s">Floor Plan Settings</a></li>', esc_html( $active ) );

		// $active = ( 'floorplan-buttons' === $section ) ? 'tab-active' : '';
		// printf( '<li><a href="?page=rentfetch-options&tab=floorplans&section=floorplan-buttons" class="tab %s">Floorplan Buttons</a></li>', esc_html( $active ) );

	echo '</ul>';
	echo '<div class="container">';

	if ( 'floorplan-search' === $section ) {
		do_action( 'rentfetch_do_settings_floorplans_floorplan_search' );
	} elseif ( 'floorplan-buttons' === $section ) {
		// do_action( 'rentfetch_do_settings_floorplans_floorplan_buttons' );
		do_action( 'rentfetch_do_settings_floorplans_floorplan_search' );
	} else {
		do_action( 'rentfetch_do_settings_floorplans_floorplan_search' );
	}
	echo '</div><!-- .container -->';
	echo '</section><!-- #rent-fetch-floorplans-page -->';
}
add_action( 'rentfetch_do_settings_floorplans', 'rentfetch_settings_floorplans' );
