<?php
/** Standalone cache invalidation and missing-property isolation regression. */
define( 'ABSPATH', __DIR__ );
define( 'DAY_IN_SECONDS', 86400 );
define( 'HOUR_IN_SECONDS', 3600 );
$options = array( 'rentfetch_search_query_cache_registry' => array( 'rentfetch_floorplans_array_sql_object_only' => array() ) );
$transients = array( 'rentfetch_propertysearch_markup_db' => 'old markup', 'rentfetch_floorplans_array_sql_object_only' => 'old results', 'rf_analytics_searches' => 'keep analytics', 'unrelated' => 'keep unrelated cache' );
function add_action( ...$args ) {}
function get_option( $key, $default = false ) { return $GLOBALS['options'][ $key ] ?? $default; }
function delete_option( $key ) { unset( $GLOBALS['options'][ $key ] ); }
function delete_transient( $key ) { unset( $GLOBALS['transients'][ $key ] ); }
function esc_html( $value ) { return (string) $value; }
class SyncCacheDB {
	public $options = 'wp_options';
	public function get_col( $sql ) { return array( '_transient_rentfetch_propertysearch_markup_db' ); }
}
$wpdb = new SyncCacheDB();
require dirname( __DIR__ ) . '/lib/common/cache-helpers.php';
require dirname( __DIR__ ) . '/lib/common/functions-floorplans-array.php';
require dirname( __DIR__ ) . '/lib/admin/ajax-handlers/clear-search-cache.php';
function sync_cache_check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
$rentfetch_floorplans = array( 'known' => array( 'id' => array( 123 ) ) );
sync_cache_check( array() === rentfetch_get_floorplans( '' ), 'An empty property ID cannot receive the whole aggregate.' );
sync_cache_check( array() === rentfetch_get_floorplans( 'missing' ), 'An absent property must never receive other properties floor plans.' );
sync_cache_check( array( 'id' => array( 123 ) ) === rentfetch_get_floorplans( 'known' ), 'Known property results must be preserved.' );
sync_cache_check( $rentfetch_floorplans === rentfetch_get_floorplans(), 'An unscoped request must still return all properties.' );
$result = rentfetch_clear_search_cache_transients();
sync_cache_check( 2 === $result['count'], 'Both database and object-cache-only registered results must be cleared.' );
sync_cache_check( array( 'rf_analytics_searches' => 'keep analytics', 'unrelated' => 'keep unrelated cache' ) === $transients, 'Unrelated caches and analytics must be preserved.' );
sync_cache_check( ! isset( $rentfetch_floorplans ) && ! isset( $options['rentfetch_search_query_cache_registry'] ), 'Request aggregate and registry must not retain invalidated data.' );
echo "Frontend cache regressions passed: database and object cache invalidation, unrelated cache preservation, property isolation.\n";
