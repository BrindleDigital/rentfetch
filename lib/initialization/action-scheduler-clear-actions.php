<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//////////////////////////////
// CLEAR OUT ACTIONS FASTER //
//////////////////////////////

/**
 * Change Action Scheduler default purge to 1 hour (because we generate a TON of actions)
 */
add_filter( 'action_scheduler_retention_period', 'wpb_action_scheduler_purge' );
function wpb_action_scheduler_purge() {
    return DAY_IN_SECONDS;
}
