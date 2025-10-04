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
		'rentfetch_properties_fees', // ID of the metabox.
		'Property Fees', // Title of the metabox.
		'rentfetch_properties_fees_metabox_callback', // Callback function to render the metabox.
		'properties', // Post type to add the metabox to.
		'normal', // Priority of the metabox.
		'default' // Context of the metabox.
	);

	add_meta_box(
		'rentfetch_properties_images', // ID of the metabox.
		'Property Images', // Title of the metabox.
		'rentfetch_properties_images_metabox_callback', // Callback function to render the metabox.
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

	// Conditionally add an API Response metabox when this post has an `api_response` meta value.
	global $post;
	$post_id = 0;
	if ( isset( $_GET['post'] ) ) {
		$post_id = (int) $_GET['post'];
	} elseif ( isset( $_GET['post_ID'] ) ) {
		$post_id = (int) $_GET['post_ID'];
	} elseif ( is_object( $post ) && isset( $post->ID ) ) {
		$post_id = (int) $post->ID;
	}

	if ( $post_id ) {
		$api_response = get_post_meta( $post_id, 'api_response', true );
		if ( ! empty( $api_response ) ) {
			add_meta_box(
				'rentfetch_properties_api_response', // ID
				'API Response', // Title
				'rentfetch_properties_api_response_metabox_callback', // Callback
				'properties', // screen
				'normal', // context
				'default' // priority
			);
		}
	}
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
		
		<div class="columns columns-2">
			
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
			
		</div>
		
		<div class="columns columns-3">
			
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
			
			<?php
			// * Property URL
			$url      = get_post_meta( $post->ID, 'url_override', true );
			$disabled = in_array( 'url_override', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="url">URL override</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="url_override" name="url_override" value="<?php echo esc_attr( $url ); ?>">
					<p class="description">Some APIs don't allow for full control. Override the synced URL here.</p>
				</div>
			</div>
			
			<?php
			// * Tour Booking Link
			$tour_booking_link = get_post_meta( $post->ID, 'tour_booking_link', true );
			$disabled          = in_array( 'tour_booking_link', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="tour_booking_link">Tour Booking Link</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="tour_booking_link" name="tour_booking_link" value="<?php echo esc_attr( $tour_booking_link ); ?>">
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
	wp_enqueue_script( 'rentfetch-metabox-properties-tour' );
	wp_enqueue_script( 'rentfetch-metabox-properties-video' );
	?>
	<div class="rf-metabox rf-metabox-properties">
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
				<label for="tour">Tour Link (Youtube or Matterport)</label>
			</div>
			<div class="column">
				<input type="text" id="tour" name="tour" value="<?php echo esc_attr( $tour ); ?>">
				<p class="description">Example: https://my.matterport.com/show/?m=sc3ykepsN4s</p>
				<div id="tour-preview"></div>
			</div>
		</div>

		<?php
		// * Has Specials
		$has_specials = get_post_meta( $post->ID, 'has_specials', true );
		$disabled     = in_array( 'has_specials', $array_disabled_fields, true ) ? 'disabled' : '';
		?>
		<div class="field">
			<div class="column">
				<label for="has_specials">Has Specials</label>
			</div>
			<div class="column">
				<input type="checkbox" <?php echo esc_attr( $disabled ); ?> id="has_specials" name="has_specials" <?php checked( $has_specials, '1' ); ?>>
			</div>
		</div>
		
		<?php
		// * Specials override text
		$specials_override_text = get_post_meta( $post->ID, 'specials_override_text', true );
		?>
		<div class="field">
			<div class="column">
				<label for="specials_override_text">Specials Override Text</label>
				<p class="description">Manually customize the specials text displayed. This will not sync with any specials in your PMS and will override what's in the PMS.</p>
			</div>
			<div class="column">
				<input type="text" id="specials_override_text" name="specials_override_text" maxlength="25" value="<?php echo esc_attr( $specials_override_text ); ?>">
				<p class="description"><em>Maximum 25 characters</em></p>
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
 * Markup for the properties fees metabox
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_fees_metabox_callback( $post ) {
	wp_nonce_field( 'rentfetch_properties_metabox_nonce', 'rentfetch_properties_metabox_nonce' );
	
	// Get current fees data
	$property_fees_data = get_post_meta( $post->ID, 'property_fees_data', true );
	if ( ! is_array( $property_fees_data ) ) {
		$property_fees_data = array();
	}
	
	?>
	<div class="rf-metabox rf-metabox-properties">
		
		<?php
		// * Property Fees Embed
		$property_fees_embed = get_post_meta( $post->ID, 'property_fees_embed', true );
		?>
		<div class="field">
			<div class="column">
				<label for="property_fees_embed">Property Fees Embed Code</label>
			</div>
			<div class="column">
				<textarea id="property_fees_embed" name="property_fees_embed" rows="5" style="width:100%;"><?php echo esc_textarea( $property_fees_embed ); ?></textarea>
				<p class="description">Paste in your embed code for property fees. This can include script tags, iframes, etc. Please ensure the code is from a trusted source.</p>
			</div>
		</div>

		<?php
		// * Property Fees CSV Upload
		?>
		<div class="field">
			<div class="column">
				<label for="property_fees_csv">Property Fees CSV Upload</label>
				<p class="description">Upload a CSV file with property fees data. This will replace any existing fees data. <a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=rentfetch_download_fees_csv_sample' ) ); ?>" download="property_fees_sample.csv">Download sample CSV</a></p>
				<p class="description">
					<button type="button" id="download-current-fees" class="button button-secondary" data-property-title="<?php echo esc_attr( get_the_title( $post->ID ) ); ?>" data-property-id="<?php echo esc_attr( get_post_meta( $post->ID, 'property_id', true ) ); ?>">Download Current Data</button>
				</p>
			</div>
			<div class="column">
				<input type="file" id="property_fees_csv" name="property_fees_csv" accept=".csv" data-post-id="<?php echo intval( $post->ID ); ?>" />
				<p class="description">CSV columns: description, price, frequency, notes, category</p>
			</div>
			
			<?php
			// * Property Fees JSON
			?>
			<div class="column">
				<label for="property_fees_json">Property Fees JSON</label>
				<p class="description">Current fees data in JSON format. You can edit this directly and save changes with the post.</p>
				<p class="description">
					<button type="button" id="copy-fees-json" class="button button-secondary">Copy Fees JSON</button>
				</p>
			</div>
			<div class="column">
				<div class="json-content">
					<textarea class="rentfetch-fees-json" name="property_fees_json" rows="10" style="width:100%; white-space: pre; word-wrap: normal; overflow-x: auto;"><?php echo esc_textarea( wp_json_encode( $property_fees_data, JSON_PRETTY_PRINT ) ); ?></textarea>
				</div>
			</div>
		</div>
		
	</div>
	
	<?php
}

/**
 * Markup for the properties images metabox
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_images_metabox_callback( $post ) {
	wp_nonce_field( 'rentfetch_properties_metabox_nonce', 'rentfetch_properties_metabox_nonce' );
	wp_enqueue_media();
	wp_enqueue_script( 'rentfetch-metabox-properties-images' );
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
						printf( '<div class="gallery-image" data-id="%s"><img loading="lazy" style="background-color: #f7f7f7;" width="150" height="82" src="%s"><button class="remove-image">Remove</button></div>', (int) $image_id, esc_url( $attachment_url[0] ) );
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
		if ( 'yardi' === $property_source || 'entrata' === $property_source ) {

			// Property Images from Yardi
			if ( 'yardi' === $property_source ) {
				$property_images = rentfetch_get_property_images_yardi( null );
			}
			
			// Property images from Entrata
			if ( 'entrata' === $property_source ) {
				$property_images = rentfetch_get_property_images_entrata( null );
			}

			?>
			 
			<div class="field">
				<div class="column">
					<label for="property_images">Synced Property Images</label>
					<p class="description">These images are not editable, because they're from your API. This just shows you a preview so that you can see the images being provided. Feel free to click 'download' on any of these so that you can easily grab any that you want if you're adding more.</p>
				</div>
				<div class="column">                
					<?php
					if ( $property_images ) {
						echo '<div class="property_images">';

						foreach ( $property_images as $property_image ) {
							
							if ( isset( $property_image['url'] ) ) {
								$property_image_url = $property_image['url'];
							}

							printf( '<div class="property-image"><img width="150" height="82" loading="lazy" style="transform: translateZ(0); will-change: transform;" src="%s"/><a href="%s" target="_blank" class="download" download>Download</a></div>', esc_url( $property_image_url ), esc_url( $property_image_url ) );
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
	
	if ( isset( $_POST['url_override'] ) ) {
		update_post_meta( $post_id, 'url_override', esc_url_raw( wp_unslash( $_POST['url_override'] ) ) );
	}

	if ( isset( $_POST['tour_booking_link'] ) ) {
		update_post_meta( $post_id, 'tour_booking_link', sanitize_text_field( wp_unslash( $_POST['tour_booking_link'] ) ) );
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
							'price'       => floatval( $fee['price'] ?? 0 ),
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

	// Handle CSV upload for property fees
	if ( isset( $_FILES['property_fees_csv'] ) && ! empty( $_FILES['property_fees_csv']['tmp_name'] ) ) {
		$csv_file = $_FILES['property_fees_csv'];
		
		// Check if it's a valid CSV file
		$file_type = wp_check_filetype( $csv_file['name'] );
		if ( 'csv' !== $file_type['ext'] ) {
			// Not a CSV file, skip processing
			error_log( 'Invalid file type for property fees CSV upload' );
		} else {
			// Process the CSV file
			$fees_data = array();
			
			if ( ( $handle = fopen( $csv_file['tmp_name'], 'r' ) ) !== false ) {
				$header = fgetcsv( $handle, 1000, ',' );
				
				// Validate header
				$expected_header = array( 'description', 'price', 'frequency', 'notes', 'category' );
				if ( $header === $expected_header ) {
					while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
						if ( count( $data ) === 5 ) {
							$fees_data[] = array(
								'description' => sanitize_text_field( $data[0] ),
								'price'       => floatval( $data[1] ),
								'frequency'   => sanitize_text_field( $data[2] ),
								'notes'       => sanitize_text_field( $data[3] ),
								'category'    => sanitize_text_field( $data[4] ),
							);
						}
					}
				}
				
				fclose( $handle );
			}
			
			// Save the processed fees data
			update_post_meta( $post_id, 'property_fees_data', $fees_data );
		}
	}

	if ( isset( $_POST['has_specials'] ) ) {
		update_post_meta( $post_id, 'has_specials', '1' );
	} else {
		delete_post_meta( $post_id, 'has_specials' );
	}

	if ( isset( $_POST['specials_override_text'] ) ) {
		update_post_meta( $post_id, 'specials_override_text', sanitize_text_field( wp_unslash( $_POST['specials_override_text'] ) ) );
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


/**
 * Render the API Response metabox. Displays structured api_response post meta when present.
 *
 * @param WP_Post $post The post object.
 * @return void
 */
function rentfetch_properties_api_response_metabox_callback( $post ) {
	$api_response = get_post_meta( $post->ID, 'api_response', true );

	if ( ! is_array( $api_response ) ) {
		$api_response = array();
	}

	echo '<div class="rf-metabox rf-metabox-api-response">';

	foreach ( $api_response as $key => $value ) {
		echo '<div class="api-response">';
		printf( '<h3 style="margin-top: 0;">%s</h3>', esc_html( $key ) );

		if ( is_array( $value ) ) {
			foreach ( $value as $subkey => $subvalue ) {
					if ( 'api_response' === $subkey ) {
						// Use the shared JSON utility to pretty-print or lightly repair the value.
						$formatted = rentfetch_pretty_json( $subvalue, $repaired );

						echo '<div class="json-content">';
						// Always display in code editor for syntax highlighting
						printf( '<textarea class="rentfetch-api-response-json" readonly rows="20" style="width:100%%; white-space: pre; word-wrap: normal; overflow-x: auto;">%s</textarea>', esc_textarea( $formatted ) );
						if ( $repaired ) {
							echo '<p style="color: #856404; background: #fff3cd; padding: 5px; border: 1px solid #ffeaa7; margin-top: 5px;">Note: This JSON was automatically repaired for display.</p>';
						}
						echo '</div>';
					} else {
						printf( '<p>%s</p>', esc_html( $subvalue ) );
					}
			}
		} else {
			echo esc_html( $value );
		}

		echo '</div>';
	}

	echo '</div>';

}


/**
 * Enqueue admin scripts/styles for API response code editor.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function rentfetch_enqueue_api_response_editor_assets( $hook ) {
	// Only load on post edit screens.
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	// Only enqueue on properties, floorplans, or units edit screens.
	if ( ! in_array( $screen->post_type, array( 'properties', 'floorplans', 'units' ), true ) ) {
		return;
	}

	// Ensure wp.codeEditor is available and get settings so WP can enqueue required addons.
	$settings = wp_enqueue_code_editor( array( 'type' => 'application/json' ) );

	// Fallback settings if enqueue didn't return anything.
	if ( false === $settings ) {
		$settings = array();
	}

	// Enqueue the script handle registered in lib/initialization/enqueue.php and localize the settings.
	wp_enqueue_script( 'rentfetch-api-response-editor' );

	// Make the settings available to our script so it uses the same assets/addons WP enqueued.
	wp_localize_script( 'rentfetch-api-response-editor', 'rentfetchCodeEditorSettings', $settings );
	
	// Enqueue JSON handling script
	wp_enqueue_script( 'rentfetch-properties-fees-json-handling', plugins_url( 'js/rentfetch-properties-fees-json-handling.js', dirname( __FILE__, 3 ) ), array( 'rentfetch-api-response-editor' ), '1.0.0', true );
	
	// Localize settings for the JSON handling script as well
	wp_localize_script( 'rentfetch-properties-fees-json-handling', 'rentfetchCodeEditorSettings', $settings );
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_api_response_editor_assets' );

/**
 * Enqueue CSV upload script for property fees
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function rentfetch_enqueue_csv_upload_script( $hook ) {
	// Only load on post edit screens.
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'properties' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_script( 'rentfetch-properties-fees-csv-upload', plugins_url( 'js/rentfetch-properties-fees-csv-upload.js', dirname( __FILE__, 3 ) ), array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'rentfetch-properties-fees-csv-download-current', plugins_url( 'js/rentfetch-properties-fees-csv-download-current.js', dirname( __FILE__, 3 ) ), array( 'jquery' ), '1.0.0', true );
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_csv_upload_script' );

/**
 * AJAX handler to download sample fees CSV
 */
function rentfetch_download_fees_csv_sample() {
	
	// Set headers for CSV download
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=property_fees_sample.csv' );
	
	// Create sample CSV content
	$csv_content = "description,price,frequency,notes,category\n";
	$csv_content .= "Application Fee,50,one-time,Required for all applicants,Application\n";
	$csv_content .= "Security Deposit,500,one-time,Refundable at move-out,Deposit\n";
	$csv_content .= "\"Pet Deposit\",300,one-time,\"Per pet, required for pets\",Pets\n";
	$csv_content .= "Monthly Rent,1200,monthly,Due on the 1st of each month,Rent\n";
	$csv_content .= "Pet Rent,25,monthly,Per pet,Pets\n";
	
	echo $csv_content;
	exit;
}
add_action( 'wp_ajax_rentfetch_download_fees_csv_sample', 'rentfetch_download_fees_csv_sample' );

/**
 * AJAX handler to download current fees data as CSV
 */
function rentfetch_download_current_fees_csv() {
	
	// Verify nonce for security
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rentfetch_properties_metabox_nonce' ) ) {
		wp_die( 'Security check failed' );
	}

	// Get the post ID
	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	
	if ( ! $post_id ) {
		wp_die( 'Invalid post ID' );
	}

	// Get current fees data
	$fees_data = get_post_meta( $post_id, 'property_fees_data', true );
	if ( ! is_array( $fees_data ) ) {
		$fees_data = array();
	}

	// Set headers for CSV download
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=property_fees_current.csv' );
	
	// Create CSV content
	$csv_content = "description,price,frequency,notes,category\n";
	
	foreach ( $fees_data as $fee ) {
		$csv_content .= sprintf(
			"\"%s\",%s,\"%s\",\"%s\",\"%s\"\n",
			str_replace( '"', '""', $fee['description'] ?? '' ),
			$fee['price'] ?? 0,
			str_replace( '"', '""', $fee['frequency'] ?? '' ),
			str_replace( '"', '""', $fee['notes'] ?? '' ),
			str_replace( '"', '""', $fee['category'] ?? '' )
		);
	}
	
	echo $csv_content;
	exit;
}
add_action( 'wp_ajax_rentfetch_download_current_fees_csv', 'rentfetch_download_current_fees_csv' );

/**
 * AJAX handler to upload and process fees CSV
 */
function rentfetch_upload_fees_csv() {
	
	// Verify nonce for security
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rentfetch_properties_metabox_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed' ) );
	}

	// Get the post ID
	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	
	if ( ! $post_id ) {
		wp_send_json_error( array( 'message' => 'Invalid post ID' ) );
	}

	// Check if file was uploaded
	if ( ! isset( $_FILES['csv_file'] ) || empty( $_FILES['csv_file']['tmp_name'] ) ) {
		wp_send_json_error( array( 'message' => 'No file uploaded' ) );
	}

	$csv_file = $_FILES['csv_file'];
	
	// Check if it's a valid CSV file
	$file_type = wp_check_filetype( $csv_file['name'] );
	if ( 'csv' !== $file_type['ext'] ) {
		wp_send_json_error( array( 'message' => 'Invalid file type. Please upload a CSV file.' ) );
	}

	// Process the CSV file
	$fees_data = array();
	
	if ( ( $handle = fopen( $csv_file['tmp_name'], 'r' ) ) !== false ) {
		$header = fgetcsv( $handle, 1000, ',' );
		
		// Validate header
		$expected_header = array( 'description', 'price', 'frequency', 'notes', 'category' );
		if ( $header !== $expected_header ) {
			fclose( $handle );
			wp_send_json_error( array( 'message' => 'Invalid CSV format. Expected columns: description, price, frequency, notes, category' ) );
		}

		while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
			if ( count( $data ) === 5 ) {
				$fees_data[] = array(
					'description' => wp_kses_post( $data[0] ), // Allow basic HTML but sanitize
					'price'       => (string) trim( $data[1] ), // Keep price as string to preserve formatting like $ signs, trim whitespace
					'frequency'   => sanitize_text_field( $data[2] ),
					'notes'       => wp_kses_post( $data[3] ), // Allow basic HTML but sanitize
					'category'    => sanitize_text_field( $data[4] ),
				);
			}
		}
		
		fclose( $handle );
	} else {
		wp_send_json_error( array( 'message' => 'Could not read CSV file' ) );
	}
	
	// Save the processed fees data
	update_post_meta( $post_id, 'property_fees_data', $fees_data );
	
	// Return the updated JSON
	wp_send_json_success( array( 
		'json' => wp_json_encode( $fees_data, JSON_PRETTY_PRINT ),
		'count' => count( $fees_data )
	) );
}
add_action( 'wp_ajax_rentfetch_upload_fees_csv', 'rentfetch_upload_fees_csv' );