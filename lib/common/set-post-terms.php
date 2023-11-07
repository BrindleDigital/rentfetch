<?php

/**
  * Appends the specified taxonomy term to the incoming post object. If 
 * the term doesn't already exist in the database, it will be created.
 *
 * @param    WP_Post    $post        The post to which we're adding the taxonomy term.
 * @param    string     $value       The name of the taxonomy term
 * @param    string     $taxonomy    The name of the taxonomy.
 * @access   private
 * @since    1.0.0
 */

function rentfetch_set_post_term( $post_id, $value, $taxonomy ) {

	$term = term_exists( $value, $taxonomy );
	$slug = sanitize_title( $value );

	// If the taxonomy term doesn't exist, then we create it
	if ( 0 === $term || null === $term ) {

		$term = wp_insert_term(
			$value,
			$taxonomy,
			array(
				'slug' => $slug,
			)
		);

		// Check for WP_Error during term creation
		if ( is_wp_error( $term ) ) {
			return; // Bail if there's a WP_Error happening here to avoid fatal errors.
		}

	}

	$term_id = intval( $term['term_id'] );
	$post_id = intval( $post_id );

	// Set the taxonomy term for the post
	wp_set_object_terms( $post_id, $term_id, $taxonomy, true );

}
