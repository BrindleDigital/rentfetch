<?php
/**
 * Shared functions for shortcode documentation.
 *
 * @package rentfetch
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Output the property search shortcode documentation.
 *
 * @return void.
 */
function rentfetch_property_search_shortcode_docs()
{
	?>
	<p><strong>We strongly recommend using this somewhere it can span the full width of the screen.</strong></p>
	<p>The main properties search can be rendered using the default shortcode, which will create a side-by-side layout
		with the properties and search filters next to the map, or you can render each component individually to make
		the layout work however you'd like it to.</p>
	<p>This one includes everything; just use this and you're done. This will attempt to force itself to be full-width
		on the page regardless of your theme styles.</p>
	<p><span class="shortcode"><!-- wp:shortcode -->[rentfetch_propertysearch]<!-- /wp:shortcode --></span></p>
	<p>This shortcode only takes one parameter, and it's a complete list of propertyids to limit what can possibly show in the search.</p>
	<p><span class="shortcode"><!-- wp:shortcode -->[rentfetch_propertysearch propertyids="propertyid1,propertyid2"]<!-- /wp:shortcode --></span></p>
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
	<?php
}

/**
 * Output the properties grid shortcode documentation.
 *
 * @return void.
 */
function rentfetch_properties_grid_shortcode_docs()
{
	?>
	<p>This layout ignores availability and shwos all properties in a grid, without a map view. We strongly recommend
		using this somewhere it can span the full width of the screen.</p>
	<p><span class="shortcode"><!-- wp:shortcode -->[rentfetch_properties]<!-- /wp:shortcode --></span></p>
	<p>Available parameters: propertyids, city, posts_per_page</p>
	<?php
}

/**
 * Output the property components shortcode documentation.
 *
 * @return void.
 */
function rentfetch_property_components_shortcode_docs()
{
	?>
	<h3>Property components</h3>
	<p>Use these shortcodes to display individual pieces of property information anywhere on your site. These work great for headers, footers, or custom layouts.</p>
	<p><span class="shortcode"><!-- wp:shortcode -->[rentfetch_property_info info="title"]<!-- /wp:shortcode --></span></p>
	<p>The <code>info</code> parameter determines what information to display. Available options:</p>
	<ul>
		<li><code>title</code> - Property title</li>
		<li><code>address</code> - Full street address</li>
		<li><code>city</code> - City name</li>
		<li><code>state</code> - State abbreviation</li>
		<li><code>zipcode</code> - ZIP code</li>
		<li><code>location</code> - City, State ZIP</li>
		<li><code>city_state</code> - City, State</li>
		<li><code>phone</code> - Phone number</li>
		<li><code>phone_link</code> - Clickable phone link</li>
		<li><code>url</code> - Property website URL</li>
		<li><code>permalink</code> - Link to property page</li>
		<li><code>website_link</code> - Website button</li>
		<li><code>contact_link</code> - Contact button</li>
		<li><code>tour_link</code> - Virtual tour button</li>
		<li><code>bedrooms</code> - Number of bedrooms</li>
		<li><code>bathrooms</code> - Number of bathrooms</li>
		<li><code>square_feet</code> - Square footage</li>
		<li><code>pricing</code> - Price range</li>
		<li><code>availability</code> - Available units count</li>
		<li><code>specials</code> - Current specials</li>
		<li><code>specials_from_meta</code> - Specials from property meta</li>
		<li><code>description</code> - Property description</li>
		<li><code>tour</code> - Virtual tour embed</li>
		<li><code>location_link</code> - Location link</li>
		<li><code>location_button</code> - Location button</li>
		<li><code>fees_embed</code> - Fees and deposits embed</li>
	</ul>
	<p>You can also use <code>before</code> and <code>after</code> parameters to wrap the output. This is useful for situations where a shortcode is being used inline with text but needs certain helper words if we have that information, but needs to be left out entirely if not.</p>
	<p><span class="shortcode"><!-- wp:shortcode -->[rentfetch_property_info info="phone" before=" call us at " after=" today!"]<!-- /wp:shortcode --></span></p>
	<p>If no <code>property_id</code> is specified, the shortcode will attempt to determine it from context (current property page or single-property site).</p>
	<?php
}

/**
 * Output the floorplans shortcode documentation.
 *
 * @return void.
 */
function rentfetch_floorplans_shortcode_docs()
{
	?>
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
	<?php
}

/**
 * Output the shortcode copy script.
 *
 * @return void.
 */
function rentfetch_shortcode_copy_script()
{
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
}