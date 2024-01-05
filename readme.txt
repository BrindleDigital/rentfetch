=== Rent Fetch ===
Contributors: jonschr
Tags: properties, property, rental, floorplans, map, google map, apartment, rent, yardi, entrata, appfolio, realpage
Requires at least: 6.4
Tested up to: 6.4.1
Requires PHP: 7.3
Stable tag: 0.4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays searchable rental properties, floorplans, and unit availability.


== Description ==

Rent Fetch lets you create properties and connect floorplans and units, showing off your property portfolio – including availability information.

[Check out the wiki for documentation](https://github.com/BrindleDigital/rentfetch)

**Property availability map**

View all of your properties with available floorplans on a Google map, and let users filter by beds, baths, price, and more. Text-based search is also available, and we integrate with Relevanssi, so your users can search for anything at all (the city or zip code works fine)

**Individual Property template**

Showcase your property – and all associated floorplans – with a unique URL for each property. Each one has a floorplan listing including availability, image gallery, location map, and more!

**Floorplans search**

You don't have to have hundreds of properties to use this plugin. For portfolios of 1-5 properties, show off all of your floorplans in one place, letting users filter and search.

**Units listing**

Our individual floorplan template goes all the way down to the unit level, including unique availability links on a per-unit basis.

== Features ==

* Google maps integration (both for the property search and for use on each property page)
* Native WordPress content types are used for everything, so a developer can create their own layouts.
* TONS of hooks, letting you customize to your heart's content.
* Works with both single-property websites and websites that showcase hundreds of properties

== Pro ==

Our [Rent Fetch Sync](https://rentfetch.io) addon works with Yardi/RentCafe (Entrata, Appfolio and RealPage support coming soon!).

== Frequently Asked Questions ==

= Will this work on a site showing just one property and a handful of floorplans? =

Yes. You'll want to use the `[floorplansearch]` shorcode to show those.

= Will this work if I have hundreds of properties? =

Yes. You'll want to use the `[propertysearch]' shortcode to show a full availability search.

= Is there a way to show all of my properties, even if some of them don't have any availability at tht moment? =

Yes. You can use the `[properties]` shortcode, or you can build your own display – all the data is saved in WordPress.

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

