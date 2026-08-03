<?php
/**
 * Floor plan availability fields.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render floor plan availability fields.
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
