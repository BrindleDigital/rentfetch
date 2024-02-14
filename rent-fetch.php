<?php
/**
	Plugin Name:    Rent Fetch
	Plugin URI:     http://wordpress.org/plugins/rent-fetch/
	Description:    Displays searchable rental properties, floorplans, and unit availability.
	Version:        0.12.6
	Author:         Brindle Digital
	Author URI:     https://www.brindledigital.com
	Text Domain:    rent-fetch
	License:        GPLv2 or later
	License URI:    http://www.gnu.org/licenses/gpl-2.0.html
 *
	@package rentfetch
 */

// Prevent direct access to the plugin.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Sorry, you are not allowed to access this page directly.' );
}

// Define the version of the plugin.
define( 'RENTFETCH_VERSION', '0.12.6' );

// Set up plugin directories.
define( 'RENTFETCH_DIR', plugin_dir_path( __FILE__ ) );
define( 'RENTFETCH_PATH', plugin_dir_url( __FILE__ ) );
define( 'RENTFETCH_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load the files
 *
 * @param   string $directory  the path to the directory to load.
 * @return  void
 */
function rentfetch_require_files_recursive( $directory ) {
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::LEAVES_ONLY
	);

	foreach ( $iterator as $file ) {
		if ( $file->isFile() && $file->getExtension() === 'php' ) {
			require_once $file->getPathname();
		}
	}
}

// Require_once all files in /lib and its subdirectories.
rentfetch_require_files_recursive( RENTFETCH_DIR . 'lib' );

/**
 * Flush the permalinks after plugin is activated.
 *
 * @return  void
 */
function rentfetch_flush_permalinks_on_activation() {
	add_action( 'init', 'flush_rewrite_rules', 999 );
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_flush_permalinks_on_activation' );

// Load Plugin Update Checker.
require RENTFETCH_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
$update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/BrindleDigital/rentfetch',
	__FILE__,
	'rentfetch'
);

// Optional: Set the branch that contains the stable release.
$update_checker->setBranch( 'main' );
