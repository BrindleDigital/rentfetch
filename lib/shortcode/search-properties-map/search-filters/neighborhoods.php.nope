<?php

function rentfetch_search_properties_map_filters_neighborhoods() {
    //* get query parameters about neighborhoods
    if (isset($_GET['neighborhoods'])) {
        $neighborhoodsparam = $_GET['neighborhoods'];
        $neighborhoodsparam = explode( ',', $neighborhoodsparam );
        $neighborhoodsparam = array_map( 'esc_attr', $neighborhoodsparam );
    } else {
        $neighborhoodsparam = array();
    }
    
    // check whether neighborhoods search is enabled
    $map_search_components = get_option( 'options_map_search_components' );
    
    // this needs to be set to an array even if the option isn't set
    if ( !is_array( $map_search_components ) )
        $map_search_components = array();
    
    // bail if neighborhoods search is not enabled
    if ( !in_array( 'neighborhoods_search', $map_search_components ) )
        return;
    
    //* check whether neighborhoods search is enabled
    $map_search_components = get_option( 'options_map_search_components' );
    $enable_neighborhoods_search = get_option( 'options_map_search_components_neighborhoods_search' );
    if ( $enable_neighborhoods_search == true ) {
        
        //* get information about neighborhoods from the database
        $getneighborhoodsargs = array(
            'post_type' => 'neighborhoods',
            'posts_per_page' => '-1',
            'orderby' => 'name',
            'order' => 'DESC',
        );
        
        $neighborhoods = get_posts( $getneighborhoodsargs );
        
        //* build neighborhoods search
        if ( $neighborhoods ) {            
            echo '<div class="input-wrap input-wrap-neighborhoods">';
                echo '<div class="dropdown">';
                    echo '<button type="button" class="dropdown-toggle" data-reset="Neighborhoods">Neighborhoods</button>';
                    echo '<div class="dropdown-menu dropdown-menu-neighborhoods">';
                        echo '<div class="dropdown-menu-items">';
                                            
                            foreach( $neighborhoods as $neighborhood ) {
                                
                                // skip if there's a null value for neighborhood
                                if ( $neighborhood === null )
                                    continue;
                                    
                                $connected_properties = MB_Relationships_API::get_connected( [
                                    'id'   => 'properties_to_neighborhoods',
                                    'from' => $neighborhood,
                                ] );
                                    
                                // skip if there'snothing connected to the neighborhood
                                if ( empty( $connected_properties ) )
                                    continue;
                                
                                $neighborhood_name = $neighborhood->post_title;
                                $neighborhood_id = $neighborhood->ID;
                                    
                                printf( '<label><input type="checkbox" data-neighborhoods="%s" data-neighborhoods-name="%s" name="neighborhoods-%s" /><span>%s</span></label>', $neighborhood_id, $neighborhood_name, $neighborhood_id, $neighborhood_name );
                                                                        
                                // if ( in_array( $neighborhood, $neighborhoodsparam ) ) {
                                //     printf( '<label><input type="checkbox" data-neighborhoods="%s" name="neighborhoods-%s" checked /><span>%s</span></label>', $neighborhood_id, $neighborhood_id, $neighborhood_name );
                                // } else {
                                //     printf( '<label><input type="checkbox" data-neighborhoods="%s" name="neighborhoods-%s" /><span>%s</span></label>', $neighborhood_id, $neighborhood_id, $neighborhood_name );
                                // }
                            }
                            
                        echo '</div>';
                        echo '<div class="filter-application">';
                            echo '<a class="clear" href="#">Clear</a>';
                            echo '<a class="apply" href="#">Apply</a>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>'; // .dropdown
            echo '</div>'; // .input-wrap
        }
    
    }
}

add_filter( 'rentfetch_search_property_map_properties_query_args', 'rentfetch_search_property_map_properties_args_neighborhoods', 10, 1 );
function rentfetch_search_property_map_properties_args_neighborhoods( $property_args ) {
    // get the list of properties connected to the selected properties
    $properties_connected_to_selected_neighborhoods = rentfetch_get_connected_properties_from_selected_neighborhoods();
    if ( $properties_connected_to_selected_neighborhoods ) {
        $property_args['post__in'] = $properties_connected_to_selected_neighborhoods;            
    }    
    
    return $property_args;
}