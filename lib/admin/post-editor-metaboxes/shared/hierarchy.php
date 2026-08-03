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
	$tooltip      = rentfetch_get_hierarchy_tooltip_for_post( $unit->ID );
	$formatted    = rentfetch_format_hierarchy_availability_date( $availability );

	echo '<a href="' . esc_url( get_edit_post_link( $unit->ID ) ) . '" class="hierarchy-item unit' . esc_attr( $highlight . $faded . ' ' . $sync_class ) . '" data-post-id="' . absint( $unit->ID ) . '" data-tooltip="' . esc_attr( $tooltip ) . '" data-rf-debug-navigation>';
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
 * Get the records needed for the compact hierarchy navigation.
 *
 * @param WP_Post $post         Current post.
 * @param string  $current_type Current post type.
 * @return array<string, mixed>
 */
function rentfetch_get_hierarchy_navigation_context( $post, $current_type ) {
	$property_id   = trim( (string) get_post_meta( $post->ID, 'property_id', true ) );
	$property_post = 'properties' === $current_type ? $post : null;

	if ( ! $property_post && '' !== $property_id ) {
		$property_posts = get_posts(
			array(
				'post_type'              => 'properties',
				'post_status'            => 'any',
				'meta_key'               => 'property_id',
				'meta_value'             => $property_id,
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);
		$property_post = $property_posts[0] ?? null;
	}

	$floorplans = array();
	if ( '' !== $property_id ) {
		$floorplans = get_posts(
			array(
				'post_type'              => 'floorplans',
				'post_status'            => 'any',
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
	}

	$current_floorplan_id          = 'floorplans' === $current_type ? (int) $post->ID : 0;
	$current_floorplan_external_id = 'units' === $current_type
		? trim( (string) get_post_meta( $post->ID, 'floorplan_id', true ) )
		: '';
	$current_floorplan              = null;
	$floorplan_data                 = array();

	foreach ( $floorplans as $floorplan ) {
		$external_id = trim( (string) get_post_meta( $floorplan->ID, 'floorplan_id', true ) );
		$floorplan_data[ $floorplan->ID ] = array(
			'external_id' => $external_id,
			'beds'        => get_post_meta( $floorplan->ID, 'beds', true ),
			'baths'       => get_post_meta( $floorplan->ID, 'baths', true ),
		);

		if ( $current_floorplan_id === (int) $floorplan->ID || ( '' !== $current_floorplan_external_id && $current_floorplan_external_id === $external_id ) ) {
			$current_floorplan    = $floorplan;
			$current_floorplan_id = (int) $floorplan->ID;
		}
	}

	$floorplan_external_ids = array_values(
		array_filter(
			array_map(
				static function ( $data ) {
					return trim( (string) $data['external_id'] );
				},
				$floorplan_data
			)
		)
	);
	$units_by_floorplan = array();
	if ( ! empty( $floorplan_external_ids ) ) {
		$units = get_posts(
			array(
				'post_type'              => 'units',
				'post_status'            => 'any',
				'posts_per_page'         => -1,
				'meta_key'               => 'unit_id',
				'orderby'                => 'meta_value',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'meta_query'             => array(
					array(
						'key'     => 'floorplan_id',
						'value'   => $floorplan_external_ids,
						'compare' => 'IN',
					),
				),
			)
		);

		foreach ( $units as $unit ) {
			$unit_floorplan_id = trim( (string) get_post_meta( $unit->ID, 'floorplan_id', true ) );
			$units_by_floorplan[ $unit_floorplan_id ][] = $unit;
		}
	}
	return array(
		'property_id'        => $property_id,
		'property_post'      => $property_post,
		'floorplans'         => $floorplans,
		'floorplan_data'     => $floorplan_data,
		'current_floorplan'  => $current_floorplan,
		'units_by_floorplan' => $units_by_floorplan,
	);
}

/**
 * Build compact navigation tooltip content for one record.
 *
 * @param string $title    Record title.
 * @param string $details  Short record details.
 * @param int    $post_id  Record post ID.
 * @return string
 */
function rentfetch_get_hierarchy_navigation_tooltip( $title, $details, $post_id ) {
	$tooltip = '<div class="rf-hierarchy-tooltip-content">';
	$tooltip .= '<div class="rf-hierarchy-tooltip-record">' . esc_html( $title ) . '</div>';

	if ( '' !== trim( $details ) ) {
		$tooltip .= '<div class="rf-hierarchy-tooltip-meta">' . esc_html( $details ) . '</div>';
	}

	$tooltip .= rentfetch_get_sync_tooltip( $post_id );
	$tooltip .= '</div>';

	return $tooltip;
}

/**
 * Build the shared tooltip for a hierarchy record.
 *
 * @param int $post_id Record post ID.
 * @return string
 */
function rentfetch_get_hierarchy_tooltip_for_post( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return rentfetch_get_sync_tooltip( $post_id );
	}

	$title   = get_the_title( $post ) ?: 'Untitled record';
	$details = array();

	if ( 'properties' === $post->post_type ) {
		$property_id = trim( (string) get_post_meta( $post_id, 'property_id', true ) );
		$source      = trim( (string) get_post_meta( $post_id, 'property_source', true ) );
		if ( '' !== $property_id ) {
			$details[] = 'Property ID: ' . $property_id;
		}
		if ( '' !== $source ) {
			$details[] = 'Source: ' . $source;
		}
	} elseif ( 'floorplans' === $post->post_type ) {
		$floorplan_id = trim( (string) get_post_meta( $post_id, 'floorplan_id', true ) );
		$beds         = trim( (string) get_post_meta( $post_id, 'beds', true ) );
		$baths        = trim( (string) get_post_meta( $post_id, 'baths', true ) );
		$source       = trim( (string) get_post_meta( $post_id, 'floorplan_source', true ) );
		$plan_details = array_filter(
			array(
				'' !== $beds ? $beds . ' bed' : '',
				'' !== $baths ? $baths . ' bath' : '',
			)
		);
		if ( '' !== $floorplan_id ) {
			$details[] = 'Floor plan ID: ' . $floorplan_id;
		}
		if ( ! empty( $plan_details ) ) {
			$details[] = implode( ', ', $plan_details );
		}
		if ( '' !== $source ) {
			$details[] = 'Source: ' . $source;
		}
	} elseif ( 'units' === $post->post_type ) {
		$unit_id      = trim( (string) get_post_meta( $post_id, 'unit_id', true ) );
		$availability = rentfetch_format_hierarchy_availability_date( get_post_meta( $post_id, 'availability_date', true ) );
		$source       = trim( (string) get_post_meta( $post_id, 'unit_source', true ) );
		if ( '' !== $unit_id ) {
			$details[] = 'Unit ID: ' . $unit_id;
		}
		if ( '' !== $availability ) {
			$details[] = 'Available: ' . $availability;
		}
		if ( '' !== $source ) {
			$details[] = 'Source: ' . $source;
		}
	}

	return rentfetch_get_hierarchy_navigation_tooltip( $title, implode( ' · ', $details ), $post_id );
}

/**
 * Render one hierarchy navigation item.
 *
 * @param int    $post_id     Record post ID.
 * @param string $label       Short visible label.
 * @param string $aria_label  Accessible record label.
 * @param string $tooltip     Hover tooltip HTML.
 * @param string $sync_class  Sync status class.
 * @param bool   $is_current  Whether this is the displayed record.
 * @param bool   $is_ancestor Whether this is the displayed record's parent.
 * @param bool   $show_status Whether to show the status dot.
 * @return void
 */
function rentfetch_render_hierarchy_navigation_item( $post_id, $label, $aria_label, $tooltip, $sync_class, $is_current = false, $is_ancestor = false, $show_status = true ) {
	$edit_link = get_edit_post_link( $post_id );
	if ( ! $edit_link ) {
		return;
	}

	$classes = array( 'rf-hierarchy-navigation-item', $sync_class );
	if ( $is_current ) {
		$classes[] = 'is-current';
	} elseif ( $is_ancestor ) {
		$classes[] = 'is-ancestor';
	}

	$current_attribute = $is_current ? ' aria-current="page"' : '';
	echo '<a href="' . esc_url( $edit_link ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '" data-post-id="' . absint( $post_id ) . '" data-tooltip="' . esc_attr( $tooltip ) . '" aria-label="' . esc_attr( $aria_label ) . '" data-rf-debug-navigation' . $current_attribute . '>';
	if ( $show_status ) {
		echo '<span class="rf-hierarchy-navigation-status" aria-hidden="true"></span>';
	}
	echo '<span class="rf-hierarchy-navigation-item-label">' . esc_html( $label ) . '</span>';
	echo '</a>';
}

/**
 * Render the nested hierarchy navigation.
 *
 * @param WP_Post $post         Current post.
 * @param string  $current_type Current post type.
 * @return void
 */
function rentfetch_render_hierarchy_navigation( $post, $current_type ) {
	$context              = rentfetch_get_hierarchy_navigation_context( $post, $current_type );
	$property_post        = $context['property_post'];
	$property_id          = $context['property_id'];
	$floorplans           = $context['floorplans'];
	$floorplan_data       = $context['floorplan_data'];
	$current_floorplan    = $context['current_floorplan'];
	$units_by_floorplan   = $context['units_by_floorplan'];
	$property_is_current  = 'properties' === $current_type;
	$property_is_ancestor = ! $property_is_current && in_array( $current_type, array( 'floorplans', 'units' ), true );

	echo '<nav class="rf-hierarchy-navigation" aria-label="Property hierarchy navigation">';
	echo '<div class="rf-hierarchy-navigation-property-shell">';
	if ( $property_post ) {
		$property_title   = get_the_title( $property_post );
		$property_label   = $property_title ? $property_title : 'Untitled property';
		$property_details = '' !== $property_id ? 'Property ID: ' . $property_id : '';
		$property_tooltip = rentfetch_get_hierarchy_tooltip_for_post( $property_post->ID );
		rentfetch_render_hierarchy_navigation_item(
			$property_post->ID,
			$property_label,
			sprintf( 'Property: %s%s', $property_label, '' !== $property_details ? ' — ' . $property_details : '' ),
			$property_tooltip,
			rentfetch_get_sync_status_class( $property_post->ID ),
			$property_is_current,
			$property_is_ancestor
		);
	} else {
		echo '<span class="rf-hierarchy-navigation-empty">Unavailable</span>';
	}
	echo '<span class="rf-hierarchy-navigation-count">(' . absint( count( $floorplans ) ) . ' ' . ( 1 === count( $floorplans ) ? 'floor plan' : 'floor plans' ) . ')</span>';
	echo '</div>';

	echo '<div class="rf-hierarchy-navigation-floorplans">';
	echo '<div class="rf-hierarchy-navigation-floorplan-list">';
	foreach ( $floorplans as $floorplan ) {
		$data             = $floorplan_data[ $floorplan->ID ];
		$units            = $units_by_floorplan[ $data['external_id'] ] ?? array();
		$title            = get_the_title( $floorplan );
		$label            = $title ? $title : 'Untitled plan';
		$details          = array();
		$details[]        = 'Floor Plan ID: ' . $data['external_id'];
		$details[]        = trim( $data['beds'] . ' bed, ' . $data['baths'] . ' bath' );
		$tooltip          = rentfetch_get_hierarchy_tooltip_for_post( $floorplan->ID );
		$is_current       = 'floorplans' === $current_type && (int) $floorplan->ID === (int) $post->ID;
		$is_ancestor      = 'units' === $current_type && $current_floorplan && (int) $floorplan->ID === (int) $current_floorplan->ID;
		$aria_label       = sprintf( 'Floor plan: %s — %s', $label, implode( ', ', array_filter( $details ) ) );

		echo '<div class="rf-hierarchy-navigation-floorplan-card' . ( $is_current ? ' is-current' : '' ) . '">';
		echo '<div class="rf-hierarchy-navigation-floorplan-label-row">';
		rentfetch_render_hierarchy_navigation_item(
			$floorplan->ID,
			$label,
			$aria_label,
			$tooltip,
			rentfetch_get_sync_status_class( $floorplan->ID ),
			$is_current,
			$is_ancestor,
			false
		);

		echo '</div>';

		echo '<div class="rf-hierarchy-navigation-unit-list" aria-label="' . esc_attr( count( $units ) . ( 1 === count( $units ) ? ' unit' : ' units' ) ) . '">';
		if ( empty( $units ) ) {
			echo '<span class="rf-hierarchy-navigation-empty rf-hierarchy-navigation-empty-units">No units</span>';
		} else {
			foreach ( $units as $unit ) {
				$unit_title   = get_the_title( $unit );
				$unit_id      = trim( (string) get_post_meta( $unit->ID, 'unit_id', true ) );
				$availability = rentfetch_format_hierarchy_availability_date( get_post_meta( $unit->ID, 'availability_date', true ) );
				$label        = '' !== $unit_id ? $unit_id : ( $unit_title ? $unit_title : 'Untitled unit' );
				$details      = array_filter(
					array(
						'' !== $unit_id ? 'Unit ID: ' . $unit_id : '',
						'' !== $availability ? 'Available: ' . $availability : '',
					)
				);
				$aria_label   = sprintf( 'Unit: %s%s', $unit_title ? $unit_title : $label, ! empty( $details ) ? ' — ' . implode( ', ', $details ) : '' );

				rentfetch_render_hierarchy_navigation_item(
					$unit->ID,
					$label,
					$aria_label,
					rentfetch_get_hierarchy_tooltip_for_post( $unit->ID ),
					rentfetch_get_sync_status_class( $unit->ID ),
					'units' === $current_type && (int) $unit->ID === (int) $post->ID
				);
			}
		}
		echo '</div>';
		echo '</div>';
	}
	echo '</div>';
	if ( empty( $floorplans ) ) {
		echo '<div class="rf-hierarchy-navigation-empty">No floor plans</div>';
	}
	echo '</div>';
	echo '</nav>';
}

/**
 * Render hierarchy navigation before the edit-screen heading.
 *
 * @return void
 */
function rentfetch_render_hierarchy_navigation_at_top() {
	$screen = get_current_screen();
	global $post;
	$allowed_types = array( 'properties', 'floorplans', 'units' );

	if (
		! $screen ||
		! in_array( $screen->base, array( 'post', 'post-new' ), true ) ||
		! in_array( $screen->post_type, $allowed_types, true ) ||
		! $post ||
		! $post->ID
	) {
		return;
	}

	echo '<div class="rf-hierarchy-navigation-top">';
	rentfetch_render_hierarchy_navigation( $post, $screen->post_type );
	echo '</div>';
}
add_action( 'in_admin_header', 'rentfetch_render_hierarchy_navigation_at_top' );

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
	$property_tooltip   = rentfetch_get_hierarchy_tooltip_for_post( $property_post->ID );
	$property_highlight = 'properties' === $current_type ? ' highlighted' : '';

	echo '<div class="rentfetch-hierarchy">';
	echo '<div class="property-container">';
	echo '<div class="property-info-content">';
	echo '<a href="' . esc_url( $property_url ) . '" class="' . esc_attr( $property_sync ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $property_post->post_title ) . '</a> · ';
	echo 'Property ID: ' . esc_html( get_post_meta( $property_post->ID, 'property_id', true ) );
	echo '</div>';
	echo '<a href="' . esc_url( get_edit_post_link( $property_post->ID ) ) . '" class="hierarchy-property-info' . esc_attr( $property_highlight . ' ' . $property_sync ) . '" data-post-id="' . absint( $property_post->ID ) . '" data-tooltip="' . esc_attr( $property_tooltip ) . '" data-rf-debug-navigation></a>';
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
			$tooltip         = rentfetch_get_hierarchy_tooltip_for_post( $floorplan->ID );
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
			echo '<a href="' . esc_url( get_edit_post_link( $floorplan->ID ) ) . '" class="hierarchy-item floorplan' . esc_attr( $highlight . ' ' . $sync_class ) . '" data-post-id="' . absint( $floorplan->ID ) . '" data-tooltip="' . esc_attr( $tooltip ) . '" data-rf-debug-navigation></a>';

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
