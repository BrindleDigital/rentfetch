<?php
/**
 * SEOPress floor plan pricing tag.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-rentfetch-seopress-tag-base.php';

if ( class_exists( 'Rentfetch_SEOPress_Tag_Base' ) && ! class_exists( 'Rentfetch_SEOPress_Tag_Floorplan_Pricing' ) ) {
	/**
	 * Provides the floor plan pricing value to SEOPress.
	 */
	class Rentfetch_SEOPress_Tag_Floorplan_Pricing extends Rentfetch_SEOPress_Tag_Base {
		const NAME = 'rentfetch_floorplan_pricing';

		/**
		 * Get the tag description.
		 *
		 * @return string
		 */
		public static function getDescription() {
			return __( 'Floorplan Pricing', 'rentfetch' );
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

			return esc_attr( rentfetch_seopress_get_floorplan_pricing( $post ) );
		}
	}
}
