<?php
/**
 * Floor plan specials fields.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render floor plan specials fields.
 *
 * @param WP_Post $post Floor plan post.
 * @return void
 */
function rentfetch_floorplans_specials_metabox_callback( $post ) {
	$disabled_fields = apply_filters( 'rentfetch_filter_floorplan_syncing_fields', array(), $post->ID );
	wp_enqueue_script( 'rentfetch-flatpickr-script' );
	wp_enqueue_script( 'rentfetch-metabox-properties' );
	wp_enqueue_style( 'rentfetch-flatpickr-style' );

	$has_specials             = get_post_meta( $post->ID, 'has_specials', true );
	$specials_override_text   = get_post_meta( $post->ID, 'specials_override_text', true );
	$specials_content         = get_post_meta( $post->ID, 'specials_content', true );
	$specials_start_date      = get_post_meta( $post->ID, 'specials_start_date', true );
	$specials_end_date        = get_post_meta( $post->ID, 'specials_end_date', true );
	$exclude_property_special = get_post_meta( $post->ID, 'specials_exclude_property', true );
	$conditional_class        = $has_specials ? '' : ' hidden is-hidden';
	$conditional_hidden       = $has_specials ? '' : ' hidden';
	?>
	<div class="rf-metabox rf-metabox-floorplans rf-floorplan-specials-metabox">
		<div class="field">
			<div class="rf-toggle-control">
				<input class="rf-toggle-input" type="checkbox" <?php disabled( in_array( 'has_specials', $disabled_fields, true ) ); ?> id="has_specials" name="has_specials" <?php checked( $has_specials, '1' ); ?>>
				<label class="rf-toggle-label" for="has_specials">
					<span class="rf-toggle-track" aria-hidden="true"><span class="rf-toggle-thumb"></span></span>
					<span class="rf-toggle-text">Use a floor plan special</span>
				</label>
			</div>
			<p class="description">A floor plan special takes priority over an inherited property special.</p>
		</div>

		<div class="field rf-specials-conditional-field<?php echo esc_attr( $conditional_class ); ?>"<?php echo esc_attr( $conditional_hidden ); ?>>
			<label for="specials_override_text">Specials Title</label>
			<input type="text" id="specials_override_text" name="specials_override_text" maxlength="25" value="<?php echo esc_attr( $specials_override_text ); ?>">
			<p class="description"><em>Maximum 25 characters</em></p>
		</div>

		<div class="field rf-specials-conditional-field<?php echo esc_attr( $conditional_class ); ?>"<?php echo esc_attr( $conditional_hidden ); ?>>
			<label for="specials_content">Specials Description</label>
			<textarea id="specials_content" name="specials_content" rows="3"><?php echo esc_textarea( $specials_content ); ?></textarea>
		</div>

		<div class="field rf-specials-conditional-field rf-specials-date-range<?php echo esc_attr( $conditional_class ); ?>"<?php echo esc_attr( $conditional_hidden ); ?>>
			<label for="specials_date_range">Specials Date Range</label>
			<div class="rf-specials-date-mode" aria-label="Specials date mode">
				<label>
					<input type="radio" name="specials_date_mode" value="range" checked>
					<span>Range</span>
				</label>
				<label>
					<input type="radio" name="specials_date_mode" value="start">
					<span>Starts on</span>
				</label>
				<label>
					<input type="radio" name="specials_date_mode" value="end">
					<span>Ends on</span>
				</label>
			</div>
			<div class="rf-specials-date-input-wrap">
				<span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
				<input type="text" id="specials_date_range" class="rf-specials-date-range-input" value="" placeholder="Always active" readonly>
			</div>
			<input type="hidden" id="specials_start_date" name="specials_start_date" value="<?php echo esc_attr( $specials_start_date ); ?>">
			<input type="hidden" id="specials_end_date" name="specials_end_date" value="<?php echo esc_attr( $specials_end_date ); ?>">
			<button type="button" class="rf-specials-date-clear">Clear dates</button>
		</div>

		<div class="field">
			<div class="rf-toggle-control">
				<input class="rf-toggle-input" type="checkbox" id="specials_exclude_property" name="specials_exclude_property" <?php checked( $exclude_property_special, '1' ); ?>>
				<label class="rf-toggle-label" for="specials_exclude_property">
					<span class="rf-toggle-track" aria-hidden="true"><span class="rf-toggle-thumb"></span></span>
					<span class="rf-toggle-text">Hide the property special on this floor plan</span>
				</label>
			</div>
			<p class="description">Use this when this floor plan should show no property special. It does not hide a floor plan special.</p>
		</div>
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var metabox = document.querySelector('.rf-floorplan-specials-metabox');
			var toggle = metabox ? metabox.querySelector('#has_specials') : null;
			var conditionalFields = metabox ? metabox.querySelectorAll('.rf-specials-conditional-field') : [];

			if (!toggle || !conditionalFields.length) {
				return;
			}

			function updateSpecialsFields() {
				conditionalFields.forEach(function(field) {
					var shouldHide = !toggle.checked;
					field.hidden = shouldHide;
					field.classList.toggle('hidden', shouldHide);
					field.classList.toggle('is-hidden', shouldHide);
					field.style.display = shouldHide ? 'none' : '';
				});
			}

			toggle.addEventListener('change', updateSpecialsFields);
			toggle.addEventListener('click', updateSpecialsFields);
			updateSpecialsFields();
		});
	</script>
	<?php
}
