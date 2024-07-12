<?php
/**
 * This file sets up the metaboxes for the properties post type
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the property details metaboxes
 *
 * @return void.
 */
function rentfetch_register_properties_details_metabox() {

	add_meta_box(
		'rentfetch_properties_identifiers', // ID of the metabox.
		'Property Identifiers', // Title of the metabox.
		'rentfetch_properties_identifiers_metabox_callback', // Callback function to render the metabox.
		'properties', // Post type to add the metabox to.
		'normal', // Priority of the metabox.
		'default' // Context of the metabox.
	);

	add_meta_box(
		'rentfetch_properties_contact', // ID of the metabox.
		'Property Contact Information', // Title of the metabox.
		'rentfetch_properties_contact_metabox_callback', // Callback function to render the metabox.
		'properties', // Post type to add the metabox to.
		'normal', // Priority of the metabox.
		'default' // Context of the metabox.
	);

	add_meta_box(
		'rentfetch_properties_location', // ID of the metabox.
		'Property Location', // Title of the metabox.
		'rentfetch_properties_location_metabox_callback', // Callback function to render the metabox.
		'properties', // Post type to add the metabox to.
		'normal', // Priority of the metabox.
		'default' // Context of the metabox.
	);

	add_meta_box(
		'rentfetch_properties_details', // ID of the metabox.
		'Property Display Information', // Title of the metabox.
		'rentfetch_properties_display_information_metabox_callback', // Callback function to render the metabox.
		'properties', // Post type to add the metabox to.
		'normal', // Priority of the metabox.
		'default' // Context of the metabox.
	);
}
add_action( 'add_meta_boxes', 'rentfetch_register_properties_details_metabox' );

/**
 * Markup for the properties identifiers metabox
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_identifiers_metabox_callback( $post ) {
	wp_nonce_field( 'rentfetch_properties_metabox_nonce', 'rentfetch_properties_metabox_nonce' );
	$array_disabled_fields = apply_filters( 'rentfetch_filter_property_syncing_fields', array(), $post->ID );

	?>
	<div class="rf-metabox rf-metabox-properties">
		
		<div class="columns columns-2">
		
			<?php
			// * Property Source
			$property_source = get_post_meta( $post->ID, 'property_source', true );
			$last_updated    = get_post_meta( $post->ID, 'updated', true );
			$api_error       = get_post_meta( $post->ID, 'api_error', true );
			if ( ! $property_source ) {
				$property_source = null;
			}
			?>
			
			<div class="field">
				<div class="column">
					<label for="property_source">Property Source</label>
				</div>
				<div class="column">
					<input disabled type="text" id="property_source" name="property_source" value="<?php echo esc_attr( $property_source ); ?>">
					<p class="description">This isn't a field meant to be edited; it's here to show you how this property is currently being managed (whether it syncs from a data source or it's manually managed).</p>
					<?php

					if ( $last_updated ) {
						printf( '<p class="description"><strong>Property metadata last updated:</strong> %s</p>', esc_html( $last_updated ) );
					}

					if ( $api_error ) {
						printf( '<p class="description"><strong>API response:</strong> %s</p>', wp_kses_post( $api_error ) );
					}

					?>
				</div>
			</div>
								
			
			<?php
			// * Property ID.
			$property_id = get_post_meta( $post->ID, 'property_id', true );
			$disabled    = in_array( 'property_id', $array_disabled_fields, true ) ? 'disabled' : '';

			// script to update the links in this area when the property ID changes.
			wp_enqueue_script( 'rentfetch-metabox-properties' );
			?>
			<div class="field">
				<div class="column">
					<label for="property_id">Property ID</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="property_id" name="property_id" value="<?php echo esc_attr( $property_id ); ?>">
					<p class="description">The Property ID should match the Property ID on each associated floorplan, and every property should always have a property ID.</p>
					<p class="description"><span id="view-related-floorplans"></span> <span id="view-related-units"></span></p>
				</div>
			</div>

			<?php
			// * Property Code
			$property_code = get_post_meta( $post->ID, 'property_code', true );
			?>
		</div>
	</div>
	<?php
}

/**
 * Properties location metabox callback
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_location_metabox_callback( $post ) {
	$array_disabled_fields = apply_filters( 'rentfetch_filter_property_syncing_fields', array(), $post->ID );
	?>
	<div class="rf-metabox rf-metabox-properties">
		
		<div class="columns columns-4">
		
			<?php
			// * Property Address
			$address  = get_post_meta( $post->ID, 'address', true );
			$disabled = in_array( 'address', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="address">Address</label>
				</div>
				<div class="column">
					<input <?php echo esc_attr( $disabled ); ?> type="text" id="address" name="address" value="<?php echo esc_attr( $address ); ?>">
				</div>
			</div>
			
			<?php
			// * Property City
			$city     = get_post_meta( $post->ID, 'city', true );
			$disabled = in_array( 'city', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="city">City</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="city" name="city" value="<?php echo esc_attr( $city ); ?>">
				</div>
			</div>
			
			<?php
			// * Property State
			$state    = get_post_meta( $post->ID, 'state', true );
			$disabled = in_array( 'state', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="state">State</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="state" name="state" value="<?php echo esc_attr( $state ); ?>">
				</div>
			</div>
			
			<?php
			// * Property Zipcode
			$zipcode  = get_post_meta( $post->ID, 'zipcode', true );
			$disabled = in_array( 'zipcode', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="zipcode">Zipcode</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="zipcode" name="zipcode" value="<?php echo esc_attr( $zipcode ); ?>">
				</div>
			</div>
		
		</div>
		
		<div class="columns columns-2">
		
			<?php
			// * Property Latitude
			$latitude = get_post_meta( $post->ID, 'latitude', true );
			$disabled = in_array( 'latitude', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="latitude">Latitude</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="latitude" name="latitude" value="<?php echo esc_attr( $latitude ); ?>">
				</div>
			</div>
			
			<?php
			// * Property Longitude
			$longitude = get_post_meta( $post->ID, 'longitude', true );
			$disabled  = in_array( 'longitude', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="longitude">Longitude</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="longitude" name="longitude" value="<?php echo esc_attr( $longitude ); ?>">
				</div>
			</div>
			
		</div>
	   
	</div>
	<?php
}

/**
 * Properties contact metabox callback
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_contact_metabox_callback( $post ) {
	$array_disabled_fields = apply_filters( 'rentfetch_filter_property_syncing_fields', array(), $post->ID );
	?>
	<div class="rf-metabox rf-metabox-properties">
		
		<div class="columns columns-3">
			
			<?php
			// * Property Email
			$email    = get_post_meta( $post->ID, 'email', true );
			$disabled = in_array( 'email', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="email">Email</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="email" name="email" value="<?php echo esc_attr( $email ); ?>">
				</div>
			</div>
			
			<?php
			// * Property Phone
			$phone    = get_post_meta( $post->ID, 'phone', true );
			$disabled = in_array( 'phone', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="phone">Phone</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="phone" name="phone" value="<?php echo esc_attr( $phone ); ?>">
				</div>
			</div>
			
			<?php
			// * Property URL
			$url      = get_post_meta( $post->ID, 'url', true );
			$disabled = in_array( 'url', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="url">URL</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="url" name="url" value="<?php echo esc_attr( $url ); ?>">
				</div>
			</div>
			
		</div>
		
	</div>
	<?php
}

/**
 * Properties display information metabox callback
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_display_information_metabox_callback( $post ) {
	$array_disabled_fields = apply_filters( 'rentfetch_filter_property_syncing_fields', array(), $post->ID );
	wp_enqueue_media();
	wp_enqueue_script( 'rentfetch-metabox-properties-images' );
	wp_enqueue_script( 'rentfetch-metabox-properties-tour' );
	wp_enqueue_script( 'rentfetch-metabox-properties-video' );
	?>
	<div class="rf-metabox rf-metabox-properties">
		<?php
		// * Property Images.
		?>
		<div class="field">
			<div class="column">
				<label for="images">Custom Images</label>
			</div>
			<div class="column"> 
				<p class="description">These are custom images added to the site, and are never synced. Any image here will override any synced images.</p>               
				<?php

				$images = get_post_meta( $post->ID, 'images', true );

				// convert to string.
				if ( is_array( $images ) ) {

					$images = array_filter(
						$images,
						function ( $image_id ) {
							return is_numeric( $image_id );
						}
					);

					$images = implode( ',', $images );
				}

				$images_ids_array = explode( ',', $images );

				echo '<input id="images" type="hidden" name="images" value="' . esc_attr( $images ) . '">';

				if ( $images ) {
					echo '<div id="gallery-container">';
					foreach ( $images_ids_array as $image_id ) {
						$attachment_url = wp_get_attachment_image_src( $image_id, 'thumbnail' );
						printf( '<div class="gallery-image" data-id="%s"><img src="%s"><button class="remove-image">Remove</button></div>', (int) $image_id, esc_url( $attachment_url[0] ) );
					}
					echo '</div>';
				}

				echo '<div id="gallery-container"></div>';
				echo '<input type="button" id="images_button" class="button" value="Add Images">';

				?>
				
			</div>
		</div>
		
		<?php

		$property_source = get_post_meta( $post->ID, 'property_source', true );
		if ( 'yardi' === $property_source ) {

			// * Property Images from Yardi
			$property_images_json = get_post_meta( $post->ID, 'yardi_property_images', true );
			$property_images      = json_decode( $property_images_json );
			?>
			 
			<div class="field">
				<div class="column">
					<label for="property_images">Yardi Property Images</label>
					<p class="description">These images are not editable, because they're from Yardi. This is merely a preview so that you can see the images being provided. Feel free to click 'download' on any of these so that you can easily grab any that you want if you're adding more.</p>
				</div>
				<div class="column">                
					<?php
					if ( $property_images ) {
						echo '<div class="property_images">';

						foreach ( $property_images as $property_image ) {

							if ( ! property_exists( $property_image, 'ImageURL' ) ) {
								printf( '<p>%s <em>An error typically indicates either a failed sync or a problem with API authentication. Be sure to look up your specific error code with the owner of this API.</em></p>', wp_json_encode( $property_images_json ) );
								continue;
							}

							$property_image_url = $property_image->ImageURL; // phpcs:ignore

							printf( '<div class="property-image"><img src="%s"/><a href="%s" target="_blank" class="download" download>Download</a></div>', esc_url( $property_image_url ), esc_url( $property_image_url ) );
						}
						echo '</div>';
					} else {
						echo '<p class="description">No images available</p>';
					}
					?>
					
				</div>
			</div>
			<?php
		}
		?>
			 
		<?php
		// * Property Description
		$description = get_post_meta( $post->ID, 'description', true );
		$disabled    = in_array( 'description', $array_disabled_fields, true ) ? 'disabled' : '';
		?>
		<div class="field">
			<div class="column">
				<label for="description">Description</label>
			</div>
			<div class="column">                
				<?php
					wp_editor(
						$description,
						'description',
						array(
							'textarea_name' => 'description',
							'textarea_rows' => 3,
							'media_buttons' => false,
						)
					);
				?>
				<p class="description">The description is synced from most APIs, but if yours is not, this is the main place to put general information about this property.</p>
			</div>
		</div>

		<?php
		// * Tour
		$tour = get_post_meta( $post->ID, 'tour', true );
		?>
		<div class="field">
			<div class="column">
				<label for="tour">Tour embed code (Matterport or Youtube iframe)</label>
			</div>
			<div class="column">
				<input type="text" id="tour" name="tour" value="<?php echo esc_attr( $tour ); ?>">
				<?php
				$iframe_code = '<iframe src="https://my.matterport.com/showcase-beta?m=VBHn8iJQ1h4" width="640" height="480" frameborder="0" allowfullscreen allow="vr"></iframe> or <iframe width="560" height="315" src="https://www.youtube.com/embed/C0DPdy98e4c?si=RltNyDXGANGUanKW" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
				?>

				<p class="description">Paste in a Matterport or Youtube iframe code. This code will look something like this: <?php echo esc_html( $iframe_code ); ?></p>
				<div id="tour-preview"></div>
			</div>
		</div>
				
		<?php
		// * Property Pets
		// $pets = get_post_meta( $post->ID, 'pets', true );
		?>
		<!-- <div class="field">
			<div class="column">
				<label for="pets">Pets</label>
			</div>
			<div class="column">
				<input type="text" id="pets" name="pets" value="<?php // echo esc_attr( $pets ); ?>">
			</div>
		</div> -->
		
		<?php
		// * Property Content Area
		$content_area = get_post_meta( $post->ID, 'content_area', true );
		?>
		<div class="field">
			<div class="column">
				<label for="content_area">Content area</label>
				<p class="description">The content area is always unsynced, so if you have more to say, you can say it here.</p>
			</div>
			<div class="column">
				<?php
				wp_editor(
					$content_area,
					'content_area',
					array(
						'textarea_name' => 'content_area',
						'media_buttons' => false,
						'textarea_rows' => 10,
						'teeny'         => false,
						'tinymce'       => true,
					)
				);
				?>
				<p class="description">It's always recommended to start this section with a heading level 2. If this is empty, the content area section of the single-properties template will not be displayed (there won't be a blank space). By default, if there's something to say here, this section will display below the amenities.</p>
			</div>
		</div>
		
	</div>
	
	<?php
}

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

	if ( isset( $_POST['property_id'] ) ) {
		update_post_meta( $post_id, 'property_id', sanitize_text_field( wp_unslash( $_POST['property_id'] ) ) );
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

	if ( isset( $_POST['images'] ) ) {
		$property_images = sanitize_text_field( wp_unslash( $_POST['images'] ) );
		$property_images = trim( $property_images, ',' );
		$property_images = explode( ',', $property_images );
		$property_images = array_unique( $property_images );

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