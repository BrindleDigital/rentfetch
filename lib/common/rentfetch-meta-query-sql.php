<?php
/**
 * SQL generation utilities for nested meta queries.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'rentfetch_build_meta_query_sql' ) ) {
	/**
	 * Build SQL JOIN and WHERE fragments from a meta query.
	 *
	 * @param array  $meta_query Meta query clauses.
	 * @param wpdb   $wpdb       WordPress database object.
	 * @param string $post_table Posts table property name.
	 * @param int    $join_count  Join alias counter, passed by reference.
	 * @return array SQL join and where fragments.
	 */
	function rentfetch_build_meta_query_sql( $meta_query, $wpdb, $post_table = 'posts', &$join_count = 0 ) {
		$allowed_post_tables = array(
			'posts' => $wpdb->posts,
		);
		$join                = array();
		$where               = array();
		$relation            = 'AND';
		$post_table_sql      = isset( $allowed_post_tables[ $post_table ] ) ? $allowed_post_tables[ $post_table ] : $wpdb->posts;
		if ( isset( $meta_query['relation'] ) ) {
			$relation = 'OR' === strtoupper( (string) $meta_query['relation'] ) ? 'OR' : 'AND';
			unset( $meta_query['relation'] );
		}
		foreach ( $meta_query as $mq ) {
			if ( isset( $mq['relation'] ) || isset( $mq[0] ) ) {
				// Nested meta_query.
				$sub = rentfetch_build_meta_query_sql( $mq, $wpdb, $post_table, $join_count );
				if ( $sub['join'] ) {
					$join = array_merge( $join, $sub['join'] );
				}
				if ( $sub['where'] ) {
					$where[] = '(' . $sub['where'] . ')';
				}
			} elseif ( is_array( $mq ) && isset( $mq['key'] ) ) {
				// The alias, comparison operator, and placeholder list below are generated from allow-listed values.
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Dynamic SQL structure is allow-listed; all data values use wpdb placeholders.
				++$join_count;
				$alias    = 'pm' . $join_count;
				$join[]   = "INNER JOIN {$wpdb->postmeta} $alias ON $alias.post_id = {$post_table_sql}.ID";
				$key      = (string) $mq['key'];
				$compare  = isset( $mq['compare'] ) ? strtoupper( (string) $mq['compare'] ) : null;
				$value    = isset( $mq['value'] ) ? $mq['value'] : '';
				$type     = isset( $mq['type'] ) ? strtolower( (string) $mq['type'] ) : '';
				$meta_val = ( 'numeric' === $type ) ? "$alias.meta_value+0" : "$alias.meta_value";
				$key_sql  = $wpdb->prepare( "$alias.meta_key = %s", $key );

				// Determine compare if not set and value is array (WP_Query default is IN).
				if ( ! $compare && is_array( $value ) ) {
					$compare = 'IN';
				} elseif ( ! $compare ) {
					$compare = '=';
				}

				// Handle IN, NOT IN, BETWEEN, NOT BETWEEN, and scalar comparisons.
				if ( in_array( $compare, array( 'IN', 'NOT IN' ), true ) && is_array( $value ) ) {
					if ( empty( $value ) ) {
						$where[] = 'NOT IN' === $compare ? '1 = 1' : '1 = 0';
					} else {
						$placeholder  = 'numeric' === $type ? '%f' : '%s';
						$placeholders = implode( ',', array_fill( 0, count( $value ), $placeholder ) );
						$where[]      = $wpdb->prepare( "$key_sql AND $meta_val $compare ($placeholders)", $value );
					}
				} elseif ( in_array( $compare, array( 'BETWEEN', 'NOT BETWEEN' ), true ) && is_array( $value ) && 2 === count( $value ) ) {
					if ( 'numeric' === $type ) {
						$where[] = $wpdb->prepare( "$key_sql AND $meta_val $compare %f AND %f", (float) $value[0], (float) $value[1] );
					} else {
						$where[] = $wpdb->prepare( "$key_sql AND $meta_val $compare %s AND %s", (string) $value[0], (string) $value[1] );
					}
				} elseif ( in_array( $compare, array( '=', '!=', '>', '<', '>=', '<=' ), true ) ) {
					if ( 'numeric' === $type ) {
						$where[] = $wpdb->prepare( "$key_sql AND $meta_val $compare %f", (float) $value );
					} else {
						$where[] = $wpdb->prepare( "$key_sql AND $meta_val $compare %s", (string) $value );
					}
				} else {
					// Fallback: treat as string equality.
					$where[] = $wpdb->prepare( "$key_sql AND $meta_val = %s", (string) $value );
				}
				// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			}
		}
		return array(
			'join'  => $join,
			'where' => $where ? implode( " $relation ", $where ) : '',
		);
	}
}
