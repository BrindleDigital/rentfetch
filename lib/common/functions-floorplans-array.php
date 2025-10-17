<?php
/**
 * This file retrieves an array of floorplans.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get the floorplans and return them as an array using custom SQL for efficiency.
 *
 * @param array $args Optional. Query arguments, same as WP_Query. Default empty array.
 * @return array an array of floorplans.
 */
function rentfetch_get_floorplans_array_sql( $args = array() ) {
	global $floorplans;
	global $wpdb;
	$floorplans = array();
	$meta_keys = array(
		'property_id', 'beds', 'baths', 'minimum_rent', 'maximum_rent',
		'minimum_sqft', 'maximum_sqft', 'available_units', 'availability_date', 'has_specials'
	);
	$meta_keys_sql = implode("','", $meta_keys);

	// Set up default args to match rentfetch_get_floorplans_array
	$default_args = array(
		'post_type'      => 'floorplans',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'post_status'    => 'publish',
	);
	$args = wp_parse_args( $args, $default_args );
	$args = apply_filters( 'rentfetch_search_floorplans_query_args', $args );

	// Pseudocache: use a transient keyed by the query args to avoid expensive SQL on
	// repeated calls. Expires after 5 minutes.
	$cache_key = 'rentfetch_floorplans_array_sql_' . md5( wp_json_encode( $args ) );
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		$cached = get_transient( $cache_key );
		if ( false !== $cached && is_array( $cached ) ) {
			// Populate the global and return cached value.
			$floorplans = $cached;
			return $floorplans;
		}
	}



	// Step 1: Get filtered, ordered, and limited list of floorplan IDs
	$where = [];
	$where[] = $wpdb->prepare( 'post_type = %s', $args['post_type'] );
	$where[] = $wpdb->prepare( 'post_status = %s', $args['post_status'] );
	if ( ! empty( $args['post__in'] ) && is_array( $args['post__in'] ) ) {
		$post__in = implode( ',', array_map( 'intval', $args['post__in'] ) );
		$where[] = "ID IN ($post__in)";
	}
	if ( ! empty( $args['post__not_in'] ) && is_array( $args['post__not_in'] ) ) {
		$post__not_in = implode( ',', array_map( 'intval', $args['post__not_in'] ) );
		$where[] = "ID NOT IN ($post__not_in)";
	}




	$meta_join_sql = '';
	if ( ! empty( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {
		$join_count = 0;
		$meta_sql = rentfetch_build_meta_query_sql($args['meta_query'], $wpdb, 'posts', $join_count);
		if ( $meta_sql['join'] ) {
			$meta_join_sql = implode( ' ', $meta_sql['join'] );
		}
		if ( $meta_sql['where'] ) {
			$where[] = $meta_sql['where'];
		}
	}

	$where_sql = 'WHERE ' . implode( ' AND ', $where );

	// Orderby
	$orderby = 'ID ASC';
	if ( isset( $args['orderby'] ) && 'date' === $args['orderby'] ) {
		$orderby = 'post_date ' . ( isset( $args['order'] ) ? $args['order'] : 'ASC' );
	}

	// Limit
	$limit = '';
	if ( isset( $args['posts_per_page'] ) && intval( $args['posts_per_page'] ) > 0 ) {
		$limit = 'LIMIT ' . intval( $args['posts_per_page'] );
	}

	// Query for IDs only
	$ids_sql = "
		SELECT ID
		FROM {$wpdb->posts}
		$meta_join_sql
		$where_sql
		ORDER BY $orderby
		$limit
	";
	$ids = $wpdb->get_col( $ids_sql );

	if ( empty( $ids ) ) {
		// Cache empty results briefly to avoid repeated queries returning no IDs.
		if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' && isset( $cache_key ) ) {
			set_transient( $cache_key, array(), 5 * MINUTE_IN_SECONDS );
		}
		return array();
	}

	// Step 2: Fetch all meta for those IDs
	$ids_in = implode( ',', array_map( 'intval', $ids ) );
	$meta_sql = "
		SELECT post_id as floorplan_id, meta_key, meta_value
		FROM {$wpdb->postmeta}
		WHERE post_id IN ($ids_in) AND meta_key IN ('$meta_keys_sql')
		ORDER BY post_id ASC
	";
	$results = $wpdb->get_results($meta_sql, ARRAY_A);

	// Group meta by floorplan, storing all meta as strings (not cast yet)
	$floorplan_meta = array();
	foreach ($results as $row) {
		$fid = $row['floorplan_id'];
		$key = $row['meta_key'];
		$val = (string) $row['meta_value']; // always string, like get_post_meta
		if (!isset($floorplan_meta[$fid])) {
			$floorplan_meta[$fid] = array();
		}
		$floorplan_meta[$fid][$key] = $val;
	}

	// Aggregate by property_id, storing all values as arrays of strings (like original)
	foreach ($floorplan_meta as $fid => $meta) {
		$property_id = isset($meta['property_id']) ? $meta['property_id'] : '';
		$beds = isset($meta['beds']) ? $meta['beds'] : '';
		$baths = isset($meta['baths']) ? $meta['baths'] : '';
		$minimum_rent = isset($meta['minimum_rent']) ? $meta['minimum_rent'] : '';
		$maximum_rent = isset($meta['maximum_rent']) ? $meta['maximum_rent'] : '';
		$minimum_sqft = isset($meta['minimum_sqft']) ? $meta['minimum_sqft'] : '';
		$maximum_sqft = isset($meta['maximum_sqft']) ? $meta['maximum_sqft'] : '';
		$available_units = isset($meta['available_units']) ? $meta['available_units'] : '';
		$availability_date = isset($meta['availability_date']) ? $meta['availability_date'] : '';
		$has_specials = isset($meta['has_specials']) ? $meta['has_specials'] : '';

		// Always store as arrays, even if empty string
		if (!isset($floorplans[$property_id])) {
			$floorplans[$property_id] = array(
				'id'                => array($fid),
				'beds'              => array($beds),
				'baths'             => array($baths),
				'minimum_rent'      => array($minimum_rent),
				'maximum_rent'      => array($maximum_rent),
				'minimum_sqft'      => array($minimum_sqft),
				'maximum_sqft'      => array($maximum_sqft),
				'available_units'   => array($available_units),
				'availability_date' => array($availability_date),
				'has_specials'      => array($has_specials),
			);
		} else {
			$floorplans[$property_id]['id'][]                = $fid;
			$floorplans[$property_id]['beds'][]              = $beds;
			$floorplans[$property_id]['baths'][]             = $baths;
			$floorplans[$property_id]['minimum_rent'][]      = $minimum_rent;
			$floorplans[$property_id]['maximum_rent'][]      = $maximum_rent;
			$floorplans[$property_id]['minimum_sqft'][]      = $minimum_sqft;
			$floorplans[$property_id]['maximum_sqft'][]      = $maximum_sqft;
			$floorplans[$property_id]['available_units'][]   = $available_units;
			$floorplans[$property_id]['availability_date'][] = $availability_date;
			$floorplans[$property_id]['has_specials'][]      = $has_specials;
		}
	}

	// Post-process to match the original function's output
	foreach ($floorplans as $key => $floorplan) {
		// * BEDS
		$beds_arr = array_map('floatval', $floorplan['beds']);
		$max = max($beds_arr);
		$min = min($beds_arr);
		$floorplans[$key]['bedsrange'] = ($max === $min) ? $max : ($min . '-' . $max);

		// * BATHS
		$baths_arr = array_map('floatval', $floorplan['baths']);
		$max = max($baths_arr);
		$min = min($baths_arr);
		$floorplans[$key]['bathsrange'] = ($max === $min) ? $max : ($min . '-' . $max);

		// * MAX RENT
		$max_rent_arr = array_filter(array_map('floatval', $floorplan['maximum_rent']), 'rentfetch_check_if_above_100');
		$min_rent_arr = array_filter(array_map('floatval', $floorplan['minimum_rent']), 'rentfetch_check_if_above_100');
		$max = !empty($max_rent_arr) ? max($max_rent_arr) : 0;
		$min = !empty($min_rent_arr) ? min($min_rent_arr) : 0;
		$max = (float)$max;
		$min = (float)$min;
		if ($max === $min) {
			$floorplans[$key]['rentrange'] = number_format($max);
		} else {
			$floorplans[$key]['rentrange'] = number_format($min) . '-' . number_format($max);
		}
		if ($min < 100 || $max < 100) {
			$floorplans[$key]['rentrange'] = null;
		}

		// * SQFT RANGE
		$max_sqft_arr = array_map('intval', $floorplan['maximum_sqft']);
		$min_sqft_arr = array_map('intval', $floorplan['minimum_sqft']);
		$max = intval(max($max_sqft_arr));
		$min = intval(min($min_sqft_arr));
		$floorplans[$key]['sqftrange'] = ($max === $min) ? number_format($max) : (number_format($min) . '-' . number_format($max));

		// * AVAILABLE UNITS
		$units_array = $floorplan['available_units'];
		if ($units_array && is_array($units_array)) {
			$numeric_units = array_filter(array_map('intval', $units_array), function($value) { return $value > 0; });
			$units = array_sum($numeric_units);
		} else {
			$units = 0;
		}
		$floorplans[$key]['availability'] = $units;

		// * AVAILABILITY DATE
		$availability_date_array = $floorplan['availability_date'];
		$floorplans[$key]['available_date'] = null;
		if ($availability_date_array) {
			foreach ($availability_date_array as $date_string) {
				if ('' === $date_string) continue;
				$date = DateTime::createFromFormat('Ymd', $date_string);
				if (false === $date) continue;
				if (null === $floorplans[$key]['available_date'] || $date < $floorplans[$key]['available_date']) {
					$floorplans[$key]['available_date'] = $date;
				}
			}
		}
		if (null !== $floorplans[$key]['available_date']) {
			$floorplans[$key]['available_date'] = $floorplans[$key]['available_date']->format('F j');
		}

		// * SPECIALS
		$floorplans[$key]['property_has_specials'] = false;
		$has_specials = $floorplan['has_specials']; // keep as string array
		if (in_array('1', $has_specials, true) || in_array(1, $has_specials, true) || in_array(true, $has_specials, true)) {
			$floorplans[$key]['property_has_specials'] = true;
		}
	}

	// Save computed floorplans to transient for 5 minutes to improve performance.
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' && isset( $cache_key ) ) {
		set_transient( $cache_key, $floorplans, 5 * MINUTE_IN_SECONDS );
	}

	return $floorplans;
}

/**
 * Get the floorplans and return them as an array.
 * Possibly deprecated, use rentfetch_get_floorplans_array_sql() instead.
 *
 * @return array an array of floorplans.
 */
function rentfetch_get_floorplans_array() {

	global $floorplans;

	$floorplans_args = array(
		'post_type'      => 'floorplans',
		'posts_per_page' => -1,
		'orderby'        => 'date', // we will sort posts by date.
		'order'          => 'ASC', // ASC or DESC.
		'no_found_rows'  => true,
		'post_status' => 'publish',
	);

	$floorplans_args = apply_filters( 'rentfetch_search_floorplans_query_args', $floorplans_args );

	$floorplans_query = new WP_Query( $floorplans_args );

	// reset the floorplans array.
	$floorplans = array();

	if ( $floorplans_query->have_posts() ) :

		while ( $floorplans_query->have_posts() ) :
			$floorplans_query->the_post();

			$id                = get_the_ID();
			$property_id       = get_post_meta( $id, 'property_id', true );
			$beds              = get_post_meta( $id, 'beds', true );
			$baths             = get_post_meta( $id, 'baths', true );
			$minimum_rent      = get_post_meta( $id, 'minimum_rent', true );
			$maximum_rent      = get_post_meta( $id, 'maximum_rent', true );
			$minimum_sqft      = get_post_meta( $id, 'minimum_sqft', true );
			$maximum_sqft      = get_post_meta( $id, 'maximum_sqft', true );
			$available_units   = get_post_meta( $id, 'available_units', true );
			$availability_date = get_post_meta( $id, 'availability_date', true );
			$has_specials      = get_post_meta( $id, 'has_specials', true );

			if ( ! isset( $floorplans[ $property_id ] ) ) {
				$floorplans[ $property_id ] = array(
					'id'                => array( $id ),
					'beds'              => array( $beds ),
					'baths'             => array( $baths ),
					'minimum_rent'      => array( $minimum_rent ),
					'maximum_rent'      => array( $maximum_rent ),
					'minimum_sqft'      => array( $minimum_sqft ),
					'maximum_sqft'      => array( $maximum_sqft ),
					'available_units'   => array( $available_units ),
					'availability_date' => array( $availability_date ),
					'has_specials'      => array( $has_specials ),
				);
			} else {
				$floorplans[ $property_id ]['id'][]                = $id;
				$floorplans[ $property_id ]['beds'][]              = $beds;
				$floorplans[ $property_id ]['baths'][]             = $baths;
				$floorplans[ $property_id ]['minimum_rent'][]      = $minimum_rent;
				$floorplans[ $property_id ]['maximum_rent'][]      = $maximum_rent;
				$floorplans[ $property_id ]['minimum_sqft'][]      = $minimum_sqft;
				$floorplans[ $property_id ]['maximum_sqft'][]      = $maximum_sqft;
				$floorplans[ $property_id ]['available_units'][]   = $available_units;
				$floorplans[ $property_id ]['availability_date'][] = $availability_date;
				$floorplans[ $property_id ]['has_specials'][]      = $has_specials;
			}

		endwhile;

		wp_reset_postdata();

	endif;

	foreach ( $floorplans as $key => $floorplan ) {

		// * BEDS

		$max = max( $floorplan['beds'] );
		$min = min( $floorplan['beds'] );

		if ( $max === $min ) {
			$floorplans[ $key ]['bedsrange'] = $max;
		} else {
			$floorplans[ $key ]['bedsrange'] = $min . '-' . $max;
		}

		// * BATHS.

		$max = max( $floorplan['baths'] );
		$min = min( $floorplan['baths'] );

		if ( $max === $min ) {
			$floorplans[ $key ]['bathsrange'] = $max;
		} else {
			$floorplans[ $key ]['bathsrange'] = $min . '-' . $max;
		}

		// * MAX RENT.

		$floorplan['maximum_rent'] = array_filter( $floorplan['maximum_rent'], 'rentfetch_check_if_above_100' );
		$floorplan['minimum_rent'] = array_filter( $floorplan['minimum_rent'], 'rentfetch_check_if_above_100' );

		if ( ! empty( $floorplan['maximum_rent'] ) ) {
			$max = max( $floorplan['maximum_rent'] );
		} else {
			$max = 0;
		}

		// * MIN RENT.

		if ( ! empty( $floorplan['minimum_rent'] ) ) {
			$min = min( $floorplan['minimum_rent'] );
		} else {
			$min = 0;
		}

		$max = (float) $max;
		$min = (float) $min;

		// * RENT RANGE.

		if ( $max === $min ) {
			$floorplans[ $key ]['rentrange'] = number_format( $max );
		} else {
			$floorplans[ $key ]['rentrange'] = number_format( $min ) . '-' . number_format( $max );
		}

		if ( $min < 100 || $max < 100 ) {
			$floorplans[ $key ]['rentrange'] = null;
		}

		// * SQFT RANGE.

		$max = intval( max( $floorplan['maximum_sqft'] ) );
		$min = intval( min( $floorplan['minimum_sqft'] ) );

		if ( $max === $min ) {
			$floorplans[ $key ]['sqftrange'] = number_format( $max );
		} else {
			$floorplans[ $key ]['sqftrange'] = number_format( $min ) . '-' . number_format( $max );
		}

		// * AVAILABLE UNITS.

		$units_array = $floorplan['available_units'];
		if ( $units_array && is_array( $units_array ) ) {
			// Filter out non-numeric values and convert to integers
			$numeric_units = array_filter( array_map( 'intval', $units_array ), function( $value ) {
				return $value > 0;
			});
			$units = array_sum( $numeric_units );
		} else {
			$units = 0;
		}

		$floorplans[ $key ]['availability'] = $units;

		// * AVAILABILITY DATE.

		$availability_date_array              = $floorplan['availability_date'];
		$floorplans[ $key ]['available_date'] = null;  // Initialize the available_date to null.

		if ( $availability_date_array ) {
			foreach ( $availability_date_array as $date_string ) {

				// Skip if date string is empty.
				if ( '' === $date_string ) {
					continue;
				}

				// Convert date string to DateTime object for comparison.
				$date = DateTime::createFromFormat( 'Ymd', $date_string );

				// Skip if date string is not a valid date.
				if ( false === $date ) {
					continue;
				}

				// If available_date is null or the current date is earlier, update available_date.
				if ( null === $floorplans[ $key ]['available_date'] || $date < $floorplans[ $key ]['available_date'] ) {
					$floorplans[ $key ]['available_date'] = $date;
				}
			}
		}

		// Convert the earliest date back to string format 'Ymd', if there's a valid date.
		if ( null !== $floorplans[ $key ]['available_date'] ) {
			$floorplans[ $key ]['available_date'] = $floorplans[ $key ]['available_date']->format( 'F j' );
		}

		// * SPECIALS

		// default value.
		$floorplans[ $key ]['property_has_specials'] = false;

		// if there are specials, save that.
		$has_specials = $floorplan['has_specials'];

		if ( in_array( true, $has_specials, true ) ) {
			$floorplans[ $key ]['property_has_specials'] = true;
		}
	}

	return $floorplans;
}

/**
 * Get the floorplans using the default function and make them available globally.
 *
 * @return void.
 */
function rentfetch_set_floorplans() {

	global $rentfetch_floorplans;
	$rentfetch_floorplans = rentfetch_get_floorplans_array_sql();
	
}
add_action( 'wp_loaded', 'rentfetch_set_floorplans' );

/**
 * Get the floorplans from the global variable, and return those for a particular property.
 *
 * @param int $property_id The property ID.
 *
 * @return array an array of floorplans.
 */
function rentfetch_get_floorplans( $property_id = null ) {

	global $rentfetch_floorplans;
	$property_id = esc_html( $property_id );

	if ( $property_id && isset( $rentfetch_floorplans[ $property_id ] ) ) {
		return $rentfetch_floorplans[ $property_id ];
	}

	return $rentfetch_floorplans;
}

/**
 * Get property IDs that have available floorplans, optimized for performance.
 * Returns an array of property IDs without full aggregation.
 *
 * @param array $args Optional. Query arguments, same as WP_Query. Default empty array.
 * @return array Array of property IDs.
 */
function rentfetch_get_property_ids_with_available_floorplans( $args = array() ) {
	global $wpdb;

	// Set up default args to match rentfetch_get_floorplans_array
	$default_args = array(
		'post_type'      => 'floorplans',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'post_status'    => 'publish',
	);
	$args = wp_parse_args( $args, $default_args );
	$args = apply_filters( 'rentfetch_search_floorplans_query_args', $args );

	// Pseudocache: use a transient keyed by the query args
	$cache_key = 'rentfetch_property_ids_available_' . md5( wp_json_encode( $args ) );
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		$cached = get_transient( $cache_key );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}
	}

	// Step 1: Get filtered floorplan IDs (same as in rentfetch_get_floorplans_array_sql)
	$where = [];
	$where[] = $wpdb->prepare( 'post_type = %s', $args['post_type'] );
	$where[] = $wpdb->prepare( 'post_status = %s', $args['post_status'] );
	if ( ! empty( $args['post__in'] ) && is_array( $args['post__in'] ) ) {
		$post__in = implode( ',', array_map( 'intval', $args['post__in'] ) );
		$where[] = "ID IN ($post__in)";
	}
	if ( ! empty( $args['post__not_in'] ) && is_array( $args['post__not_in'] ) ) {
		$post__not_in = implode( ',', array_map( 'intval', $args['post__not_in'] ) );
		$where[] = "ID NOT IN ($post__not_in)";
	}

	$meta_join_sql = '';
	if ( ! empty( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {
		$join_count = 0;
		$meta_sql = rentfetch_build_meta_query_sql($args['meta_query'], $wpdb, 'posts', $join_count);
		if ( $meta_sql['join'] ) {
			$meta_join_sql = implode( ' ', $meta_sql['join'] );
		}
		if ( $meta_sql['where'] ) {
			$where[] = $meta_sql['where'];
		}
	}

	$where_sql = 'WHERE ' . implode( ' AND ', $where );

	// Orderby
	$orderby = 'ID ASC';
	if ( isset( $args['orderby'] ) && 'date' === $args['orderby'] ) {
		$orderby = 'post_date ' . ( isset( $args['order'] ) ? $args['order'] : 'ASC' );
	}

	// Limit
	$limit = '';
	if ( isset( $args['posts_per_page'] ) && intval( $args['posts_per_page'] ) > 0 ) {
		$limit = 'LIMIT ' . intval( $args['posts_per_page'] );
	}

	// Query for IDs only
	$ids_sql = "
		SELECT ID
		FROM {$wpdb->posts}
		$meta_join_sql
		$where_sql
		ORDER BY $orderby
		$limit
	";
	$ids = $wpdb->get_col( $ids_sql );

	if ( empty( $ids ) ) {
		// Cache empty results briefly
		if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' && isset( $cache_key ) ) {
			set_transient( $cache_key, array(), 5 * MINUTE_IN_SECONDS );
		}
		return array();
	}

	// Step 2: Get distinct property_ids from those floorplan IDs
	$ids_in = implode( ',', array_map( 'intval', $ids ) );
	$property_ids_sql = "
		SELECT DISTINCT meta_value as property_id
		FROM {$wpdb->postmeta}
		WHERE post_id IN ($ids_in) AND meta_key = 'property_id' AND meta_value != ''
	";
	$property_ids = $wpdb->get_col( $property_ids_sql );

	// Cache the results
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		set_transient( $cache_key, $property_ids, 5 * MINUTE_IN_SECONDS );
	}

	return $property_ids;
}
