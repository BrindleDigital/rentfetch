<?php
/**
 * Property search ordering.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set the base order of the properties
 *
 * @param array $property_args Existing property query arguments.
 * @return array
 */
function rentfetch_search_property_order_options( $property_args ) {

	$orderby = get_option( 'rentfetch_options_property_orderby' );
	$order   = get_option( 'rentfetch_options_property_order' );

	$property_args['orderby'] = $orderby;
	$property_args['order']   = $order;

	return $property_args;
}
add_filter( 'rentfetch_search_property_map_properties_query_args', 'rentfetch_search_property_order_options' );

/**
 * Output property search sorting options.
 *
 * @return void
 */
function rentfetch_search_filters_sort_properties() {
	$sort    = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : '';
	$options = array(
		'default'      => 'Default',
		'availability' => 'Available Units',
		'pricelow'     => 'Price (low to high)',
		'pricehigh'    => 'Price (high to low)',
		'alphabetical' => 'Alphabetical',
	);

	echo '<fieldset class="sort">';
		echo '<legend>Sorting</legend>';
		echo '<button type="button" class="toggle">Sort</button>';
		echo '<div class="input-wrap radio checkboxes inactive">';
	foreach ( $options as $value => $label ) {
		printf(
			'<label><input type="radio" name="sort" id="sort-%1$s" value="%1$s" data-sort="%1$s" %2$s><span>%3$s</span></label>',
			esc_attr( $value ),
			checked( $sort, $value, false ),
			esc_html( $label )
		);
	}
		echo '</div>';
	echo '</fieldset>';
}

/**
 * Sort property posts by their filtered floorplan aggregates.
 *
 * @param array  $posts Property posts.
 * @param string $sort  Requested sort.
 * @return array
 */
function rentfetch_sort_property_posts( $posts, $sort ) {
	if ( ! in_array( $sort, array( 'availability', 'pricelow', 'pricehigh', 'alphabetical' ), true ) ) {
		return $posts;
	}

	usort(
		$posts,
		function ( $a, $b ) use ( $sort ) {
			if ( 'alphabetical' === $sort ) {
				return strcasecmp( $a->post_title, $b->post_title );
			}

			$a_floorplans = rentfetch_get_floorplans( get_post_meta( $a->ID, 'property_id', true ) );
			$b_floorplans = rentfetch_get_floorplans( get_post_meta( $b->ID, 'property_id', true ) );
			if ( 'availability' === $sort ) {
				$comparison = (int) ( $b_floorplans['availability'] ?? 0 ) <=> (int) ( $a_floorplans['availability'] ?? 0 );
			} else {
				$a_prices   = array_filter( array_map( 'floatval', rentfetch_get_available_property_floorplan_values( $a_floorplans, 'minimum_rent' ) ), 'rentfetch_check_if_above_100' );
				$b_prices   = array_filter( array_map( 'floatval', rentfetch_get_available_property_floorplan_values( $b_floorplans, 'minimum_rent' ) ), 'rentfetch_check_if_above_100' );
				$missing    = 'pricehigh' === $sort ? -PHP_FLOAT_MAX : PHP_FLOAT_MAX;
				$a_price    = $a_prices ? min( $a_prices ) : $missing;
				$b_price    = $b_prices ? min( $b_prices ) : $missing;
				$comparison = 'pricehigh' === $sort ? $b_price <=> $a_price : $a_price <=> $b_price;
			}

			return $comparison ?: strcasecmp( $a->post_title, $b->post_title );
		}
	);

	return $posts;
}
