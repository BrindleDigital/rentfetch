=== Rent Fetch ===
Contributors: jonschr
Tags: property, apartment, rent, yardi, realpage
Requires at least: 6.4
Tested up to: 6.6.2
Requires PHP: 7.3
Stable tag: 0.18.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays searchable rental properties, floorplans, and unit availability.


== Description ==

Rent Fetch for apartments is a powerful plugin solution for displaying current floor plan pricing & availability for your apartment and rental properties.

**Pro**

Automatically display updated pricing, availability, and property info for your single apartment or multi-property / corporate site with our premium Rent Fetch Sync add-on that integrates with popular property management leasing softwares. 

[Rent Fetch Sync](https://rentfetch.io) works with Yardi/RentCafe (Entrata, Appfolio and RealPage support coming soon!). Looking to integrate with a different property management system? [Contact us](https://rentfetch.io/get-started/) to discuss!

**For Apartment Sites: Display Floor Plan & Unit Availability**

Showcase your floor plan data – including featured photos and 3D floor plans, beds, baths, sq ft, unit availability, 3D video tour links, and pricing. Plus let users filter and search.

**Units listing:** Our individual floor plan template goes all the way down to the unit level, including unique availability links on a per-unit basis.

**For Corporate Sites: Property Availability Map**

View multiple properties with available floor plans on a Google map, and let users filter by property name, beds, baths, price, and more. Text-based search is also available, and we integrate with Relevanssi, so your users can search for anything at all (the city or zip code works fine).

**Single Property Template:** Showcase your property – and all associated floorplans – with a unique URL for each property. Each one has a floorplan listing including availability, image gallery, location map, and more!

== Features ==

* Native WordPress content types are used for everything, so a developer can create their own layouts.
* Google maps integration (both for the property search and for use on each property page. To use this, you'll need to set up an API key for the Google Maps Javascript API at [maps.googleapis.com](https://maps.googleapis.com))
* Sliders to show property images, floorplan images, and nearby properties (we use the MIT-licensed [Blaze Slider](https://blaze-slider.dev) for these, and you don't need to set up anything for these to work)
* Your floorplans can display [Matterport](https://my.matterport.com) and [YouTube](https://www.youtube.com) tours.
* TONS of hooks, letting you (or your developer) customize to your heart's content. The single layouts for both floorplans and properties can be fully replaced by the theme, and we have lots of helpful functions to let you grab preprocessed information for display.
* This plugin works with both single-property websites and websites that showcase hundreds of properties.
* Mini search capability

== Frequently Asked Questions ==

= What APIs do you work with? =

This free version of the plugin allows for unlimited usage with *manual data entry*. Our Premium [Rent Fetch Sync](https://rentfetch.io) add-on works with Yardi RentCafe, Appfolio and RealPage OneSite (Entrata coming soon). Looking to integrate with a different property management system? [Contact us](https://rentfetch.io/get-started/) to discuss!

= Will this work on a site showing just one property and a handful of floorplans? =

Yes. You'll want to use the `[rentfetch_floorplansearch]` shorcode to show those.

= Will this work if I have hundreds of properties? =

Yes. You'll want to use the `[rentfetch_propertysearch]' shortcode to show a full availability search.

= Is there a way to show all of my properties, even if some of them don't have any availability at tht moment? =

Yes. You can use the `[rentfetch_properties]` shortcode, or you can build your own display – all the data is saved in WordPress.

= What APIs do you work with? =

This free version of the plugin allows for unlimited usage *with manual data entry*. Our [Rent Fetch Sync](https://rentfetch.io) addon works with Yardi/RentCafe (Entrata, Appfolio and RealPage support coming soon!).

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
* Feature: adding a setting to allow default faded styles on unavailable floorplan views with 'no-units-unavailable-faded' and adding those default styles.
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

