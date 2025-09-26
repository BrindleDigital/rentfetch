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

	add_filter('admin_footer_text', 'rentfetch_override_admin_footer');
	add_filter('update_footer', function () { echo ''; });

	$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';

	echo '<div class="wrap" id="rent-fetch-wrap-page">';

		$action = esc_url( admin_url( 'admin-post.php' ) );

		printf( '<form method="post" class="rent-fetch-options" action="%s">', esc_url( $action ) );
		
			echo '<h1 style="display: none;">RentFetch</h1>';
			
			echo '<section class="nav-container">';
				echo '<a class="rentfetch-logo-link" href="/wp-admin/admin.php?page=rentfetch-options"><img class="rentfetch-logo" src="' . RENTFETCH_PATH . '/images/logo.svg' . '" alt="logo" /></a>';
				
				echo '<nav class="nav-tab-wrapper">';

					$active = ( 'general' === $tab ) ? 'nav-tab-active' : '';
					printf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( admin_url( 'admin.php?page=rentfetch-options' ) ), esc_html( $active ), esc_html( 'General' ) );

					$active = ( 'floorplans' === $tab ) ? 'nav-tab-active' : '';
					printf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( admin_url( 'admin.php?page=rentfetch-options&tab=floorplans' ) ), esc_html( $active ), esc_html( 'Floor Plan Settings' ) );
					
					$active = ( 'properties' === $tab ) ? 'nav-tab-active' : '';
					printf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( admin_url( 'admin.php?page=rentfetch-options&tab=properties' ) ), esc_html( $active ), esc_html( 'Property Settings' ) );

					// TODO Removing this settings tab temporarily; we need to implement in a more flexible way.
					// $active = ( 'labels' === $tab ) ? 'nav-tab-active' : '';
					// printf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( admin_url( 'admin.php?page=rentfetch-options&tab=labels' ) ), esc_html( $active ), esc_html( 'Labels' ) );

				echo '</nav>';
			echo '</section>';

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

	// set the section to 'property-maps' if it's not set.
	$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'property-maps';

	echo '<section id="rent-fetch-property-settings-page" class="options-container">';
	
		echo '<div class="rent-fetch-options-nav-wrap">';
			echo '<div class="rent-fetch-options-sticky-wrap">';
				// add a wordpress save button here
				submit_button();
			
				echo '<ul class="rent-fetch-options-submenu">';

					$active = ( 'property-maps' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=properties&section=property-maps" class="tab %s">Google maps API</a></li>', esc_html( $active ) );

					$active = ( 'property-search' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=properties&section=property-search" class="tab %s">Property search</a></li>', esc_html( $active ) );

					$active = ( 'property-archives' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=properties&section=property-archives" class="tab %s">Property archives</a></li>', esc_html( $active ) );

					$active = ( 'property-single' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=properties&section=property-single" class="tab %s">Property single</a></li>', esc_html( $active ) );

					$active = ( 'property-settings-embed' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=properties&section=property-settings-embed" class="tab %s">Property shortcodes</a></li>', esc_html( $active ) );

				echo '</ul>';
				
			echo '</div>';
		echo '</div>';
		
		$containerDiv = '<div class="container">';

		if ('property-settings-embed' === $section) {
			$containerDiv = '<div class="container shortcodes wide">';
		}

		echo $containerDiv;

		if ( 'property-maps' === $section ) {
			do_action( 'rentfetch_do_settings_properties_property_maps' );
		} elseif ( 'property-search' === $section ) {
			do_action( 'rentfetch_do_settings_properties_property_search' );
		} elseif ( 'property-archives' === $section ) {
			do_action( 'rentfetch_do_settings_properties_property_archives' );
		} elseif ( 'property-single' === $section ) {
			do_action( 'rentfetch_do_settings_properties_property_single' );
		} elseif ( 'property-settings-embed' === $section ) {
			do_action( 'rentfetch_do_settings_properties_property_embed' );
		} else {
			do_action( 'rentfetch_do_settings_properties_property_maps' );
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

	// set the section to 'floorplan-search' if it's not set.
	$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'floorplan-search';

	echo '<section id="rent-fetch-floorplans-page" class="options-container">';	
		echo '<div class="rent-fetch-options-nav-wrap">';
			echo '<div class="rent-fetch-options-sticky-wrap">';
				// add a wordpress save button here
				submit_button();
				
				echo '<ul class="rent-fetch-options-submenu">';

					$active = ( 'floorplan-search' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=floorplans&section=floorplan-search" class="tab %s">Floor plan search</a></li>', esc_html( $active ) );

					$active = ( 'floorplan-display' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=floorplans&section=floorplan-display" class="tab %s">Floor plan display</a></li>', esc_html( $active ) );

					$active = ( 'floorplan-buttons' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=floorplans&section=floorplan-buttons" class="tab %s">Floor plan buttons</a></li>', esc_html( $active ) );

					$active = ( 'floorplan-embed' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=floorplans&section=floorplan-embed" class="tab %s">Floor plan shortcodes</a></li>', esc_html( $active ) );

				echo '</ul>';
			echo '</div>';
		echo '</div>';

		$containerDiv = '<div class="container">';

		if ('floorplan-embed' === $section) {
			$containerDiv = '<div class="container shortcodes">';
		}

		echo $containerDiv;

		if ( 'floorplan-search' === $section ) {
			do_action( 'rentfetch_do_settings_floorplans_floorplan_search' );
		} elseif ( 'floorplan-display' === $section ) {
			do_action( 'rentfetch_do_settings_floorplans_floorplan_display' );
		} elseif ( 'floorplan-buttons' === $section ) {
			do_action( 'rentfetch_do_settings_floorplans_floorplan_buttons_page' );
		} elseif ( 'floorplan-embed' === $section ) {
			do_action( 'rentfetch_do_settings_floorplans_floorplan_embed' );
		} else {
			do_action( 'rentfetch_do_settings_floorplans_floorplan_search' );
		}
		echo '</div><!-- .container -->';
	echo '</section><!-- #rent-fetch-floorplans-page -->';
}
add_action( 'rentfetch_do_settings_floorplans', 'rentfetch_settings_floorplans' );
