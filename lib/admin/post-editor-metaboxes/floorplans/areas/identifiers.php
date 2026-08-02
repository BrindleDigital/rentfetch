<?php
/**
 * Floor plan identity fields.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get the source choices available to a floor plan.
 *
 * @param int $post_id Floor plan post ID.
 * @return array<string, string>
 */
function rentfetch_get_floorplan_source_options( $post_id ) {
	$current_source  = sanitize_key( get_post_meta( $post_id, 'floorplan_source', true ) );
	$enabled_sources = get_option( 'rentfetch_options_enabled_integrations', array() );
	$source_labels   = apply_filters(
		'rentfetch_floorplan_source_labels',
		array(
			'engrain'     => 'Engrain / SightMap',
			'entrata'     => 'Entrata',
			'realpage'    => 'RealPage',
			'rentmanager' => 'Rent Manager',
			'yardi'       => 'Yardi / RentCafe',
		),
		$post_id
	);
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

	if ( $current_source && ! isset( $source_options[ $current_source ] ) ) {
		$source_options[ $current_source ] = isset( $source_labels[ $current_source ] )
			? $source_labels[ $current_source ]
			: ucwords( str_replace( array( '-', '_' ), ' ', $current_source ) );
	}

	return apply_filters( 'rentfetch_floorplan_source_options', $source_options, $post_id, $enabled_sources );
}

/**
 * Get properties that can be selected as the floor plan's parent.
 *
 * @return array<int, array{id:int, property_id:string, title:string, label:string}>
 */
function rentfetch_get_floorplan_property_options() {
	$property_posts = get_posts(
		array(
			'post_type'              => 'properties',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);
	$options        = array();

	foreach ( $property_posts as $property_post ) {
		$property_id = trim( (string) get_post_meta( $property_post->ID, 'property_id', true ) );

		if ( '' === $property_id ) {
			continue;
		}

		$title     = get_the_title( $property_post );
		$options[] = array(
			'id'          => $property_post->ID,
			'property_id' => $property_id,
			'title'       => $title,
			'label'       => sprintf( '%1$s — ID: %2$s', $title, $property_id ),
		);
	}

	return $options;
}

/**
 * Render the prominent floor plan identity bar above the tabbed editor.
 *
 * @param WP_Post $post Floor plan post.
 * @return void
 */
function rentfetch_render_floorplan_identity_bar( $post ) {
	$floorplan_source = sanitize_key( get_post_meta( $post->ID, 'floorplan_source', true ) );
	$property_id      = trim( (string) get_post_meta( $post->ID, 'property_id', true ) );
	$floorplan_id     = (string) get_post_meta( $post->ID, 'floorplan_id', true );
	$source_options   = rentfetch_get_floorplan_source_options( $post->ID );
	$property_options = rentfetch_get_floorplan_property_options();
	$property_found   = false;
	?>
	<div class="rf-property-identity rf-floorplan-identity" data-rf-floorplan-identity>
		<div class="rf-property-identity-item rf-property-identity-source">
			<div class="rf-property-identity-label-row">
				<label class="rf-property-identity-label" for="floorplan_source">Floor plan source</label>
			</div>
			<div class="rf-property-identity-control">
				<select id="floorplan_source" name="floorplan_source" aria-readonly="true" aria-haspopup="dialog">
					<?php foreach ( $source_options as $source_value => $source_label ) : ?>
						<option value="<?php echo esc_attr( $source_value ); ?>" <?php selected( $floorplan_source, $source_value ); ?>><?php echo esc_html( $source_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<input type="hidden" name="rentfetch_floorplan_source_original" value="<?php echo esc_attr( $floorplan_source ); ?>">
			<input type="hidden" name="rentfetch_floorplan_source_override_confirmed" value="0" data-rf-floorplan-source-confirmed>
		</div>

		<div class="rf-property-identity-item rf-property-identity-id">
			<div class="rf-property-identity-label-row">
				<label class="rf-property-identity-label" for="property_id_search">Property ID</label>
				<span class="rf-property-identity-help">
					<button type="button" class="rf-property-identity-info" aria-describedby="rf-floorplan-property-help">
						<span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
						<span class="screen-reader-text">About the associated property</span>
					</button>
					<span class="rf-property-identity-tooltip" id="rf-floorplan-property-help" role="tooltip">Choose the property that owns this floor plan. The selected property’s Property ID is saved on the floor plan.</span>
				</span>
			</div>
			<div class="rf-property-identity-control">
				<div class="rf-property-relationship-combobox" data-rf-property-relationship>
					<input
						type="search"
						id="property_id_search"
						class="rf-property-relationship-search"
						placeholder="Search properties by name or ID…"
						autocomplete="off"
						aria-label="Search properties by name or Property ID"
						hidden
					>
					<button type="button" class="rf-property-relationship-toggle" aria-label="Show all properties" hidden>
						<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
					</button>
					<select id="property_id" name="property_id" required>
						<option value="">Select a property</option>
						<?php foreach ( $property_options as $property_option ) : ?>
							<?php $is_selected = $property_id === $property_option['property_id']; ?>
							<?php $property_found = $property_found || $is_selected; ?>
							<option
								value="<?php echo esc_attr( $property_option['property_id'] ); ?>"
								data-property-name="<?php echo esc_attr( $property_option['title'] ); ?>"
								data-property-id="<?php echo esc_attr( $property_option['property_id'] ); ?>"
								<?php selected( $is_selected ); ?>
							><?php echo esc_html( $property_option['label'] ); ?></option>
						<?php endforeach; ?>
						<?php if ( '' !== $property_id && ! $property_found ) : ?>
							<option value="<?php echo esc_attr( $property_id ); ?>" data-property-name="Unknown property" data-property-id="<?php echo esc_attr( $property_id ); ?>" selected>Unknown property — ID: <?php echo esc_html( $property_id ); ?></option>
						<?php endif; ?>
					</select>
				</div>
			</div>
			<input type="hidden" name="rentfetch_floorplan_property_id_original" value="<?php echo esc_attr( $property_id ); ?>">
			<input type="hidden" name="rentfetch_floorplan_property_id_override_confirmed" value="0" data-rf-floorplan-property-id-confirmed>
		</div>

		<div class="rf-property-identity-item rf-property-identity-id">
			<div class="rf-property-identity-label-row">
				<label class="rf-property-identity-label" for="floorplan_id">Floor Plan ID</label>
				<span class="rf-property-identity-help">
					<button type="button" class="rf-property-identity-info" aria-describedby="rf-floorplan-id-help">
						<span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
						<span class="screen-reader-text">About the Floor Plan ID</span>
					</button>
					<span class="rf-property-identity-tooltip" id="rf-floorplan-id-help" role="tooltip">This identifier connects the floor plan to its units and synced API data.</span>
				</span>
			</div>
			<div class="rf-property-identity-control">
				<input
					type="text"
					id="floorplan_id"
					name="floorplan_id"
					value="<?php echo esc_attr( $floorplan_id ); ?>"
					placeholder="Not set"
					required
					<?php echo $floorplan_id ? 'readonly' : ''; ?>
					<?php echo $floorplan_id ? 'aria-readonly="true" aria-haspopup="dialog"' : ''; ?>
				>
			</div>
			<input type="hidden" name="rentfetch_floorplan_id_original" value="<?php echo esc_attr( $floorplan_id ); ?>">
			<input type="hidden" name="rentfetch_floorplan_id_override_confirmed" value="0" data-rf-floorplan-id-confirmed>
		</div>
	</div>

	<dialog class="rf-confirmation-dialog" data-rf-floorplan-source-dialog aria-labelledby="rf-floorplan-source-dialog-title">
		<div class="rf-confirmation-dialog-header">
			<h2 id="rf-floorplan-source-dialog-title">Change the Floor Plan Source?</h2>
			<p>The source determines which integration manages this floor plan. Changing it can affect syncing and which floor plan fields are overwritten by an integration.</p>
		</div>
		<div class="rf-confirmation-dialog-body">
			<p class="rf-confirmation-dialog-warning"><strong>Continue only if you understand the floor plan’s sync configuration.</strong></p>
		</div>
		<div class="rf-confirmation-dialog-actions">
			<button type="button" class="button button-secondary" data-rf-dialog-cancel>Cancel</button>
			<button type="button" class="button button-primary" data-rf-dialog-confirm>Continue to edit</button>
		</div>
	</dialog>

	<dialog class="rf-confirmation-dialog" data-rf-floorplan-property-id-dialog aria-labelledby="rf-floorplan-property-id-dialog-title">
		<div class="rf-confirmation-dialog-header">
			<h2 id="rf-floorplan-property-id-dialog-title">Move this Floor Plan to Another Property?</h2>
			<p>Changing the Property ID detaches this floor plan from its current property and assigns it to another one. Related units can be disconnected, and a future sync may recreate or remove records.</p>
		</div>
		<div class="rf-confirmation-dialog-body">
			<p class="rf-confirmation-dialog-warning"><strong>Continue only if you intend to move this floor plan and understand how its records are synced.</strong></p>
		</div>
		<div class="rf-confirmation-dialog-actions">
			<button type="button" class="button button-secondary" data-rf-dialog-cancel>Cancel</button>
			<button type="button" class="button button-primary" data-rf-dialog-confirm>Continue to select a property</button>
		</div>
	</dialog>

	<dialog class="rf-confirmation-dialog" data-rf-floorplan-id-dialog aria-labelledby="rf-floorplan-id-dialog-title">
		<div class="rf-confirmation-dialog-header">
			<h2 id="rf-floorplan-id-dialog-title">Change the Floor Plan ID?</h2>
			<p>This identifier connects the floor plan to its units and synced API data. Changing it can disconnect existing unit records or cause future syncs to create a separate floor plan.</p>
		</div>
		<div class="rf-confirmation-dialog-body">
			<p class="rf-confirmation-dialog-warning"><strong>Continue only if you know how this identifier connects the floor plan to related records.</strong></p>
		</div>
		<div class="rf-confirmation-dialog-actions">
			<button type="button" class="button button-secondary" data-rf-dialog-cancel>Cancel</button>
			<button type="button" class="button button-primary" data-rf-dialog-confirm>Continue to edit</button>
		</div>
	</dialog>
	<?php
}
