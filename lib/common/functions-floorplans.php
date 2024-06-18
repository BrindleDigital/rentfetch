<?php
/**
 * This file has the Rent Fetch functions for getting floorplan data.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

//* Post classes (in all archive contexts)

function rentfetch_floorplans_post_classes( $classes ) {

	$units_count = rentfetch_get_floorplan_units_count_from_meta();
	if ( $units_count > 0 ) {
		$classes[] = 'has-units-available';
	} else {
		$classes[] = 'no-units-available';
		
		$fade_out_unavailable = get_option( 'rentfetch_options_floorplan_apply_styles_no_floorplans' );
		if ( $fade_out_unavailable === '1' ) {
			$classes[] = 'no-units-unavailable-faded';
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

	$price_display = get_option( 'rentfetch_options_floorplan_pricing_display', 'range' );

	if ( 'range' === $price_display ) {

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
	} elseif ( 'minimum' === $price_display ) {

		if ( $minimum_rent && $maximum_rent && $minimum_rent > 0 && $maximum_rent > 0 ) {
			if ( $minimum_rent === $maximum_rent ) {
				$rent_range = sprintf( 'From $%s', number_format( $minimum_rent ) );
			} elseif ( $minimum_rent < $maximum_rent ) {
				$rent_range = sprintf( 'From $%s', number_format( $minimum_rent ) );
			} elseif ( $minimum_rent > $maximum_rent ) {
				$rent_range = sprintf( 'From $%s', number_format( $maximum_rent ) );
			}
		} elseif ( $minimum_rent && ! $maximum_rent ) {
			$rent_range = sprintf( 'From $%s', number_format( $minimum_rent ) );
		} elseif ( ! $minimum_rent && $maximum_rent ) {
			$rent_range = sprintf( 'From $%s', number_format( $maximum_rent ) );
		} else {
			$rent_range = null;
		}
	}

	$rent_range = apply_filters( 'rentfetch_filter_floorplan_pricing', $rent_range, $minimum_rent, $maximum_rent );

	return $rent_range;
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

	return apply_filters( 'rentfetch_filter_floorplan_specials', $specials );
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

	return apply_filters( 'rentfetch_filter_floorplan_tour', $embedlink );
}

/**
 * Get the tour embed code
 *
 * @return string the tour embed code.
 */
function rentfetch_get_floorplan_tour_embed() {

	global $post;

	$iframe    = get_post_meta( get_the_ID(), 'tour', true );
	$embedlink = null;

	// check against youtube.
	$youtube_pattern = '/src="https:\/\/www\.youtube\.com\/embed\/([^?"]+)\?/';
	preg_match( $youtube_pattern, $iframe, $youtube_matches );

	// if it's youtube and it's a full iframe.
	if ( isset( $youtube_matches[1] ) ) {
		$video_id   = $youtube_matches[1];
		$oembedlink = 'https://www.youtube.com/watch?v=' . $video_id;
		$embedlink  = wp_oembed_get( $oembedlink );
	}

	$matterport_pattern = '/src="([^"]*matterport[^"]*)"/i'; // Added "matterport" to the pattern.
	preg_match( $matterport_pattern, $iframe, $matterport_matches );

	// if it's matterport and it's a full iframe.
	if ( isset( $matterport_matches[1] ) ) {
		$oembedlink = $matterport_matches[1];
		$embedlink  = wp_oembed_get( $oembedlink );
	}

	// if it's anything else (like just an oembed, including an oembed for either matterport or youtube).
	if ( ! $embedlink ) {
		$embedlink = wp_oembed_get( $iframe );
	}

	return apply_filters( 'rentfetch_filter_floorplan_tour_embed', $embedlink );
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

	$link   = get_post_meta( get_the_ID(), 'availability_url', true );
	$target = rentfetch_get_link_target( $link );

	// bail if no link is set.
	if ( false === $link || empty( $link ) ) {
		return false;
	}

	return sprintf( '<a href="%s" target="%s" class="rentfetch-button rentfetch-floorplan-availability-button">%s</a>', $link, $target, $button_label );
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

	$button_label = get_option( 'rentfetch_options_unavailability_button_button_label', 'availability' );

	$link   = get_option( 'rentfetch_options_unavailability_button_link' );
	$target = rentfetch_get_link_target( $link );

	// bail if no link is set.
	if ( false === $link || empty( $link ) ) {
		return false;
	}

	return sprintf( '<a href="%s" target="%s" class="rentfetch-button rentfetch-floorplan-unavailability-button">%s</a>', $link, $target, $button_label );
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

	$button_label = get_option( 'rentfetch_options_contact_button_button_label', 'Contact' );
	$link         = get_option( 'rentfetch_options_contact_button_link', false );
	$target       = rentfetch_get_link_target( $link );

	return sprintf( '<a href="%s" target="%s" class="rentfetch-button rentfetch-floorplan-contact-button">%s</a>', $link, $target, $button_label );
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

	$button = sprintf( '<a href="%s" target="%s" class="rentfetch-button rentfetch-floorplan-tour-button">%s</a>', $fallback_link, $target, $label );

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
	// (This is the whole point of the display, so we're not going to bother with this one being optional).
	$columns[] = 'pricing';

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
	// We need to add an array to args that looks for 'specials' in the meta key and makes sure the value is non-empty.
	$args_specials      = $args;
	$args_specials_meta = array(
		'key'     => 'specials',
		'value'   => '',
		'compare' => '!=',
	);

	$args_specials['meta_query'][] = $args_specials_meta;

	$posts_specials = get_posts( $args_specials );

	// if $posts_specials is an array with at least one item, then we'll add the specials column.
	if ( is_array( $posts_specials ) && count( $posts_specials ) > 0 ) {
		$columns[] = 'specials';
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

	$args    = apply_filters( 'rentfetch_floorplan_unit_display_args', $args );
	$columns = rentfetch_floorplan_unit_display_get_columns( $args );

	// The Query.
	$units_table_query = new WP_Query( $args );

	// The Loop.
	if ( $units_table_query->have_posts() ) {

		echo '<table class="unit-details-table">';
			echo '<tr>';

				if ( in_array( 'title', $columns, true ) ) {
					echo '<th class="unit-title">Apt #</th>';
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
				$pricing           = rentfetch_get_unit_pricing();
				$deposit           = rentfetch_get_unit_deposit();
				$availability_date = rentfetch_get_unit_availability_date();
				$amenities         = rentfetch_get_unit_amenities();
				$floor             = null;
				$specials          = rentfetch_get_unit_specials();

				echo '<tr>';

					if ( in_array( 'title', $columns, true ) ) {
						printf( '<td class="unit-title">%s</td>', esc_html( $title ) );
					}

					if ( in_array( 'pricing', $columns, true ) ) {
						printf( '<td class="unit-starting-at">%s</td>', esc_html( $pricing ) );
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

	$args = apply_filters( 'rentfetch_floorplan_unit_display_args', $args );

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
			$amenities         = rentfetch_get_unit_amenities();
			$floor             = null;
			$specials          = rentfetch_get_unit_specials();

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

					if ( $amenities ) {
						printf( '<li class="unit-amenities"><span class="label">Amenities:</span> %s</li>', esc_html( $amenities ) );
					}

					if ( $specials ) {
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

	// TODO need to remove the current floorplan from this query.

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
	$description = get_post_meta( get_the_ID(), 'floorplan_description', true );

	return wp_kses_post( $description );
}

/**
 * Output the description
 */
function rentfetch_floorplan_description() {
	$description = rentfetch_get_floorplan_description();

	echo wp_kses_post( $description );
}