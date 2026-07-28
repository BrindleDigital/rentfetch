<?php
/**
 * Property fees editor.
 *
 * @package rentfetch
 */

/**
 * Render the editable property fees shell.
 *
 * Expensive source resolution and preview markup are loaded only when the Fees
 * tab is opened.
 *
 * @param WP_Post $post Property post.
 * @return void
 */
function rentfetch_properties_fees_metabox_callback( $post ) {
	$api_fees_payload = function_exists( 'rentfetch_get_yardi_synced_property_lease_fees_payload' )
		? rentfetch_get_yardi_synced_property_lease_fees_payload( $post->ID )
		: null;
	?>
	<div class="rf-metabox rf-metabox-properties">
		<?php if ( is_array( $api_fees_payload ) ) : ?>
			<?php rentfetch_render_property_editor_lazy_fragment( 'fees-preview', $post->ID ); ?>
			<?php return; ?>
		<?php endif; ?>

		<?php
		$csv_url                    = get_post_meta( $post->ID, 'property_fees_csv_url', true );
		$monthly_required_total     = get_post_meta( $post->ID, 'property_monthly_required_total_fees', true );
		$monthly_fees_last_checked = (int) get_post_meta( $post->ID, 'property_monthly_required_total_fees_last_checked', true );
		$monthly_fee_rows          = get_post_meta( $post->ID, 'property_monthly_required_total_fees_rows', true );
		$monthly_refresh_nonce     = wp_create_nonce( 'rentfetch_refresh_monthly_required_fees_now' );
		$property_fees_embed       = get_post_meta( $post->ID, 'property_fees_embed', true );

		if ( ! is_array( $monthly_fee_rows ) ) {
			$monthly_fee_rows = array();
		}
		?>

		<div class="field">
			<div class="column">
				<label for="property_fees_csv_url">OPTION 1: CSV Upload or Link</label>
				<p class="description">Upload or link to a CSV file with property fees data.</p>
			</div>
			<div class="column">
				<div class="csv-input-group">
					<button type="button" id="property_fees_csv_upload_btn" class="button">Choose File</button>
					<input type="url" id="property_fees_csv_url" name="property_fees_csv_url" value="<?php echo esc_attr( $csv_url ); ?>" placeholder="or paste in a link to a .csv file">
				</div>
				<div id="csv-url-validation-status"></div>
				<p class="description">
					<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=rentfetch_download_fees_csv_sample' ) ); ?>" download="property_fees_sample.csv">Download sample CSV</a>
					<?php if ( ! empty( $csv_url ) ) : ?>
						or <a href="<?php echo esc_url( $csv_url ); ?>" download="<?php echo esc_attr( get_the_title( $post->ID ) ); ?>-fees.csv">Download Current Data</a>
					<?php endif; ?>
				</p>
			</div>
		</div>

		<div class="field">
			<div class="column">
				<label for="property_monthly_required_total_fees">Monthly Required Total Fees</label>
				<p class="description">Auto-calculated from the property fees CSV about every 12 hours by summing rows where notes are exactly “required” (case-insensitive) and frequency includes “month”.</p>
			</div>
			<div class="column">
				<input
					type="text"
					id="property_monthly_required_total_fees"
					name="property_monthly_required_total_fees"
					value="<?php echo esc_attr( $monthly_required_total ); ?>"
					placeholder="e.g. 129.50"
				>
				<p class="description">You can edit this manually if needed. Leave blank to clear the stored value. This value is refreshed from the CSV approximately every 12 hours and may be overwritten or cleared when parsing runs.</p>
				<p>
					<button
						type="button"
						class="button button-secondary"
						id="refresh-monthly-required-fees-now"
						data-post-id="<?php echo esc_attr( $post->ID ); ?>"
						data-nonce="<?php echo esc_attr( $monthly_refresh_nonce ); ?>"
					>Refresh from CSV now</button>
				</p>
				<div id="monthly-required-fees-refresh-status"></div>
				<?php if ( $monthly_fees_last_checked > 0 ) : ?>
					<p class="description">Last CSV check: <?php echo esc_html( wp_date( 'M j, Y g:ia', $monthly_fees_last_checked ) ); ?></p>
					<?php if ( ! empty( $monthly_fee_rows ) ) : ?>
						<table class="rentfetch-admin-monthly-fees-table">
							<thead>
								<tr>
									<th>Description</th>
									<th>Applied Price</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $monthly_fee_rows as $row ) : ?>
									<?php
									$row_description = sanitize_text_field( (string) ( $row['description'] ?? '' ) );
									$row_price       = isset( $row['applied_price'] ) ? (float) $row['applied_price'] : 0;
									?>
									<tr>
										<td><?php echo esc_html( $row_description ); ?></td>
										<td><?php echo esc_html( '$' . number_format( $row_price, 2 ) ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="field">
			<div class="column">
				<label for="property_fees_embed">OPTION 2: Embed Code</label>
				<p class="description">This option allows you to add a PDF embed via Canva or similar.</p>
			</div>
			<div class="column">
				<textarea id="property_fees_embed" name="property_fees_embed" rows="5"><?php echo esc_textarea( $property_fees_embed ); ?></textarea>
				<p class="description">Paste in your embed code for property fees. This can include script tags, iframes, etc. Please ensure the code is from a trusted source.</p>
			</div>
		</div>

		<?php rentfetch_render_property_editor_lazy_fragment( 'fees-preview', $post->ID ); ?>
	</div>
	<?php
}

/**
 * Render the resolved, read-only fees preview.
 *
 * @param WP_Post $post Property post.
 * @return void
 */
function rentfetch_render_property_fees_preview( $post ) {
	$fees_enabled = ! function_exists( 'rentfetch_should_show_property_fees' ) || rentfetch_should_show_property_fees();
	$settings_url = admin_url( 'admin.php?page=rentfetch-options&tab=properties&section=global-property-fees' );
	$monthly_context = function_exists( 'rentfetch_get_effective_monthly_required_fees_preview_context_for_property' )
		? rentfetch_get_effective_monthly_required_fees_preview_context_for_property( $post->ID, false )
		: array();
	$monthly_total = isset( $monthly_context['total'] ) ? (float) $monthly_context['total'] : 0.0;
	$source_context = function_exists( 'rentfetch_get_property_fees_display_source_context' )
		? rentfetch_get_property_fees_display_source_context( $post->ID, false )
		: array();
	$preview_markup = function_exists( 'rentfetch_get_property_fees_embed' )
		? rentfetch_get_property_fees_embed( $post->ID, false )
		: '';
	?>
	<div class="field rentfetch-admin-fees-preview-field">
		<div class="column rentfetch-admin-fees-preview-meta">
			<?php if ( ! $fees_enabled ) : ?>
				<div class="notice notice-warning inline rentfetch-admin-fees-disabled-notice">
					<p><strong>Property fees are turned off globally.</strong></p>
					<p>These fees will not render on the frontend. <a href="<?php echo esc_url( $settings_url ); ?>">Adjust the Property Fees settings</a>.</p>
				</div>
			<?php endif; ?>
			<label class="rentfetch-admin-fees-source-label">
				Fee source: <?php echo esc_html( (string) ( $source_context['source_label'] ?? 'No active fees source' ) ); ?>
			</label>
			<p class="description">This preview shows the fees content currently resolved for this property after source precedence is applied.</p>
		</div>
		<div class="column rentfetch-admin-fees-preview-content">
			<div class="rentfetch-admin-effective-pricing-total">
				<span>Effective total added to pricing</span>
				<strong><?php echo esc_html( '$' . number_format( $monthly_total, 2 ) . '/mo' ); ?></strong>
			</div>
			<?php if ( ! empty( trim( (string) $preview_markup ) ) ) : ?>
				<div class="rentfetch-admin-effective-fees-preview">
					<?php echo $preview_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php else : ?>
				<p class="description">No fees preview is currently available for this property.</p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
