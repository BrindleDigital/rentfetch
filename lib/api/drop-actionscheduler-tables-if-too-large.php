<?php


//! Perhaps add this in a later release. This function drops the Action Scheduler tables if they are over 500MB; however, administrative action is required to fix this.
//! maybe consider programatically deactivating then reactivating Rent Fetch to regen the tables instead?
// add_action( 'init', 'rentfetch_drop_large_actionscheduler_tables' );
function rentfetch_drop_large_actionscheduler_tables() {
    
    global $wpdb;
    
    $table_list = array(
        'actionscheduler_actions',
        'actionscheduler_logs',
        'actionscheduler_groups',
        'actionscheduler_claims',
    );

    $found_tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}actionscheduler%'" );

    foreach ($table_list as $table_name) {
        if (in_array($wpdb->prefix . $table_name, $found_tables)) {
            // Get table size in bytes
            $query = "SELECT data_length + index_length AS size FROM information_schema.TABLES WHERE table_schema = '{$wpdb->dbname}' AND table_name = '{$wpdb->prefix}{$table_name}'";
            $result = $wpdb->get_row($query);
            
            // Check if the query was successful
            if ($result !== null) {
                $size_in_bytes = (int) $result->size;
                
                // Check if the table size exceeds 500MB (in bytes)
                if ($size_in_bytes > 500 * 1024 * 1024) {
                    // Drop the table
                    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table_name}");
                    
                    // Output a success message
                    echo "Table {$wpdb->prefix}{$table_name} dropped successfully.<br>";
                }
            }
        }
    }
}