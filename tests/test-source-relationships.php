<?php
/** Real disposable-site regression: WP-CLI eval-file; no external requests. */
if ( ! defined( 'RFS_DISPOSABLE_SITE' ) || ! RFS_DISPOSABLE_SITE ) { throw new RuntimeException( 'Disposable site required.' ); }
require_once dirname( __DIR__ ) . '/rentfetch.php';
rentfetch_register_properties_cpt(); rentfetch_register_floorplans_cpt(); rentfetch_register_units_cpt();
function relationship_check( $ok, $message ) { if ( ! $ok ) { throw new RuntimeException( $message ); } }
$ids = array();
$tag = 'COLLISION_' . wp_generate_password( 8, false );
$make = static function( $type, $title, $meta ) use ( &$ids ) {
	$id = wp_insert_post( array( 'post_type' => $type, 'post_title' => $title, 'post_status' => 'publish', 'meta_input' => $meta ), true );
	if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
	$ids[] = $id; return $id;
};
$cache_filter = static function() { return '1'; };
add_filter( 'pre_option_rentfetch_options_disable_query_caching', $cache_filter );
$prior_post = $GLOBALS['post'] ?? null;
try {
	$a = $make( 'properties', 'Provider A', array( 'property_id' => $tag, 'property_source' => 'rentegi' ) );
	$b = $make( 'properties', 'Provider B', array( 'property_id' => $tag, 'property_source' => 'rentmanager' ) );
	$m = $make( 'properties', 'Manual property', array( 'property_id' => $tag . '_MANUAL' ) );
	$plan_meta = array( 'property_id' => $tag, 'floorplan_id' => 'PLAN', 'beds' => 1, 'baths' => 1, 'minimum_rent' => 1000, 'maximum_rent' => 1000, 'minimum_sqft' => 700, 'maximum_sqft' => 700, 'available_units' => 1 );
	$pa = $make( 'floorplans', 'Plan A', $plan_meta + array( 'floorplan_source' => 'rentegi' ) );
	$pb = $make( 'floorplans', 'Plan B', $plan_meta + array( 'floorplan_source' => 'rentmanager' ) );
	$pm = $make( 'floorplans', 'Manual plan', array_replace( $plan_meta, array( 'property_id' => $tag . '_MANUAL' ) ) );
	$unit_meta = array( 'property_id' => $tag, 'floorplan_id' => 'PLAN', 'availability_date' => '01/02/2027' );
	$ua = $make( 'units', 'Unit A', $unit_meta + array( 'unit_source' => 'rentegi', 'unit_id' => 'A' ) );
	$ub = $make( 'units', 'Unit B', $unit_meta + array( 'unit_source' => 'rentmanager', 'unit_id' => 'B' ) );
	$um = $make( 'units', 'Attached manual unit', array( 'property_id' => $tag, 'floorplan_id' => 'PLAN', 'unit_id' => 'MANUAL', 'rentfetch_floorplan_post_id' => $pa ) );
	$ambiguous = $make( 'units', 'Unlinked ambiguous unit', array( 'property_id' => $tag, 'floorplan_id' => 'PLAN', 'unit_id' => 'AMBIGUOUS' ) );
	$legacy = $make( 'units', 'Legacy manual unit', array( 'property_id' => $tag . '_MANUAL', 'floorplan_id' => 'PLAN', 'unit_id' => 'LEGACY' ) );
	$legacy_source = $make( 'units', 'Legacy source unit', $unit_meta + array( 'floorplan_source' => 'rentmanager', 'unit_id' => 'LEGACY_SOURCE' ) );
	// Synced units carry many diagnostic/price fields; source filtering must not
	// multiply unconstrained metadata joins as that payload grows.
	foreach ( array( $ua, $ub, $um ) as $heavy_unit ) {
		for ( $i = 0; $i < 30; ++$i ) { update_post_meta( $heavy_unit, 'fixture_payload_' . $i, 'value' ); }
	}
	$assert_units = static function( $plan, $expected, $with_dates = false ) {
		$args = array( 'posts_per_page' => -1, 'fields' => 'ids' );
		if ( $with_dates ) { $args['meta_key'] = 'availability_date'; $args['orderby'] = 'meta_value'; }
		$actual = array_map( 'intval', get_posts( rentfetch_get_floorplan_unit_query_args( $plan, $args ) ) );
		sort( $actual ); sort( $expected );
		relationship_check( $expected === $actual, 'Units crossed provider boundary or manual unit disappeared: ' . wp_json_encode( array( $expected, $actual ) ) );
	};
	$assert_units( $pa, array( $ua, $um ) );
	$assert_units( $pa, array( $ua, $um ), true );
	$assert_units( $pb, array( $ub, $legacy_source ), true );
	$assert_units( $pm, array( $legacy ), true );
	relationship_check( $pa === rentfetch_resolve_floorplan_post_id( $um ) && $a === rentfetch_get_parent_property_post_id( $um ), 'Manual explicit parent did not resolve.' );
	relationship_check( 0 === rentfetch_resolve_floorplan_post_id( $ambiguous ), 'Ambiguous legacy manual attachment must not be guessed.' );
	relationship_check( $pa === rentfetch_get_connected_floorplan_post_id_for_unit( $ua ) && $pb === rentfetch_get_connected_floorplan_post_id_for_unit( $ub ), 'Synced source parent mismatch.' );
	$groups = rentfetch_get_monitoring_child_posts_grouped_by_property( 'units' );
	$key_a = rentfetch_get_property_group_key( $tag, $a ); $key_b = rentfetch_get_property_group_key( $tag, $b );
	relationship_check( $key_a !== $key_b && 2 === count( $groups[$key_a] ?? array() ) && 2 === count( $groups[$key_b] ?? array() ), 'Monitoring mixed provider children.' );
	$GLOBALS['rentfetch_floorplans'] = rentfetch_get_floorplans_array_sql( array( 'post__in' => array( $pa, $pb, $pm ) ) );
	relationship_check( array( $pa ) === array_map( 'intval', rentfetch_get_floorplans( $tag, $a )['id'] ?? array() ), 'Provider A aggregate mixed plans.' );
	relationship_check( array( $pb ) === array_map( 'intval', rentfetch_get_floorplans( $tag, $b )['id'] ?? array() ), 'Provider B aggregate mixed plans.' );
	$GLOBALS['post'] = null;
	relationship_check( array() === rentfetch_get_floorplans( $tag ), 'Unscoped ambiguous lookup must not leak another provider.' );
	$property_posts = rentfetch_get_property_ids_with_available_floorplans( array( 'post__in' => array( $pa ) ), true );
	relationship_check( array( $a ) === array_map( 'intval', $property_posts ), 'Filtered property search crossed source identity.' );
	$property_plans = get_posts( rentfetch_get_property_floorplan_query_args( $a, array( 'fields' => 'ids', 'posts_per_page' => -1 ) ) );
	relationship_check( array( $pa ) === array_map( 'intval', $property_plans ), 'Property floor-plan list crossed source identity.' );
	// Preserve a manual attachment through an actual metabox save and reject an unconfirmed move.
	$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) ); wp_set_current_user( $admins[0] );
	$_POST = array( 'rentfetch_units_metabox_nonce' => wp_create_nonce( 'rentfetch_units_metabox_nonce' ), 'unit_source' => '', 'unit_id' => 'MANUAL', 'property_id' => $tag, 'floorplan_id' => 'PLAN', 'rentfetch_floorplan_post_id' => $pb );
	rentfetch_save_units_metaboxes( $um );
	relationship_check( $pa === (int) get_post_meta( $um, 'rentfetch_floorplan_post_id', true ), 'Manual parent changed without confirmation.' );
	$_POST['rentfetch_unit_relationship_override_confirmed'] = '1';
	rentfetch_save_units_metaboxes( $um );
	relationship_check( $pb === (int) get_post_meta( $um, 'rentfetch_floorplan_post_id', true ), 'Confirmed manual attachment selection was not saved.' );
	$assert_units( $pa, array( $ua ) ); $assert_units( $pb, array( $ub, $legacy_source, $um ) );
	ob_start(); rentfetch_render_unit_identity_bar( get_post( $um ) ); $html = ob_get_clean();
	$previous_errors = libxml_use_internal_errors( true );
	$dom = new DOMDocument(); $dom->loadHTML( $html ); $xpath = new DOMXPath( $dom );
	$selected_property = $xpath->query( '//select[@id="property_id"]/option[@selected]' );
	$selected_plan = $xpath->query( '//select[@id="floorplan_id"]/option[@selected]' );
	relationship_check( 1 === $selected_property->length && (string) $b === $selected_property->item(0)->getAttribute('data-property-post-id'), 'Editor property selection is ambiguous.' );
	relationship_check( 1 === $selected_plan->length && (string) $pb === $selected_plan->item(0)->getAttribute('data-floorplan-post-id'), 'Editor floor plan selection is ambiguous.' );
	libxml_clear_errors(); libxml_use_internal_errors( $previous_errors );
	echo "Source relationship regressions passed: provider collisions, explicit/legacy manual attachments, undated units, counts, aggregates, search, monitoring and protected editor saves.\n";
} finally {
	$_POST = array(); $GLOBALS['post'] = $prior_post;
	remove_filter( 'pre_option_rentfetch_options_disable_query_caching', $cache_filter );
	foreach ( array_reverse( $ids ) as $id ) { wp_delete_post( $id, true ); }
}
