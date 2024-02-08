<?php
/**
 * Flooroplan images
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Do the floorplan images, selecting how to output those
 */
function rentfetch_floorplan_images() {
	
	
	if ( is_singular( 'floorplans' ) ) {
		rentfetch_floorplan_image_slider();
	} elseif ( is_singular( 'properties' ) ) {
		rentfetch_floorplan_image_slider();
	} else {
		rentfetch_floorplan_single_image();	
	}
	
	// get the single image
	
	
	// get the slider
	//! TODO: add a slider capability and an option to toggle between these
	
}
add_action( 'rentfetch_do_floorplan_images', 'rentfetch_floorplan_images' );

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


function rentfetch_floorplan_image_slider() {
	$images = rentfetch_get_floorplan_images();
		
	wp_enqueue_script( 'blaze-script' );
	wp_enqueue_script( 'rentfetch-floorplan-images-slider-init' );
	wp_enqueue_style( 'blaze-style' );

	echo '<div class="floorplan-images-slider blaze-slider">';
		echo '<div class="blaze-container">';
			echo '<div class="blaze-track-container">';
				echo '<div class="blaze-track">';
				
					foreach ( $images as $image ) {
						?>
						<div class="floorplan-image-slide">
							<img class="floorplan-image" src="<?php echo $image['url']; ?>" loading="lazy">
						</div>
						<?php
					}
					
				echo '</div>'; // .blaze-track
			echo '</div>'; // .blaze-track-container
			
			if ( count( $images ) > 1 ) {
				echo '<div class="blaze-buttons">';
					echo '<button class="blaze-prev"></button>';
					echo '<button class="blaze-next"></button>';
				echo '</div>';
			}
			

			// echo '<div class="blaze-pagination"></div>';
			
		echo '</div>'; // .blaze-container
	echo '</div>'; // .blaze-slider
}