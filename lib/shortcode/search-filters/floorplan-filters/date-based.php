<?php
/**
 * Date-based filter
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if a date range has any availability
 *
 * @param string $start Start date in Ymd format
 * @param string $end End date in Ymd format
 * @return bool True if availability exists
 */
function rentfetch_date_option_has_availability( $start, $end ) {
	$transient_key = 'rentfetch_date_availability_' . md5( $start . '_' . $end );
	$has_availability = false;
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		$has_availability = get_transient( $transient_key );
		if ( false !== $has_availability ) {
			return (bool) $has_availability;
		}
	}

	global $wpdb;
	$start_date = date( 'Y-m-d', strtotime( $start ) );
	$end_date = date( 'Y-m-d', strtotime( $end ) );

	// Check floorplans
	$floorplan_count = $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'floorplans' AND ID IN (
			SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'availability_date' AND CAST(meta_value AS UNSIGNED) BETWEEN %d AND %d
		)",
		$start,
		$end
	) );

	// Check units
	$unit_count = $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->postmeta} pm1 
		JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id 
		WHERE pm1.meta_key = 'availability_date' AND DATE(STR_TO_DATE(pm1.meta_value, '%m/%d/%Y')) BETWEEN DATE(%s) AND DATE(%s) 
		AND pm2.meta_key = 'floorplan_id'",
		$start_date,
		$end_date
	) );

	$has_availability = ( $floorplan_count > 0 || $unit_count > 0 );

	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		set_transient( $transient_key, $has_availability, 5 * MINUTE_IN_SECONDS );
	}

	return $has_availability;
}

/**
 * Output the form markup for the availability date
 *
 * @return void.
 */
function rentfetch_search_filters_date() {

	// get the parameters.
	if ( isset( $_GET['search-dates'] ) ) {
		$active_parameters = array_map( 'sanitize_text_field', wp_unslash( $_GET['search-dates'] ) );
	} else {
		$active_parameters = array();
	}

	$current_year = date( 'Y' );
	$fall_start = $current_year . '-06-30';
	$fall_end = $current_year . '-10-01';
	$spring_start = $current_year . '-03-01';
	$spring_end = $current_year . '-05-31';
	$today = date( 'Y-m-d' );

	if ( $today >= $fall_start && $today <= $fall_end ) {
		$fall_year = $current_year;
	} else {
		$fall_year = $current_year + 1;
	}

	if ( $today >= $spring_start && $today <= $spring_end ) {
		$spring_year = $current_year;
	} else {
		$spring_year = $current_year + 1;
	}

	$all_options = array(
		'now-30' => array(
			'label' => 'Next 30 days',
			'start' => date( 'Ymd', strtotime( '-1 year' ) ),
			'end' => date( 'Ymd', strtotime( '+30 days' ) ),
		),
		'30-60' => array(
			'label' => '30-60 days',
			'start' => date( 'Ymd', strtotime( '+30 days' ) ),
			'end' => date( 'Ymd', strtotime( '+60 days' ) ),
		),
		'60-90' => array(
			'label' => '60-90 days',
			'start' => date( 'Ymd', strtotime( '+60 days' ) ),
			'end' => date( 'Ymd', strtotime( '+90 days' ) ),
		),
		'fall-' . $fall_year => array(
			'label' => 'Fall ' . $fall_year . ' Semester',
			'start' => $fall_year . '0630',
			'end' => $fall_year . '1001',
		),
		'spring-' . $spring_year => array(
			'label' => 'Spring ' . $spring_year . ' Semester',
			'start' => $spring_year . '0301',
			'end' => $spring_year . '0531',
		),
	);

	// Filter options to only show those with availability
	$options = array();
	foreach ( $all_options as $key => $option ) {
		if ( rentfetch_date_option_has_availability( $option['start'], $option['end'] ) ) {
			$options[ $key ] = $option['label'];
		}
	}

	$label = apply_filters( 'rentfetch_search_filters_date_label', 'Move-In' );

	// build the date-based search.
	echo '<fieldset class="move-in">';
		printf( '<legend>%s</legend>', esc_html( $label ) );
		printf( '<button type="button" class="toggle">%s</button>', esc_html( $label ) );
		echo '<div class="input-wrap checkboxes inactive">';

	foreach ( $options as $value => $label_text ) {

		// Check if the value is in the GET parameter array.
		$checked = in_array( $value, $active_parameters, true );

		// Generate CSS class based on value
		if ( strpos( $value, 'fall-' ) === 0 ) {
			$css_class = 'date-fall';
		} elseif ( strpos( $value, 'spring-' ) === 0 ) {
			$css_class = 'date-spring';
		} else {
			$css_class = 'date-' . $value;
		}

		printf(
			'<label class="%s">
				<input type="checkbox" 
					name="search-dates[]"
					value="%s" 
					%s />
				<span>%s</span>
			</label>',
			esc_attr( $css_class ),
			esc_attr( $value ),
			$checked ? 'checked' : '', // Apply checked attribute.
			esc_html( $label_text )
		);
	}

		echo '</div>'; // .checkboxes.
	echo '</fieldset>';
}

/**
 * Add the date-based filter to the search filters
 *
 * @param array $floorplans_args The floorplan arguments.
 *
 * @return array.
 */
function rentfetch_search_floorplans_args_date( $floorplans_args ) {

	if ( ! isset( $_POST['search-dates'] ) || ! is_array( $_POST['search-dates'] ) ) {
		return $floorplans_args;
	}

	$selected = array_map( 'sanitize_text_field', wp_unslash( $_POST['search-dates'] ) );

	if ( empty( $selected ) ) {
		return $floorplans_args;
	}

	$nonce = isset( $_POST['rentfetch_frontend_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_frontend_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_frontend_nonce_action' ) ) {
		die( 'Nonce verification failed (date-based search)' );
	}

	$current_year = date( 'Y' );
	$ranges = array();

	foreach ( $selected as $sel ) {
		if ( $sel === 'now-30' ) {
			$start = date( 'Ymd', strtotime( '-1 year' ) );
			$end   = date( 'Ymd', strtotime( '+30 days' ) );
		} elseif ( $sel === '30-60' ) {
			$start = date( 'Ymd', strtotime( '+30 days' ) );
			$end   = date( 'Ymd', strtotime( '+60 days' ) );
		} elseif ( $sel === '60-90' ) {
			$start = date( 'Ymd', strtotime( '+60 days' ) );
			$end   = date( 'Ymd', strtotime( '+90 days' ) );
		} elseif ( strpos( $sel, 'fall-' ) === 0 ) {
			$year  = str_replace( 'fall-', '', $sel );
			$start = $year . '0630';
			$end   = $year . '1001';
		} elseif ( strpos( $sel, 'spring-' ) === 0 ) {
			$year  = str_replace( 'spring-', '', $sel );
			$start = $year . '0301';
			$end   = $year . '0531';
		} else {
			continue; // Invalid selection
		}
		$ranges[] = array( 'start' => $start, 'end' => $end );
	}

	if ( empty( $ranges ) ) {
		return $floorplans_args;
	}

	$transient_key = 'rentfetch_date_search_' . md5( serialize( $selected ) );
	$floorplan_ids = false;
	if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
		$floorplan_ids = get_transient( $transient_key );
	}

	if ( false === $floorplan_ids ) {
		$floorplan_ids = array();
		global $wpdb;

		foreach ( $ranges as $range ) {
			$start = $range['start'];
			$end   = $range['end'];
			$start_date = date( 'Y-m-d', strtotime( $start ) );
			$end_date   = date( 'Y-m-d', strtotime( $end ) );

			// Get floorplans directly (availability_date stored as Ymd numeric)
			$floorplan_query = $wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'floorplans' AND ID IN (
					SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'availability_date' AND CAST(meta_value AS UNSIGNED) BETWEEN %d AND %d
				)",
				$start,
				$end
			);
			$floorplan_ids_direct = $wpdb->get_col( $floorplan_query );
			$floorplan_ids         = array_merge( $floorplan_ids, $floorplan_ids_direct );

			// Get from units (availability_date stored as m/d/Y)
			$unit_query = $wpdb->prepare(
				"SELECT DISTINCT pm2.meta_value FROM {$wpdb->postmeta} pm1 
				JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id 
				WHERE pm1.meta_key = 'availability_date' AND DATE(STR_TO_DATE(pm1.meta_value, '%m/%d/%Y')) BETWEEN DATE(%s) AND DATE(%s) 
				AND pm2.meta_key = 'floorplan_id'",
				$start_date,
				$end_date
			);
			$floorplan_ids_from_units = $wpdb->get_col( $unit_query );
			$floorplan_ids            = array_merge( $floorplan_ids, $floorplan_ids_from_units );
		}

		$floorplan_ids = array_unique( array_map( 'intval', $floorplan_ids ) );
		if ( get_option( 'rentfetch_options_disable_query_caching' ) !== '1' ) {
			set_transient( $transient_key, $floorplan_ids, 5 * MINUTE_IN_SECONDS ); // Cache for 5 minutes
		}
	}

	if ( ! empty( $floorplan_ids ) ) {
		$floorplans_args['post__in'] = $floorplan_ids;
	} else {
		// If no floorplans found, set post__in to [0] to ensure zero results
		$floorplans_args['post__in'] = array( 0 );
	}

	return $floorplans_args;
}
add_filter( 'rentfetch_search_floorplans_query_args', 'rentfetch_search_floorplans_args_date', 10, 1 );
