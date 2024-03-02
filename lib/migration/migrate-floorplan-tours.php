<?php
/**
 * Migrate the floorplan tours 
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Place this code in your plugin file.
register_activation_hook( RENTFETCH_FILE, 'update_floorplans_meta' );

/**
 * Move old tour meta to the new system
 *
 * @return void.
 */
function update_floorplans_meta() {
	// Query all posts of type 'floorplans'
	$args = array(
		'post_type' => 'floorplans',
		'posts_per_page' => -1, // Get all posts
		'meta_query' => array(
			array(
				'key' => 'floorplan_video_or_tour',
				'value' => '', // This will match posts with any value for 'floorplan_video_or_tour'
				'compare' => '!=',
			),
		),
	);

	$query = new WP_Query($args);

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$post_id = get_the_ID();

			// Get the current value of 'floorplan_video_or_tour'
			$value = get_post_meta($post_id, 'floorplan_video_or_tour', true);

			// Update the 'tour' meta with the value of 'floorplan_video_or_tour'
			update_post_meta($post_id, 'tour', $value);

			// Delete the old 'floorplan_video_or_tour' meta
			delete_post_meta($post_id, 'floorplan_video_or_tour');
		}
		wp_reset_postdata();
	}
}