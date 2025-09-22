<?php
/**
 * This file sets up the shortcodes page in the admin area.
 *
 * @package rentfetch
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/../shortcode-documentation.php';

/**
 * The html for the shortcodes page.
 *
 * @return void.
 */
function rentfetch_shortcodes_page_html()
{
	if (!current_user_can('manage_options')) {
		return;
	}


	add_filter('admin_footer_text', 'rentfetch_override_admin_footer');
	add_filter('update_footer', function () { echo ''; });

	?>
	<?php rentfetch_shortcode_copy_script(); ?>
	<?php

	echo '<div class="wrap">';
	echo '<h1>Rent Fetch Shortcodes</h1>';
	echo '<p>Rent Fetch includes a number of shortcodes that can be used wherever you\'d like on your site. <strong>Click any of them below to copy them.</strong></p>';
	do_action('rentfetch_do_documentation_shortcodes');
	echo '</div>';
}

/**
 * Output the shortcodes content.
 *
 * @return void.
 */
function rentfetch_documentation_shortcodes()
{
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
// add_action('rentfetch_do_documentation_shortcodes', 'rentfetch_documentation_shortcodes');