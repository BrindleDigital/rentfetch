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
		echo esc_attr( get_post_meta( $post_id, 'availability_date', true ) );
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
