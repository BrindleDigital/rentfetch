<?php
/**
 * Text-based search
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add the text-based search to the search filters.
 *
 * @return void.
 */
function rentfetch_search_filters_text_search() {

	// check the query to see if we have a text-based search.
	if ( isset( $_GET['textsearch'] ) ) {
		$searchtext = sanitize_text_field( wp_unslash( $_GET['textsearch'] ) );
	} else {
		$searchtext = null;
	}

	$placeholder = apply_filters( 'rentfetch_search_placeholder_text', 'Search...' );

	// build the text-based search.
	echo '<fieldset class="text-based-search">';
		echo '<legend>Search</legend>';
		echo '<div class="input-wrap text">';
	if ( $searchtext ) {
		printf( '<input type="text" name="textsearch" placeholder="%s" class="active" value="%s" />', esc_attr( $placeholder ), esc_attr( $searchtext ) );
	} else {
		printf( '<input type="text" name="textsearch" placeholder="%s" />', esc_attr( $placeholder ) );
	}
		echo '</div>'; // .text
	echo '</fieldset>';
}

/**
 * Add the text-based search to the search filters
 *
 * @param array $property_args The property arguments.
 *
 * @return array.
 */
function rentfetch_search_properties_args_text( $property_args ) {

	// * Add text-based search into the query.
	$search = null;

	if ( isset( $_POST['textsearch'] ) ) {
		$search = sanitize_text_field( wp_unslash( $_POST['textsearch'] ) );
	}

	if ( null !== $search ) {
		$property_args['s'] = $search;
		$property_args['rentfetch_property_text_search'] = true;
	}

	return $property_args;
}
add_filter( 'rentfetch_search_property_map_properties_query_args', 'rentfetch_search_properties_args_text', 10, 1 );

/**
 * Get the property meta fields included in front-end text search.
 *
 * Taxonomy terms are intentionally excluded because those have dedicated filters.
 *
 * @return array
 */
function rentfetch_get_property_text_search_meta_keys() {
	return apply_filters(
		'rentfetch_property_text_search_meta_keys',
		array(
			'address',
			'city',
			'state',
			'zipcode',
		)
	);
}

/**
 * Determine whether a WP_Query is the front-end Rent Fetch property text search.
 *
 * @param WP_Query $query The query object.
 * @return bool
 */
function rentfetch_is_property_text_search_query( $query ) {
	return $query instanceof WP_Query
		&& $query->get( 'rentfetch_property_text_search' )
		&& 'properties' === $query->get( 'post_type' )
		&& '' !== (string) $query->get( 's' );
}

/**
 * Include selected property meta fields in front-end property text search.
 *
 * @param string   $search The SQL search clause.
 * @param WP_Query $query  The query object.
 * @return string
 */
function rentfetch_property_text_search_clause( $search, $query ) {
	if ( ! rentfetch_is_property_text_search_query( $query ) ) {
		return $search;
	}

	global $wpdb;

	$search_term = trim( (string) $query->get( 's' ) );
	if ( '' === $search_term ) {
		return $search;
	}

	$terms = preg_split( '/\s+/', $search_term );
	$terms = array_filter( array_map( 'trim', $terms ) );

	if ( empty( $terms ) ) {
		return $search;
	}

	$meta_keys = rentfetch_get_property_text_search_meta_keys();
	$meta_keys = array_filter( array_map( 'sanitize_key', $meta_keys ) );

	$search_parts = array();
	foreach ( $terms as $term ) {
		$like = '%' . $wpdb->esc_like( $term ) . '%';
		$term_parts = array(
			$wpdb->prepare( "({$wpdb->posts}.post_title LIKE %s)", $like ),
			$wpdb->prepare( "({$wpdb->posts}.post_excerpt LIKE %s)", $like ),
			$wpdb->prepare( "({$wpdb->posts}.post_content LIKE %s)", $like ),
		);

		if ( ! empty( $meta_keys ) ) {
			$meta_key_placeholders = implode( ', ', array_fill( 0, count( $meta_keys ), '%s' ) );
			$term_parts[] = $wpdb->prepare(
				"EXISTS (
					SELECT 1
					FROM {$wpdb->postmeta} AS rentfetch_property_searchmeta
					WHERE rentfetch_property_searchmeta.post_id = {$wpdb->posts}.ID
					AND rentfetch_property_searchmeta.meta_key IN ( {$meta_key_placeholders} )
					AND rentfetch_property_searchmeta.meta_value LIKE %s
				)",
				array_merge( $meta_keys, array( $like ) )
			);
		}

		$search_parts[] = '(' . implode( ' OR ', $term_parts ) . ')';
	}

	return ' AND (' . implode( ' AND ', $search_parts ) . ') ';
}
add_filter( 'posts_search', 'rentfetch_property_text_search_clause', 10, 2 );
