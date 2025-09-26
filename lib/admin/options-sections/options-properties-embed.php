<?php
/**
 * This file sets up the properties shortcodes sub-page in the admin area.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/../shortcode-documentation.php';

/**
 * Output the embed copy-pastable links for the properties shortcodes.
 *
 * @return  void
 */
function rentfetch_settings_properties_property_embed() {
	?>
	<div class="header">
	<h2 class="title">Property Shortcodes</h2>
	<p class="description" style="margin-bottom: 24px;">WordPress shortcodes allow users to perform certain actions as well as display predefined items on a site. The Rent Fetch shortcode is the method used to display properties and floor plans on your website.</p>
	<p class="description">The shortcode can be used anywhere within WordPress where shortcodes are supported. For most users, this will primarily be within the content of a WordPress post or page. Shortcodes are added when you use a standard WordPress editor to add the form to the page.</p>
	</div>

	<div class="row">
		<div class="section">
			<h2>Property Search</h2>
			<?php rentfetch_property_search_shortcode_docs(); ?>
		</div>
	</div>
	<div class="row">
		<div class="section">
			<h2>Properties grid</h2>
			<?php rentfetch_properties_grid_shortcode_docs(); ?>
		</div>
	</div>
	<div class="row">
		<div class="section">
			<h2>Property Components</h2>
			<?php rentfetch_property_components_shortcode_docs(); ?>
		</div>
	</div>
	<?php rentfetch_shortcode_copy_script(); ?>
	<?php
}
add_action( 'rentfetch_do_settings_properties_property_embed', 'rentfetch_settings_properties_property_embed' );