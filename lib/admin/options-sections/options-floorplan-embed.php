<?php
/**
 * This file sets up the Properties shortcodes sub-page in the admin area.
 *
 * @package rentfetch
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

function rentfetch_settings_floorplans_floorplan_embed()
{
  ?>
  <div class="header">
    <h2 class="title">Floor Plan Shortcodes</h2>
    <p class="description" style="margin-bottom: 24px;">
      WordPress shortcodes allow users to perform certain actions as well as display predefined items on a site. The Rent Fetch shortcode is the method used to display properties and floor plans on your website.
    </p>
    <p class="description">
      The shortcode can be used anywhere within WordPress where shortcodes are supported. For most users, this will primarily be within the content of a WordPress post or page. Shortcodes are added when you use a standard WordPress editor to add the form to the page.
    </p>
  </div>

  <div class="row">
    <div class="section">
      <h2>Default Floor Plans Search</h2>
      <p>
        The default shortcode to display floor plans, showing the floor plans in a grid with search filters.
      </p>
      <p>
        <span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplansearch]<!-- /wp:shortcode --></span>
      </p>
    </div>

    <div class="separator"></div>

  <div class="row">
    <div class="section">
      <h3>Display a Specific Property</h3>
      <p>
        You can use a parameter to customize by property, so that only a given property (or multiple properties) will display:
      </p>
      <p>
        <span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplansearch
          property_id=p1234]<!-- /wp:shortcode --></span>
        <span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplansearch
          property_id=p1234,p2345]<!-- /wp:shortcode --></span>
      </p>
    </div>

    <div class="separator"></div>

    <div class="section">
      <h2>Floor Plans grid</h2>
      <p>
        These layout options display all floor plans regardless of availability and is useful for displaying arbitrary groupings of floor plans. Does not display search filters.
      </p>
      <p>
        <span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplans]<!-- /wp:shortcode --></span>
        <span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplans property_id=p1234,p5678
          beds=2,3]<!-- /wp:shortcode --></span>
        <span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplans sort=beds]<!-- /wp:shortcode --></span>
        <span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplans
          sort=availability]<!-- /wp:shortcode --></span>
      </p>
    </div>

    <div class="separator"></div>

    <div class="section">
      <h3>Individual components</h3>
      <p>
        <span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplansearchfilters]<!-- /wp:shortcode --></span>
        <span class="shortcode"><!-- wp:shortcode -->[rentfetch_floorplansearchresults]<!-- /wp:shortcode --></span>
      </p>
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

add_action('rentfetch_do_settings_floorplans_floorplan_embed', 'rentfetch_settings_floorplans_floorplan_embed');



