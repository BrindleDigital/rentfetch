<?php


function rentfetch_check_actionscheduler_tables() {
	
	global $wpdb;

	$table_list = array(
		'actionscheduler_actions',
		'actionscheduler_logs',
		'actionscheduler_groups',
		'actionscheduler_claims',
	);
		
	foreach( $table_list as $table ) {
		
		$found_tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}{$table}'" );
		
		// if the tables are missing, output the notice and return
		if ( !$found_tables ) {
			
			add_action( 'admin_notices', 'rentfetch_database_tables_missing_notice' );
			
			return;
		}
	}
}
add_action( 'wp_loaded', 'rentfetch_check_actionscheduler_tables' );

function rentfetch_database_tables_missing_notice() {
	echo '<div class="notice notice-error is-dismissible">';
	echo '<p>' . __( '<strong>Rent Fetch:</strong> The Action Scheduler tables appear to be missing. Please <a href="/wp-admin/tools.php?page=action-scheduler">vist the Action Scheduler admin page</a> to regenerate those.', 'rentfetch' ) . '</p>';
	echo '</div>';
}