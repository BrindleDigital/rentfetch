<?php
/**
 * Floor plan availability fields.
 *
 * @package rentfetch
 */

/**
 * Render floor plan availability and specials fields.
 *
 * @param WP_Post $post Floor plan post.
 * @return void
 */
function rentfetch_floorplans_availability_metabox_callback( $post ) {
	$disabled_fields = apply_filters( 'rentfetch_filter_floorplan_syncing_fields', array(), $post->ID );
	?>
	<div class="rf-metabox rf-metabox-floorplans">
		<script>
			jQuery(function($) {
				$('#availability_date:not([disabled])').datepicker({
					dateFormat: 'yy-mm-dd'
				});
			});
		</script>
		<div class="field">
			<div class="column">
				<label for="availability_date">Availability Date</label>
			</div>
			<div class="column">
				<input type="text" <?php disabled( in_array( 'availability_date', $disabled_fields, true ) ); ?> id="availability_date" name="availability_date" value="<?php echo esc_attr( get_post_meta( $post->ID, 'availability_date', true ) ); ?>">
			</div>
		</div>

		<div class="field">
			<div class="column">
				<label for="has_specials">Has Specials</label>
			</div>
			<div class="column">
				<input type="checkbox" <?php disabled( in_array( 'has_specials', $disabled_fields, true ) ); ?> id="has_specials" name="has_specials" <?php checked( get_post_meta( $post->ID, 'has_specials', true ), '1' ); ?>>
			</div>
		</div>

		<div class="field">
			<div class="column">
				<label for="specials_override_text">Specials Override Text</label>
				<p class="description">Manually customize the specials text displayed. This never syncs and overrides specials from the property-management system.</p>
			</div>
			<div class="column">
				<input type="text" id="specials_override_text" name="specials_override_text" maxlength="25" value="<?php echo esc_attr( get_post_meta( $post->ID, 'specials_override_text', true ) ); ?>">
				<p class="description"><em>Maximum 25 characters</em></p>
			</div>
		</div>

		<div class="field">
			<div class="column">
				<label for="availability_url">Availability URL</label>
			</div>
			<div class="column">
				<input type="text" <?php disabled( in_array( 'availability_url', $disabled_fields, true ) ); ?> id="availability_url" name="availability_url" value="<?php echo esc_attr( get_post_meta( $post->ID, 'availability_url', true ) ); ?>">
			</div>
		</div>

		<div class="field">
			<div class="column">
				<label for="available_units">Available Units</label>
			</div>
			<div class="column">
				<input type="text" <?php disabled( in_array( 'available_units', $disabled_fields, true ) ); ?> id="available_units" name="available_units" value="<?php echo esc_attr( get_post_meta( $post->ID, 'available_units', true ) ); ?>">
			</div>
		</div>
	</div>
	<?php
}
