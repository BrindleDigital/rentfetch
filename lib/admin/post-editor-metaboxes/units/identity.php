<?php
/**
 * Unit identity and relationship fields.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get source choices available to a unit.
 *
 * @param int $post_id Unit post ID.
 * @return array<string, string>
 */
function rentfetch_get_unit_source_options( $post_id ) {
	$current_source  = sanitize_key( get_post_meta( $post_id, 'unit_source', true ) );
	$enabled_sources = get_option( 'rentfetch_options_enabled_integrations', array() );
	$source_labels   = apply_filters(
		'rentfetch_unit_source_labels',
		array(
			'engrain'     => 'Engrain / SightMap',
			'entrata'     => 'Entrata',
			'realpage'    => 'RealPage',
			'rentmanager' => 'Rent Manager',
			'yardi'       => 'Yardi / RentCafe',
		),
		$post_id
	);
	$options         = array( '' => 'Manual' );

	if ( ! is_array( $enabled_sources ) ) {
		$enabled_sources = array();
	}

	foreach ( $enabled_sources as $enabled_source ) {
		$enabled_source = sanitize_key( $enabled_source );
		if ( '' === $enabled_source ) {
			continue;
		}

		$options[ $enabled_source ] = $source_labels[ $enabled_source ]
			?? ucwords( str_replace( array( '-', '_' ), ' ', $enabled_source ) );
	}

	if ( $current_source && ! isset( $options[ $current_source ] ) ) {
		$options[ $current_source ] = $source_labels[ $current_source ]
			?? ucwords( str_replace( array( '-', '_' ), ' ', $current_source ) );
	}

	return apply_filters( 'rentfetch_unit_source_options', $options, $post_id, $enabled_sources );
}

/**
 * Get floor plans available to a unit.
 *
 * @return array<int, array<string, mixed>>
 */
function rentfetch_get_unit_floorplan_options() {
	$floorplans = get_posts(
		array(
			'post_type'              => 'floorplans',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);
	$options    = array();

	foreach ( $floorplans as $floorplan ) {
		$floorplan_id = trim( (string) get_post_meta( $floorplan->ID, 'floorplan_id', true ) );
		$property_id  = trim( (string) get_post_meta( $floorplan->ID, 'property_id', true ) );

		if ( '' === $floorplan_id || '' === $property_id ) {
			continue;
		}

		$title     = get_the_title( $floorplan );
		$options[] = array(
			'id'           => $floorplan->ID,
			'floorplan_id' => $floorplan_id,
			'property_id'  => $property_id,
			'title'        => $title,
			'label'        => sprintf( '%1$s — ID: %2$s', $title, $floorplan_id ),
		);
	}

	return $options;
}

/**
 * Render the prominent unit identity bar above the tabbed editor.
 *
 * @param WP_Post $post Unit post.
 * @return void
 */
function rentfetch_render_unit_identity_bar( $post ) {
	$unit_source       = sanitize_key( get_post_meta( $post->ID, 'unit_source', true ) );
	$property_id       = trim( (string) get_post_meta( $post->ID, 'property_id', true ) );
	$floorplan_id      = trim( (string) get_post_meta( $post->ID, 'floorplan_id', true ) );
	$unit_id           = trim( (string) get_post_meta( $post->ID, 'unit_id', true ) );
	$source_options    = rentfetch_get_unit_source_options( $post->ID );
	$property_options  = rentfetch_get_floorplan_property_options();
	$floorplan_options = rentfetch_get_unit_floorplan_options();
	$property_found    = false;
	$floorplan_found   = false;
	?>
	<div class="rf-property-identity rf-unit-identity" data-rf-unit-identity>
		<div class="rf-property-identity-item rf-property-identity-source">
			<div class="rf-property-identity-label-row">
				<label class="rf-property-identity-label" for="unit_source">Unit source</label>
			</div>
			<div class="rf-property-identity-control">
				<select id="unit_source" name="unit_source" aria-readonly="true" aria-haspopup="dialog">
					<?php foreach ( $source_options as $source_value => $source_label ) : ?>
						<option value="<?php echo esc_attr( $source_value ); ?>" <?php selected( $unit_source, $source_value ); ?>><?php echo esc_html( $source_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<input type="hidden" name="rentfetch_unit_source_original" value="<?php echo esc_attr( $unit_source ); ?>">
			<input type="hidden" name="rentfetch_unit_source_override_confirmed" value="0" data-rf-unit-source-confirmed>
		</div>

		<div class="rf-property-identity-item rf-property-identity-id">
			<div class="rf-property-identity-label-row">
				<label class="rf-property-identity-label" for="unit_property_id_search">Property ID</label>
			</div>
			<div class="rf-property-identity-control">
				<div class="rf-property-relationship-combobox" data-rf-unit-property-relationship>
					<input type="search" id="unit_property_id_search" class="rf-property-relationship-search" placeholder="Search properties…" autocomplete="off" hidden>
					<button type="button" class="rf-property-relationship-toggle" aria-label="Show all properties" hidden>
						<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
					</button>
					<select id="property_id" name="property_id" required>
						<option value="">Select a property</option>
						<?php foreach ( $property_options as $property_option ) : ?>
							<?php $selected_property = $property_id === $property_option['property_id']; ?>
							<?php $property_found = $property_found || $selected_property; ?>
							<option value="<?php echo esc_attr( $property_option['property_id'] ); ?>" <?php selected( $selected_property ); ?>><?php echo esc_html( $property_option['label'] ); ?></option>
						<?php endforeach; ?>
						<?php if ( '' !== $property_id && ! $property_found ) : ?>
							<option value="<?php echo esc_attr( $property_id ); ?>" selected>Unknown property — ID: <?php echo esc_html( $property_id ); ?></option>
						<?php endif; ?>
					</select>
				</div>
			</div>
		</div>

		<div class="rf-property-identity-item rf-property-identity-id">
			<div class="rf-property-identity-label-row">
				<label class="rf-property-identity-label" for="unit_floorplan_id_search">Floor Plan ID</label>
			</div>
			<div class="rf-property-identity-control">
				<div class="rf-property-relationship-combobox" data-rf-unit-floorplan-relationship>
					<input type="search" id="unit_floorplan_id_search" class="rf-property-relationship-search" placeholder="Search floor plans…" autocomplete="off" hidden>
					<button type="button" class="rf-property-relationship-toggle" aria-label="Show all floor plans" hidden>
						<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
					</button>
					<select id="floorplan_id" name="floorplan_id" required>
						<option value="">Select a floor plan</option>
						<?php foreach ( $floorplan_options as $floorplan_option ) : ?>
							<?php $selected_floorplan = $floorplan_id === $floorplan_option['floorplan_id']; ?>
							<?php $floorplan_found = $floorplan_found || $selected_floorplan; ?>
							<option
								value="<?php echo esc_attr( $floorplan_option['floorplan_id'] ); ?>"
								data-property-id="<?php echo esc_attr( $floorplan_option['property_id'] ); ?>"
								<?php selected( $selected_floorplan ); ?>
							><?php echo esc_html( $floorplan_option['label'] ); ?></option>
						<?php endforeach; ?>
						<?php if ( '' !== $floorplan_id && ! $floorplan_found ) : ?>
							<option value="<?php echo esc_attr( $floorplan_id ); ?>" data-property-id="<?php echo esc_attr( $property_id ); ?>" selected>Unknown floor plan — ID: <?php echo esc_html( $floorplan_id ); ?></option>
						<?php endif; ?>
					</select>
				</div>
			</div>
		</div>

		<div class="rf-property-identity-item rf-property-identity-id">
			<div class="rf-property-identity-label-row">
				<label class="rf-property-identity-label" for="unit_id">Unit ID</label>
			</div>
			<div class="rf-property-identity-control">
				<input
					type="text"
					id="unit_id"
					name="unit_id"
					value="<?php echo esc_attr( $unit_id ); ?>"
					placeholder="Not set"
					required
					<?php echo $unit_id ? 'readonly' : ''; ?>
					<?php echo $unit_id ? 'aria-readonly="true" aria-haspopup="dialog"' : ''; ?>
				>
			</div>
		</div>

		<input type="hidden" name="rentfetch_unit_property_id_original" value="<?php echo esc_attr( $property_id ); ?>">
		<input type="hidden" name="rentfetch_unit_floorplan_id_original" value="<?php echo esc_attr( $floorplan_id ); ?>">
		<input type="hidden" name="rentfetch_unit_relationship_override_confirmed" value="0" data-rf-unit-relationship-confirmed>
		<input type="hidden" name="rentfetch_unit_id_original" value="<?php echo esc_attr( $unit_id ); ?>">
		<input type="hidden" name="rentfetch_unit_id_override_confirmed" value="0" data-rf-unit-id-confirmed>
	</div>

	<dialog class="rf-confirmation-dialog" data-rf-unit-source-dialog aria-labelledby="rf-unit-source-dialog-title">
		<div class="rf-confirmation-dialog-header">
			<h2 id="rf-unit-source-dialog-title">Change the Unit Source?</h2>
			<p>The source determines which integration manages this unit and which values may be overwritten by a future sync.</p>
		</div>
		<div class="rf-confirmation-dialog-body">
			<p class="rf-confirmation-dialog-warning"><strong>Continue only if you understand the unit’s sync configuration.</strong></p>
		</div>
		<div class="rf-confirmation-dialog-actions">
			<button type="button" class="button button-secondary" data-rf-dialog-cancel>Cancel</button>
			<button type="button" class="button button-primary" data-rf-dialog-confirm>Continue to edit</button>
		</div>
	</dialog>

	<dialog class="rf-confirmation-dialog" data-rf-unit-relationship-dialog aria-labelledby="rf-unit-relationship-dialog-title">
		<div class="rf-confirmation-dialog-header">
			<h2 id="rf-unit-relationship-dialog-title">Move this Unit?</h2>
			<p>Changing the Property ID or Floor Plan ID detaches this unit from its current hierarchy. A future sync may recreate or remove records.</p>
		</div>
		<div class="rf-confirmation-dialog-body">
			<p class="rf-confirmation-dialog-warning"><strong>Continue only if you intend to move this unit to another property or floor plan.</strong></p>
		</div>
		<div class="rf-confirmation-dialog-actions">
			<button type="button" class="button button-secondary" data-rf-dialog-cancel>Cancel</button>
			<button type="button" class="button button-primary" data-rf-dialog-confirm>Continue to select relationships</button>
		</div>
	</dialog>

	<dialog class="rf-confirmation-dialog" data-rf-unit-id-dialog aria-labelledby="rf-unit-id-dialog-title">
		<div class="rf-confirmation-dialog-header">
			<h2 id="rf-unit-id-dialog-title">Change the Unit ID?</h2>
			<p>This identifier connects the unit to synced API data. Changing it can cause a future sync to recreate or remove the unit.</p>
		</div>
		<div class="rf-confirmation-dialog-body">
			<p class="rf-confirmation-dialog-warning"><strong>Continue only if you understand how this identifier is managed.</strong></p>
		</div>
		<div class="rf-confirmation-dialog-actions">
			<button type="button" class="button button-secondary" data-rf-dialog-cancel>Cancel</button>
			<button type="button" class="button button-primary" data-rf-dialog-confirm>Continue to edit</button>
		</div>
	</dialog>
	<?php
}
