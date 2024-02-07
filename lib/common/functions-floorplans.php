<?php
/**
 * This file has the Rent Fetch functions for getting floorplan data.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// * Title

/**
 * Get the floorplan title
 *
 * @return string the title of the floorplan.
 */
function rentfetch_get_floorplan_title() {
	$title = apply_filters( 'rentfetch_filter_floorplan_title', get_the_title() );
	return esc_html( $title );
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
	$beds_number = get_post_meta( get_the_ID(), 'beds', true );

	$beds_number = apply_filters( 'rentfetch_filter_floorplan_bedrooms', $beds_number );
	return wp_kses_post( apply_filters( 'rentfetch_get_bedroom_number_label', $beds_number ) );
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
	$baths_number = get_post_meta( get_the_ID(), 'baths', true );

	$baths_number = apply_filters( 'rentfetch_filter_floorplan_bathrooms', $baths_number );
	return wp_kses_post( apply_filters( 'rentfetch_get_bathroom_number_label', $baths_number ) );
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
	return wp_kses_post( apply_filters( 'rentfetch_get_square_feet_number_label', $square_feet ) );
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

	return wp_kses_post( apply_filters( 'rentfetch_get_available_units_label', $available_units ) );
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
 * Get the pricing for the floorplan
 *
 * @return string the pricing for the floorplan.
 */
function rentfetch_get_floorplan_pricing() {
	$minimum_rent = intval( get_post_meta( get_the_ID(), 'minimum_rent', true ) );
	$maximum_rent = intval( get_post_meta( get_the_ID(), 'maximum_rent', true ) );

	// bail if there's no rent value over $50 (this is junk data).
	if ( max( $minimum_rent, $maximum_rent ) < 50 ) {
		return null;
	}

	if ( $minimum_rent && $maximum_rent && $minimum_rent > 0 && $maximum_rent > 0 ) {
		if ( $minimum_rent === $maximum_rent ) {
			$rent_range = sprintf( '$%s', number_format( $minimum_rent ) );
		} elseif ( $minimum_rent < $maximum_rent ) {
			$rent_range = sprintf( '$%s-$%s', number_format( $minimum_rent ), number_format( $maximum_rent ) );
		} elseif ( $minimum_rent > $maximum_rent ) {
			$rent_range = sprintf( '$%s-$%s', number_format( $maximum_rent ), number_format( $minimum_rent ) );
		}
	} elseif ( $minimum_rent && ! $maximum_rent ) {
		$rent_range = sprintf( '$%s', number_format( $minimum_rent ) );
	} elseif ( ! $minimum_rent && $maximum_rent ) {
		$rent_range = sprintf( '$%s', number_format( $maximum_rent ) );
	} else {
		$rent_range = null;
	}

	return esc_html( apply_filters( 'rentfetch_filter_floorplan_pricing', $rent_range ) );
}

/**
 * Echo the pricing for the floorplan.
 *
 * @return void.
 */
function rentfetch_floorplan_pricing() {
	echo esc_html( rentfetch_get_floorplan_pricing() );
}

// * Move in special

/**
 * Get the move-in special markup.
 *
 * @return string the move-in special markup.
 */
function rentfetch_get_floorplan_specials() {

	$specials = get_post_meta( get_the_ID(), 'has_specials', true );

	return esc_html( apply_filters( 'rentfetch_filter_floorplan_specials', $specials ) );
}

/**
 * Echo the move-in special markup.
 *
 * @param string $specials The move-in special value (yes or no).
 *
 * @return string|null.
 */
function rentfetch_floorplan_property_specials_label( $specials ) {

	if ( $specials ) {
		return 'Specials available';
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

	$iframe    = get_post_meta( get_the_ID(), 'tour', true );
	$embedlink = null;

	if ( $iframe ) {

		wp_enqueue_style( 'rentfetch-glightbox-style' );
		wp_enqueue_script( 'rentfetch-glightbox-script' );
		wp_enqueue_script( 'rentfetch-glightbox-init' );

		// check against youtube.
		$youtube_pattern = '/src="https:\/\/www\.youtube\.com\/embed\/([^?"]+)\?/';
		preg_match( $youtube_pattern, $iframe, $youtube_matches );

		// if it's youtube and it's a full iframe.
		if ( isset( $youtube_matches[1] ) ) {
			$video_id   = $youtube_matches[1];
			$oembedlink = 'https://www.youtube.com/watch?v=' . $video_id;
			$embedlink  = sprintf( '<div class="tour-link-wrapper"><a class="tour-link tour-link-youtube" data-gallery="post-%s" data-glightbox="type: video;" href="%s"></a></div>', get_the_ID(), $oembedlink );
		}

		$matterport_pattern = '/src="([^"]*matterport[^"]*)"/i'; // Added "matterport" to the pattern.
		preg_match( $matterport_pattern, $iframe, $matterport_matches );

		// if it's matterport and it's a full iframe.
		if ( isset( $matterport_matches[1] ) ) {
			$oembedlink = $matterport_matches[1];
			$embedlink  = sprintf( '<div class="tour-link-wrapper"><a class="tour-link tour-link-matterport" data-gallery="post-%s" href="%s"></a></div>', get_the_ID(), $oembedlink );
		}

		// if it's anything else (like just an oembed, including an oembed for either matterport or youtube).
		if ( ! $embedlink ) {
			$oembedlink = $iframe;
			$embedlink  = sprintf( '<div class="tour-link-wrapper"><a class="tour-link" target="_blank" data-gallery="post-%s" href="%s"></a></div>', get_the_ID(), $oembedlink );
		}
	}

	return wp_kses_post( apply_filters( 'rentfetch_filter_floorplan_tour', $embedlink ) );
}

// * Buttons

/**
 * Echo the floorplan links
 *
 * @return string the floorplan links.
 */
function rentfetch_get_floorplan_links() {

	$units_count = rentfetch_get_floorplan_units_count_from_cpt();

	ob_start();

	if ( $units_count > 0 ) {

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

	return wp_kses_post( ob_get_clean() );
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
	return wp_kses_post( ob_get_clean() );
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

	$button_enabled = get_option( 'rentfetch_options_availability_button_enabled', false );

	$button_enabled = (int) $button_enabled;

	// bail if the button is not enabled.
	if ( 1 !== $button_enabled ) {
		return false;
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

	$link = get_post_meta( get_the_ID(), 'availability_url', true );

	// bail if no link is set.
	if ( false === $link ) {
		return false;
	}

	return wp_kses_post( sprintf( '<a href="%s" target="_blank" class="rentfetch-button">%s</a>', $link, $button_label ) );
}
add_filter( 'rentfetch_floorplan_default_availability_button_markup', 'rentfetch_floorplan_default_availability_button_markup' );

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

	$button_label = get_option( 'rentfetch_options_contact_button_button_label', 'Contact' );
	$external     = get_option( 'rentfetch_options_contact_button_link_target', false );
	$link         = get_option( 'rentfetch_options_contact_button_link', false );

	if ( true === $external ) {
		$target = 'target="_blank"';
	} else {
		$target = 'target="_self"';
	}

	return wp_kses_post( sprintf( '<a href="%s" %s class="rentfetch-button">%s</a>', $link, $target, $button_label ) );
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

	// bail if the button is not enabled.
	if ( 1 !== $button_enabled ) {
		return;
	}

	$button = sprintf( '<a href="%s" class="rentfetch-button">Tour</a>', $fallback_link );

	echo wp_kses_post( apply_filters( 'rentfetch_floorplan_default_tour_button', $button ) );
}
add_action( 'rentfetch_do_floorplan_buttons', 'rentfetch_floorplan_default_tour_button' );


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
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
		'meta_query'     => array(
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

	// The Query.
	$units_table_query = new WP_Query( $args );

	// The Loop.
	if ( $units_table_query->have_posts() ) {

		echo '<table class="unit-details-table">';
				echo '<tr>';
				echo '<th class="unit-title">Apt #</th>';
				echo '<th class="unit-starting-at">Starting At</th>';
				echo '<th class="unit-deposit">Deposit</th>';
				echo '<th class="unit-availability">Date Available</th>';
				echo '<th class="unit-tour-video"></th>';
				echo '<th class="unit-buttons"></th>';
			echo '</tr>';

		while ( $units_table_query->have_posts() ) {

			$units_table_query->the_post();

			$title             = rentfetch_get_unit_title();
			$pricing           = rentfetch_get_unit_pricing();
			$deposit           = rentfetch_get_unit_deposit();
			$availability_date = rentfetch_get_unit_availability_date();
			$floor             = null;
			$tour_video        = null;

			echo '<tr>';
				printf( '<td class="unit-title">%s</td>', esc_html( $title ) );
				printf( '<td class="unit-starting-at">%s</td>', esc_html( $pricing ) );
				printf( '<td class="unit-deposit">%s</td>', esc_html( $deposit ) );
				printf( '<td class="unit-availability">%s</td>', esc_html( $availability_date ) );
				printf( '<td class="unit-tour-video">%s</td>', wp_kses_post( $tour_video ) );
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
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
		'meta_query'     => array(
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

	// The Query.
	$units_list_query = new WP_Query( $args );

	// The Loop.
	if ( $units_list_query->have_posts() ) {

		echo '<div class="unit-details-list">';

		while ( $units_list_query->have_posts() ) {

			$units_list_query->the_post();

			$title             = rentfetch_get_unit_title();
			$pricing           = rentfetch_get_unit_pricing();
			$deposit           = rentfetch_get_unit_deposit();
			$availability_date = rentfetch_get_unit_availability_date();
			$floor             = null;
			$tour_video        = null;

			echo '<details class="unit-details">';
				echo '<summary class="unit-summary">';
					printf( '<p class="unit-title">%s, <span class="label">starting at</span> %s<span class="dropdown"></span></p>', esc_html( $title ), esc_html( $pricing ) );
				echo '</summary>';
				echo '<ul class="unit-details-list-wrap">';

			if ( $deposit ) {
				printf( '<li class="unit-deposit"><span class="label">Deposit:</span> %s</li>', esc_html( $deposit ) );
			}

			if ( $availability_date ) {
				printf( '<li class="unit-availability"><span class="label">Date Available:</span> %s</li>', esc_html( $availability_date ) );
			}

			if ( $tour_video ) {
				printf( '<li class="unit-tour-video">%s</li>', wp_kses_post( $tour_video ) );
			}

					echo '<li class="unit-buttons">';
						do_action( 'rentfetch_do_unit_button' );
					echo '</li>';

				echo '</ul>';
			echo '</details>';
		}

		echo '</div>';

	}
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
