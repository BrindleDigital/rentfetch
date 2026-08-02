<?php
/**
 * Floor plan hierarchy.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render the property, floor plan, and unit hierarchy for a floor plan.
 *
 * @param WP_Post $post Floor plan post.
 * @return void
 */
function rentfetch_floorplans_hierarchy_metabox_callback( $post ) {
	rentfetch_render_hierarchy( $post, 'floorplans' );
}
