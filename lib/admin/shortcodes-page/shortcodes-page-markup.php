<?php

function rent_fetch_shortcodes_page_html() {
	if (!current_user_can('manage_options')) {
		return;
	}
	
	?>
	<script>
		jQuery(document).ready(function($) {
			// Get all .shortcode elements
			const shortcodes = document.querySelectorAll('.shortcode');

			// Add event listener to each .shortcode element
			shortcodes.forEach(shortcode => {
				shortcode.addEventListener('click', () => {
					// Remove the .copied class from all .shortcode elements
					shortcodes.forEach(element => {
						element.classList.remove('copied');
					});

					// Copy the contents of the clicked .shortcode element to the clipboard
					const range = document.createRange();
					range.selectNodeContents(shortcode);
					const selection = window.getSelection();
					selection.removeAllRanges();
					selection.addRange(range);
					document.execCommand('copy');
					selection.removeAllRanges();

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
		do_action( 'rent_fetch_do_documentation_shortcodes' );
	echo '</div>';
}

function rent_fetch_documentation_shortcodes() {
	?>
	<h2>Multiple properties search</h2>
	<p>The main properties search can be rendered using the default shortcode, which will create a side-by-side layout with the properties and search filters next to the map, or you can render each component individually to make the layout work however you'd like it to.</p>
	<h3>Default search</h3>
	<p>This one includes everything; just use this and you're done. This will attempt to force itself to be full-width on the page regardless of your theme styles.</p>
	<p><span class="shortcode">[propertysearch]</span></p>
	<h3>Individual components</h3>
	<p>Use these individually to arrange various components. It's quite likely, using these, that you'll need to write some styles to position them the way you'd like on the page.</p>
	<p><span class="shortcode">[propertysearchmap]</span> <span class="shortcode">[propertysearchfilters]</span> <span class="shortcode">[propertysearchresults]</span></p>
	
	<h2>Properties grid</h2>
	<p>This layout ignores availability, and is most suitable for smaller ownership groups with 5-20 properties.</p>
	<p><span class="shortcode">[properties]</span></p>
	
	<h2>Floorplans search</h2>
	<p>This layout ignores availability, and is most suitable for very small ownership groups, listing 1-5 properties.</p>
	<h3>Default search</h3>
	<p><span class="shortcode">[floorplansearch]</span></p>
	<h3>Individual components</h3>
	<p><span class="shortcode">[floorplansearchfilters]</span><span class="shortcode">[floorplansearchresults]</span></p>
	<?php
}
add_action( 'rent_fetch_do_documentation_shortcodes', 'rent_fetch_documentation_shortcodes' );