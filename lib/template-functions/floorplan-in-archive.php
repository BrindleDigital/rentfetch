<?php

// add_action( 'rentfetch_do_floorplan_in_floorplans_block', 'rentfetch_floorplan_in_archive', 10, 1 );
add_action( 'rentfetch_do_floorplan_in_archive', 'rentfetch_floorplan_in_archive', 10, 1 );
function rentfetch_floorplan_in_archive( $post_id ) {
        
    // slick
    wp_enqueue_script( 'rentfetch-slick-main-script' );
    wp_enqueue_script( 'rentfetch-floorplan-images-slider-init' );
    wp_enqueue_style( 'rentfetch-slick-main-styles' );
    wp_enqueue_style( 'rentfetch-slick-main-theme' );
    
    // $post_id = $post->ID;
        
    //* Grab the data
    $title = get_the_title( $post_id );
    $available_units = get_post_meta( $post_id, 'available_units', true );    
        
    //* Set up the classes
    $floorplanclass = get_post_class( $post_id );
    
    if ( $available_units > 0 ) {
        $floorplanclass[] = 'units-available';
    } else {
        $floorplanclass[] = 'no-units-available';  
    } 
        
    $floorplanclass = implode( ' ', $floorplanclass );

    //* Do the markup
    printf( '<div class="%s">', $floorplanclass );
        echo '<div class="floorplan-inner">';
        
            do_action( 'rentfetch_do_each_floorplan_images' );
                
            echo '<div class="floorplan-content">';
            
                if ( $title )
                    printf( '<h3 class="floorplan-title">%s</h3>', $title );
                                        
                do_action( 'rentfetch_do_each_floorplan_availability' );
                
                do_action( 'rentfetch_do_each_floorplan_description' );
                
                do_action( 'rentfetch_floorplan_in_archive_do_show_specials' );
                                    
                echo '<p class="info">';
                
                    do_action( 'rentfetch_do_each_floorplan_beds' );
                    do_action( 'rentfetch_do_each_floorplan_baths' ); 
                    do_action( 'rentfetch_do_each_floorplan_squarefoot_range' );
                                                                
                echo '</p>';
                
                if ( current_user_can( 'edit_posts' ) ) {
                    echo '<p class="admin-data">';
                    
                        $floorplan_id = get_post_meta( get_the_ID(), 'floorplan_id', true );
                        $property_id = get_post_meta( get_the_ID(), 'property_id', true );
                    
                        if ( $floorplan_id )
                            printf( '<span><strong>Floorplan ID:</strong> %s</span>', $floorplan_id );
                            
                        if ( $property_id )
                            printf( '<span><strong>Property ID:</strong> %s</span>', $property_id );
                    
                    echo '</p>';
                }
                
                edit_post_link( 'Edit', '', '', $post_id );
                
            echo '</div>';  
            
            echo '<div class="floorplan-rent-range">';
            
                do_action( 'rentfetch_do_each_floorplan_rent_range' );
                do_action( 'rentfetch_do_each_floorplan_buttons' );
            
            echo '</div>'; // .floorplan-rent-range
            
          echo '</div>'; // .floorplan-inner  
        
    echo '</div>';
    
}

add_action( 'rentfetch_do_each_floorplan_description', 'rentfetch_each_floorplan_description' );
function rentfetch_each_floorplan_description() {
    
    $post_id = get_the_ID();
    
    $floorplan_description = get_post_meta( $post_id, 'floorplan_description', true );
    
    if ( $floorplan_description )
        printf( '<p class="floorplan-description">%s</p>', $floorplan_description );
    
    
}

add_action( 'rentfetch_do_each_floorplan_availability', 'rentfetch_each_floorplan_availability' );
function rentfetch_each_floorplan_availability() {
    
    $post_id = get_the_ID();
    
    $availability_date = get_post_meta( $post_id, 'availability_date', true );
    
    if ( $availability_date ) {
        $availability_date = date('m/d/y', strtotime($availability_date));
        printf( '<p class="availability-date"><span class="availability">Availability:</span> %s</p>', $availability_date );
    }
    
}

add_action( 'rentfetch_do_each_floorplan_baths', 'rentfetch_each_floorplan_baths' );
function rentfetch_each_floorplan_baths() {
    
    $post_id = get_the_ID();
        
    $baths = get_post_meta( $post_id, 'baths', true );
    $baths = floatval( $baths );
    
    // allow for hooking in
    $label = apply_filters( 'rentfetch_get_baths_label', $baths );
    
    if ( $baths )
        printf( '<span class="floorplan-baths">%s %s</span>', $baths, $label );
    
}

add_action( 'rentfetch_do_each_floorplan_beds', 'rentfetch_each_floorplan_beds' );
function rentfetch_each_floorplan_beds() {
    
    $post_id = get_the_ID();
        
    $beds = get_post_meta( $post_id, 'beds', true );
    $beds = floatval( $beds );
    
    // allow for hooking in
    $label = apply_filters( 'rentfetch_get_bedroom_number_label', $label = null, $beds );
            
    printf( '<span class="floorplan-beds">%s</span>', $label );
}

add_action( 'rentfetch_do_each_floorplan_rent_range', 'rentfetch_each_floorplan_rent_range' );
function rentfetch_each_floorplan_rent_range() {
    
    $rent_range_display_type = get_option( 'options_floorplan_pricing_display' );
    
    if ( $rent_range_display_type == 'range' || ( !$rent_range_display_type ) ) {
        // if there's no option set or if it's set to range...
        do_action( 'rentfetch_do_each_floorplan_rent_range_display_as_range' );        
    } elseif ( $rent_range_display_type == 'minimum' ) {
        // if it's set to minimum...
        do_action( 'rentfetch_do_each_floorplan_rent_range_display_as_minimum' );
    } 
}

add_action( 'rentfetch_do_each_floorplan_rent_range_display_as_range', 'rentfetch_each_floorplan_rent_range_display_as_range' );
function rentfetch_each_floorplan_rent_range_display_as_range() {
    
    $post_id = get_the_ID();
    $minimum_rent = get_post_meta( $post_id, 'minimum_rent', true );
    $maximum_rent = get_post_meta( $post_id, 'maximum_rent', true );
            
    $rent_range = null;
    if ( $minimum_rent && $maximum_rent ) {
        
        if ( $minimum_rent != $maximum_rent )
            $rent_range = sprintf( '<span class="dollars">$</span><span class="amount">%s</span>-<span class="amount">%s</span>', $minimum_rent, $maximum_rent );
            
        if ( $minimum_rent == $maximum_rent )
            $rent_range = sprintf( '<span class="dollars">$</span><span class="amount">%s</span>', $minimum_rent );
                    
    }
    
    if ( $minimum_rent < 100 || $maximum_rent < 100 )
        $rent_range = apply_filters( 'rentfetch_floorplan_pricing_unavailable_text', 'Pricing unavailable' );
            
            
    if ( $minimum_rent && !$maximum_rent ) $rent_range = sprintf( '<span class="dollars">$</span><span class="amount">%s</span>', $minimum_rent );
    if ( $maximum_rent && !$minimum_rent ) $rent_range = sprintf( '<span class="dollars">$</span><span class="amount">%s</span>', $maximum_rent );
        
    if ( $rent_range )
        printf( '<p class="rent_range">%s</p>', $rent_range );
}

add_action( 'rentfetch_do_each_floorplan_rent_range_display_as_minimum', 'rentfetch_each_floorplan_rent_range_display_as_minimum' );
function rentfetch_each_floorplan_rent_range_display_as_minimum() {
    
    $post_id = get_the_ID();
    $minimum_rent = get_post_meta( $post_id, 'minimum_rent', true );
    $maximum_rent = get_post_meta( $post_id, 'maximum_rent', true );
    
    $rent = null;
    
    // get the lesser value above 0
    if ( $minimum_rent > 100 && $maximum_rent > 100 ) 
        $rent = min( $minimum_rent, $maximum_rent );
        
    if ( $minimum_rent > 100 && $maximum_rent < 100 )
        $rent = $minimum_rent;
        
    if ( $minimum_rent < 100 && $maximum_rent > 100 )
        $rent = $maximum_rent;

    // output the rent
    if ( $rent > 100 ) {
        printf( '<p class="rent_range">From <span class="dollars">$</span><span class="amount">%s</span></p>', $rent );
    } else {
        $pricing_unavailable_label = apply_filters( 'rentfetch_floorplan_pricing_unavailable_text', 'Pricing unavailable' );
        printf( '<p class="rent_range">%s</p>', $pricing_unavailable_label );
    }
        
}

add_action( 'rentfetch_do_gform_lightbox', 'rentfetch_gform_lightbox' );
function rentfetch_gform_lightbox() {
    
    // fancybox
    wp_enqueue_style( 'rentfetch-fancybox-style' );
    wp_enqueue_script( 'rentfetch-fancybox-script' );
    
    // get the options
    $contact_button = get_option( 'options_contact_button' ); 
            
    $enabled = get_option( 'options_contact_button_enabled' );
        
    $gravity_form_id = get_option( 'options_contact_button_gravity_form_id' );
        
    //* bail if this button isn't enabled
    if ( $enabled !== true )
        return;
    
    //* bail if there's no gravity form ID specified
    if ( !$gravity_form_id )
        return;
    
    printf( '<div id="gform-%s" class="rentfetch-gform">', $gravity_form_id );
        echo do_shortcode( '[gravityform id="' . $gravity_form_id . '" title=false description=false ajax=true tabindex=49]');
    echo '</div>';
}

add_action( 'rentfetch_do_each_floorplan_buttons', 'rentfetch_each_floorplan_buttons' );
function rentfetch_each_floorplan_buttons() {
    
    // fancybox
    wp_enqueue_style( 'rentfetch-fancybox-style' );
    wp_enqueue_script( 'rentfetch-fancybox-script' );
    
    $contact_button = get_option( 'options_contact_button' );
    $availability_button = get_option( 'options_availability_button' );
    
    echo '<div class="floorplan-buttons">';
    
        // each of the buttons is hooked onto this action, which allows for simpler reordering if needed
        do_action( 'rentfetch_do_floorplan_each_button' );
            
    echo '</div>'; // .buttons
}

add_action( 'rentfetch_do_floorplan_each_button', 'rentfetch_default_tour_button', 5 );
function rentfetch_default_tour_button() {
    
    // get the options
    $tour_button = get_option( 'options_tour_button' ); 
    $enabled = get_option( 'options_tour_button_enabled' );
        
    //* bail if this button isn't enabled
    if ( $enabled !== true )
        return;
        
    $post_id = get_the_ID();
    $floorplan_video_or_tour = get_post_meta( $post_id, 'floorplan_video_or_tour', true );
                
    if ( isset( $tour_button['link_target'] ) )
        $link_target = $tour_button['link_target'];
        
    if ( isset( $tour_button['button_label'] ) )
        $button_label = $tour_button['button_label'];

    if ( $floorplan_video_or_tour && $link_target === 'lightbox' ) {
        
        if ( strpos( $floorplan_video_or_tour, 'youtube' ) !== false || strpos( $floorplan_video_or_tour, 'vimeo' ) !== false ) {
            // if this is a youtube/vimeo link
            printf( '<a href="#" data-fancybox data-src="%s" class="button tour-button">%s</a>', $floorplan_video_or_tour, $button_label );
        } else {
            // if it's anything else
            printf( '<a href="%s" data-fancybox data-type="iframe" class="button tour-button">%s</a>', $floorplan_video_or_tour, $button_label );
        }
        
    }
        
    if ( $floorplan_video_or_tour && $link_target === 'newtab' )
        printf( '<a href="%s" target="blank" class="button tour-button">%s</a>', $floorplan_video_or_tour, $button_label );
        
}

add_action( 'rentfetch_do_floorplan_each_button', 'rentfetch_default_availability_button', 10 );
function rentfetch_default_availability_button() {
    
    $post_id = get_the_ID();
    
    // get the options
    $availability_button = get_option( 'options_availability_button', 'options' ); 
            
    $enabled = get_option( 'options_availability_button_enabled' );
        
    //* bail if this button isn't enabled
    if ( $enabled !== true )
        return;
        
    $button_label = get_option( 'options_availability_button_button_label' );
    
    $button_behavior = get_option( 'options_availability_button_button_behavior' );
    
    $link = get_option( 'options_availability_button_link' );
        
    $availability_url = get_post_meta( $post_id, 'availability_url', true );    
    $available_units = get_post_meta( $post_id, 'available_units', true );
        
    // if there's a specific link, use that instead
    if ( $availability_url )
        $link = $availability_url;
        
        
    // bail if there's no link to output
    if ( !$link )
        return;
        
    if ( $available_units > 0 || $button_behavior === 'fallback' )
        printf( '<a href="%s" class="button availability-button" target="_blank">%s</a>', $link, $button_label );
        
}

add_action( 'rentfetch_do_floorplan_each_button', 'rentfetch_default_contact_button', 15 );
function rentfetch_default_contact_button() {
    
    // ob_start();
        
    // get the options
    $contact_button = get_option( 'options_contact_button' ); 
        
    $enabled = get_option( 'options_contact_button_enabled' );
        
    //* bail if this button isn't enabled
    if ( $enabled !== true )
        return;
    
    $button_type = get_option( 'options_contact_button_button_type' );
    
    $link = get_option( 'options_contact_button_link' );

    $link_target = get_option( 'options_contact_button_link_target' );

    $gravity_form_id = get_option( 'options_contact_button_gravity_form_id' );

    $button_label = get_option( 'options_contact_button_button_label' );
    
    if ( $button_type === 'link' && !empty( $link ) && !empty( $link_target ) && !empty( $button_label ) )
        printf( '<a href="%s" target="%s" class="button contact-button">%s</a>', $link, $link_target, $button_label );
            
    if ( $button_type === 'gform' && !empty( $gravity_form_id ) )
        printf( '<a href="#" data-fancybox data-src="#gform_wrapper_%s" class="button contact-button">%s</a>', $gravity_form_id, $button_label );
        
    // return ob_get_clean();
        
}

add_action( 'rentfetch_do_floorplan_each_button', 'rentfetch_default_single_button', 20 );
function rentfetch_default_single_button() {
    
    // get the options
    $single_button = get_option( 'options_single_button' ); 
        
    $enabled = get_option( 'options_single_button_enabled' );
        
    //* bail if this button isn't enabled
    if ( $enabled !== true )
        return;
        
    $post_id = get_the_ID();
    $permalink = get_the_permalink( $post_id );
            
    $button_label = get_option( 'options_single_button_button_label' );
    
    if ( $permalink )
        printf( '<a href="%s" class="button single-button">%s</a>', $permalink, $button_label );
       
}

add_action( 'rentfetch_do_each_floorplan_squarefoot_range', 'rentfetch_each_floorplan_squarefoot_range' );
function rentfetch_each_floorplan_squarefoot_range() {
    
    $post_id = get_the_ID();
    
    $maximum_sqft = get_post_meta( $post_id, 'maximum_sqft', true );
    $minimum_sqft = get_post_meta( $post_id, 'minimum_sqft', true );
    
    $sqft_range = null;
    
    if ( $minimum_sqft && $maximum_sqft ) {
        
        if ( $minimum_sqft != $maximum_sqft )
            $sqft_range = sprintf( '%s-%s', $minimum_sqft, $maximum_sqft );
            
        if ( $minimum_sqft == $maximum_sqft )
            $sqft_range = sprintf( '%s', $minimum_sqft );
        
    }
    
    if ( $minimum_sqft && !$maximum_sqft ) $sqft_range = sprintf( '%s', $minimum_sqft );
    if ( !$minimum_sqft && $maximum_sqft ) $sqft_range = sprintf( '%s', $maximum_sqft );
    
    $sqft_range = apply_filters( 'rentfetch_customize_sqft_text', $sqft_range );
    
    if ( $sqft_range )
        printf( '<span class="floorplan-sqft_range">%s</span>', $sqft_range );
}

add_filter( 'rentfetch_customize_sqft_text', 'rentfetch_default_beds_text', 10, 1  );
function rentfetch_default_beds_text( $sqft_range ) {
    
    // bail if there isn't a value
    if ( $sqft_range == null )
        return;
        
    return $sqft_range . ' sqft';
}

add_action( 'rentfetch_do_each_floorplan_images', 'rentfetch_each_floorplan_images' );
function rentfetch_each_floorplan_images() {
    
    // fancybox
    wp_enqueue_style( 'rentfetch-fancybox-style' );
    wp_enqueue_script( 'rentfetch-fancybox-script' );
    
    $post_id = get_the_ID();
    
    $page_title = get_the_title();
    
    //* get the images from whatever source we have
    $floorplan_image_urls = null;
    $floorplan_images = apply_filters( 'floorplan_image_urls', $floorplan_image_urls );
                
    // if there aren't any images, then output a fallback
    if ( !$floorplan_images ) {
        
        $floorplan_image = RENTFETCH_PATH . 'images/fallback-property.svg';
        
        echo '<div class="floorplan-images-wrap">';
                    
            echo '<div class="floorplan-slider">';
                    echo '<div class="floorplan-slide">';
                        printf( '<img loading=lazy src="%s" alt="%s" title="%s" />', $floorplan_image, $page_title, $page_title );
                    echo '</div>';
            echo '</div>';
        echo '</div>';
        
    } else {
        
        echo '<div class="floorplan-images-wrap">';
                    
            echo '<div class="floorplan-slider">';
                        
                foreach( $floorplan_images as $floorplan_image ) {
                                                                                                                            
                    // detect if there are special characters
                    $regex = preg_match('[@_!#$%^&*()<>?/|}{~:]', $floorplan_image);
                                                            
                    // bail on this slide if there are special characters in the image url
                    if ( $regex )
                        break;
                                        
                    echo '<div class="floorplan-slide">';
                        printf( '<a data-fancybox="gallery-%s" href="%s" ><img loading=lazy src="%s" alt="%s" title="%s" /></a>', $post_id, $floorplan_image, $floorplan_image, $page_title, $page_title );
                    echo '</div>';
                    
                    // $count++;
                    
                }
            
            echo '</div>';
        echo '</div>';
    }    
}

$floorplan_image_urls = null;
add_filter( 'floorplan_image_urls', 'floorplan_get_image_urls', $floorplan_image_urls );
function floorplan_get_image_urls() {
    
    // get the ID of the post we're on
    $post_id = get_the_ID();
    
    // set the value of floorplan_image_urls to nothing
    $floorplan_image_urls = array();
    
    //* Try for manual images first
    $manual_images = get_post_meta( $post_id, 'manual_images', true );
    
    
            
    if ( $manual_images ) {
        
        if ( !is_array( $manual_images ) )
            $manual_images = explode( ',', $manual_images );
        
        foreach ( $manual_images as $manual_image ) {
            $floorplan_image_urls[] = wp_get_attachment_image_url($manual_image, 'large' );
        }
        
        return $floorplan_image_urls;
    }
      
    //* Try for Yardi images next
    $yardi_image_urls = get_post_meta( $post_id, 'floorplan_image_url', true );
        
    if ( $yardi_image_urls ) {
        
        $floorplan_image_urls = explode( ',', $yardi_image_urls );
        
        return $floorplan_image_urls;    
    }
        
    //* if we didn't find any images, just return nothing
    return null;
    
}


add_action( 'rentfetch_floorplan_in_archive_do_show_specials', 'rentfetch_floorplan_in_archive_show_specials' );
function rentfetch_floorplan_in_archive_show_specials() {
    $post_id = get_the_ID();
    
    $has_specials = get_post_meta( $post_id, 'has_specials', true );
    
    // bail if there are no specials
    if ( $has_specials != true )
        return;
        
    $text = null; // set $text to null, since we're not passing anything in
    $specials_text = apply_filters( 'rentfetch_has_specials_text', $text );    
    printf( '<div class="has-specials-floorplan-wrap"><div class="has-specials-floorplan">%s</div></div>', $specials_text );
}

add_filter( 'rentfetch_has_specials_text', 'rentfetch_default_specials_text', 10, 1 );
function rentfetch_default_specials_text( $specials_text ) {
    $specials_text = 'Specials available';
    return $specials_text;
} 