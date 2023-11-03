<?php

/**
 * Adds Rent Fetch options page to the admin menu.
 */
add_action( 'admin_menu', 'rent_fetch_options_page' );
function rent_fetch_options_page() {
    // Get the contents of the Rent Fetch dashboard icon.
    $menu_icon = file_get_contents( RENTFETCH_DIR . 'images/rentfetch-dashboard-icon.svg' );
    
    // Add Rent Fetch options page to the admin menu.
    add_menu_page(
        'Rent Fetch Options', // Page title.
        'Rent Fetch', // Menu title.
        'manage_options', // Capability required to access the menu.
        'rent_fetch_options', // Menu slug.
        'rent_fetch_options_page_html', // Callback function to render the page.
        'data:image/svg+xml;base64,' . base64_encode( $menu_icon ), // Menu icon.
        58.99 // Menu position.
    );
    
    // Add Rent Fetch options sub-menu page to the admin menu.
    add_submenu_page(
        'rent_fetch_options', // Parent menu slug.
        'Settings', // Page title.
        'Settings', // Menu title.
        'manage_options', // Capability required to access the menu.
        'rent_fetch_options', // Menu slug.
        'rent_fetch_options_page_html' // Callback function to render the page.
    );
    
    // Add Rent Fetch options sub-menu page to the admin menu.
    add_submenu_page(
        'rent_fetch_options', // Parent menu slug.
        'Shortcodes', // Page title.
        'Shortcodes', // Menu title.
        'manage_options', // Capability required to access the menu.
        'rent_fetch_shortcodes', // Menu slug.
        'rent_fetch_shortcodes_page_html' // Callback function to render the page.
    );
        
    // Add Documentation sub-menu page to the admin menu, linking to a third-party URL.
    add_submenu_page(
        'rent_fetch_options', // Parent menu slug.
        'Documentation', // Page title.
        'Documentation', // Menu title.
        'manage_options', // Capability required to access the menu.
        'rent_fetch_documentation', // Menu slug.
        'rent_fetch_documentation_page_html' // Callback function to render the page.
    );
}

/**
 * If the rent_fetch_message parameter is set to 'success', display a success message.
 */
add_action( 'admin_notices', 'rent_fetch_options_page_notice' );
function rent_fetch_options_page_notice() {
    if ( isset( $_GET['rent_fetch_message'] ) && $_GET['rent_fetch_message'] === 'success' ) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'Rent Fetch settings successfully saved.', 'rent-fetch' ); ?></p>
        </div>
        <?php
    }
}


/**
 * Force the documentation link to go to a third-party URL.
 */
add_action( 'admin_footer', 'rentfetch_documentation_submenu_open_new_tab' );    
function rentfetch_documentation_submenu_open_new_tab() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('a[href="admin.php?page=rent_fetch_documentation"').each(function () {
            if ($(this).text() == 'Documentation') {
                $(this).css('color', 'yellow');
                $(this).attr('href', 'https://github.com/jonschr/rent-fetch');
                $(this).attr('target','_blank');
            }
        });
    });
    </script>
    <?php
}

function rent_fetch_shortcodes_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    ?>
    <script>
        jQuery(document).ready(function($) {
            // Get all .shortcode elements
            const shortcodes = document.querySelectorAll('.shortcode');

            // Add event listener to each .shortcode element
            shortcodes.forEach(shortcode => {
                shortcode.addEventListener('click', () => {
                    // Remove the .copied class from all .shortcode elements
                    shortcodes.forEach(element => {
                        element.classList.remove('copied');
                    });

                    // Copy the contents of the clicked .shortcode element to the clipboard
                    const range = document.createRange();
                    range.selectNodeContents(shortcode);
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                    document.execCommand('copy');
                    selection.removeAllRanges();

                    // Add the .copied class to the clicked .shortcode element
                    shortcode.classList.add('copied');

                    // Remove the .copied class after 5 seconds
                    setTimeout(() => {
                        shortcode.classList.remove('copied');
                    }, 5000);
                });
            });
        });
    </script>
    <?php
    
    echo '<div class="wrap">';
        echo '<h1>Rent Fetch Shortcodes</h1>';
        echo '<p>Rent Fetch includes a number of shortcodes that can be used wherever you\'d like on your site. <strong>Click any of them below to copy them.</strong></p>';
        do_action( 'rent_fetch_do_documentation_shortcodes' );
    echo '</div>';
}

add_action( 'rent_fetch_do_documentation_shortcodes', 'rent_fetch_documentation_shortcodes' );
function rent_fetch_documentation_shortcodes() {
    ?>
    <h2>Multiple properties search</h2>
    <p>The main properties search can be rendered using the default shortcode, which will create a side-by-side layout with the properties and search filters next to the map, or you can render each component individually to make the layout work however you'd like it to.</p>
    <h3>Default search</h3>
    <p>This one includes everything; just use this and you're done. This will attempt to force itself to be full-width on the page regardless of your theme styles.</p>
    <p><span class="shortcode">[propertysearch]</span></p>
    <h3>Individual components</h3>
    <p>Use these individually to arrange various components. It's quite likely, using these, that you'll need to write some styles to position them the way you'd like on the page.</p>
    <p><span class="shortcode">[propertysearchmap]</span> <span class="shortcode">[propertysearchfilters]</span> <span class="shortcode">[propertysearchresults]</span></p>
    
    <h2>Properties grid</h2>
    <p>This layout ignores availability, and is most suitable for smaller ownership groups with 5-20 properties.</p>
    <p><span class="shortcode">[properties]</span></p>
    
    <h2>Floorplans search</h2>
    <p>This layout ignores availability, and is most suitable for very small ownership groups, listing 1-5 properties.</p>
    <h3>Default search</h3>
    <p><span class="shortcode">[floorplansearch]</span></p>
    <h3>Individual components</h3>
    <p><span class="shortcode">[floorplansearchfilters]</span><span class="shortcode">[floorplansearchresults]</span></p>
    <?php
}

function rent_fetch_options_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <form method="post" class="rent-fetch-options" action="<?php echo esc_url( admin_url( 'admin-post.php' ) );  ?>">
            <div class="top-right-submit">
                <?php submit_button(); ?>
            </div>
            <h1>Rent Fetch Options</h1>
            <nav class="nav-tab-wrapper">
                <a href="?page=rent_fetch_options" class="nav-tab<?php if (!isset($_GET['tab']) || $_GET['tab'] === 'general') { echo ' nav-tab-active'; } ?>">General</a>
                <a href="?page=rent_fetch_options&tab=google" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'google') { echo ' nav-tab-active'; } ?>">Google</a>
                <a href="?page=rent_fetch_options&tab=properties" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'properties') { echo ' nav-tab-active'; } ?>">Properties</a>
                <a href="?page=rent_fetch_options&tab=floorplans" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'floorplans') { echo ' nav-tab-active'; } ?>">Floorplans</a>
                <a href="?page=rent_fetch_options&tab=labels" class="nav-tab<?php if (isset($_GET['tab']) && $_GET['tab'] === 'labels') { echo ' nav-tab-active'; } ?>">Labels</a>
            </nav>
        
            <input type="hidden" name="action" value="rent_fetch_process_form">
            <?php wp_nonce_field( 'rent_fetch_nonce', 'rent_fetch_form_nonce' ); ?>
            <?php $rent_fetch_options_nonce = wp_create_nonce( 'rent_fetch_options_nonce' );  ?>
            
            <?php
            
            if ( !isset($_GET['tab']) || $_GET['tab'] === 'general') {
                do_action( 'rent_fetch_do_settings_general' );
            } elseif (isset($_GET['tab']) && $_GET['tab'] === 'google') {
                do_action( 'rent_fetch_do_settings_google' );
            } elseif (isset($_GET['tab']) && $_GET['tab'] === 'properties') {
                do_action( 'rent_fetch_do_settings_properties' );
            } elseif (isset($_GET['tab']) && $_GET['tab'] === 'property_search') {
                do_action( 'rent_fetch_do_settings_property_search' );
            } elseif (isset($_GET['tab']) && $_GET['tab'] === 'property_archives') {
                do_action( 'rent_fetch_do_settings_property_archives' );
            } elseif (isset($_GET['tab']) && $_GET['tab'] === 'single_property_template') {
                do_action( 'rent_fetch_do_settings_single_property_template' );
            } elseif (isset($_GET['tab']) && $_GET['tab'] === 'floorplans') {
                do_action( 'rent_fetch_do_settings_floorplans' );
            } elseif (isset($_GET['tab']) && $_GET['tab'] === 'labels') {
                do_action( 'rent_fetch_do_settings_labels' );
            } else {

            }
            
            submit_button(); 
            ?>
            
        </form>
    </div>

    <?php
}

/**
 * Save the form data for ALL tabs on the Rent Fetch settings page
 */
add_action( 'admin_post_rent_fetch_process_form', 'rent_fetch_process_form_data' );
function rent_fetch_process_form_data() {
    
    //* Verify the nonce
    if ( ! wp_verify_nonce( $_POST['rent_fetch_form_nonce'], 'rent_fetch_nonce' ) ) {
        die( 'Security check failed' );
    }
    
    //* Save the settings
    do_action( 'rent_fetch_save_settings' );
    
    //* Redirect back to the form page with a success message
    // wp_redirect( add_query_arg( 'rent_fetch_message', 'success', 'admin.php?page=rent_fetch_options' ) );
        
    //* Redirect back to the current page with a success message
    $referrer = $_SERVER['HTTP_REFERER'];
    
    // remove the URL from the referrer
    $referrer = preg_replace('/https?:\/\/[^\/]+/', '', $referrer);
    
    // remove /wp-admin/ from the referrer
    $referrer = preg_replace('/\/wp-admin\//', '', $referrer);
    
    // var_dump( $referrer );
    
    wp_redirect( add_query_arg( 'rent_fetch_message', 'success', $referrer ) );
    
    exit;

}

/**
 * Adds the general settings section to the Rent Fetch settings page.
 */
add_action( 'rent_fetch_do_settings_general', 'rent_fetch_settings_general' );
function rent_fetch_settings_general() {
    ?>
    <div class="row">
        <div class="column">
            <label for="options_rent_fetch_api_key">Rent Fetch API Key</label>
        </div>
        <div class="column">
            <input type="text" name="options_rent_fetch_api_key" id="options_rent_fetch_api_key" value="<?php echo esc_attr( get_option( 'options_rent_fetch_api_key' ) ); ?>">
            <p class="description">Required for syncing any data down from an API.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_apartment_site_type">Site type</label>
        </div>
        <div class="column">
            <select name="options_apartment_site_type" id="options_apartment_site_type" value="<?php echo esc_attr( get_option( 'options_apartment_site_type' ) ); ?>">
                <option value="single" <?php selected( get_option( 'options_apartment_site_type' ), 'single' ); ?>>This site is for a single property</option>
                <option value="multiple" <?php selected( get_option( 'options_apartment_site_type' ), 'multiple' ); ?>>This site is for multiple properties</option>
            </select>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_data_sync">Data Sync</label>
            <p class="description">When you start syncing from data from your management software, it generally takes 5-15 seconds per property to sync. <strong>Rome wasn't built in a day.</strong></p>
        </div>
        <div class="column">
            <ul class="radio">
                <li>
                    <label>
                        <input type="radio" name="options_data_sync" id="options_data_sync" value="nosync" <?php checked( get_option( 'options_data_sync' ), 'nosync' ); ?>>
                        Pause all syncing from all APIs
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="options_data_sync" id="options_data_sync" value="updatesync" <?php checked( get_option( 'options_data_sync' ), 'updatesync' ); ?>>
                        Update data on this site with data from the API. This option should never modify manually-added properties/floorplans, nor should it overwrite any custom data you've added to otherwise synced properties/floorplans.
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="options_data_sync" id="options_data_sync" value="delete" <?php checked( get_option( 'options_data_sync' ), 'delete' ); ?>>
                        <span style="color: red;">Delete all data that's been pulled from a third-party API. <strong style="color: white; background-color: red; padding: 3px 5px; border-radius: 3px;">This will take place immediately upon saving. There is no undo.</strong></span>
                    </label>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- <div class="row">
        <div class="column">
            <label for="options_sync_term">Sync Term</label>
        </div>
        <div class="column">
            <p class="description">If you're seeing repeated API failures, pausing this temporarily can often clean up zombie tasks that apply to properties no longer listed in your API settings. It can also serve to reset overdue tasks in the event that API functionality was unavailable for a period (for example, on a staging site behind basic authentication).</p>
            <select name="options_sync_term" id="options_sync_term" value="<?php echo esc_attr( get_option( 'options_sync_term' ) ); ?>">
                <option value="paused" <?php selected( get_option( 'options_sync_term' ), 'paused' ); ?>>Paused</option>
                <option value="hourly" <?php selected( get_option( 'options_sync_term' ), 'hourly' ); ?>>Hourly</option>
            </select>
        </div>
    </div> -->
    
    <div class="row">
        <div class="column">
            <label for="options_enabled_integrations">Enabled Integrations</label>
        </div>
        <div class="column">
            <script type="text/javascript">
                jQuery(document).ready(function( $ ) {
	
                    $( '.integration' ).hide();
                    
                    // on load and on change of input[name="options_enabled_integrations[]"], show/hide the integration options
                    $( 'input[name="options_enabled_integrations[]"]' ).on( 'change', function() {
                        
                        // hide all the integration options
                        $( '.integration' ).hide();
                        
                        // show the integration options for the checked integrations
                        $( 'input[name="options_enabled_integrations[]"]:checked' ).each( function() {
                            $( '.integration.' + $( this ).val() ).show();
                        });
                        
                    }).trigger( 'change' );
                    
                });
                
            </script>
            <ul class="checkboxes">
                <li>
                    <label>
                        <input type="checkbox" name="options_enabled_integrations[]" value="yardi" <?php checked( in_array( 'yardi', get_option( 'options_enabled_integrations', array() ) ) ); ?>>
                        Yardi/RentCafe
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_enabled_integrations[]" value="entrata" <?php checked( in_array( 'entrata', get_option( 'options_enabled_integrations', array() ) ) ); ?>>
                        Entrata
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_enabled_integrations[]" value="realpage" <?php checked( in_array( 'realpage', get_option( 'options_enabled_integrations', array() ) ) ); ?>>
                        RealPage
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_enabled_integrations[]" value="appfolio" <?php checked( in_array( 'appfolio', get_option( 'options_enabled_integrations', array() ) ) ); ?>>
                        Appfolio
                    </label>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="row integration yardi">
        <div class="column">
            <label>Yardi/RentCafe</label>
        </div>
        <div class="column">
            <div class="white-box">
                <label for="options_yardi_integration_creds_yardi_api_key">Yardi API Key</label>
                <input type="text" name="options_yardi_integration_creds_yardi_api_key" id="options_yardi_integration_creds_yardi_api_key" value="<?php echo esc_attr( get_option( 'options_yardi_integration_creds_yardi_api_key' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_yardi_integration_creds_yardi_voyager_code">Yardi Voyager Codes</label>
                <textarea rows="10" style="width: 100%;" name="options_yardi_integration_creds_yardi_voyager_code" id="options_yardi_integration_creds_yardi_voyager_code"><?php echo esc_attr( get_option( 'options_yardi_integration_creds_yardi_voyager_code' ) ); ?></textarea>
                <p class="description">Multiple property codes should be entered separated by commas</p>
            </div>
            <div class="white-box">
                <label for="options_yardi_integration_creds_yardi_property_code">Yardi Property Codes</label>
                <textarea rows="10" style="width: 100%;" name="options_yardi_integration_creds_yardi_property_code" id="options_yardi_integration_creds_yardi_property_code"><?php echo esc_attr( get_option( 'options_yardi_integration_creds_yardi_property_code' ) ); ?></textarea>
                <p class="description">Multiple property codes should be entered separated by commas</p>
            </div>
            <!-- <div class="white-box">
                <label for="options_yardi_integration_creds_enable_yardi_api_lead_generation">
                    <input type="checkbox" name="options_yardi_integration_creds_enable_yardi_api_lead_generation" id="options_yardi_integration_creds_enable_yardi_api_lead_generation" <?php checked( get_option( 'options_yardi_integration_creds_enable_yardi_api_lead_generation' ), true ); ?>>
                    Enable Yardi API Lead Generation
                </label>
                <p class="description">Adds a lightbox form on the single properties template which can send leads directly to the Yardi API.</p>
            </div> -->
            <div class="white-box">
                <label for="options_yardi_integration_creds_yardi_username">Yardi Username</label>
                <input type="text" name="options_yardi_integration_creds_yardi_username" id="options_yardi_integration_creds_yardi_username" value="<?php echo esc_attr( get_option( 'options_yardi_integration_creds_yardi_username' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_yardi_integration_creds_yardi_password">Yardi Password</label>
                <input type="text" name="options_yardi_integration_creds_yardi_password" id="options_yardi_integration_creds_yardi_password" value="<?php echo esc_attr( get_option( 'options_yardi_integration_creds_yardi_password' ) ); ?>">
            </div>
        </div>
    </div>
    
    <div class="row integration entrata">
        <div class="column">
            <label>Entrata</label>
        </div>
        <div class="column">
            <div class="white-box">
                <label for="options_entrata_integration_creds_entrata_user">Entrata Username</label>
                <input type="text" name="options_entrata_integration_creds_entrata_user" id="options_entrata_integration_creds_entrata_user" value="<?php echo esc_attr( get_option( 'options_entrata_integration_creds_entrata_user' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_entrata_integration_creds_entrata_pass">Entrata Password</label>
                <input type="text" name="options_entrata_integration_creds_entrata_pass" id="options_entrata_integration_creds_entrata_pass" value="<?php echo esc_attr( get_option( 'options_entrata_integration_creds_entrata_pass' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_entrata_integration_creds_entrata_property_ids">Entrata Property IDs</label>
                <textarea rows="10" style="width: 100%;" name="options_entrata_integration_creds_entrata_property_ids" id="options_entrata_integration_creds_entrata_property_ids"><?php echo esc_attr( get_option( 'options_entrata_integration_creds_entrata_property_ids' ) ); ?></textarea>
                <p class="description">If there are multiple properties to be pulled in, enter those separated by commas</p>
            </div>
        </div>
    </div>
    
    <div class="row integration realpage">
        <div class="column">
            <label>RealPage</label>
        </div>
        <div class="column">
            <div class="white-box">
                <label for="options_realpage_integration_creds_realpage_user">RealPage Username</label>
                <input type="text" name="options_realpage_integration_creds_realpage_user" id="options_realpage_integration_creds_realpage_user" value="<?php echo esc_attr( get_option( 'options_realpage_integration_creds_realpage_user' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_realpage_integration_creds_realpage_pass">RealPage Password</label>
                <input type="text" name="options_realpage_integration_creds_realpage_pass" id="options_realpage_integration_creds_realpage_pass" value="<?php echo esc_attr( get_option( 'options_realpage_integration_creds_realpage_pass' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_realpage_integration_creds_realpage_pmc_id">RealPage PMC ID</label>
                <input type="text" name="options_realpage_integration_creds_realpage_pmc_id" id="options_realpage_integration_creds_realpage_pmc_id" value="<?php echo esc_attr( get_option( 'options_realpage_integration_creds_realpage_pmc_id' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_realpage_integration_creds_realpage_site_ids">RealPage Site IDs</label>
                <textarea rows="10" style="width: 100%;" name="options_realpage_integration_creds_realpage_site_ids" id="options_realpage_integration_creds_realpage_site_ids"><?php echo esc_attr( get_option( 'options_realpage_integration_creds_realpage_site_ids' ) ); ?></textarea>
                <p class="description">If there are multiple properties to be pulled in, enter those separated by commas</p>
            </div>
        </div>
    </div>
    
    <div class="row integration appfolio">
        <div class="column">
            <label>AppFolio</label>
        </div>
        <div class="column">
            <div class="white-box">
                <label for="options_appfolio_integration_creds_appfolio_database_name">Appfolio Database Name</label>
                <input type="text" name="options_appfolio_integration_creds_appfolio_database_name" id="options_appfolio_integration_creds_appfolio_database_name" value="<?php echo esc_attr( get_option( 'options_appfolio_integration_creds_appfolio_database_name' ) ); ?>">
                <p class="description">Typically this is xxxxxxxxxxx.appfolio.com</p>
            </div>
            <div class="white-box">
                <label for="options_appfolio_integration_creds_appfolio_client_id">Appfolio Client ID</label>
                <input type="text" name="options_appfolio_integration_creds_appfolio_client_id" id="options_appfolio_integration_creds_appfolio_client_id" value="<?php echo esc_attr( get_option( 'options_appfolio_integration_creds_appfolio_client_id' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_appfolio_integration_creds_appfolio_client_secret">Appfolio Client Secret</label>
                <input type="text" name="options_appfolio_integration_creds_appfolio_client_secret" id="options_appfolio_integration_creds_appfolio_client_secret" value="<?php echo esc_attr( get_option( 'options_appfolio_integration_creds_appfolio_client_secret' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_appfolio_integration_creds_appfolio_property_ids">Appfolio Property IDs</label>
                <textarea rows="10" style="width: 100%;" name="options_appfolio_integration_creds_appfolio_property_ids" id="options_appfolio_integration_creds_appfolio_property_ids"><?php echo esc_attr( get_option( 'options_appfolio_integration_creds_appfolio_property_ids' ) ); ?></textarea>
                <p class="description">For AppFolio, this is an optional field. If left blank, Rent Fetch will simply fetch all of the properties in the account, which may or not be your preference. Please note that if property IDs are present here, all *other* synced properties through AppFolio will be deleted when the site next syncs.</p>
            </div>
        </div>
    </div>
         
   
         
    <?php
}

/**
 * Save the general settings
 */
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_general' );
function rent_fetch_save_settings_general() {
    
    // Text field
    if ( isset( $_POST['options_rent_fetch_api_key']) ) {
        $options_rent_fetch_api_key = sanitize_text_field( $_POST['options_rent_fetch_api_key'] );
        update_option( 'options_rent_fetch_api_key', $options_rent_fetch_api_key );
    }
    
    // Select field
    if ( isset( $_POST['options_apartment_site_type']) ) {
        $options_apartment_site_type = sanitize_text_field( $_POST['options_apartment_site_type'] );
        update_option( 'options_apartment_site_type', $options_apartment_site_type );
    }
    
    // Radio field
    if ( isset( $_POST['options_data_sync'] ) ) {
        $options_data_sync = sanitize_text_field( $_POST['options_data_sync'] );
        update_option( 'options_data_sync', $options_data_sync );
    }
    
    // Select field
    // if ( isset( $_POST['options_sync_term'] ) ) {
    //     $options_sync_term = sanitize_text_field( $_POST['options_sync_term'] );
    //     update_option( 'options_sync_term', $options_sync_term );
    // }
    
    // Checkboxes field
    if ( isset ( $_POST['options_enabled_integrations'] ) ) {
        $enabled_integrations = array_map('sanitize_text_field', $_POST['options_enabled_integrations']);
        update_option('options_enabled_integrations', $enabled_integrations);
    }
    
    // Text field
    if ( isset( $_POST['options_yardi_integration_creds_yardi_api_key'] ) ) {
        $options_yardi_integration_creds_yardi_api_key = sanitize_text_field( $_POST['options_yardi_integration_creds_yardi_api_key'] );
        update_option( 'options_yardi_integration_creds_yardi_api_key', $options_yardi_integration_creds_yardi_api_key );
    }
    
    // Textarea field
    if ( isset( $_POST['options_yardi_integration_creds_yardi_voyager_code'] ) ) {
        $options_yardi_integration_creds_yardi_voyager_code = sanitize_text_field( $_POST['options_yardi_integration_creds_yardi_voyager_code'] );
        
        // Remove all whitespace
        $options_yardi_integration_creds_yardi_voyager_code = preg_replace('/\s+/', '', $options_yardi_integration_creds_yardi_voyager_code);
        
        // Add a space after each comma
        $options_yardi_integration_creds_yardi_voyager_code = preg_replace('/,/', ', ', $options_yardi_integration_creds_yardi_voyager_code);
        
        update_option( 'options_yardi_integration_creds_yardi_voyager_code', $options_yardi_integration_creds_yardi_voyager_code );
    }
    
    // Textarea field
    if ( isset( $_POST['options_yardi_integration_creds_yardi_property_code'] ) ) {
        $options_yardi_integration_creds_yardi_property_code = sanitize_text_field( $_POST['options_yardi_integration_creds_yardi_property_code'] );
        
        // Remove all whitespace
        $options_yardi_integration_creds_yardi_property_code = preg_replace('/\s+/', '', $options_yardi_integration_creds_yardi_property_code);
        
        // Add a space after each comma
        $options_yardi_integration_creds_yardi_property_code = preg_replace('/,/', ', ', $options_yardi_integration_creds_yardi_property_code);
        
        update_option( 'options_yardi_integration_creds_yardi_property_code', $options_yardi_integration_creds_yardi_property_code );
    }
    
    // Single checkbox field
    if ( isset( $_POST['options_yardi_integration_creds_enable_yardi_api_lead_generation'] ) ) {
        $options_yardi_integration_creds_enable_yardi_api_lead_generation = true;
    } else {
        $options_yardi_integration_creds_enable_yardi_api_lead_generation = false;
    }
    update_option( 'options_yardi_integration_creds_enable_yardi_api_lead_generation', $options_yardi_integration_creds_enable_yardi_api_lead_generation );

    
    // Text field
    if ( isset( $_POST['options_yardi_integration_creds_yardi_username'] ) ) {
        $options_yardi_integration_creds_yardi_username = sanitize_text_field( $_POST['options_yardi_integration_creds_yardi_username'] );
        update_option( 'options_yardi_integration_creds_yardi_username', $options_yardi_integration_creds_yardi_username );
    }
    
    // Text field
    if ( isset( $_POST['options_yardi_integration_creds_yardi_password'] ) ) {
        $options_yardi_integration_creds_yardi_password = sanitize_text_field( $_POST['options_yardi_integration_creds_yardi_password'] );
        update_option( 'options_yardi_integration_creds_yardi_password', $options_yardi_integration_creds_yardi_password );
    }
    
    // Text field
    if ( isset( $_POST['options_entrata_integration_creds_entrata_user'] ) ) {
        $options_entrata_integration_creds_entrata_user = sanitize_text_field( $_POST['options_entrata_integration_creds_entrata_user'] );
        update_option( 'options_entrata_integration_creds_entrata_user', $options_entrata_integration_creds_entrata_user );
    }
    
    // Text field
    if ( isset( $_POST['options_entrata_integration_creds_entrata_pass'] ) ) {
        $options_entrata_integration_creds_entrata_pass = sanitize_text_field( $_POST['options_entrata_integration_creds_entrata_pass'] );
        update_option( 'options_entrata_integration_creds_entrata_pass', $options_entrata_integration_creds_entrata_pass );
    }
    
    // Textarea field
    if ( isset( $_POST['options_entrata_integration_creds_entrata_property_ids'] ) ) {
        $options_entrata_integration_creds_entrata_property_ids = sanitize_text_field( $_POST['options_entrata_integration_creds_entrata_property_ids'] );
        
        // Remove all whitespace
        $options_entrata_integration_creds_entrata_property_ids = preg_replace('/\s+/', '', $options_entrata_integration_creds_entrata_property_ids);
        
        // Add a space after each comma
        $options_entrata_integration_creds_entrata_property_ids = preg_replace('/,/', ', ', $options_entrata_integration_creds_entrata_property_ids);
        
        update_option( 'options_entrata_integration_creds_entrata_property_ids', $options_entrata_integration_creds_entrata_property_ids );
    }
    
    // Text field
    if ( isset( $_POST['options_realpage_integration_creds_realpage_user'] ) ) {
        $options_realpage_integration_creds_realpage_user = sanitize_text_field( $_POST['options_realpage_integration_creds_realpage_user'] );
        update_option( 'options_realpage_integration_creds_realpage_user', $options_realpage_integration_creds_realpage_user );
    }
    
    // Text field
    if ( isset( $_POST['options_realpage_integration_creds_realpage_pass'] ) ) {
        $options_realpage_integration_creds_realpage_pass = sanitize_text_field( $_POST['options_realpage_integration_creds_realpage_pass'] );
        update_option( 'options_realpage_integration_creds_realpage_pass', $options_realpage_integration_creds_realpage_pass );
    }
    
    // Text field
    if ( isset( $_POST['options_realpage_integration_creds_realpage_pmc_id'] ) ) {
        $options_realpage_integration_creds_realpage_pmc_id = sanitize_text_field( $_POST['options_realpage_integration_creds_realpage_pmc_id'] );
        update_option( 'options_realpage_integration_creds_realpage_pmc_id', $options_realpage_integration_creds_realpage_pmc_id );
    }
    
    // Textarea field
    if ( isset( $_POST['options_realpage_integration_creds_realpage_site_ids'] ) ) {
        $options_realpage_integration_creds_realpage_site_ids = sanitize_text_field( $_POST['options_realpage_integration_creds_realpage_site_ids'] );
        
        // Remove all whitespace
        $options_realpage_integration_creds_realpage_site_ids = preg_replace('/\s+/', '', $options_realpage_integration_creds_realpage_site_ids);
        
        // Add a space after each comma
        $options_realpage_integration_creds_realpage_site_ids = preg_replace('/,/', ', ', $options_realpage_integration_creds_realpage_site_ids);
        
        update_option( 'options_realpage_integration_creds_realpage_site_ids', $options_realpage_integration_creds_realpage_site_ids );
    }
    
    // Text field
    if ( isset( $_POST['options_appfolio_integration_creds_appfolio_database_name'] ) ) {
        $options_appfolio_integration_creds_appfolio_database_name = sanitize_text_field( $_POST['options_appfolio_integration_creds_appfolio_database_name'] );
        
        // Remove .appfolio.com from the end of the database name
        $options_appfolio_integration_creds_appfolio_database_name = preg_replace('/.appfolio.com/', '', $options_appfolio_integration_creds_appfolio_database_name);
        
        update_option( 'options_appfolio_integration_creds_appfolio_database_name', $options_appfolio_integration_creds_appfolio_database_name );
    }
    
    // Text field
    if ( isset( $_POST['options_appfolio_integration_creds_appfolio_client_id'] ) ) {
        $options_appfolio_integration_creds_appfolio_client_id = sanitize_text_field( $_POST['options_appfolio_integration_creds_appfolio_client_id'] );
        update_option( 'options_appfolio_integration_creds_appfolio_client_id', $options_appfolio_integration_creds_appfolio_client_id );
    }
    
    // Text field
    if ( isset( $_POST['options_appfolio_integration_creds_appfolio_client_secret'] ) ) {
        $options_appfolio_integration_creds_appfolio_client_secret = sanitize_text_field( $_POST['options_appfolio_integration_creds_appfolio_client_secret'] );
        update_option( 'options_appfolio_integration_creds_appfolio_client_secret', $options_appfolio_integration_creds_appfolio_client_secret );
    }
    
    // Textarea field
    if ( isset( $_POST['options_appfolio_integration_creds_appfolio_property_ids'] ) ) {
        $options_appfolio_integration_creds_appfolio_property_ids = sanitize_text_field( $_POST['options_appfolio_integration_creds_appfolio_property_ids'] );
        
        // Remove all whitespace
        $options_appfolio_integration_creds_appfolio_property_ids = preg_replace('/\s+/', '', $options_appfolio_integration_creds_appfolio_property_ids);
        
        // Add a space after each comma
        $options_appfolio_integration_creds_appfolio_property_ids = preg_replace('/,/', ', ', $options_appfolio_integration_creds_appfolio_property_ids);
        
        update_option( 'options_appfolio_integration_creds_appfolio_property_ids', $options_appfolio_integration_creds_appfolio_property_ids );
    }
    
}

/**
 * Adds the Google settings section to the Rent Fetch settings page
 */
add_action( 'rent_fetch_do_settings_google', 'rent_fetch_settings_google' );
function rent_fetch_settings_google() {
    ?>
    
    <div class="row">
        <div class="column">
            <label for="options_google_maps_api_key">Google Maps API Key</label>
        </div>
        <div class="column">
            <input type="text" name="options_google_maps_api_key" id="options_google_maps_api_key" value="<?php echo esc_attr( get_option( 'options_google_maps_api_key' ) ); ?>">
            <p class="description">Required for Google Maps.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_google_map_marker">Google Maps Marker</label>
        </div>
        <div class="column">
            <input type="text" name="options_google_map_marker" id="options_google_map_marker" value="<?php echo esc_attr( get_option( 'options_google_map_marker' ) ); ?>">
            <p class="description">URL to a custom marker image. Leave blank to use the default marker.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_google_maps_styles">Google Maps Styles</label>
        </div>
        <div class="column">
            <textarea name="options_google_maps_styles" id="options_google_maps_styles" rows="10" style="width: 100%;"><?php echo esc_attr( get_option( 'options_google_maps_styles' ) ); ?></textarea>
            <p class="description">JSON array of Google Maps styles. See <a href="https://snazzymaps.com/" target="_blank">Snazzy Maps</a> for examples.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label>Google Maps default location</label>
            <p class="description">This serves as the map center in the event of a search with no results.</p>
        </div>
        <div class="column">
            <div class="white-box">
                <label for="options_google_maps_default_latitude">Latitude</label>
                <input type="text" name="options_google_maps_default_latitude" id="options_google_maps_default_latitude" value="<?php echo esc_attr( get_option( 'options_google_maps_default_latitude' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_google_maps_default_longitude">Longitude</label>
                <input type="text" name="options_google_maps_default_longitude" id="options_google_maps_default_longitude" value="<?php echo esc_attr( get_option( 'options_google_maps_default_longitude' ) ); ?>">
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label>Google reCAPTCHA v2</label>
        </div>
        <div class="column">
            <div class="white-box">
                <label for="options_google_recaptcha_google_recaptcha_v2_site_key">reCAPTCHA key</label>
                <input type="text" name="options_google_recaptcha_google_recaptcha_v2_site_key" id="options_google_recaptcha_google_recaptcha_v2_site_key" value="<?php echo esc_attr( get_option( 'options_google_recaptcha_google_recaptcha_v2_site_key' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_google_recaptcha_google_recaptcha_v2_secret">reCAPTCHA key</label>
                <input type="text" name="options_google_recaptcha_google_recaptcha_v2_secret" id="options_google_recaptcha_google_recaptcha_v2_secret" value="<?php echo esc_attr( get_option( 'options_google_recaptcha_google_recaptcha_v2_secret' ) ); ?>">
            </div>
        </div>
    </div>
           
    <?php
}

/**
 * Save the Google settings
 */
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_google' );
function rent_fetch_save_settings_google() {
        
    // Text field
    if ( isset( $_POST['options_google_maps_api_key'] ) ) {
        $options_google_maps_api_key = sanitize_text_field( $_POST['options_google_maps_api_key'] );
        update_option( 'options_google_maps_api_key', $options_google_maps_api_key );
    }
    
    // Text field
    if ( isset( $_POST['options_google_geocoding_api_key'] ) ) {
        $options_google_geocoding_api_key = sanitize_text_field( $_POST['options_google_geocoding_api_key'] );
        update_option( 'options_google_geocoding_api_key', $options_google_geocoding_api_key );
    }
    
    // Text field
    if ( isset( $_POST['options_google_map_marker'] ) ) {
        $options_google_map_marker = sanitize_text_field( $_POST['options_google_map_marker'] );
        update_option( 'options_google_map_marker', $options_google_map_marker );
    }
    
    // Textarea field
    if ( isset( $_POST['options_google_maps_styles'] ) ) {
        $options_google_maps_styles = sanitize_text_field( $_POST['options_google_maps_styles'] );
        update_option( 'options_google_maps_styles', $options_google_maps_styles );
    }
    
    // Text field
    if ( isset( $_POST['options_google_maps_default_latitude'] ) ) {
        $options_google_maps_default_latitude = sanitize_text_field( $_POST['options_google_maps_default_latitude'] );
        update_option( 'options_google_maps_default_latitude', $options_google_maps_default_latitude );
    }
    
    // Text field
    if ( isset( $_POST['options_google_maps_default_longitude'] ) ) {
        $options_google_maps_default_longitude = sanitize_text_field( $_POST['options_google_maps_default_longitude'] );
        update_option( 'options_google_maps_default_longitude', $options_google_maps_default_longitude );
    }
    
    // Text field
    if ( isset( $_POST['options_google_recaptcha_google_recaptcha_v2_site_key'] ) ) {
        $options_google_recaptcha_google_recaptcha_v2_site_key = sanitize_text_field( $_POST['options_google_recaptcha_google_recaptcha_v2_site_key'] );
        update_option( 'options_google_recaptcha_google_recaptcha_v2_site_key', $options_google_recaptcha_google_recaptcha_v2_site_key );
    }
    
    // Text field
    if ( isset( $_POST['options_google_recaptcha_google_recaptcha_v2_secret'] ) ) {
        $options_google_recaptcha_google_recaptcha_v2_secret = sanitize_text_field( $_POST['options_google_recaptcha_google_recaptcha_v2_secret'] );
        update_option( 'options_google_recaptcha_google_recaptcha_v2_secret', $options_google_recaptcha_google_recaptcha_v2_secret );
    }
    
}

/**
 * Adds the properties settings section to the Rent Fetch settings page
 */
add_action( 'rent_fetch_do_settings_properties', 'rent_fetch_settings_properties' );
function rent_fetch_settings_properties() {    
    ?>
    <ul class="rent-fetch-options-submenu">
        <li><a href="?page=rent_fetch_options&tab=properties&section=property_search" class="tab<?php if (!isset($_GET['section']) || $_GET['section'] === 'property_search') { echo ' tab-active'; } ?>">Property Search</a></li>
        <li><a href="?page=rent_fetch_options&tab=properties&section=property_archives" class="tab<?php if ( isset( $_GET['section']) && $_GET['section'] === 'property_archives') { echo ' tab-active'; } ?>">Property Archives</a></li>
        <li><a href="?page=rent_fetch_options&tab=properties&section=property_single" class="tab<?php if ( isset( $_GET['section']) && $_GET['section'] === 'property_single') { echo ' tab-active'; } ?>">Property Single Template</a></li>
    </ul>    
    <?php
    if ( !isset($_GET['section']) || $_GET['section'] === 'property_search') {
        do_action( 'rent_fetch_do_settings_properties_property_search' );
    } elseif (isset($_GET['section']) && $_GET['section'] === 'property_archives') {
        do_action( 'rent_fetch_do_settings_properties_property_archives' );
    } elseif (isset($_GET['section']) && $_GET['section'] === 'property_single') {
        do_action( 'rent_fetch_do_settings_properties_property_single' );
    }
}

/**
 * Adds the property search settings subsection to the Rent Fetch settings page
 */
add_action( 'rent_fetch_do_settings_properties_property_search', 'rent_fetch_settings_properties_property_search' );
function rent_fetch_settings_properties_property_search() {
    ?>
    
     <div class="row">
        <div class="column">
            <label for="options_maximum_number_of_properties_to_show">Maximum number of properties to show</label>
        </div>
        <div class="column">
            <p class="description">The most properties we should attempt to show while matching a search. We recommend for performance reasons that this number is not set above ~200 properties.</p>
            <input type="text" name="options_maximum_number_of_properties_to_show" id="options_maximum_number_of_properties_to_show" value="<?php echo esc_attr( get_option( 'options_maximum_number_of_properties_to_show' ) ); ?>">
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_property_availability_display">Property availability display</label>
            
        </div>
        <div class="column">
            <p class="description">Select whether you'd like to show properties that are available or all properties. This setting applies to the properties search and to the "nearby properties" listing on the properties single template.</p>
            <select name="options_property_availability_display" id="options_property_availability_display" value="<?php echo esc_attr( get_option( 'options_property_availability_display' ) ); ?>">
                <option value="available" <?php selected( get_option( 'options_property_availability_display' ), 'available' ); ?>>Availability</option>
                <option value="all" <?php selected( get_option( 'options_property_availability_display' ), 'all' ); ?>>All properties ignoring availability</option>
            </select>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_featured_filters">Featured property filters</label>
            <p class="description">Which components should be shown by default?</p>
        </div>
        <div class="column">
            <?php
            
            // Get saved options
            $options_featured_filters = get_option('options_featured_filters');
            
            // Define default values
            $default_options = array(
                'text_based_search',
                'beds_search',
                'price_search',
            );
            
            // Make it an array just in case it isn't (for example, if it's a new install)
            if (!is_array($options_featured_filters)) {
                $options_featured_filters = $default_options;
            }
            
            ?>
            <ul class="checkboxes">
                <li>
                    <label>
                        <input type="checkbox" name="options_featured_filters[]" value="text_based_search" <?php checked( in_array( 'text_based_search', $options_featured_filters ) ); ?>>
                        Text-based search (this works best with the Relevanssi plugin enhancing your search)
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_featured_filters[]" value="beds_search" <?php checked( in_array( 'beds_search', $options_featured_filters ) ); ?>>
                        Beds search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_featured_filters[]" value="baths_search" <?php checked( in_array( 'baths_search', $options_featured_filters ) ); ?>>
                        Baths search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_featured_filters[]" value="type_search" <?php checked( in_array( 'type_search', $options_featured_filters ) ); ?>>
                        Type search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_featured_filters[]" value="date_search" <?php checked( in_array( 'date_search', $options_featured_filters ) ); ?>>
                        Date search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_featured_filters[]" value="price_search" <?php checked( in_array( 'price_search', $options_featured_filters ) ); ?>>
                        Price search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_featured_filters[]" value="amenities_search" <?php checked( in_array( 'amenities_search', $options_featured_filters ) ); ?>>
                        Amenities search
                    </label>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_dialog_filters">All property filters</label>
            <p class="description">Which components should be shown in the filters lightbox? Typically all filters are shown here, even if they also appear in the featured filters area.</p>
        </div>
        <div class="column">
            <?php
            
            // Get saved options
            $options_dialog_filters = get_option('options_dialog_filters');
            
            // Define default values
            $default_options = array(
                'text_based_search',
                'beds_search',
                'type_search',
                'date_search',
                'price_search',
                'amenities_search',
            );
            
            // Make it an array just in case it isn't (for example, if it's a new install)
            if (!is_array($options_dialog_filters)) {
                $options_dialog_filters = $default_options;
            }
            
            ?>
            <ul class="checkboxes">
                <li>
                    <label>
                        <input type="checkbox" name="options_dialog_filters[]" value="text_based_search" <?php checked( in_array( 'text_based_search', $options_dialog_filters ) ); ?>>
                        Text-based search (this works best with the Relevanssi plugin enhancing your search)
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_dialog_filters[]" value="beds_search" <?php checked( in_array( 'beds_search', $options_dialog_filters ) ); ?>>
                        Beds search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_dialog_filters[]" value="baths_search" <?php checked( in_array( 'baths_search', $options_dialog_filters ) ); ?>>
                        Baths search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_dialog_filters[]" value="type_search" <?php checked( in_array( 'type_search', $options_dialog_filters ) ); ?>>
                        Type search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_dialog_filters[]" value="date_search" <?php checked( in_array( 'date_search', $options_dialog_filters ) ); ?>>
                        Date search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_dialog_filters[]" value="price_search" <?php checked( in_array( 'price_search', $options_dialog_filters ) ); ?>>
                        Price search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_dialog_filters[]" value="amenities_search" <?php checked( in_array( 'amenities_search', $options_dialog_filters ) ); ?>>
                        Amenities search
                    </label>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_maximum_bedrooms_to_search">Maximum bedrooms to search</label>
        </div>
        <div class="column">
            <input type="text" name="options_maximum_bedrooms_to_search" id="options_maximum_bedrooms_to_search" value="<?php echo esc_attr( get_option( 'options_maximum_bedrooms_to_search' ) ); ?>">
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_price_filter_minimum">Price filter</label>
        </div>
        <div class="column">
            <div class="white-box">
                <label for="options_price_filter_minimum">Price filter minimum</label>
                <input type="text" name="options_price_filter_minimum" id="options_price_filter_minimum" value="<?php echo esc_attr( get_option( 'options_price_filter_minimum' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_price_filter_maximum">Price filter maximum</label>
                <input type="text" name="options_price_filter_maximum" id="options_price_filter_maximum" value="<?php echo esc_attr( get_option( 'options_price_filter_maximum' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_price_filter_step">Price filter step</label>
                <input type="text" name="options_price_filter_step" id="options_price_filter_step" value="<?php echo esc_attr( get_option( 'options_price_filter_step' ) ); ?>">
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_number_of_amenities_to_show">Number of amenities to show</label>
        </div>
        <div class="column">
            <input type="text" name="options_number_of_amenities_to_show" id="options_number_of_amenities_to_show" value="<?php echo esc_attr( get_option( 'options_number_of_amenities_to_show' ), 10 ); ?>">
        </div>
    </div>        
    <?php
}

/**
 * Save the property search settings
 */
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_property_search' );
function rent_fetch_save_settings_property_search() {
    
    // Number field
    if ( isset( $_POST['options_maximum_number_of_properties_to_show'] ) ) {
        $max_properties = intval( $_POST['options_maximum_number_of_properties_to_show'] );
        update_option( 'options_maximum_number_of_properties_to_show', $max_properties );
    }
    
    // Select field
    if ( isset( $_POST['options_property_availability_display'] ) ) {
        $property_display = sanitize_text_field( $_POST['options_property_availability_display'] );
        update_option( 'options_property_availability_display', $property_display );
    }
            
    // Checkboxes field
    if (isset($_POST['options_dialog_filters'])) {
        $options_dialog_filters = array_map('sanitize_text_field', $_POST['options_dialog_filters']);
        update_option('options_dialog_filters', $options_dialog_filters);
    }
    
    // Checkboxes field
    if (isset($_POST['options_featured_filters'])) {
        $options_featured_filters = array_map('sanitize_text_field', $_POST['options_featured_filters']);
        update_option('options_featured_filters', $options_featured_filters);
    }
    
    // Number field
    if ( isset( $_POST['options_maximum_bedrooms_to_search'] ) ) {
        $max_bedrooms = intval( $_POST['options_maximum_bedrooms_to_search'] );
        update_option( 'options_maximum_bedrooms_to_search', $max_bedrooms );
    }
    
    // Number field
    if ( isset( $_POST['options_price_filter_minimum'] ) ) {
        $price_filter_minimum = intval( $_POST['options_price_filter_minimum'] );
        update_option( 'options_price_filter_minimum', $price_filter_minimum );
    } else {
        $price_filter_minimum = null;
        update_option( 'options_price_filter_minimum', $price_filter_minimum );
    }
    
    // Number field
    if ( isset( $_POST['options_price_filter_maximum'] ) ) {
        $price_filter_maximum = intval( $_POST['options_price_filter_maximum'] );
        update_option( 'options_price_filter_maximum', $price_filter_maximum );
    }
    
    // Number field
    if ( isset( $_POST['options_price_filter_step'] ) ) {
        $price_filter_step = intval( $_POST['options_price_filter_step'] );
        update_option( 'options_price_filter_step', $price_filter_step );
    }
    
    // Number field
    if ( isset( $_POST['options_number_of_amenities_to_show'] ) ) {
        $number_of_amenities_to_show = intval( $_POST['options_number_of_amenities_to_show'] );
        update_option( 'options_number_of_amenities_to_show', $number_of_amenities_to_show );
    }
    
}

/**
 * Adds the properties archives settings subsection to the Rent Fetch settings page
 */
add_action( 'rent_fetch_do_settings_properties_property_archives', 'rent_fetch_settings_properties_property_archives' );
function rent_fetch_settings_properties_property_archives() {
    ?>
    
    <div class="row">
        <div class="column">
            <label for="options_property_footer_grid_number_properties">Property Footer Grid Maximum</label>
            <p class="description">By default, this shows on the individual properties template. If there are less than two properties that would show, this area simply does not appear.</p>
        </div>
        <div class="column">
            <p class="description">The maximum number of properties to show. By default, this will show all (-1) properties</p>
            <input type="number" name="options_property_footer_grid_number_properties" id="options_property_footer_grid_number_properties" value="<?php echo esc_attr( get_option( 'options_property_footer_grid_number_properties' ) ); ?>">
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_property_pricing_display">Property Pricing Display</label>
            <p class="description">How should pricing be shown on property archives?</p>
        </div>
        <div class="column">
            <ul class="radio">
                <li>
                    <label>
                        <input type="radio" name="options_property_pricing_display" id="options_property_pricing_display" value="range" <?php checked( get_option( 'options_property_pricing_display' ), 'range' ); ?>>
                        Range (e.g. "$1999 to $2999")
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="options_property_pricing_display" id="options_property_pricing_display" value="minimum" <?php checked( get_option( 'options_property_pricing_display' ), 'minimum' ); ?>>
                        Minimum (e.g. "from $1999")
                    </label>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_property_orderby">Order Properties By</label>
            <p class="description">In archives, what order would you like properties to be shown in by default?</p>
        </div>
        <div class="column">
            <ul class="radio">
                <li>
                    <label>
                        <input type="radio" name="options_property_orderby" id="options_property_orderby" value="menu_order" <?php checked( get_option( 'options_property_orderby' ), 'menu_order' ); ?>>
                        Menu order (use this if utilizing drag/drop reordering)
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="options_property_orderby" id="options_property_orderby" value="date" <?php checked( get_option( 'options_property_orderby' ), 'date' ); ?>>
                        Publish date
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="options_property_orderby" id="options_property_orderby" value="modified" <?php checked( get_option( 'options_property_orderby' ), 'modified' ); ?>>
                        Last modified date
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="options_property_orderby" id="options_property_orderby" value="ID" <?php checked( get_option( 'options_property_orderby' ), 'ID' ); ?>>
                        Post ID number
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="options_property_orderby" id="options_property_orderby" value="title" <?php checked( get_option( 'options_property_orderby' ), 'title' ); ?>>
                        Alphabetical, based on property name
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="options_property_orderby" id="options_property_orderby" value="name" <?php checked( get_option( 'options_property_orderby' ), 'name' ); ?>>
                        Alphabetical, based on property slug
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="options_property_orderby" id="options_property_orderby" value="rand" <?php checked( get_option( 'options_property_orderby' ), 'rand' ); ?>>
                        Randomize
                    </label>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="row">
        <div class="column">
            <label for="options_property_order">Property order direction</label>
        </div>
        <div class="column">
            <ul class="radio">
                <li>
                    <label>
                        <input type="radio" name="options_property_order" id="options_property_order" value="ASC" <?php checked( get_option( 'options_property_order' ), 'ASC' ); ?>>
                        Ascending
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="options_property_order" id="options_property_order" value="DESC" <?php checked( get_option( 'options_property_order' ), 'DESC' ); ?>>
                        Descending
                    </label>
                </li>
            </ul>
        </div>
    </div>
    
    <?php
}

/**
 * Save the property archive settings
 */
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_property_archives' );
function rent_fetch_save_settings_property_archives() {
    
    // Number field
    if ( isset( $_POST['options_property_footer_grid_number_properties'] ) ) {
        $max_properties = intval( $_POST['options_property_footer_grid_number_properties'] );
        update_option( 'options_property_footer_grid_number_properties', $max_properties );
    }
    
    // Select field
    if ( isset( $_POST['options_property_pricing_display'] ) ) {
        $property_display = sanitize_text_field( $_POST['options_property_pricing_display'] );
        update_option( 'options_property_pricing_display', $property_display );
    }
    
    // Select field
    if ( isset( $_POST['options_property_orderby'] ) ) {
        $property_display = sanitize_text_field( $_POST['options_property_orderby'] );
        update_option( 'options_property_orderby', $property_display );
    }
    
    // Select field
    if ( isset( $_POST['options_property_order'] ) ) {
        $property_display = sanitize_text_field( $_POST['options_property_order'] );
        update_option( 'options_property_order', $property_display );
    }
}

/**
 * Adds the properties single settings subsection to the Rent Fetch settings page
 */
add_action( 'rent_fetch_do_settings_properties_property_single', 'rent_fetch_settings_properties_property_single' );
function rent_fetch_settings_properties_property_single() {
    ?>
    
    <div class="row">
        <div class="column">
            <label for="options_single_property_components">Single property components</label>
            <p class="description">These settings control which default components of the page display. Please note that theme developers can also customize this display in several other ways. Each individual section can be replaced by removing the corresponding action, or you can simply add a single-properties.php file to the root of your theme.</p>
            <p class="description">Please note that each individual section will only display if there's enough information to meaningfully display it. A property with no images set will not output a blank "images" section, for example.</p>
        </div>
        <div class="column">
            <?php
            
            // Get saved options
            $options_single_property_components = get_option('options_single_property_components');
            
            // Define default values
            $default_options = array(
                'enable_property_title',
                'enable_property_images',
                'enable_basic_info_display',
                'enable_property_description',
                'enable_floorplans_display',
                'enable_amenities_display',
                'enable_lease_details_display',
                'enable_property_map',
                'enable_nearby_properties',
            );
            
            // Make it an array just in case it isn't (for example, if it's a new install)
            if (!is_array($options_single_property_components)) {
                $options_single_property_components = $default_options;
            }
                        
            ?>
            <ul class="checkboxes">
                <li>
                    <label>
                        <input type="checkbox" name="options_single_property_components[]" value="property_images" <?php checked( in_array( 'property_images', $options_single_property_components ) ); ?>>
                        Enable property images
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_single_property_components[]" value="section_navigation" <?php checked( in_array( 'section_navigation', $options_single_property_components ) ); ?>>
                        Enable section navigation
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_single_property_components[]" value="property_details" <?php checked( in_array( 'property_details', $options_single_property_components ) ); ?>>
                        Enable property details 
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_single_property_components[]" value="floorplans_display" <?php checked( in_array( 'floorplans_display', $options_single_property_components ) ); ?>>
                        Enable floorplan display
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_single_property_components[]" value="amenities_display" <?php checked( in_array( 'amenities_display', $options_single_property_components ) ); ?>>
                        Enable amenities display
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_single_property_components[]" value="property_map" <?php checked( in_array( 'property_map', $options_single_property_components ) ); ?>>
                        Enable property map
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_single_property_components[]" value="nearby_properties" <?php checked( in_array( 'nearby_properties', $options_single_property_components ) ); ?>>
                        Enable nearby properties
                    </label>
                </li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Save the property single settings
 */
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_property_single' );
function rent_fetch_save_settings_property_single() {
    
    // Checkboxes field
    if ( isset ( $_POST['options_single_property_components'] ) ) {
        $enabled_integrations = array_map('sanitize_text_field', $_POST['options_single_property_components']);
        update_option('options_single_property_components', $enabled_integrations);
    }
}

/**
 * Adds the floorplans settings section to the Rent Fetch settings page
 */
add_action( 'rent_fetch_do_settings_floorplans', 'rent_fetch_settings_floorplans' );
function rent_fetch_settings_floorplans() {
    ?>
    <ul class="rent-fetch-options-submenu">
        <li><a href="?page=rent_fetch_options&tab=floorplans&section=floorplan_search" class="tab<?php if (!isset($_GET['section']) || $_GET['section'] === 'floorplan_search') { echo ' tab-active'; } ?>">Floorplan Search</a></li>
        <li><a href="?page=rent_fetch_options&tab=floorplans&section=floorplan_buttons" class="tab<?php if ( isset( $_GET['section']) && $_GET['section'] === 'floorplan_buttons') { echo ' tab-active'; } ?>">Floorplan Buttons</a></li>
    </ul>    
    <?php
    if ( !isset($_GET['section']) || $_GET['section'] === 'floorplan_search') {
        do_action( 'rent_fetch_do_settings_floorplans_floorplan_search' );
    } elseif (isset($_GET['section']) && $_GET['section'] === 'floorplan_buttons') {
        do_action( 'rent_fetch_do_settings_floorplans_floorplan_buttons' );
    }
}

add_action( 'rent_fetch_do_settings_floorplans_floorplan_search', 'rent_fetch_settings_floorplans_floorplan_search' );
function rent_fetch_settings_floorplans_floorplan_search() {
    ?>
    <div class="row">
        <div class="column">
            <label for="options_floorplan_filters">Floorplan search filters</label>
            <p class="description">Which components should be shown floorplans search?</p>
        </div>
        <div class="column">
            <?php
            
            // Get saved options
            $options_floorplan_filters = get_option( 'options_floorplan_filters' );
            
            // Define default values
            $default_options = array(
                'beds_search',
                'baths_search',
                'price_search',
                'date_search',
                'squarefoot_search',
                'sort',
            );
            
            // Make it an array just in case it isn't (for example, if it's a new install)
            if (!is_array($options_floorplan_filters)) {
                $options_floorplan_filters = $default_options;
            }
            
            ?>
            <ul class="checkboxes">
                <li>
                    <label>
                        <input type="checkbox" name="options_floorplan_filters[]" value="beds_search" <?php checked( in_array( 'beds_search', $options_floorplan_filters ) ); ?>>
                        Beds search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_floorplan_filters[]" value="baths_search" <?php checked( in_array( 'baths_search', $options_floorplan_filters ) ); ?>>
                        Baths search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_floorplan_filters[]" value="price_search" <?php checked( in_array( 'price_search', $options_floorplan_filters ) ); ?>>
                        Price search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_floorplan_filters[]" value="date_search" <?php checked( in_array( 'date_search', $options_floorplan_filters ) ); ?>>
                        Date search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_floorplan_filters[]" value="squarefoot_search" <?php checked( in_array( 'squarefoot_search', $options_floorplan_filters ) ); ?>>
                        Square footage search
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="options_floorplan_filters[]" value="sort" <?php checked( in_array( 'sort', $options_floorplan_filters ) ); ?>>
                        Sorting
                    </label>
                </li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Save the floorplan 
 */
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_floorplan_search' );
function rent_fetch_save_settings_floorplan_search() {
        
        // Checkboxes field
        if ( isset ( $_POST['options_floorplan_filters'] ) ) {
            $options_floorplan_filters = array_map('sanitize_text_field', $_POST['options_floorplan_filters']);
            update_option('options_floorplan_filters', $options_floorplan_filters);
        }
    
}

/**
 * Output floorplan button settings
 */
add_action( 'rent_fetch_do_settings_floorplans_floorplan_buttons', 'rent_fetch_settings_floorplans_floorplan_buttons' );
function rent_fetch_settings_floorplans_floorplan_buttons() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function( $ ) {
	
            // $( '.contact .white-box:not(.always-visible)' ).hide();
            
            // on change of input[name="options_enabled_integrations[]"], show/hide the integration options
            $( 'input[name="options_contact_button_enabled"]' ).on( 'change', function() {
                
                console.log( this );
                                
                if( this.checked ) {
                    $( '.contact .white-box:not(.always-visible)' ).show();
                } else {
                    $( '.contact .white-box:not(.always-visible)' ).hide();
                }
                                                
            }).trigger( 'change' );
            
        });
    </script>
    <div class="row floorplan-archive-buttons contact">
        <div class="column">
            <label>Contact button</label>
            <p class="description">A button linking either to a static page on the site or to a single third-party location. This will be the same link for every floorplan (it is not dynamic).</p>
        </div>
        <div class="column">
            <div class="white-box always-visible">
                <label for="options_contact_button_enabled">
                    <input type="checkbox" name="options_contact_button_enabled" id="options_contact_button_enabled" <?php checked( get_option( 'options_contact_button_enabled' ), true ); ?>>
                    Enable the contact button
                </label>
            </div>
            <div class="white-box">
                <label for="options_contact_button_button_label">Button label</label>
                <input type="text" name="options_contact_button_button_label" id="options_contact_button_button_label" value="<?php echo esc_attr( get_option( 'options_contact_button_button_label' ) ); ?>">
                <p class="description">Required for syncing any data down from an API.</p>
            </div>
            <div class="white-box">
                <label for="options_contact_button_link">Link</label>
                <input type="text" name="options_contact_button_link" id="options_contact_button_link" value="<?php echo esc_attr( get_option( 'options_contact_button_link' ) ); ?>">
                <label style="margin-top: 10px;" for="options_contact_button_link_target">
                    <input type="checkbox" name="options_contact_button_link_target" id="options_contact_button_link_target" <?php checked( get_option( 'options_contact_button_link_target' ), true ); ?>>
                    Open in new tab?
                </label>
            </div>
        </div>
    </div>
    
    <script type="text/javascript">
        jQuery(document).ready(function( $ ) {
	
            // $( '.contact .white-box:not(.always-visible)' ).hide();
            
            // on change of input[name="options_enabled_integrations[]"], show/hide the integration options
            $( 'input[name="options_availability_button_enabled"]' ).on( 'change', function() {
                
                console.log( this );
                                
                if( this.checked ) {
                    $( '.availability .white-box:not(.always-visible)' ).show();
                } else {
                    $( '.availability .white-box:not(.always-visible)' ).hide();
                }
                                                
            }).trigger( 'change' );
            
        });
    </script>
    <div class="row floorplan-archive-buttons availability">
        <div class="column">
            <label>Availability button</label>
            <p class="description">A button which can pull in the availability link for each individual floorplan, with a single fallback link to use if no availability url is available.</p>
        </div>
        <div class="column">
            <div class="white-box always-visible">
                <label for="options_availability_button_enabled">
                    <input type="checkbox" name="options_availability_button_enabled" id="options_availability_button_enabled" <?php checked( get_option( 'options_availability_button_enabled' ), true ); ?>>
                    Enable the availability button
                </label>
            </div>
            <div class="white-box">
                <label for="options_availability_button_button_label">Button label</label>
                <input type="text" name="options_availability_button_button_label" id="options_availability_button_button_label" value="<?php echo esc_attr( get_option( 'options_availability_button_button_label' ) ); ?>">
            </div>
            <div class="white-box">
                <label for="options_availability_button_button_behavior">Button behavior</label>
                <p class="description">How should this button behave when this floorplan does not currently have units available?</p>
                <ul class="radio">
                    <li>
                        <label>
                            <input type="radio" name="options_availability_button_button_behavior" id="options_availability_button_button_behavior" value="hide" <?php checked( get_option( 'options_availability_button_button_behavior' ), 'hide' ); ?>>
                            Hide this button entirely
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="options_availability_button_button_behavior" id="options_availability_button_button_behavior" value="fallback" <?php checked( get_option( 'options_availability_button_button_behavior' ), 'fallback' ); ?>>
                            Fall back to a static link
                        </label>
                    </li>
                </ul>
            </div>
            <div class="white-box">
                <label for="options_availability_button_link">Fallback link</label>
                <input type="text" name="options_availability_button_link" id="options_availability_button_link" value="<?php echo esc_attr( get_option( 'options_availability_button_link' ) ); ?>">
            </div>
        </div>
    </div>
    
    <script type="text/javascript">
        jQuery(document).ready(function( $ ) {
	
            // $( '.contact .white-box:not(.always-visible)' ).hide();
            
            // on change of input[name="options_enabled_integrations[]"], show/hide the integration options
            $( 'input[name="options_tour_button_enabled"]' ).on( 'change', function() {
                
                console.log( this );
                                
                if( this.checked ) {
                    $( '.tour .white-box:not(.always-visible)' ).show();
                } else {
                    $( '.tour .white-box:not(.always-visible)' ).hide();
                }
                                                
            }).trigger( 'change' );
            
        });
    </script>
    <div class="row floorplan-archive-buttons tour">
        <div class="column">
            <label>Tour button</label>
            <p class="description">A button to show a lightbox with a video, or an external link (this link is always per-floorplan).</p>
        </div>
        <div class="column">
            <div class="white-box always-visible">
                <label for="options_tour_button_enabled">
                    <input type="checkbox" name="options_tour_button_enabled" id="options_tour_button_enabled" <?php checked( get_option( 'options_tour_button_enabled' ), true ); ?>>
                    Enable the tour button
                </label>
            </div>
            <div class="white-box">
                <label for="options_tour_button_button_label">Button label</label>
                <input type="text" name="options_tour_button_button_label" id="options_tour_button_button_label" value="<?php echo esc_attr( get_option( 'options_tour_button_button_label' ) ); ?>">
            </div>
        </div>
    </div>
    
    <script type="text/javascript">
        jQuery(document).ready(function( $ ) {
	
            // $( '.contact .white-box:not(.always-visible)' ).hide();
            
            // on change of input[name="options_enabled_integrations[]"], show/hide the integration options
            $( 'input[name="options_single_button_enabled"]' ).on( 'change', function() {
                
                console.log( this );
                                
                if( this.checked ) {
                    $( '.floorplan-archive-button .white-box:not(.always-visible)' ).show();
                } else {
                    $( '.floorplan-archive-button .white-box:not(.always-visible)' ).hide();
                }
                                                
            }).trigger( 'change' );
            
        });
    </script>
    <div class="row floorplan-archive-button single">
        <div class="column">
            <label>Link to floorplan single template</label>
            <p class="description">A button which simply links to the individual floorplan template.</p>
        </div>
        <div class="column">
            <div class="white-box always-visible">
                <label for="options_single_button_enabled">
                    <input type="checkbox" name="options_single_button_enabled" id="options_single_button_enabled" <?php checked( get_option( 'options_single_button_enabled' ), true ); ?>>
                    Enable the single floorplan template button
                </label>
            </div>
            <div class="white-box">
                <label for="options_single_button_button_label">Button label</label>
                <input type="text" name="options_single_button_button_label" id="options_single_button_button_label" value="<?php echo esc_attr( get_option( 'options_single_button_button_label' ) ); ?>">
            </div>
        </div>
    </div>
    <?php
}

/**
 * Save the floorplan button settings
 */
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_floorplan_buttons' );
function rent_fetch_save_settings_floorplan_buttons() {
    
    // Checkbox field - Enable the contact button
    $options_contact_button_enabled = isset( $_POST['options_contact_button_enabled'] ) ? '1' : '0';
    update_option( 'options_contact_button_enabled', $options_contact_button_enabled );

    // Text field - Button label
    if ( isset( $_POST['options_contact_button_button_label'] ) ) {
        $options_contact_button_button_label = sanitize_text_field( $_POST['options_contact_button_button_label'] );
        update_option( 'options_contact_button_button_label', $options_contact_button_button_label );
    }

    // Text field - Link
    if ( isset( $_POST['options_contact_button_link'] ) ) {
        $options_contact_button_link = sanitize_text_field( $_POST['options_contact_button_link'] );
        update_option( 'options_contact_button_link', $options_contact_button_link );
    }

    // Checkbox field - Open in new tab?
    $options_contact_button_link_target = isset( $_POST['options_contact_button_link_target'] ) ? '1' : '0';
    update_option( 'options_contact_button_link_target', $options_contact_button_link_target );

    // Checkbox field - Enable the availability button
    $options_availability_button_enabled = isset( $_POST['options_availability_button_enabled'] ) ? '1' : '0';
    update_option( 'options_availability_button_enabled', $options_availability_button_enabled );

    // Text field - Button label
    if ( isset( $_POST['options_availability_button_button_label'] ) ) {
        $options_availability_button_button_label = sanitize_text_field( $_POST['options_availability_button_button_label'] );
        update_option( 'options_availability_button_button_label', $options_availability_button_button_label );
    }

    // Radio field - Button behavior
    if ( isset( $_POST['options_availability_button_button_behavior'] ) ) {
        $options_availability_button_button_behavior = sanitize_text_field( $_POST['options_availability_button_button_behavior'] );
        update_option( 'options_availability_button_button_behavior', $options_availability_button_button_behavior );
    }

    // Text field - Fallback link
    if ( isset( $_POST['options_availability_button_link'] ) ) {
        $options_availability_button_link = sanitize_text_field( $_POST['options_availability_button_link'] );
        update_option( 'options_availability_button_link', $options_availability_button_link );
    }

    // Checkbox field - Enable the tour button
    $options_tour_button_enabled = isset( $_POST['options_tour_button_enabled'] ) ? '1' : '0';
    update_option( 'options_tour_button_enabled', $options_tour_button_enabled );

    // Text field - Tour button label
    if ( isset( $_POST['options_tour_button_button_label'] ) ) {
        $options_tour_button_button_label = sanitize_text_field( $_POST['options_tour_button_button_label'] );
        update_option( 'options_tour_button_button_label', $options_tour_button_button_label );
    }

    // Checkbox field - Enable the single floorplan template button
    $options_single_button_enabled = isset( $_POST['options_single_button_enabled'] ) ? '1' : '0';
    update_option( 'options_single_button_enabled', $options_single_button_enabled );

    // Text field - Single floorplan template button label
    if ( isset( $_POST['options_single_button_button_label'] ) ) {
        $options_single_button_button_label = sanitize_text_field( $_POST['options_single_button_button_label'] );
        update_option( 'options_single_button_button_label', $options_single_button_button_label );
    }

    
}


/**
 * Adds the labels settings section to the Rent Fetch settings page
 */
add_action( 'rent_fetch_do_settings_labels', 'rent_fetch_settings_labels' );
function rent_fetch_settings_labels() {
    ?>
        
    <div class="row">
        <div class="column">
            <label for="options_bedroom_numbers_0_bedroom">0 Bedroom</label>
        </div>
        <div class="column">
            <input type="text" name="options_bedroom_numbers_0_bedroom" id="options_bedroom_numbers_0_bedroom" value="<?php echo esc_attr( get_option( 'options_bedroom_numbers_0_bedroom', 'Studio' ) ); ?>">
        </div>
    </div>
    <div class="row">
        <div class="column">
            <label for="options_bedroom_numbers_1_bedroom">1 Bedroom</label>
        </div>
        <div class="column">
            <input type="text" name="options_bedroom_numbers_1_bedroom" id="options_bedroom_numbers_1_bedroom" value="<?php echo esc_attr( get_option( 'options_bedroom_numbers_1_bedroom', 'One Bedroom' ) ); ?>">
        </div>
    </div>
    <div class="row">
        <div class="column">
            <label for="options_bedroom_numbers_2_bedroom">2 Bedroom</label>
        </div>
        <div class="column">
            <input type="text" name="options_bedroom_numbers_2_bedroom" id="options_bedroom_numbers_2_bedroom" value="<?php echo esc_attr( get_option( 'options_bedroom_numbers_2_bedroom', 'Two Bedroom' ) ); ?>">
        </div>
    </div>
    <div class="row">
        <div class="column">
            <label for="options_bedroom_numbers_3_bedroom">3 Bedroom</label>
        </div>
        <div class="column">
            <input type="text" name="options_bedroom_numbers_3_bedroom" id="options_bedroom_numbers_3_bedroom" value="<?php echo esc_attr( get_option( 'options_bedroom_numbers_3_bedroom', 'Three Bedroom' ) ); ?>">            
        </div>
    </div>
    <div class="row">
        <div class="column">
            <label for="options_bedroom_numbers_4_bedroom">4 Bedroom</label>
        </div>
        <div class="column">
            <input type="text" name="options_bedroom_numbers_4_bedroom" id="options_bedroom_numbers_4_bedroom" value="<?php echo esc_attr( get_option( 'options_bedroom_numbers_4_bedroom', 'Four Bedroom' ) ); ?>">
        </div>
    </div>
    <div class="row">
        <div class="column">
            <label for="options_bedroom_numbers_5_bedroom">5 Bedroom</label>
        </div>
        <div class="column">
            <input type="text" name="options_bedroom_numbers_5_bedroom" id="options_bedroom_numbers_5_bedroom" value="<?php echo esc_attr( get_option( 'options_bedroom_numbers_5_bedroom', 'Five Bedroom' ) ); ?>">
        </div>
    </div>
    <?php
}

/**
 * Save the general settings
 */
add_action( 'rent_fetch_save_settings', 'rent_fetch_save_settings_labels' );
function rent_fetch_save_settings_labels() {
    
    // Text field
    if ( isset( $_POST['options_bedroom_numbers_0_bedroom']) ) {
        $options_bedroom_numbers_0_bedroom = sanitize_text_field( $_POST['options_bedroom_numbers_0_bedroom'] );
        update_option( 'options_bedroom_numbers_0_bedroom', $options_bedroom_numbers_0_bedroom );
    }
    
    // Text field
    if ( isset( $_POST['options_bedroom_numbers_1_bedroom']) ) {
        $options_bedroom_numbers_1_bedroom = sanitize_text_field( $_POST['options_bedroom_numbers_1_bedroom'] );
        update_option( 'options_bedroom_numbers_1_bedroom', $options_bedroom_numbers_1_bedroom );
    }
    
    // Text field
    if ( isset( $_POST['options_bedroom_numbers_2_bedroom']) ) {
        $options_bedroom_numbers_2_bedroom = sanitize_text_field( $_POST['options_bedroom_numbers_2_bedroom'] );
        update_option( 'options_bedroom_numbers_2_bedroom', $options_bedroom_numbers_2_bedroom );
    }
    
    // Text field
    if ( isset( $_POST['options_bedroom_numbers_3_bedroom']) ) {
        $options_bedroom_numbers_3_bedroom = sanitize_text_field( $_POST['options_bedroom_numbers_3_bedroom'] );
        update_option( 'options_bedroom_numbers_3_bedroom', $options_bedroom_numbers_3_bedroom );
    }
    
    // Text field
    if ( isset( $_POST['options_bedroom_numbers_4_bedroom']) ) {
        $options_bedroom_numbers_4_bedroom = sanitize_text_field( $_POST['options_bedroom_numbers_4_bedroom'] );
        update_option( 'options_bedroom_numbers_4_bedroom', $options_bedroom_numbers_4_bedroom );
    }
    
    // Text field
    if ( isset( $_POST['options_bedroom_numbers_5_bedroom']) ) {
        $options_bedroom_numbers_5_bedroom = sanitize_text_field( $_POST['options_bedroom_numbers_5_bedroom'] );
        update_option( 'options_bedroom_numbers_5_bedroom', $options_bedroom_numbers_5_bedroom );
    }
}
