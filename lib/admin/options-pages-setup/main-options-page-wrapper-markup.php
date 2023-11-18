<?php

/**
 * Callback for the Rent Fetch options page
 */
function rent_fetch_options_page_html() {
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
				<a href="?page=rent_fetch_options" class="nav-tab<?php if (!isset($_GET['tab']) || $_GET['tab'] === 'general') { echo ' nav-tab-active'; } ?>">General</a>
				<a href="?page=rent_fetch_options&tab=maps" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'maps') { echo ' nav-tab-active'; } ?>">Maps</a>
				<a href="?page=rent_fetch_options&tab=properties" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'properties') { echo ' nav-tab-active'; } ?>">Properties</a>
				<a href="?page=rent_fetch_options&tab=floorplans" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'floorplans') { echo ' nav-tab-active'; } ?>">Floorplans</a>
				<a href="?page=rent_fetch_options&tab=labels" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'labels') { echo ' nav-tab-active'; } ?>">Labels</a>
			</nav>
		
			<input type="hidden" name="action" value="rent_fetch_process_form">
			<?php wp_nonce_field( 'rent_fetch_nonce', 'rent_fetch_form_nonce' ); ?>
			<?php $rent_fetch_options_nonce = wp_create_nonce( 'rent_fetch_options_nonce' );  ?>
			
			<?php
			
			if ( !isset($_GET['tab']) || $_GET['tab'] === 'general') {
				do_action( 'rent_fetch_do_settings_general' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'maps') {
				do_action( 'rent_fetch_do_settings_maps' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'properties') {
				do_action( 'rent_fetch_do_settings_properties' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'property_search') {
				do_action( 'rent_fetch_do_settings_property_search' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'property_archives') {
				do_action( 'rent_fetch_do_settings_property_archives' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'single_property_template') {
				do_action( 'rent_fetch_do_settings_single_property_template' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'floorplans') {
				do_action( 'rent_fetch_do_settings_floorplans' );
			} elseif (isset($_GET['tab']) && $_GET['tab'] === 'labels') {
				do_action( 'rent_fetch_do_settings_labels' );
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
function rent_fetch_settings_properties() {    
	?>
	<ul class="rent-fetch-options-submenu">
		<li><a href="?page=rent_fetch_options&tab=properties&section=property_search" class="tab<?php if (!isset($_GET['section']) || $_GET['section'] === 'property_search') { echo ' tab-active'; } ?>">Property Search</a></li>
		<li><a href="?page=rent_fetch_options&tab=properties&section=property_archives" class="tab<?php if ( isset( $_GET['section']) && $_GET['section'] === 'property_archives') { echo ' tab-active'; } ?>">Property Archives</a></li>
		<li><a href="?page=rent_fetch_options&tab=properties&section=property_single" class="tab<?php if ( isset( $_GET['section']) && $_GET['section'] === 'property_single') { echo ' tab-active'; } ?>">Property Single Template</a></li>
	</ul>    
	<?php
	if ( !isset($_GET['section']) || $_GET['section'] === 'property_search') {
		do_action( 'rent_fetch_do_settings_properties_property_search' );
	} elseif (isset($_GET['section']) && $_GET['section'] === 'property_archives') {
		do_action( 'rent_fetch_do_settings_properties_property_archives' );
	} elseif (isset($_GET['section']) && $_GET['section'] === 'property_single') {
		do_action( 'rent_fetch_do_settings_properties_property_single' );
	}
}
add_action( 'rent_fetch_do_settings_properties', 'rent_fetch_settings_properties' );

/**
 * Adds the floorplans settings section to the Rent Fetch settings page
 */
function rent_fetch_settings_floorplans() {
	?>
	<ul class="rent-fetch-options-submenu">
		<li><a href="?page=rent_fetch_options&tab=floorplans&section=floorplan_search" class="tab<?php if (!isset($_GET['section']) || $_GET['section'] === 'floorplan_search') { echo ' tab-active'; } ?>">Floorplan Search</a></li>
		<li><a href="?page=rent_fetch_options&tab=floorplans&section=floorplan_buttons" class="tab<?php if ( isset( $_GET['section']) && $_GET['section'] === 'floorplan_buttons') { echo ' tab-active'; } ?>">Floorplan Buttons</a></li>
	</ul>    
	<?php
	if ( !isset($_GET['section']) || $_GET['section'] === 'floorplan_search') {
		do_action( 'rent_fetch_do_settings_floorplans_floorplan_search' );
	} elseif (isset($_GET['section']) && $_GET['section'] === 'floorplan_buttons') {
		do_action( 'rent_fetch_do_settings_floorplans_floorplan_buttons' );
	}
}
add_action( 'rent_fetch_do_settings_floorplans', 'rent_fetch_settings_floorplans' );