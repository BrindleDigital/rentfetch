<?php
/**
 * Property, floor plan, and unit hierarchy.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Property hierarchy callback.
 *
 * @param WP_Post $post Property post.
 * @return void
 */
function rentfetch_properties_hierarchy_metabox_callback( $post ) {
	rentfetch_render_hierarchy( $post, 'properties' );
}

/**
 * Format a unit availability date.
 *
 * @param string $availability Raw availability value.
 * @return string
 */
function rentfetch_format_hierarchy_availability_date( $availability ) {
	if ( empty( $availability ) ) {
		return '';
	}

	foreach ( array( 'Y-m-d', 'm/d/Y', 'm/d/y' ) as $format ) {
		$date = DateTime::createFromFormat( $format, $availability );
		if ( $date ) {
			return $date->format( 'n/j/y' );
		}
	}

	return $availability;
}

/**
 * Render one hierarchy unit.
 *
 * @param WP_Post $unit         Unit post.
 * @param array   $unit_data    Preloaded unit display data.
 * @param string  $current_type Current record type.
 * @param int     $current_id   Current post ID.
 * @return void
 */
function rentfetch_render_hierarchy_unit( $unit, $unit_data, $current_type, $current_id ) {
	$availability = $unit_data['availability'] ?? '';
	$unit_id      = $unit_data['unit_id'] ?? '';
	$highlight    = ( 'units' === $current_type && $unit->ID === $current_id ) ? ' highlighted' : '';
	$faded        = empty( $availability ) ? ' faded' : '';
	$sync_class   = rentfetch_get_sync_status_class( $unit->ID );
	$tooltip      = rentfetch_get_sync_tooltip( $unit->ID );
	$formatted    = rentfetch_format_hierarchy_availability_date( $availability );

	echo '<a href="' . esc_url( get_edit_post_link( $unit->ID ) ) . '" class="hierarchy-item unit' . esc_attr( $highlight . $faded . ' ' . $sync_class ) . '" data-tooltip="' . esc_attr( $tooltip ) . '" data-rf-debug-navigation>';
	echo '<div class="unit-title">' . esc_html( $unit->post_title );
	if ( '' !== (string) $unit_id ) {
		echo '<span class="unit-id">' . esc_html( $unit_id ) . '</span>';
	}
	if ( '' !== $formatted ) {
		echo '<span class="unit-availability"> - ' . esc_html( $formatted ) . '</span>';
	}
	echo '</div></a>';
}

/**
 * Render the hierarchy for a property, floor plan, or unit.
 *
 * Floor plans and units are each retrieved in one query. Metadata is then read
 * from WordPress's primed post-meta cache and units are grouped in PHP.
 *
 * @param WP_Post $post         Current post.
 * @param string  $current_type Current post type.
 * @return void
 */
function rentfetch_render_hierarchy( $post, $current_type ) {
	$property_id = get_post_meta( $post->ID, 'property_id', true );

	if ( 'properties' === $current_type ) {
		$property_post = $post;
	} else {
		$property_posts = get_posts(
			array(
				'post_type'              => 'properties',
				'meta_key'               => 'property_id',
				'meta_value'             => $property_id,
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);
		$property_post  = $property_posts[0] ?? null;
	}

	if ( ! $property_post ) {
		echo '<p>No associated property found.</p>';
		return;
	}

	$floorplans = get_posts(
		array(
			'post_type'              => 'floorplans',
			'posts_per_page'         => -1,
			'meta_key'               => 'beds',
			'orderby'                => 'meta_value_num',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'   => 'property_id',
					'value' => $property_id,
				),
			),
		)
	);

	$floorplan_external_ids = array();
	$floorplan_data         = array();
	foreach ( $floorplans as $floorplan ) {
		$external_id = get_post_meta( $floorplan->ID, 'floorplan_id', true );
		if ( '' !== (string) $external_id ) {
			$floorplan_external_ids[] = $external_id;
		}

		$floorplan_data[ $floorplan->ID ] = array(
			'external_id'  => $external_id,
			'beds'         => get_post_meta( $floorplan->ID, 'beds', true ),
			'baths'        => get_post_meta( $floorplan->ID, 'baths', true ),
			'source'       => get_post_meta( $floorplan->ID, 'floorplan_source', true ),
			'api_response' => get_post_meta( $floorplan->ID, 'api_response', true ),
		);
	}

	$units = array();
	if ( ! empty( $floorplan_external_ids ) ) {
		$units = get_posts(
			array(
				'post_type'              => 'units',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'meta_query'             => array(
					array(
						'key'     => 'floorplan_id',
						'value'   => array_values( array_unique( $floorplan_external_ids ) ),
						'compare' => 'IN',
					),
				),
			)
		);
	}

	$units_by_floorplan = array();
	$unit_data          = array();
	foreach ( $units as $unit ) {
		$unit_floorplan_id                                   = get_post_meta( $unit->ID, 'floorplan_id', true );
		$unit_data[ $unit->ID ]                              = array(
			'availability' => get_post_meta( $unit->ID, 'availability_date', true ),
			'unit_id'      => get_post_meta( $unit->ID, 'unit_id', true ),
		);
		$units_by_floorplan[ (string) $unit_floorplan_id ][] = $unit;
	}

	$property_url = get_post_meta( $property_post->ID, 'url', true );
	if ( empty( $property_url ) ) {
		$property_url = get_permalink( $property_post->ID );
	}

	$property_sync      = rentfetch_get_sync_status_class( $property_post->ID );
	$property_tooltip   = rentfetch_get_sync_tooltip( $property_post->ID );
	$property_highlight = 'properties' === $current_type ? ' highlighted' : '';

	echo '<div class="rentfetch-hierarchy">';
	echo '<div class="property-container">';
	echo '<div class="property-info-content">';
	echo '<a href="' . esc_url( $property_url ) . '" class="' . esc_attr( $property_sync ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $property_post->post_title ) . '</a> · ';
	echo 'Property ID: ' . esc_html( get_post_meta( $property_post->ID, 'property_id', true ) );
	echo '</div>';
	echo '<a href="' . esc_url( get_edit_post_link( $property_post->ID ) ) . '" class="hierarchy-property-info' . esc_attr( $property_highlight . ' ' . $property_sync ) . '" data-tooltip="' . esc_attr( $property_tooltip ) . '" data-rf-debug-navigation></a>';
	echo '</div>';

	if ( ! empty( $floorplans ) ) {
		echo '<div class="floorplans-grid">';
		foreach ( $floorplans as $floorplan ) {
			$data            = $floorplan_data[ $floorplan->ID ];
			$external_id     = $data['external_id'];
			$all_units       = $units_by_floorplan[ (string) $external_id ] ?? array();
			$available_units = array_values(
				array_filter(
					$all_units,
					static function ( $unit ) use ( $unit_data ) {
						return ! empty( $unit_data[ $unit->ID ]['availability'] );
					}
				)
			);

			usort(
				$available_units,
				static function ( $unit_a, $unit_b ) use ( $unit_data ) {
					return strcmp(
						(string) $unit_data[ $unit_a->ID ]['availability'],
						(string) $unit_data[ $unit_b->ID ]['availability']
					);
				}
			);

			$available_count = count( $available_units );
			$faded           = 0 === $available_count ? ' faded' : '';
			$highlight       = ( 'floorplans' === $current_type && $floorplan->ID === $post->ID ) ? ' highlighted' : '';
			$sync_class      = rentfetch_get_sync_status_class( $floorplan->ID );
			$tooltip         = rentfetch_get_sync_tooltip( $floorplan->ID );
			$api_count       = null;
			$api_response    = is_array( $data['api_response'] ) ? $data['api_response'] : array();

			if ( 'yardi' === $data['source'] && isset( $api_response['floorplans_api']['api_response'] ) ) {
				$api_data = $api_response['floorplans_api']['api_response'];
				if ( is_string( $api_data ) ) {
					$api_data = json_decode( $api_data, true );
				}
				if ( is_array( $api_data ) && isset( $api_data['availableUnitsCount'] ) ) {
					$api_count = (int) $api_data['availableUnitsCount'];
				}
			}

			echo '<div class="floorplan-container' . esc_attr( $faded ) . '">';
			echo '<div class="floorplan-header">';
			echo '<div class="floorplan-title"><a href="' . esc_url( get_permalink( $floorplan->ID ) ) . '" class="' . esc_attr( $sync_class ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $floorplan->post_title ) . '</a></div>';
			echo '<div class="floorplan-details">' . esc_html( $data['beds'] ) . ' bed, ' . esc_html( $data['baths'] ) . ' bath · ID: ' . esc_html( $external_id ) . '</div>';
			if ( null !== $api_count && $available_count !== $api_count ) {
				echo '<div class="floorplan-units-count partial-availability">' . intval( $available_count ) . ' available (API: ' . intval( $api_count ) . ')</div>';
			} else {
				echo '<div class="floorplan-units-count">' . intval( $available_count ) . ' available</div>';
			}
			echo '</div>';
			echo '<a href="' . esc_url( get_edit_post_link( $floorplan->ID ) ) . '" class="hierarchy-item floorplan' . esc_attr( $highlight . ' ' . $sync_class ) . '" data-tooltip="' . esc_attr( $tooltip ) . '" data-rf-debug-navigation></a>';

			if ( ! empty( $available_units ) ) {
				$visible_units = array_slice( $available_units, 0, 3 );
				$hidden_units  = array_slice( $available_units, 3 );

				echo '<div class="units-grid">';
				foreach ( $visible_units as $unit ) {
					rentfetch_render_hierarchy_unit( $unit, $unit_data[ $unit->ID ], $current_type, $post->ID );
				}
				if ( ! empty( $hidden_units ) ) {
					echo '<div class="units-hidden" hidden>';
					foreach ( $hidden_units as $unit ) {
						rentfetch_render_hierarchy_unit( $unit, $unit_data[ $unit->ID ], $current_type, $post->ID );
					}
					echo '</div>';
					echo '<div class="units-show-more"><button type="button" class="button-link show-more-link">Show ' . intval( count( $hidden_units ) ) . ' more…</button></div>';
				}
				echo '</div>';
			}

			echo '</div>';
		}
		echo '</div>';
	}

	echo '</div>';
}
