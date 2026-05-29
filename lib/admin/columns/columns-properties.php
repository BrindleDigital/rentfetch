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
		'sync_status'           => __( 'Syncing', 'rentfetch' ),
		'property_source'       => __( 'Property Source', 'rentfetch' ),
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
		'url_override'          => __( 'URL override', 'rentfetch' ),
		'tour_booking_link'     => __( 'Tour Booking Link', 'rentfetch' ),
		'images'                => __( 'Manual Images', 'rentfetch' ),
		'synced_property_images' => __( 'Synced Images', 'rentfetch' ),
		'description'           => __( 'Description', 'rentfetch' ),
		'tour'                  => __( 'Tour', 'rentfetch' ),
		// 'pets'                  => __( 'Pets', 'rentfetch' ),
		'content_area'          => __( 'Content Area', 'rentfetch' ),
		'api_response'          => __( 'API response', 'rentfetch' ),
	);

	return $columns;
}
add_filter( 'manage_properties_posts_columns', 'rentfetch_default_properties_admin_columns' );

/**
 * Filter synced content lists to records that need attention.
 *
 * @param WP_Query $query Main admin query.
 * @return void
 */
function rentfetch_filter_admin_sync_status_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$post_type = $query->get( 'post_type' );
	if ( ! in_array( $post_type, array( 'properties', 'floorplans', 'units' ), true ) ) {
		return;
	}

	$sync_status_filter = isset( $_GET['rentfetch_sync_status'] )
		? sanitize_key( wp_unslash( $_GET['rentfetch_sync_status'] ) )
		: '';

	if ( 'needs_attention' !== $sync_status_filter ) {
		return;
	}

	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$post_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status != 'trash'",
			$post_type
		)
	);

	$needs_attention_ids = array();
	foreach ( $post_ids as $post_id ) {
		$status = function_exists( 'rentfetch_get_sync_status_class' )
			? rentfetch_get_sync_status_class( (int) $post_id )
			: 'sync-gray';

		if ( 'sync-green' !== $status ) {
			$needs_attention_ids[] = (int) $post_id;
		}
	}

	$query->set( 'post__in', ! empty( $needs_attention_ids ) ? $needs_attention_ids : array( 0 ) );
	$query->set( 'orderby', 'post__in' );
}
add_action( 'pre_get_posts', 'rentfetch_filter_admin_sync_status_query' );

/**
 * Get the overall sync status from an array of individual statuses.
 *
 * @param array $statuses Array of sync status classes.
 * @return string The worst sync status class.
 */
function rentfetch_get_overall_sync_status( $statuses ) {
	if ( empty( $statuses ) ) {
		return 'sync-gray';
	}

	$priorities = array(
		'sync-red'    => 5,
		'sync-orange' => 4,
		'sync-yellow' => 3,
		'sync-green'  => 2,
		'sync-gray'   => 1,
	);

	$max_priority = 0;
	$worst_status = 'sync-gray';

	foreach ( $statuses as $status ) {
		if ( isset( $priorities[ $status ] ) && $priorities[ $status ] > $max_priority ) {
			$max_priority = $priorities[ $status ];
			$worst_status = $status;
		}
	}

	return $worst_status;
}

/**
 * Get a display label for a sync endpoint.
 *
 * @param string $endpoint Endpoint key.
 * @return string
 */
function rentfetch_get_sync_endpoint_label( $endpoint ) {
	$labels = array(
		'properties_api'                  => __( 'Properties', 'rentfetch' ),
		'property_images_api'             => __( 'Images', 'rentfetch' ),
		'lease_fees_api'                  => __( 'Fees', 'rentfetch' ),
		'floorplans_api'                  => __( 'Floor plans', 'rentfetch' ),
		'unit_types_api'                  => __( 'Plans', 'rentfetch' ),
		'apartmentavailability_api'       => __( 'Units', 'rentfetch' ),
		'units_api'                       => __( 'Units', 'rentfetch' ),
		'getMitsPropertyUnits'            => __( 'Property units', 'rentfetch' ),
		'getUnitsAvailabilityAndPricing'  => __( 'Availability', 'rentfetch' ),
	);

	return isset( $labels[ $endpoint ] ) ? $labels[ $endpoint ] : ucwords( str_replace( '_', ' ', (string) $endpoint ) );
}

/**
 * Get the endpoint-level sync status map for a post.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function rentfetch_get_column_sync_status_map( $post_id ) {
	if ( function_exists( 'rfs_prune_sync_status_to_registry' ) ) {
		$sync_status = rfs_prune_sync_status_to_registry( $post_id );
		return is_array( $sync_status ) ? $sync_status : array();
	}

	if ( function_exists( 'rfs_get_sync_status_map' ) ) {
		$sync_status = rfs_get_sync_status_map( $post_id );
		return is_array( $sync_status ) ? $sync_status : array();
	}

	$sync_status = get_post_meta( $post_id, 'sync_status', true );
	return is_array( $sync_status ) ? $sync_status : array();
}

/**
 * Get a readable endpoint state for tooltip text.
 *
 * @param array $endpoint_state Endpoint state data.
 * @return string
 */
function rentfetch_get_column_endpoint_state_label( $endpoint_state ) {
	if ( ! is_array( $endpoint_state ) ) {
		return __( 'unknown', 'rentfetch' );
	}

	$state = isset( $endpoint_state['state'] ) ? (string) $endpoint_state['state'] : 'unknown';

	if ( 'success' === $state ) {
		return __( 'worked', 'rentfetch' );
	}

	if ( 'partial' === $state ) {
		return __( 'no data returned', 'rentfetch' );
	}

	if ( 'failed' === $state ) {
		return __( 'failed', 'rentfetch' );
	}

	return $state;
}

/**
 * Build the hover tooltip for a sync status dot.
 *
 * @param string $heading Heading for the tooltip.
 * @param array  $posts   Posts represented by the dot.
 * @return string
 */
function rentfetch_get_column_sync_tooltip( $heading, $posts ) {
	$lines = array( $heading );
	$posts = array_values( array_filter( (array) $posts ) );

	if ( empty( $posts ) ) {
		$lines[] = __( 'No synced records found.', 'rentfetch' );
		return implode( "\n", $lines );
	}

	$endpoint_summary = array();
	$no_status_count  = 0;

	foreach ( $posts as $post ) {
		$post_id = is_object( $post ) && isset( $post->ID ) ? (int) $post->ID : (int) $post;
		if ( $post_id <= 0 ) {
			continue;
		}

		$sync_status = rentfetch_get_column_sync_status_map( $post_id );

		if ( empty( $sync_status ) ) {
			++$no_status_count;
			continue;
		}

		foreach ( $sync_status as $endpoint => $endpoint_state ) {
			$endpoint_label = rentfetch_get_sync_endpoint_label( $endpoint );
			$state_label    = rentfetch_get_column_endpoint_state_label( $endpoint_state );

			if ( ! isset( $endpoint_summary[ $endpoint_label ] ) ) {
				$endpoint_summary[ $endpoint_label ] = array();
			}

			if ( ! isset( $endpoint_summary[ $endpoint_label ][ $state_label ] ) ) {
				$endpoint_summary[ $endpoint_label ][ $state_label ] = 0;
			}

			++$endpoint_summary[ $endpoint_label ][ $state_label ];
		}
	}

	foreach ( $endpoint_summary as $endpoint_label => $states ) {
		$state_parts = array();
		foreach ( $states as $state_label => $count ) {
			$state_parts[] = 1 === (int) count( $posts )
				? $state_label
				: sprintf( '%s %s', number_format_i18n( $count ), $state_label );
		}

		$lines[] = sprintf( '%s: %s', $endpoint_label, implode( ', ', $state_parts ) );
	}

	if ( $no_status_count > 0 ) {
		$lines[] = 1 === $no_status_count
			? __( 'No API status', 'rentfetch' )
			: sprintf( __( '%s records with no API status', 'rentfetch' ), number_format_i18n( $no_status_count ) );
	}

	return implode( "\n", $lines );
}

/**
 * Print instant sync-dot tooltip styles for admin list tables.
 *
 * @return void
 */
function rentfetch_admin_sync_dot_tooltip_styles() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->post_type, array( 'properties', 'floorplans', 'units' ), true ) ) {
		return;
	}
	?>
	<style>
		.wp-list-table .rentfetch-sync-dot {
			cursor: pointer;
			display: inline-block;
			font-size: 16px;
			line-height: 1;
		}

		#rentfetch-sync-dot-tooltip {
			background: #1d2327;
			border-radius: 3px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
			box-sizing: border-box;
			color: #fff;
			display: none;
			font-size: 12px;
			font-weight: 400;
			line-height: 1.35;
			max-width: 360px;
			min-width: 220px;
			padding: 8px 10px;
			pointer-events: none;
			position: fixed;
			text-align: left;
			white-space: pre-line;
			z-index: 100000;
		}
	</style>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var tooltip = document.getElementById('rentfetch-sync-dot-tooltip');

			if (!tooltip) {
				tooltip = document.createElement('div');
				tooltip.id = 'rentfetch-sync-dot-tooltip';
				tooltip.setAttribute('role', 'tooltip');
				document.body.appendChild(tooltip);
			}

			function positionTooltip(dot) {
				var rect = dot.getBoundingClientRect();
				var tooltipRect = tooltip.getBoundingClientRect();
				var viewportWidth = document.documentElement.clientWidth;
				var top = rect.bottom + 8;
				var left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);

				if (left < 8) {
					left = 8;
				}

				if (left + tooltipRect.width > viewportWidth - 8) {
					left = viewportWidth - tooltipRect.width - 8;
				}

				tooltip.style.left = left + 'px';
				tooltip.style.top = top + 'px';
			}

			function showTooltip(dot) {
				var text = dot.getAttribute('data-tooltip');

				if (!text) {
					return;
				}

				tooltip.textContent = text;
				tooltip.style.display = 'block';
				positionTooltip(dot);
			}

			function hideTooltip() {
				tooltip.style.display = 'none';
			}

			document.addEventListener('mouseenter', function(event) {
				if (event.target.matches('.rentfetch-sync-dot')) {
					showTooltip(event.target);
				}
			}, true);

			document.addEventListener('focusin', function(event) {
				if (event.target.matches('.rentfetch-sync-dot')) {
					showTooltip(event.target);
				}
			});

			document.addEventListener('mouseleave', function(event) {
				if (event.target.matches('.rentfetch-sync-dot')) {
					hideTooltip();
				}
			}, true);

			document.addEventListener('focusout', function(event) {
				if (event.target.matches('.rentfetch-sync-dot')) {
					hideTooltip();
				}
			});

			window.addEventListener('scroll', hideTooltip, true);
			window.addEventListener('resize', hideTooltip);
		});
	</script>
	<?php
}
add_action( 'admin_head', 'rentfetch_admin_sync_dot_tooltip_styles' );

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
	
	if ( 'sync_status' === $column ) {
		
		$property_sync = rentfetch_get_sync_status_class( $post_id );
		$property_id = get_post_meta( $post_id, 'property_id', true );
		
		// Floorplans
		$floorplans = get_posts( array(
			'post_type' => 'floorplans',
			'meta_query' => array(
				array(
					'key' => 'property_id',
					'value' => $property_id,
					'compare' => '=',
				),
			),
			'posts_per_page' => -1,
		) );
		
		$floorplan_statuses = array();
		foreach ( $floorplans as $floorplan ) {
			$floorplan_statuses[] = rentfetch_get_sync_status_class( $floorplan->ID );
		}
		$floorplan_sync = rentfetch_get_overall_sync_status( $floorplan_statuses );
		
		// Units
		$floorplan_ids = array();
		foreach ( $floorplans as $floorplan ) {
			$id = get_post_meta( $floorplan->ID, 'floorplan_id', true );
			if ( ! empty( $id ) ) {
				$floorplan_ids[] = $id;
			}
		}
		
		$units = array();
		if ( ! empty( $floorplan_ids ) ) {
			$units = get_posts( array(
				'post_type' => 'units',
				'meta_query' => array(
					array(
						'key' => 'floorplan_id',
						'value' => $floorplan_ids,
						'compare' => 'IN',
					),
				),
				'posts_per_page' => -1,
			) );
		}
		
		$unit_statuses = array();
		foreach ( $units as $unit ) {
			$unit_statuses[] = rentfetch_get_sync_status_class( $unit->ID );
		}
		$unit_sync = rentfetch_get_overall_sync_status( $unit_statuses );
		
		// Colors
		$sync_colors = array(
			'sync-green'  => '#28a745',
			'sync-yellow' => '#dba617',
			'sync-orange' => '#d9822b',
			'sync-red'    => '#dc3545',
			'sync-gray'   => '#6c757d',
		);

		$property_tooltip  = rentfetch_get_column_sync_tooltip( __( 'Properties APIs', 'rentfetch' ), array( $post_id ) );
		$floorplan_tooltip = rentfetch_get_column_sync_tooltip( __( 'Floor plans APIs', 'rentfetch' ), $floorplans );
		$unit_tooltip      = rentfetch_get_column_sync_tooltip( __( 'Units APIs', 'rentfetch' ), $units );
		
		// Output
		echo '<span class="rentfetch-sync-dot" tabindex="0" data-tooltip="' . esc_attr( $property_tooltip ) . '" aria-label="' . esc_attr( $property_tooltip ) . '" style="color: ' . esc_attr( $sync_colors[ $property_sync ] ?? '#6c757d' ) . ';">●</span> ';
		echo '<span class="rentfetch-sync-dot" tabindex="0" data-tooltip="' . esc_attr( $floorplan_tooltip ) . '" aria-label="' . esc_attr( $floorplan_tooltip ) . '" style="color: ' . esc_attr( $sync_colors[ $floorplan_sync ] ?? '#6c757d' ) . ';">●</span> ';
		echo '<span class="rentfetch-sync-dot" tabindex="0" data-tooltip="' . esc_attr( $unit_tooltip ) . '" aria-label="' . esc_attr( $unit_tooltip ) . '" style="color: ' . esc_attr( $sync_colors[ $unit_sync ] ?? '#6c757d' ) . ';">●</span>';
		
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
	
	if ( 'url_override' === $column ) {
		printf( '<a target="_blank" href="%s">%s</a>', esc_url( get_post_meta( $post_id, 'url_override', true ) ), esc_attr( get_post_meta( $post_id, 'url_override', true ) ) );
	}
	
	if ( 'tour_booking_link' === $column ) {
		$tour_booking_link = get_post_meta( $post_id, 'tour_booking_link', true );
		if ( $tour_booking_link ) {
			printf( '<a target="_blank" href="%s">%s</a>', esc_url( $tour_booking_link ), esc_attr( $tour_booking_link ) );
		} else {
			echo '—';
		}
	}

	if ( 'images' === $column ) {
		$images = get_post_meta( $post_id, 'images', true );
		$remaining_images = 0;

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
		
		if ( 'yardi' === $property_source ) {
			$synced_images = rentfetch_get_property_images_yardi( null );
		} elseif ( 'rentmanager' === $property_source ) {
			$synced_images = rentfetch_get_property_images_rentmanager( null );
		} elseif ( 'entrata' === $property_source ) {
			$synced_images = rentfetch_get_property_images_entrata( null );
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
