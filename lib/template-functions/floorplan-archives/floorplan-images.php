<?php

/**
 * Do the floorplan images, selecting how to output those
 */
add_action( 'rentfetch_do_floorplan_images', 'rentfetch_floorplan_images' );
function rentfetch_floorplan_images() {
    
    // get the single image
    rentfetch_floorplan_single_image();
    
    // get the slider
    //! TODO: add a slider capability and an option to toggle between these
    
}

/**
 * Single image for each floorplan
 */
function rentfetch_floorplan_single_image() {
    $images = rentfetch_get_floorplan_images();            

    ?>
    <div class="floorplan-single-image-wrap">
        <img class="floorplan-single-image" src="<?php echo $images[0]['url']; ?>" loading="lazy">
    </div>
    <?php
}
