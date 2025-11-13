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
}
register_activation_hook( RENTFETCH_BASENAME, 'rentfetch_settings_set_defaults_general' );

/**
 * Adds the general settings section to the Rent Fetch settings page.
 */
function rentfetch_settings_general() {

	// Silence is golden.
}
add_action( 'rentfetch_do_settings_general', 'rentfetch_settings_general' );

/**
 * Add the notice about the sync functionality (this will be removed by the sync plugin if it's installed)
 *
 * @return void
 */
function rentfetch_settings_sync_functionality_notice() {
	echo '<section id="rent-fetch-general-page" class="options-container">';
	
		echo '<div class="rent-fetch-options-nav-wrap">';
			echo '<div class="rent-fetch-options-sticky-wrap">';
				// add a wordpress save button here
				submit_button();
			echo '</div>';
		echo '</div>';
		
		echo '<div class="container">';
		?>
		<div class="header">
			<h2 class="title">Rent Fetch General Settings</h2>
			<p class="description">Let’s get started. Select from the options below to configure Rent Fetch and any integrations.</p>
		</div>

		<div class="row">
			<div class="section">
				<!-- <div class="white-box"> -->
					<h2 class="title">Our premium availability syncing addon</h2>
					<p class="description">You can already manually enter data for as many properties, floorplans, and units as you'd like, and all layouts are enabled for this information.</p><p>However, if you'd like to automate the addition of properties and sync availability information hourly, we offer the <strong>Rent Fetch Sync</strong> addon to sync data with the Yardi/RentCafe, Realpage, Appfolio, and Entrata platforms. More information at <a href="https://rentfetch.io" target="_blank">rentfetch.io</a></p>
				<!-- </div> -->
			</div>
		</div>
		<?php
		do_action( 'rentfetch_do_settings_general_shared' );

		echo '</div>';
	echo '</section><!-- #rent-fetch-general-page -->';
}
add_action( 'rentfetch_do_settings_general', 'rentfetch_settings_sync_functionality_notice', 25 );

/**
 * Add shared general settings
 *
 * @return void
 */
function rentfetch_settings_shared_general() {
	?>
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
						Automatically pre-fetch popular searches every 25 minutes
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
									var html = '<table class="widefat striped" style="border: none;"><thead><tr>';
									html += '<th style="width: 100px;">Type</th>';
									html += '<th>Search Query</th>';
									html += '<th style="width: 120px; text-align: center;">Times Hit</th>';
									html += '<th style="width: 140px;">Last Executed</th>';
									html += '</tr></thead><tbody>';
									
									$.each(response.data.searches, function(index, search) {
										var decodedQuery = decodeURIComponent(search.query.replace(/\+/g, ' '));
										var displayQuery = decodedQuery;
										
										// If query is empty or only has availability, show as "all available"
										if (decodedQuery === '' || decodedQuery === 'availability=1') {
											displayQuery = '(all available)';
										} else {
											// Remove availability parameter from other queries for cleaner display
											displayQuery = displayQuery.replace(/&?availability=[^&]*/g, '').replace(/^&/, '');
										}
										
										html += '<tr>';
										html += '<td><span style="display: inline-block; padding: 3px 8px; background: #f0f0f1; border-radius: 3px; font-size: 11px; text-transform: uppercase; font-weight: 600; color: #2c3338;">' + search.type + '</span></td>';
										html += '<td style="font-family: Consolas, Monaco, monospace; font-size: 12px; color: #50575e;">' + displayQuery + '</td>';
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
						Enable database indexes for faster searches
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
add_action( 'rentfetch_do_settings_general_shared', 'rentfetch_settings_shared_general' );

/**
 * Save the general settings
 */
function rentfetch_save_settings_general() {

	// Get the tab and section.
	$tab     = rentfetch_settings_get_tab();
	$section = rentfetch_settings_get_section();

	// this particular settings page has no tab or section, and it's the only one that doesn't.
	if ( $tab || $section ) {
		return;
	}

	$nonce = isset( $_POST['rentfetch_main_options_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['rentfetch_main_options_nonce_field'] ) ) : '';

	// * Verify the nonce
	if ( ! wp_verify_nonce( wp_unslash( $nonce ), 'rentfetch_main_options_nonce_action' ) ) {
		die( 'Security check failed' );
	}

	// * When we save this particular batch of settings, we want to re-check the license
	delete_transient( 'rentfetchsync_properties_limit' );

	// * When we save this particular batch of settings, we might be changing the sync settings, so we need to unschedule all the sync actions
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
		// this function is defined in the rentfetch-sync plugin, and allows for prefilling the properties for Rent Manager, where there are multiple locations possible.
		// and it's not feasible to have the user enter them all manually.
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

	// Checkbox field - Enable query caching (inverted: checked = '0' for disable, unchecked = '1' for disable)
	$disable_query_caching = isset( $_POST['rentfetch_options_disable_query_caching'] ) ? '0' : '1';
	update_option( 'rentfetch_options_disable_query_caching', $disable_query_caching );

	// Checkbox field - Enable cache warming (checked = '1', unchecked = '0')
	$enable_cache_warming = isset( $_POST['rentfetch_options_enable_cache_warming'] ) ? '1' : '0';
	$previous_cache_warming = get_option( 'rentfetch_options_enable_cache_warming', '0' );
	update_option( 'rentfetch_options_enable_cache_warming', $enable_cache_warming );

	// Schedule or unschedule cache warming based on setting change
	if ( $enable_cache_warming !== $previous_cache_warming ) {
		if ( function_exists( 'rentfetch_schedule_cache_warming' ) ) {
			rentfetch_schedule_cache_warming();
		}
	}

	// If caching is disabled (value is '1'), clear existing transients
	if ( $disable_query_caching === '1' ) {
		global $wpdb;
		$transients = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_rentfetch_%'" );
		foreach ( $transients as $transient ) {
			$key = str_replace( '_transient_', '', $transient );
			delete_transient( $key );
		}
	}

	// Checkbox field - Enable search indexes
	$enable_search_indexes = isset( $_POST['rentfetch_options_enable_search_indexes'] ) ? '1' : '0';
	$previous_value        = get_option( 'rentfetch_options_enable_search_indexes', '1' );
	update_option( 'rentfetch_options_enable_search_indexes', $enable_search_indexes );

	// If the setting changed, create or remove indexes accordingly
	if ( $enable_search_indexes !== $previous_value ) {
		if ( '1' === $enable_search_indexes ) {
			// Create indexes
			rentfetch_create_indexes();
		} else {
			// Remove indexes
			rentfetch_remove_indexes();
		}
	}

	// * When we save this particular batch of settings, we want to always clear the transient that holds the API info.
	delete_transient( 'rentfetch_api_info' );
}
add_action( 'rentfetch_save_settings', 'rentfetch_save_settings_general' );