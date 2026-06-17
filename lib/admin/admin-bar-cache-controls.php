<?php
/**
 * Admin bar controls for Rent Fetch cache settings.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Parse a sync timestamp for admin bar summaries.
 *
 * @param mixed $raw_value Stored timestamp value.
 * @return int
 */
function rentfetch_admin_bar_parse_sync_timestamp( $raw_value ) {
	if ( function_exists( 'rentfetch_parse_sync_timestamp' ) ) {
		return rentfetch_parse_sync_timestamp( $raw_value );
	}

	if ( empty( $raw_value ) ) {
		return 0;
	}

	if ( is_numeric( $raw_value ) ) {
		return (int) $raw_value;
	}

	$timestamp = strtotime( (string) $raw_value );

	return false === $timestamp ? 0 : $timestamp;
}

/**
 * Get endpoint-level sync status for an admin bar record.
 *
 * @param int $post_id The post ID.
 * @return array
 */
function rentfetch_admin_bar_get_endpoint_sync_status( $post_id ) {
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
 * Determine whether an endpoint should count as partial in aggregate summaries.
 *
 * @param string $endpoint Endpoint key.
 * @return bool
 */
function rentfetch_admin_bar_is_partial_sync_endpoint( $endpoint ) {
	return in_array(
		(string) $endpoint,
		array(
			'property_images_api',
			'lease_fees_api',
		),
		true
	);
}

/**
 * Derive a dropdown sync state from endpoint-level data when possible.
 *
 * @param array $row Database row.
 * @return string
 */
function rentfetch_admin_bar_get_row_sync_state( $row ) {
	$post_id = isset( $row['ID'] ) ? (int) $row['ID'] : 0;

	if ( $post_id > 0 ) {
		$sync_status = rentfetch_admin_bar_get_endpoint_sync_status( $post_id );

		if ( ! empty( $sync_status ) ) {
			$has_success = false;
			$has_partial = false;
			$has_failed  = false;

			foreach ( $sync_status as $endpoint => $endpoint_state ) {
				if ( ! is_array( $endpoint_state ) ) {
					continue;
				}

				$state = isset( $endpoint_state['state'] ) ? (string) $endpoint_state['state'] : '';

				if ( 'failed' === $state ) {
					if ( rentfetch_admin_bar_is_partial_sync_endpoint( $endpoint ) ) {
						$has_partial = true;
					} else {
						$has_failed = true;
					}
				} elseif ( 'partial' === $state ) {
					$has_partial = true;
				} elseif ( 'success' === $state ) {
					$has_success = true;
				}
			}

			if ( $has_failed ) {
				return 'failed';
			}

			if ( $has_partial ) {
				return 'partial';
			}

			if ( $has_success ) {
				return 'success';
			}
		}
	}

	return isset( $row['sync_state'] ) ? (string) $row['sync_state'] : '';
}

/**
 * Clear cached admin bar content summaries.
 *
 * @return void
 */
function rentfetch_clear_admin_bar_content_summary_cache() {
	foreach ( range( 1, 6 ) as $version ) {
		delete_transient( 'rentfetch_admin_bar_content_summary_v' . $version );
	}
}

/**
 * Clear the admin bar summary when synced content changes.
 *
 * @param int $post_id The post ID.
 * @return void
 */
function rentfetch_clear_admin_bar_content_summary_cache_for_post( $post_id ) {
	$post_type = get_post_type( $post_id );

	if ( ! in_array( $post_type, array( 'properties', 'floorplans', 'units' ), true ) ) {
		return;
	}

	rentfetch_clear_admin_bar_content_summary_cache();
}

add_action( 'save_post', 'rentfetch_clear_admin_bar_content_summary_cache_for_post', 10, 1 );
add_action( 'trashed_post', 'rentfetch_clear_admin_bar_content_summary_cache_for_post', 10, 1 );
add_action( 'untrashed_post', 'rentfetch_clear_admin_bar_content_summary_cache_for_post', 10, 1 );
add_action( 'deleted_post', 'rentfetch_clear_admin_bar_content_summary_cache_for_post', 10, 1 );

/**
 * Get a lightweight content and sync summary for the admin bar.
 *
 * @return array
 */
function rentfetch_get_admin_bar_content_summary() {
	$cached = get_transient( 'rentfetch_admin_bar_content_summary_v6' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;

	$counts = array(
		'properties' => 0,
		'floorplans' => 0,
		'units'      => 0,
	);

	foreach ( array_keys( $counts ) as $post_type ) {
		$post_counts          = wp_count_posts( $post_type );
		$counts[ $post_type ] = isset( $post_counts->publish ) ? (int) $post_counts->publish : 0;
	}

	$sync_by_type = array();
	foreach ( array_keys( $counts ) as $post_type ) {
		$sync_by_type[ $post_type ] = array(
			'status'  => 'gray',
			'label'   => 'No sync data',
			'current' => 0,
			'aging'   => 0,
			'stale'   => 0,
			'failed'  => 0,
			'partial' => 0,
			'unknown' => 0,
			'total'   => 0,
		);
	}

	$content_rows = array(
		'properties' => array(
			'post_type' => 'properties',
			'count'     => $counts['properties'],
			'label'     => 'properties',
		),
		'floorplans' => array(
			'post_type' => 'floorplans',
			'count'     => $counts['floorplans'],
			'label'     => 'floor plans',
		),
		'units'      => array(
			'post_type' => 'units',
			'count'     => $counts['units'],
			'label'     => 'units',
		),
	);

	if ( isset( $wpdb ) && (int) array_sum( $counts ) > 0 ) {
		$post_types = array( 'properties', 'floorplans', 'units' );
		$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID,
					p.post_type,
					MAX(sync_state.meta_value) AS sync_state,
					MAX(last_synced.meta_value) AS last_synced_at,
					MAX(last_attempt.meta_value) AS last_attempt_at,
					MAX(legacy_updated.meta_value) AS legacy_updated
				FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} sync_state
					ON sync_state.post_id = p.ID AND sync_state.meta_key = 'last_sync_state'
				LEFT JOIN {$wpdb->postmeta} last_synced
					ON last_synced.post_id = p.ID AND last_synced.meta_key = 'last_synced_at'
				LEFT JOIN {$wpdb->postmeta} last_attempt
					ON last_attempt.post_id = p.ID AND last_attempt.meta_key = 'last_sync_attempt_at'
				LEFT JOIN {$wpdb->postmeta} legacy_updated
					ON legacy_updated.post_id = p.ID AND legacy_updated.meta_key = 'updated'
				WHERE p.post_status = 'publish'
					AND p.post_type IN ({$placeholders})
				GROUP BY p.ID",
				$post_types
			),
			ARRAY_A
		);

		$now = current_time( 'timestamp' );

		foreach ( (array) $rows as $row ) {
			$post_type = isset( $row['post_type'] ) ? (string) $row['post_type'] : '';
			if ( ! isset( $sync_by_type[ $post_type ] ) ) {
				continue;
			}

			++$sync_by_type[ $post_type ]['total'];

			$state = rentfetch_admin_bar_get_row_sync_state( $row );
			if ( 'failed' === $state ) {
				++$sync_by_type[ $post_type ]['failed'];
				continue;
			}

			if ( 'partial' === $state ) {
				++$sync_by_type[ $post_type ]['partial'];
				continue;
			}

			if ( 'never' === $state ) {
				++$sync_by_type[ $post_type ]['unknown'];
				continue;
			}

			$timestamp = rentfetch_admin_bar_parse_sync_timestamp( $row['last_synced_at'] ?? '' );
			if ( $timestamp <= 0 ) {
				$timestamp = rentfetch_admin_bar_parse_sync_timestamp( $row['last_attempt_at'] ?? '' );
			}
			if ( $timestamp <= 0 ) {
				$timestamp = rentfetch_admin_bar_parse_sync_timestamp( $row['legacy_updated'] ?? '' );
			}

			if ( $timestamp <= 0 ) {
				++$sync_by_type[ $post_type ]['unknown'];
				continue;
			}

			$hours_diff = ( $now - $timestamp ) / HOUR_IN_SECONDS;
			if ( $hours_diff <= 24 ) {
				++$sync_by_type[ $post_type ]['current'];
			} elseif ( $hours_diff <= 72 ) {
				++$sync_by_type[ $post_type ]['aging'];
			} else {
				++$sync_by_type[ $post_type ]['stale'];
			}
		}
	}

	foreach ( $sync_by_type as $post_type => $sync ) {
		$summary = rentfetch_get_admin_bar_sync_summary_label( $sync );

		if ( 'nosync' === get_option( 'rentfetch_options_data_sync' ) ) {
			$sync['status'] = 'gray';
			$sync['label']  = 'Sync off';
		} elseif ( (int) ( $sync['failed'] ?? 0 ) > 0 ) {
			$sync['status'] = 'red';
			$sync['label']  = $summary;
		} elseif ( (int) ( $sync['partial'] ?? 0 ) > 0 ) {
			$sync['status'] = 'orange';
			$sync['label']  = $summary;
		} elseif ( (int) ( $sync['stale'] ?? 0 ) > 0 || (int) ( $sync['aging'] ?? 0 ) > 0 ) {
			$sync['status'] = 'yellow';
			$sync['label']  = $summary;
		} elseif ( (int) ( $sync['current'] ?? 0 ) > 0 && 0 === (int) ( $sync['unknown'] ?? 0 ) ) {
			$sync['status'] = 'green';
			$sync['label']  = 'Current';
		} elseif ( (int) ( $sync['unknown'] ?? 0 ) > 0 ) {
			$sync['status'] = (int) ( $sync['current'] ?? 0 ) > 0 ? 'yellow' : 'gray';
			$sync['label']  = $summary;
		}

		$sync_by_type[ $post_type ] = $sync;
		if ( isset( $content_rows[ $post_type ] ) ) {
			$content_rows[ $post_type ]['sync'] = $sync;
		}
	}

	$summary = array(
		'counts'        => $counts,
		'content_rows'  => array_values( $content_rows ),
		'sync_by_type'  => $sync_by_type,
	);

	set_transient( 'rentfetch_admin_bar_content_summary_v6', $summary, MINUTE_IN_SECONDS );

	return $summary;
}

/**
 * Build a compact sync summary label for the admin bar.
 *
 * @param array $sync Sync counts.
 * @return string
 */
function rentfetch_get_admin_bar_sync_summary_label( $sync ) {
	$parts = array();
	$labels = array(
		'failed'  => 'failed',
		'partial' => 'partial',
		'stale'   => 'dated',
		'aging'   => 'aging',
		'unknown' => 'unknown',
	);

	foreach ( $labels as $key => $label ) {
		$count = isset( $sync[ $key ] ) ? (int) $sync[ $key ] : 0;
		if ( $count <= 0 ) {
			continue;
		}

		$parts[] = sprintf( '%s %s', number_format_i18n( $count ), $label );
	}

	if ( ! empty( $parts ) ) {
		return implode( ', ', $parts );
	}

	if ( isset( $sync['current'] ) && (int) $sync['current'] > 0 ) {
		return 'Current';
	}

	return 'No sync data';
}

/**
 * Get lightweight cache counts for the admin bar UI.
 *
 * @return array
 */
function rentfetch_get_admin_bar_cache_counts() {
	$registry = get_option( 'rentfetch_search_query_cache_registry', array() );
	if ( ! is_array( $registry ) ) {
		$registry = array();
	}

	$counts = array(
		'total'          => 0,
		'html'           => 0,
		'query'          => 0,
		'preload'        => 0,
		'limit'          => (int) apply_filters( 'rentfetch_search_query_cache_limit', RENTFETCH_SEARCH_QUERY_CACHE_LIMIT ),
		'summary_label'  => '',
		'details_label'  => '',
	);
	$now    = time();
	$ttl    = defined( 'RENTFETCH_CACHE_TTL' ) ? (int) RENTFETCH_CACHE_TTL : DAY_IN_SECONDS;

	foreach ( $registry as $key => $entry ) {
		$last_set_at = isset( $entry['last_set_at'] ) ? (int) $entry['last_set_at'] : 0;
		if ( $last_set_at > 0 && ( $now - $last_set_at ) > $ttl ) {
			continue;
		}

		$family = function_exists( 'rentfetch_get_search_query_cache_family' ) ? rentfetch_get_search_query_cache_family( $key ) : 'other';
		if ( ! in_array( $family, array( 'html', 'query' ), true ) ) {
			continue;
		}

		++$counts['total'];
		++$counts[ $family ];

		if ( ! empty( $entry['priority'] ) ) {
			++$counts['preload'];
		}
	}

	$counts['summary_label'] = sprintf(
		'%s cached result(s)',
		number_format_i18n( $counts['total'] )
	);
	$counts['details_label'] = sprintf(
		'HTML %1$d | Data %2$d',
		$counts['html'],
		$counts['query']
	);

	return $counts;
}

/**
 * Get cache states for the admin bar UI.
 *
 * @return array
 */
function rentfetch_get_admin_bar_cache_states() {
	$counts = rentfetch_get_admin_bar_cache_counts();
	$states = array(
		'query_caching' => get_option( 'rentfetch_options_disable_query_caching', '1' ) !== '1',
		'auto_preload'  => get_option( 'rentfetch_options_enable_cache_warming', '0' ) === '1',
		'counts'        => $counts,
	);

	$lock_started   = (int) get_option( 'rentfetch_cache_warming_lock', 0 );
	$lock_is_active = $lock_started > 0 && ( time() - $lock_started ) <= 10 * MINUTE_IN_SECONDS;
	$has_preload    = $counts['preload'] > 0;

	if ( ! $states['query_caching'] ) {
		$states['status']       = 'red';
		$states['status_label'] = 'Search caching is off';
	} elseif ( $states['auto_preload'] && $has_preload && ! $lock_is_active ) {
		$states['status']       = 'green';
		$states['status_label'] = 'Search caching is on and preload is ready';
	} else {
		$states['status']       = 'yellow';
		$states['status_label'] = 'Search caching is on; preload is unavailable or running';
	}

	return $states;
}

/**
 * Build a section heading for the admin bar dropdown.
 *
 * @param string $label Section label.
 * @param string $dot_status Optional dot status.
 * @param string $dot_label Optional dot label.
 * @return string
 */
function rentfetch_get_admin_bar_section_title( $label, $dot_status = '', $dot_label = '' ) {
	$dot = '';
	if ( '' !== $dot_status ) {
		$dot = sprintf(
			'<span class="rentfetch-cache-dot is-%s" title="%s" aria-label="%s"></span>',
			esc_attr( $dot_status ),
			esc_attr( $dot_label ),
			esc_attr( $dot_label )
		);
	}

	return sprintf(
		'<span class="rentfetch-admin-bar-section-title">%s%s</span>',
		esc_html( strtoupper( $label ) ),
		$dot
	);
}

/**
 * Build an admin bar content row with sync summary.
 *
 * @param array $row Content row.
 * @return string
 */
function rentfetch_get_admin_bar_content_row_title( $row ) {
	$post_type = isset( $row['post_type'] ) ? sanitize_key( $row['post_type'] ) : '';
	$sync = isset( $row['sync'] ) && is_array( $row['sync'] ) ? $row['sync'] : array(
		'status' => 'gray',
		'label'  => 'No sync data',
	);
	$sync_label = (string) ( $sync['label'] ?? 'No sync data' );
	$sync_status = (string) ( $sync['status'] ?? 'gray' );
	$count       = (int) ( $row['count'] ?? 0 );
	$label       = (string) ( $row['label'] ?? '' );
	$singular_labels = array(
		'properties'  => 'property',
		'floor plans' => 'floor plan',
		'units'       => 'unit',
	);

	if ( 1 === $count && isset( $singular_labels[ $label ] ) ) {
		$label = $singular_labels[ $label ];
	}

	$overview_url = '' !== $post_type ? admin_url( 'edit.php?post_type=' . $post_type ) : '#';
	$status_url   = '' !== $post_type ? add_query_arg( 'rentfetch_sync_status', 'needs_attention', $overview_url ) : '#';
	$sync_markup  = sprintf(
		'<span class="rentfetch-cache-dot is-%1$s" aria-hidden="true"></span><span class="rentfetch-admin-bar-count-sync-label">%2$s</span>',
		esc_attr( $sync_status ),
		esc_html( $sync_label )
	);

	if ( 'green' === $sync_status || 'Sync off' === $sync_label ) {
		$status_markup = sprintf(
			'<span class="rentfetch-admin-bar-count-sync is-static" title="%1$s" aria-label="%1$s">%2$s</span>',
			esc_attr( $sync_label ),
			$sync_markup
		);
	} else {
		$status_markup = sprintf(
			'<a class="rentfetch-admin-bar-count-sync" href="%1$s" title="%2$s" aria-label="%2$s">%3$s</a>',
			esc_url( $status_url ),
			esc_attr( $sync_label ),
			$sync_markup
		);
	}

	return sprintf(
		'<span class="rentfetch-admin-bar-count-line"><a class="rentfetch-admin-bar-count-label-link" href="%1$s"><span class="rentfetch-admin-bar-count-label">%2$s %3$s</span></a>%4$s</span>',
		esc_url( $overview_url ),
		esc_html( number_format_i18n( $count ) ),
		esc_html( $label ),
		$status_markup
	);
}

/**
 * Build the admin bar cache count summary.
 *
 * @param array $counts Cache counts.
 * @return string
 */
function rentfetch_get_admin_bar_status_title( $counts ) {
	return sprintf(
		'<span class="rentfetch-admin-bar-status"><span class="rentfetch-admin-bar-cache-summary">%s</span><span class="rentfetch-admin-bar-cache-details">%s</span><span class="rentfetch-admin-bar-message"></span></span>',
		esc_html( $counts['summary_label'] ),
		esc_html( $counts['details_label'] )
	);
}

/**
 * Build an admin bar toggle label.
 *
 * @param string $label   Toggle label.
 * @param bool   $enabled Whether the toggle is enabled.
 * @return string
 */
function rentfetch_get_admin_bar_toggle_title( $label, $enabled ) {
	return sprintf(
		'<span class="rentfetch-admin-bar-toggle-control %s" aria-hidden="true"><span></span></span><span class="rentfetch-admin-bar-toggle-label">%s</span>',
		$enabled ? 'is-on' : 'is-off',
		esc_html( $label )
	);
}

/**
 * Build the admin bar title.
 *
 * @return string
 */
function rentfetch_get_admin_bar_title() {
	$icon = function_exists( 'rentfetch_get_dashboard_icon_svg' )
		? rentfetch_get_dashboard_icon_svg( '#eb6836' )
		: '';

	return sprintf(
		'<span class="rentfetch-admin-bar-title"><span class="rentfetch-admin-bar-icon" aria-hidden="true">%1$s</span><span class="rentfetch-admin-bar-label">%2$s</span></span>',
		$icon,
		esc_html__( 'RentFetch', 'rentfetch' )
	);
}

/**
 * Build the custom admin bar dropdown panel.
 *
 * @param array $content Content summary.
 * @param array $states Cache states.
 * @param bool  $show_performance Whether performance controls should show.
 * @return string
 */
function rentfetch_get_admin_bar_panel_markup( $content, $states, $show_performance ) {
	$settings_url = admin_url( 'admin.php?page=rentfetch-options&tab=general&section=performance' );
	$sync_panel_is_registered = has_action( 'rentfetch_admin_bar_panel_after_content', 'rfs_render_accelerated_sync_admin_bar_panel' );

	ob_start();
	?>
	<div class="rentfetch-admin-bar-panel" role="menu" aria-label="<?php esc_attr_e( 'RentFetch', 'rentfetch' ); ?>">
		<div id="wp-admin-bar-rentfetch-content-section" class="rentfetch-admin-bar-section">
			<?php echo wp_kses_post( rentfetch_get_admin_bar_section_title( 'Content' ) ); ?>
		</div>

		<?php if ( isset( $content['content_rows'] ) && is_array( $content['content_rows'] ) ) : ?>
			<?php foreach ( $content['content_rows'] as $row ) : ?>
				<?php
				$post_type = isset( $row['post_type'] ) ? sanitize_key( $row['post_type'] ) : '';
				if ( '' === $post_type ) {
					continue;
				}
				?>
				<div
					id="<?php echo esc_attr( 'wp-admin-bar-rentfetch-content-' . $post_type ); ?>"
					class="rentfetch-admin-bar-row rentfetch-admin-bar-content-row"
				>
					<?php echo wp_kses_post( rentfetch_get_admin_bar_content_row_title( $row ) ); ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php do_action( 'rentfetch_admin_bar_panel_after_content', $content ); ?>

		<?php if ( ! $sync_panel_is_registered && function_exists( 'rfs_get_accelerated_sync_status_title' ) ) : ?>
			<?php $sync_status_title = rfs_get_accelerated_sync_status_title(); ?>
			<?php
			$sync_status_is_empty = '' === $sync_status_title;
			if ( '' === $sync_status_title ) {
				$sync_status_title = '<span class="rfs-accelerated-sync-status is-empty"><span class="rfs-accelerated-sync-message"></span><span class="rfs-accelerated-sync-current"></span></span>';
			}
			?>
			<div id="wp-admin-bar-rentfetch-sync-section" class="rentfetch-admin-bar-section rentfetch-admin-bar-sync-section">
				<?php echo wp_kses_post( rentfetch_get_admin_bar_section_title( 'Sync' ) ); ?>
			</div>
			<div id="wp-admin-bar-rentfetch-sync-status" class="<?php echo esc_attr( $sync_status_is_empty ? 'rentfetch-admin-bar-sync-status is-empty' : 'rentfetch-admin-bar-sync-status' ); ?>">
				<?php echo wp_kses_post( $sync_status_title ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $show_performance ) : ?>
			<div id="wp-admin-bar-rentfetch-performance-section" class="rentfetch-admin-bar-section rentfetch-admin-bar-performance-section">
				<?php echo wp_kses_post( rentfetch_get_admin_bar_section_title( 'Performance' ) ); ?>
			</div>

			<div id="wp-admin-bar-rentfetch-cache-status" class="rentfetch-admin-bar-status-wrap">
				<?php echo wp_kses_post( rentfetch_get_admin_bar_status_title( $states['counts'] ) ); ?>
			</div>

			<button type="button" id="wp-admin-bar-rentfetch-cache-flush" class="rentfetch-admin-bar-action rentfetch-admin-bar-link-action">Clear search cache</button>
			<button type="button" id="wp-admin-bar-rentfetch-cache-preload" class="rentfetch-admin-bar-action rentfetch-admin-bar-link-action">Preload popular searches</button>

			<button type="button" id="wp-admin-bar-rentfetch-cache-toggle-query" class="rentfetch-admin-bar-action rentfetch-admin-bar-toggle">
				<?php echo wp_kses_post( rentfetch_get_admin_bar_toggle_title( 'Search caching', $states['query_caching'] ) ); ?>
			</button>

			<button type="button" id="wp-admin-bar-rentfetch-cache-toggle-preload" class="rentfetch-admin-bar-action rentfetch-admin-bar-toggle">
				<?php echo wp_kses_post( rentfetch_get_admin_bar_toggle_title( 'Automatic preload', $states['auto_preload'] ) ); ?>
			</button>

			<a id="wp-admin-bar-rentfetch-cache-settings" class="rentfetch-admin-bar-settings-link" href="<?php echo esc_url( $settings_url ); ?>">Performance settings</a>
		<?php endif; ?>
	</div>
	<?php

	return trim( ob_get_clean() );
}

/**
 * Add Rent Fetch cache controls to the admin bar.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 * @return void
 */
function rentfetch_add_admin_bar_cache_controls( $wp_admin_bar ) {
	if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$wp_admin_bar->add_node(
		array(
			'id'    => 'rentfetch-admin-bar',
			'title' => rentfetch_get_admin_bar_title(),
			'href'  => admin_url( 'admin.php?page=rentfetch-options' ),
			'meta'  => array(
				'class' => 'rentfetch-admin-bar',
			),
		)
	);
}
add_action( 'admin_bar_menu', 'rentfetch_add_admin_bar_cache_controls', 90 );

/**
 * Toggle a cache option from the admin bar.
 *
 * @return void
 */
function rentfetch_ajax_toggle_cache_option() {
	check_ajax_referer( 'rentfetch_admin_bar_cache', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error(
			array(
				'message' => 'You do not have permission to update cache settings.',
			)
		);
	}

	$option = isset( $_POST['option'] ) ? sanitize_key( wp_unslash( $_POST['option'] ) ) : '';

	if ( 'query_caching' === $option ) {
		$is_enabled = get_option( 'rentfetch_options_disable_query_caching', '1' ) !== '1';
		$new_value  = $is_enabled ? '1' : '0';
		update_option( 'rentfetch_options_disable_query_caching', $new_value );

		if ( '1' === $new_value && function_exists( 'rentfetch_clear_search_cache_transients' ) ) {
			rentfetch_clear_search_cache_transients();
		}
	} elseif ( 'auto_preload' === $option ) {
		$is_enabled = get_option( 'rentfetch_options_enable_cache_warming', '0' ) === '1';
		$new_value  = $is_enabled ? '0' : '1';
		update_option( 'rentfetch_options_enable_cache_warming', $new_value );

		if ( function_exists( 'rentfetch_schedule_cache_warming' ) ) {
			rentfetch_schedule_cache_warming();
		}
	} else {
		wp_send_json_error(
			array(
				'message' => 'Unknown cache setting.',
			)
		);
	}

	wp_send_json_success(
		array(
			'message' => 'Cache setting updated.',
			'states'  => rentfetch_get_admin_bar_cache_states(),
		)
	);
}
add_action( 'wp_ajax_rentfetch_toggle_cache_option', 'rentfetch_ajax_toggle_cache_option' );

/**
 * Print admin bar cache control styles.
 *
 * @return void
 */
function rentfetch_admin_bar_cache_controls_styles() {
	if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<style>
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar > .ab-item {
			display: flex;
			align-items: center;
			gap: 5px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-title {
			display: inline-flex;
			align-items: center;
			gap: 5px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-icon {
			display: inline-flex;
			width: 14px;
			height: 15px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-icon svg {
			display: block;
			width: 14px;
			height: 15px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar {
			position: relative;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel {
			background: #1d2327;
			box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
			box-sizing: border-box;
			display: none;
			left: 0;
			min-width: 240px;
			max-width: 280px;
			padding: 10px 12px 12px;
			position: absolute;
			top: 100%;
			z-index: 99999;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar:hover .rentfetch-admin-bar-panel,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar:focus-within .rentfetch-admin-bar-panel {
			display: block;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel a,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel button,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel div,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel span {
			box-sizing: border-box;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel a,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel button {
			background: transparent;
			border: 0;
			box-shadow: none;
			color: #a7aaad;
			cursor: pointer;
			display: block;
			font: inherit;
			height: auto;
			margin: 0;
			min-width: 220px;
			padding: 0;
			text-align: left;
			white-space: normal;
			width: 100%;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel a:hover,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel a:focus,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel button:hover,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel button:focus {
			background: transparent;
			color: inherit;
			outline: none;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-cache-dot {
			border-radius: 50%;
			display: inline-block;
			height: 8px;
			margin-left: 5px;
			vertical-align: 1px;
			width: 8px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-cache-dot.is-green {
			background: #00a32a;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-cache-dot.is-yellow {
			background: #dba617;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-cache-dot.is-orange {
			background: #d9822b;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-cache-dot.is-red {
			background: #d63638;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-cache-dot.is-gray {
			background: #8c8f94;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-section {
			background: transparent;
			color: #fff;
			cursor: default;
			font-size: 10px;
			font-weight: 700;
			height: auto;
			letter-spacing: 0.08em;
			line-height: 1.25;
			padding-bottom: 6px;
			padding-top: 8px;
			text-transform: uppercase;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-section-title {
			align-items: center;
			color: #fff !important;
			display: flex;
			font-size: 10px !important;
			font-weight: 700 !important;
			gap: 8px;
			letter-spacing: 0.08em !important;
			line-height: 1.25 !important;
			text-transform: uppercase !important;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-section-title::after {
			background: rgba(255, 255, 255, 0.16);
			content: "";
			display: block;
			flex: 1 1 auto;
			height: 1px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-content-section .ab-item {
			padding-top: 8px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-performance-section .ab-item {
			padding-bottom: 6px;
			padding-top: 10px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-cache-status {
			color: #a7aaad;
			cursor: default;
			font-size: 11px;
			height: auto;
			line-height: 1.25;
			min-width: 220px;
			padding-bottom: 7px;
			padding-top: 0;
			white-space: normal;
		}

		#wpadminbar #wp-admin-bar-rentfetch-sync-status.is-empty {
			display: none;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-content-row {
			color: #a7aaad;
			font-size: 10px;
			height: auto;
			line-height: 1.2;
			min-width: 220px;
			padding-bottom: 1px;
			padding-top: 0;
			white-space: normal;
			width: 100%;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-content-row:hover,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-content-row:focus {
			color: #a7aaad;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-status {
			display: block;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-cache-summary {
			color: #dcdcde;
			display: block;
			font-size: 12px;
			line-height: 1.25;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-count-line {
			display: grid;
			grid-template-columns: minmax(0, 1fr);
			row-gap: 1px;
			width: 100%;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel a.rentfetch-admin-bar-count-label-link {
			border-radius: 2px;
			color: #a7aaad;
			display: inline-block;
			line-height: 1.15;
			max-width: 100%;
			min-width: 0;
			padding: 0 2px;
			width: auto;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel a.rentfetch-admin-bar-count-label-link:hover,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel a.rentfetch-admin-bar-count-label-link:focus {
			background: transparent;
			color: #dcdcde;
			text-decoration: underline;
			text-underline-offset: 2px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-count-label {
			display: block;
			line-height: 1.25;
			white-space: nowrap;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-count-sync {
			align-items: center;
			border-radius: 2px;
			color: #a7aaad;
			display: inline-flex;
			flex-wrap: nowrap;
			font-size: 9px;
			gap: 4px;
			line-height: 1.05;
			margin-top: 0;
			max-width: 100%;
			min-width: 0;
			overflow-wrap: anywhere;
			padding: 0 2px 0 10px;
			text-align: left;
			white-space: normal;
			width: fit-content;
			word-break: normal;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel a.rentfetch-admin-bar-count-sync {
			align-items: center;
			display: inline-flex;
			min-width: 0;
			padding: 0 2px 0 10px;
			width: fit-content;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-count-sync-label {
			min-width: 0;
			overflow-wrap: anywhere;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel a.rentfetch-admin-bar-count-sync .rentfetch-admin-bar-count-sync-label {
			text-decoration: underline;
			text-underline-offset: 2px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-count-sync.is-static {
			cursor: default;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel a.rentfetch-admin-bar-count-sync:hover,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel a.rentfetch-admin-bar-count-sync:focus {
			background: transparent;
			color: #dcdcde;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-count-sync .rentfetch-cache-dot {
			flex: 0 0 auto;
			height: 7px;
			margin-left: 0;
			vertical-align: middle;
			width: 7px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-cache-details {
			display: block;
			font-size: 11px;
			line-height: 1.25;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-message {
			display: none;
			font-size: 11px;
			line-height: 1.4;
			padding-top: 3px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-message:not(:empty) {
			display: block;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-panel button.rentfetch-admin-bar-toggle {
			align-items: center;
			display: flex;
			font-size: 12px;
			gap: 8px;
			justify-content: flex-start;
			line-height: 16px;
			min-height: 20px;
			min-width: 220px;
			padding: 2px 0;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-toggle-label {
			display: block;
			line-height: 16px;
			padding-right: 0;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-toggle-control {
			background: #646970;
			border-radius: 999px;
			display: block;
			flex: 0 0 auto;
			height: 14px;
			position: relative;
			transition: background 140ms ease;
			width: 26px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-toggle-control span {
			background: #fff;
			border-radius: 50%;
			height: 10px;
			left: 2px;
			position: absolute;
			top: 2px;
			transition: transform 140ms ease;
			width: 10px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-toggle-control.is-on {
			background: #00a32a;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-toggle-control.is-on span {
			transform: translateX(12px);
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-link-action {
			color: #dcdcde;
			font-size: 12px;
			line-height: 1.35;
			margin-top: 5px;
			text-decoration: underline;
			text-underline-offset: 2px;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-link-action:hover,
		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-link-action:focus {
			color: #dcdcde;
			text-decoration: underline;
		}

		#wpadminbar #wp-admin-bar-rentfetch-cache-settings.rentfetch-admin-bar-settings-link {
			color: #8c8f94;
			display: inline-block;
			font-size: 11px;
			margin-top: 5px;
			min-width: 0;
			text-decoration: underline;
			text-underline-offset: 2px;
			width: auto;
		}

		#wpadminbar #wp-admin-bar-rentfetch-cache-settings.rentfetch-admin-bar-settings-link:hover,
		#wpadminbar #wp-admin-bar-rentfetch-cache-settings.rentfetch-admin-bar-settings-link:focus {
			color: #8c8f94;
			text-decoration: underline;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-status.is-success {
			color: #a7aaad;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-status.is-success .rentfetch-admin-bar-message {
			color: #72aee6;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-status.is-error {
			color: #a7aaad;
		}

		#wpadminbar #wp-admin-bar-rentfetch-admin-bar .rentfetch-admin-bar-status.is-error .rentfetch-admin-bar-message {
			color: #f86368;
		}
	</style>
	<?php
}
add_action( 'admin_head', 'rentfetch_admin_bar_cache_controls_styles' );
add_action( 'wp_head', 'rentfetch_admin_bar_cache_controls_styles' );

/**
 * Print admin bar cache control script.
 *
 * @return void
 */
function rentfetch_admin_bar_cache_controls_script() {
	if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$config = array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonces'  => array(
			'rentfetch_clear_search_cache' => wp_create_nonce( 'rentfetch_clear_cache' ),
			'rentfetch_warm_cache'         => wp_create_nonce( 'rentfetch_warm_cache' ),
			'rentfetch_toggle_cache_option' => wp_create_nonce( 'rentfetch_admin_bar_cache' ),
		),
	);
	$content          = rentfetch_get_admin_bar_content_summary();
	$show_performance = true;
	$states           = rentfetch_get_admin_bar_cache_states();
	?>
	<template id="rentfetch-admin-bar-panel-template">
		<?php echo rentfetch_get_admin_bar_panel_markup( $content, $states, $show_performance ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</template>
	<script>
	(function() {
		var config = <?php echo wp_json_encode( $config ); ?>;

		function mountPanel() {
			var host = document.querySelector('#wp-admin-bar-rentfetch-admin-bar');
			var template = document.querySelector('#rentfetch-admin-bar-panel-template');

			if (!host || !template || host.querySelector('.rentfetch-admin-bar-panel')) {
				return;
			}

			host.appendChild(template.content.firstElementChild.cloneNode(true));
		}

		mountPanel();

		function getStatusElement() {
			return document.querySelector('#wp-admin-bar-rentfetch-cache-status .rentfetch-admin-bar-status');
		}

		function setStatus(message, type) {
			var status = getStatusElement();
			var messageElement;
			if (!status) {
				return;
			}

			messageElement = status.querySelector('.rentfetch-admin-bar-message');
			status.classList.remove('is-success', 'is-error');
			if (type) {
				status.classList.add(type === 'error' ? 'is-error' : 'is-success');
			}
			if (messageElement) {
				messageElement.textContent = message;
			}
		}

		function updateStates(states) {
			if (!states) {
				return;
			}

			var query = document.querySelector('#wp-admin-bar-rentfetch-cache-toggle-query');
			var preload = document.querySelector('#wp-admin-bar-rentfetch-cache-toggle-preload');

			updateCounts(states.counts);

			if (query) {
				updateToggle(query, states.query_caching);
			}
			if (preload) {
				updateToggle(preload, states.auto_preload);
			}
		}

		function updateCounts(counts) {
			var summary = document.querySelector('#wp-admin-bar-rentfetch-cache-status .rentfetch-admin-bar-cache-summary');
			var details = document.querySelector('#wp-admin-bar-rentfetch-cache-status .rentfetch-admin-bar-cache-details');

			if (!counts) {
				return;
			}
			if (summary && counts.summary_label) {
				summary.textContent = counts.summary_label;
			}
			if (details && counts.details_label) {
				details.textContent = counts.details_label;
			}
		}

		function updateToggle(item, enabled) {
			var toggle = item.querySelector('.rentfetch-admin-bar-toggle-control');
			if (!toggle) {
				return;
			}

			toggle.classList.toggle('is-on', !!enabled);
			toggle.classList.toggle('is-off', !enabled);
		}

		var actions = {
			'wp-admin-bar-rentfetch-cache-flush': {
				action: 'rentfetch_clear_search_cache',
				working: 'Clearing cache...'
			},
			'wp-admin-bar-rentfetch-cache-preload': {
				action: 'rentfetch_warm_cache',
				working: 'Preloading popular searches...'
			},
			'wp-admin-bar-rentfetch-cache-toggle-query': {
				action: 'rentfetch_toggle_cache_option',
				option: 'query_caching',
				working: 'Updating search caching...'
			},
			'wp-admin-bar-rentfetch-cache-toggle-preload': {
				action: 'rentfetch_toggle_cache_option',
				option: 'auto_preload',
				working: 'Updating automatic preload...'
			}
		};

		function buildActionBody(settings, resetCursor) {
			var body = new window.FormData();

			body.append('action', settings.action);
			body.append('nonce', config.nonces[settings.action]);

			if (settings.option) {
				body.append('option', settings.option);
			}
			if (resetCursor) {
				body.append('reset_cursor', '1');
			}

			return body;
		}

		function sendAction(settings, resetCursor) {
			return window.fetch(config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: buildActionBody(settings, resetCursor)
			}).then(function(response) {
				return response.json();
			});
		}

		function runPreloadCycle(settings) {
			var progress = {
				total: 0,
				processed: 0,
				warmed: 0,
				failed: 0,
				steps: 0
			};

			setStatus('Starting preload...', '');

			function runNext(resetCursor) {
				return sendAction(settings, resetCursor)
					.then(function(response) {
						var data = response && response.data ? response.data : {};
						var batchSize = parseInt(data.batch_size || 0, 10);

						if (!response.success) {
							throw new Error(data.message || 'Preload failed.');
						}

						if (data.states) {
							updateStates(data.states);
						}

						if (!data.total) {
							setStatus(data.message || 'No popular searches found to preload.', 'success');
							return;
						}

						progress.total = parseInt(data.total || progress.total, 10);
						progress.processed += batchSize;
						progress.warmed += parseInt(data.warmed || 0, 10);
						progress.failed += parseInt(data.failed || 0, 10);
						progress.steps += 1;
						if (data.errors && data.errors.length && window.console && window.console.warn) {
							window.console.warn('RentFetch preload failures', data.errors);
						}

						if (progress.processed > progress.total) {
							progress.processed = progress.total;
						}

						if (progress.processed < progress.total && batchSize > 0 && progress.steps < 100) {
							setStatus(
								'Preloaded ' + progress.processed + ' of ' + progress.total + ' popular searches...',
								''
							);
							return runNext(false);
						}

						if (data.states) {
							updateStates(data.states);
						}

						setStatus(
							'Preload complete. ' + progress.warmed + ' loaded' + (progress.failed ? ', ' + progress.failed + ' failed' : '') + '.',
							'success'
						);
					});
			}

			runNext(true).catch(function(error) {
				setStatus(error.message || 'Request failed. Try again.', 'error');
			});
		}

		function postAction(item) {
			var settings = actions[item.id] || {};
			var action = settings.action;
			var nonce = config.nonces[action];
			var body = new window.FormData();

			if (!action || !nonce) {
				return;
			}

			if (action === 'rentfetch_warm_cache') {
				runPreloadCycle(settings);
				return;
			}

			body.append('action', action);
			body.append('nonce', nonce);

			if (settings.option) {
				body.append('option', settings.option);
			}

			setStatus(settings.working || 'Working...', '');

			window.fetch(config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: body
			})
				.then(function(response) {
					return response.json();
				})
				.then(function(response) {
					var data = response && response.data ? response.data : {};
					var message = data.message || (response.success ? 'Done.' : 'Request failed.');

					if (response.success) {
						setStatus(message, 'success');
						if (data.states) {
							updateStates(data.states);
						}
					} else {
						setStatus(message, 'error');
					}
				})
				.catch(function() {
					setStatus('Request failed. Try again.', 'error');
				});
		}

		document.addEventListener('click', function(event) {
			var link = event.target.closest('#wpadminbar .rentfetch-admin-bar-action');
			if (!link) {
				return;
			}

			event.preventDefault();
			postAction(link);
		});
	}());
	</script>
	<?php
}
add_action( 'admin_footer', 'rentfetch_admin_bar_cache_controls_script' );
add_action( 'wp_footer', 'rentfetch_admin_bar_cache_controls_script' );
