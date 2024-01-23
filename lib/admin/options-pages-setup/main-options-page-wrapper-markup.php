<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Callback for the Rent Fetch options page
 */
function rentfetch_options_page_html() {
	if (!current_user_can('manage_options')) {
		return;
	}
	?>
	<div class="wrap">
		<form method="post" class="rent-fetch-options" action="<?php echo esc_url( admin_url( 'admin-post.php' ) );  ?>">
			<div class="top-right-submit">
				<?php submit_button(); ?>
			</div>
			<h1>Rent Fetch Options</h1>
			<nav class="nav-tab-wrapper">
				<a href="?page=rentfetch-options" class="nav-tab<?php if (!isset($_GET['tab']) || $_GET['tab'] === 'general') { echo ' nav-tab-active'; } ?>">General</a>
				<a href="?page=rentfetch-options&tab=maps" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'maps') { echo ' nav-tab-active'; } ?>">Maps</a>
				<a href="?page=rentfetch-options&tab=properties" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'properties') { echo ' nav-tab-active'; } ?>">Properties</a>
				<a href="?page=rentfetch-options&tab=floorplans" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'floorplans') { echo ' nav-tab-active'; } ?>">Floorplans</a>
				<a href="?page=rentfetch-options&tab=labels" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'labels') { echo ' nav-tab-active'; } ?>">Labels</a>
			</nav>
		
			<input type="hidden" name="action" value="rentfetch_process_form">
			<?php wp_nonce_field( 'rentfetch_nonce', 'rentfetch_form_nonce' ); ?>
			<?php $rentfetch_options_nonce = wp_create_nonce( 'rentfetch_options_nonce' );  ?>
			
			<?php
			
			if ( !isset($_GET['tab']) || $_GET['tab'] === 'general') {
				do_action( 'rentfetch_do_settings_general' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'maps') {
				do_action( 'rentfetch_do_settings_maps' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'properties') {
				do_action( 'rentfetch_do_settings_properties' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'property-search') {
				do_action( 'rentfetch_do_settings_property_search' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'property-archives') {
				do_action( 'rentfetch_do_settings_property_archives' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'single-property-template') {
				do_action( 'rentfetch_do_settings_single_property_template' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'floorplans') {
				do_action( 'rentfetch_do_settings_floorplans' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'labels') {
				do_action( 'rentfetch_do_settings_labels' );
			} else {

			}
			
			submit_button(); 
			?>
			
		</form>
	</div>

	<?php
}

/**
 * Adds the properties settings section to the Rent Fetch settings page
 */
function rentfetch_settings_properties() {    
	?>
	<ul class="rent-fetch-options-submenu">
		<li><a href="?page=rentfetch-options&tab=properties&section=property-search" class="tab<?php if (!isset($_GET['section']) || $_GET['section'] === 'property-search') { echo ' tab-active'; } ?>">Property Search</a></li>
		<li><a href="?page=rentfetch-options&tab=properties&section=property-archives" class="tab<?php if ( isset( $_GET['section']) && $_GET['section'] === 'property-archives') { echo ' tab-active'; } ?>">Property Archives</a></li>
		<li><a href="?page=rentfetch-options&tab=properties&section=property-single" class="tab<?php if ( isset( $_GET['section']) && $_GET['section'] === 'property-single') { echo ' tab-active'; } ?>">Property Single Template</a></li>
	</ul>    
	<?php
	if ( !isset($_GET['section']) || $_GET['section'] === 'property-search') {
		do_action( 'rentfetch_do_settings_properties_property_search' );
	} elseif (isset($_GET['section']) && $_GET['section'] === 'property-archives') {
		do_action( 'rentfetch_do_settings_properties_property_archives' );
	} elseif (isset($_GET['section']) && $_GET['section'] === 'property-single') {
		do_action( 'rentfetch_do_settings_properties_property_single' );
	}
}
add_action( 'rentfetch_do_settings_properties', 'rentfetch_settings_properties' );

/**
 * Adds the floorplans settings section to the Rent Fetch settings page
 */
function rentfetch_settings_floorplans() {
	?>
	<ul class="rent-fetch-options-submenu">
		<li><a href="?page=rentfetch-options&tab=floorplans&section=floorplan-search" class="tab<?php if (!isset($_GET['section']) || $_GET['section'] === 'floorplan-search') { echo ' tab-active'; } ?>">Floorplan Search</a></li>
		<li><a href="?page=rentfetch-options&tab=floorplans&section=floorplan-buttons" class="tab<?php if ( isset( $_GET['section']) && $_GET['section'] === 'floorplan-buttons') { echo ' tab-active'; } ?>">Floorplan Buttons</a></li>
	</ul>
	<?php
	if ( !isset($_GET['section']) || $_GET['section'] === 'floorplan-search') {
		do_action( 'rentfetch_do_settings_floorplans_floorplan_search' );
	} elseif (isset($_GET['section']) && $_GET['section'] === 'floorplan-buttons') {
		do_action( 'rentfetch_do_settings_floorplans_floorplan_buttons' );
	}
}
add_action( 'rentfetch_do_settings_floorplans', 'rentfetch_settings_floorplans' );