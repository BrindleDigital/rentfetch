<?php
/**
 * This file sets up the shortcodes page in the admin area.
 *
 * @package rentfetch
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

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
	<script>
		jQuery(document).ready(function ($) {

			// Get all .shortcode elements
			const shortcodes = document.querySelectorAll('.shortcode');

			// Add event listener to each .shortcode element
			shortcodes.forEach(shortcode => {
				shortcode.addEventListener('click', () => {
					// Create a new textarea element to hold the full shortcode markup
					const textarea = document.createElement('textarea');
					textarea.value = shortcode.textContent;

					// Append the textarea to the document and select its contents
					document.body.appendChild(textarea);
					textarea.select();

					// Copy the selected content to the clipboard
					document.execCommand('copy');

					// Remove the textarea from the document
					document.body.removeChild(textarea);

					// Add the .copied class to the clicked .shortcode element
					shortcode.classList.add('copied');

					// Remove the .copied class after 5 seconds
					setTimeout(() => {
						shortcode.classList.remove('copied');
					}, 5000);
				});
			});
		});


	</script>
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
				<h3>Default search (with the map)</h3>
				<p>The main properties search can be rendered using the default shortcode, which will create a side-by-side layout
					with the properties and search filters next to the map, or you can render each component individually to make
					the layout work however you'd like it to.</p>
				<p>This one includes everything; just use this and you're done. This will attempt to force itself to be full-width
					on the page regardless of your theme styles.</p>
				<p><span class="shortcode"><!-- wp:shortcode -->[rentfetch_propertysearch]<!-- /wp:shortcode --></span></p>
				<h4>Individual components</h4>
				<p>
					Use these individually to arrange various components. It's quite likely, using these, that you'll need to write
					some styles to position them the way you'd like on the page.
				</p>
				<p>
					<span class="shortcode"><!-- wp:shortcode -->[rentfetch_propertysearchmap]<!-- /wp:shortcode --></span>
					<span class="shortcode"><!-- wp:shortcode -->[rentfetch_propertysearchfilters]<!-- /wp:shortcode --></span>
					<span class="shortcode"><!-- wp:shortcode -->[rentfetch_propertysearchresults]<!-- /wp:shortcode --></span>
				</p>
				<h3>Properties grid</h3>
				<p>This layout ignores availability, and is most suitable for smaller ownership groups with 5-20 properties.
					<strong>We strongly recommend using this somewhere it can span the full width of the screen.</strong>
				</p>
				<p><span class="shortcode"><!-- wp:shortcode -->[rentfetch_properties]<!-- /wp:shortcode --></span></p>
			</div>
			<div class="separator"></div>
			<div class="section">
				<h2>Floorplans</h2>
				<h3>Floorplans search</h3>
				<p>You can use a parameter to customize by property, so that only a given property (or multiple properties) will
					display:</p>
				<p>
					<span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplansearch]<!-- /wp:shortcode --></span>
					<span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplansearch
						property_id=p1234]<!-- /wp:shortcode --></span>
					<span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplansearch
						property_id="p1234,p2345"]<!-- /wp:shortcode --></span>
				</p>
				<p>Available parameters for sort are: availability, beds, baths, pricelow, pricehigh, alphabetical, and menu_order </p>
				<h4>Individual components</h4>
				<p>You can restructure this if you'd like, so that you can arrange and restyle the filters and position them where you'd like them.</p>
				<p>
					<span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplansearchfilters]<!-- /wp:shortcode --></span>
					<span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplansearchresults]<!-- /wp:shortcode --></span>
				</p>
				<h3>Floorplans grid</h3>
				<p>This layout ignores availability, and is useful for displaying arbitrary groupings of floorplans. Several
					available parameters are shown below:</p>
				<p>
					<span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplans]<!-- /wp:shortcode --></span>
					<span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplans property_id="p1234,p5678"
						beds=2,3]<!-- /wp:shortcode --></span>
					<span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplans sort=beds]<!-- /wp:shortcode --></span>
				</p>
				<p>Available parameters for sort are: availability, beds, baths, pricelow, pricehigh, alphabetical, and menu_order </p>
			</div>
		</div>
	</section>
	<?php
}
add_action('rentfetch_do_documentation_shortcodes', 'rentfetch_documentation_shortcodes');