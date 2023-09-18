<?php

/////////////
// LOGGING //
/////////////

// used for debugging
if ( !function_exists( 'console_log' ) ) {
	function console_log( $data ){
		echo '<script>';
		echo 'console.log('. json_encode( $data ) .')';
		echo '</script>';
	}
}

//* Add debug logging
function rentfetch_log($message) { 
    
    //* bail and delete the log file if logging is not enabled
    $enable_logging = get_option( 'options_enable_rentfetch_logging' );
    if ( boolval($enable_logging) !== true ) {
        
        if ( file_exists( WP_CONTENT_DIR . "/apartment-sync-debug.log" ) )
            $delete = unlink( WP_CONTENT_DIR . "/apartment-sync-debug.log" );
            
        if ( file_exists( WP_CONTENT_DIR . "/rentfetch-debug.log" ) )
            $delete = unlink( WP_CONTENT_DIR . "/rentfetch-debug.log" );
            
        return;
    }
    
    if( is_array( $message ) )
        $message = json_encode($message); 
        
    $file = fopen( WP_CONTENT_DIR . "/rentfetch-debug.log", "a" );
    fwrite($file, date('Y-m-d h:i:s') . " " . $message . "\n"); 
    fclose($file); 
    
    $file = fopen( WP_CONTENT_DIR . "/rentfetch-debug-verbose.log", "a" );
    fwrite($file, date('Y-m-d h:i:s') . " " . $message . "\n"); 
    fclose($file); 
    
}

//* Add debug verbose logging
function rentfetch_verbose_log($message) { 
    
    //* bail and delete the log file if logging is not enabled
    $enable_logging = get_option( 'options_enable_rentfetch_logging' );
    if ( boolval($enable_logging) !== true ) {
        
        if ( file_exists( WP_CONTENT_DIR . "/apartment-sync-debug-verbose.log" ) )
            $delete = unlink( WP_CONTENT_DIR . "/apartment-sync-debug-verbose.log" );
            
        if ( file_exists( WP_CONTENT_DIR . "/rentfetch-debug-verbose.log" ) )
            $delete = unlink( WP_CONTENT_DIR . "/rentfetch-debug-verbose.log" );
            
        return;
    }
    
    if( is_array( $message ) )
        $message = json_encode($message); 
                
    $file = fopen( WP_CONTENT_DIR . "/rentfetch-debug-verbose.log", "a" );
    fwrite($file, date('Y-m-d h:i:s') . " " . $message . "\n"); 
    fclose($file); 
    
}