<?php
/**
 * This file sets up the default columns for the Units custom post type in the admin.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set up the admin columns order.
 *
 * @param array $columns an array of the columns we'd like to use.
 *
 * @return array $columns.
 */
function rentfetch_default_units_admin_columns( $columns ) {

	$columns = array(
		'cb'                    => '<input type="checkbox" />',
		'title'                 => __( 'Title', 'rentfetch' ),
		'unit_id'               => __( 'Unit ID', 'rentfetch' ),
		'building_name'         => __( 'Building Name', 'rentfetch' ),
		'floor_number'          => __( 'Floor Number', 'rentfetch' ),
		'floorplan_id'          => __( 'Floorplan ID', 'rentfetch' ),
		'floorplan_name'        => __( 'Floorplan Name', 'rentfetch' ),
		'property_id'           => __( 'Property ID', 'rentfetch' ),
		'apply_online_url'      => __( 'Apply URL', 'rentfetch' ),
		'availability_date'     => __( 'Availability date', 'rentfetch' ),
		'baths'                 => __( 'Baths', 'rentfetch' ),
		'beds'                  => __( 'Beds', 'rentfetch' ),
		'deposit'               => __( 'Deposit', 'rentfetch' ),
		'minimum_rent'          => __( 'Min Rent', 'rentfetch' ),
		'maximum_rent'          => __( 'Max Rent', 'rentfetch' ),
		'sqrft'                 => __( 'Sqrft', 'rentfetch' ),
		// 'yardi_unit_image_urls' => __( 'Synced Images', 'rentfetch' ),
		'amenities'             => __( 'Amenities', 'rentfetch' ),
		// 'specials'              => __( 'Specials', 'rentfetch' ),
		'unit_source'           => __( 'Integration', 'rentfetch' ),
		'api_response'          => __( 'API response', 'rentfetch' ),
	);

	return $columns;
}
add_filter( 'manage_units_posts_columns', 'rentfetch_default_units_admin_columns' );

/**
 * Make availability_date column sortable
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function rentfetch_units_sortable_columns( $columns ) {
	$columns['availability_date'] = 'availability_date';
	return $columns;
}
add_filter( 'manage_edit-units_sortable_columns', 'rentfetch_units_sortable_columns' );

/**
 * Handle sorting by availability_date
 *
 * @param WP_Query $query The query object.
 */
function rentfetch_units_orderby_availability_date( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( 'availability_date' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', 'availability_date' );
		$query->set( 'orderby', 'meta_value' );
		
		// For units, availability_date is stored as m/d/Y, so we need custom ordering
		add_filter( 'posts_orderby', 'rentfetch_units_orderby_availability_date_custom' );
	}
}
add_action( 'pre_get_posts', 'rentfetch_units_orderby_availability_date' );

/**
 * Custom orderby for availability_date to handle multiple date formats
 *
 * @param string $orderby The ORDER BY clause.
 * @return string
 */
function rentfetch_units_orderby_availability_date_custom( $orderby ) {
	global $wpdb;
	
	// Replace the standard meta_value ordering with date conversion that handles multiple formats
	$orderby = str_replace(
		"{$wpdb->postmeta}.meta_value",
		"COALESCE(
			STR_TO_DATE({$wpdb->postmeta}.meta_value, '%m/%d/%Y'),
			STR_TO_DATE({$wpdb->postmeta}.meta_value, '%Y-%m-%d %H:%i:%s'),
			STR_TO_DATE({$wpdb->postmeta}.meta_value, '%Y-%m-%d %H:%i'),
			STR_TO_DATE({$wpdb->postmeta}.meta_value, '%Y-%m-%d'),
			STR_TO_DATE({$wpdb->postmeta}.meta_value, '%Y%m%d'),
			STR_TO_DATE({$wpdb->postmeta}.meta_value, '%m-%d-%Y'),
			STR_TO_DATE({$wpdb->postmeta}.meta_value, '%d/%m/%Y')
		)",
		$orderby
	);
	
	return $orderby;
}

/**
 * Add filter dropdown for unit_source (Integration) in units admin
 *
 * @param string $post_type The post type.
 */
function rentfetch_units_filter_by_unit_source( $post_type ) {
	if ( 'units' !== $post_type ) {
		return;
	}

	global $wpdb;
	$unit_sources = $wpdb->get_col( $wpdb->prepare( "
		SELECT DISTINCT pm.meta_value
		FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = %s
		AND pm.meta_value != ''
		AND p.post_type = %s
		ORDER BY pm.meta_value ASC
	", 'unit_source', 'units' ) );

	$current = isset( $_GET['unit_source_filter'] ) ? $_GET['unit_source_filter'] : '';

	echo '<select name="unit_source_filter">';
	echo '<option value="">' . __( 'All Integrations', 'rentfetch' ) . '</option>';
	foreach ( $unit_sources as $source ) {
		$selected = ( $source === $current ) ? ' selected="selected"' : '';
		echo '<option value="' . esc_attr( $source ) . '"' . $selected . '>' . esc_html( $source ) . '</option>';
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'rentfetch_units_filter_by_unit_source' );

/**
 * Disable the default date filter for units
 *
 * @param bool   $disable Whether to disable the months dropdown.
 * @param string $post_type The post type.
 * @return bool
 */
function rentfetch_disable_months_dropdown_for_units( $disable, $post_type ) {
	if ( 'units' === $post_type ) {
		return true;
	}
	return $disable;
}
add_filter( 'disable_months_dropdown', 'rentfetch_disable_months_dropdown_for_units', 10, 2 );

/**
 * Modify the query to filter by unit_source
 *
 * @param WP_Query $query The query object.
 */
function rentfetch_units_filter_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'units' !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( isset( $_GET['unit_source_filter'] ) && ! empty( $_GET['unit_source_filter'] ) ) {
		$query->set( 'meta_query', array(
			array(
				'key'     => 'unit_source',
				'value'   => sanitize_text_field( $_GET['unit_source_filter'] ),
				'compare' => '=',
			),
		) );
	}
}
add_action( 'pre_get_posts', 'rentfetch_units_filter_query' );

/**
 * Set up the admin columns content.
 *
 * @param string $column  The name of the column.
 * @param int    $post_id The ID of the post.
 *
 * @return void
 */
function rentfetch_units_default_column_content( $column, $post_id ) {

	if ( 'title' === $column ) {
		echo esc_attr( get_the_title( $post_id ) );
	}
	
	if ( 'unit_source' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'unit_source', true ) );
		
		// let's also run the script for this post here, showing disabled fields.
		$array_disabled_fields = apply_filters( 'rentfetch_filter_unit_syncing_fields', array(), $post_id );
				
		// Output the inline script to add 'disabled' class
		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				var disabledFields = ' . json_encode( $array_disabled_fields ) . ';
				var postId = ' . json_encode( $post_id ) . ';
				
				disabledFields.forEach(function(field) {
					document.querySelectorAll("tr#post-" + postId + " td." + field).forEach(function(td) {
						td.classList.add("disabled");
					});
				});
			});
		</script>';
		
	}

	if ( 'unit_id' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'unit_id', true ) );
	}
	
	if ( 'building_name' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'building_name', true ) );
	
	}
	if ( 'floor_number' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'floor_number', true ) );
	}

	if ( 'floorplan_id' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'floorplan_id', true ) );
	}

	if ( 'floorplan_name' === $column ) {

		$property_id  = intval( get_post_meta( $post_id, 'property_id', true ) );
		$floorplan_id = intval( get_post_meta( $post_id, 'floorplan_id', true ) );

		// do a query for properties with this property_id.
		$args = array(
			'post_type'      => 'floorplans',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'floorplan_id',
					'value'   => $floorplan_id,
					'compare' => '=',
				),
			),
		);
		
		if ( 0 !== $property_id ) {
			$args['meta_query'][] = array(
				'key'     => 'property_id',
				'value'   => $property_id,
				'compare' => '=',
			);
		}

		$floorplan_name_query = new WP_Query( $args );

		if ( $floorplan_name_query->have_posts() ) {
			while ( $floorplan_name_query->have_posts() ) {
				$floorplan_name_query->the_post();
				$floorplan_title = get_the_title( get_the_ID() );
				$floorplan_link  = get_the_permalink( get_the_ID() );
				printf( '<p class="description"><a target="_blank" href="%s">%s</a> (<a target="_blank" href="/wp-admin/post.php?post=%s&action=edit">edit</a>)</p>', esc_url( $floorplan_link ), esc_html( $floorplan_title ), (int) get_the_ID() );
			}
		}
	}

	if ( 'property_id' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'property_id', true ) );
	}

	if ( 'apply_online_url' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'apply_online_url', true ) );
	}

	if ( 'availability_date' === $column ) {
		$date_string = get_post_meta( $post_id, 'availability_date', true );
		if ( $date_string ) {
			// Try different date formats that might exist in the data
			$formats = array( 'm/d/Y', 'Y-m-d', 'Ymd', 'm-d-Y', 'd/m/Y', 'Y-m-d H:i:s', 'Y-m-d H:i', 'm/d/Y H:i:s', 'm/d/Y H:i' );
			$date = false;
			
			foreach ( $formats as $format ) {
				$date = DateTime::createFromFormat( $format, $date_string );
				if ( $date !== false ) {
					break;
				}
			}
			
			if ( $date ) {
				echo esc_attr( $date->format( 'm/d/Y' ) );
			} else {
				// If no format works, show the raw value
				echo esc_attr( $date_string );
			}
		}
	}

	if ( 'baths' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'baths', true ) );
	}

	if ( 'beds' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'beds', true ) );
	}

	if ( 'deposit' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'deposit', true ) );
	}

	if ( 'minimum_rent' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'minimum_rent', true ) );
	}

	if ( 'maximum_rent' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'maximum_rent', true ) );
	}

	if ( 'sqrft' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'sqrft', true ) );
	}

	if ( 'specials' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'specials', true ) );
	}
	
	if ( 'amenities' === $column ) {
		echo esc_html( get_post_meta( $post_id, 'amenities', true ) );
	}

	if ( 'yardi_unit_image_urls' === $column ) {

		$yardi_unit_image_urls = get_post_meta( $post_id, 'yardi_unit_image_urls', true );
		$unit_image_urls       = array();

		// escape the array of image urls.
		if ( is_array( $yardi_unit_image_urls ) ) {
			foreach ( $yardi_unit_image_urls as $url ) {
				$unit_image_urls[] = esc_url( $url );
			}
		}

		foreach ( $unit_image_urls as $url ) {
			if ( $url ) {

				$url = explode( ',', $url );

				foreach ( $url as $imgurl ) {
					printf( '<div style="border: 1px solid gray; margin: 2px; background-image: url(\'%s\'); background-size: cover; background-position: center center; height: 35px; width: 35px; overflow: hidden; position: relative; display: inline-block;"></div>', esc_url( $imgurl ) );
				}
			}
		}
	}

	if ( 'updated' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'updated', true ) );
	}

	if ( 'api_error' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'api_error', true ) );
	}

	if ( 'api_response' === $column ) {
		$api_response = get_post_meta( $post_id, 'api_response', true );

		if ( ! is_array( $api_response ) ) {
			$api_response = array();
		}

		echo '<div class="api-responses">';

		foreach ( $api_response as $key => $value ) {

			echo '<div class="api-response">';

				printf( '<strong>%s:</strong><br/>', esc_attr( $key ) );

			foreach ( $value as $subkey => $subvalue ) {
				printf( '%s: %s<br/>', esc_attr( $subkey ), esc_attr( $subvalue ) );
			}

			echo '</div>';
		}

		echo '</div>';

	}
}
add_action( 'manage_units_posts_custom_column', 'rentfetch_units_default_column_content', 10, 2 );
