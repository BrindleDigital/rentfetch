=== Rent Fetch ===
Contributors: jonschr
Tags: apartments, properties, yardi, entrata, appfolio
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.3
Stable tag: 0.39.5
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
* Property and floor plan editors include searchable Categories tabs with native WordPress term selection and creation controls.
* Google Maps integration (both for the property search and for use on each property page. To use this, you’ll need to set up an API key for the Google Maps Javascript API at [maps.googleapis.com](https://maps.googleapis.com))
* Sliders to show property images, floorplan images, and nearby properties (we use the MIT-licensed  [Blaze Slider](https://blaze-slider.dev) for these, and you don’t need to set up anything for these to work)
* Properties and floor plans can display manual or synced video and virtual tours from YouTube, Vimeo, Matterport, Zillow 3D Home, TheViewVR, Google Drive, and compatible iframe or oEmbed providers.
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

= 0.39.5 =

* Showed the unit Photos column only when at least one displayed unit has unit-level photos.
* Filled units without their own photos with the complete parent floor plan gallery instead of leaving an empty space.

= 0.39.4 =

* Added unit-level photo galleries to desktop availability tables and mobile unit details, including compact stacked previews when multiple images are available.
* Added consistent desktop thumbnail navigation to property, floor plan, and unit lightboxes, with rails that size themselves to the number of images.
* Made Escape reliably close every image lightbox, including while it is opening.

= 0.39.3 =

* Fixed property and floor plan WebP lightboxes being rendered as scrollable external pages, and removed unwanted image captions.

= 0.39.2 =

* Made property availability filtering, unavailable styling, pricing, and price sorting consistently use floorplans with available units, while retaining pricing for future-dated available units.
* Added property sorting by default order, availability, price, or name without changing the configured default sort.
* Kept property search fields wrapping independently from the aligned Filters and Sort controls, with matching responsive sizing and icons.

= 0.39.1 =

* Fixed a compatibility issue with the Genesis theme framework, whose global administrator styles can override the native closed-dialog display state and leave property editor confirmation dialogs visible.

= 0.39.0 =

* Rebuilt the single floor plan template with consistent full-width sections, an availability-focused hero, refined feature and fee layouts, and improved secondary-text styling shared with single property pages.
* Reworked available units into a spacious desktop table and responsive mobile cards, with regular-sized application buttons, availability and amenity pills, styled specials, complete unit details, and no unit lightboxes.
* Added combined manual and synced Videos & Tours support for properties, floor plans, and units, including provider-aware video and virtual-tour handling, editor previews, and responsive front-end tour sections.
* Improved property and floor plan imagery with responsive image delivery, gallery thumbnails, full-size lightbox sources, and automatic contain-or-cover handling that keeps floor plan drawings aligned without cropping property photos.
* Reordered property fees to appear immediately before maps on single properties and last on single floor plans, while refining amenities, headings, section spacing, and supporting text colors across both templates.
* Preserved legacy hooks, filters, theme template overrides, and CSS selectors where possible, and hardened tour parsing, external links, iframe output, and supported CDN URL handling.

= 0.38.2 =

* Added searchable Categories tabs to the property and floor plan editors using native WordPress term controls and filtered taxonomy labels.
* Added compact, expandable hierarchy navigation to property, floor plan, and unit editors, with immediate sync-status tooltips that remain interactive for copying and scrolling.
* Displayed Engrain-synced property, floor plan, and unit images in editor Images tabs alongside Yardi-synced images.
* Removed the duplicate taxonomy sidebar boxes.
* Standardized the first property and floor plan editor tab label as Basic Information.

= 0.38.1 =

* Completed plugin-wide checks with WordPress Plugin Check and PHPCS using the WordPress Coding Standards.
* Hardened settings saves with request-method, capability, and nonce checks.
* Validated built-in search parameters without changing extension parameters, and bounded analytics storage.

= 0.38.0 =

* Rebuilt the floor plan and unit editing screens as responsive, accessible tabbed interfaces that retain the existing fields while organizing their rendering, assets, lazy loading, and save handling into focused components.
* Added prominent identity controls for floor plans and units, including friendly searchable property and floor plan selectors that show record names alongside their IDs.
* Added confirmation dialogs and server-side safeguards before changing sync-critical sources, relationships, floor plan IDs, or unit IDs.
* Required the appropriate relationship IDs when manually publishing properties, floor plans, and units, with incomplete manual records kept as drafts and explained through an administrator notice; sync-created records remain unaffected.
* Condensed the floor plan information fields into full-width four-column numeric rows, with compact help tooltips and a responsive two-column layout on smaller screens.
* Improved editor performance by lazy-loading diagnostics and synced floor plan image previews.
* Kept the Diagnostics tab active while navigating between related properties, floor plans, and units, and streamlined property diagnostic actions within the hierarchy section.

= 0.37.2 =

* Added core compatibility for Engrain-backed partial sync data across properties, floor plans, and units, including source-aware field controls, sync endpoint labels, integration settings handling, and orphan-cleanup scheduling.
* Added support for Engrain property galleries and floor plan images throughout frontend and administrator displays.
* Added provider-neutral synced fee handling and total monthly pricing support so Engrain pricing and expense data can power fee-inclusive property, floor plan, and unit displays while retaining existing Yardi compatibility.
* Rebuilt the property editor as a responsive, accessible tabbed interface for contact details, content, specials, images, office hours, fees, and diagnostics.
* Improved property editor performance by loading diagnostics and synced previews only when needed, while remembering each administrator's active tab.
* Refactored property editor assets, rendering, and save handling into focused components for easier maintenance and integration compatibility.

= 0.37.1 =

* Improved property editor fee previews with clearer source labels, a prominent global visibility warning, full-width fee tables, and backend previews that remain available when frontend fees are disabled.
* Added the effective monthly total above both synced API and fallback fee previews while removing the separate frontend pricing explanation and contributor breakdown.
* Updated property hierarchy highlighting to use a subtle WordPress-blue outline instead of a solid fill, keeping property titles and IDs readable while preserving sync-status background colors.

= 0.37 =

* Added a global Property Fees setting to show or hide fee details and fee-inclusive pricing across the frontend.
* Added unit square footage display support so unit listings can include unit-level square footage details.

= 0.36.5 =

* Added an Apply Online URL field for properties and displayed it as a highlighted single-property sidebar button below Book Tour.
* Added optional start/end date controls for property specials so specials can display only inside a configured date window.
* Added Flatpickr-backed property specials date range controls in the property editor.
* Changed the default query caching behavior to be uncached unless explicitly enabled.

= 0.36.4 =

* Clear cached Rent Fetch Sync API bootstrap error state when sync settings are saved, including any stale refresh lock.

= 0.36.3 =

* Prevented empty property and floorplan search results from being stored in Rent Fetch search/query transients.
* Treated existing empty search/query cache entries as misses so they do not continue serving zero-result searches while they age out.
* Prevented all-unavailable floorplan aggregates and rendered property/floorplan results from being cached when no result has positive availability.
* Always show RentFetch admin bar cache controls, including on sites with five or fewer properties.
* Refined the RentFetch admin bar dropdown with tighter content spacing, corrected singular count labels, and clearer sync status display.
* Kept healthy sync summaries visible as non-clickable text so administrators do not land on empty needs-attention lists.
* Improved admin bar sync status rendering so inactive status rows do not take up space.
* Updated the admin documentation menu link to point to the Rent Fetch getting started docs.

Earlier release history is retained in the source repository.
