<?php
/**
 * Base SEOPress tag class.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( interface_exists( 'SEOPress\\Models\\GetTagValue' ) && ! class_exists( 'Rentfetch_SEOPress_Tag_Base' ) ) {
	/**
	 * Base tag class for Rent Fetch SEOPress variables.
	 */
	abstract class Rentfetch_SEOPress_Tag_Base implements SEOPress\Models\GetTagValue {
		/**
		 * Get the post from context.
		 *
		 * @param array|null $args Context arguments.
		 * @return WP_Post|null
		 */
		protected function get_post_from_context( $args ) {
			$context = isset( $args[0] ) ? $args[0] : null;
			if ( ! $context || ! isset( $context['post'] ) ) {
				return null;
			}

			return $context['post'] instanceof WP_Post ? $context['post'] : null;
		}
	}
}
