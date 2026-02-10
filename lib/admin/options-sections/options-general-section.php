<?php
/**
 * This file includes the options for the general section
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Set defaults on activation
 */
function rentfetch_settings_set_defaults_general() {

	// Add option if it doesn't exist.
	add_option( 'rentfetch_options_data_sync', 'nosync' );
	add_option( 'rentfetch_options_disable_query_caching', '0' );
	add_option( 'rentfetch_options_enable_search_indexes', '1' );
	add_option( 'rentfetch_options_enable_cache_warming', '0' );
	add_option( 'rentfetch_options_enable_search_tracking', '1' );
	add_option( 'rentfetch_options_enable_analytics', '1' );
	add_option( 'rentfetch_options_enable_analytics_debug', '0' );
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_general' );

/**
 * Get the general settings section from the query string.
 *
 * @return string
 */
function rentfetch_settings_get_general_section() {
	$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';

	if ( '' === $section ) {
		return 'data-sync';
	}

	return $section;
}

/**
 * Adds the general settings section to the Rent Fetch settings page.
 */
function rentfetch_settings_general() {
	$section = rentfetch_settings_get_general_section();

	echo '<section id="rent-fetch-general-page" class="options-container">';
		echo '<div class="rent-fetch-options-nav-wrap">';
			echo '<div class="rent-fetch-options-sticky-wrap">';
				submit_button();

				echo '<ul class="rent-fetch-options-submenu">';

					$active = ( 'data-sync' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=general&section=data-sync" class="tab %s">Data Sync</a></li>', esc_html( $active ) );

					$active = ( 'performance' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=general&section=performance" class="tab %s">Performance</a></li>', esc_html( $active ) );

					$active = ( 'analytics' === $section ) ? 'tab-active' : '';
					printf( '<li><a href="?page=rentfetch-options&tab=general&section=analytics" class="tab %s">Analytics</a></li>', esc_html( $active ) );

				echo '</ul>';
			echo '</div>';
		echo '</div>';

		echo '<div class="container">';
			if ( 'performance' === $section ) {
				do_action( 'rentfetch_do_settings_general_performance' );
			} elseif ( 'analytics' === $section ) {
				do_action( 'rentfetch_do_settings_general_analytics' );
			} else {
				do_action( 'rentfetch_do_settings_general_data_sync' );
			}
		echo '</div><!-- .container -->';
	echo '</section><!-- #rent-fetch-general-page -->';
}
add_action( 'rentfetch_do_settings_general', 'rentfetch_settings_general' );

/**
 * Add the notice about the sync functionality (this will be removed by the sync plugin if it's installed).
 *
 * @return void
 */
function rentfetch_settings_sync_functionality_notice() {
	?>
	<div class="header">
		<h2 class="title">Data Sync</h2>
		<p class="description">Set up data sync settings and integrations for Rent Fetch.</p>
	</div>

	<div class="row">
		<div class="section">
			<h2 class="title">Automated Availability Sync Add-On</h2>
			<p class="description">Rent Fetch already supports unlimited manual entry for properties, floor plans, and units, and all layouts work with manually entered data.</p>
			<p>Need automation? <strong>Rent Fetch Sync</strong> can import properties and sync availability hourly with Yardi/RentCafe, RealPage, AppFolio, and Entrata. Learn more at <a href="https://rentfetch.io" target="_blank">rentfetch.io</a>.</p>
		</div>
	</div>
	<?php
}
add_action( 'rentfetch_do_settings_general_data_sync', 'rentfetch_settings_sync_functionality_notice', 25 );

/**
 * Output the performance section of general settings.
 *
 * @return void
 */
function rentfetch_settings_general_performance() {
	?>
	<div class="header">
		<h2 class="title">Performance</h2>
		<p class="description">Configure search result caching and search performance optimization settings.</p>
	</div>

	<div class="row">
		<div class="section">
			<label class="label-large">Search Result Caching</label>
			<p class="description">Cache search results for 30 minutes to improve performance. Results are cached in WordPress transients (Redis/Memcached if configured) and sent with cache headers for CDN/edge caching (Varnish, Fastly, Cloudflare, etc.). Recommended for best performance. Only disable if you need real-time data updates or are troubleshooting cache issues. <em>Note: Disabling this setting prevents future caching but does not purge existing CDN/edge caches. You may need to manually purge your cache (typically through your host or CDN provider) to see immediate changes.</em></p>
			<ul class="checkboxes">
				<li>
					<label for="rentfetch_options_disable_query_caching">
						<input type="checkbox" name="rentfetch_options_disable_query_caching" id="rentfetch_options_disable_query_caching" <?php checked( get_option( 'rentfetch_options_disable_query_caching' ), '0' ); ?>>
						Enable search result caching (recommended)
					</label>
				</li>
				<li>
					<label for="rentfetch_options_enable_cache_warming">
						<input type="checkbox" name="rentfetch_options_enable_cache_warming" id="rentfetch_options_enable_cache_warming" <?php checked( get_option( 'rentfetch_options_enable_cache_warming', '0' ), '1' ); ?>>
						Automatically pre-fetch popular searches every 25 minutes (recommended)
					</label>
				</li>
			</ul>

			<p>
				<button type="button" class="button" id="rentfetch-clear-cache">Clear Search Cache</button>
				<button type="button" class="button" id="rentfetch-warm-cache">Pre-fetch Popular Searches</button>
				<span id="rentfetch-cache-status" style="margin-left: 10px;"></span>
			</p>

			<p style="margin-top: 15px;">
				<a href="#" id="rentfetch-toggle-popular-searches" style="color: #2271b1;">View Tracked Searches</a>
			</p>
			<div id="rentfetch-popular-searches-container" style="display: none; margin-top: 15px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<p style="color: #646970; padding: 20px;"><em>Loading...</em></p>
			</div>

			<style>
				#rentfetch-toggle-popular-searches:focus {
					outline: none;
					box-shadow: none;
				}
				#rentfetch-popular-searches-container {
					overflow-x: auto;
					max-width: 100%;
				}
				#rentfetch-popular-searches-container table.rentfetch-popular-searches-table {
					width: 100%;
					table-layout: fixed;
					margin: 0;
				}
				#rentfetch-popular-searches-container table.rentfetch-popular-searches-table th,
				#rentfetch-popular-searches-container table.rentfetch-popular-searches-table td {
					padding: 8px 10px;
					vertical-align: top;
				}
				#rentfetch-popular-searches-container table.rentfetch-popular-searches-table th {
					white-space: nowrap;
				}
				#rentfetch-popular-searches-container .rentfetch-search-query {
					white-space: normal;
					overflow-wrap: anywhere;
					word-break: break-word;
				}
			</style>

			<script>
			jQuery(document).ready(function($) {
				// Clear cache button
				$('#rentfetch-clear-cache').on('click', function(e) {
					e.preventDefault();
					
					var $button = $(this);
					var $status = $('#rentfetch-cache-status');
					
					$button.prop('disabled', true).text('Clearing...');
					$status.html('<span style="color: #f0b849;">⏳ Clearing cache...</span>');
					
					$.post(ajaxurl, {
						action: 'rentfetch_clear_search_cache',
						nonce: '<?php echo esc_js( wp_create_nonce( 'rentfetch_clear_cache' ) ); ?>'
					}, function(response) {
						$button.prop('disabled', false).text('Clear Search Cache');
						
						if (response.success) {
							$status.html('<span style="color: #46b450;">✓ ' + response.data.message + '</span>');
							setTimeout(function() {
								$status.html('');
							}, 3000);
						} else {
							$status.html('<span style="color: #dc3232;">✗ ' + response.data.message + '</span>');
						}
					}).fail(function() {
						$button.prop('disabled', false).text('Clear Search Cache');
						$status.html('<span style="color: #dc3232;">✗ Error: Request failed. Please try again.</span>');
					});
				});

				// Warm cache button
				$('#rentfetch-warm-cache').on('click', function(e) {
					e.preventDefault();
					
					var $button = $(this);
					var $status = $('#rentfetch-cache-status');
					
					$button.prop('disabled', true).text('Pre-fetching...');
					$status.html('<span style="color: #f0b849;">⏳ Pre-fetching popular searches... this may take a moment.</span>');
					
					$.post(ajaxurl, {
						action: 'rentfetch_warm_cache',
						nonce: '<?php echo esc_js( wp_create_nonce( 'rentfetch_warm_cache' ) ); ?>'
					}, function(response) {
						$button.prop('disabled', false).text('Pre-fetch Popular Searches');
						
						if (response.success) {
							$status.html('<span style="color: #46b450;">✓ ' + response.data.message + '</span>');
							setTimeout(function() {
								$status.html('');
							}, 3000);
						} else {
							$status.html('<span style="color: #dc3232;">✗ ' + response.data.message + '</span>');
						}
					}).fail(function() {
						$button.prop('disabled', false).text('Pre-fetch Popular Searches');
						$status.html('<span style="color: #dc3232;">✗ Error: Request failed. Please try again.</span>');
					});
				});

				// Toggle popular searches accordion
				$('#rentfetch-toggle-popular-searches').on('click', function(e) {
					e.preventDefault();
					
					var $link = $(this);
					var $container = $('#rentfetch-popular-searches-container');
					
					if ($container.is(':visible')) {
						$container.slideUp();
						$link.text('View Tracked Searches');
					} else {
						$container.slideDown();
						$link.text('Hide Tracked Searches');
						
						// Load popular searches if not already loaded
						if ($container.find('table').length === 0) {
							$container.html('<p style="color: #646970; padding: 20px;"><em>Loading...</em></p>');
							
							$.post(ajaxurl, {
								action: 'rentfetch_get_popular_searches',
								nonce: '<?php echo esc_js( wp_create_nonce( 'rentfetch_popular_searches' ) ); ?>'
							}, function(response) {
								if (response.success && response.data.searches.length > 0) {
									var html = '<table class="widefat striped rentfetch-popular-searches-table" style="border: none;"><thead><tr>';
									html += '<th style="width: 90px;">Type</th>';
									html += '<th>Search Query</th>';
									html += '<th style="width: 110px; text-align: center;">Times Hit</th>';
									html += '<th style="width: 130px;">Last Executed</th>';
									html += '</tr></thead><tbody>';
									
									$.each(response.data.searches, function(index, search) {
										var displayQuery = search.display_query || '';

										html += '<tr>';
										html += '<td><span style="display: inline-block; padding: 3px 8px; background: #f0f0f1; border-radius: 3px; font-size: 11px; text-transform: uppercase; font-weight: 600; color: #2c3338;">' + search.type + '</span></td>';
										html += '<td class="rentfetch-search-query" style="font-family: Consolas, Monaco, monospace; font-size: 12px; color: #50575e;">' + displayQuery + '</td>';
										html += '<td style="text-align: center; font-weight: 600; color: #2271b1;">' + search.count + '</td>';
										html += '<td style="color: #646970; font-size: 13px;">' + search.last_used + '</td>';
										html += '</tr>';
									});
									
									html += '</tbody></table>';
									html += '<p style="padding: 15px; color: #646970; font-size: 13px;"><em>Showing searches that required database queries in the last 30 days. These are candidates for automatic cache warming. Note: Cached searches don\'t appear here until the cache expires and they need to be regenerated.</em></p>';
									
									$container.html(html);
								} else {
									$container.html('<p style="color: #646970; padding: 20px;"><em>No search data available yet. Searches will be tracked when they require database queries (not served from cache).</em></p>');
								}
							}).fail(function() {
								$container.html('<p style="color: #d63638;"><em>Error loading popular searches.</em></p>');
							});
						}
					}
				});
			});
			</script>
		</div>
	</div>

	<div class="row">
		<div class="section">
			<label class="label-large">Search Performance Optimization</label>
			<p class="description">Adds database indexes to improve search query performance. Indexes optimize searches by price, square footage, bedrooms, bathrooms, availability, city, and other common filters. This is especially beneficial for sites with large numbers of properties or floorplans. <em>Note: For larger databases (10,000+ posts), index creation can take up to a minute. Your site will remain functional during this process.</em></p>
			
			<p><strong>Current Status:</strong> <?php echo rentfetch_get_index_status(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			
			<ul class="checkboxes">
				<li>
					<label for="rentfetch_options_enable_search_indexes">
						<input type="checkbox" name="rentfetch_options_enable_search_indexes" id="rentfetch_options_enable_search_indexes" <?php checked( get_option( 'rentfetch_options_enable_search_indexes', '1' ), '1' ); ?>>
						Enable database indexes for faster searches (recommended)
					</label>
				</li>
			</ul>

			<p>
				<button type="button" class="button" id="rentfetch-rebuild-indexes">Rebuild Search Indexes</button>
				<span id="rentfetch-index-status" style="margin-left: 10px;"></span>
			</p>

			<script>
			jQuery(document).ready(function($) {
				$('#rentfetch-rebuild-indexes').on('click', function(e) {
					e.preventDefault();
					
					var $button = $(this);
					var $status = $('#rentfetch-index-status');
					
					$button.prop('disabled', true).text('Rebuilding...');
					$status.html('<span style="color: #f0b849;">⏳ Processing... this may take up to a minute for large databases.</span>');
					
					$.post(ajaxurl, {
						action: 'rentfetch_rebuild_indexes',
						nonce: '<?php echo esc_js( wp_create_nonce( 'rentfetch_rebuild_indexes' ) ); ?>'
					}, function(response) {
						$button.prop('disabled', false).text('Rebuild Search Indexes');
						
						if (response.success) {
							$status.html('<span style="color: #46b450;">✓ ' + response.data.message + '</span>');
							// Reload to update status display
							setTimeout(function() {
								location.reload();
							}, 2000);
						} else {
							$status.html('<span style="color: #dc3232;">✗ ' + response.data.message + '</span>');
						}
					}).fail(function() {
						$button.prop('disabled', false).text('Rebuild Search Indexes');
						$status.html('<span style="color: #dc3232;">✗ Error: Request failed. Please try again.</span>');
					});
				});
			});
			</script>
		</div>
	</div>
	<?php
}
add_action( 'rentfetch_do_settings_general_performance', 'rentfetch_settings_general_performance' );

/**
 * Output the analytics section of general settings.
 *
 * @return void
 */
function rentfetch_settings_general_analytics() {
	?>
	<div class="header">
		<h2 class="title">Analytics</h2>
		<p class="description">Enable or disable analytics event tracking for Rent Fetch templates.</p>
	</div>

	<div class="row">
		<div class="section">
			<label class="label-large">Analytics</label>
			<p class="description">Enable or disable analytics event tracking for Rent Fetch templates. When enabled, events are sent to any existing Google Analytics or Tag Manager setup found on the site.</p>

			<ul class="checkboxes">
				<li>
					<label for="rentfetch_options_enable_analytics">
						<input type="checkbox" name="rentfetch_options_enable_analytics" id="rentfetch_options_enable_analytics" <?php checked( get_option( 'rentfetch_options_enable_analytics', '1' ), '1' ); ?>>
						Enable analytics tracking (recommended)
					</label>
				</li>
				<li>
					<label for="rentfetch_options_enable_analytics_debug">
						<input type="checkbox" name="rentfetch_options_enable_analytics_debug" id="rentfetch_options_enable_analytics_debug" <?php checked( get_option( 'rentfetch_options_enable_analytics_debug', '0' ), '1' ); ?>>
						Enable analytics debug overlay on click
					</label>
				</li>
			</ul>
		</div>
	</div>
	<?php
}
add_action( 'rentfetch_do_settings_general_analytics', 'rentfetch_settings_general_analytics' );

/**
 * Save general data sync settings.
 *
 * @return void
 */
function rentfetch_save_settings_general_data_sync() {
	// * When we save this particular batch of settings, we want to re-check the license.
	delete_transient( 'rentfetchsync_properties_limit' );

	// * When we save this particular batch of settings, we might be changing the sync settings, so we need to unschedule all the sync actions.
	if ( function_exists( 'as_unschedule_all_actions' ) ) {
		as_unschedule_all_actions( 'rfs_do_sync' );
		as_unschedule_all_actions( 'rfs_yardi_do_delete_orphans' );
	}

	// Radio field.
	if ( isset( $_POST['rentfetch_options_data_sync'] ) ) {
		$options_data_sync = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_data_sync'] ) );
		update_option( 'rentfetch_options_data_sync', $options_data_sync );
	}

	// Checkboxes field.
	if ( isset( $_POST['rentfetch_options_enabled_integrations'] ) ) {
		$enabled_integrations = array_map( 'sanitize_text_field', wp_unslash( $_POST['rentfetch_options_enabled_integrations'] ) );
		update_option( 'rentfetch_options_enabled_integrations', $enabled_integrations );
	} else {
		update_option( 'rentfetch_options_enabled_integrations', array() );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_api_key'] ) ) {
		$options_yardi_integration_creds_yardi_api_key = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_api_key'] ) );
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_api_key', $options_yardi_integration_creds_yardi_api_key );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_company_code'] ) ) {
		$options_yardi_integration_creds_yardi_company_code = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_company_code'] ) );
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_company_code', $options_yardi_integration_creds_yardi_company_code );
	}

	// Textarea field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_voyager_code'] ) ) {
		$options_yardi_integration_creds_yardi_voyager_code = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_voyager_code'] ) );

		// Remove all whitespace.
		$options_yardi_integration_creds_yardi_voyager_code = preg_replace( '/\s+/', '', $options_yardi_integration_creds_yardi_voyager_code );

		// Add a space after each comma.
		$options_yardi_integration_creds_yardi_voyager_code = preg_replace( '/,/', ', ', $options_yardi_integration_creds_yardi_voyager_code );

		update_option( 'rentfetch_options_yardi_integration_creds_yardi_voyager_code', $options_yardi_integration_creds_yardi_voyager_code );
	}

	// Textarea field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_property_code'] ) ) {
		$options_yardi_integration_creds_yardi_property_code = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_property_code'] ) );

		// Remove all whitespace.
		$options_yardi_integration_creds_yardi_property_code = preg_replace( '/\s+/', '', $options_yardi_integration_creds_yardi_property_code );

		// Add a space after each comma.
		$options_yardi_integration_creds_yardi_property_code = preg_replace( '/,/', ', ', $options_yardi_integration_creds_yardi_property_code );

		update_option( 'rentfetch_options_yardi_integration_creds_yardi_property_code', $options_yardi_integration_creds_yardi_property_code );
	}

	// Single checkbox field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_enable_yardi_api_lead_generation'] ) ) {
		$options_yardi_integration_creds_enable_yardi_api_lead_generation = true;
	} else {
		$options_yardi_integration_creds_enable_yardi_api_lead_generation = false;
	}
	update_option( 'rentfetch_options_yardi_integration_creds_enable_yardi_api_lead_generation', $options_yardi_integration_creds_enable_yardi_api_lead_generation );

	// Text field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_username'] ) ) {
		$options_yardi_integration_creds_yardi_username = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_username'] ) );
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_username', $options_yardi_integration_creds_yardi_username );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_yardi_integration_creds_yardi_password'] ) ) {
		$options_yardi_integration_creds_yardi_password = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_yardi_integration_creds_yardi_password'] ) );
		update_option( 'rentfetch_options_yardi_integration_creds_yardi_password', $options_yardi_integration_creds_yardi_password );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_entrata_integration_creds_entrata_subdomain'] ) ) {
		$input_value = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_entrata_integration_creds_entrata_subdomain'] ) );

		// Extract the subdomain if a full URL is provided.
		$parsed_url = parse_url( $input_value );
		if ( isset( $parsed_url['host'] ) ) {
			$host_parts = explode( '.', $parsed_url['host'] );
			$options_entrata_integration_creds_entrata_subdomain = $host_parts[0];
		} else {
			$options_entrata_integration_creds_entrata_subdomain = $input_value;
		}

		update_option( 'rentfetch_options_entrata_integration_creds_entrata_subdomain', $options_entrata_integration_creds_entrata_subdomain );
	}

	// Textarea field.
	if ( isset( $_POST['rentfetch_options_entrata_integration_creds_entrata_property_ids'] ) ) {
		$options_entrata_integration_creds_entrata_property_ids = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_entrata_integration_creds_entrata_property_ids'] ) );

		// Remove all whitespace.
		$options_entrata_integration_creds_entrata_property_ids = preg_replace( '/\s+/', '', $options_entrata_integration_creds_entrata_property_ids );

		// Add a space after each comma.
		$options_entrata_integration_creds_entrata_property_ids = preg_replace( '/,/', ', ', $options_entrata_integration_creds_entrata_property_ids );

		update_option( 'rentfetch_options_entrata_integration_creds_entrata_property_ids', $options_entrata_integration_creds_entrata_property_ids );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_rentmanager_integration_creds_rentmanager_companycode'] ) ) {
		// Remove ".api.rentmanager.com" and anything that follows it.
		$input_value   = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_rentmanager_integration_creds_rentmanager_companycode'] ) );
		$cleaned_value = preg_replace( '/\.api\.rentmanager\.com.*/', '', $input_value );

		update_option( 'rentfetch_options_rentmanager_integration_creds_rentmanager_companycode', $cleaned_value );
	}

	if ( function_exists( 'rfs_get_rentmanager_properties_from_setting' ) ) {
		// This function is defined in the rentfetch-sync plugin, and allows for prefilling the properties for Rent Manager.
		rfs_get_rentmanager_properties_from_setting();
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_database_name'] ) ) {
		$options_appfolio_integration_creds_appfolio_database_name = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_database_name'] ) );

		// Remove .appfolio.com from the end of the database name.
		$options_appfolio_integration_creds_appfolio_database_name = preg_replace( '/.appfolio.com/', '', $options_appfolio_integration_creds_appfolio_database_name );

		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_database_name', $options_appfolio_integration_creds_appfolio_database_name );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_client_id'] ) ) {
		$options_appfolio_integration_creds_appfolio_client_id = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_client_id'] ) );
		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_client_id', $options_appfolio_integration_creds_appfolio_client_id );
	}

	// Text field.
	if ( isset( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_client_secret'] ) ) {
		$options_appfolio_integration_creds_appfolio_client_secret = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_client_secret'] ) );
		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_client_secret', $options_appfolio_integration_creds_appfolio_client_secret );
	}

	// Textarea field.
	if ( isset( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_property_ids'] ) ) {
		$options_appfolio_integration_creds_appfolio_property_ids = sanitize_text_field( wp_unslash( $_POST['rentfetch_options_appfolio_integration_creds_appfolio_property_ids'] ) );

		// Remove all whitespace.
		$options_appfolio_integration_creds_appfolio_property_ids = preg_replace( '/\s+/', '', $options_appfolio_integration_creds_appfolio_property_ids );

		// Add a space after each comma.
		$options_appfolio_integration_creds_appfolio_property_ids = preg_replace( '/,/', ', ', $options_appfolio_integration_creds_appfolio_property_ids );

		update_option( 'rentfetch_options_appfolio_integration_creds_appfolio_property_ids', $options_appfolio_integration_creds_appfolio_property_ids );
	}

	// * When we save this particular batch of settings, we want to always clear the transient that holds the API info.
	delete_transient( 'rentfetch_api_info' );
}

/**
 * Save general performance settings.
 *
 * @return void
 */
function rentfetch_save_settings_general_performance() {
	// Checkbox field - Enable query caching (inverted: checked = '0' for disable, unchecked = '1' for disable).
	$disable_query_caching = isset( $_POST['rentfetch_options_disable_query_caching'] ) ? '0' : '1';
	update_option( 'rentfetch_options_disable_query_caching', $disable_query_caching );

	// Checkbox field - Enable cache warming (checked = '1', unchecked = '0').
	$enable_cache_warming   = isset( $_POST['rentfetch_options_enable_cache_warming'] ) ? '1' : '0';
	$previous_cache_warming = get_option( 'rentfetch_options_enable_cache_warming', '0' );
	update_option( 'rentfetch_options_enable_cache_warming', $enable_cache_warming );

	// Schedule or unschedule cache warming based on setting change.
	if ( $enable_cache_warming !== $previous_cache_warming ) {
		if ( function_exists( 'rentfetch_schedule_cache_warming' ) ) {
			rentfetch_schedule_cache_warming();
		}
	}

	// If caching is disabled (value is '1'), clear existing transients.
	if ( '1' === $disable_query_caching ) {
		global $wpdb;
		$transients = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_rentfetch_%'" );
		foreach ( $transients as $transient ) {
			$key = str_replace( '_transient_', '', $transient );
			delete_transient( $key );
		}
	}

	// Checkbox field - Enable search indexes.
	$enable_search_indexes = isset( $_POST['rentfetch_options_enable_search_indexes'] ) ? '1' : '0';
	$previous_value        = get_option( 'rentfetch_options_enable_search_indexes', '1' );
	update_option( 'rentfetch_options_enable_search_indexes', $enable_search_indexes );

	// If the setting changed, create or remove indexes accordingly.
	if ( $enable_search_indexes !== $previous_value ) {
		if ( '1' === $enable_search_indexes ) {
			rentfetch_create_indexes();
		} else {
			rentfetch_remove_indexes();
		}
	}
}

/**
 * Save general analytics settings.
 *
 * @return void
 */
function rentfetch_save_settings_general_analytics() {
	// Checkbox field - Enable analytics.
	$enable_analytics = isset( $_POST['rentfetch_options_enable_analytics'] ) ? '1' : '0';
	update_option( 'rentfetch_options_enable_analytics', $enable_analytics );

	// Checkbox field - Enable analytics debug overlay.
	$enable_analytics_debug = isset( $_POST['rentfetch_options_enable_analytics_debug'] ) ? '1' : '0';
	update_option( 'rentfetch_options_enable_analytics_debug', $enable_analytics_debug );
}

/**
 * Save the general settings.
 *
 * @return void
 */
function rentfetch_save_settings_general() {
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	if ( $tab && 'general' !== $tab ) {
		return;
	}

	if ( ! $section ) {
		$section = 'data-sync';
	}

	if ( ! in_array( $section, array( 'data-sync', 'performance', 'analytics' ), true ) ) {
		return;
	}

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce.
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_main_options_nonce_action' ) ) {
		die( 'Security check failed' );
	}

	if ( 'performance' === $section ) {
		rentfetch_save_settings_general_performance();
	} elseif ( 'analytics' === $section ) {
		rentfetch_save_settings_general_analytics();
	} else {
		rentfetch_save_settings_general_data_sync();
	}
}
add_action( 'rentfetch_save_settings', 'rentfetch_save_settings_general' );
