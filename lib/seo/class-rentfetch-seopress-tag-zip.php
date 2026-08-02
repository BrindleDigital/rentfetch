<?php
/**
 * SEOPress ZIP code tag.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-rentfetch-seopress-tag-base.php';

if ( class_exists( 'Rentfetch_SEOPress_Tag_Base' ) && ! class_exists( 'Rentfetch_SEOPress_Tag_Zip' ) ) {
	/**
	 * Provides the ZIP code value to SEOPress.
	 */
	class Rentfetch_SEOPress_Tag_Zip extends Rentfetch_SEOPress_Tag_Base {
		const NAME = 'rentfetch_zip';

		/**
		 * Get the tag description.
		 *
		 * @return string
		 */
		public static function getDescription() {
			return __( 'Rent Fetch Zip', 'rentfetch' );
		}

		/**
		 * Get the tag value.
		 *
		 * @param array|null $args Context arguments.
		 * @return string
		 */
		public function getValue( $args = null ) {
			$post = $this->get_post_from_context( $args );
			if ( ! $post || ! in_array( $post->post_type, array( 'floorplans', 'properties' ), true ) ) {
				return '';
			}

			return esc_attr( rentfetch_seopress_get_location_meta( $post, 'zipcode' ) );
		}
	}
}
