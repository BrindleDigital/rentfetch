<?php
/**
 * This file sets up the Properties shortcodes sub-page in the admin area.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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
			<h3>Default layout</h3>
			<p>
			This one includes everything; just use this and you're done. This will attempt to force itself to be full-width on
			the page regardless of your theme styles. Copy and paste the default shortcode within your page builder, which
			will create a side-by-side layout with the properties and search filters next to a map.
			</p>
			<p><span class="shortcode"><!-- wp:shortcode -->[rentfetch_propertysearch]<!-- /wp:shortcode --></span></p>
			<p>This shortcode only takes one parameter, and it's a complete list of propertyids to limit what can possibly show in the search.</p>
			<p><span class="shortcode"><!-- wp:shortcode -->[rentfetch_propertysearch propertyids="propertyid1,propertyid2"]<!-- /wp:shortcode --></span></p>
			<h3>Individual components</h3>
			<p>
			Use these individually to arrange various components. It's quite likely, using these, that you'll need to write
			some styles to position them the way you'd like on the page.
			</p>
			<p>
			<span class="shortcode"><!-- wp:shortcode -->[rentfetch_propertysearchmap]<!-- /wp:shortcode --></span>
			<span class="shortcode"><!-- wp:shortcode -->[rentfetch_propertysearchfilters]<!-- /wp:shortcode --></span>
			<span class="shortcode"><!-- wp:shortcode -->[rentfetch_propertysearchresults]<!-- /wp:shortcode --></span>
			</p>
		</div>
	</div>
	<div class="row">
		<div class="section">
			<h2>Properties grid</h2>
			<p>
			This layout ignores availability and shows all properties in a grid, without a map view. We strongly recommend
			using this somewhere it can span the full width of the screen.
			</p>
			<p><span class="shortcode"><!-- wp:shortcode -->[rentfetch_properties]<!-- /wp:shortcode --></span></p>
			<p>Available parameters: propertyids, city, posts_per_page</p>		
		</div>
	</div>
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
}
add_action( 'rentfetch_do_settings_properties_property_embed', 'rentfetch_settings_properties_property_embed' );