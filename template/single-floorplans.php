<?php

get_header();

echo '<h1>Plugin single floorplans.</h1>';

$images = rentfetch_get_floorplan_images();

foreach ( $images as $image ) {
    printf( '<img style="width:100px; height: auto;" src="%s" />', $image['url'] );
}

get_footer();