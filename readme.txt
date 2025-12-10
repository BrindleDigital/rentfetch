=== Rent Fetch ===
Contributors: jonschr
Tags: apartments, properties, yardi, entrata, appfolio
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.3
Stable tag: 0.32
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays searchable rental properties, floorplans, and unit availability.


== Description ==

Rent Fetch for apartments and property managers is a powerful plugin solution for displaying current floor plan pricing & availability for your apartment and rental properties.

#### Rent Fetch Sync (premium version)

Automatically display updated pricing, availability, and property info for your single apartment or multi-property (corporate site) with our premium Rent Fetch Sync add-on that integrates with popular property management leasing software.

[Rent Fetch Sync](https://rentfetch.io) works with Yardi RentCafe, Entrata, Appfolio, Rent Manager, and more. Looking to integrate with a different property management system? [Contact us](https://rentfetch.io/get-started/) to discuss!

#### For Single Property Apartment Sites

**Display Pricing & Availability Info**

Showcase your floor plan and unit data – including pricing, photos, video tours, beds, baths, sq ft, unit availability, and more. 

Our individual floor plan page filters down to the unit level, including unique availability links on a per-unit basis.

#### For Corporate Sites 

**Property Availability Map**

View multiple properties with availability on an interactive Google map, and let users filter by property name, beds, baths, price, and more. Text-based search is also available. 

**Units listing** 

Our individual floor plan template goes all the way down to the unit level, including unique availability links on a per-unit basis.

**Single Property Template:** 

Showcase your property info and availability on a single webpage. Each property page can sync (or you can manually manage) photos, contact info, external links, availability, amenity info, tour videos, nearby locations, and more.

Rent Fetch is the engine behind our sites at [Brindle Digital Marketing](https://brindledigital.com/) – at Brindle, we help multifamily apartment properties increase their online presence through web design, social media, branding, and digital advertising.

== Features ==

* Native WordPress content types are used for everything, so a developer can create their own layouts.
* Google Maps integration (both for the property search and for use on each property page. To use this, you’ll need to set up an API key for the Google Maps Javascript API at [maps.googleapis.com](https://maps.googleapis.com))
* Sliders to show property images, floorplan images, and nearby properties (we use the MIT-licensed  [Blaze Slider](https://blaze-slider.dev) for these, and you don’t need to set up anything for these to work)
* Your floorplans can display [Matterport](https://my.matterport.com) and [YouTube](https://www.youtube.com) tours.
* TONS of hooks, letting you (or your developer) customize to your heart’s content. The single layouts for both floorplans and properties can be fully replaced by the theme, and we have lots of helpful functions to let you grab preprocessed information for display.
* This plugin works with both single-property apartment websites and websites that showcase hundreds of properties.
* Adding mini search capability

== Frequently Asked Questions ==

= What APIs and PMS’s do you work with? =

The free version of the plugin allows for unlimited usage with *manual data entry*. Our Premium  [Rent Fetch Sync](https://rentfetch.io) add-on works with Yardi RentCafe, Entrata, Rent Manager, and Appfolio (more coming soon). Looking to integrate with a different property management system? [Contact us](https://rentfetch.io/get-started/) to discuss!

= Will this work on a site showing just one property and a handful of floorplans? =

Yes. You'll want to use the `[rentfetch_floorplansearch]` shorcode to show those.

= Will this work if I have hundreds of properties? =

Yes. You'll want to use the `[rentfetch_propertysearch]' shortcode to show a full availability search.

= Is there a way to show all of my properties, even if some of them don't have any availability at tht moment? =

Yes. You can use the `[rentfetch_properties]` shortcode, or you can build your own display – all the data is saved in WordPress.

= What APIs do you work with? =

This free version of the plugin allows for unlimited usage *with manual data entry*. Our [Rent Fetch Sync](https://rentfetch.io) addon works with Yardi/RentCafe, Entrata, and Rent Manager).

= Can synced properties be customized in WordPress? =

Yes, to a large degree. You might need a php developer to help with this customization, depending on the level of customization you're after.

== Screenshots ==

1. Properties search

2. Floorplans admin screen

3. Single property

== Installation == 

Start from your WordPress dashboard.

1. **Visit** Plugins > Add New
2. **Search** for "Rent Fetch"
3. **Install and Activate** Rent Fetch from your Plugins page
4. Once activated, you'll want to add at least one property to the site (be sure to include a unique property ID when you do). 
5. Add floorplans to your property, using that same unique property ID for any connected floorplans.
6. Add more properties and floorplans as necessary. (Most websites doing manual entry won't benefit from adding units).
7. Add a shortcode to display what you'd like to display (there's a one-click copy list of available shortcodes on one of the plugin settings pages).

== Changelog ==

= 0.32 =

* Reworking property fees to make them less confusing to users. This is NOT a breaking change. Data already loaded into sites will be preserved (but when you update it you'll need to use the new format)
* Updating some verbiage around property fees
* Adding capability to use published Google Docs .csv files

= 0.31.3 =

* Addressing a concern about UTM parameters being lost when a search happens.

= 0.31.2 =

* Removing the icon/styles from the shortcode version of the property data buttons
* Fixing style bug for the office hours in some situations.

= 0.31.1 =

* Moving the entire search (for both properties and floorplans) to use REST instead of admin-ajax.
* Adding transient-based cache setting for preloading
* Adding UI to show recent uncached searches.
* Adding UI to clear the transient cache
* Adding database indexing and associated UI
* New icons for single-properties buttons
* New single-properties button to show office hours, if synced.
* Updating shortcodes with new button and icons
* Restoring capability to click on shortcodes on the site backend to copy them to the clipboard.
* Fixing link to the floorplan shortcodes page that wasn't going to the specific shortcodes page of the floorplan settings.

= 0.30.0 =

* Add new metabox for properties to show office hours.
* Add office hours functionality to the property functions, with an appropriate function to get the markup, one to echo the markup, and an appropriate filter.
* Add office hours to the shortcodes capability
* Add detection for whether we're syncing this to disable these fields on the backend if these are synced values.
* This functionality depends on Rentfetch Sync 0.11.11 for the actual syncing capabilities; these updates make manual entry possible, however.

= 0.29.5 =

* Speculative fix for nonce issues on floorplans search (we can't directly test this across all hosts involved)

= 0.29.4 =

* Heirarchy view: allowing for CMD/CTRL+click to open in a new tab.
* Heirarchy view: adding the unit IDs into the unit information
* Heirarchy view: more consistent styles and more obvious highlighting for where we are in the heirarchy.
* Heirarchy view: general style updates, simplifying the structure of this for consistency
* Heirarchy view: tooltips line-break and sizing when showing dates for multiple APIs (e.g. both Yardi and Entrata properties, since we call two APIs for each of these)

= 0.29.3 =

* Adding handling for showing Yardi 304 responses on the site backend (for units, which would have gotten a 304 response from the apartmentavailability API).

= 0.29.2 =

* Bugfix: When cleaning json responses (Yardi sometimes sends us strings that include unescaped quotes), we were sometimes converting single quotes to double quotes.

= 0.29 =

* Adding backend highlighting functionality in the propety columns view to see at a glance where likely problems are arising.
* Adding internal navigation between backend properties/floorplans/units and a tree to help us easily move between related items.

= 0.28.1 =

* Fixing a bug related to the properties sea1rch and not finding every unit (resulting in properties not showing in the main search)

= 0.28 =

* Feature: json/csv uploads for the fee structures for properties, adding appropriate meta and functionality to handle this
* Frontend base styles and functionality for the new fee structures
* Updating the grouping of properties metaboxes for readability
* Loading photos asynchronously and forcing GPU support on the properties backend because in some cases those pages take a long time to load

= 0.27 =

* Itegrating changes from automatic github scanner highlighting places where we were double-escaping some values.
* Substantial changes to our [rentfetch_property_info] shortcode, adding new values and parameters.
* Updating shortcode documentation to match.
* Updates to the nomenclature used in navigating to the shortcode pages, adding direct links to those on the backend.
* Improvements to our json processing and display on the backend to try to show formatted json even when there are some common errors in the markup.
* Adding new capability to properties for a tour booking link, integrating that into the shortcodes, adding admin columns, etc.
* Updates to the admin columns for units and floorplans to allow filtering by integration and sorting to availability and availability date to allow for easier surfacing of data on larger sites.
* New functionality replacing the dates field for both properties and shortcodes. The new functionality searches floorplans AND UNITS and allows for preset ranges. 
* Adding transient-based psuedocache for these relatively complex queries for the updated date-based search.
* Smart display of date-based availability options such that we don't display options that will return zero results.


= 0.26.1 =

* Updating admin menu verbiage for consistency

= 0.26 =

* Adding parameters for the propertytypes and propertycategories to the [rentfetch_properties] shortcode.
* Adding a filter to modify the text shown when there's a property and we don't know what price to show. 'Call for pricing' is the default.
* Updating Matterport/Youtube descriptions
* Adding property-level shortcodes and automatic detection where needed.

= 0.25 =

* Removing RealPage from the options, since we're not actually using that API.

= 0.24 = 

* Implementing transient-based pseudocaching for recent queries on the floorplan and property level (we're caching the actual post-processed markup on the property level). This should reduce server load and make property searches faster.
* Adding alt attribues to images in the properties grid.
* Improvements to the scroll when we click on a property in the map, making that feel smoother
* Adding explicit lazy loading to property images.
* Adding min and max width values to the cells in the unit details table, so that we don't get weird formatting if we encounter significantly more data in a cell than expected.
* Adding a new option for disabling those transients (it starts enabled to reduce server load, but for debugging it would be nice to do actual queries)
* Lots of reorganization and improvement in our settings pages. There were a few settings that were just on the wrong page, and there were a few places that settings mismatches could break something. General improvements.

= 0.23 =

* Updating column order for properties since the data source is one of the most important pieces of information
* Updating column styles to accommodate more debugging data
* Adding capability to show the most recent request completed for properties, floorplans, and units to help with more quickly debugging (and finding out what the API actually is telling us)

= 0.22.5 =

* More efficient queries for the main floorplans loop (for property searches), as we're seeing some examples of sites with 10k+ floorplans that were struggling. This should be a 1-to-1 change.
* Adding the backend column for 'amenities' to units for easier visibility into which units have amenities attached.
* Making the 'phone' field readonly for Entrata-synced properties, since that data is being dynamically pulled.
* Fixing the columns display on the admin for unit-level amenities.
* Fixing several sql duplicate joins that were throwing an error when searching the units for a particular unit, etc.

= 0.22.4 =

* Enabling editing on the 'phone' field for Entrata properties, as the API doesn't provide a phone number and the field was also being disabled.
* Updating verbiage of "floorplans" to "floor plans" throughout.
* Backend options for property filters now pull in the *current* label of the various taxonomies for properties rather than pulling in their default labels, as this will make more sense to the user when seeing those settings (this is often customized by end users in code)

= 0.22.3 =

* Compatibility fix: Popup Maker seems to do something a little odd with add_meta_boxes, causing our addition of the units metabox to run before the RF plugin is fully loaded. The result of this is an error on PUM pages on the site, which can be fixed when RFS is disabled. Adding more specific logic to make sure that we're only loading the meta boxes for the units when we're actualy on a units page in the admin.

= 0.22.2 =

* Feature addition: new filter on the property level for "Cities"

= 0.22.1

* Bugfix: Fixing a php notice 8.4.4 of php where we're using array_sum on an array that includes a mixture of strings and ints.
* Bugfix: When we queried to get the property pricing embed, we were resetting the query *after* the function had bailed in cases where a null value was found. This could result in other queries lower on the page failing in this circumstance (caused a site to show a sidebar when it shouldn't have.)

= 0.22 =

* Adding new functionality aimed at meeting new legal requirements for apartments in regard to pricing.
* Added embed code option.
* Added embed code and section nav conditionally to single-properties template
* Added new option and logic for the section nav on the single-properties template
* Added embed code conditionally to single-floorplans template
* Added embed code and description conditionally to floorplans grid
* Added embed code and description conditionally to floorplans search
* Added functionality to check whether the site is a single-property site 

= 0.21.7 =

* Adding styles for the new slider for dates on the new form.

= 0.21.6 =

* Adding property-level specials for manual entry (we don't have any APIs that sync this information), following the model from floorplans.

= 0.21.5 =

* Moving functionality for editing units to Rent Fetch Sync and out of core.

= 0.21.4 = 

* Feature: adding a URL override field to allow for overriding the URL in cases where the synced URL can't be changed.
* Adding column to the properties table in the admin.

= 0.21.3

* More admin style updates

= 0.21.2 = 

* Minor admin UI updates (fixing a few labels, adding internal link to the Rent Fetch logo)
* Adding ability to edit units
* Adding highlighting for the synced unit fields
* Adding filters to auto-highlight/disable synced unit fields for Yardi and Entrata

= 0.21.1 =

* Fixing bad commit (accidentally committed the previous version, resulting in no update being available)

= 0.21.0 =

* New base styles for the site backend. This should continue to be improved over time, but because we're working with the base plugin and addons, it's getting increasingly complex to maintain both admins.
* A few additional styles for new Entrata forms.
* Removing the units and floorplans CPTs from SEOPress notifications (this won't remove existing notices, but should help not spawn new ones all the time)

= 0.20.1 = 

* Minor updates to styles for the new Entrata forms

= 0.20 = 

* Adding styles for the new Entrata forms
* Minor bugfixes

= 0.19.2 = 

* Updating our methodology for pulling the number of units (and whether to show the units section) on the single-floorplans template. We previously pulled this from the floorplan meta, but for manual-entry sites with no units and forced floorplan single template enabled, this could result in a blank section.
* Switching to hover capabilities for all of the dropdowns on both the properties and floorplans searches. This makes the user experience much less clicky.

= 0.19.1 = 

* Updating default verbiage on the single-floorplans template ("Floorplan" to "Floor Plan")
* Fixing several php notices in relatively rare situations on both the backend and frontend.

= 0.19 = 

* Feature: Adding the ability to output the building name and floor number for units (these exist in the Entrata API)
* Feature: Adding building name and floor number on the backend display
* Bugfix: In the units table, we were set up to always show pricing. However, the Entrata API has situations where there is no pricing and yet there's an Apply Online link. So we're removing that column when it's not useful.
* Update: modifying the styles on the filters such that they display better on mobile. On desktop, there's also minor (possibly breaking, in a minor way) changes to this, but should be a significant improvement to UI.

= 0.18.10 = 

* Bugfix: the filters for floorplan searches were not being shown on mobile. Those are now visible again.
* Improvement: minor CSS update to improve the display of the filters on property searches on older (smaller) mobile devices.
* Changing the structure of our plugin header to more closely align with ACF, since the author of the plugin is currently showing wrong and ACF has gone through a renaming recently. (Possible fix)

= 0.18.9 = 

* Updates to readme file, etc.

= 0.18.8 = 

* Improvement: removed the amenities classes from the post_class, as this significantly can impact performance on sites with dozens of amenities, and we've not seen those used in any instance.
* Improvement: Updated scss processing setup, as our previous version had become deprecated.
* Improvement: Updated css variables to generalize them, removing specific color names.
* Feature: Add unavailable classes and option to automatically fade out properties without availability.
* Improvement: add better handling to rent numbers shown on the property level to filter out negative numbers and rent values below 100, as some clients like to use junk data when things aren't available.
* Feature: Add an option allowing for forcing links to the single-floorplans template and supporting functionality.

= 0.18.7 =

* Feature: Adding a javascript hook to allow more markers to be added to the map by third-party plugins
* Feature: Filtering the value of the Entrata subdomain to save just the subdomain if the user doesn't read the directions
* Feature: Adding a new capability for the property search to use a parameter, like [rentfetch_propertysearch propertyids="p0241141,1301505"]
* Updating the documentation for the shortcodes, as we've added a number of parameters.

= 0.18.6 = 

* Feature: Adding a shortcode parameter for propertyids to [rentfetch_properties]

= 0.18.5 =

* Feature: Adding compatibility for Entrata sync (backend display of their data formats for images, columns, etc.)
* Bugfix: Removing the smoothscroll being added on the main property search.

= 0.18.4 =

* Feature: adding the ability for the administrator to determine whether to automatically redirect properties to their websites, or to use our pre-built template for that.
* Adding standard function and filter to get these URLs, so that we can do that programatically if needed.
* Adding redirect functionality that is based on the option

= 0.18.3 =

* Bugfix: When a manual property image is removed from the property, let's make sure that a null value isn't saved to the array.
* Bugfix: When navigating through admin submenu items to the property search page, those settings were not savable (if you navigated there by clicking "Property Settings," you were still able to save normally).
* Improved the shortcodes section on the backend to show a few new parameters and structure them in a more logical way.
* Added new option to hide the availability button in situations where there's a link available, but no floorplans are available (and have no upcoming availability date)
* Added updated sorting options for the floorplan search to the grid as well, as the capabilities weren't fully in sync.

= 0.18.2 =

* Fixing versioning issue when updating.

= 0.18.1 =

* Bugfix: When a floorplan image is removed from the floorplan, let's make sure that a null value isn't saved to the array.

= 0.18.0 =

* Fix compatibility issues with data formats in the Yardi v2 API (we display this data in various places, so we're fixing errors caused by the changing structure). These new formats start to matter in 0.5 of RFS.

= 0.17.9 =

* Remove meta for Brindle's API key (we'll pass this in the RF API moving forward, so that we don't need to give it out)

= 0.17.8 =

* Flush the transient which contains API information when the Rent Fetch sync settings are changed. We do this because when those settings change, we might need the new Yardi bearer token, etc.

= 0.17.7 =

* Updating to match changes made to the Yardi v2 fields in RFS.

= 0.17.6 =

* Adding saving for fields for Yardi API v2

= 0.17.5 = 

* Updating the php version requirement (down to 7.1; we really don't want people updating RFS without RF at the same time)

= 0.17.3 = 

* New look and feel (still in progress)
* New pages for embeds to help with shortcodes
* Add new options for Rent Manager data
* Save the Rent Manager company code option
* Minor bugfix for notices when some values are empty
* Adding capabilities to render images from RentManager in the admin
* Adding capabilities for column highlighting for the synced stuff from Rent Manager
* Fixing a number of issues introduced by the styling project
* Fixing a fatal error when the floorplan rent is unexpectedly not being set (casting it to an int)
* New capability for taxonomy shortcodes for floorplans (setting a category terms to only show, as either a search or as a grid)

= 0.17.2 =

* Bugfix: adding new functionality to allow for better phone number parsing for the property phone numbers, allowing for separate parsing for the phone number link.
* Adding a button on the single-properties template for the tour (this previously didn't actually show up anywhere, it turns out).
* Adding a button on the properties search template to do the same
* Standardizing the "Edit this" buttons when logged in between the floorplans grid, properties grid, and properties simple grid, to show all of these on hover, all centered at the top.
* Reworking the script to load properties buttons to allow it to work with more AJAX requests (like on the properties search), and adding an enqueue on the properties search level outside the AJAX request, since it wasn't loading otherwise.

= 0.17.1 =

* Bugfix: the custom content area was not outputting by default on the single-properties template; this is fixed.

= 0.17 =

* Adding capability to disable fields on the backend of the site that are synced for Yardi (both floorplans and properties)
* Adding capability to disable fields on the backend of the site that are synced for Realpage (both floorplans and properties)
* Adding capabilities to highlight synced fields in the columns views for both properties and floorplans
* Adding filters to control which fields those are for both (this only has the capability of disabling fields; it doesn't (yet) disable the actual sync for those).
* Removing a few options that are unused on the backend
* Bugfixes: fixing some potential manual-entry errors when adding images on both floorplans and properties
* Bugfix: changing how we calculate the number of units when looking at a floorplan on the single-properties page (helps with manual entry)
* Bugfix: only show the nearby properties slider if there are at least two properties to show (layout is awkward with just one)
* Removing the pets meta field on properties, as only the Yardi API contains that and it does it on a one-off basis, so we can't predict how the structure of that works.
* Code standard fixes

= 0.16.1 = 

* Bugfix: added an event on save of the sync settings to cancel all pending actions. This should avoid a rare situation where user-added data might be deleted from a property if the property was added while that same property was scheduled for deletion (orphan control)

= 0.16 = 

* Feature: adding a maximum length for floorplan-level custom specials
* Feature: adding the floorplan specials to the single-floorplans template an styling those to match other views
* Feature: adding a WYSIWYG editor for the floorplan description, allowing output of WordPress default tags. Adding appropriate styles to this.
* Feature: adding a WYSIWYG editor for the property description, allowing output of WordPress default tags. Adding appropriate styles to this.

= 0.15.12 =

* Bugfix: videos weren't fading out when default fade was applied to floorplans
* Bugfix: fixing a php notice on the Floorplans overview admin view in situations where there are manual images but they aren't on this server (either image deleted or on a local server where the images aren't present)
* Feature: add meta fields and output for overriding the specials text on floorplans with override text

= 0.15.11 =

* Feature: adding a 'no-units-available' or 'has-units-available' class on all floorplan archive views, based on whether units are available.
* Feature: adding a setting to allow default faded styles on unavailable floorplan views with 'no-units-available-faded' and adding those default styles.
* Feature: adding a button option and output for when a floorplan has no units available
* Update: adding styles for Google's updated map markup

= 0.15.10 = 

* Bugfix: Google appears to have changed their scheme for linking to locations. Updating this to get better results while still linking to the location page where possible (if it has a "named" Google places location)

= 0.15.9 =

* Feature: adding floorplan tag and floorplan category search parameters to the floorplan searches
* Feature: adding floorplan tag and floorplan category search parameters to the property searches
* Adding appropriate options to support these new features.

= 0.15.8 =

* Feature: making the images drag-droppable when manually added for both properties and floorplans
* Bugfix: the "remove" buttons on those images, when used after new images were added to a pre-existing gallery, were failing to remove some images. Fixing that.

= 0.15.7 =

* Feature: adding setting for how often syncs happen (ranging from 1 hour to 1 day)

= 0.15.6 = 

* Feature: adding a price sort to the floorplans shortcode
* Feature: adding options for a price sort (low and high) to the defaults for the floorplans shortcode on initial load

= 0.15.5 =

* Feature: add floorplan buttons when there are no units available on the list view for floorplans. This brings the functionality closer in line with the grid view.

= 0.15.4 = 

* Feature: adding the floorplan description on the single-floorplans template and the list view (not in the grid view)
* Bugfix: adding a maximum height to the images on the single-floorplans template to make it look less bad when a user uses a very vall image in that space.

= 0.15.3 =

* Bugfix: fixing a situation where the "lease now" button for a floorplan could show if the value were set to "" instead of null.
* Adding a setting to hide the number of available units in floorplan displays
* Adding a setting to hide the number of available units in property displays (some displays hide this by default)
* Adding image display in the properties birdseye view
* Adding image display in the floorplans birdseye view

= 0.15.2 =

* Bugfix: fixing a situation where the word "available" could get cut off when italic in the floorplans grid
* Feature: adding automated detection for whether property and floorplan links should open in the same or a new tab.

= 0.15.1 =

* Bugfix: fixing a rare case where multi-property sites could show wrong numbers of units for a given floorplan if that floorplan has an ID that is not unique.

= 0.15.0 =

* Initial commit on the WordPress repo.

= 0.14.11 =

* Bugfix: the bedrooms label filter now accounts for values where 0 is a string or empty. Previously, it would fail a check for studio floorplans when the number was a string instead of an int.
* Adding additional migration actions on activate to attempt to ease some pain points for future migrations.


= 0.14.10 =

* Bugfix: fixing a php error caused by either dated information or a blank string in the unit amenities in some views.
* Adding the ability to hide unused columns automatically in the units table.
* Adding a column for specials in the unit table, since we're already saving unit-level specials in Yardi.

= 0.14.9 =

* Bugfix: when there's no associated property, modify the query for the units columns to allow floorplan names to still show.
* Bugfix: the floorplan tour link label wasn't outputting properly

= 0.14.8 =

* Adding display of unit-level amenities to the single-floorplans template
* BUGFIX: the link for the floorplans grid which connects it to the single floorplans template was mistakenly removed; re-adding that.
* Adding hooks in two locations for the units query, to allow for filtering the units further by meta

= 0.14.7 =

* Adding the floorplan buttons to the single template

= 0.14.6 =

* CSS hardening as we're seeing common issues that can be automatically resolved

= 0.14.5 =

* Add migration script to allow the plugin to automatically migrate data for tours from the old field (floorplan_video_or_tour) to the new one (tour)
* Bugfix in the single-floorplans display where matterport links that are not embed codes were not showing up.

= 0.14.4 =

* Fixing a php notice when there are no properties on the main search ($filtered_beds array is not an array).
* Updating the display of rent values on all three levels. For properties and floorplans, giving them an option to show range or minimum. For units, we're just showing minimum, as some APIs (RealPage) don't give reliable max values.

= 0.14.3 =

* Fixing a bug with the [rentfetch_properties] shortcode that was resetting the posts_per_page to -1 in all cases.

= 0.14.2 =

* Simplifying the args for the [rentfetch_floorplans] shortcode. Don't need to separately pass $args and $atts, and more extendable without.
* Adding shortcode parameter for 'city' to the [rentfetch_properties] shortcode.

= 0.14.1 =

* Adding base styles for the units table so that it doesn't look broken when the theme doesn't use a CSS reset.
* Adding posts_per_page capability to [rentfetch_floorplans] shortcode.

= 0.14.0 =

* Updating the filters for the contact button and email address on properties to not output a blank button.
* Added a way to filter just the email address itself without messing with the button markup for simplicity.
* Fixing an error that prevented the property archive settings from saving properly
* Adding a filter to allow for the default property archive order to be set
* Connecting the settings for property archive order to the filter.
* Style bugfix: when text overflows the text search area in the featured filters of the property search, it now will end in a ... (this is important for bookmarks)
* Style bugfix: removing unwanted WP default spacing below the unit table
* Making the single properties buttons hookable so that we don't have to constantly re-add this section when customizing client sites
* Adding a new default location for custom filters to be added on property searches (after the text search, before everything else)
* Adding filterable labels to some of the dropdowns
* Reordering/standardizing names for admin columns
* Adding a floorplancategories taxonomy

= 0.13.1 =

* Minor stylefix in the properties map
* Two bugs in the property archive settings (typo prevented setting from saving)

= 0.13.0 =

* After initial review for the WordPress plugin repo, adjusting code to match WordPress code standards (this is s substantial code review)
* Adding to the single-floorplans.php template
* Adding nonces throughout where appropriate
* Rechecking all of the filters and making some logic adjustments
* BUGFIX: we were converting some ranges (bedrooms) from range strings to integers, and this was showing up in the frontend display. That's fixed.

= 0.12.6 =

* Bugfix: Fixed a fatal error that could happen in some environments when manually entering values for rent (string to float conversion wasn't happening)

= 0.12.5 =

* Adding prefixed versions of the shortcodes (unprefixed to be removed as soon as a few sites are updated)
* Escaping an admin notice

= 0.12.4 =

* Changing all instances of rf_ and rent_fetch_ to rentfetch_

= 0.12.2 = 

* Exiting all php files if accessed directly (changes made in all php files)

= 0.12.1 = 

* Removing third-party update capabilities from the Wordpress.org version of the plugin (this is the version that will become canonical once it's on the repo.)

= 0.12 =

* Adding a setting for the default sort to the floorplans search
* Adding a parameter to the [floorplans] shortcode for sort (beds or availability are currently supported)

= 0.11.1 =

* Updating shortcode documentation

= 0.11 =

* Adding shortcode parameters for the [floorplans] shortcode to allow selection of one/multiple properties
* Adding shortcode parameters for the [floorplans] shortcode to allow selection of one/multiple numbers of bedrooms
* Adding default sort for the [floorplans] shortcode to sort DESC by beds

= 0.10 =

* Adding styles for the floorplans grid to match up with the floorplans search

= 0.9.2 =

* Bugfix: the tour link referred to settings which don't presently exist. Those references were removed.
* Bugfix: the tour link wasn't being inherited properly; that's now fixed, and there's currently just a global default for this.

= 0.9.1 =

* Bugfix: The default number of floorplans pulled by the floorplans search (should be -1, not 10)
* Changing the alignment of the sorting filter dropdown on the floorplans search so that its right side aligns with the right side
* Extremely rough version of a new floorplans grid shortcode enabled (this is incomplete and shouldn't be used yet)
* Minor updates to the markup for the floorplans search loop to bring it into alignment with the new grid

= 0.9 = 

* Adding sorting capability to the floorplans search

= 0.8 = 

* Adding parameters to the [floorplansearch] shortcode, like this: [floorplansearch property_id=p1671482]

= 0.7.1 =

* Improving the performance of the pricing filter when it's not set. Previously, it was still only finding floorplans that had pricing, and if it's null, then it really should be fully ignored.

= 0.7 = 

* Adding functionality for more mobile-friendly displays on the single-floorplans view and on the properties list view.
* Adding the image sliders where appropriate for floorplans
* Minor bugfixes throughout

= 0.6.1 

* Fixing a bug where the square footage search wasn't showing up in the properties search when enabled.

= 0.6 = 

* Adding the square footage search everywhere

= 0.5.1 =

* Assign the single-floorplans and single-properties templates in cases where the $template is not being passed in, for whatever reason.

= 0.5 = 

* Adding glightbox for use on matterport and youtube embeds
* Adding tours field to both properties and floorplans and standardizing that
* Adding the specials available element on the single-properties template for each floorplan
* Adding fields for matterport/youtube and making those work reasonably well whether that's an iframe or an oembed, to support more use cases
* Adding the new tours buttons on the single-properties layout
* Adding the new tours buttons on the floorplans search

= 0.4.10 = 

* Fixing the enqueue for Google Maps such that it no longer is loading on every page, but still works with FSE themes

= 0.4.9 =

* Removing multiproperty settings, making that the default to remove a friction point

= 0.4.8 =

* Adding specials display on the floorplans search at the floorplan level 

= 0.4.7 =

* Changing the default on the Floorplan search images to contain instead of cover
* Adding the dividers between floorplan attributes in the floorplans search

= 0.4.6 =

* Adding PUC back in since it's going to be like 70 days until we could possibly be on WP.org

= 0.4.5 =

* Removed an errant 'echo 3' statement that wasn't ever running in any of our test environments, since they define a constant for Google Maps
* Fixed button width in the floorplan grid when used in a small space

= 0.4.4 =

* Style bug in the floorplans search (height of images)
* Fixing bug where empty min/max rent or square footage was causing an error
* Moving the AS tables check into Rentfetch Sync (because it's no longer relevant for the core plugin)

= 0.4.3 =

* Minor style bugfixes

= 0.4.2 =

* Style fixes for images being too large in some spots
* Fixed a fatal error when an expected number is a string instead

= 0.4.1 =

* Attempting to flush the permalinks on activation, as properties don't seem to be getting flushed automatically on new sites.

= 0.3 =

* Upgrading to WordPress coding standards throughout
* Fully removing deprecated functions

= 0.2 =

* Reorganizing the options files
* Prefixing all options with rentfetch
* Fixing numerous bugs, particularly in the map
* Adding support for themes that intentionally use .hentry instead of .entry for targeting generic posts
* Updating box shadows
* Setting key plugin uptions on activation

= 0.1 =

* Initial version

