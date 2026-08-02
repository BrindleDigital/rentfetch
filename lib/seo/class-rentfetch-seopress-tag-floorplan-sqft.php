<?php
/**
 * SEOPress floor plan square-footage tag.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-rentfetch-seopress-tag-base.php';

if ( class_exists( 'Rentfetch_SEOPress_Tag_Base' ) && ! class_exists( 'Rentfetch_SEOPress_Tag_Floorplan_Sqft' ) ) {
	/**
	 * Provides the floor plan square-footage value to SEOPress.
	 */
	class Rentfetch_SEOPress_Tag_Floorplan_Sqft extends Rentfetch_SEOPress_Tag_Base {
		const NAME = 'rentfetch_floorplan_sqft';

		/**
		 * Get the tag description.
		 *
		 * @return string
		 */
		public static function getDescription() {
			return __( 'Floorplan Square Footage', 'rentfetch' );
		}

		/**
		 * Get the tag value.
		 *
		 * @param array|null $args Context arguments.
		 * @return string
		 */
		public function getValue( $args = null ) {
			$post = $this->get_post_from_context( $args );
			if ( ! $post || 'floorplans' !== $post->post_type ) {
				return '';
			}

			return esc_attr( rentfetch_seopress_get_floorplan_square_footage( $post ) );
		}
	}
}
