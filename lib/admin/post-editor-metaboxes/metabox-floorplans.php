<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function rentfetch_register_floorplans_details_metabox() {
		
	add_meta_box(
		'rentfetch_floorplans_identifiers', // ID of the metabox
		'Floorplan Identifiers', // Title of the metabox
		'rentfetch_floorplans_identifiers_metabox_callback', // Callback function to render the metabox
		'floorplans', // Post type to add the metabox to
		'normal', // Priority of the metabox
		'default' // Context of the metabox
	);
	
	add_meta_box(
		'rentfetch_floorplans_display', // ID of the metabox
		'Floorplan Display Information', // Title of the metabox
		'rentfetch_floorplans_display_metabox_callback', // Callback function to render the metabox
		'floorplans', // Post type to add the metabox to
		'normal', // Priority of the metabox
		'default' // Context of the metabox
	);
	
	add_meta_box(
		'rentfetch_floorplans_info', // ID of the metabox
		'Floorplan Information', // Title of the metabox
		'rentfetch_floorplans_info_metabox_callback', // Callback function to render the metabox
		'floorplans', // Post type to add the metabox to
		'normal', // Priority of the metabox
		'default' // Context of the metabox
	);
	
	add_meta_box(
		'rentfetch_floorplans_availability', // ID of the metabox
		'Floorplan Availability', // Title of the metabox
		'rentfetch_floorplans_availability_metabox_callback', // Callback function to render the metabox
		'floorplans', // Post type to add the metabox to
		'normal', // Priority of the metabox
		'default' // Context of the metabox
	);
		
}
add_action( 'add_meta_boxes', 'rentfetch_register_floorplans_details_metabox' );

function rentfetch_floorplans_identifiers_metabox_callback( $post ) {
	wp_nonce_field( 'rentfetch_floorplans_metabox_nonce', 'rentfetch_floorplans_metabox_nonce' );
	?>
	
	<div class="rf-metabox rf-metabox-floorplans">
		<div class="columns columns-4">
		
			<?php 
			//* Floorplan Source
			$floorplan_source = get_post_meta( $post->ID, 'floorplan_source', true ); 
			if ( !$floorplan_source )
				$floorplan_source = 'Manually managed';
			?>
			
			<div class="field">
				<div class="column">
					<label for="floorplan_source">Floorplan Source</label>
				</div>
				<div class="column">
					<input disabled type="text" id="floorplan_source" name="floorplan_source" value="<?php echo esc_attr( $floorplan_source ); ?>">
					<p class="description">This isn't a field meant to be edited; it's here to show you how this floorplan is currently being managed (whether it syncs from a data source or it's manually managed).</p>
				</div>
			</div>
			
			<?php 
			//* Property ID
			$property_id = get_post_meta( $post->ID, 'property_id', true ); ?>
			<div class="field">
				<div class="column">
					<label for="property_id">Property ID</label>
				</div>
				<div class="column">
					<input type="text" id="property_id" name="property_id" value="<?php echo esc_attr( $property_id ); ?>">
					
					<?php
					$args = array(
						'post_type' => 'properties',
						'posts_per_page' => 1,
						'post_status' => 'publish',
						'orderby' => 'title',
						'order' => 'ASC',
						'meta_query' => array(
							array(
								'key' => 'property_id',
								'value' => $property_id,
								'compare' => '=',
							)
						)
					);
					
					$query = new WP_Query( $args );
					
					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();
							$property_title = get_the_title();
							$property_link = get_the_permalink();
							$property_id = get_post_meta( get_the_ID(), 'property_id', true );
							printf( '<p class="description"><a target="_blank" href="%s">%s</a> (<a target="_blank" href="/wp-admin/post.php?post=%s&action=edit">edit</a>)</p>', $property_link, $property_title, get_the_ID() );
						}
					} else {
						echo '<p class="description">When this is filled out, just save and refresh the page to see a link to the associated property.</p>';
					}
					?>
					
				</div>
			</div>
			
			<?php 
			//* Floorplan ID
			$floorplan_id = get_post_meta( $post->ID, 'floorplan_id', true ); ?>
			<div class="field">
				<div class="column">
					<label for="floorplan_id">Floorplan ID</label>
				</div>
				<div class="column">
					<input type="text" id="floorplan_id" name="floorplan_id" value="<?php echo esc_attr( $floorplan_id ); ?>">
					<p class="description">The ID given by the API this floorplan comes from (if manual entry, please give it a unique identifier)</p>
				</div>
			</div>
								
			<?php 
			//* Unit Type Mapping
			// $unit_type_mapping = get_post_meta( $post->ID, 'unit_type_mapping', true ); ?>
			<!-- <input type="text" id="unit_type_mapping" name="unit_type_mapping" value="<?php // echo esc_attr( $unit_type_mapping ); ?>"> -->
			<?php
			$floorplan_id = get_post_meta( $post->ID, 'floorplan_id', true );
			$args = array(
				'post_type' => 'units',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'orderby' => 'title',
				'order' => 'ASC',
				'meta_query' => array(
					array(
						'key' => 'floorplan_id',
						'value' => $floorplan_id,
						'compare' => '=',
					)
				)
			);
			
			$query = new WP_Query( $args );
			
			if ( $query->have_posts() ) {
				echo '<div class="field">';
					echo '<div class="column">';
						echo '<label for="units">Units</label>';
					echo '</div>';
					echo '<div class="column">';
						echo '<ul class="unit-list">';
						printf( '<li><a href="/wp-admin/edit.php?s=%s&post_status=all&post_type=units" target="_blank">View related units</a></li>', $floorplan_id );
						while ( $query->have_posts() ) {
							$query->the_post();
							$unit_title = get_the_title();
							$unit_id = get_post_meta( get_the_ID(), 'unit_id', true );
							printf( '<li>%s (<a target="_blank" href="/wp-admin/post.php?post=%s&action=edit">edit</a>)</li>', $unit_title, get_the_ID() );
						}
						echo '</ul>';
					echo '</div>'; // .column
				echo '</div>'; // .field
			} else {
				// echo '<p class="description">When this is filled out, just save and refresh the page to see a link to the associated property.</p>';
			}
			?>			
		</div>
	</div>
	
	<?php
}

function rentfetch_floorplans_display_metabox_callback( $post ) {
	wp_enqueue_media();
	wp_enqueue_script( 'rentfetch-metabox-floorplans-images' );
	?>
	
	<div class="rf-metabox rf-metabox-floorplans">
		
		<?php //* Property Images ?>
		<div class="field">
			<div class="column">
				<label for="images">Manual Images</label>
			</div>
			<div class="column"> 
				<p class="description">These are custom images added to the site, and are never synced. Any image here will override any synced images.</p>               
				<?php
				
				$images = get_post_meta( $post->ID, 'manual_images', true );
				
				// convert to string
				if ( is_array( $images ) )
					$images = implode( ',', $images );
									
				$images_ids_array = explode( ',', $images );
				$image_url = '';
				
				echo '<input style="display: none;" type="text" id="images" name="images" value="' . esc_attr( $images ) . '">';
				
				if ( $images ) {
					echo '<div id="gallery-container">';
						foreach( $images_ids_array as $image_id ) {
							$attachment_url = wp_get_attachment_image_src( $image_id, 'thumbnail' );
							printf( '<div class="gallery-image" data-id="%s"><img src="%s"><button class="remove-image">Remove</button></div>', $image_id, $attachment_url[0] );
						}
					echo '</div>';
				}
				
				echo '<div id="gallery-container">' . $image_url . '</div>';                
				echo '<input type="button" id="images_button" class="button" value="Add Images">';
		
				?>
				
			</div>
		</div>
		
		<?php
		$floorplan_source = get_post_meta($post->ID, 'floorplan_source', true );
		if ( $floorplan_source == 'yardi' ) {
			
			//* Floorplan Images from Yardi
			$floorplan_images = get_post_meta( $post->ID, 'floorplan_image_url', true );
			
			// convert to array
			$floorplan_images = explode( ',', $floorplan_images );
			
			// $floorplan_images = json_decode( $floorplan_images );
			?>
			 
			<div class="field">
				<div class="column">
					<label for="floorplan_images">Yardi Floorplan Images</label>
					<p class="description">These images are not editable, because they're from Yardi. This is merely a preview so that you can see the images being provided. Feel free to click 'download' on any of these so that you can easily grab any that you want if you're adding more.</p>
				</div>
				<div class="column">                
					<?php
					if ( $floorplan_images ) {
						echo '<div class="floorplan_images">';
						foreach ( $floorplan_images as $floorplan_image ) {
							printf( '<div class="property-image"><img src="%s"/><a href="%s" target="_blank" class="download" download>Download</a></div>', $floorplan_image, $floorplan_image );                
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
		//* Floorplan Description
		$floorplan_description = get_post_meta( $post->ID, 'floorplan_description', true ); ?>
		<div class="field">
			<div class="column">
				<label for="floorplan_description">Floorplan Description</label>
			</div>
			<div class="column">
				<textarea rows="3" id="floorplan_description" name="floorplan_description"><?php echo esc_attr( $floorplan_description ); ?></textarea>
			</div>
		</div>
			
		<?php 
		//* Tour
		wp_enqueue_script( 'rentfetch-metabox-properties-tour' );
		$tour = get_post_meta( $post->ID, 'tour', true ); ?>
		<div class="field">
			<div class="column">
				<label for="tour">Tour embed code (Matterport or Youtube iframe)</label>
			</div>
			<div class="column">
				<input type="text" id="tour" name="tour" value="<?php echo esc_attr( $tour ); ?>">
				<?php 
				$iframeCode = '<iframe src="https://my.matterport.com/showcase-beta?m=VBHn8iJQ1h4" width="640" height="480" frameborder="0" allowfullscreen allow="vr"></iframe> or <iframe width="560" height="315" src="https://www.youtube.com/embed/C0DPdy98e4c?si=RltNyDXGANGUanKW" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
				$escapedIframeCode = htmlspecialchars($iframeCode);
				?>

				<p class="description">Paste in a Matterport or Youtube iframe code. This code will look something like this: <?php echo $escapedIframeCode; ?></p>
				<div id="tour-preview"></div>
			</div>
		</div>
	</div>
	
	<?php
}

function rentfetch_floorplans_info_metabox_callback( $post ) {
	?>
	
	<div class="rf-metabox rf-metabox-floorplans">
		<div class="columns columns-2">
			
			<?php 
			//* Beds
			$beds = get_post_meta( $post->ID, 'beds', true ); ?>
			<div class="field">
				<div class="column">
					<label for="beds">Beds</label>
				</div>
				<div class="column">
					<input type="text" id="beds" name="beds" value="<?php echo esc_attr( $beds ); ?>">
				</div>
			</div>
			
			<?php 
			//* Baths
			$baths = get_post_meta( $post->ID, 'baths', true ); ?>
			<div class="field">
				<div class="column">
					<label for="baths">Baths</label>
				</div>
				<div class="column">
					<input type="text" id="baths" name="baths" value="<?php echo esc_attr( $baths ); ?>">
				</div>
			</div>
		
		</div>
		
		<div class="columns columns-2">
			
			<?php 
			//* Minimum Deposit
			$minimum_deposit = get_post_meta( $post->ID, 'minimum_deposit', true ); ?>
			<div class="field">
				<div class="column">
					<label for="minimum_deposit">Minimum Deposit</label>
				</div>
				<div class="column">
					<input type="text" id="minimum_deposit" name="minimum_deposit" value="<?php echo esc_attr( $minimum_deposit ); ?>">
				</div>
			</div>
			
			<?php 
			//* Maximum Deposit
			$maximum_deposit = get_post_meta( $post->ID, 'maximum_deposit', true ); ?>
			<div class="field">
				<div class="column">
					<label for="maximum_deposit">Maximum Deposit</label>
				</div>
				<div class="column">
					<input type="text" id="maximum_deposit" name="maximum_deposit" value="<?php echo esc_attr( $maximum_deposit ); ?>">
				</div>
			</div>
		
		</div>
		
		<div class="columns columns-2">
			
			<?php 
			//* Minimum Rent
			$minimum_rent = get_post_meta( $post->ID, 'minimum_rent', true ); ?>
			<div class="field">
				<div class="column">
					<label for="minimum_rent">Minimum Rent</label>
				</div>
				<div class="column">
					<input type="text" id="minimum_rent" name="minimum_rent" value="<?php echo esc_attr( $minimum_rent ); ?>">
					<p class="description">Typically an API will set both the minimum and maximum numbers. The minimim is required for the pricing search to operate normally, so if you're entering information manually, use the minimum values.</p>
				</div>
			</div>
			
			<?php 
			//* Maximum Rent
			$maximum_rent = get_post_meta( $post->ID, 'maximum_rent', true ); ?>
			<div class="field">
				<div class="column">
					<label for="maximum_rent">Maximum Rent</label>
				</div>
				<div class="column">
					<input type="text" id="maximum_rent" name="maximum_rent" value="<?php echo esc_attr( $maximum_rent ); ?>">
				</div>
			</div>
			
		</div>
		
		<div class="columns columns-2">
		
			<?php 
			//* Minimum Sqrft
			$minimum_sqft = get_post_meta( $post->ID, 'minimum_sqft', true ); ?>
			<div class="field">
				<div class="column">
					<label for="minimum_sqft">Minimum Sqrft</label>
				</div>
				<div class="column">
					<input type="text" id="minimum_sqft" name="minimum_sqft" value="<?php echo esc_attr( $minimum_sqft ); ?>">
					<p class="description">Typically an API will set both the minimum and maximum numbers. The minimim is required for the square footage search to operate normally, so if you're entering information manually, use the minimum values.</p>
				</div>
			</div>
			
			<?php 
			//* Maximum Sqrft
			$maximum_sqft = get_post_meta( $post->ID, 'maximum_sqft', true ); ?>
			<div class="field">
				<div class="column">
					<label for="maximum_sqft">Maximum Sqrft</label>
				</div>
				<div class="column">
					<input type="text" id="maximum_sqft" name="maximum_sqft" value="<?php echo esc_attr( $maximum_sqft ); ?>">
				</div>
			</div>
			
		</div>
		
	</div>
	
	<?php
}

function rentfetch_floorplans_availability_metabox_callback( $post ) {
	?>
	
	<div class="rf-metabox rf-metabox-floorplans">
	
		<?php 
		//* Availability Date
		
		// enqueue jquery-ui datepicker so that this can be a datepicker
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-style' );
				
		$availability_date = get_post_meta( $post->ID, 'availability_date', true ); ?>
		
		<script>
			jQuery(document).ready(function($) {
				$('#availability_date').datepicker({
					dateFormat : 'yy-mm-dd'
				});
			});
		</script>
		<div class="field">
			<div class="column">
				<label for="availability_date">Availability Date</label>
			</div>
			<div class="column">
				<input type="text" id="availability_date" name="availability_date" value="<?php echo esc_attr( $availability_date ); ?>">
			</div>
		</div>
		
		<?php 
		//* Property Show Specials
		// $property_show_specials = get_post_meta( $post->ID, 'property_show_specials', true ); ?>
		<!-- <div class="field">
			<div class="column">
				<label for="property_show_specials">Property Show Specials</label>
			</div>
			<div class="column">
				<input type="checkbox" id="property_show_specials" name="property_show_specials" <?php // checked( $property_show_specials, '1' ); ?>>
			</div>
		</div> -->
						
		<?php 
		//* Has Specials
		$has_specials = get_post_meta( $post->ID, 'has_specials', true ); ?>
		<div class="field">
			<div class="column">
				<label for="has_specials">Has Specials</label>
			</div>
			<div class="column">
				<input type="checkbox" id="has_specials" name="has_specials" <?php checked( $has_specials, '1' ); ?>>
			</div>
		</div>
		
		<?php 
		//* Availability URL
		$availability_url = get_post_meta( $post->ID, 'availability_url', true ); ?>
		<div class="field">
			<div class="column">
				<label for="availability_url">Availability URL</label>
			</div>
			<div class="column">
				<input type="text" id="availability_url" name="availability_url" value="<?php echo esc_attr( $availability_url ); ?>">
			</div>
		</div>
		
		<?php 
		//* Available Units
		$available_units = get_post_meta( $post->ID, 'available_units', true ); ?>
		<div class="field">
			<div class="column">
				<label for="available_units">Available Units</label>
			</div>
			<div class="column">
				<input type="text" id="available_units" name="available_units" value="<?php echo esc_attr( $available_units ); ?>">
			</div>
		</div>
	
	</div>
	<?php
}

function rentfetch_save_floorplans_metaboxes( $post_id ) {
	
	if ( !isset( $_POST['rentfetch_floorplans_metabox_nonce'] ) )
		return;

	if ( ! wp_verify_nonce( $_POST['rentfetch_floorplans_metabox_nonce'], 'rentfetch_floorplans_metabox_nonce' ) )
		return;
	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	
	if ( isset( $_POST['property_id'] ) )
		update_post_meta( $post_id, 'property_id', sanitize_text_field( $_POST['property_id'] ) );
		
	if ( isset( $_POST['floorplan_source'] ) )
		update_post_meta( $post_id, 'floorplan_source', sanitize_text_field( $_POST['floorplan_source'] ) );
		
	if ( isset( $_POST['floorplan_id'] ) )
		update_post_meta( $post_id, 'floorplan_id', sanitize_text_field( $_POST['floorplan_id'] ) );
		
	if ( isset( $_POST['unit_type_mapping'] ) )
		update_post_meta( $post_id, 'unit_type_mapping', sanitize_text_field( $_POST['unit_type_mapping'] ) );
		
	if ( isset( $_POST['floorplan_image_url'] ) )
		update_post_meta( $post_id, 'floorplan_image_url', sanitize_text_field( $_POST['floorplan_image_url'] ) );
		
	if ( isset( $_POST['floorplan_description'] ) )
		update_post_meta( $post_id, 'floorplan_description', sanitize_text_field( $_POST['floorplan_description'] ) );
		
	if ( isset( $_POST['tour'] ) ) {
		
		$allowed_tags = array(
			'iframe' => [
				'src' => [],
				'width' => [],
				'height' => [],
				'frameborder' => [],
				'allowfullscreen' => [],
				'allow' => [],
			],
		);
		
		update_post_meta( $post_id, 'tour', wp_kses( $_POST['tour'], $allowed_tags ) );
		
	}
		
	if ( isset( $_POST['beds'] ) )
		update_post_meta( $post_id, 'beds', sanitize_text_field( $_POST['beds'] ) );
		
	if ( isset( $_POST['baths'] ) )
		update_post_meta( $post_id, 'baths', sanitize_text_field( $_POST['baths'] ) );
		
	if ( isset( $_POST['minimum_deposit'] ) )
		update_post_meta( $post_id, 'minimum_deposit', sanitize_text_field( $_POST['minimum_deposit'] ) );
		
	if ( isset( $_POST['maximum_deposit'] ) )
		update_post_meta( $post_id, 'maximum_deposit', sanitize_text_field( $_POST['maximum_deposit'] ) );
		
	if ( isset( $_POST['minimum_rent'] ) )
		update_post_meta( $post_id, 'minimum_rent', sanitize_text_field( $_POST['minimum_rent'] ) );
		
	if ( isset( $_POST['maximum_rent'] ) )
		update_post_meta( $post_id, 'maximum_rent', sanitize_text_field( $_POST['maximum_rent'] ) );
		
	if ( isset( $_POST['minimum_sqft'] ) )
		update_post_meta( $post_id, 'minimum_sqft', sanitize_text_field( $_POST['minimum_sqft'] ) );
		
	if ( isset( $_POST['maximum_sqft'] ) )
		update_post_meta( $post_id, 'maximum_sqft', sanitize_text_field( $_POST['maximum_sqft'] ) );
		
	if ( isset( $_POST['availability_date'] ) )
		update_post_meta( $post_id, 'availability_date', sanitize_text_field( $_POST['availability_date'] ) );
		
	if ( isset( $_POST['property_show_specials'] ) ) {
		update_post_meta( $post_id, 'property_show_specials', '1' );
	} else {
		delete_post_meta( $post_id, 'property_show_specials' );
	}
	
	if ( isset( $_POST['has_specials'] ) ) {
		update_post_meta( $post_id, 'has_specials', '1' );
	} else {
		delete_post_meta( $post_id, 'has_specials' );
	}
				
	if ( isset( $_POST['availability_url'] ) )
		update_post_meta( $post_id, 'availability_url', sanitize_text_field( $_POST['availability_url'] ) );
		
	if ( isset( $_POST['available_units'] ) )
		update_post_meta( $post_id, 'available_units', sanitize_text_field( $_POST['available_units'] ) );
		
	if ( isset( $_POST['images'] ) ) {
		$property_images = sanitize_text_field( $_POST['images'] );
		$property_images = trim($property_images, ",");
		$property_images = explode(",", $property_images);
		$property_images = array_unique( $property_images );
		
		update_post_meta( $post_id, 'manual_images', $property_images );
	}
}
add_action( 'save_post', 'rentfetch_save_floorplans_metaboxes' );