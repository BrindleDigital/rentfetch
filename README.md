# Rent Fetch

A plugin which displays property, floorplan and unit availability and information.
[Visit the official site](https://rentfetch.io)

## Getting Started

If you'll be entering your information manually, just start adding some properties and floorplans. Please note that the property ID for a given floorplan MUST match the property ID of one of the properties for it to be found in search.

It's also important to note that the default property search only will show properties with _available_ floorplans. That means if you don't have any floorplans with the number of available units set to a number above zero, no properties will display in the main properties search.

For manual entry properties, **units are not required**. That means you just need to enter properties and floorplans; the various searches will always function even without any units ever being entered (because we want to keep things easy).

## Premium Addons

### Rent Fetch Sync

Rent Fetch Sync allows for pulling properties, floorplans, and units down from a number of APIs, including Yardi/RentCafe, RealPage and Appfolio. It's not required for the base plugin to function normally, but can be helpful for automatically handling your data input needs and keeping availability up to date leveraging systems you're already updating regularly. [Get that right here](https://rentfetch.io)

## Helpful third-party WordPress plugins

None of these are required for normal operation of this plugin.

### WP Cron HTTP Auth

If you're having trouble with syncing down on a development environment, it might be because you're behind basic authentication, which prevents WP Cron from running. Use the [WP Cron HTTP Auth plugin](https://wordpress.org/plugins/wp-cron-http-auth/) to fix that.

### Admin Columns Pro

Used for fancy columns allowing for birds-eye editing and seeing what data is in each floorplan/property/unit. However, this is merely an enhancement for manual-entry properties; Rent Fetch doesn't require use of Admin Columns Pro

### Relevanssi

The built-in WordPress text search ... isn't good. Relevanssi fills in that gap. It's important to:

-   Make sure that the properties, floorplans, and units content types are indexed
-   Index ALL custom fields (unless you know what you're doing and can meaningfully select which custom fields to index)
-   Use Relevanssi for the admin search. Please note that we've baked the truly vital search modifications into the plugin itself, but this can still help the admin search find better results.

## What's in the box

### Included Content types

-   Properties
-   Floorplans
-   Units
-   Neighborhoods

### Included Taxonomies

-   Property types (properties)
-   Floorplan types
-   Amenities (properties)

### Included WordPress templates

-   single-properties.php (override this in your theme if you like by dropping a file into your main theme directory)
-   single-floorplans.php (override this in your theme if you like by dropping a file into your main theme directory, currently not used for anything)
