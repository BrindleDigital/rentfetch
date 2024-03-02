<?php
/**
 * Logging
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'rentfetch_console_log' ) ) {

	/**
	 * Set up a rentfetch_console_log function for debugging
	 *
	 * @param array $data  data to debug.
	 *
	 * @return void.
	 */
	function rentfetch_console_log( $data ) {
		echo '<script>';
		echo 'console.log(' . wp_json_encode( $data ) . ')';
		echo '</script>';
	}

}
