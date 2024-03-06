=== Rent Fetch ===
Contributors: jonschr
Tags: property, apartment, rent, yardi, realpage
Requires at least: 6.4
Tested up to: 6.4.1
Requires PHP: 7.3
Stable tag: 0.14.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays searchable rental properties, floorplans, and unit availability.


== Description ==

Rent Fetch lets you create properties and connect floorplans and units, showing off your property portfolio – including availability information.

[Check out the wiki for more documentation](https://github.com/BrindleDigital/rentfetch)

**Property availability map**

View all of your properties with available floorplans on a Google map, and let users filter by beds, baths, price, and more. Text-based search is also available, and we integrate with Relevanssi, so your users can search for anything at all (the city or zip code works fine)

**Individual Property template**

Showcase your property – and all associated floorplans – with a unique URL for each property. Each one has a floorplan listing including availability, image gallery, location map, and more!

**Floorplans search**

You don't have to have hundreds of properties to use this plugin. For portfolios of 1-5 properties, show off all of your floorplans in one place, letting users filter and search.

**Units listing**

Our individual floorplan template goes all the way down to the unit level, including unique availability links on a per-unit basis.

== Features ==

* Native WordPress content types are used for everything, so a developer can create their own layouts.
* Google maps integration (both for the property search and for use on each property page. To use this, you'll need to set up an API key for the Google Maps Javascript API at [maps.googleapis.com](https://maps.googleapis.com))
* Sliders to show property images, floorplan images, and nearby properties (we use the MIT-licensed [Blaze Slider](https://blaze-slider.dev) for these, and you don't need to set up anything for these to work)
* Your floorplans can display [Matterport](https://my.matterport.com) and [YouTube](https://www.youtube.com) tours.
* TONS of hooks, letting you (or your developer) customize to your heart's content. The single layouts for both floorplans and properties can be fully replaced by the theme, and we have lots of helpful functions to let you grab preprocessed information for display.
* This plugin works with both single-property websites and websites that showcase hundreds of properties.
* Adding minisearch capability

== Pro ==

Our [Rent Fetch Sync](https://rentfetch.io) addon works with Yardi/RentCafe (Entrata, Appfolio and RealPage support coming soon!).

== Frequently Asked Questions ==

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

