<?php
/**
 * This file sets up the metaboxes for the floorplans post type
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the metabox
 *
 * @return void
 */
function rentfetch_register_units_details_metabox() {
	
	// bail if we're not on the 'units' post type.
	if ( 'units' !== get_current_screen()->post_type ) {
		return;
	}

	add_meta_box(
		'rentfetch_units_hierarchy', // ID of the metabox.
		'Property Hierarchy', // Title of the metabox.
		'rentfetch_units_hierarchy_metabox_callback', // Callback function to render the metabox.
		'units', // Post type to add the metabox to.
		'normal', // Priority of the metabox.
		'default' // Context of the metabox.
	);

	add_meta_box(
		'rentfetch_units_identifiers', // ID of the metabox.
		'Unit Identifiers', // Title of the metabox.
		'rentfetch_units_identifiers_metabox_callback', // Callback function to render the metabox.
		'units', // Post type to add the metabox to.
		'normal', // Priority of the metabox.
		'default' // Context of the metabox.
	);

	add_meta_box(
		'rentfetch_unit_info', // ID of the metabox.
		'Unit Information', // Title of the metabox.
		'rentfetch_units_info_metabox_callback', // Callback function to render the metabox.
		'units', // Post type to add the metabox to.
		'normal', // Priority of the metabox.
		'default' // Context of the metabox.
	);

	add_meta_box(
		'rentfetch_units_availability', // ID of the metabox.
		'Unit Availability', // Title of the metabox.
		'rentfetch_units_availability_metabox_callback', // Callback function to render the metabox.
		'units', // Post type to add the metabox to.
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
				'rentfetch_units_api_response', // ID
				'API Response', // Title
				'rentfetch_units_api_response_metabox_callback', // Callback
				'units', // screen
				'normal', // context
				'default' // priority
			);
		}
	}
}

/**
 * Units identifiers callback
 *
 * @param object $post The post object.
 *
 * @return void
 */
function rentfetch_units_identifiers_metabox_callback( $post ) {
	wp_nonce_field( 'rentfetch_units_metabox_nonce', 'rentfetch_units_metabox_nonce' );
	$array_disabled_fields = apply_filters( 'rentfetch_filter_unit_syncing_fields', array(), $post->ID );
	?>
	
	<div class="rf-metabox rf-metabox-floorplans">
		<div class="columns columns-4">
		
			<?php
			// * Floorplan Source
			$unit_source = get_post_meta( $post->ID, 'unit_source', true );
			if ( ! $unit_source ) {
				$unit_source = 'Manually managed';
			}
			?>
			
			<div class="field">
				<div class="column">
					<label for="unit_source">Unit Source</label>
				</div>
				<div class="column">
					<input disabled type="text" id="unit_source" name="unit_source" value="<?php echo esc_attr( $unit_source ); ?>">
					<p class="description">Current syncing source, if any</p>
				</div>
			</div>
			
			<?php
			// * Property ID
			$property_id = get_post_meta( $post->ID, 'property_id', true );
			$disabled    = in_array( 'property_id', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="property_id">Property ID</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="property_id" name="property_id" value="<?php echo esc_attr( $property_id ); ?>">
					
					<?php
					$args = array(
						'post_type'      => 'properties',
						'posts_per_page' => 1,
						'post_status'    => 'publish',
						'orderby'        => 'title',
						'order'          => 'ASC',
						'meta_query'     => array( // phpcs:ignore
							array(
								'key'     => 'property_id',
								'value'   => $property_id,
								'compare' => '=',
							),
						),
					);

					$query = new WP_Query( $args );

					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();
							$property_title = get_the_title();
							$property_link  = get_the_permalink();
							$property_id    = get_post_meta( get_the_ID(), 'property_id', true );
							printf( '<p class="description"><a target="_blank" href="%s">%s</a> (<a target="_blank" href="/wp-admin/post.php?post=%s&action=edit">edit</a>)</p>', esc_url( $property_link ), esc_attr( $property_title ), (int) get_the_ID() );
						}
					} else {
						echo '<p class="description">When this is filled out, just save and refresh the page to see a link to the associated property.</p>';
					}
					?>
					
				</div>
			</div>
			
			<?php
			// * Floorplan ID
			$floorplan_id = get_post_meta( $post->ID, 'floorplan_id', true );
			$disabled     = in_array( 'floorplan_id', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="floorplan_id">Floorplan ID</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="floorplan_id" name="floorplan_id" value="<?php echo esc_attr( $floorplan_id ); ?>">
					<?php
					// get the floorplan title from the ID (this is the floorplan_id meta, not the post ID)
					$args = array(
						'post_type'      => 'floorplans',
						'posts_per_page' => 1,
						'post_status'    => 'publish',
						'orderby'        => 'title',
						'order'          => 'ASC',
						'meta_query'     => array( // phpcs:ignore
							array(
								'key'     => 'floorplan_id',
								'value'   => $floorplan_id,
								'compare' => '=',
							),
						),
					);
					$query = new WP_Query( $args );
					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();
							$floorplan_title = get_the_title();
							$floorplan_link  = get_the_permalink();
							$floorplan_id    = get_post_meta( get_the_ID(), 'floorplan_id', true );
							printf( '<p class="description"><a target="_blank" href="%s">%s</a> (<a target="_blank" href="/wp-admin/post.php?post=%s&action=edit">edit</a>)</p>', esc_url( $floorplan_link ), esc_attr( $floorplan_title ), (int) get_the_ID() );
						}
					} else {
						echo '<p class="description">When this is filled out, just save and refresh the page to see a link to the associated floorplan.</p>';
					}
					?>					
				</div>
			</div>
			
			<?php
			// * Unit ID
			$unit_id = get_post_meta( $post->ID, 'unit_id', true );
			$disabled     = in_array( 'unit_id', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="unit_id">Unit ID</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="unit_id" name="unit_id" value="<?php echo esc_attr( $unit_id ); ?>">
					<p class="description">Unique identifier, typically not the same as the display title.</p>
				</div>
			</div>
		</div>
	</div>
	
	<?php
}

/**
 * Units hierarchy metabox callback
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_units_hierarchy_metabox_callback( $post ) {
	rentfetch_render_hierarchy( $post, 'units' );
}

/**
 * Floorplans info metabox callback
 *
 * @param object $post The post object.
 *
 * @return void
 */
function rentfetch_units_info_metabox_callback( $post ) {
	$array_disabled_fields = apply_filters( 'rentfetch_filter_unit_syncing_fields', array(), $post->ID );
	?>
	
	<div class="rf-metabox rf-metabox-floorplans">
		<div class="columns columns-3">
			
			<?php
			// * Beds
			$beds     = get_post_meta( $post->ID, 'beds', true );
			$disabled = in_array( 'beds', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="beds">Beds</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="beds" name="beds" value="<?php echo esc_attr( $beds ); ?>">
				</div>
			</div>
			
			<?php
			// * Baths
			$baths    = get_post_meta( $post->ID, 'baths', true );
			$disabled = in_array( 'beds', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="baths">Baths</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="baths" name="baths" value="<?php echo esc_attr( $baths ); ?>">
				</div>
			</div>
			
			<?php
			// * Sqrft
			$sqrft = get_post_meta( $post->ID, 'sqrft', true );
			$disabled     = in_array( 'sqrft', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="sqrft">Sqrft</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="sqrft" name="sqrft" value="<?php echo esc_attr( $sqrft ); ?>">
				</div>
			</div>
			
			<?php
			// * Building Name
			$building_name = get_post_meta( $post->ID, 'building_name', true );
			$disabled     = in_array( 'building_name', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="building_name">Building</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="building_name" name="building_name" value="<?php echo esc_attr( $building_name ); ?>">
				</div>
			</div>
			
			<?php
			// * Floor Number
			$floor_number = get_post_meta( $post->ID, 'floor_number', true );
			$disabled     = in_array( 'floor_number', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="floor_number">Floor #</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="floor_number" name="floor_number" value="<?php echo esc_attr( $floor_number ); ?>">
				</div>
			</div>

			<?php
			// * Amenities (comma separated list)
			$amenities = get_post_meta( $post->ID, 'amenities', true );
			$disabled  = in_array( 'amenities', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="amenities">Amenities</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="amenities" name="amenities" value="<?php echo esc_attr( $amenities ); ?>" placeholder="e.g. Pool, Fitness Center, Pet Friendly">
					<p class="description">Comma-separated list used in unit displays.</p>
				</div>
			</div>
		
		</div>
		
		<div class="columns columns-3">
			
			<?php
			// * Minimum Rent
			$minimum_rent = get_post_meta( $post->ID, 'minimum_rent', true );
			$disabled     = in_array( 'minimum_rent', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="minimum_rent">Minimum Rent</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="minimum_rent" name="minimum_rent" value="<?php echo esc_attr( $minimum_rent ); ?>">
					<p class="description">On the unit level, either a minimum or maximum rent value will suffice.</p>
				</div>
			</div>
			
			<?php
			// * Maximum Rent
			$maximum_rent = get_post_meta( $post->ID, 'maximum_rent', true );
			$disabled     = in_array( 'maximum_rent', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="maximum_rent">Maximum Rent</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="maximum_rent" name="maximum_rent" value="<?php echo esc_attr( $maximum_rent ); ?>">
				</div>
			</div>
			
			<?php
			// * Deposit
			$deposit = get_post_meta( $post->ID, 'deposit', true );
			$disabled        = in_array( 'deposit', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="deposit">Deposit</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="deposit" name="deposit" value="<?php echo esc_attr( $deposit ); ?>">
				</div>
			</div>
			
		</div>
	</div>
	
	<?php
}

/**
 * The Floorplans Availability Metabox
 *
 * @param object $post The post object.
 *
 * @return void
 */
function rentfetch_units_availability_metabox_callback( $post ) {
	$array_disabled_fields = apply_filters( 'rentfetch_filter_unit_syncing_fields', array(), $post->ID );
	?>
	
	<div class="rf-metabox rf-metabox-floorplans">
	
		<?php
		// * Availability Date

		// enqueue jquery-ui datepicker so that this can be a datepicker.
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'rentfetch-jquery-style' );

		$availability_date = get_post_meta( $post->ID, 'availability_date', true );
		$disabled          = in_array( 'availability_date', $array_disabled_fields, true ) ? 'disabled' : '';
		?>
		
		<script>
			jQuery(document).ready(function($) {
				$('#availability_date:not(disabled)').datepicker({
					dateFormat : 'yy-mm-dd'
				});
			});
		</script>
		<div class="field">
			<div class="column">
				<label for="availability_date">Availability Date</label>
			</div>
			<div class="column">
				<input type="text" <?php echo esc_attr( $disabled ); ?> id="availability_date" name="availability_date" value="<?php echo esc_attr( $availability_date ); ?>">
			</div>
		</div>

		<?php
		// * Apply Online URL
		$apply_online_url = get_post_meta( $post->ID, 'apply_online_url', true );
		$disabled         = in_array( 'apply_online_url', $array_disabled_fields, true ) ? 'disabled' : '';
		?>
		<div class="field">
			<div class="column">
				<label for="apply_online_url">Apply Online URL</label>
			</div>
			<div class="column">
				<input type="text" <?php echo esc_attr( $disabled ); ?> id="apply_online_url" name="apply_online_url" value="<?php echo esc_attr( $apply_online_url ); ?>">
			</div>
		</div>
			
	</div>
	<?php
}

/**
 * Save the floorplans metaboxes
 *
 * @param int $post_id The post ID.
 *
 * @return void
 */
function rentfetch_save_units_metaboxes( $post_id ) {

	if ( ! isset( $_POST['rentfetch_units_metabox_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rentfetch_units_metabox_nonce'] ) ), 'rentfetch_units_metabox_nonce' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( isset( $_POST['property_id'] ) ) {
		update_post_meta( $post_id, 'property_id', sanitize_text_field( wp_unslash( $_POST['property_id'] ) ) );
	}

	if ( isset( $_POST['floorplan_id'] ) ) {
		update_post_meta( $post_id, 'floorplan_id', sanitize_text_field( wp_unslash( $_POST['floorplan_id'] ) ) );
	}

	if ( isset( $_POST['unit_id'] ) ) {
		update_post_meta( $post_id, 'unit_id', sanitize_text_field( wp_unslash( $_POST['unit_id'] ) ) );
	}

	if ( isset( $_POST['beds'] ) ) {
		update_post_meta( $post_id, 'beds', sanitize_text_field( wp_unslash( $_POST['beds'] ) ) );
	}

	if ( isset( $_POST['baths'] ) ) {
		update_post_meta( $post_id, 'baths', sanitize_text_field( wp_unslash( $_POST['baths'] ) ) );
	}

	if ( isset( $_POST['sqrft'] ) ) {
		update_post_meta( $post_id, 'sqrft', sanitize_text_field( wp_unslash( $_POST['sqrft'] ) ) );
	}

	if ( isset( $_POST['minimum_rent'] ) ) {
		update_post_meta( $post_id, 'minimum_rent', sanitize_text_field( wp_unslash( $_POST['minimum_rent'] ) ) );
	}

	if ( isset( $_POST['maximum_rent'] ) ) {
		update_post_meta( $post_id, 'maximum_rent', sanitize_text_field( wp_unslash( $_POST['maximum_rent'] ) ) );
	}

	if ( isset( $_POST['deposit'] ) ) {
		update_post_meta( $post_id, 'deposit', sanitize_text_field( wp_unslash( $_POST['deposit'] ) ) );
	}

	if ( isset( $_POST['availability_date'] ) ) {
		update_post_meta( $post_id, 'availability_date', sanitize_text_field( wp_unslash( $_POST['availability_date'] ) ) );
	}

	if ( isset( $_POST['apply_online_url'] ) ) {
		update_post_meta( $post_id, 'apply_online_url', esc_url_raw( wp_unslash( $_POST['apply_online_url'] ) ) );
	}
	
	if ( isset( $_POST['floor_number'] ) ) {
		update_post_meta( $post_id, 'floor_number', sanitize_text_field( wp_unslash( $_POST['floor_number'] ) ) );
	}
	
	if ( isset( $_POST['building_name'] ) ) {
		update_post_meta( $post_id, 'building_name', sanitize_text_field( wp_unslash( $_POST['building_name'] ) ) );
	}

	if ( isset( $_POST['amenities'] ) ) {
		update_post_meta( $post_id, 'amenities', sanitize_text_field( wp_unslash( $_POST['amenities'] ) ) );
	}
	
}
add_action( 'save_post', 'rentfetch_save_units_metaboxes' );


/**
 * Render the Units API Response metabox. Displays structured api_response post meta when present.
 *
 * @param WP_Post $post The post object.
 * @return void
 */
function rentfetch_units_api_response_metabox_callback( $post ) {
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
					$formatted = rentfetch_pretty_json( $subvalue );
					echo '<div class="json-content">';
					printf( '<textarea class="rentfetch-api-response-json" readonly rows="10" style="width:100%%;">%s</textarea>', esc_textarea( $formatted ) );
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