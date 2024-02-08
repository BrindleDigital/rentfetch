<?php
/**
 * Logging
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'console_log' ) ) {

	/**
	 * Set up a console_log function for debugging
	 *
	 * @param array $data  data to debug.
	 *
	 * @return void.
	 */
	function console_log( $data ) {
		echo '<script>';
		echo 'console.log(' . wp_json_encode( $data ) . ')';
		echo '</script>';
	}

}
