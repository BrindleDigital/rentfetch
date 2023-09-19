<?php
/*
	Plugin Name: Rent Fetch
	Plugin URI: https://github.com/jonschr/rentfetch
    Description: Syncs properties, and floorplans with various rental APIs
	Version: 0.1
    Author: Brindle Digital
    Author URI: https://www.brindledigital.com/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
*/

/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    die( "Sorry, you are not allowed to access this page directly." );
}

// Define the version of the plugin
define ( 'RENTFETCH_VERSION', '0.1' );

// Plugin directory
define( 'RENTFETCH_DIR', plugin_dir_path( __FILE__ ) );
define( 'RENTFETCH_PATH', plugin_dir_url( __FILE__ ) );

// Define path and URL to the ACF plugin.
define( 'RENTFETCH_ACF_PATH', plugin_dir_path( __FILE__ ) . 'vendor/acf/' );
define( 'RENTFETCH_ACF_URL', plugin_dir_url( __FILE__ ) . 'vendor/acf/' );

$search_components = get_option( 'options_map_search_components' );

//////////////////////////////
// INCLUDE ACTION SCHEDULER //
//////////////////////////////

require_once( plugin_dir_path( __FILE__ ) . 'vendor/action-scheduler/action-scheduler.php' );

///////////////////
// FILE INCLUDES //
///////////////////

function rentfetch_require_files_recursive($directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            require_once $file->getPathname();
        }
    }
}

// require_once all files in /lib and its subdirectories
rentfetch_require_files_recursive(RENTFETCH_DIR . 'lib');

//////////////////////
// START THE ENGINE //
//////////////////////

add_action( 'wp_loaded', 'rentfetch_start_sync' );

////////////////////
// PLUGIN UPDATER //
////////////////////

// Updater
require RENTFETCH_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/jonschr/rentfetch',
	__FILE__,
	'rentfetch'
);

// Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch( 'main' );
