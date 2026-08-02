<?php
/**
 * SEOPress floor plan baths tag.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-rentfetch-seopress-tag-base.php';

if ( class_exists( 'Rentfetch_SEOPress_Tag_Base' ) && ! class_exists( 'Rentfetch_SEOPress_Tag_Floorplan_Baths' ) ) {
	/**
	 * Provides the floor plan baths value to SEOPress.
	 */
	class Rentfetch_SEOPress_Tag_Floorplan_Baths extends Rentfetch_SEOPress_Tag_Base {
		const NAME = 'rentfetch_floorplan_baths';

		/**
		 * Get the tag description.
		 *
		 * @return string
		 */
		public static function getDescription() {
			return __( 'Floorplan Baths', 'rentfetch' );
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

			return esc_attr( rentfetch_seopress_format_number( get_post_meta( $post->ID, 'baths', true ) ) );
		}
	}
}
