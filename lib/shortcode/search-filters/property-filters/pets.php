<?php
/**
 * Pets filter
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add the pets filter to the search
 *
 * @return void.
 */
function rentfetch_search_filters_pets() {

	// check whether beds search is enabled.
	$map_search_components = get_option( 'rentfetch_options_map_search_components' );

	// this needs to be set to an array even if the option isn't set.
	if ( ! is_array( $map_search_components ) ) {
		$map_search_components = array();
	}

	// bail if beds search is not enabled.
	if ( ! in_array( 'pets_search', $map_search_components, true ) ) {
		return;
	}

	// * get information about pets from the database
	$pets = rentfetch_get_meta_values( 'pets', 'properties' );
	$pets = array_unique( $pets );
	asort( $pets );
	$pets = array_filter( $pets );

	$pets_choices = array(
		1 => 'Cats allowed',
		2 => 'Cats and Dogs allowed',
		3 => 'Pet-friendly',
		4 => 'Pets not allowed',
	);

	// * build the pets search
	if ( ! empty( $pets ) ) {
		echo '<fieldset ckass="pets">';
			echo '<legend>Pets</legend>';
			echo '<button class="toggle">Pets</button>';
			echo '<div class="input-wrap checkboxes">';
		foreach ( $pets as $pet ) {
			printf( '<label><input type="radio" data-pets="%s" data-pets-name="%s" name="pets" value="%s" /><span>%s</span></label>', esc_html( $pet ), esc_html( $pets_choices[ $pet ] ), esc_html( $pet ), esc_html( $pets_choices[ $pet ] ) );
		}
			echo '</div>'; // .checkboxes.
		echo '</fieldset>';
	}
}

/**
 * Apply the selected pets filter to the search
 *
 * @param array $property_args The property arguments.
 *
 * @return array.
 */
function rentfetch_search_properties_args_pets( $property_args ) {

	if ( isset( $_POST['pets'] ) ) {
		$property_args['meta_query'][] = array(
			array(
				'key'   => 'pets',
				'value' => sanitize_text_field( wp_unslash( $_POST['pets'] ) ),
			),
		);
	}

	return $property_args;
}
add_filter( 'rentfetch_search_property_map_properties_query_args', 'rentfetch_search_properties_args_pets', 10, 1 );
