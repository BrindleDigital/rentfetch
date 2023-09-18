# Rent Fetch
A plugin which gathers properties and floorplans from the Yardi API and displays them in a number of different ways.
[More detailed information in the Wiki](https://github.com/jonschr/rent-fetch/wiki)

## Important Note
If you're having trouble with syncing down on a development environment, it might be because you're behind basic authentication, which prevents WP Cron from running. Use the [WP Cron HTTP Auth plugin](https://wordpress.org/plugins/wp-cron-http-auth/) to fix that.

## Dependencies (for full functionality)
- ACF Pro (bundled with this plugin): Used for setting up custom fields. You can install this alongside Rent Fetch, but you don't have to (it's included)
- [Admin Columns Pro](https://www.admincolumns.com/): Used for fancy columns allowing for birds-eye editing and seeing what data is in each floorplan/property/neighborhood
- Admin Columns Pro ACF addon (allows for displaying and editing ACF fields)
- [Metabox.io](https://wordpress.org/plugins/meta-box/): used for CPT connections between neighborhoods and properties, not bundled
- [Metabox.io Relationships](https://docs.metabox.io/extensions/mb-relationships/)
- [Action Scheduler](https://actionscheduler.org/): Bundled with this plugin, this provides functionality for regularly getting things from the APIs more reliably than WP Cron alone. Still triggered by WP Cron, so don't disable that – but this piece of functionality makes it possible for your site to sync in the background without crashing your site (especially if you have a lot of properties).

## Content types
- Floorplans
- Properties
- Neighborhoods

## Taxonomies
- Property types (properties)
- Floorplan types
- Amenities (properties)
- Areas (neighborhoods)

## Templates
- single-properties.php (override this in your theme if you like by dropping a file into your main theme directory)
- single-floorplans.php (override this in your theme if you like by dropping a file into your main theme directory, currently not used for anything)

## Shortcodes
This plugin includes three shortcodes. There's more information about each of these on the [wiki](https://github.com/jonschr/rent-fetch/wiki/Included-shortcodes), but none of them take any parameters.
```
[propertymap]
[propertysearch]
[favoriteproperties]
```

## Gutenberg blocks
- Floorplans: shows a configurable grid of the floorplans, using either local information or information from an API that's been synced into a content type.

## Customization
- [Labels](https://github.com/jonschr/rent-fetch/wiki/Customizing-labels-for-beds,-baths,-and-square-feet): you can customize the labels for bedrooms, bathrooms, and square footage. Useful for setting "0 bedroom" to be "studio" instead.
- [Property URLs](https://github.com/jonschr/rent-fetch/wiki/Customizing-property-URLs): sometimes, property managers don't add "http://" or "https://" in front of the url. If we click a link without that, it won't work property. So there's a filter to change that, and the plugin already hooks into there to add the http or https – but this can also be used to disallow particular buttons from outputting, etc.
