<?php
/**
 * Properties display information metabox callback
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_display_information_metabox_callback( $post ) {
	$array_disabled_fields = apply_filters( 'rentfetch_filter_property_syncing_fields', array(), $post->ID );
	wp_enqueue_script( 'rentfetch-metabox-properties-video' );
	?>
	<div class="rf-metabox rf-metabox-properties">
		<?php
		// * Property Description
		$description = get_post_meta( $post->ID, 'description', true );
		$disabled    = in_array( 'description', $array_disabled_fields, true );
		?>
		<div class="field">
			<div class="column">
				<label for="description">Description</label>
			</div>
			<div class="column">                
				<textarea
					id="description"
					name="description"
					class="wp-editor-area rf-property-lazy-editor"
					rows="3"
					data-rf-rich-editor
					<?php disabled( $disabled ); ?>
				><?php echo esc_textarea( $description ); ?></textarea>
				<p class="description">The description is synced from most APIs, but if yours is not, this is the main place to put general information about this property.</p>
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
				<textarea
					id="content_area"
					name="content_area"
					class="wp-editor-area rf-property-lazy-editor"
					rows="10"
					data-rf-rich-editor
				><?php echo esc_textarea( $content_area ); ?></textarea>
				<p class="description">It's always recommended to start this section with a heading level 2. If this is empty, the content area section of the single-properties template will not be displayed (there won't be a blank space). By default, if there's something to say here, this section will display below the amenities.</p>
			</div>
		</div>
		
	</div>
	
	<?php
}
