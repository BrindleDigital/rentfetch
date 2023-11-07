<?php 

add_action( 'rentfetch_do_each_property_in_archive', 'rentfetch_each_property_in_archive_simple' );
function rentfetch_each_property_in_archive_simple() {
    
    $title = rentfetch_get_property_title();
    $property_location = rentfetch_get_property_location();
    
    $permalink = apply_filters( 'rentfetch_filter_property_permalink', get_the_permalink() );
    $permalink_target = apply_filters( 'rentfetch_filter_property_permalink_target', '_self' );
    
    if ( $permalink )
        printf( '<a class="overlay" href="%s" target="%s"></a>', esc_url( $permalink ), esc_attr( $permalink_target ) );
    
    do_action( 'rentfetch_do_property_images' );
    
    echo '<div class="property-content">';
    
        if ( $title )
            printf( '<h3>%s</h3>', esc_html( $title ) );
            
        if ( $property_location )
            printf( '<p class="property-location">%s</p>', esc_html( $property_location ) );
        
    echo '</div>';
    
}