<?php
/**
 * Property identity and related-record navigation.
 *
 * @package rentfetch
 */

/**
 * Render the prominent property identity bar above the tabbed editor.
 *
 * @param WP_Post $post Property post.
 * @return void
 */
function rentfetch_render_property_identity_bar( $post ) {
	$property_source = get_post_meta( $post->ID, 'property_source', true );
	$property_id     = get_post_meta( $post->ID, 'property_id', true );
	$source_label    = $property_source ? ucwords( str_replace( array( '-', '_' ), ' ', $property_source ) ) : 'Manual';
	$source_labels   = apply_filters(
		'rentfetch_property_source_labels',
		array(
			'engrain'     => 'Engrain / SightMap',
			'entrata'     => 'Entrata',
			'rentmanager' => 'Rent Manager',
			'yardi'       => 'Yardi / RentCafe',
		),
		$post->ID
	);
	$enabled_sources = get_option( 'rentfetch_options_enabled_integrations', array() );
	$source_options  = array( '' => 'Manual' );

	if ( ! is_array( $enabled_sources ) ) {
		$enabled_sources = array();
	}

	foreach ( $enabled_sources as $enabled_source ) {
		$enabled_source = sanitize_key( $enabled_source );

		if ( '' === $enabled_source ) {
			continue;
		}

		$source_options[ $enabled_source ] = isset( $source_labels[ $enabled_source ] )
			? $source_labels[ $enabled_source ]
			: ucwords( str_replace( array( '-', '_' ), ' ', $enabled_source ) );
	}

	if ( $property_source && ! isset( $source_options[ $property_source ] ) ) {
		$source_options[ $property_source ] = isset( $source_labels[ $property_source ] )
			? $source_labels[ $property_source ]
			: $source_label;
	}

	$source_options = apply_filters( 'rentfetch_property_source_options', $source_options, $post->ID, $enabled_sources );

	wp_enqueue_script( 'rentfetch-metabox-properties' );
	?>
	<div class="rf-property-identity" data-rf-property-identity>
		<div class="rf-property-identity-item rf-property-identity-source">
			<div class="rf-property-identity-label-row">
				<label class="rf-property-identity-label" for="property_source">Property source</label>
			</div>
			<div class="rf-property-identity-control">
				<select id="property_source" name="property_source" data-rf-protected-field="source" aria-readonly="true" aria-haspopup="dialog">
					<?php foreach ( $source_options as $source_value => $source_option_label ) : ?>
						<option value="<?php echo esc_attr( $source_value ); ?>" <?php selected( $property_source, $source_value ); ?>><?php echo esc_html( $source_option_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<input type="hidden" name="rentfetch_property_source_original" value="<?php echo esc_attr( $property_source ); ?>">
			<input type="hidden" name="rentfetch_property_source_override_confirmed" value="0" data-rf-property-source-confirmed>
		</div>

		<div class="rf-property-identity-item rf-property-identity-id">
			<div class="rf-property-identity-label-row">
				<label class="rf-property-identity-label" for="property_id">Property ID</label>
				<span class="rf-property-identity-help">
					<button type="button" class="rf-property-identity-info" aria-describedby="rf-property-id-help">
						<span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
						<span class="screen-reader-text">About the Property ID</span>
					</button>
					<span class="rf-property-identity-tooltip" id="rf-property-id-help" role="tooltip">The same Property ID must be used on the property and every associated floor plan and unit, whether those records are synced or entered manually. For synced data, this will be the sync source’s internal identifier.</span>
				</span>
			</div>
			<div class="rf-property-identity-control">
				<input
					type="text"
					id="property_id"
					name="property_id"
					value="<?php echo esc_attr( $property_id ); ?>"
					placeholder="Not set"
					<?php echo $property_id ? 'readonly' : ''; ?>
					<?php echo $property_id ? 'aria-readonly="true" aria-haspopup="dialog"' : ''; ?>
					data-rf-protected-field="id"
					data-original-value="<?php echo esc_attr( $property_id ); ?>"
				>
			</div>
			<input type="hidden" name="rentfetch_property_id_original" value="<?php echo esc_attr( $property_id ); ?>">
			<input type="hidden" name="rentfetch_property_id_override_confirmed" value="0" data-rf-property-id-confirmed>
		</div>
	</div>

	<dialog class="rf-confirmation-dialog" data-rf-property-id-dialog aria-labelledby="rf-property-id-dialog-title">
		<div class="rf-confirmation-dialog-header">
			<h2 id="rf-property-id-dialog-title">Change the Property ID?</h2>
			<p>This identifier connects the property to its floor plans and units. Changing it can disconnect the property from its floor plans, units, or API data.</p>
		</div>
		<div class="rf-confirmation-dialog-body">
			<p class="rf-confirmation-dialog-warning"><strong>Continue only if you know how this identifier connects the property to related records.</strong></p>
		</div>
		<div class="rf-confirmation-dialog-actions">
			<button type="button" class="button button-secondary" data-rf-dialog-cancel>Cancel</button>
			<button type="button" class="button button-primary" data-rf-dialog-confirm>Continue to edit</button>
		</div>
	</dialog>

	<dialog class="rf-confirmation-dialog" data-rf-property-source-dialog aria-labelledby="rf-property-source-dialog-title">
		<div class="rf-confirmation-dialog-header">
			<h2 id="rf-property-source-dialog-title">Change the Property Source?</h2>
			<p>The source determines which integration manages this property. Changing it can affect syncing and which property fields are overwritten by an integration.</p>
		</div>
		<div class="rf-confirmation-dialog-body">
			<p class="rf-confirmation-dialog-warning"><strong>Continue only if you understand the property’s sync configuration.</strong></p>
		</div>
		<div class="rf-confirmation-dialog-actions">
			<button type="button" class="button button-secondary" data-rf-dialog-cancel>Cancel</button>
			<button type="button" class="button button-primary" data-rf-dialog-confirm>Continue to edit</button>
		</div>
	</dialog>
	<?php
}

/**
 * Render links to records that share the current Property ID.
 *
 * @param WP_Post $post Property post.
 * @return void
 */
function rentfetch_properties_related_records_callback( $post ) {
	$property_id   = get_post_meta( $post->ID, 'property_id', true );
	$floorplan_url = add_query_arg(
		array(
			'post_type' => 'floorplans',
			's'         => $property_id,
		),
		admin_url( 'edit.php' )
	);
	$unit_url      = add_query_arg(
		array(
			'post_type' => 'units',
			's'         => $property_id,
		),
		admin_url( 'edit.php' )
	);
	?>
	<div class="rf-related-record-links" data-rf-related-record-links>
		<a class="button button-secondary" href="<?php echo esc_url( $floorplan_url ); ?>" data-base-url="<?php echo esc_url( admin_url( 'edit.php?post_type=floorplans' ) ); ?>">View related floor plans</a>
		<a class="button button-secondary" href="<?php echo esc_url( $unit_url ); ?>" data-base-url="<?php echo esc_url( admin_url( 'edit.php?post_type=units' ) ); ?>">View related units</a>
	</div>
	<?php
}
