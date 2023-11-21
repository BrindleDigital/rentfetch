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
		do_action( 'rent_fetch_do_documentation_shortcodes' );
	echo '</div>';
}

function rent_fetch_documentation_shortcodes() {
	?>
	<h2>Multiple properties search</h2>
	<p>The main properties search can be rendered using the default shortcode, which will create a side-by-side layout with the properties and search filters next to the map, or you can render each component individually to make the layout work however you'd like it to.</p>
	<h3>Default search</h3>
	<p>This one includes everything; just use this and you're done. This will attempt to force itself to be full-width on the page regardless of your theme styles.</p>
	<p><span class="shortcode"><!-- wp:shortcode -->[propertysearch]<!-- /wp:shortcode --></span></p>
	<h3>Individual components</h3>
	<p>Use these individually to arrange various components. It's quite likely, using these, that you'll need to write some styles to position them the way you'd like on the page.</p>
	<p><span class="shortcode"><!-- wp:shortcode -->[propertysearchmap]<!-- /wp:shortcode --></span> <span class="shortcode"><!-- wp:shortcode -->[propertysearchfilters]<!-- /wp:shortcode --></span> <span class="shortcode"><!-- wp:shortcode -->[propertysearchresults]<!-- /wp:shortcode --></span></p>
	
	<h2>Properties grid</h2>
	<p>This layout ignores availability, and is most suitable for smaller ownership groups with 5-20 properties. <strong>We strongly recommend using this somewhere it can span the full width of the screen.</strong></p>
	<p><span class="shortcode"><!-- wp:shortcode -->[properties]<!-- /wp:shortcode --></span></p>
	
	<h2>Floorplans search</h2>
	<p>This layout ignores availability, and is most suitable for very small ownership groups, listing 1-5 properties.</p>
	<h3>Default search</h3>
	<p><span class="shortcode"><!-- wp:shortcode -->[floorplansearch]<!-- /wp:shortcode --></span></p>
	<h3>Individual components</h3>
	<p><span class="shortcode"><!-- wp:shortcode -->[floorplansearchfilters]<!-- /wp:shortcode --></span><span class="shortcode"><!-- wp:shortcode -->[floorplansearchresults]<!-- /wp:shortcode --></span></p>
	<?php
}
add_action( 'rent_fetch_do_documentation_shortcodes', 'rent_fetch_documentation_shortcodes' );