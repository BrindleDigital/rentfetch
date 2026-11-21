<?php
/** Source-aware relationships, including explicitly attached manual units. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function rentfetch_normalize_record_source( $source ) {
	$source = strtolower( trim( (string) $source ) );
	return 'manual' === $source ? '' : $source;
}

/** Cache only identity fields; unit writes do not require reloading this index. */
function rentfetch_get_relationship_index() {
	global $wpdb, $rentfetch_relationship_index;
	if ( isset( $rentfetch_relationship_index ) ) {
		return $rentfetch_relationship_index;
	}
	$index = array( 'records' => array(), 'properties' => array(), 'properties_source' => array(), 'plans' => array(), 'plans_source' => array() );
	$rows = $wpdb->get_results(
		"SELECT p.ID, p.post_type, p.post_status, m.meta_key, m.meta_value FROM {$wpdb->posts} p
		LEFT JOIN {$wpdb->postmeta} m ON m.post_id=p.ID
		AND m.meta_key IN ('property_id','floorplan_id','property_source','floorplan_source','rentfetch_property_post_id')
		WHERE p.post_type IN ('properties','floorplans') AND p.post_status IN ('publish','draft','private','pending','future') ORDER BY m.meta_id ASC",
		ARRAY_A
	);
	foreach ( $rows as $row ) {
		$id = (int) $row['ID'];
		$index['records'][ $id ]['type'] = $row['post_type'];
		if ( null !== $row['meta_key'] && ! isset( $index['records'][ $id ][ $row['meta_key'] ] ) ) {
			$index['records'][ $id ][ $row['meta_key'] ] = $row['meta_value'];
		}
	}
	foreach ( $index['records'] as $id => $record ) {
		$property = (string) ( $record['property_id'] ?? '' );
		$source = rentfetch_normalize_record_source( $record[ 'properties' === $record['type'] ? 'property_source' : 'floorplan_source' ] ?? '' );
		if ( '' === $property ) { continue; }
		if ( 'properties' === $record['type'] ) {
			$index['properties'][ $property ][] = $id;
			$index['properties_source'][ wp_json_encode( array( $source, $property ) ) ][] = $id;
		} else {
			$plan = (string) ( $record['floorplan_id'] ?? '' );
			$index['plans'][ wp_json_encode( array( $property, $plan ) ) ][] = $id;
			$index['plans_source'][ wp_json_encode( array( $source, $property, $plan ) ) ][] = $id;
		}
	}
	$rentfetch_relationship_index = $index;
	return $index;
}

function rentfetch_clear_relationship_index() {
	unset( $GLOBALS['rentfetch_relationship_index'] );
}
function rentfetch_invalidate_relationship_identity( $meta_id, $post_id, $key ) {
	if ( in_array( $key, array( 'property_id', 'floorplan_id', 'property_source', 'floorplan_source', 'rentfetch_property_post_id' ), true )
		&& in_array( get_post_type( $post_id ), array( 'properties', 'floorplans' ), true ) ) {
		rentfetch_clear_relationship_index();
	}
}
foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta' ) as $hook ) {
	add_action( $hook, 'rentfetch_invalidate_relationship_identity', 10, 3 );
}
add_action( 'save_post_properties', 'rentfetch_clear_relationship_index' );
add_action( 'save_post_floorplans', 'rentfetch_clear_relationship_index' );
add_action( 'deleted_post', 'rentfetch_clear_relationship_index' );
add_action( 'switch_blog', 'rentfetch_clear_relationship_index' );

function rentfetch_get_record_source( $post_id ) {
	$index = rentfetch_get_relationship_index();
	if ( isset( $index['records'][ $post_id ] ) ) {
		$record = $index['records'][ $post_id ];
		return rentfetch_normalize_record_source( $record[ 'properties' === $record['type'] ? 'property_source' : 'floorplan_source' ] ?? '' );
	}
	$type = get_post_type( $post_id );
	$key = array( 'properties' => 'property_source', 'floorplans' => 'floorplan_source', 'units' => 'unit_source' )[ $type ] ?? '';
	$source = $key ? get_post_meta( $post_id, $key, true ) : '';
	if ( 'units' === $type && '' === (string) $source ) {
		$source = get_post_meta( $post_id, 'floorplan_source', true );
	}
	return rentfetch_normalize_record_source( $source );
}

/** Unlinked legacy manual records are resolved only when their IDs are unambiguous. */
function rentfetch_resolve_floorplan_post_id( $unit_id ) {
	if ( 'units' !== get_post_type( $unit_id ) ) { return 0; }
	$property = (string) get_post_meta( $unit_id, 'property_id', true );
	$plan = (string) get_post_meta( $unit_id, 'floorplan_id', true );
	$source = rentfetch_get_record_source( $unit_id );
	$index = rentfetch_get_relationship_index();
	$explicit = (int) get_post_meta( $unit_id, 'rentfetch_floorplan_post_id', true );
	if ( '' === $source && $explicit ) {
		$record = $index['records'][ $explicit ] ?? array();
		return 'floorplans' === ( $record['type'] ?? '' ) && $property === ( $record['property_id'] ?? '' ) && $plan === ( $record['floorplan_id'] ?? '' ) ? $explicit : 0;
	}
	$key = wp_json_encode( '' === $source ? array( $property, $plan ) : array( $source, $property, $plan ) );
	$matches = $index[ '' === $source ? 'plans' : 'plans_source' ][ $key ] ?? array();
	return 1 === count( $matches ) ? $matches[0] : 0;
}

function rentfetch_get_parent_property_post_id( $post_id ) {
	$index = rentfetch_get_relationship_index();
	$type = $index['records'][ $post_id ]['type'] ?? get_post_type( $post_id );
	if ( 'properties' === $type ) { return (int) $post_id; }
	$index = rentfetch_get_relationship_index();
	$record = $index['records'][ $post_id ] ?? array();
	$property = (string) ( $record['property_id'] ?? get_post_meta( $post_id, 'property_id', true ) );
	$source = rentfetch_get_record_source( $post_id );
	if ( 'units' === $type && '' === $source ) {
		$plan = rentfetch_resolve_floorplan_post_id( $post_id );
		return $plan ? rentfetch_get_parent_property_post_id( $plan ) : 0;
	}
	$explicit = '' === $source ? (int) ( $record['rentfetch_property_post_id'] ?? 0 ) : 0;
	if ( $explicit ) {
		$parent = $index['records'][ $explicit ] ?? array();
		return 'properties' === ( $parent['type'] ?? '' ) && $property === ( $parent['property_id'] ?? '' ) ? $explicit : 0;
	}
	$matches = '' === $source ? ( $index['properties'][ $property ] ?? array() )
		: ( $index['properties_source'][ wp_json_encode( array( $source, $property ) ) ] ?? array() );
	return 1 === count( $matches ) ? $matches[0] : 0;
}

/** Preserve legacy aggregate keys unless external property IDs actually collide. */
function rentfetch_get_property_group_key( $property_id, $property_post_id = 0 ) {
	$index = rentfetch_get_relationship_index();
	$owners = $index['properties'][ (string) $property_id ] ?? array();
	if ( count( $owners ) <= 1 ) { return (string) $property_id; }
	return in_array( (int) $property_post_id, $owners, true ) ? 'rf-property:' . (int) $property_post_id : '';
}

/** Include safe manual attachments, without joining another provider's units. */
function rentfetch_get_floorplan_unit_query_args( $floorplan_post_id, $args = array() ) {
	$index = rentfetch_get_relationship_index();
	$plan = $index['records'][ (int) $floorplan_post_id ] ?? array();
	if ( 'floorplans' !== ( $plan['type'] ?? '' ) ) { return array_merge( $args, array( 'post__in' => array( 0 ) ) ); }
	$property = (string) ( $plan['property_id'] ?? '' );
	$external_plan = (string) ( $plan['floorplan_id'] ?? '' );
	if ( '' === $property || '' === $external_plan ) { return array_merge( $args, array( 'post__in' => array( 0 ) ) ); }
	// Resolve a small identity-scoped candidate list in PHP. Nested source/manual
	// OR meta queries create many unconstrained joins on metadata-heavy synced units.
	$units = get_posts( array(
		'post_type' => 'units', 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future' ),
		'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true,
		'meta_query' => array(
			array( 'key' => 'property_id', 'value' => $property ),
			array( 'key' => 'floorplan_id', 'value' => $external_plan ),
		),
	) );
	update_meta_cache( 'post', $units );
	$ids = array_values( array_filter( $units, static function( $id ) use ( $floorplan_post_id ) {
		return (int) $floorplan_post_id === rentfetch_resolve_floorplan_post_id( $id );
	} ) );
	if ( ! empty( $args['post__in'] ) ) { $ids = array_values( array_intersect( $ids, $args['post__in'] ) ); }
	$args['post_type'] = 'units';
	$args['post_status'] = $args['post_status'] ?? 'publish';
	$args['post__in'] = $ids ?: array( 0 );
	if ( 'availability_date' === ( $args['meta_key'] ?? '' ) ) {
		unset( $args['meta_key'] );
		$args['meta_query'][] = array( 'relation' => 'OR', 'rentfetch_unit_date' => array( 'key' => 'availability_date', 'compare' => 'EXISTS' ), array( 'key' => 'availability_date', 'compare' => 'NOT EXISTS' ) );
		$args['orderby'] = 'rentfetch_unit_date';
	}
	return $args;
}

/** Resolve property child lists by WordPress parent identity, not a shared external code. */
function rentfetch_get_property_floorplan_query_args( $property_post_id, $args = array() ) {
	if ( $property_post_id <= 0 ) { return array_merge( $args, array( 'post__in' => array( 0 ) ) ); }
	$index = rentfetch_get_relationship_index();
	$ids = array();
	foreach ( $index['records'] as $id => $record ) {
		if ( 'floorplans' === $record['type'] && (int) $property_post_id === rentfetch_get_parent_property_post_id( $id ) ) {
			$ids[] = $id;
		}
	}
	if ( ! empty( $args['post__in'] ) ) { $ids = array_values( array_intersect( $ids, $args['post__in'] ) ); }
	$args['post_type'] = 'floorplans';
	$args['post__in'] = $ids ?: array( 0 );
	return $args;
}
