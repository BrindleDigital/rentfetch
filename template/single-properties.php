<?php

get_header();

//* Markup
echo '<div class="single-properties-wrap">';
	
	do_action( 'rentfetch_do_single_properties_parts' );
	
echo '</div>'; // .single-properties-wrap

get_footer();