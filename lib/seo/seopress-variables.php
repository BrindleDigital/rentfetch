<?php
/**
 * Register SEOPress dynamic variables for Rent Fetch.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Format numeric values as plain text (no HTML).
 *
 * @param mixed $value Raw value.
 * @return string
 */
function rentfetch_seopress_format_number( $value ) {
	if ( null === $value || '' === $value ) {
		return '';
	}

	if ( ! is_numeric( $value ) ) {
		return sanitize_text_field( (string) $value );
	}

	$float_value = (float) $value;
	if ( floor( $float_value ) === $float_value ) {
		return (string) (int) $float_value;
	}

	return rtrim( rtrim( number_format( $float_value, 2, '.', '' ), '0' ), '.' );
}

/**
 * Get the current post object for SEOPress variables.
 *
 * @return WP_Post|null
 */
function rentfetch_seopress_get_current_post() {
	$post = get_post();
	if ( $post instanceof WP_Post ) {
		return $post;
	}

	return null;
}

/**
 * Get a location meta value for floorplan or property posts.
 *
 * @param WP_Post $post Post object.
 * @param string  $meta_key Location meta key.
 * @return string
 */
function rentfetch_seopress_get_location_meta( $post, $meta_key ) {
	if ( ! $post || ! in_array( $meta_key, array( 'city', 'state', 'zipcode' ), true ) ) {
		return '';
	}

	$value = '';
	if ( 'floorplans' === $post->post_type ) {
		$property_id = get_post_meta( $post->ID, 'property_id', true );
		if ( $property_id ) {
			if ( 'city' === $meta_key ) {
				$value = rentfetch_get_property_city( $property_id );
			} elseif ( 'state' === $meta_key ) {
				$value = rentfetch_get_property_state( $property_id );
			} elseif ( 'zipcode' === $meta_key ) {
				$value = rentfetch_get_property_zipcode( $property_id );
			}
		}
	} elseif ( 'properties' === $post->post_type ) {
		$value = get_post_meta( $post->ID, $meta_key, true );
	} else {
		$value = '';
	}

	return $value ? sanitize_text_field( $value ) : '';
}

/**
 * Get a floorplan square footage range.
 *
 * @param WP_Post $post Floorplan post.
 * @return string
 */
function rentfetch_seopress_get_floorplan_square_footage( $post ) {
	if ( ! $post || 'floorplans' !== $post->post_type ) {
		return '';
	}

	$minimum_sqft = get_post_meta( $post->ID, 'minimum_sqft', true );
	$maximum_sqft = get_post_meta( $post->ID, 'maximum_sqft', true );

	$min_value = is_numeric( $minimum_sqft ) ? (int) $minimum_sqft : 0;
	$max_value = is_numeric( $maximum_sqft ) ? (int) $maximum_sqft : 0;

	if ( $min_value && $max_value ) {
		if ( $min_value === $max_value ) {
			return number_format( $min_value ) . ' sq ft';
		}

		$range_min = min( $min_value, $max_value );
		$range_max = max( $min_value, $max_value );
		return number_format( $range_min ) . '-' . number_format( $range_max ) . ' sq ft';
	}

	if ( $min_value ) {
		return number_format( $min_value ) . ' sq ft';
	}

	if ( $max_value ) {
		return number_format( $max_value ) . ' sq ft';
	}

	return '';
}

/**
 * Get the SEOPress-friendly floorplan bed label.
 *
 * @param WP_Post $post Floorplan post.
 * @return string
 */
function rentfetch_seopress_get_floorplan_beds_label( $post ) {
	if ( ! $post || 'floorplans' !== $post->post_type ) {
		return '';
	}

	$beds = get_post_meta( $post->ID, 'beds', true );
	if ( null === $beds || '' === $beds ) {
		return '';
	}

	if ( is_numeric( $beds ) ) {
		$number = (float) $beds;
		if ( $number <= 0 ) {
			return 'Studio';
		}

		return rentfetch_seopress_format_number( $number ) . ' Bed';
	}

	return sanitize_text_field( (string) $beds );
}

/**
 * Get a floorplan pricing label for SEOPress variables.
 *
 * @param WP_Post $post Floorplan post.
 * @return string
 */
function rentfetch_seopress_get_floorplan_pricing( $post ) {
	if ( ! $post || 'floorplans' !== $post->post_type ) {
		return '';
	}

	$minimum_rent = (float) get_post_meta( $post->ID, 'minimum_rent', true );
	$maximum_rent = (float) get_post_meta( $post->ID, 'maximum_rent', true );

	// bail if there's no rent value over $50 (this is junk data).
	if ( max( $minimum_rent, $maximum_rent ) < 50 ) {
		return apply_filters( 'rentfetch_filter_floorplan_pricing', null, $minimum_rent, $maximum_rent );
	}

	$price_display = get_option( 'rentfetch_options_floorplan_pricing_display', 'range' );
	$base_rent     = null;
	$rent_display  = null;

	if ( function_exists( 'rentfetch_format_floorplan_rent_display' ) ) {
		$base_rent = rentfetch_format_floorplan_rent_display( $minimum_rent, $maximum_rent, $price_display );
	}

	if ( ! $base_rent ) {
		return '';
	}

	$monthly_required_fees = 0.0;
	if ( function_exists( 'rentfetch_get_floorplan_property_monthly_required_fees_total' ) ) {
		$monthly_required_fees = (float) rentfetch_get_floorplan_property_monthly_required_fees_total( $post->ID );
	}

	if ( $monthly_required_fees > 0 && function_exists( 'rentfetch_format_floorplan_rent_display' ) ) {
		$minimum_with_fees = ( $minimum_rent > 0 ? $minimum_rent : $maximum_rent ) + $monthly_required_fees;
		$maximum_with_fees = ( $maximum_rent > 0 ? $maximum_rent : $minimum_rent ) + $monthly_required_fees;
		$with_fees         = rentfetch_format_floorplan_rent_display( $minimum_with_fees, $maximum_with_fees, $price_display );

		$rent_display = sprintf( '%s/mo', $with_fees );
	} else {
		$rent_display = sprintf( '%s/mo', $base_rent );
	}

	$rent_display = apply_filters( 'rentfetch_filter_floorplan_pricing', $rent_display, $minimum_rent, $maximum_rent );

	return $rent_display ? sanitize_text_field( $rent_display ) : '';
}

/**
 * Register Rent Fetch variables with SEOPress.
 *
 * @param array $variables Template variables.
 * @return array
 */
function rentfetch_seopress_add_template_variables( $variables ) {
	$variables[] = '%%rentfetch_floorplan_beds%%';
	$variables[] = '%%rentfetch_floorplan_baths%%';
	$variables[] = '%%rentfetch_floorplan_sqft%%';
	$variables[] = '%%rentfetch_floorplan_pricing%%';
	$variables[] = '%%rentfetch_city%%';
	$variables[] = '%%rentfetch_state%%';
	$variables[] = '%%rentfetch_zip%%';

	return $variables;
}
add_filter( 'seopress_titles_template_variables_array', 'rentfetch_seopress_add_template_variables' );

/**
 * Provide Rent Fetch variable replacements to SEOPress.
 *
 * @param array $replacements Template replacement values.
 * @return array
 */
function rentfetch_seopress_add_template_replacements( $replacements ) {
	$post = rentfetch_seopress_get_current_post();
	$post_type = $post ? $post->post_type : '';

	$floorplan_beds = '';
	$floorplan_baths = '';
	$floorplan_sqft = '';
	$floorplan_pricing = '';
	$location_city = '';
	$location_state = '';
	$location_zip = '';

	if ( 'floorplans' === $post_type ) {
		$floorplan_beds = esc_attr( rentfetch_seopress_get_floorplan_beds_label( $post ) );
		$floorplan_baths = esc_attr( rentfetch_seopress_format_number( get_post_meta( $post->ID, 'baths', true ) ) );
		$floorplan_sqft = esc_attr( rentfetch_seopress_get_floorplan_square_footage( $post ) );
		$floorplan_pricing = esc_attr( rentfetch_seopress_get_floorplan_pricing( $post ) );
	}

	if ( 'floorplans' === $post_type || 'properties' === $post_type ) {
		$location_city = esc_attr( rentfetch_seopress_get_location_meta( $post, 'city' ) );
		$location_state = esc_attr( rentfetch_seopress_get_location_meta( $post, 'state' ) );
		$location_zip = esc_attr( rentfetch_seopress_get_location_meta( $post, 'zipcode' ) );
	}

	return array_merge(
		$replacements,
		array(
			$floorplan_beds,
			$floorplan_baths,
			$floorplan_sqft,
			$floorplan_pricing,
			$location_city,
			$location_state,
			$location_zip,
		)
	);
}
add_filter( 'seopress_titles_template_replace_array', 'rentfetch_seopress_add_template_replacements' );

/**
 * Add Rent Fetch variables to the standard SEOPress variables dropdown.
 *
 * @param array $variables Variable labels keyed by tag.
 * @return array
 */
function rentfetch_seopress_register_dynamic_variables( $variables ) {
	$variables['%%rentfetch_floorplan_beds%%'] = __( 'Rent Fetch Floorplan Beds', 'rentfetch' );
	$variables['%%rentfetch_floorplan_baths%%'] = __( 'Rent Fetch Floorplan Baths', 'rentfetch' );
	$variables['%%rentfetch_floorplan_sqft%%'] = __( 'Rent Fetch Floorplan Square Footage', 'rentfetch' );
	$variables['%%rentfetch_floorplan_pricing%%'] = __( 'Rent Fetch Floorplan Pricing', 'rentfetch' );
	$variables['%%rentfetch_city%%'] = __( 'Rent Fetch City', 'rentfetch' );
	$variables['%%rentfetch_state%%'] = __( 'Rent Fetch State', 'rentfetch' );
	$variables['%%rentfetch_zip%%'] = __( 'Rent Fetch Zip', 'rentfetch' );

	return $variables;
}
add_filter( 'seopress_get_dynamic_variables', 'rentfetch_seopress_register_dynamic_variables' );

if ( interface_exists( 'SEOPress\\Models\\GetTagValue' ) ) {
	/**
	 * Base tag class for Rent Fetch SEOPress variables.
	 */
	abstract class Rentfetch_SEOPress_Tag_Base implements SEOPress\Models\GetTagValue {
		/**
		 * Get the post from context.
		 *
		 * @param array|null $args Context args.
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

	class Rentfetch_SEOPress_Tag_Floorplan_Beds extends Rentfetch_SEOPress_Tag_Base {
		const NAME = 'rentfetch_floorplan_beds';

		public static function getDescription() {
			return __( 'Floorplan Beds', 'rentfetch' );
		}

		public function getValue( $args = null ) {
			$post = $this->get_post_from_context( $args );
			if ( ! $post || 'floorplans' !== $post->post_type ) {
				return '';
			}

			return esc_attr( rentfetch_seopress_get_floorplan_beds_label( $post ) );
		}
	}

	class Rentfetch_SEOPress_Tag_Floorplan_Baths extends Rentfetch_SEOPress_Tag_Base {
		const NAME = 'rentfetch_floorplan_baths';

		public static function getDescription() {
			return __( 'Floorplan Baths', 'rentfetch' );
		}

		public function getValue( $args = null ) {
			$post = $this->get_post_from_context( $args );
			if ( ! $post || 'floorplans' !== $post->post_type ) {
				return '';
			}

			return esc_attr( rentfetch_seopress_format_number( get_post_meta( $post->ID, 'baths', true ) ) );
		}
	}

	class Rentfetch_SEOPress_Tag_Floorplan_Sqft extends Rentfetch_SEOPress_Tag_Base {
		const NAME = 'rentfetch_floorplan_sqft';

		public static function getDescription() {
			return __( 'Floorplan Square Footage', 'rentfetch' );
		}

		public function getValue( $args = null ) {
			$post = $this->get_post_from_context( $args );
			if ( ! $post || 'floorplans' !== $post->post_type ) {
				return '';
			}

			return esc_attr( rentfetch_seopress_get_floorplan_square_footage( $post ) );
		}
	}

	class Rentfetch_SEOPress_Tag_Floorplan_Pricing extends Rentfetch_SEOPress_Tag_Base {
		const NAME = 'rentfetch_floorplan_pricing';

		public static function getDescription() {
			return __( 'Floorplan Pricing', 'rentfetch' );
		}

		public function getValue( $args = null ) {
			$post = $this->get_post_from_context( $args );
			if ( ! $post || 'floorplans' !== $post->post_type ) {
				return '';
			}

			return esc_attr( rentfetch_seopress_get_floorplan_pricing( $post ) );
		}
	}

	class Rentfetch_SEOPress_Tag_City extends Rentfetch_SEOPress_Tag_Base {
		const NAME = 'rentfetch_city';

		public static function getDescription() {
			return __( 'Rent Fetch City', 'rentfetch' );
		}

		public function getValue( $args = null ) {
			$post = $this->get_post_from_context( $args );
			if ( ! $post || ! in_array( $post->post_type, array( 'floorplans', 'properties' ), true ) ) {
				return '';
			}

			return esc_attr( rentfetch_seopress_get_location_meta( $post, 'city' ) );
		}
	}

	class Rentfetch_SEOPress_Tag_State extends Rentfetch_SEOPress_Tag_Base {
		const NAME = 'rentfetch_state';

		public static function getDescription() {
			return __( 'Rent Fetch State', 'rentfetch' );
		}

		public function getValue( $args = null ) {
			$post = $this->get_post_from_context( $args );
			if ( ! $post || ! in_array( $post->post_type, array( 'floorplans', 'properties' ), true ) ) {
				return '';
			}

			return esc_attr( rentfetch_seopress_get_location_meta( $post, 'state' ) );
		}
	}

	class Rentfetch_SEOPress_Tag_Zip extends Rentfetch_SEOPress_Tag_Base {
		const NAME = 'rentfetch_zip';

		public static function getDescription() {
			return __( 'Rent Fetch Zip', 'rentfetch' );
		}

		public function getValue( $args = null ) {
			$post = $this->get_post_from_context( $args );
			if ( ! $post || ! in_array( $post->post_type, array( 'floorplans', 'properties' ), true ) ) {
				return '';
			}

			return esc_attr( rentfetch_seopress_get_location_meta( $post, 'zipcode' ) );
		}
	}

	/**
	 * Register Rent Fetch variables for the Universal SEO metabox and schemas.
	 *
	 * @param array $tags Tags available.
	 * @return array
	 */
	function rentfetch_seopress_register_universal_tags( $tags ) {
		$rentfetch_tags = array(
			'rentfetch_floorplan_beds' => array(
				'class' => Rentfetch_SEOPress_Tag_Floorplan_Beds::class,
				'name' => __( 'Rent Fetch Floorplan Beds', 'rentfetch' ),
				'schema' => false,
				'alias' => array(),
				'custom' => null,
				'input' => '%%rentfetch_floorplan_beds%%',
				'description' => __( 'Rent Fetch Floorplan Beds', 'rentfetch' ),
			),
			'rentfetch_floorplan_baths' => array(
				'class' => Rentfetch_SEOPress_Tag_Floorplan_Baths::class,
				'name' => __( 'Rent Fetch Floorplan Baths', 'rentfetch' ),
				'schema' => false,
				'alias' => array(),
				'custom' => null,
				'input' => '%%rentfetch_floorplan_baths%%',
				'description' => __( 'Rent Fetch Floorplan Baths', 'rentfetch' ),
			),
			'rentfetch_floorplan_sqft' => array(
				'class' => Rentfetch_SEOPress_Tag_Floorplan_Sqft::class,
				'name' => __( 'Rent Fetch Floorplan Square Footage', 'rentfetch' ),
				'schema' => false,
				'alias' => array(),
				'custom' => null,
				'input' => '%%rentfetch_floorplan_sqft%%',
				'description' => __( 'Rent Fetch Floorplan Square Footage', 'rentfetch' ),
			),
			'rentfetch_floorplan_pricing' => array(
				'class' => Rentfetch_SEOPress_Tag_Floorplan_Pricing::class,
				'name' => __( 'Rent Fetch Floorplan Pricing', 'rentfetch' ),
				'schema' => false,
				'alias' => array(),
				'custom' => null,
				'input' => '%%rentfetch_floorplan_pricing%%',
				'description' => __( 'Rent Fetch Floorplan Pricing', 'rentfetch' ),
			),
			'rentfetch_city' => array(
				'class' => Rentfetch_SEOPress_Tag_City::class,
				'name' => __( 'Rent Fetch City', 'rentfetch' ),
				'schema' => false,
				'alias' => array(),
				'custom' => null,
				'input' => '%%rentfetch_city%%',
				'description' => __( 'Rent Fetch City', 'rentfetch' ),
			),
			'rentfetch_state' => array(
				'class' => Rentfetch_SEOPress_Tag_State::class,
				'name' => __( 'Rent Fetch State', 'rentfetch' ),
				'schema' => false,
				'alias' => array(),
				'custom' => null,
				'input' => '%%rentfetch_state%%',
				'description' => __( 'Rent Fetch State', 'rentfetch' ),
			),
			'rentfetch_zip' => array(
				'class' => Rentfetch_SEOPress_Tag_Zip::class,
				'name' => __( 'Rent Fetch Zip', 'rentfetch' ),
				'schema' => false,
				'alias' => array(),
				'custom' => null,
				'input' => '%%rentfetch_zip%%',
				'description' => __( 'Rent Fetch Zip', 'rentfetch' ),
			),
		);

		return array_merge( $tags, $rentfetch_tags );
	}
	add_filter( 'seopress_tags_available', 'rentfetch_seopress_register_universal_tags' );
}
