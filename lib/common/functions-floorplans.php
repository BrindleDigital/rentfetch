<?php
/**
 * This file has the Rent Fetch functions for getting floorplan data.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add availability classes to floor plan posts in archive contexts.
 *
 * @param array $classes Existing post classes.
 * @return array Modified post classes.
 */
function rentfetch_floorplans_post_classes( $classes ) {

	$units_count = rentfetch_get_floorplan_units_count_from_meta();
	if ( $units_count > 0 ) {
		$classes[] = 'has-units-available';
	} else {
		$classes[] = 'no-units-available';

		$fade_out_unavailable = get_option( 'rentfetch_options_floorplan_apply_styles_no_floorplans' );
		if ( '1' === $fade_out_unavailable ) {
			$classes[] = 'no-units-available-faded';
		}
	}

	return $classes;
}
add_filter( 'rentfetch_filter_floorplans_post_classes', 'rentfetch_floorplans_post_classes', 10, 1 );

// * Title

/**
 * Get the floorplan title
 *
 * @return string the title of the floorplan.
 */
function rentfetch_get_floorplan_title() {
	$title = apply_filters( 'rentfetch_filter_floorplan_title', get_the_title() );
	return $title;
}

/**
 * Echo the floorplan title
 *
 * @return void.
 */
function rentfetch_floorplan_title() {
	$title = rentfetch_get_floorplan_title();
	if ( $title ) {
		echo esc_html( $title );
	}
}

// * Bedrooms

/**
 * Get the bedroom label
 *
 * @return string the label for the number of bedrooms.
 */
function rentfetch_get_floorplan_bedrooms() {
	$beds_number = (int) get_post_meta( get_the_ID(), 'beds', true );

	$beds_number = apply_filters( 'rentfetch_filter_floorplan_bedrooms', $beds_number );
	return apply_filters( 'rentfetch_get_bedroom_number_label', $beds_number );
}

/**
 * Echo the label for the number of bedrooms
 *
 * @return void.
 */
function rentfetch_floorplan_bedrooms() {
	echo wp_kses_post( rentfetch_get_floorplan_bedrooms() );
}

// * Bathrooms

/**
 * Get the bathroom label
 *
 * @return string the label for the number of bathrooms.
 */
function rentfetch_get_floorplan_bathrooms() {
	$baths_number = (float) get_post_meta( get_the_ID(), 'baths', true );

	$baths_number = apply_filters( 'rentfetch_filter_floorplan_bathrooms', $baths_number );
	return apply_filters( 'rentfetch_get_bathroom_number_label', $baths_number );
}

/**
 * Echo the label for the number of bathrooms
 *
 * @return void.
 */
function rentfetch_floorplan_bathrooms() {
	echo wp_kses_post( rentfetch_get_floorplan_bathrooms() );
}

// * Square feet

/**
 * Get the square feet label
 *
 * @return string the label for the number of square feet.
 */
function rentfetch_get_floorplan_square_feet() {
	$minimum_sqft = intval( get_post_meta( get_the_ID(), 'minimum_sqft', true ) );
	$maximum_sqft = intval( get_post_meta( get_the_ID(), 'maximum_sqft', true ) );

	if ( $minimum_sqft && $maximum_sqft ) {
		if ( $minimum_sqft === $maximum_sqft ) {
			$square_feet = sprintf( '%s', number_format( $minimum_sqft ) );
		} elseif ( $minimum_sqft < $maximum_sqft ) {
			$square_feet = sprintf( '%s-%s', number_format( $minimum_sqft ), number_format( $maximum_sqft ) );
		} elseif ( $minimum_sqft > $maximum_sqft ) {
			$square_feet = sprintf( '%s-%s', number_format( $maximum_sqft ), number_format( $minimum_sqft ) );
		}
	} elseif ( $minimum_sqft && ! $maximum_sqft ) {
			$square_feet = sprintf( '%s', number_format( $minimum_sqft ) );
	} elseif ( ! $minimum_sqft && $maximum_sqft ) {
		$square_feet = sprintf( '%s', number_format( $maximum_sqft ) );
	} else {
		$square_feet = null;
	}

	$square_feet = apply_filters( 'rentfetch_filter_floorplan_square_feet', $square_feet );
	return apply_filters( 'rentfetch_get_square_feet_number_label', $square_feet );
}

/**
 * Echo the label for the number of square feet
 *
 * @return void.
 */
function rentfetch_floorplan_square_feet() {
	echo wp_kses_post( rentfetch_get_floorplan_square_feet() );
}

// * Number available

/**
 * Get the number of available units with label
 *
 * @return string the number of available units with label.
 */
function rentfetch_get_floorplan_available_units() {
	$available_units = get_post_meta( get_the_ID(), 'available_units', true );

	return apply_filters( 'rentfetch_get_available_units_label', $available_units );
}

/**
 * Echo the number of available units with label
 *
 * @return void.
 */
function rentfetch_floorplan_available_units() {
	echo wp_kses_post( rentfetch_get_floorplan_available_units() );
}

// * Pricing

/**
 * Format a floorplan rent display string based on the configured display mode.
 *
 * @param float       $minimum_rent Minimum monthly rent.
 * @param float       $maximum_rent Maximum monthly rent.
 * @param string|null $price_display Display mode ('range' or 'minimum').
 * @return string|null
 */
function rentfetch_format_floorplan_rent_display( $minimum_rent, $maximum_rent, $price_display = null ) {
	$minimum_rent = is_numeric( $minimum_rent ) ? (float) $minimum_rent : null;
	$maximum_rent = is_numeric( $maximum_rent ) ? (float) $maximum_rent : null;
	$minimum_rent = ( null !== $minimum_rent && $minimum_rent > 0 ) ? $minimum_rent : null;
	$maximum_rent = ( null !== $maximum_rent && $maximum_rent > 0 ) ? $maximum_rent : null;

	if ( null === $minimum_rent && null === $maximum_rent ) {
		return null;
	}

	if ( null === $minimum_rent ) {
		$minimum_rent = $maximum_rent;
	}
	if ( null === $maximum_rent ) {
		$maximum_rent = $minimum_rent;
	}

	if ( null === $minimum_rent || null === $maximum_rent ) {
		return null;
	}

	if ( $maximum_rent < $minimum_rent ) {
		$temp         = $minimum_rent;
		$minimum_rent = $maximum_rent;
		$maximum_rent = $temp;
	}

	if ( ! $price_display ) {
		$price_display = get_option( 'rentfetch_options_floorplan_pricing_display', 'range' );
	}

	if ( 'minimum' === $price_display ) {
		return sprintf( 'From $%s', number_format( $minimum_rent ) );
	}

	if ( $minimum_rent === $maximum_rent ) {
		return sprintf( '$%s', number_format( $minimum_rent ) );
	}

	return sprintf( '$%s-$%s', number_format( $minimum_rent ), number_format( $maximum_rent ) );
}

/**
 * Resolve a floorplan post to its connected property post ID via matching property_id meta.
 *
 * @param int|null $floorplan_post_id Optional floorplan post ID.
 * @return int|null Property post ID when found, null otherwise.
 */
function rentfetch_get_connected_property_post_id_for_floorplan( $floorplan_post_id = null ) {
	if ( ! $floorplan_post_id ) {
		$floorplan_post_id = get_the_ID();
	}

	$floorplan_post_id = (int) $floorplan_post_id;
	if ( $floorplan_post_id <= 0 ) {
		return null;
	}

	$property_id = trim( (string) get_post_meta( $floorplan_post_id, 'property_id', true ) );
	if ( '' === $property_id || ! function_exists( 'rentfetch_get_post_id_from_property_id' ) ) {
		return null;
	}

	$property_post_id = (int) rentfetch_get_post_id_from_property_id( $property_id );
	if ( $property_post_id <= 0 ) {
		return null;
	}

	return $property_post_id;
}

/**
 * Get all fixed monthly required fees applicable to a floorplan.
 *
 * Includes related property fees plus provider-normalized floorplan fees.
 *
 * @param int|null $floorplan_post_id Optional floorplan post ID.
 * @return float
 */
function rentfetch_get_floorplan_property_monthly_required_fees_total( $floorplan_post_id = null ) {
	if ( ! $floorplan_post_id ) {
		$floorplan_post_id = get_the_ID();
	}
	$scoped_total     = function_exists( 'rentfetch_get_synced_scoped_monthly_required_fee_total' )
		? rentfetch_get_synced_scoped_monthly_required_fee_total( $floorplan_post_id )
		: 0.0;
	$property_post_id = rentfetch_get_connected_property_post_id_for_floorplan( $floorplan_post_id );
	if ( ! $property_post_id ) {
		return $scoped_total;
	}

	if ( function_exists( 'rentfetch_get_effective_monthly_required_total_fees_for_property' ) ) {
		$effective_total = rentfetch_get_effective_monthly_required_total_fees_for_property( $property_post_id );
		if ( is_numeric( $effective_total ) && (float) $effective_total > 0 ) {
			return (float) $effective_total + $scoped_total;
		}
		return $scoped_total;
	}

	$property_raw = get_post_meta( $property_post_id, 'property_monthly_required_total_fees', true );
	if ( '' === (string) $property_raw ) {
		return $scoped_total;
	}

	$property_total = null;
	if ( function_exists( 'rentfetch_extract_first_numeric_fee_value' ) ) {
		$property_total = rentfetch_extract_first_numeric_fee_value( $property_raw );
	} elseif ( is_numeric( $property_raw ) ) {
		$property_total = (float) $property_raw;
	}

	if ( null === $property_total || $property_total <= 0 ) {
		return $scoped_total;
	}

	return (float) $property_total + $scoped_total;
}

/**
 * Get the pricing for the floorplan
 *
 * @return string the pricing for the floorplan.
 */
function rentfetch_get_floorplan_pricing() {
	$minimum_rent = intval( get_post_meta( get_the_ID(), 'minimum_rent', true ) );
	$maximum_rent = intval( get_post_meta( get_the_ID(), 'maximum_rent', true ) );

	// bail if there's no rent value over $50 (this is junk data).
	if ( max( $minimum_rent, $maximum_rent ) < 50 ) {
		return apply_filters( 'rentfetch_filter_floorplan_pricing', null, $minimum_rent, $maximum_rent );
	}

	$minimum_rent_value = $minimum_rent > 0 ? (float) $minimum_rent : null;
	$maximum_rent_value = $maximum_rent > 0 ? (float) $maximum_rent : null;
	if ( null === $minimum_rent_value ) {
		$minimum_rent_value = $maximum_rent_value;
	}
	if ( null === $maximum_rent_value ) {
		$maximum_rent_value = $minimum_rent_value;
	}
	if ( null !== $minimum_rent_value && null !== $maximum_rent_value && $maximum_rent_value < $minimum_rent_value ) {
		$temp               = $minimum_rent_value;
		$minimum_rent_value = $maximum_rent_value;
		$maximum_rent_value = $temp;
	}

	$price_display = get_option( 'rentfetch_options_floorplan_pricing_display', 'range' );
	$base_rent     = rentfetch_format_floorplan_rent_display( $minimum_rent_value, $maximum_rent_value, $price_display );

	if ( ! $base_rent ) {
		return apply_filters( 'rentfetch_filter_floorplan_pricing', null, $minimum_rent, $maximum_rent );
	}

	$total_monthly_price = rentfetch_format_floorplan_rent_display(
		get_post_meta( get_the_ID(), 'minimum_total_monthly_price', true ),
		get_post_meta( get_the_ID(), 'maximum_total_monthly_price', true ),
		$price_display
	);
	if ( $total_monthly_price ) {
		$tooltip_markup = function_exists( 'rentfetch_get_total_monthly_leasing_pricing_tooltip_markup' ) ? rentfetch_get_total_monthly_leasing_pricing_tooltip_markup() : '';
		$rent_range     = sprintf(
			'<span class="rentfetch-floorplan-rent-lines"><span class="rentfetch-floorplan-rent-with-fees"><span class="rentfetch-pricing-with-tooltip">%1$s/mo%3$s</span></span><span class="rentfetch-floorplan-base-rent">%2$s base rent</span></span>',
			esc_html( $total_monthly_price ),
			esc_html( $base_rent ),
			$tooltip_markup
		);
	} else {
		$monthly_required_fees = rentfetch_get_floorplan_property_monthly_required_fees_total( get_the_ID() );
	}
	if ( ! $total_monthly_price && $monthly_required_fees > 0 ) {
		$minimum_rent_with_fees = $minimum_rent_value + $monthly_required_fees;
		$maximum_rent_with_fees = $maximum_rent_value + $monthly_required_fees;
		$including_fees_rent    = rentfetch_format_floorplan_rent_display( $minimum_rent_with_fees, $maximum_rent_with_fees, $price_display );
		$tooltip_markup         = function_exists( 'rentfetch_get_total_monthly_leasing_pricing_tooltip_markup' ) ? rentfetch_get_total_monthly_leasing_pricing_tooltip_markup() : '';

		$rent_range = sprintf(
			'<span class="rentfetch-floorplan-rent-lines"><span class="rentfetch-floorplan-rent-with-fees"><span class="rentfetch-pricing-with-tooltip">%1$s/mo%3$s</span></span><span class="rentfetch-floorplan-base-rent">%2$s base rent</span></span>',
			esc_html( $including_fees_rent ),
			esc_html( $base_rent ),
			$tooltip_markup
		);
	} elseif ( ! $total_monthly_price ) {
		$rent_range = sprintf(
			'<span class="rentfetch-floorplan-rent-lines"><span class="rentfetch-floorplan-rent-with-fees">%1$s/mo</span></span>',
			esc_html( $base_rent )
		);
	}

	return apply_filters( 'rentfetch_filter_floorplan_pricing', $rent_range, $minimum_rent, $maximum_rent );
}

/**
 * Echo the pricing for the floorplan.
 *
 * @return void.
 */
function rentfetch_floorplan_pricing() {
	echo wp_kses_post( rentfetch_get_floorplan_pricing() );
}

// * Move in special

/**
 * Resolve the special that should be shown for a floor plan.
 *
 * Floor-plan values stay on the floor plan. Property values are read only when
 * the property explicitly allows the special to show on floor plans and units.
 *
 * @param int|null $floorplan_post_id Optional floor plan post ID.
 * @return array<string, mixed>|null
 */
function rentfetch_get_effective_floorplan_special( $floorplan_post_id = null ) {
	if ( ! $floorplan_post_id ) {
		$floorplan_post_id = get_the_ID();
	}

	$floorplan_post_id = (int) $floorplan_post_id;
	if ( $floorplan_post_id <= 0 ) {
		return null;
	}

	$floorplan_has_specials = get_post_meta( $floorplan_post_id, 'has_specials', true );
	$floorplan_heading      = sanitize_text_field( get_post_meta( $floorplan_post_id, 'specials_override_text', true ) );
	$floorplan_heading      = function_exists( 'mb_substr' ) ? mb_substr( $floorplan_heading, 0, 25 ) : substr( $floorplan_heading, 0, 25 );
	$floorplan_content      = sanitize_textarea_field( get_post_meta( $floorplan_post_id, 'specials_content', true ) );
	$has_floorplan_special  = in_array( $floorplan_has_specials, array( '1', 1, true ), true );

	if ( $has_floorplan_special ) {
		if ( ! rentfetch_property_specials_are_active_by_date( $floorplan_post_id ) ) {
			return null;
		}

		return array(
			'source'  => 'floorplan',
			'post_id' => $floorplan_post_id,
			'heading' => $floorplan_heading,
			'content' => $floorplan_content,
		);
	}

	$exclude_property_special = get_post_meta( $floorplan_post_id, 'specials_exclude_property', true );
	if ( in_array( $exclude_property_special, array( '1', 1, true ), true ) ) {
		return null;
	}

	$property_post_id      = rentfetch_get_connected_property_post_id_for_floorplan( $floorplan_post_id );
	$show_on_floorplans    = $property_post_id ? get_post_meta( $property_post_id, 'specials_show_on_floorplans', true ) : null;
	$property_has_specials = $property_post_id ? get_post_meta( $property_post_id, 'has_specials', true ) : null;

	if ( ! $property_post_id || ! in_array( $show_on_floorplans, array( '1', 1, true ), true ) || ! in_array( $property_has_specials, array( '1', 1, true ), true ) ) {
		return null;
	}

	if ( ! rentfetch_property_specials_are_active_by_date( $property_post_id ) ) {
		return null;
	}

	$property_heading = sanitize_text_field( get_post_meta( $property_post_id, 'specials_override_text', true ) );
	$property_heading = function_exists( 'mb_substr' ) ? mb_substr( $property_heading, 0, 25 ) : substr( $property_heading, 0, 25 );

	return array(
		'source'  => 'property',
		'post_id' => $property_post_id,
		'heading' => $property_heading,
		'content' => sanitize_textarea_field( get_post_meta( $property_post_id, 'specials_content', true ) ),
	);
}

/**
 * Get the short special title for a floor plan.
 *
 * @param int|null $floorplan_post_id Optional floor plan post ID.
 * @return string|null
 */
function rentfetch_get_floorplan_specials( $floorplan_post_id = null ) {
	$specials      = rentfetch_get_effective_floorplan_special( $floorplan_post_id );
	$specials_text = $specials ? $specials['heading'] : null;

	if ( $specials && ! $specials_text ) {
		$specials_text = 'Specials available';
	}

	return apply_filters( 'rentfetch_filter_floorplan_specials', $specials_text );
}

/**
 * Get the full special callout for a floor plan.
 *
 * @param int|null $floorplan_post_id Optional floor plan post ID.
 * @return string|null
 */
function rentfetch_get_floorplan_specials_callout( $floorplan_post_id = null ) {
	$specials = rentfetch_get_effective_floorplan_special( $floorplan_post_id );

	if ( ! $specials || ( ! $specials['heading'] && ! $specials['content'] ) ) {
		return null;
	}

	return rentfetch_get_specials_callout_markup( $specials['heading'], $specials['content'] );
}

/**
 * Echo the move-in special markup.
 *
 * @param string $specials_text The move-in special text.
 *
 * @return string|null.
 */
function rentfetch_floorplan_property_specials_label( $specials_text ) {

	if ( $specials_text ) {
		return $specials_text;
	}

	return null;
}
add_filter( 'rentfetch_filter_floorplan_specials', 'rentfetch_floorplan_property_specials_label', 10, 1 );

// * Tour

/**
 * Get the tour markup
 *
 * @return string the tour markup.
 */
function rentfetch_get_floorplan_tour() {
	$tours = rentfetch_get_floorplan_tours( get_the_ID() );
	if ( ! $tours ) {
		return apply_filters( 'rentfetch_filter_floorplan_tour', null );
	}
	$tour = $tours[0];

	wp_enqueue_style( 'rentfetch-glightbox-style' );
	wp_enqueue_script( 'rentfetch-glightbox-script' );
	wp_enqueue_script( 'rentfetch-glightbox-init' );

	$class          = 'tour-link';
	$lightbox       = '';
	$target         = ' target="_blank" rel="noopener noreferrer"';
	$provider_class = in_array( $tour['type'], array( 'youtube', 'matterport' ), true ) ? ' tour-link-' . $tour['type'] : '';
	if ( 'youtube' === $tour['type'] ) {
		$lightbox = ' data-glightbox="type: video;"';
		$target   = '';
	} elseif ( 'matterport' === $tour['type'] ) {
		$target = '';
	}

	$embedlink = sprintf( '<div class="tour-link-wrapper"><a class="%s%s"%s%s data-gallery="post-%s" href="%s"></a></div>', $class, $provider_class, $target, $lightbox, get_the_ID(), esc_url( $tour['link_url'] ) );

	return apply_filters( 'rentfetch_filter_floorplan_tour', $embedlink );
}

/**
 * Get the tour embed code
 *
 * @return string the tour embed code.
 */
function rentfetch_get_floorplan_tour_embed() {
	$tours = rentfetch_get_floorplan_tours( get_the_ID() );
	$embed = $tours ? rentfetch_get_tour_embed_html( $tours[0]['url'] ) : '';

	return apply_filters( 'rentfetch_filter_floorplan_tour_embed', $embed );
}

/**
 * Output the tour embed code
 *
 * @return void.
 */
function rentfetch_floorplan_tour_embed() {
	$tour = rentfetch_get_floorplan_tour_embed();

	$allowed_tags = array(
		'iframe' => array(
			'title'           => array(),
			'src'             => array(),
			'width'           => array(),
			'height'          => array(),
			'frameborder'     => array(),
			'allow'           => array(),
			'allowfullscreen' => array(),
		),
	);

	if ( $tour ) {
		echo wp_kses( $tour, $allowed_tags );
	}
}

// * Buttons.

/**
 * Echo the floorplan links
 *
 * @return string the floorplan links.
 */
function rentfetch_get_floorplan_links() {

	$units_count            = rentfetch_get_floorplan_units_count_from_cpt();
	$force_enable_fb_single = get_option( 'rentfetch_options_floorplan_force_single_template_link', 'disabled' );

	ob_start();

	if ( $units_count > 0 || 'enabled' === $force_enable_fb_single ) {

		// if there are units attached to this floorplan, then link to the permalink of the floorplan.
		$overlay = sprintf( '<a href="%s" class="overlay-link"></a>', get_the_permalink() );
		echo wp_kses_post( apply_filters( 'rentfetch_do_floorplan_overlay_link', $overlay ) );

	} else {

		// if there are no units attached to this floorplan, then do the buttons.
		echo '<div class="buttons-outer">';
			echo '<div class="buttons-inner">';
				do_action( 'rentfetch_do_floorplan_buttons' );
			echo '</div>';
		echo '</div>';
	}

	return ob_get_clean();
}

/**
 * Echo the floorplan links
 *
 * @return void.
 */
function rentfetch_floorplan_links() {
	echo wp_kses_post( rentfetch_get_floorplan_links() );
}

/**
 * Get the floorplan buttons
 *
 * @return string the floorplan buttons.
 */
function rentfetch_get_floorplan_buttons() {
	ob_start();
	do_action( 'rentfetch_do_floorplan_buttons' );
	return ob_get_clean();
}

/**
 * Echo the floorplan buttons
 *
 * @return void.
 */
function rentfetch_floorplan_buttons() {
	echo wp_kses_post( rentfetch_get_floorplan_buttons() );
}

/**
 * Get the availability button
 *
 * @return string|bool the availability button.
 */
function rentfetch_floorplan_default_availability_button() {

	$button_enabled        = (int) get_option( 'rentfetch_options_availability_button_enabled', false );
	$hide_if_no_availabily = (int) get_option( 'rentfetch_options_availability_button_enabled_hide_when_unavailable', false );

	// bail if the button is not enabled.
	if ( 1 !== $button_enabled ) {
		return false;
	}

	// if the button is set to hide when there's not availability, let's check for availability and do the needful.
	if ( $hide_if_no_availabily ) {
		$available_units   = get_post_meta( get_the_ID(), 'available_units', true );
		$availability_date = get_post_meta( get_the_ID(), 'availability_date', true );

		if ( $available_units < 1 && empty( $availability_date ) ) {
			return false;
		}
	}

	echo wp_kses_post( apply_filters( 'rentfetch_floorplan_default_availability_button_markup', null ) );
}
add_action( 'rentfetch_do_floorplan_buttons', 'rentfetch_floorplan_default_availability_button' );

/**
 * Set up the default markup for the availability button.
 *
 * @return string the availability button markup.
 */
function rentfetch_floorplan_default_availability_button_markup() {

	$button_label = get_option( 'rentfetch_options_availability_button_button_label', 'availability' );

	$link           = get_post_meta( get_the_ID(), 'availability_url', true );
	$target         = rentfetch_get_link_target( $link );
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_applynow_click', rentfetch_get_floorplan_tracking_context() );

	// bail if no link is set.
	if ( false === $link || empty( $link ) ) {
		return false;
	}

	return sprintf( '<a href="%s" target="%s" class="rentfetch-button rentfetch-floorplan-availability-button"%s>%s</a>', $link, $target, $tracking_attrs, $button_label );
}
add_filter( 'rentfetch_floorplan_default_availability_button_markup', 'rentfetch_floorplan_default_availability_button_markup' );


/**
 * Get the unavailability button
 *
 * @return string|bool the unavailability button.
 */
function rentfetch_floorplan_default_unavailability_button() {

	$button_enabled = get_option( 'rentfetch_options_unavailability_button_enabled', false );

	$button_enabled = (int) $button_enabled;

	// bail if the button is not enabled.
	if ( 1 !== $button_enabled ) {
		return false;
	}

	echo wp_kses_post( apply_filters( 'rentfetch_floorplan_default_unavailability_button_markup', null ) );
}
add_action( 'rentfetch_do_floorplan_buttons', 'rentfetch_floorplan_default_unavailability_button' );

/**
 * Set up the default markup for the availability button.
 *
 * @return string the availability button markup.
 */
function rentfetch_floorplan_default_unavailability_button_markup() {

	$units_count = rentfetch_get_floorplan_units_count_from_meta();
	if ( $units_count > 0 ) {
		return false;
	}

	$button_label = get_option( 'rentfetch_options_unavailability_button_button_label', 'availability' );

	$link           = get_option( 'rentfetch_options_unavailability_button_link' );
	$target         = rentfetch_get_link_target( $link );
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_unavailability_click', rentfetch_get_floorplan_tracking_context() );

	// bail if no link is set.
	if ( false === $link || empty( $link ) ) {
		return false;
	}

	return sprintf( '<a href="%s" target="%s" class="rentfetch-button rentfetch-floorplan-unavailability-button"%s>%s</a>', $link, $target, $tracking_attrs, $button_label );
}
add_filter( 'rentfetch_floorplan_default_unavailability_button_markup', 'rentfetch_floorplan_default_unavailability_button_markup' );

/**
 * Get the contact button
 *
 * @return string the contact button.
 */
function rentfetch_floorplan_default_contact_button() {

	$button_enabled = (int) get_option( 'rentfetch_options_contact_button_enabled', false );

	// bail if the button is not enabled.
	if ( 1 !== $button_enabled ) {
		return;
	}

	echo wp_kses_post( apply_filters( 'rentfetch_filter_floorplan_default_contact_button_markup', null ) );
}
add_action( 'rentfetch_do_floorplan_buttons', 'rentfetch_floorplan_default_contact_button' );

/**
 * The default markup for the contact button.
 *
 * @return string the contact button markup.
 */
function rentfetch_floorplan_default_contact_button_markup() {

	$button_label   = get_option( 'rentfetch_options_contact_button_button_label', 'Contact' );
	$link           = get_option( 'rentfetch_options_contact_button_link', false );
	$target         = rentfetch_get_link_target( $link );
	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_contact_click', rentfetch_get_floorplan_tracking_context() );

	return sprintf( '<a href="%s" target="%s" class="rentfetch-button rentfetch-floorplan-contact-button"%s>%s</a>', $link, $target, $tracking_attrs, $button_label );
}
add_filter( 'rentfetch_filter_floorplan_default_contact_button_markup', 'rentfetch_floorplan_default_contact_button_markup' );

/**
 * Echo the tour button
 *
 * @return void.
 */
function rentfetch_floorplan_default_tour_button() {

	$button_enabled = (int) get_option( 'rentfetch_options_tour_button_enabled' );
	$fallback_link  = get_option( 'rentfetch_options_tour_button_fallback_link' );
	$label          = get_option( 'rentfetch_options_tour_button_button_label', 'Tour' );
	$target         = rentfetch_get_link_target( $fallback_link );

	// bail if the button is not enabled.
	if ( 1 !== $button_enabled ) {
		return;
	}

	$tracking_attrs = rentfetch_get_tracking_data_attributes( 'rentfetch_scheduletour_click', rentfetch_get_floorplan_tracking_context() );
	$button         = sprintf( '<a href="%s" target="%s" class="rentfetch-button rentfetch-floorplan-tour-button"%s>%s</a>', $fallback_link, $target, $tracking_attrs, $label );

	echo wp_kses_post( apply_filters( 'rentfetch_floorplan_default_tour_button', $button ) );
}
add_action( 'rentfetch_do_floorplan_buttons', 'rentfetch_floorplan_default_tour_button' );

/**
 * From a link, figure out whether the target should be _blank or _self.
 *
 * @param string $link the link to check.
 *
 * @return string the target.
 */
function rentfetch_get_link_target( $link ) {
	$target = '_blank'; // Default target.
	$host   = wp_parse_url( $link, PHP_URL_HOST );

	// If the host is the same as the current site, then we'll use _self.
	if ( wp_parse_url( home_url(), PHP_URL_HOST ) === $host ) {
		$target = '_self';
	}

	return $target;
}

/**
 * Get an array of the columns that we should output for the unit table.
 *
 * @param   array $args  the args for the current unit query.
 *
 * @return  array an array of the columns to output.
 */
function rentfetch_floorplan_unit_display_get_columns( $args ) {
	$columns = array();

	// * Apartment number.
	// (This is just the title, so we're not going to bother with this one being optional).
	$columns[] = 'title';

	// * Pricing.
	// We need to get the 'minimum_rent' and 'maximum_rent' meta values and make sure they're not empty. If there are any values, then we add the pricing column.
	$args_pricing                 = $args;
	$args_pricing_meta            = array(
		'key'     => 'minimum_rent',
		'value'   => 0,
		'compare' => '>',
	);
	$args_pricing['meta_query'][] = $args_pricing_meta;

	// We need to get the 'maximum_rent' meta values and make sure they're not empty. If there are any values, then we add the pricing column.
	$args_pricing_max                 = $args;
	$args_pricing_max_meta            = array(
		'key'     => 'maximum_rent',
		'value'   => 0,
		'compare' => '>',
	);
	$args_pricing_max['meta_query'][] = $args_pricing_max_meta;

	$posts_pricing     = get_posts( $args_pricing );
	$posts_pricing_max = get_posts( $args_pricing_max );

	// If either $posts_pricing or $posts_pricing_max is an array with at least one item, then we'll add the pricing column.
	if ( ( is_array( $posts_pricing ) && count( $posts_pricing ) > 0 ) || ( is_array( $posts_pricing_max ) && count( $posts_pricing_max ) > 0 ) ) {
		$columns[] = 'pricing';
	}

	// * Deposit.
	// We need to add an array to args that looks for 'deposit' in the meta key and makes sure the value is non-zero and not empty/null.
	$args_deposit      = $args;
	$args_deposit_meta = array(
		'key'     => 'deposit',
		'value'   => 0,
		'compare' => '>',
	);

	$args_deposit['meta_query'][] = $args_deposit_meta;

	$posts_deposit = get_posts( $args_deposit );

	// if $posts_deposit is an array with at least one item, then we'll add the deposit column.
	if ( is_array( $posts_deposit ) && count( $posts_deposit ) > 0 ) {
		$columns[] = 'deposit';
	}

	// * Availability date.
	// We need to add an array to args that looks for 'availability_date' in the meta key and makes sure the value is non-empty.
	$args_availability      = $args;
	$args_availability_meta = array(
		'key'     => 'availability_date',
		'value'   => '',
		'compare' => '!=',
	);

	$args_availability['meta_query'][] = $args_availability_meta;

	$posts_availability = get_posts( $args_availability );

	// if $posts_availability is an array with at least one item, then we'll add the availability date column.
	if ( is_array( $posts_availability ) && count( $posts_availability ) > 0 ) {
		$columns[] = 'availability_date';
	}

	// * Square feet.
	// Add the square footage column only when at least one unit has a positive unit-level square footage value.
	$args_square_feet      = $args;
	$args_square_feet_meta = array(
		'key'     => 'sqrft',
		'value'   => 0,
		'compare' => '>',
		'type'    => 'NUMERIC',
	);

	$args_square_feet['meta_query'][] = $args_square_feet_meta;

	$posts_square_feet = get_posts( $args_square_feet );

	if ( is_array( $posts_square_feet ) && count( $posts_square_feet ) > 0 ) {
		$columns[] = 'sqrft';
	}

	// * Amenities.
	// We need to add an array to args that looks for 'amenities' in the meta key and makes sure the value is non-empty and not an empty array

	// This is a bit more complicated because we need to check if the value is an empty array.
	// We'll use a custom meta query for this.
	$args_amenities = $args;

	// Meta query for 'amenities' to ensure the value is not 0, empty, or null.
	$args_amenities_meta = array(
		'key'     => 'amenities',
		'compare' => 'EXISTS',
	);

	// Merge the 'amenities' meta query into the original query.
	$args_amenities['meta_query'][] = $args_amenities_meta;

	// Query posts with the updated arguments using WP_Query.
	$posts_amenities = new WP_Query( $args_amenities );

	// for each of the posts, get the amenities and add them to an array.
	$filtered_posts_amenities = array();

	if ( $posts_amenities->have_posts() ) {
		while ( $posts_amenities->have_posts() ) {
			$posts_amenities->the_post();
			$amenities = get_post_meta( get_the_ID(), 'amenities', true );

			// if $amenities[0] is not empty, add it to the $filtered_posts_amenities array.
			if ( ! empty( $amenities[0] ) ) {
				$filtered_posts_amenities[] = get_the_ID();
			}
		}
	}

	// If $filtered_posts_amenities is an array with at least one item, add the amenities column.
	if ( is_array( $filtered_posts_amenities ) && count( $filtered_posts_amenities ) > 0 ) {
		$columns[] = 'amenities';
	}

	// * Specials.
	// Specials are inherited through each unit's floor plan, so check the same displayed units used by both layouts.
	$posts_specials = get_posts( $args );

	foreach ( $posts_specials as $unit ) {
		if ( rentfetch_get_unit_specials( $unit->ID ) ) {
			$columns[] = 'specials';
			break;
		}
	}

	// * Building name.
	// We need to add an array to args that looks for 'building_name' in the meta key and makes sure the value is non-empty.
	$args_building_name      = $args;
	$args_building_name_meta = array(
		'key'     => 'building_name',
		'value'   => '',
		'compare' => '!=',
	);

	$args_building_name['meta_query'][] = $args_building_name_meta;
	$posts_building_name                = get_posts( $args_building_name );

	// if $posts_building_name is an array with at least one item, then we'll add the building name column.
	if ( is_array( $posts_building_name ) && count( $posts_building_name ) > 0 ) {
		$columns[] = 'building_name';
	}

	// * Floor number.
	// We need to add an array to args that looks for 'floor_number' in the meta key and makes sure the value is non-empty.
	$args_floor_number                 = $args;
	$args_floor_number_meta            = array(
		'key'     => 'floor_number',
		'value'   => '',
		'compare' => '!=',
	);
	$args_floor_number['meta_query'][] = $args_floor_number_meta;
	$posts_floor_number                = get_posts( $args_floor_number );

	// if $posts_floor_number is an array with at least one item, then we'll add the floor number column.
	if ( is_array( $posts_floor_number ) && count( $posts_floor_number ) > 0 ) {
		$columns[] = 'floor_number';
	}

	return apply_filters( 'rentfetch_floorplan_unit_display_columns', $columns, $args );
}

/**
 * Echo the unit table (this always must be in the context of a floorplan, which is why it's in this file).
 *
 * @return void.
 */
function rentfetch_floorplan_unit_table() {

	// get the current post.
	global $post;

	$floorplan_id = get_post_meta( get_the_ID(), 'floorplan_id', true );
	$property_id  = get_post_meta( get_the_ID(), 'property_id', true );

	$args = array(
		'post_type'      => 'units',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_key'       => 'availability_date', // phpcs:ignore
		'meta_query'     => array( // phpcs:ignore
			array(
				'key'   => 'property_id',
				'value' => $property_id,
			),
			array(
				'key'   => 'floorplan_id',
				'value' => $floorplan_id,
			),
		),
	);

	$args              = apply_filters( 'rentfetch_floorplan_unit_display_args', $args );
	$columns           = rentfetch_floorplan_unit_display_get_columns( $args );
	$square_feet_label = rentfetch_get_unit_square_feet_heading_label();

	// The Query.
	$units_table_query = new WP_Query( $args );

	// The Loop.
	if ( $units_table_query->have_posts() ) {

		echo '<table class="unit-details-table">';
			echo '<tr>';

		if ( in_array( 'building_name', $columns, true ) ) {
			echo '<th class="unit-buliding-name">Building</th>';
		}

		if ( in_array( 'title', $columns, true ) ) {
			echo '<th class="unit-title">Apt #</th>';
		}

		if ( in_array( 'floor_number', $columns, true ) ) {
			echo '<th class="building-floor-number">Floor</th>';
		}

		if ( in_array( 'sqrft', $columns, true ) ) {
			printf( '<th class="unit-sqrft">%s</th>', esc_html( $square_feet_label ) );
		}

		if ( in_array( 'pricing', $columns, true ) ) {
			echo '<th class="unit-starting-at">Starting At</th>';
		}

		if ( in_array( 'deposit', $columns, true ) ) {
			echo '<th class="unit-deposit">Deposit</th>';
		}

		if ( in_array( 'availability_date', $columns, true ) ) {
			echo '<th class="unit-availability">Date Available</th>';
		}

		if ( in_array( 'amenities', $columns, true ) ) {
			echo '<th class="unit-amenities">Amenities</th>';
		}

		if ( in_array( 'specials', $columns, true ) ) {
			echo '<th class="unit-specials">Specials</th>';
		}

				echo '<th class="unit-buttons"></th>';
			echo '</tr>';

		while ( $units_table_query->have_posts() ) {

			$units_table_query->the_post();

			$title             = rentfetch_get_unit_title();
			$building_name     = rentfetch_get_unit_building_name();
			$floor_number      = rentfetch_get_unit_floor_number();
			$square_feet       = rentfetch_get_unit_square_feet();
			$pricing           = rentfetch_get_unit_pricing();
			$deposit           = rentfetch_get_unit_deposit();
			$availability_date = rentfetch_get_unit_availability_date();
			$amenities         = rentfetch_get_unit_amenities();
			$specials          = rentfetch_get_unit_specials();

			echo '<tr>';

			if ( in_array( 'building_name', $columns, true ) ) {
				printf( '<td class="unit-building-name">%s</td>', esc_html( $building_name ) );
			}

			if ( in_array( 'title', $columns, true ) ) {
				printf( '<td class="unit-title">%s</td>', esc_html( $title ) );
			}

			if ( in_array( 'floor_number', $columns, true ) ) {
				printf( '<td class="unit-floor-number">%s</td>', esc_html( $floor_number ) );
			}

			if ( in_array( 'sqrft', $columns, true ) ) {
				printf( '<td class="unit-sqrft">%s</td>', $square_feet ? esc_html( $square_feet ) : '' );
			}

			if ( in_array( 'pricing', $columns, true ) ) {
				printf( '<td class="unit-starting-at">%s</td>', $pricing ? wp_kses_post( $pricing ) : '' );
			}

			if ( in_array( 'deposit', $columns, true ) ) {
				printf( '<td class="unit-deposit">%s</td>', esc_html( $deposit ) );
			}

			if ( in_array( 'availability_date', $columns, true ) ) {
				printf( '<td class="unit-availability">%s</td>', esc_html( $availability_date ) );
			}

			if ( in_array( 'amenities', $columns, true ) ) {
				printf( '<td class="unit-amenities">%s</td>', esc_html( $amenities ) );
			}

			if ( in_array( 'specials', $columns, true ) ) {
				printf( '<td class="unit-specials">%s</td>', wp_kses_post( $specials ) );
			}

				echo '<td class="unit-buttons">';
					do_action( 'rentfetch_do_unit_button' );
				echo '</td>';

				echo '</tr>';

		}

		echo '</table>';

	}
}
add_action( 'rentfetch_floorplan_do_unit_table', 'rentfetch_floorplan_unit_table' );

/**
 * Echo the unit list (this always must be in the context of a floorplan, which is why it's in this file).
 *
 * @return void.
 */
function rentfetch_floorplan_unit_list() {

	$floorplan_id = get_post_meta( get_the_ID(), 'floorplan_id', true );
	$property_id  = get_post_meta( get_the_ID(), 'property_id', true );

	$args = array(
		'post_type'      => 'units',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_key'       => 'availability_date', // phpcs:ignore
		'meta_query'     => array( // phpcs:ignore
			array(
				'key'   => 'property_id',
				'value' => $property_id,
			),
			array(
				'key'   => 'floorplan_id',
				'value' => $floorplan_id,
			),
		),
	);

	$args              = apply_filters( 'rentfetch_floorplan_unit_display_args', $args );
	$columns           = rentfetch_floorplan_unit_display_get_columns( $args );
	$square_feet_label = rentfetch_get_unit_square_feet_heading_label();

	// The Query.
	$units_list_query = new WP_Query( $args );

	// The Loop.
	if ( $units_list_query->have_posts() ) {

		echo '<div class="unit-details-list">';

		while ( $units_list_query->have_posts() ) {

			$units_list_query->the_post();

			$title             = rentfetch_get_unit_title();
			$building_name     = rentfetch_get_unit_building_name();
			$floor_number      = rentfetch_get_unit_floor_number();
			$square_feet       = rentfetch_get_unit_square_feet();
			$pricing           = rentfetch_get_unit_pricing();
			$deposit           = rentfetch_get_unit_deposit();
			$availability_date = rentfetch_get_unit_availability_date();
			$amenities         = rentfetch_get_unit_amenities();
			$specials          = rentfetch_get_unit_specials();

			echo '<details class="unit-details">';
				echo '<summary class="unit-summary">';
			if ( $pricing ) {
				printf( '<p class="unit-title">%s, <span class="label">starting at</span> %s<span class="dropdown"></span></p>', esc_html( $title ), wp_kses_post( $pricing ) );
			} else {
				printf( '<p class="unit-title">%s<span class="dropdown"></span></p>', esc_html( $title ) );
			}
				echo '</summary>';
				echo '<ul class="unit-details-list-wrap">';

			if ( $building_name ) {
				printf( '<li class="unit-building-name"><span class="label">Building:</span> %s</li>', esc_html( $building_name ) );
			}

			if ( $floor_number ) {
				printf( '<li class="unit-floor-number"><span class="label">Floor number:</span> %s</li>', esc_html( $floor_number ) );
			}

			if ( in_array( 'sqrft', $columns, true ) && $square_feet ) {
				printf( '<li class="unit-sqrft"><span class="label">%s:</span> %s</li>', esc_html( $square_feet_label ), esc_html( $square_feet ) );
			}

			if ( $deposit && 'Please inquire' !== $deposit ) {
				printf( '<li class="unit-deposit"><span class="label">Deposit:</span> %s</li>', esc_html( $deposit ) );
			}

			if ( $availability_date ) {
				printf( '<li class="unit-availability"><span class="label">Date Available:</span> %s</li>', esc_html( $availability_date ) );
			}

			if ( $amenities ) {
				printf( '<li class="unit-amenities"><span class="label">Amenities:</span> %s</li>', esc_html( $amenities ) );
			}

			if ( in_array( 'specials', $columns, true ) && $specials ) {
				printf( '<li class="unit-specials">Specials: %s</li>', esc_html( $specials ) );
			}

					echo '<li class="unit-buttons">';
						do_action( 'rentfetch_do_unit_button' );
					echo '</li>';

				echo '</ul>';
			echo '</details>';
		}

		echo '</div>';

	}

	wp_reset_postdata();
}
add_action( 'rentfetch_floorplan_do_unit_table', 'rentfetch_floorplan_unit_list' );

/**
 * Check if the number is over 100, return null if not.
 *
 * @param   int $number The number to check.
 *
 * @return  int|null The number if it's over 100, null if it's not.
 */
function rentfetch_check_if_above_100( $number ) {

	$number = (int) $number;

	if ( $number > 100 ) {
		return $number;
	}

	return null;
}

/**
 * Get the similar floorplans
 *
 * @return string the similar floorplans markup.
 */
function rentfetch_get_similar_floorplans() {

	ob_start();

	$property_id = get_post_meta( get_the_ID(), 'property_id', true );
	$beds        = get_post_meta( get_the_ID(), 'beds', true );

	$args = array(
		'post_type'      => 'floorplans',
		'posts_per_page' => -1,
		'post__not_in'   => array( get_the_ID() ),
		'meta_query'     => array( // phpcs:ignore
			'relation' => 'AND',
			array(
				'key'   => 'property_id',
				'value' => $property_id,
			),
			array(
				'key'   => 'beds',
				'value' => $beds,
			),
		),
	);

	// The Query.
	$similar_floorplans_query = new WP_Query( $args );

	// The Loop.
	if ( $similar_floorplans_query->have_posts() ) {

		echo '<div class="floorplans-loop">';

		while ( $similar_floorplans_query->have_posts() ) {

			$similar_floorplans_query->the_post();

			printf( '<div class="%s">', esc_attr( join( ' ', get_post_class() ) ) );

				do_action( 'rentfetch_floorplans_do_similar_each' );

			echo '</div>'; // .post_class
		}

		echo '</div>';
	}

	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * Output the similar floorplans.
 *
 * @return void.
 */
function rentfetch_similar_floorplans() {
	$floorplans = rentfetch_get_similar_floorplans();

	if ( $floorplans ) {
		echo wp_kses_post( $floorplans );
	}
}

/**
 * Get the description
 *
 * @return string the floorplan description
 */
function rentfetch_get_floorplan_description() {
	$description = apply_filters( 'the_content', get_post_meta( get_the_ID(), 'floorplan_description', true ) );

	return wp_kses_post( $description );
}

/**
 * Output the description
 */
function rentfetch_floorplan_description() {
	$description = rentfetch_get_floorplan_description();

	echo wp_kses_post( $description );
}

/**
 * Get the connected property's fees embed for a floor plan.
 *
 * @param int|null $post_id Floor plan post ID.
 * @return string|null Fees embed markup, or null when unavailable.
 */
function rentfetch_get_property_fee_embed_from_floorplan_id( $post_id = null ) {

	// the post_id is the *floorplan* post ID, not the property post ID.
	if ( ! $post_id ) {
		return;
	}

	$property_post_id = rentfetch_get_connected_property_post_id_for_floorplan( $post_id );

	// bail if we can't find the id.
	if ( ! $property_post_id ) {
		return;
	}

	$embed = rentfetch_get_property_fees_embed( $property_post_id );

	// if the embed is empty, return null.
	if ( ! $embed ) {
		return null;
	}

	// if the embed is not empty, return the embed.
	return $embed;
}
