<?php
/**
 * Properties specials metabox callback
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_specials_metabox_callback( $post ) {
	$array_disabled_fields = apply_filters( 'rentfetch_filter_property_syncing_fields', array(), $post->ID );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'rentfetch-flatpickr-script' );
	wp_enqueue_script( 'rentfetch-metabox-properties' );
	wp_enqueue_style( 'rentfetch-flatpickr-style' );
	?>
	<div class="rf-metabox rf-metabox-properties rf-property-specials-metabox">
		<?php
		// * Has Specials
		$has_specials = get_post_meta( $post->ID, 'has_specials', true );
		$disabled     = in_array( 'has_specials', $array_disabled_fields, true ) ? 'disabled' : '';
		?>
		<div class="field">
			<div class="rf-toggle-control">
				<input class="rf-toggle-input" type="checkbox" <?php echo esc_attr( $disabled ); ?> id="has_specials" name="has_specials" <?php checked( $has_specials, '1' ); ?>>
				<label class="rf-toggle-label" for="has_specials">
					<span class="rf-toggle-track" aria-hidden="true"><span class="rf-toggle-thumb"></span></span>
					<span class="rf-toggle-text">Show specials for this property</span>
				</label>
			</div>
		</div>

		<?php
		// * Specials heading
		$specials_override_text = get_post_meta( $post->ID, 'specials_override_text', true );
		$specials_content       = get_post_meta( $post->ID, 'specials_content', true );
		$specials_start_date    = get_post_meta( $post->ID, 'specials_start_date', true );
		$specials_end_date      = get_post_meta( $post->ID, 'specials_end_date', true );
		$conditional_class      = $has_specials ? '' : ' hidden is-hidden';
		$conditional_hidden     = $has_specials ? '' : ' hidden';
		?>
		<div class="field rf-specials-conditional-field<?php echo esc_attr( $conditional_class ); ?>"<?php echo esc_attr( $conditional_hidden ); ?>>
			<label for="specials_override_text">Specials Heading</label>
			<input type="text" id="specials_override_text" name="specials_override_text" maxlength="25" value="<?php echo esc_attr( $specials_override_text ); ?>">
		</div>

		<div class="field rf-specials-conditional-field<?php echo esc_attr( $conditional_class ); ?>"<?php echo esc_attr( $conditional_hidden ); ?>>
			<label for="specials_content">Specials Content</label>
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
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var metabox = document.querySelector('.rf-property-specials-metabox');
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
