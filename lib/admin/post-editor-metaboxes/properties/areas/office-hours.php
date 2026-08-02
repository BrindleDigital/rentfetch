<?php
/**
 * Property editor office-hours fields.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Properties office hours metabox callback
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_office_hours_metabox_callback( $post ) {
	$array_disabled_fields = apply_filters( 'rentfetch_filter_property_syncing_fields', array(), $post->ID );
	?>
	<div class="rf-metabox rf-metabox-properties">

		<p class="description">Enter times in 24-hour format (e.g., 09:00, 18:00). You can enter just the hour (e.g., 9) and it will be formatted as 09:00.</p>

		<?php
		$office_hours = get_post_meta( $post->ID, 'office_hours', true );
		if ( ! is_array( $office_hours ) ) {
			$office_hours = array();
		}

		$days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
		?>

		<div class="office-hours-grid">
			<?php
			foreach ( $days as $day ) :
				$disabled = in_array( 'office_hours', $array_disabled_fields, true ) ? 'disabled' : '';
				?>
				<div class="field office-hours-field">
						<label for="<?php echo esc_attr( $day ); ?>_start"><?php echo esc_html( ucfirst( $day ) ); ?></label>
					<div class="time-inputs">
						<input type="text"
								id="<?php echo esc_attr( $day ); ?>_start"
								name="office_hours[<?php echo esc_attr( $day ); ?>][start]"
								value="<?php echo esc_attr( $office_hours[ $day ]['start'] ?? '' ); ?>"
								<?php echo esc_attr( $disabled ); ?>>
						<span>to</span>
						<input type="text"
								id="<?php echo esc_attr( $day ); ?>_end"
								name="office_hours[<?php echo esc_attr( $day ); ?>][end]"
								value="<?php echo esc_attr( $office_hours[ $day ]['end'] ?? '' ); ?>"
								<?php echo esc_attr( $disabled ); ?>>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
	<?php
}
