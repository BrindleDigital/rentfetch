<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function rentfetch_check_if_above_100( $var ) {
	
	if ( $var > 100 )
		return $var;
		
	return null;
}