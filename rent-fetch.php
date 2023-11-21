<?php
/*
	Plugin Name: Rent Fetch
	Plugin URI: https://github.com/BrindleDigital/rentfetch
	Description: Displays rental properties, floorplans, and unit availability
	Version: 0.4.3
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
define ( 'RENTFETCH_VERSION', '0.4.3' );

// Plugin directory
define( 'RENTFETCH_DIR', plugin_dir_path( __FILE__ ) );
define( 'RENTFETCH_PATH', plugin_dir_url( __FILE__ ) );
define( 'RENTFETCH_BASENAME', plugin_basename( __FILE__ ) );

///////////////////
// FILE INCLUDES //
///////////////////

function rentfetch_require_files_recursive( $directory ) {
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::LEAVES_ONLY
	);

	foreach ($iterator as $file) {
		if ($file->isFile() && $file->getExtension() === 'php') {
			require_once $file->getPathname();
		}
	}
}

// require_once all files in /lib and its subdirectories
rentfetch_require_files_recursive( RENTFETCH_DIR . 'lib' );

// Flush the permalinks after plugin is activated
function rentfetch_flush_permalinks_on_activation() {	
	add_action('init', 'flush_rewrite_rules', 999 );
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_flush_permalinks_on_activation' );
