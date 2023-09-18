<?php

//* Add our menus for rent fetch
add_action('acf/init', 'apartment_sync_add_settings');
function apartment_sync_add_settings() {

    // Check function exists.
    if( function_exists( 'acf_add_options_page' ) ) {
        
        // get the logo
        $menu_icon = file_get_contents( RENTFETCH_DIR . 'images/rentfetch-dashboard-icon.svg' );

        // Add parent.
        $parent = acf_add_options_page(array(
            'page_title'  => __( 'Rent Fetch' ),
            'menu_title' => __( 'Rent Fetch' ),
            'menu_slug' => 'rent-fetch',
            'position' => 58.99,
            'capability' => 'manage_options',
            'icon_url' => 'data:image/svg+xml;base64,' . base64_encode( $menu_icon ),
        ));
        
        // Add sub page (this is actually our main settings page)
        $child = acf_add_options_sub_page(array(
            'page_title'  => __('Settings'),
            'menu_title'  => __('Settings'),
            'menu_slug' => 'rent-fetch-settings',
            'parent_slug' => $parent['menu_slug'],
        ));
        
        // Add sub page (we're just adding a page here, we'll actually link this somewhere else)
        $child = acf_add_options_sub_page(array(
            'page_title'  => __('Documentation'),
            'menu_title'  => __('Documentation'),
            'menu_slug' => 'rent-fetch-documentation',
            'parent_slug' => $parent['menu_slug'],
        ));

    }
}

//* Force the documentation link to go offsite
add_action( 'admin_footer', 'rentfetch_admin_menu_open_new_tab' );    
function rentfetch_admin_menu_open_new_tab() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('a[href="admin.php?page=rent-fetch-documentation"]').each(function () {
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