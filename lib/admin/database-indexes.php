<?php
/**
 * Database index management for search optimization
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if indexes exist
 *
 * @return array Array with index names as keys and boolean existence as values.
 */
function rentfetch_check_indexes_exist() {
	global $wpdb;

	$indexes = array(
		'idx_meta_key_value' => false,
		'idx_post_meta_key'  => false,
	);

	// Get existing indexes on postmeta table.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$existing_indexes = $wpdb->get_results(
		$wpdb->prepare(
			'SHOW INDEX FROM ' . $wpdb->postmeta . ' WHERE Key_name IN (%s, %s)',
			'idx_meta_key_value',
			'idx_post_meta_key'
		),
		ARRAY_A
	);

	foreach ( $existing_indexes as $index ) {
		$key_name = $index['Key_name'];
		if ( isset( $indexes[ $key_name ] ) ) {
			$indexes[ $key_name ] = true;
		}
	}

	return $indexes;
}

/**
 * Create database indexes for search optimization
 *
 * @return array Array with 'success' boolean and 'message' string.
 */
function rentfetch_create_indexes() {
	global $wpdb;

	// Set longer timeout for large databases.
	set_time_limit( 300 ); // 5 minutes.

	$results = array(
		'success' => true,
		'message' => '',
		'indexes' => array(),
	);

	// Check which indexes already exist.
	$existing = rentfetch_check_indexes_exist();

	// Index 1: meta_key + meta_value composite (most important).
	if ( ! $existing['idx_meta_key_value'] ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$result = $wpdb->query(
			'ALTER TABLE ' . $wpdb->postmeta . ' ADD INDEX idx_meta_key_value (meta_key(20), meta_value(20))'
		);

		if ( false === $result ) {
			$results['success']                       = false;
			$results['indexes']['idx_meta_key_value'] = array(
				'created' => false,
				'error'   => $wpdb->last_error,
			);
		} else {
			$results['indexes']['idx_meta_key_value'] = array(
				'created' => true,
			);
		}
	} else {
		$results['indexes']['idx_meta_key_value'] = array(
			'created'       => false,
			'already_exists' => true,
		);
	}

	// Index 2: post_id + meta_key composite.
	if ( ! $existing['idx_post_meta_key'] ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$result = $wpdb->query(
			'ALTER TABLE ' . $wpdb->postmeta . ' ADD INDEX idx_post_meta_key (post_id, meta_key(20))'
		);

		if ( false === $result ) {
			$results['success']                      = false;
			$results['indexes']['idx_post_meta_key'] = array(
				'created' => false,
				'error'   => $wpdb->last_error,
			);
		} else {
			$results['indexes']['idx_post_meta_key'] = array(
				'created' => true,
			);
		}
	} else {
		$results['indexes']['idx_post_meta_key'] = array(
			'created'       => false,
			'already_exists' => true,
		);
	}

	// Build message.
	$created_count = 0;
	$exists_count  = 0;
	$error_count   = 0;

	foreach ( $results['indexes'] as $index_name => $index_result ) {
		if ( ! empty( $index_result['created'] ) ) {
			++$created_count;
		} elseif ( ! empty( $index_result['already_exists'] ) ) {
			++$exists_count;
		} elseif ( isset( $index_result['error'] ) ) {
			++$error_count;
		}
	}

	if ( $error_count > 0 ) {
		$results['message'] = sprintf(
			'Error creating %d index(es). Please check your database permissions or contact your hosting provider.',
			$error_count
		);
	} elseif ( $created_count > 0 ) {
		$results['message'] = sprintf(
			'Successfully created %d search index(es). Search performance should be improved.',
			$created_count
		);
	} elseif ( $exists_count > 0 ) {
		$results['message'] = 'All search indexes already exist.';
	}

	return $results;
}

/**
 * Remove database indexes
 *
 * @return array Array with 'success' boolean and 'message' string.
 */
function rentfetch_remove_indexes() {
	global $wpdb;

	$results = array(
		'success' => true,
		'message' => '',
		'indexes' => array(),
	);

	// Check which indexes exist.
	$existing = rentfetch_check_indexes_exist();

	// Remove index 1.
	if ( $existing['idx_meta_key_value'] ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$result = $wpdb->query(
			'ALTER TABLE ' . $wpdb->postmeta . ' DROP INDEX idx_meta_key_value'
		);

		if ( false === $result ) {
			$results['success']                       = false;
			$results['indexes']['idx_meta_key_value'] = array(
				'removed' => false,
				'error'   => $wpdb->last_error,
			);
		} else {
			$results['indexes']['idx_meta_key_value'] = array(
				'removed' => true,
			);
		}
	} else {
		$results['indexes']['idx_meta_key_value'] = array(
			'removed'      => false,
			'not_found'    => true,
		);
	}

	// Remove index 2.
	if ( $existing['idx_post_meta_key'] ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$result = $wpdb->query(
			'ALTER TABLE ' . $wpdb->postmeta . ' DROP INDEX idx_post_meta_key'
		);

		if ( false === $result ) {
			$results['success']                      = false;
			$results['indexes']['idx_post_meta_key'] = array(
				'removed' => false,
				'error'   => $wpdb->last_error,
			);
		} else {
			$results['indexes']['idx_post_meta_key'] = array(
				'removed' => true,
			);
		}
	} else {
		$results['indexes']['idx_post_meta_key'] = array(
			'removed'   => false,
			'not_found' => true,
		);
	}

	// Build message.
	$removed_count = 0;
	$not_found_count = 0;
	$error_count   = 0;

	foreach ( $results['indexes'] as $index_name => $index_result ) {
		if ( ! empty( $index_result['removed'] ) ) {
			++$removed_count;
		} elseif ( ! empty( $index_result['not_found'] ) ) {
			++$not_found_count;
		} elseif ( isset( $index_result['error'] ) ) {
			++$error_count;
		}
	}

	if ( $error_count > 0 ) {
		$results['message'] = sprintf(
			'Error removing %d index(es). Please check your database permissions.',
			$error_count
		);
	} elseif ( $removed_count > 0 ) {
		$results['message'] = sprintf(
			'Successfully removed %d search index(es).',
			$removed_count
		);
	} elseif ( $not_found_count > 0 ) {
		$results['message'] = 'No search indexes found to remove.';
	}

	return $results;
}

/**
 * Get current index status for display
 *
 * @return string Status message.
 */
function rentfetch_get_index_status() {
	$existing = rentfetch_check_indexes_exist();

	$total_indexes   = count( $existing );
	$active_indexes  = array_filter( $existing );
	$active_count    = count( $active_indexes );

	if ( $active_count === $total_indexes ) {
		return sprintf(
			'<span style="color: #46b450;">● Active</span> (%d of %d indexes installed)',
			$active_count,
			$total_indexes
		);
	} elseif ( $active_count > 0 ) {
		return sprintf(
			'<span style="color: #f0b849;">◐ Partial</span> (%d of %d indexes installed)',
			$active_count,
			$total_indexes
		);
	} else {
		return sprintf(
			'<span style="color: #dc3232;">○ Not installed</span> (0 of %d indexes)',
			$total_indexes
		);
	}
}

/**
 * Check and create indexes on plugin activation or version update
 */
function rentfetch_maybe_create_indexes_on_activation() {
	// Only run if the option is enabled.
	if ( get_option( 'rentfetch_options_enable_search_indexes', '1' ) !== '1' ) {
		return;
	}

	// Check if we've already run for this version.
	$current_version = get_option( 'rentfetch_search_indexes_version', '0' );
	$plugin_version  = defined( 'RENTFETCH_VERSION' ) ? RENTFETCH_VERSION : '1.0.0';

	// Only run if version changed or never run before.
	if ( version_compare( $current_version, $plugin_version, '<' ) ) {
		$result = rentfetch_create_indexes();

		if ( $result['success'] ) {
			update_option( 'rentfetch_search_indexes_version', $plugin_version );
		}
	}
}
add_action( 'admin_init', 'rentfetch_maybe_create_indexes_on_activation' );
