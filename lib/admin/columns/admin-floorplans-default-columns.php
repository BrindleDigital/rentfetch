<?php
/**
 * This file sets up the default columns for the Floorplans custom post type in the admin.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set the admin columns order.
 *
 * @param array $columns an array of the columns we'd like to use.
 *
 * @return array $columns.
 */
function rentfetch_default_floorplans_admin_columns( $columns ) {

	$columns = array(
		'cb'                     => '<input type="checkbox" />',
		'title'                  => __( 'Title', 'rentfetch' ),
		'floorplan_source'       => __( 'Floorplan Source', 'rentfetch' ),
		'property_id'            => __( 'Property ID', 'rentfetch' ),
		'property_name'          => __( 'Property Name', 'rentfetch' ),
		'floorplan_id'           => __( 'Floorplan ID', 'rentfetch' ),
		'tour'                   => __( 'Tour', 'rentfetch' ),
		'manual_images'          => __( 'Manual Images', 'rentfetch' ),
		'floorplan_images'       => __( 'Synced Images', 'rentfetch' ),
		'floorplan_description'  => __( 'Floorplan Description', 'rentfetch' ),
		'beds'                   => __( 'Beds', 'rentfetch' ),
		'baths'                  => __( 'Baths', 'rentfetch' ),
		'minimum_deposit'        => __( 'Min Deposit', 'rentfetch' ),
		'maximum_deposit'        => __( 'Max Deposit', 'rentfetch' ),
		'minimum_rent'           => __( 'Min Rent', 'rentfetch' ),
		'maximum_rent'           => __( 'Max Rent', 'rentfetch' ),
		'minimum_sqft'           => __( 'Min Sqrft', 'rentfetch' ),
		'maximum_sqft'           => __( 'Max Sqrft', 'rentfetch' ),
		'availability_date'      => __( 'Availability Date', 'rentfetch' ),
		'has_specials'           => __( 'Has Specials', 'rentfetch' ),
		'specials_override_text' => __( 'Specials Override Text', 'rentfetch' ),
		'availability_url'       => __( 'Availability URL', 'rentfetch' ),
		'available_units'        => __( 'Available Units', 'rentfetch' ),
		'api_response'           => __( 'API response', 'rentfetch' ),
	);

	return $columns;
}
add_filter( 'manage_floorplans_posts_columns', 'rentfetch_default_floorplans_admin_columns' );

/**
 * Set up the content of the columns
 *
 * @param string $column the label for the column.
 * @param int    $post_id the WordPress post ID.
 *
 * @return void
 */
function rentfetch_floorplans_default_column_content( $column, $post_id ) {
	
	if ( 'title' === $column ) {
		echo esc_attr( get_the_title( $post_id ) );
	}

	if ( 'floorplan_source' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'floorplan_source', true ) );
		
		// let's also run the script for this post here, showing disabled fields.
		$array_disabled_fields = apply_filters( 'rentfetch_filter_floorplan_syncing_fields', array(), $post_id );
		
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

	if ( 'property_id' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'property_id', true ) );
	}

	if ( 'property_name' === $column ) {

		$property_id = get_post_meta( $post_id, 'property_id', true );

		// Do a query for properties with this property_id.
		$args = array(
			'post_type'      => 'properties',
			'posts_per_page' => 1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'   => 'property_id',
					'value' => $property_id,
				),
			),
		);

		$properties = get_posts( $args );

		if ( ! empty( $properties ) ) {
			foreach ( $properties as $property ) {
				$property_title = get_the_title( $property->ID );
				$property_link  = get_the_permalink( $property->ID );
				printf(
					'<p class="description"><a target="_blank" href="%s">%s</a> (<a target="_blank" href="/wp-admin/post.php?post=%s&action=edit">edit</a>)</p>',
					esc_url( $property_link ),
					esc_attr( $property_title ),
					(int) $property->ID
				);
			}
		}
	}

	if ( 'floorplan_id' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'floorplan_id', true ) );
	}

	if ( 'tour' === $column ) {
		if ( get_post_meta( $post_id, 'tour', true ) ) {
			echo 'Tour embed code added';
		}
	}

	if ( 'manual_images' === $column ) {
		$images = get_post_meta( $post_id, 'manual_images', true );

		if ( is_array( $images ) && array( '' ) !== $images ) {

			$count = count( $images );

			// limit the array to 3 images
			if ( $count > 3 ) {
				$images           = array_slice( $images, 0, 3 );
				$remaining_images = $count - 3;
			}

			foreach ( $images as $image ) {
				$image = wp_get_attachment_image_url( $image, 'thumbnail' );
				echo '<img src="' . esc_attr( $image ) . '" style="width: 40px; height: 40px; margin-right: 2px;" />';
			}

			if ( ! empty( $remaining_images ) && 1 < $remaining_images ) {
				echo '<span style="">+' . esc_attr( $remaining_images ) . '</span>';
			}
		}
	}

	if ( 'floorplan_images' === $column ) {
		
		$floorplan_source = get_post_meta( $post_id, 'floorplan_source', true );
		$floorplan_images = null;
		
				
		if ( 'yardi' === $floorplan_source ) {
			$floorplan_images = rentfetch_get_floorplan_images_yardi();	
		} elseif ( 'rentmanager' === $floorplan_source ) {
			$floorplan_images = rentfetch_get_floorplan_images_rentmanager();
		} elseif ( 'entrata' === $floorplan_source ) {
			$floorplan_images = rentfetch_get_floorplan_images_entrata();
		}

		if ( is_array( $floorplan_images ) && array( '' ) !== $floorplan_images ) {
			$count = count( $floorplan_images );

			// limit the array to 3 images.
			if ( $count > 3 ) {
				$floorplan_images = array_slice( $floorplan_images, 0, 3 );
				$remaining_images = $count - 3;
			}

			foreach ( $floorplan_images as $image ) {				
				if ( isset( $image['url'] ) ) {
					$url = esc_url( $image['url'] );
					echo '<img src="' . esc_url( $url ) . '" style="width: 40px; height: 40px; margin-right: 2px;" />';
				}
				

			}

			if ( ! empty( $remaining_images ) && 1 < $remaining_images ) {
				echo '<span style="">+' . esc_attr( $remaining_images ) . '</span>';
			}
		}
	}

	if ( 'floorplan_description' === $column ) {
		if ( get_post_meta( $post_id, 'floorplan_description', true ) ) {
			echo '<span class="floorplan-description">';
				echo esc_attr( get_post_meta( $post_id, 'floorplan_description', true ) );
			echo '</span>';
		}
		
	}

	if ( 'floorplan_video_or_tour' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'floorplan_video_or_tour', true ) );
	}

	if ( 'beds' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'beds', true ) );
	}

	if ( 'baths' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'baths', true ) );
	}

	if ( 'minimum_deposit' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'minimum_deposit', true ) );
	}

	if ( 'maximum_deposit' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'maximum_deposit', true ) );
	}

	if ( 'minimum_rent' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'minimum_rent', true ) );
	}

	if ( 'maximum_rent' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'maximum_rent', true ) );
	}

	if ( 'minimum_sqft' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'minimum_sqft', true ) );
	}

	if ( 'maximum_sqft' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'maximum_sqft', true ) );
	}

	if ( 'availability_date' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'availability_date', true ) );
	}

	if ( 'has_specials' === $column ) {
		$has_specials = get_post_meta( $post_id, 'has_specials', true );

		if ( $has_specials ) {
			echo 'Yes';
		} else {
			echo 'No';
		}
	}
	
	if ( 'specials_override_text' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'specials_override_text', true ) );
	}

	if ( 'availability_url' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'availability_url', true ) );
	}

	if ( 'available_units' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'available_units', true ) );
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
add_action( 'manage_floorplans_posts_custom_column', 'rentfetch_floorplans_default_column_content', 10, 2 );
