<?php
/**
 * This file sets up the shortcodes page in the admin area.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/../shortcode-documentation.php';

/**
 * Render the floor plan shortcode reference page without settings controls.
 */
function rentfetch_floorplan_shortcodes_page_html() {
	rentfetch_shortcode_reference_page_html( 'floorplans' );
}

/**
 * Render the property shortcode reference page without settings controls.
 */
function rentfetch_property_shortcodes_page_html() {
	rentfetch_shortcode_reference_page_html( 'properties' );
}

/**
 * Render the shared reference-page layout around the existing shortcode content.
 *
 * @param string $type Reference content type: properties or floorplans.
 */
function rentfetch_shortcode_reference_page_html( $type ) {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	if ( ! in_array( $type, array( 'properties', 'floorplans' ), true ) ) {
		return;
	}

	$is_property = 'properties' === $type;
	$page_slug   = $is_property ? 'rentfetch-property-shortcodes' : 'rentfetch-floorplan-shortcodes';
	$section_id  = $is_property ? 'rent-fetch-property-settings-page' : 'rent-fetch-floorplans-page';

	add_filter( 'admin_footer_text', 'rentfetch_override_admin_footer' );

	echo '<div class="wrap rentfetch-shortcode-reference" id="rent-fetch-wrap-page">';
	echo '<h1 class="screen-reader-text">Rent Fetch Shortcodes</h1>';
	echo '<header class="nav-container">';
	printf( '<a class="rentfetch-logo-link" href="%s"><img class="rentfetch-logo" src="%s" alt="Rent Fetch" /></a>', esc_url( admin_url( 'admin.php?page=' . $page_slug ) ), esc_url( RENTFETCH_PATH . 'images/logo.svg' ) );
	echo '<p class="rentfetch-reference-label">Shortcode reference</p>';
	echo '</header>';
	printf( '<section id="%s" class="options-container">', esc_attr( $section_id ) );
	echo '<aside class="rent-fetch-options-nav-wrap" aria-label="Rent Fetch settings access">';
	echo '<div class="rent-fetch-options-sticky-wrap">';
	echo '<span class="dashicons dashicons-lock" aria-hidden="true"></span>';
	echo '<h2>Settings access</h2>';
	echo '<p>If you need access to the Rent Fetch settings, please request administrative site access from a site administrator.</p>';
	echo '</div></aside>';
	echo '<div class="container shortcodes shortcodes-container">';
	if ( $is_property ) {
		rentfetch_settings_properties_property_embed();
	} else {
		rentfetch_settings_floorplans_floorplan_embed();
	}
	echo '</div></section></div>';
}

/**
 * The html for the shortcodes page.
 *
 * @return void.
 */
function rentfetch_shortcodes_page_html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	add_filter( 'admin_footer_text', 'rentfetch_override_admin_footer' );
	add_filter(
		'update_footer',
		function () {
			echo '';
		}
	);

	?>
	<?php rentfetch_shortcode_copy_script(); ?>
	<?php

	echo '<div class="wrap">';
	echo '<h1>Rent Fetch Shortcodes</h1>';
	echo '<p>Rent Fetch includes a number of shortcodes that can be used wherever you\'d like on your site. <strong>Click any of them below to copy them.</strong></p>';
	do_action( 'rentfetch_do_documentation_shortcodes' );
	echo '</div>';
}

/**
 * Output the shortcodes content.
 *
 * @return void.
 */
function rentfetch_documentation_shortcodes() {
	?>
	<section id="rent-fetch-shortcodes-page" class="shortcodes-container">
		<div class="row">
			<div class="section" style="padding-bottom: 20px;">
				<h2>Properties</h2>
				<h3>Property Search</h3>
				<?php rentfetch_property_search_shortcode_docs(); ?>
				<h3>Properties Grid</h3>
				<?php rentfetch_properties_grid_shortcode_docs(); ?>
				<?php rentfetch_property_components_shortcode_docs(); ?>
			</div>
			<div class="separator"></div>
			<div class="section">
				<h2>Floorplans</h2>
				<?php rentfetch_floorplans_shortcode_docs(); ?>
			</div>
		</div>
	</section>
	<?php
}
