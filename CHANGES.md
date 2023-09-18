# 3.10.2

-   Fix the fallback image not appearing when there's nothing in Yardi or Manual

## 3.10.1

-   Adding a fix for situations where we're attempting to sync a property in Yardi while the site is set for a single property where actions were failing due to the 'amenities' taxonomy not being registered; we should no longer attempt to sync such a property.

## 3.10

-   Updating Action Scheduler to 3.6
-   Attempting to mitigate situations where the tables don't exist and avoid fatal errors
-   Removing the need to install the Action Scheduler plugin to resolve missing tables situations

## 3.9.18

-   Add filterable lables for bathrooms. Defaults are "Bath" for 0 or 1, or "Baths" for more than that.

## 3.9.17

-   On the single properties template, don't output the floorplans section at all if there aren't any floorplans to show

## 3.9.16

-   Removing a few places where we log to the console by accident (inserted for testing and neglected to remove)

## 3.9.15

-   Make sure to load the third-level directories

## 3.9.14

-   Loading everything automatically

## 3.9.13

-   Added the "Floorplans" heading on single-properties

## 3.9.11

-   Simplify the removal process for Yardi properties and floorplans (appears it was not triggering)

## 3.9.10

-   Adding class to the properties archives for whether properties are available
-   Adding several new filters to the plugin for various bits of text
-   Fixing php notices

## 3.9.9

-   Adding function_exists wrapper on console_log, which is used for debugging and which might have already been declared

## 3.9.8

-   Adding nav capabilities to the single-properties template
-   Adding nearby properties to the single-properties template
-   Adding tour capabilities to the single-properties template

## 3.9.7

-   Going through templates and fixing a few places where we weren't properly escaping values
-   Adding a new filter for rentfetch_property_title to allow for themes to modify the title of a property

## 3.9.6

-   Updates to geocoding for reliability

## 3.9.3

-   Update ACP for 6.0+ version of their columns (thiis was already working, but wanted to make sure it was up to date)
-   Adding Appfolio capability to delete unused properties and floorplans
-   Fixing the icons when loading over SSL

## 3.9.2

-   Update the admin icons for each content type

## 3.9.1

-   Bugfix: if floorplan sliders are hidden, then shown, reinitialze the slider (we're just doing this on any click)

## 3.9

-   Code refactor, adding basic autoloading of php files throughout and moving most items out of the main rent-fetch.php file
-   Adding commas as psuedoelements in the map layout, the search, and fixing the spacing on the single properties template
-   Better loading for geocoding, as it was duplicating some actions if pages were loaded too quickly
-   Pausing syncing now disables geocoding
-   Update bundled ACF

### 3.8.2

-   Added a filter for customizing the search text

### 3.8.1

-   Added geocoding capability with basic rate-limiting setup (if it fails, it adds an error message in the content instead)

### 3.8

-   Added a basic version of the AppFolio API
-   TODO: Add cleanup tasks for removing properties and floorplans when/if they become defunct

### 3.7

-   Add settings for each of the sections of the single-properties template (show or don't show each of those)

### 3.6.9

-   Enqueue dashicons, since we use those on the frontend

### 3.6.7

-   Standarizing section names for the single-properties template

### 3.6.6

-   Make data source available for properties for editing

### 3.6.5

-   Adding the option to show ALL properties in search rather than just available ones
-   Adding functionality when ALL is selected to ignore pricing fields unless they've been manually set

### 3.6.4

-   Updating bundled ACF to 6.0

## 3.6.3

-   Minifying css for prod (whoops, we hadn't previously been doing this)

## 3.6.2

-   Fixing a loading issue for the floorplan grid block when the initial pageload happened with prefiltered slides. When clicking a different number of bedrooms, the sliders didn't appear to load. Added a refresh to slick affecting just the slides that needed rerendered after the click.

## 3.6.1

-   Fixing various php notices associates with php 8+
-   Adding option to remove logs and to disable logging

## 3.6

-   Updating dependencies for php 8.1 (action scheduler and ACF), fixing php notices

## 3.5.15

-   Adding a setting for whether to use property URLs or no on the site

## 3.5.14

-   Adding better filters for property URLs to allow for more customization

## 3.5.12

-   Adding the labels on the floorplan filters in the floorplan grid block

## 3.5.11

-   Adding setting for labeling numbers of bedrooms (e.g. Studio or Individual Beds or whatever)

## 3.5.10

-   Updating all Admin Columns Pro fields to add support for version 5.7.1+ of ACPs

## 3.5.9

-   Add wraps to the single-rpoperties template to make more layouts possible.

## 3.5.8

-   Better logging for the orphan floorplans

## 3.5.7

-   Adding functionality to set availability and available date of Yardi floorplans no longer in the API to unavailable.

## 3.5.6

-   Adding mechanism to find the wp-load file when submitting Yardi API requests (to account for hosts like Flywheel which move that file)

## 3.5.5

-   Reverting

## 3.5.4

-   Using a local path for Yardi API form proxy file

## 3.5.3

-   Updating the button text

## 3.5.2

-   Oops! Turning off the ability to edit fields for the Rent Fetch settings.

## 3.5.1

-   Verifying that the Yardi API submission feature is enabled (not just the settings configured) before outputting anything

## 3.5

-   Adding functionality for Yardi to automatically add an inquire button and lightbox form to product pages which submits through the API.

## 3.4.6

-   Fixing a duplicate action

## 3.4.5

-   Pulling the specials markup off into a separate action for more portability

## 3.4.4

-   Moving the specials label on both properties and floorplans, minor style updates to accommodate this

## 3.4.3

-   Removing the Rent Fetch version number when loading Google Maps scripts, e.g. https://maps.googleapis.com/maps/api/js?key={KEY}&ver=3.4.1 becomes https://maps.googleapis.com/maps/api/js?key={KEY}

## 3.4.2

-   Adding a new piece of meta information for "floorplan description"
-   Adding that to the floorplan block
-   Adding that to the floorplan display on single-properties template
-   Adding that within Admin Columns Pro

## 3.4.1

-   Adding the Rent Fetch logo and new menu to the WordPress sidebar
-   Adding the ability to add credentials and info for RealPage to pull
-   Adding the ability to save and update basic floorplan info via RealPage
-   Adding capacility to run updates in the background

## 3.3.4

-   Reordering elements on the single-properties page

## 3.3.3

-   Adding map to the single-properties template

## 3.3.2

-   Property descriptions should have the_content filter applied

## 3.3.1

-   Make property descriptions a WYSIWYG field for better manual entry

## 3.3.0

-   Add capability in the floorplans grid block to pull in floorplans from one or more properties specifically

## 3.2.0

-   Adding settings for showing/hiding all elements of the search

## 3.1.3

-   Bugfix for the sliders not showing on the single-properties page (the floorplans template)

## 3.1.2

-   MUCH nicer previews for the floorplansgrid block

## 3.1.1

-   Ading capabilities for showing minimums (e.g. "From... ) for rent prices for both properties and floorplans

## 3.1.0

-   Adding capability to use manual images for properties (this previously hadn't specifically been requested)

## 3.0.5

-   Updating the single-properties template to remove components if they're not used
-   Fixing several php notices for undefined variables when those items haven't been defined (manual entry)

## 3.0.4

-   Removing the actions if things are supposed to be set to pause/delete (we're accidentally spinning up a TON of actions)

## 3.0.3

-   BUGFIX: removing functionality for related neighborhoods if the Metabox Relationships dependency doesn't exist in the single properties template

## 3.0.2

-   Adding manual entry version of fields for properties
-   Reordering some fields in the birds-eye view for both properties and floorplans
-   Testing update capabilities

## 3.0.1

-   Adding updates back into the plugin
-   More name changes

## 3.0

-   Changing the plugin name to Rent Fetch, updating hooks throughout, and verifying that all functionality is still working

## 2.24.3

-   Update Flatpickr initialization so that we use abbreviated dates

## 2.24.2

-   Fix minor bug where no properties came up when only one date (and not a range) was selected in the search

## 2.24.1

-   Add scheduled actions button

## 2.24.0

-   Adding functionality to clean up orphan Yardi floorplans, but keeping it disabled for now

## 2.23.4

-   Updating the positions of Floorplans fields for better data entry

## 2.23.3

-   Add property and floorplan IDs on the frontend for admins only

## 2.23.2

-   BUGFIX: Further updates to availability dates which hopefully will resolve availability showing in the past
-   Making deletions happen much faster, at the risk of temporarily overloading the site if there's too much information (but happens a lot faster)

## 2.23.1

-   Move the neighborhood search to the right

## 2.23.0

-   Add the neighborhood search to the site

## 2.22.3

-   Bugfix: we need to ensure that when we're not doing a date-based search, ALL floorplans with 0 units available are being excluded from the search.

## 2.22.2

-   When we're not given a floorplan availability date, save a null value instead of updating it to today by default.

## 2.22.1

-   Fixing a bug where the property grid wasn' picking up any properties with floorplans available when only one unit was available for a floorplan (it previously was only picking up those with 2+ available)

## 2.22.0

-   Adding capability for a shortcode for properties

## 2.21.1

-   Getting the custom amenity name instead of the amenity name

## 2.21.0

-   Adding capability to set a maximum number of bedrooms in search

## 2.20.0

-   Refactoring the single-properties template to allow themes to edit this in a more fine-grained way.

## 2.19.3

-   Ensuring that ACF doesn't load twice

## 2.19.2

-   Adding support for studio beds in the grid (they weren't showing up correctly)

## 2.19.1

-   Fixing a bug when the number of bedrooms isn't defined

## 2.19.0

-   Adding a field for an access token. Nonfunctional at this time.

## 2.18.0

-   Add mechanism to allow for limiting the number of floorplans shown in the floorplansgrid block settings

## 2.17.0

-   Making the whole area clickable inside map areas

## 2.16.0

-   Fixing error where floorplans weren't showing when a floorplan had previously had manual images but they'd been removed in the time since (was returning false, but was checking for null)
-   Fixing Matterport links showing up properly, implementing a check to output different things if it's a youtube/vimeo video or if it's something else (eg matterport).

## 2.15.0

-   Fixing php notice for an undefined variable for floorplan images
-   Adding setting and capability for setting a maximum number of properties to show, defaulting to 100.
-   Fixing undefined offset error in the favories functionality when a favorite is no longer available on the site

## 2.14.0

-   Adding capability for custom floorplan images (for manual entry), falling back to Yardi images, and then to a fallback image if neither are found
-   Adding capability for fallback images for properties

## 2.13.0

-   Adding custom marker capabilities

## 2.12.0

-   Allowing custom sorting of properties with the "Order" attribute

## 2.11.0

-   Better detection of properties for deletion: mismatches in capitalization were causing things to delete that shouldnt have
-   Better detection of properties for deletion: when none were found, properties were slowly deleting over time.

## 2.10.0

-   Minor CSS fixes and updates
-   Adding a check to make sure we don't output an availability button in the floorplan archive if there shouldn't be one (if we don't have a link)

## 2.9.0

-   Remove the properties that are no longer in the setting

## 2.8.0

-   Fix error on single when the API returns an error instead of images for a property

## 2.7.0

-   Better min/max calculations for rent

## 2.6.0

-   Only show available properties in footers (so now only properties with at least one available floorplan will show anywhere on the site)

## 2.5.0

-   Add specials to floorplans layouts
-   Add specials to properties layouts
-   Add specials to floorplansgrid block

## 2.4.0

-   More reliable cancelling of upcoming actions when paused or deleted is set
-   Pulling in beds and baths when decimals are used without accidentally converting those to integers
-   Minor style adjustments to the filters (removing max width, as we might not always have tons of filters to fill the space)

## 2.3.1

-   Add a filter to urls for the property website links in case they don't have them. This filter is also available for themes to dynamically change those URLs if needed.

## 2.3.0

-   Adding support for updating properties (whoops! We had just missed this one, other than the amenities and pets, as those items are actually only handled as part of an update task, as we want to ensure the post actually exists before hooking it up with external stuff).

## 2.2.0

-   Reworking the floorplans and getting those ready for other sites to use.

## 2.1.0

-   Adding the favorites functionality

## 2.0.2

-   Add settings on backend for the price filter values

## 2.0.1

-   Prevent load flash on filters

## 2.0.0

-   Search now fully functional

## 1.19.0

-   Flatpickr in place

## 1.18.0

-   Price-based search complete

## 1.17.0

-   Better functionality for the price-based search

## 1.15.0

-   Fixing several php notices
-   Adding new logic for the availability dates (fixing a major bug where all floorplans from a given property were getting the same date)

## 1.14.0

-   Performance optimization

## 1.13.0

-   Adding map toggle reset functionality (it was breaking without it)

## 1.12.0

-   Fix line break issue in property archives
-   Add toggle to maps
-   Remove the double dollar sign on properties in the map

## 1.11.0

-   Add new content area for properties

## 1.10.0

-   Add setting for the max properties to show in the footer grid

## 1.9.0

-   Adding improvements to the properties footer (showing even when unavailable)
-   Adding the neighborhood to the single-properties template

## 1.8.0

-   Adding nearby properties to the single-properties template

## 1.7.0

-   All buttons added to the floorplans archives

## 1.6.0

-   Adding the layout for floorplans archives
-   Adding the floorplans archive slider
-   Adding fancyboxes in floorplans archives
-   Adding in the availability data into the floorplans archives

## 1.4.1

-   Minor updates

## 1.4.0

-   Map is now pretty functional, still default pins

## 1.3.0

-   Adding properties to the neighborhoods pages

## 1.2.1

-   Bugfix on maps

## 1.2.0

-   Getting the API key pulled correctly for Google maps

## 1.1.0

-   Registering some content types correctly only when option set

## 1.0.0

-   Map in place and working

## 0.42.0

-   Improving hover effects on the sliders

## 0.41.0

-   Adding better versions of the sliders on the home page (all of the styles, better load times)

## 0.40.0

-   Adding the slider

## 0.39.1

-   BUGFIX: search was pulling results when the floorplan search was empty but the properties search has results

## 0.39.0

-   Property images on the properties-single template

## 0.38.0

-   ACP compat with other plugins and themes that use local storage

## 0.37.2

-   Style changes to the search

## 0.37.1

-   Fix empty value in pets

## 0.37.0

-   Get and save the property images

## 0.36.0

-   Add option to limit the number of amenities shown
-   Adjustments to the backend of properties

## 0.35.0

-   Adding the pet policy search

## 0.34.0

-   Adding capability to pull pet policy from Yardi

## 0.33.0

-   Amenities search now working

## 0.32.0

-   Adding capability to grab amenities from Yardi

## 0.31.0

-   Single-properties: adding dropdowns by number of floorplans

## 0.30.0

-   Adding initial styles for the properties

## 0.29.1

-   Button style fixes

## 0.29.0

-   Adding the property type taxonomy and enabling it in the search if there are any types

## 0.27.0

-   Add the home page search form basics

## 0.26.0

-   Remove unrealistically low results from the big search

## 0.25.3

-   Attempt to fix empty properties being added to the database

## 0.25.1

-   Only use Relevanssi if it's installed
-   Only add the search term if there is a search term

## 0.24.1

-   Updating the columns for neighborhoods

## 0.24.0

-   Converting the search over to find properties

## 0.23.0

-   Adding new filters, but not the new functionality

## 0.22.1

-   Adding Relevanssi functionality to search the custom fields

## 0.22.0

-   Adding text-based search (simple version, only finds titles)

## 0.21.2

-   Adding the columns for relationships

## 0.21.1

-   Adding ACP for neighborhoods

## 0.21.0

-   Adding neighborhoods registration back into the plugin

## 0.20.1

-   Better way of doing reset (just reload the page without parameters)

## 0.20.0

-   Adding GET parameter detection to beds and baths on the property search

## 0.19.0

-   Adding a shortcode to do an ajax search of the floorplans

## 0.18.1

-   Adding ability to target a specific floorplan with javascript more easily.

## 0.18.0

-   Continuing work on the single-properties template

## 0.17.0

-   Starting on the basic version of the single-properties template

## 0.16.0

-   Add single template detection and hotswapping

## 0.15.0

-   Initial functionality to pull properties

## 0.14.0

-   Addition of functionality to detect and delete floorplans attached to properties which are no longer syncing
-   Addition of functionality to cancel upcoming sync actions for floorplans attached to properties which are no longer syncing

## 0.13.0

-   Fixes to logic forcing deletes of processes

## 0.12.1

-   Fixing minor bugs in the floorplans block

## 0.12.0

-   Performance improvements when there are many properties to query (we were running into the async triggers themselves causing performance problems).

## 0.10.0

-   Adding bedroom filters

## 0.9.1

-   Separating the block into functions (everything has access to a new settings object now), so that we can actually organize a bit better. Needed in order to do the filters the right way, as that will have to be its own function.

## 0.9.0

-   Adding floorplan limits capabilities to the Floorplans block

## 0.8.1

-   Adding the gravity forms lightbox

## 0.8.0

-   Adding action scheduler directly, as submodules don't update properly

## 0.7.0

-   Fancybox functionality
-   Local featured images working in the Gutenberg block

## 0.6.0

-   Gutenberg block added

## 0.5.0

-   Adding gulp, initial styles for the floorplans layout

## 0.4.1

-   Changing the stable branch to 'main' for PUC

## 0.4.0

-   Adding syncing for the acf fields

## 0.3.0

-   Adding syncing for the admin columns pro columns

## 0.2.0

-   Sync functionality basically in place for Yardi
