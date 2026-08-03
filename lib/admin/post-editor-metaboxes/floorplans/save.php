<?php
/**
 * Save floor plan editor fields.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Save the Rent Fetch floor plan fields.
 *
 * @param int $post_id The post ID.
 * @return void
 */
function rentfetch_save_floorplans_metaboxes( $post_id ) {
	if ( ! isset( $_POST['rentfetch_floorplans_metabox_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rentfetch_floorplans_metabox_nonce'] ) ), 'rentfetch_floorplans_metabox_nonce' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if (
		! rentfetch_validate_manual_record_identifiers(
			$post_id,
			'floorplan_source',
			array(
				'property_id'  => 'Property ID',
				'floorplan_id' => 'Floor Plan ID',
			),
			array(
				'floorplan_source' => isset( $_POST['floorplan_source'] )
					? sanitize_key( wp_unslash( $_POST['floorplan_source'] ) )
					: null,
				'property_id'      => isset( $_POST['property_id'] )
					? sanitize_text_field( wp_unslash( $_POST['property_id'] ) )
					: null,
				'floorplan_id'     => isset( $_POST['floorplan_id'] )
					? sanitize_text_field( wp_unslash( $_POST['floorplan_id'] ) )
					: null,
			)
		)
	) {
		return;
	}

	if ( isset( $_POST['property_id'] ) ) {
		$submitted_property_id = sanitize_text_field( wp_unslash( $_POST['property_id'] ) );
		$current_property_id   = (string) get_post_meta( $post_id, 'property_id', true );
		$original_property_id  = isset( $_POST['rentfetch_floorplan_property_id_original'] )
			? sanitize_text_field( wp_unslash( $_POST['rentfetch_floorplan_property_id_original'] ) )
			: $current_property_id;
		$override_confirmed    = isset( $_POST['rentfetch_floorplan_property_id_override_confirmed'] )
			&& '1' === sanitize_text_field( wp_unslash( $_POST['rentfetch_floorplan_property_id_override_confirmed'] ) );

		if (
			$submitted_property_id !== $current_property_id
			&& ( $override_confirmed || '' === trim( $current_property_id ) )
			&& $original_property_id === $current_property_id
		) {
			update_post_meta( $post_id, 'property_id', $submitted_property_id );
		}
	}

	if ( isset( $_POST['floorplan_id'] ) ) {
		$submitted_floorplan_id = sanitize_text_field( wp_unslash( $_POST['floorplan_id'] ) );
		$current_floorplan_id   = (string) get_post_meta( $post_id, 'floorplan_id', true );
		$original_floorplan_id  = isset( $_POST['rentfetch_floorplan_id_original'] )
			? sanitize_text_field( wp_unslash( $_POST['rentfetch_floorplan_id_original'] ) )
			: $current_floorplan_id;
		$override_confirmed     = isset( $_POST['rentfetch_floorplan_id_override_confirmed'] )
			&& '1' === sanitize_text_field( wp_unslash( $_POST['rentfetch_floorplan_id_override_confirmed'] ) );

		if (
			$submitted_floorplan_id !== $current_floorplan_id
			&& ( $override_confirmed || '' === trim( $current_floorplan_id ) )
			&& $original_floorplan_id === $current_floorplan_id
		) {
			update_post_meta( $post_id, 'floorplan_id', $submitted_floorplan_id );
		}
	}

	if ( isset( $_POST['floorplan_source'] ) ) {
		$submitted_floorplan_source = sanitize_key( wp_unslash( $_POST['floorplan_source'] ) );
		$current_floorplan_source   = (string) get_post_meta( $post_id, 'floorplan_source', true );
		$original_floorplan_source  = isset( $_POST['rentfetch_floorplan_source_original'] )
			? sanitize_key( wp_unslash( $_POST['rentfetch_floorplan_source_original'] ) )
			: $current_floorplan_source;
		$source_override_confirmed  = isset( $_POST['rentfetch_floorplan_source_override_confirmed'] )
			&& '1' === sanitize_text_field( wp_unslash( $_POST['rentfetch_floorplan_source_override_confirmed'] ) );

		if (
			$submitted_floorplan_source !== $current_floorplan_source
			&& $source_override_confirmed
			&& $original_floorplan_source === $current_floorplan_source
		) {
			if ( '' === $submitted_floorplan_source ) {
				delete_post_meta( $post_id, 'floorplan_source' );
			} else {
				update_post_meta( $post_id, 'floorplan_source', $submitted_floorplan_source );
			}
		}
	}

	$text_fields = array(
		'unit_type_mapping',
		'floorplan_image_url',
		'beds',
		'baths',
		'minimum_deposit',
		'maximum_deposit',
		'minimum_rent',
		'maximum_rent',
		'minimum_sqft',
		'maximum_sqft',
		'availability_date',
		'availability_url',
		'available_units',
	);

	foreach ( $text_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	if ( isset( $_POST['floorplan_description'] ) ) {
		update_post_meta( $post_id, 'floorplan_description', wp_kses_post( wp_unslash( $_POST['floorplan_description'] ) ) );
	}

	if ( isset( $_POST['specials_override_text'] ) ) {
		$specials_heading = sanitize_text_field( wp_unslash( $_POST['specials_override_text'] ) );
		$specials_heading = function_exists( 'mb_substr' ) ? mb_substr( $specials_heading, 0, 25 ) : substr( $specials_heading, 0, 25 );
		update_post_meta( $post_id, 'specials_override_text', $specials_heading );
	}

	if ( isset( $_POST['specials_content'] ) ) {
		update_post_meta( $post_id, 'specials_content', sanitize_textarea_field( wp_unslash( $_POST['specials_content'] ) ) );
	}

	foreach ( array( 'specials_start_date', 'specials_end_date' ) as $specials_date_field ) {
		if ( ! isset( $_POST[ $specials_date_field ] ) ) {
			continue;
		}

		$specials_date = sanitize_text_field( wp_unslash( $_POST[ $specials_date_field ] ) );

		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $specials_date ) ) {
			update_post_meta( $post_id, $specials_date_field, $specials_date );
		} else {
			delete_post_meta( $post_id, $specials_date_field );
		}
	}

	$specials_start_date = get_post_meta( $post_id, 'specials_start_date', true );
	$specials_end_date   = get_post_meta( $post_id, 'specials_end_date', true );

	if ( $specials_start_date && $specials_end_date && $specials_start_date > $specials_end_date ) {
		update_post_meta( $post_id, 'specials_start_date', $specials_end_date );
		update_post_meta( $post_id, 'specials_end_date', $specials_start_date );
	}

	if ( isset( $_POST['tour'] ) ) {
		$allowed_tags = array(
			'iframe' => array(
				'src'             => array(),
				'width'           => array(),
				'height'          => array(),
				'frameborder'     => array(),
				'allowfullscreen' => array(),
				'allow'           => array(),
			),
		);

		update_post_meta( $post_id, 'tour', wp_kses( wp_unslash( $_POST['tour'] ), $allowed_tags ) );
	}

	foreach ( array( 'property_show_specials', 'has_specials', 'specials_exclude_property' ) as $checkbox ) {
		if ( isset( $_POST[ $checkbox ] ) ) {
			update_post_meta( $post_id, $checkbox, '1' );
		} else {
			delete_post_meta( $post_id, $checkbox );
		}
	}

	if ( isset( $_POST['images'] ) ) {
		$image_ids = sanitize_text_field( wp_unslash( $_POST['images'] ) );
		$image_ids = array_unique( explode( ',', trim( $image_ids, ',' ) ) );
		$image_ids = array_filter( $image_ids, 'is_numeric' );

		update_post_meta( $post_id, 'manual_images', $image_ids );
	}
}
add_action( 'save_post', 'rentfetch_save_floorplans_metaboxes' );
