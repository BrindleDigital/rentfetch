<?php
/*
	Plugin Name:	Rent Fetch
	Plugin URI:		http://wordpress.org/plugins/rent-fetch/
	Description:	Displays searchable rental properties, floorplans, and unit availability.
	Version:		0.9.1
	Author:			Brindle Digital
	Author URI:		https://www.brindledigital.com
	Text Domain:	rentfetch
	License:		GPLv2 or later
	License URI:	http://www.gnu.org/licenses/gpl-2.0.html
*/

/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
	die( "Sorry, you are not allowed to access this page directly." );
}

// Define the version of the plugin
define ( 'RENTFETCH_VERSION', '0.9.1' );

// Plugin directories 
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


////////////////////
// PLUGIN UPDATER //
////////////////////

// Updater
require RENTFETCH_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/BrindleDigital/rentfetch',
	__FILE__,
	'rentfetch'
);

// Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch( 'main' );