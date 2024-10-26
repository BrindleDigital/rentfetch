<?php
/**
 * This file sets up the default columns for the Properties custom post type in the admin.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Enqueue the admin style for the properties custom post type.
 *
 * @return void
 */
function rentfetch_enqueue_properties_admin_style() {

	// bail if admin columns pro is active, or admin columns is active, since our styles conflict with those plugins.
	// if ( is_plugin_active( 'admin-columns-pro/admin-columns-pro.php' ) || is_plugin_active( 'codepress-admin-columns/codepress-admin-columns.php' ) ) {
	// return;
	// }

	$current_screen = get_current_screen();

	// Check if the current screen is the admin archive page of the properties content type.
	if ( 'edit' === $current_screen->base && 'properties' === $current_screen->post_type ) {

		// Enqueue your custom admin style.
		wp_enqueue_style( 'properties-edit-admin-style', RENTFETCH_PATH . 'css/admin/admin-edit-properties.css', array(), RENTFETCH_VERSION, 'screen' );

	}
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_properties_admin_style' );

/**
 * Set up the admin columns order.
 *
 * @param array $columns an array of the columns we'd like to use.
 *
 * @return array $columns.
 */
function rentfetch_default_properties_admin_columns( $columns ) {

	$columns = array(
		'cb'                    => '<input type="checkbox" />',
		'title'                 => __( 'Title', 'rentfetch' ),
		'property_id'           => __( 'Property ID', 'rentfetch' ),
		'address'               => __( 'Address', 'rentfetch' ),
		'city'                  => __( 'City', 'rentfetch' ),
		'state'                 => __( 'State', 'rentfetch' ),
		'zipcode'               => __( 'Zipcode', 'rentfetch' ),
		'latitude'              => __( 'Latitude', 'rentfetch' ),
		'longitude'             => __( 'Longitude', 'rentfetch' ),
		'email'                 => __( 'Email', 'rentfetch' ),
		'phone'                 => __( 'Phone', 'rentfetch' ),
		'url'                   => __( 'URL', 'rentfetch' ),
		'images'                => __( 'Manual Images', 'rentfetch' ),
		'synced_property_images' => __( 'Synced Images', 'rentfetch' ),
		'description'           => __( 'Description', 'rentfetch' ),
		'tour'                  => __( 'Tour', 'rentfetch' ),
		// 'pets'                  => __( 'Pets', 'rentfetch' ),
		'content_area'          => __( 'Content Area', 'rentfetch' ),
		'property_source'       => __( 'Property Source', 'rentfetch' ),
		'api_response'          => __( 'API response', 'rentfetch' ),
	);

	return $columns;
}
add_filter( 'manage_properties_posts_columns', 'rentfetch_default_properties_admin_columns' );

/**
 * Set up the content for the admin columns.
 *
 * @param string $column  The column name.
 * @param int    $post_id The post ID.
 *
 * @return void
 */
function rentfetch_properties_default_column_content( $column, $post_id ) {

	if ( 'title' === $column ) {
		echo esc_attr( get_the_title( $post_id ) );
	}

	if ( 'property_id' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'property_id', true ) );
	}

	if ( 'property_code' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'property_code', true ) );
	}

	if ( 'address' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'address', true ) );
	}

	if ( 'city' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'city', true ) );
	}

	if ( 'state' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'state', true ) );
	}

	if ( 'zipcode' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'zipcode', true ) );
	}

	if ( 'latitude' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'latitude', true ) );
	}

	if ( 'longitude' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'longitude', true ) );
	}

	if ( 'email' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'email', true ) );
	}

	if ( 'phone' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'phone', true ) );
	}

	if ( 'url' === $column ) {
		printf( '<a target="_blank" href="%s">%s</a>', esc_url( get_post_meta( $post_id, 'url', true ) ), esc_attr( get_post_meta( $post_id, 'url', true ) ) );
	}

	if ( 'images' === $column ) {
		$images = get_post_meta( $post_id, 'images', true );

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

			if ( 1 < $remaining_images ) {
				echo '<span style="">+' . esc_attr( $remaining_images ) . '</span>';
			}
		}
	}

	if ( 'property_source' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'property_source', true ) );
		
		// let's also run the script for this post here, showing disabled fields.
		$array_disabled_fields = apply_filters( 'rentfetch_filter_property_syncing_fields', array(), $post_id );
		
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

	if ( 'description' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'description', true ) );
	}

	if ( 'tour' === $column ) {
		if ( get_post_meta( $post_id, 'tour', true ) ) {
			echo 'Tour embed code added';
		}
	}

	if ( 'pets' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'pets', true ) );
	}

	if ( 'content_area' === $column ) {
		echo esc_attr( get_post_meta( $post_id, 'content_area', true ) );
	}

	if ( 'synced_property_images' === $column ) {
		
		$property_source = get_post_meta( $post_id, 'property_source', true );
		$synced_images = null;
		
		if ( $property_source === 'yardi' ) {
			$synced_images = rentfetch_get_property_images_yardi( null );
		} elseif ( $property_source === 'rentmanager' ) {
			$synced_images = rentfetch_get_property_images_rentmanager( null );
		}

		if ( is_array( $synced_images ) ) {
			$count = count( $synced_images );

			// limit the array to 3 images
			if ( $count > 3 ) {
				$synced_images     = array_slice( $synced_images, 0, 3 );
				$remaining_images = $count - 3;
			}

			foreach ( $synced_images as $image ) {
				// var_dump( $image['url'] );
				echo '<img src="' . esc_url( $image['url'] ) . '" style="width: 40px; height: 40px; margin-right: 2px;" />';

			}

			if ( ! empty( $remainining_images ) && 1 < $remaining_images ) {
				echo '<span style="">+' . esc_attr( $remaining_images ) . '</span>';
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

	if ( 'attraction_type' === $column ) {
		$terms = get_the_terms( $post_id, 'attractiontypes' );
		$count = 0;

		if ( $terms ) {
			foreach ( $terms as $term ) {
				if ( 0 !== $count ) {
					echo ', ';
				}

				echo esc_html( $term->name );
				++$count;
			}
		}
	}

	if ( 'na_attractions_always_show' === $column ) {
		$always_show = get_post_meta( $post_id, 'na_attractions_always_show', true );

		if ( $always_show ) {
			echo 'Yes';
		} else {
			echo 'No';
		}
	}
}
add_action( 'manage_properties_posts_custom_column', 'rentfetch_properties_default_column_content', 10, 2 );
