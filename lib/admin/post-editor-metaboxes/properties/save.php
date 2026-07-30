<?php
/**
 * Rentfetch save properties metaboxes
 *
 * @param int $post_id The post ID.
 *
 * @return void.
 */
function rentfetch_save_properties_metaboxes( $post_id ) {

	if ( ! isset( $_POST['rentfetch_properties_metabox_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rentfetch_properties_metabox_nonce'] ) ), 'rentfetch_properties_metabox_nonce' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if (
		! rentfetch_validate_manual_record_identifiers(
			$post_id,
			'property_source',
			array(
				'property_id' => 'Property ID',
			)
		)
	) {
		return;
	}

	if ( isset( $_POST['property_id'] ) ) {
		$submitted_property_id = sanitize_text_field( wp_unslash( $_POST['property_id'] ) );
		$current_property_id   = (string) get_post_meta( $post_id, 'property_id', true );
		$original_property_id  = isset( $_POST['rentfetch_property_id_original'] )
			? sanitize_text_field( wp_unslash( $_POST['rentfetch_property_id_original'] ) )
			: $current_property_id;
		$override_confirmed    = isset( $_POST['rentfetch_property_id_override_confirmed'] )
			&& '1' === sanitize_text_field( wp_unslash( $_POST['rentfetch_property_id_override_confirmed'] ) );

		if ( $submitted_property_id === $current_property_id ) {
			// The locked field was submitted unchanged.
		} elseif ( ( $override_confirmed || '' === trim( $current_property_id ) ) && $original_property_id === $current_property_id ) {
			update_post_meta( $post_id, 'property_id', $submitted_property_id );
		}
	}

	if ( isset( $_POST['property_source'] ) ) {
		$submitted_property_source = sanitize_key( wp_unslash( $_POST['property_source'] ) );
		$current_property_source   = (string) get_post_meta( $post_id, 'property_source', true );
		$original_property_source  = isset( $_POST['rentfetch_property_source_original'] )
			? sanitize_key( wp_unslash( $_POST['rentfetch_property_source_original'] ) )
			: $current_property_source;
		$source_override_confirmed = isset( $_POST['rentfetch_property_source_override_confirmed'] )
			&& '1' === sanitize_text_field( wp_unslash( $_POST['rentfetch_property_source_override_confirmed'] ) );

		if ( $submitted_property_source === $current_property_source ) {
			// The source was enabled but left unchanged.
		} elseif ( $source_override_confirmed && $original_property_source === $current_property_source ) {
			if ( '' === $submitted_property_source ) {
				delete_post_meta( $post_id, 'property_source' );
			} else {
				update_post_meta( $post_id, 'property_source', $submitted_property_source );
			}
		}
	}

	if ( isset( $_POST['property_code'] ) ) {
		update_post_meta( $post_id, 'property_code', sanitize_text_field( wp_unslash( $_POST['property_code'] ) ) );
	}

	if ( isset( $_POST['address'] ) ) {
		update_post_meta( $post_id, 'address', sanitize_text_field( wp_unslash( $_POST['address'] ) ) );
	}

	if ( isset( $_POST['city'] ) ) {
		update_post_meta( $post_id, 'city', sanitize_text_field( wp_unslash( $_POST['city'] ) ) );
	}

	if ( isset( $_POST['state'] ) ) {
		update_post_meta( $post_id, 'state', sanitize_text_field( wp_unslash( $_POST['state'] ) ) );
	}

	if ( isset( $_POST['zipcode'] ) ) {
		update_post_meta( $post_id, 'zipcode', sanitize_text_field( wp_unslash( $_POST['zipcode'] ) ) );
	}

	if ( isset( $_POST['latitude'] ) ) {
		update_post_meta( $post_id, 'latitude', sanitize_text_field( wp_unslash( $_POST['latitude'] ) ) );
	}

	if ( isset( $_POST['longitude'] ) ) {
		update_post_meta( $post_id, 'longitude', sanitize_text_field( wp_unslash( $_POST['longitude'] ) ) );
	}

	if ( isset( $_POST['email'] ) ) {
		update_post_meta( $post_id, 'email', sanitize_text_field( wp_unslash( $_POST['email'] ) ) );
	}

	if ( isset( $_POST['phone'] ) ) {
		update_post_meta( $post_id, 'phone', sanitize_text_field( wp_unslash( $_POST['phone'] ) ) );
	}

	if ( isset( $_POST['url'] ) ) {
		update_post_meta( $post_id, 'url', sanitize_text_field( wp_unslash( $_POST['url'] ) ) );
	}
	
	if ( isset( $_POST['url_override'] ) ) {
		update_post_meta( $post_id, 'url_override', esc_url_raw( wp_unslash( $_POST['url_override'] ) ) );
	}

	if ( isset( $_POST['resident_portal_url'] ) ) {
		update_post_meta( $post_id, 'resident_portal_url', esc_url_raw( wp_unslash( $_POST['resident_portal_url'] ) ) );
	}

	if ( isset( $_POST['tour_booking_link'] ) ) {
		update_post_meta( $post_id, 'tour_booking_link', sanitize_text_field( wp_unslash( $_POST['tour_booking_link'] ) ) );
	}

	if ( isset( $_POST['apply_online_url'] ) ) {
		update_post_meta( $post_id, 'apply_online_url', esc_url_raw( wp_unslash( $_POST['apply_online_url'] ) ) );
	}

	if ( isset( $_POST['office_hours'] ) && is_array( $_POST['office_hours'] ) ) {
		$office_hours = array();
		$days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
		
		foreach ( $days as $day ) {
			if ( isset( $_POST['office_hours'][ $day ] ) && is_array( $_POST['office_hours'][ $day ] ) ) {
				$start = sanitize_text_field( wp_unslash( $_POST['office_hours'][ $day ]['start'] ?? '' ) );
				$end = sanitize_text_field( wp_unslash( $_POST['office_hours'][ $day ]['end'] ?? '' ) );
				
				// Normalize time format
				$start = rentfetch_normalize_time_input( $start );
				$end = rentfetch_normalize_time_input( $end );
				
				if ( ! empty( $start ) || ! empty( $end ) ) {
					$office_hours[ $day ] = array(
						'start' => $start,
						'end' => $end,
					);
				}
			}
		}
		
		if ( ! empty( $office_hours ) ) {
			update_post_meta( $post_id, 'office_hours', $office_hours );
		} else {
			delete_post_meta( $post_id, 'office_hours' );
		}
	}

	if ( isset( $_POST['images'] ) ) {
		$property_images = sanitize_text_field( wp_unslash( $_POST['images'] ) );
		$property_images = trim( $property_images, ',' );
		$property_images = explode( ',', $property_images );
		$property_images = array_unique( $property_images );
		
		// remove any empty values.
		$property_images = array_filter(
			$property_images,
			function ( $image_id ) {
				return is_numeric( $image_id );
			}
		);

		update_post_meta( $post_id, 'images', $property_images );
	}

	if ( isset( $_POST['description'] ) ) {
		update_post_meta( $post_id, 'description', wp_kses_post( wp_unslash( $_POST['description'] ) ) );
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

	if ( isset( $_POST['property_fees_embed'] ) ) {
		update_post_meta( $post_id, 'property_fees_embed', wp_unslash( $_POST['property_fees_embed'] ) );
	}

	if ( isset( $_POST['property_fees_json'] ) ) {
		$json_data = wp_unslash( $_POST['property_fees_json'] );
		$json_data = trim( $json_data ); // Trim whitespace
		
		// If CSV URL is set, clear the JSON data
		if ( isset( $_POST['property_fees_csv_url'] ) && ! empty( $_POST['property_fees_csv_url'] ) ) {
			delete_post_meta( $post_id, 'property_fees_data' );
		} else {
			// If JSON field is empty, save empty array
			if ( empty( $json_data ) ) {
				update_post_meta( $post_id, 'property_fees_data', array() );
			} else {
				$fees_data = json_decode( $json_data, true );
				if ( json_last_error() === JSON_ERROR_NONE && is_array( $fees_data ) ) {
					// Sanitize each fee item
					$sanitized_fees = array();
					foreach ( $fees_data as $fee ) {
						if ( is_array( $fee ) ) {
							$sanitized_fees[] = array(
								'description' => sanitize_text_field( $fee['description'] ?? '' ),
								'price'       => sanitize_text_field( $fee['price'] ?? '' ),
								'frequency'   => sanitize_text_field( $fee['frequency'] ?? '' ),
								'notes'       => sanitize_text_field( $fee['notes'] ?? '' ),
								'category'    => sanitize_text_field( $fee['category'] ?? '' ),
							);
						}
					}
					update_post_meta( $post_id, 'property_fees_data', $sanitized_fees );
				}
			}
		}
	}

	$previous_property_fees_csv_url = trim( (string) get_post_meta( $post_id, 'property_fees_csv_url', true ) );

	// Handle CSV upload or URL for property fees
	if ( isset( $_POST['property_fees_csv_url'] ) ) {
		$url = esc_url_raw( $_POST['property_fees_csv_url'] );
		update_post_meta( $post_id, 'property_fees_csv_url', $url );
		if ( ! empty( $url ) ) {
			// Clear the JSON data when CSV URL is set
			delete_post_meta( $post_id, 'property_fees_data' );
		}
	}

	$property_fees_csv_url = trim( (string) get_post_meta( $post_id, 'property_fees_csv_url', true ) );
	$csv_url_changed       = ( $property_fees_csv_url !== $previous_property_fees_csv_url );
	$csv_url_was_removed   = ( '' === $property_fees_csv_url && '' !== $previous_property_fees_csv_url );

	// Bust short-term CSV cache on any property save for previous/current URL.
	if ( function_exists( 'rentfetch_clear_cached_fees_csv_content' ) ) {
		rentfetch_clear_cached_fees_csv_content( $previous_property_fees_csv_url );
		rentfetch_clear_cached_fees_csv_content( $property_fees_csv_url );
	}
	if ( function_exists( 'rentfetch_clear_cached_monthly_required_fees_calculation' ) ) {
		rentfetch_clear_cached_monthly_required_fees_calculation( $previous_property_fees_csv_url );
		rentfetch_clear_cached_monthly_required_fees_calculation( $property_fees_csv_url );
	}

	// Only clear stored totals when a previously set CSV URL was explicitly removed.
	// CSV recalculation is AJAX-driven (validation + refresh flow), not save-driven.
	if ( $csv_url_was_removed ) {
		delete_post_meta( $post_id, 'property_monthly_required_total_fees' );
		delete_post_meta( $post_id, 'property_monthly_required_total_fees_last_checked' );
		delete_post_meta( $post_id, 'property_monthly_required_total_fees_rows' );
	}

	// Manual override field. If CSV URL changed in this save, keep the freshly parsed value.
	if ( isset( $_POST['property_monthly_required_total_fees'] ) ) {
		$raw_total = trim( (string) wp_unslash( $_POST['property_monthly_required_total_fees'] ) );

		// If CSV was explicitly removed in this save, keep the cleared state.
		// Also allow manual clearing when no CSV is configured and the field is blank.
		if ( $csv_url_was_removed || ( '' === $property_fees_csv_url && '' === $raw_total ) ) {
			delete_post_meta( $post_id, 'property_monthly_required_total_fees' );
			delete_post_meta( $post_id, 'property_monthly_required_total_fees_last_checked' );
			delete_post_meta( $post_id, 'property_monthly_required_total_fees_rows' );
		} elseif ( '' === $raw_total || $csv_url_changed ) {
			// Keep current parsed value (or cleared state from parser) when blank or when CSV changed.
		} else {
			$numeric_total = rentfetch_extract_first_numeric_fee_value( $raw_total );
			if ( null === $numeric_total || $numeric_total <= 0 ) {
				delete_post_meta( $post_id, 'property_monthly_required_total_fees' );
			} else {
				update_post_meta( $post_id, 'property_monthly_required_total_fees', number_format( $numeric_total, 2, '.', '' ) );
			}
		}
	}

	if ( isset( $_POST['has_specials'] ) ) {
		update_post_meta( $post_id, 'has_specials', '1' );
	} else {
		delete_post_meta( $post_id, 'has_specials' );
	}

	if ( isset( $_POST['specials_override_text'] ) ) {
		$specials_heading = sanitize_text_field( wp_unslash( $_POST['specials_override_text'] ) );
		$specials_heading = function_exists( 'mb_substr' ) ? mb_substr( $specials_heading, 0, 25 ) : substr( $specials_heading, 0, 25 );
		update_post_meta( $post_id, 'specials_override_text', $specials_heading );
	}

	if ( isset( $_POST['specials_content'] ) ) {
		update_post_meta( $post_id, 'specials_content', sanitize_textarea_field( wp_unslash( $_POST['specials_content'] ) ) );
	}

	$specials_date_fields = array(
		'specials_start_date',
		'specials_end_date',
	);

	foreach ( $specials_date_fields as $specials_date_field ) {
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

	if ( isset( $_POST['video'] ) ) {
		update_post_meta( $post_id, 'video', sanitize_text_field( wp_unslash( $_POST['video'] ) ) );
	}

	if ( isset( $_POST['pets'] ) ) {
		update_post_meta( $post_id, 'pets', sanitize_text_field( wp_unslash( $_POST['pets'] ) ) );
	}

	if ( isset( $_POST['content_area'] ) ) {
		$allowed_tags = array(
			'h2'     => array(),
			'h3'     => array(),
			'p'      => array(),
			'ul'     => array(),
			'ol'     => array(),
			'li'     => array(),
			'a'      => array(
				'href'   => array(),
				'title'  => array(),
				'target' => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
		);

		update_post_meta( $post_id, 'content_area', wp_kses( wp_unslash( $_POST['content_area'] ), $allowed_tags ) );
	}
}
add_action( 'save_post', 'rentfetch_save_properties_metaboxes' );
