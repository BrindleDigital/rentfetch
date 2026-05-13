<?php
/**
 * The Details section of the single property page
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output the details section
 *
 * @return void.
 */
function rentfetch_single_properties_parts_details() {

	$maybe_do_details = apply_filters( 'rentfetch_maybe_do_property_part_details', true );
	$maybe_do_floorplans = apply_filters( 'rentfetch_maybe_do_property_part_floorplans', true );

	if ( true !== $maybe_do_details && true !== $maybe_do_floorplans ) {
		return;
	}

	echo '<div id="details" class="single-properties-section">';
		echo '<div class="wrap">';

			$property_id          = get_post_meta( get_the_ID(), 'property_id', true );
			$title                = true === $maybe_do_details ? rentfetch_get_property_title() : null;
			$location             = true === $maybe_do_details ? rentfetch_get_property_city_state() : null;
			$property_description = true === $maybe_do_details ? rentfetch_get_property_description() : null;
			$property_rent        = true === $maybe_do_details ? rentfetch_get_property_pricing() : null;
			$beds                 = true === $maybe_do_details ? rentfetch_get_property_bedrooms() : null;
			$sqrft                = true === $maybe_do_details ? rentfetch_get_property_square_feet() : null;

			if ( true === $maybe_do_details ) {
				echo '<div class="property-details-header">';
					echo '<div class="property-details-basic-info">';

						if ( $title ) {
							printf( '<h1 class="title">%s</h1>', esc_html( $title ) );
						}

						if ( $location ) {
							echo '<p class="location">';
								printf( '<span class="city-state">%s</span>', esc_html( $location ) );
							echo '</p>';
						}

					echo '</div>';
					echo '<div class="property-details-buttons">';

						if ( $property_rent ) {
							printf( '<p class="rent">%s</p>', wp_kses_post( $property_rent ) );
						}

					echo '</div>';
				echo '</div>'; // .property-details-header.

				if ( $beds || $sqrft ) {
					$bed_icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true" focusable="false" class="property-stat-icon property-stat-icon-bed"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5v9m16.5 0v-5.25a3 3 0 0 0-3-3H9.75v8.25m-6 0h16.5m-16.5-6h6m-6 0V6.75A1.5 1.5 0 0 1 5.25 5.25h3A1.5 1.5 0 0 1 9.75 6.75v1.5" /></svg>';
					$sqrft_icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true" focusable="false" class="property-stat-icon property-stat-icon-sqrft"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.25h13.5v13.5H5.25z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15.75v-7.5h7.5" /></svg>';

					echo '<div class="property-stats" aria-label="Property statistics">';
						if ( $beds ) {
							echo '<div class="property-stat property-stat-beds">';
								echo $bed_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo '<div class="property-stat-text">';
									echo '<span class="property-stat-label">Bedrooms</span>';
									printf( '<span class="property-stat-value">%s</span>', wp_kses_post( $beds ) );
								echo '</div>';
							echo '</div>';
						}

						if ( $sqrft ) {
							echo '<div class="property-stat property-stat-sqrft">';
								echo $sqrft_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo '<div class="property-stat-text">';
									echo '<span class="property-stat-label">Square Feet</span>';
									printf( '<span class="property-stat-value">%s</span>', wp_kses_post( $sqrft ) );
								echo '</div>';
							echo '</div>';
						}
					echo '</div>'; // .property-stats.
				}
			}

			echo '<div class="property-details-body">';
				echo '<div class="property-main-content">';
					if ( true === $maybe_do_details && $property_description ) {
						echo '<div class="property-basic-info">';
							echo wp_kses_post( apply_filters( 'rentfetch_single_property_description_headline', '<h2>About the Property</h2>' ) );

							$description_plain_text = trim( wp_strip_all_tags( $property_description ) );
							$should_collapse_description = strlen( $description_plain_text ) > 900 || str_word_count( $description_plain_text ) > 140;
							$should_collapse_description = (bool) apply_filters( 'rentfetch_single_property_should_collapse_description', $should_collapse_description, $property_description );

							if ( $should_collapse_description ) {
								echo '<div class="property-description-details is-collapsible">';
									echo '<input type="checkbox" id="property-description-toggle" class="property-description-toggle">';
									printf( '<div class="description">%s</div>', wp_kses_post( $property_description ) );
									echo '<label for="property-description-toggle">More</label>';
								echo '</div>';
							} else {
								printf( '<div class="description">%s</div>', wp_kses_post( $property_description ) );
							}

						echo '</div>'; // .property-basic-info.
					}

					if ( true === $maybe_do_floorplans && $property_id ) {
						echo '<div class="property-floorplans">';
							echo wp_kses_post( apply_filters( 'rentfetch_single_property_floorplans_headline', '<h2>Floor Plans</h2>' ) );

							$floorplan_search_shortcode = sprintf(
								'[rentfetch_floorplansearch property_id="%s"]',
								esc_attr( $property_id )
							);

							if ( function_exists( 'rentfetch_property_fees_embed_and_wrap' ) ) {
								remove_action( 'rentfetch_after_floorplans_search', 'rentfetch_property_fees_embed_and_wrap' );
							}

							echo do_shortcode( $floorplan_search_shortcode );

							if ( function_exists( 'rentfetch_property_fees_embed_and_wrap' ) ) {
								add_action( 'rentfetch_after_floorplans_search', 'rentfetch_property_fees_embed_and_wrap' );
							}
						echo '</div>'; // .property-floorplans.
					}
				echo '</div>'; // .property-main-content.

				if ( true === $maybe_do_details ) {
					echo '<div class="property-links">';

						$full_location = rentfetch_get_property_location();
						$location_link = rentfetch_get_property_location_link();
						$property_url  = rentfetch_get_property_url();
						$phone         = rentfetch_get_property_phone();
						$phone_link    = rentfetch_format_phone_number_link( $phone );
						$email         = rentfetch_get_property_email();
						$email_link    = rentfetch_get_property_email_link( null, 'property-sidebar-email-link' );
						$location_icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 location-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>';
						$website_icon  = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 website-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>';
						$phone_icon    = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 phone-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>';
						$email_icon    = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 email-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>';
						$sidebar_icon_allowed_html = array_merge(
							wp_kses_allowed_html( 'post' ),
							array(
								'svg'  => array(
									'xmlns'        => true,
									'fill'         => true,
									'viewbox'      => true,
									'stroke-width' => true,
									'stroke'       => true,
									'class'        => true,
								),
								'path' => array(
									'stroke-linecap'  => true,
									'stroke-linejoin' => true,
									'd'               => true,
								),
							)
						);

						if ( $full_location ) {
							echo '<div class="property-sidebar-item property-sidebar-address">';
								echo wp_kses( $location_icon, $sidebar_icon_allowed_html );
								echo '<div class="property-sidebar-item-content property-sidebar-address-content">';
									echo '<h3>Address</h3>';
									printf(
										'<a class="property-sidebar-address-link" href="%s" target="_blank">%s</a>',
										esc_url( $location_link ),
										esc_html( $full_location )
									);
								echo '</div>';
							echo '</div>';
						}

						if ( $property_url ) {
							echo '<div class="property-sidebar-item property-sidebar-website">';
								echo wp_kses( $website_icon, $sidebar_icon_allowed_html );
								echo '<div class="property-sidebar-item-content property-sidebar-website-content">';
									echo '<h3>Visit Website</h3>';
									printf(
										'<a class="property-sidebar-website-link" href="%s" target="%s">%s</a>',
										esc_url( $property_url ),
										esc_attr( rentfetch_get_link_target( $property_url ) ),
										esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $property_url ) ) )
									);
								echo '</div>';
							echo '</div>';
						}

						if ( $phone && $phone_link ) {
							echo '<div class="property-sidebar-item property-sidebar-phone">';
								echo wp_kses( $phone_icon, $sidebar_icon_allowed_html );
								echo '<div class="property-sidebar-item-content property-sidebar-phone-content">';
									echo '<h3>Phone</h3>';
									printf(
										'<a class="property-sidebar-phone-link" href="tel:%s">%s</a>',
										esc_attr( $phone_link ),
										esc_html( $phone )
									);
								echo '</div>';
							echo '</div>';
						}

						if ( $email && $email_link ) {
							echo '<div class="property-sidebar-item property-sidebar-email">';
								echo wp_kses( $email_icon, $sidebar_icon_allowed_html );
								echo '<div class="property-sidebar-item-content property-sidebar-email-content">';
									echo '<h3>Email</h3>';
									echo wp_kses_post( $email_link );
								echo '</div>';
							echo '</div>';
						}

						rentfetch_property_office_hours_button();

						if ( rentfetch_get_property_tour_booking_url() ) {
							echo '<div class="property-sidebar-actions">';
								rentfetch_property_tour_booking_button();
							echo '</div>';
						}

						rentfetch_property_tour_button();

					echo '</div>'; // .property-links.
				}
			echo '</div>'; // .property-details-body.

		echo '</div>'; // .wrap.
	echo '</div>'; // #details.
}

/**
 * Maybe output the details section
 *
 * @return bool the answer to the question: should we output the details section?
 */
function rentfetch_maybe_property_part_details() {

	// bail if this section is not enabled.
	$property_components = get_option( 'rentfetch_options_single_property_components' );
	if ( ! is_array( $property_components ) || ! in_array( 'property_details', $property_components, true ) ) {
		return false;
	}

	return true;
}
add_filter( 'rentfetch_maybe_do_property_part_details', 'rentfetch_maybe_property_part_details' );

/**
 * Output the details section in the subnav
 *
 * @return void.
 */
function rentfetch_single_properties_parts_subnav_details() {
	$maybe_do_details    = apply_filters( 'rentfetch_maybe_do_property_part_details', true );
	$maybe_do_floorplans = apply_filters( 'rentfetch_maybe_do_property_part_floorplans', true );

	if ( true === $maybe_do_details || true === $maybe_do_floorplans ) {
		if ( true === $maybe_do_details && true === $maybe_do_floorplans ) {
			$default_label = 'Details & Floor Plans';
		} elseif ( true === $maybe_do_floorplans ) {
			$default_label = 'Floor Plans';
		} else {
			$default_label = 'Details';
		}

		$label         = apply_filters( 'rentfetch_property_details_subnav_label', $default_label );
		printf( '<li><a href="#details">%s</a></li>', esc_attr( $label ) );
	}
}
