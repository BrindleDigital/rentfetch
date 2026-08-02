<?php
/**
 * Floor plan information fields.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render one compact numeric floor plan field.
 *
 * @param int                  $post_id        Floor plan post ID.
 * @param array<int, string>   $disabled_fields Synced fields that cannot be edited.
 * @param array<string, mixed> $field          Field definition.
 * @return void
 */
function rentfetch_render_floorplan_numeric_field( $post_id, $disabled_fields, $field ) {
	$key     = $field['key'];
	$label   = $field['label'];
	$help    = $field['help'] ?? '';
	$help_id = 'rf-floorplan-' . str_replace( '_', '-', $key ) . '-help';
	?>
	<div class="rf-floorplan-number-field">
		<div class="rf-floorplan-number-label-row">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php if ( $help ) : ?>
				<span class="rf-property-identity-help">
					<button type="button" class="rf-property-identity-info" aria-describedby="<?php echo esc_attr( $help_id ); ?>">
						<span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
						<span class="screen-reader-text">About <?php echo esc_html( $label ); ?></span>
					</button>
					<span class="rf-property-identity-tooltip" id="<?php echo esc_attr( $help_id ); ?>" role="tooltip"><?php echo esc_html( $help ); ?></span>
				</span>
			<?php endif; ?>
		</div>
		<input
			type="number"
			id="<?php echo esc_attr( $key ); ?>"
			name="<?php echo esc_attr( $key ); ?>"
			value="<?php echo esc_attr( get_post_meta( $post_id, $key, true ) ); ?>"
			min="0"
			step="any"
			inputmode="decimal"
			<?php disabled( in_array( $key, $disabled_fields, true ) ); ?>
		>
	</div>
	<?php
}

/**
 * Render floor plan bedroom, bathroom, deposit, rent, and square-footage fields.
 *
 * @param WP_Post $post Floor plan post.
 * @return void
 */
function rentfetch_floorplans_info_metabox_callback( $post ) {
	$disabled_fields = apply_filters( 'rentfetch_filter_floorplan_syncing_fields', array(), $post->ID );
	$field_rows      = array(
		array(
			array(
				'key'   => 'beds',
				'label' => 'Beds',
			),
			array(
				'key'   => 'baths',
				'label' => 'Baths',
			),
			array(
				'key'   => 'minimum_sqft',
				'label' => 'Minimum Sqft',
				'help'  => 'The minimum square-footage value is required for square-footage search. When entering a floor plan manually, use the minimum value.',
			),
			array(
				'key'   => 'maximum_sqft',
				'label' => 'Maximum Sqft',
			),
		),
		array(
			array(
				'key'   => 'minimum_deposit',
				'label' => 'Minimum Deposit',
			),
			array(
				'key'   => 'maximum_deposit',
				'label' => 'Maximum Deposit',
			),
			array(
				'key'   => 'minimum_rent',
				'label' => 'Minimum Rent',
				'help'  => 'The minimum rent is required for pricing search. When entering a floor plan manually, use the minimum value.',
			),
			array(
				'key'   => 'maximum_rent',
				'label' => 'Maximum Rent',
			),
		),
	);
	?>
	<div class="rf-metabox rf-metabox-floorplans rf-floorplan-information">
		<?php foreach ( $field_rows as $row_index => $fields ) : ?>
			<div class="rf-floorplan-information-row" role="group" aria-label="<?php echo esc_attr( 0 === $row_index ? 'Floor plan dimensions' : 'Floor plan pricing' ); ?>">
				<?php
				foreach ( $fields as $field ) {
					rentfetch_render_floorplan_numeric_field( $post->ID, $disabled_fields, $field );
				}
				?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}
