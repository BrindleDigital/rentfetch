<?php

/**
 * Do the property images, selecting how to output those
 */

function rentfetch_property_images() {
	
	// get the single image
	rentfetch_property_single_image();
	
	// get the slider
	//! TODO: add a slider capability and an option to toggle between these
	
}
add_action( 'rentfetch_do_property_images', 'rentfetch_property_images' );

/**
 * Single image for each property
 */
function rentfetch_property_single_image() {
	$images = rentfetch_get_property_images();            

	?>
	<div class="property-single-image-wrap">
		<img class="property-single-image" src="<?php echo $images[0]['url']; ?>" loading="lazy">
	</div>
	<?php
}
