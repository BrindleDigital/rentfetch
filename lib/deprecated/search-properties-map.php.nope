<?php

add_shortcode( 'propertymap', 'rentfetch_propertymap' );
function rentfetch_propertymap( $atts ) {
    
    ob_start();
    
    // search scripts and styles
    wp_enqueue_style( 'rentfetch-search-properties-map' );
    
    // Localize the search filters general script, then enqueue that
    $search_options = array(
        'maximum_bedrooms_to_search' => intval( get_option( 'options_maximum_bedrooms_to_search' ) ),
    );
    wp_localize_script( 'rentfetch-search-filters-general', 'searchoptions', $search_options );
    wp_enqueue_script( 'rentfetch-search-filters-general' );
        
    wp_enqueue_script( 'rentfetch-search-properties-ajax' );
    wp_enqueue_script( 'rentfetch-search-properties-script' );
    wp_enqueue_script( 'rentfetch-toggle-map' );
        
    // slick
    wp_enqueue_script( 'rentfetch-slick-main-script' );
    wp_enqueue_style( 'rentfetch-slick-main-styles' );
    wp_enqueue_style( 'rentfetch-slick-main-theme' );
    
    // properties in archive
    wp_enqueue_style( 'rentfetch-properties-in-archive' );
    wp_enqueue_script( 'rentfetch-property-images-slider-init' );
    
    // favorites
    wp_enqueue_script( 'rentfetch-property-favorites-cookies' );
    wp_enqueue_script( 'rentfetch-property-favorites' );
    
    // the map itself
    $key = apply_filters( 'rentfetch_get_google_maps_api_key', null );
    wp_enqueue_script( 'rentfetch-google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $key, array(), null, true );
            
    // Localize the google maps script, then enqueue that
    $maps_options = array(
        'json_style' => json_decode( get_option( 'options_google_maps_styles' ) ),
        'marker_url' => get_option( 'options_google_map_marker' ),
        'google_maps_default_latitude' => get_option( 'options_google_maps_default_latitude' ),
        'google_maps_default_longitude' => get_option( 'options_google_maps_default_longitude' ),
    );
    wp_localize_script( 'rentfetch-property-map', 'options', $maps_options );
    wp_enqueue_script( 'rentfetch-property-map');
    
    //* Start the form...
    echo '<div class="properties-search-wrap">';
        printf( '<form class="property-search-filters" action="%s/wp-admin/admin-ajax.php" method="POST" id="filter" style="opacity:0;">', site_url() );
        
            //* check the query to see if we have a text-based search
            if (isset($_GET['textsearch'])) {
                $searchtext = $_GET['textsearch'];
                $searchtext = esc_attr( $searchtext );
                
            } else {
                $searchtext = null;
            }
        
            //* check whether text-based search is enabled
            $map_search_components = get_option( 'options_map_search_components' );
            $enable_text_based_search = get_option( 'options_map_search_components_text_based_search' );
            if ( $enable_text_based_search == true ) {
                                
                $placeholder = apply_filters( 'rentfetch_search_placeholder_text', 'Search city or zipcode ...' );
                
                //* build the text-based search
                echo '<div class="input-wrap input-wrap-text-search">';
                    if ( $searchtext ) {
                        printf( '<input type="text" name="textsearch" placeholder="%s" class="active" value="%s" />', $placeholder, $searchtext );
                    } else {
                        printf( '<input type="text" name="textsearch" placeholder="%s" />', $placeholder );
                    }
                echo '</div>';
                
            }
                    
            //* Reset
            printf( '<a href="%s" class="reset link-as-button">Reset</a>', get_permalink( get_the_ID() ) );
            
            // beds parameter
            if (isset($_GET['beds'])) {
                $bedsparam = $_GET['beds'];
                $bedsparam = explode( ',', $bedsparam );
                $bedsparam = array_map( 'esc_attr', $bedsparam );
            } else {
                $bedsparam = array();
            }
            
            //* check whether beds search is enabled
            $map_search_components = get_option( 'options_map_search_components' );
            $enable_beds_search = get_option( 'options_map_search_components_beds_search' );
            if ( $enable_beds_search == true ) {
                
                //* get info about beds from the database
                $beds = rentfetch_get_meta_values( 'beds', 'floorplans' );
                $beds = array_unique( $beds );
                asort( $beds );
                        
                //* build the beds search
                echo '<div class="input-wrap input-wrap-beds">';
                    echo '<div class="dropdown">';
                        echo '<button type="button" class="dropdown-toggle" data-reset="Beds">Beds</button>';
                        echo '<div class="dropdown-menu dropdown-menu-beds">';
                            echo '<div class="dropdown-menu-items">';
                            
                                foreach( $beds as $bed ) {
                                    
                                    // skip if there's a null value for bed
                                    if ( $bed === null )
                                        continue;
                                        
                                    $label = apply_filters( 'rentfetch_get_bedroom_number_label', $label = null, $bed );
                                        
                                    if ( in_array( $bed, $bedsparam ) ) {
                                        printf( '<label><input type="checkbox" data-beds="%s" name="beds-%s" checked /><span>%s</span></label>', $bed, $bed, $label );
                                    } else {
                                        printf( '<label><input type="checkbox" data-beds="%s" name="beds-%s" /><span>%s</span></label>', $bed, $bed, $label );
                                    }
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
                
            //* get query parameters about baths
            if (isset($_GET['baths'])) {
                $bathsparam = $_GET['baths'];
                $bathsparam = explode( ',', $bathsparam );
                $bathsparam = array_map( 'esc_attr', $bathsparam );
            } else {
                $bathsparam = array();
            }
                
            //* check whether beds search is enabled
            $map_search_components = get_option( 'options_map_search_components' );
            $enable_baths_search = get_option( 'options_map_search_components_baths_search' );
            if ( $enable_baths_search == true ) {
                
                //* get information about baths from the database
                $baths = rentfetch_get_meta_values( 'baths', 'floorplans' );
                $baths = array_unique( $baths );
                asort( $baths );
                
                //* build the baths search
                echo '<div class="input-wrap input-wrap-baths">';
                    echo '<div class="dropdown">';
                        echo '<button type="button" class="dropdown-toggle" data-reset="Baths">Baths</button>';
                        echo '<div class="dropdown-menu dropdown-menu-baths">';
                            echo '<div class="dropdown-menu-items">';
                                foreach( $baths as $bath ) {
                                    if ( in_array( $bath, $bathsparam ) ) {
                                        printf( '<label><input type="checkbox" data-baths="%s" name="baths-%s" checked /><span>%s Bathroom</span></label>', $bath, $bath, $bath );
                                    } else {
                                        printf( '<label><input type="checkbox" data-baths="%s" name="baths-%s" /><span>%s Bathroom</span></label>', $bath, $bath, $bath );
                                    }
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
            
            
            //* get query parameters about neighborhoods
            //TODO add a param for this to the search start page
            if (isset($_GET['neighborhoods'])) {
                $neighborhoodsparam = $_GET['neighborhoods'];
                $neighborhoodsparam = explode( ',', $neighborhoodsparam );
                $neighborhoodsparam = array_map( 'esc_attr', $neighborhoodsparam );
            } else {
                $neighborhoodsparam = array();
            }
            
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
            
            //* get query parameters about types
            if ( isset( $_GET['propertytypes'])) {
                $propertytypesparam = $_GET['propertytypes'];
                $propertytypesparam = explode( ',', $propertytypesparam );
                $propertytypesparam = array_map( 'esc_attr', $propertytypesparam );
            } else {
                $propertytypesparam = array();
            }   
            
            //* check whether type search is enabled
            $map_search_components = get_option( 'options_map_search_components' );
            $enable_type_search = get_option( 'options_map_search_components_type_search' );
            if ( $enable_type_search == true && taxonomy_exists( 'propertytypes' ) ) {
                
                //* get information about types from the database
                $propertytypes = get_terms( 
                    array(
                        'taxonomy' => 'propertytypes',
                        'hide_empty' => true,
                    ),
                );
                                
                //* build types search
                if ( !empty( $propertytypes && taxonomy_exists( 'propertytypes' ) ) ) {
                    echo '<div class="input-wrap input-wrap-propertytypes">';
                        echo '<div class="dropdown">';
                            echo '<button type="button" class="dropdown-toggle" data-reset="Type">Type</button>';
                            echo '<div class="dropdown-menu dropdown-menu-propertytypes">';
                                echo '<div class="dropdown-menu-items">';
                                    foreach( $propertytypes as $propertytype ) {
                                        $name = $propertytype->name;
                                        $propertytype_term_id = $propertytype->term_id;
                                        if ( in_array( $propertytype_term_id, $propertytypesparam ) ) {
                                                printf( '<label><input type="checkbox" data-propertytypes="%s" data-propertytypesname="%s" name="propertytypes-%s" checked /><span>%s</span></label>', $propertytype_term_id, $name, $propertytype_term_id, $name );
                                        } else {
                                            printf( '<label><input type="checkbox" data-propertytypes="%s" data-propertytypesname="%s" name="propertytypes-%s" /><span>%s</span></label>', $propertytype_term_id, $name, $propertytype_term_id, $name );
                                        }
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
        
            //* check whether date-based search is enabled
            $map_search_components = get_option( 'options_map_search_components' );
            $enable_date_search = get_option( 'options_map_search_components_date_search' );
            if ( $enable_date_search == true ) {
                
                //* enqueue date picker scripts
                wp_enqueue_style( 'rentfetch-flatpickr-style' );
                wp_enqueue_script( 'rentfetch-flatpickr-script' );
                wp_enqueue_script( 'rentfetch-flatpickr-script-init' );
                
                //* build the date-based search
                echo '<div class="input-wrap input-wrap-date-available">';
                    echo '<div class="dropdown">';
                        echo '<div class="flatpickr">';
                            echo '<input type="text" name="dates" placeholder="Available date" style="width:auto;" data-input>';
                        echo '</div>';
                    echo '</div>'; // .dropdown
                echo '</div>'; // .input-wrap
                
            }
            
            //* check whether price search is enabled
            $map_search_components = get_option( 'options_map_search_components' );
            $enable_price_search = get_option( 'options_map_search_components_price_search' );
            if ( $enable_price_search == true ) {
                
                //* figure out our min/max values
                $price_settings = get_option( 'options_price_filter' );
                $valueSmall = isset( $price_settings['minimum'] ) ? $price_settings['minimum'] : 0;
                $valueBig = isset( $price_settings['maximum'] ) ? $price_settings['maximum'] : 5000;
                $step = isset( $price_settings['step'] ) ? $price_settings['step'] : 50;        
                
                //* enqueue the noui slider
                wp_enqueue_style( 'rentfetch-nouislider-style' );
                wp_enqueue_script( 'rentfetch-nouislider-script' );
                wp_localize_script( 'rentfetch-nouislider-init-script', 'settings', 
                    array(
                        'valueSmall' => $valueSmall,
                        'valueBig' => $valueBig,
                        'step' => $step,
                    )
                );
                wp_enqueue_script( 'rentfetch-nouislider-init-script' );
                
                //* build the price search
                echo '<div class="input-wrap input-wrap-prices">';
                    echo '<div class="dropdown">';
                        echo '<button type="button" class="dropdown-toggle" data-reset="Price">Price</button>';
                        echo '<div class="dropdown-menu dropdown-menu-prices">';
                            echo '<div class="dropdown-menu-items">';
                                echo '<div class="price-slider-wrap"><div id="price-slider" style="width:100%;"></div></div>';
                                echo '<div class="inputs-prices">';
                                    printf( '<input type="number" name="pricesmall" id="pricesmall" value="%s" />', $valueSmall );
                                    printf( '<input type="number" name="pricebig" id="pricebig" value="%s" />', $valueBig );
                                echo '</div>';
                            echo '</div>';
                            echo '<div class="filter-application">';
                                echo '<a class="clear" href="#">Clear</a>';
                                echo '<a class="apply" href="#">Apply</a>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>'; // .dropdown
                echo '</div>'; // .input-wrap
                
            }
            
            //* check whether amenities search is enabled
            $map_search_components = get_option( 'options_map_search_components' );
            $enable_amenities_search = get_option( 'options_map_search_components_amenities_search' );
            if ( $enable_amenities_search == true ) {
                
                //* figure out how many amenities to show
                $number_of_amenities_to_show = get_option( 'options_number_of_amenities_to_show' );
                if ( empty( $number_of_amenities_to_show ) )
                    $number_of_amenities_to_show = 10;
                
                //* get information about amenities from the database
                $amenities = get_terms( 
                    array(
                        'taxonomy'      => 'amenities',
                        'hide_empty'    => true,
                        'number'        => $number_of_amenities_to_show,
                        'orderby'       => 'count',
                        'order'         => 'DESC',
                    ),
                );
                    
                //* build amenities search
                if ( !empty( $amenities ) && taxonomy_exists( 'amenities' ) ) {
                    echo '<div class="input-wrap input-wrap-amenities">';
                        echo '<div class="dropdown">';
                            echo '<button type="button" class="dropdown-toggle" data-reset="Amenities">Amenities</button>';
                            echo '<div class="dropdown-menu dropdown-menu-amenities">';
                                echo '<div class="dropdown-menu-items">';
                                    foreach( $amenities as $amenity ) {
                                        $name = $amenity->name;
                                        $amenity_term_id = $amenity->term_id;
                                        printf( '<label><input type="checkbox" data-amenities="%s" data-amenities-name="%s" name="amenities-%s" /><span>%s</span></label>', $amenity_term_id, $name, $amenity_term_id, $name );
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
                    
            //* check whether pets search is enabled
            $map_search_components = get_option( 'options_map_search_components' );
            $enable_pets_search = get_option( 'options_map_search_components_pets_search' );
            if ( $enable_pets_search == true ) {
                
                //* get information about pets from the database
                $pets = rentfetch_get_meta_values( 'pets', 'properties' );
                $pets = array_unique( $pets );
                asort( $pets );
                $pets = array_filter( $pets );
                                
                $pets_choices = [
                  1 => 'Cats allowed',
                  2 => 'Cats and Dogs allowed',  
                  3 => 'Pet-friendly', 
                  4 => 'Pets not allowed',
                ];
                                        
                //* build the pets search
                if ( !empty( $pets ) ) {
                    echo '<div class="input-wrap input-wrap-pets">';
                        echo '<div class="dropdown">';
                            echo '<button type="button" class="dropdown-toggle" data-reset="Pet policy">Pet policy</button>';
                            echo '<div class="dropdown-menu dropdown-menu-pets">';
                                echo '<div class="dropdown-menu-items">';
                                    foreach( $pets as $pet ) {
                                        printf( '<label><input type="radio" data-pets="%s" data-pets-name="%s" name="pets" value="%s" /><span>%s</span></label>', $pet, $pets_choices[$pet], $pet, $pets_choices[$pet] );
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
                    
            //* Buttons        
            echo '<button type="submit" style="display:none;">Search</button>';
            echo '<input type="hidden" name="action" value="propertysearch">';
            
        echo '</form>';
        
        //* Our container markup for the results
        echo '<div class="map-response-wrap">';
            echo '<div id="response"></div>';
            echo '<a class="toggle"></a>';
            echo '<div id="map"></div>';
        echo '</div>';
    
    echo '</div>'; // .properties-search-wrap

    return ob_get_clean();
}

add_action( 'wp_ajax_propertysearch', 'rentfetch_filter_properties' ); // wp_ajax_{ACTION HERE} 
add_action( 'wp_ajax_nopriv_propertysearch', 'rentfetch_filter_properties' );
function rentfetch_filter_properties(){
            
    //* start with floorplans
	$floorplans_args = array(
        'post_type' => 'floorplans',
        'posts_per_page' => -1,
		'orderby' => 'date', // we will sort posts by date
		'order'	=> 'ASC', // ASC or DESC
        // 'cache_results' => false,
        // 'update_post_meta_cache' => false,
        // 'update_post_term_cache' => false,
        'no_found_rows' => true,
	);
        
    //* bedrooms
    $beds = rentfetch_get_meta_values( 'beds', 'floorplans' );
    $beds = array_unique( $beds );
    asort( $beds );
    
    // loop through the checkboxes, and for each one that's checked, let's add that value to our meta query array
    foreach ( $beds as $bed ) {
        if ( isset( $_POST['beds-' . $bed ] ) && $_POST['beds-' . $bed ] == 'on' ) {
            $bed = sanitize_text_field( $bed );
            $bedsarray[] = $bed;
        }
    }
    
    // add the meta query array to our $floorplans_args
    if ( isset( $bedsarray ) ) {
        $floorplans_args['meta_query'][] = array(
            array(
                'key' => 'beds',
                'value' => $bedsarray,
            )
        );
    }
    
    //* bathrooms
    $baths = rentfetch_get_meta_values( 'baths', 'floorplans' );
    $baths = array_unique( $baths );
    asort( $baths );
    
    // loop through the checkboxes, and for each one that's checked, let's add that value to our meta query array
    foreach ( $baths as $bath ) {
        if ( isset( $_POST['baths-' . $bath ] ) && $_POST['baths-' . $bath ] == 'on' ) {
            $bath = sanitize_text_field( $bath );
            $bathsarray[] = $bath;
        }
    }
    
    // add the meta query array to our $floorplans_args
    if ( isset( $bathsarray ) ) {
        $floorplans_args['meta_query'][] = array(
            array(
                'key' => 'baths',
                'value' => $bathsarray,
            )
        );
    }
    
    //* Date    
    if ( isset( $_POST['dates'] ) ) {
                
        // get the dates, in a format like this: 'YYYYMMDD to YYYYMMDD'
        $datestring = sanitize_text_field( $_POST['dates'] );
    
        // get the dates into an array
        $dates = explode( ' to ', $datestring  );
        
        // typical use, we have two dates, a start and end
        if ( count( $dates ) == 2 ) {
                    
            // do a between query against the availability dates
            $floorplans_args['meta_query'][] = array(
                array(
                    'key' => 'availability_date',
                    'value' => array( $dates[0], $dates[1] ),
                    'type' => 'numeric',
                    'compare' => 'BETWEEN',
                )
            );
            
        // or we might just have one date, which we'll treat as an end
        } elseif ( count( $dates ) == 1 && !empty( $dates[0] ) ) {
            
            $yesterday = date('Ymd',strtotime("-1 days"));
                        
            // do a between query between yesterday and the date entered
            $floorplans_args['meta_query'][] = array(
                array(
                    'key' => 'availability_date',
                    'value' => array( $yesterday, $dates[0] ),
                    'type' => 'numeric',
                    'compare' => 'BETWEEN',
                )
            );
           
        // no date is set, so let's not make that part of the query; fall back to available units
        } else {
                        
            // if the date is anything else, then we need to only pick up floorplans that have more than 0 units available
            $property_availability_display = $price_settings = get_option( 'options_property_availability_display', 'options' );
            if ( $property_availability_display != 'all' ) {
                $floorplans_args['meta_query'][] = array(
                    array(
                        'key' => 'available_units',
                        'value' => 0,
                        'compare' => '>'
                    )
                );
            }            
            
        }
        
    }
            
    //* Add the actual rent parameters if those are set
    if ( isset( $_POST['pricesmall'] ) && isset( $_POST['pricebig'] ) ) {
        
        $defaultpricesmall = 100;
        $defaultpricebig = 5000;
        
        // get the small value
        if ( isset( $_POST['pricesmall'] ) )
            $pricesmall = intval( sanitize_text_field( $_POST['pricesmall'] ) );
            
        // if it's not a good value, then change it to something sensible
        if ( $pricesmall < 100 )
            $pricesmall = $defaultpricesmall;
        
        // get the big value
        if ( isset( $_POST['pricebig'] ) )
            $pricebig = intval( sanitize_text_field( $_POST['pricebig'] ) );
            
        // if there's isn't one, then use the default instead
        if ( empty( $pricebig ) )
            $pricebig = $defaultpricebig;
                
        
        // if we're showing all properties, then by default we need to ignore pricing
        $property_availability_display = $price_settings = get_option( 'options_property_availability_display', 'options' );
        if ( $property_availability_display == 'all' ) {
            
            // but if pricing parameters are actually being manually set, then we need that search to work
            if ( $pricesmall > 100 || $pricebig < 5000 ) {
                                    
                $floorplans_args['meta_query'][] = array(
                    array(
                        'key' => 'minimum_rent',
                        'value' => array( $pricesmall, $pricebig ),
                        'type' => 'numeric',
                        'compare' => 'BETWEEN',
                    )
                );
            }            
        } else {
            // if this is an availability search, then always take pricing into account
            $floorplans_args['meta_query'][] = array(
                    array(
                        'key' => 'minimum_rent',
                        'value' => array( $pricesmall, $pricebig ),
                        'type' => 'numeric',
                        'compare' => 'BETWEEN',
                    )
                );
        }
    }
     
    // var_dump( $floorplans_args );
    
	$floorplans_query = new WP_Query( $floorplans_args );
    
    // var_dump( $floorplans_query->post );
    
    // reset the floorplans array
    $floorplans = array();
     
	if( $floorplans_query->have_posts() ) :
        
        // printf( '<div class="count"><h2 class="post-count"><span class="number">%s</span> results</h2><p>Note: Right now this is searching floorplans. Long-term, it will need to search the floorplans first, then do a secondary search of the associated properties.</p></div>', $numberofposts );
        
            while( $floorplans_query->have_posts() ): $floorplans_query->the_post();
                        
                $id = get_the_ID();
                $property_id = get_post_meta( $id, 'property_id', true );
                $beds = get_post_meta( $id, 'beds', true );
                $baths = get_post_meta( $id, 'baths', true );
                $minimum_rent = get_post_meta( $id, 'minimum_rent', true );
                $maximum_rent = get_post_meta( $id, 'maximum_rent', true );
                $minimum_sqft = get_post_meta( $id, 'minimum_sqft', true );
                $maximum_sqft = get_post_meta( $id, 'maximum_sqft', true );
                $available_units = get_post_meta( $id, 'available_units', true );
                $has_specials = get_post_meta( $id, 'has_specials', true );
                
                if ( !isset( $floorplans[$property_id ] ) ) {
                    $floorplans[ $property_id ] = array(
                        'id' => array( $id ),
                        'beds' => array( $beds ),
                        'baths' => array( $baths ),
                        'minimum_rent' => array( $minimum_rent ),
                        'maximum_rent' => array( $maximum_rent ),
                        'minimum_sqft' => array( $minimum_sqft ),
                        'maximum_sqft' => array( $maximum_sqft ),
                        'available_units' => array( $available_units ),
                        'has_specials' => array( $has_specials ),
                    );
                } else {
                    $floorplans[ $property_id ]['id'][] = $id;
                    $floorplans[ $property_id ]['beds'][] = $beds;
                    $floorplans[ $property_id ]['baths'][] = $baths;
                    $floorplans[ $property_id ]['minimum_rent'][] = $minimum_rent;
                    $floorplans[ $property_id ]['maximum_rent'][] = $maximum_rent;
                    $floorplans[ $property_id ]['minimum_sqft'][] = $minimum_sqft;
                    $floorplans[ $property_id ]['maximum_sqft'][] = $maximum_sqft;
                    $floorplans[ $property_id ]['available_units'][] = $available_units;
                    $floorplans[ $property_id ]['has_specials'][] = $has_specials;
                }
                
            endwhile;
        
		wp_reset_postdata();
        
	endif;
    
    // echo '<pre style="font-size: 14px;">';
    // print_r( $floorplans );
    // echo '</pre>';
    
    foreach ( $floorplans as $key => $floorplan ) {
        $max = max( $floorplan['beds'] );
        $min = min( $floorplan['beds'] );
        
        if ( $max == $min ) {
            $floorplans[$key]['bedsrange'] = $max;
        } else {
            $floorplans[$key]['bedsrange'] = $min . '-' . $max;
        }
        
        $max = max( $floorplan['baths'] );
        $min = min( $floorplan['baths'] );
        
        if ( $max == $min ) {
            $floorplans[$key]['bathsrange'] = $max;
        } else {
            $floorplans[$key]['bathsrange'] = $min . '-' . $max;
        }
        
        $floorplan['maximum_rent'] = array_filter( $floorplan['maximum_rent'], 'rentfetch_check_if_above_100' );
        $floorplan['minimum_rent'] = array_filter( $floorplan['minimum_rent'], 'rentfetch_check_if_above_100' );
        
        if ( !empty( $floorplan['maximum_rent'] ) ) {
            $max = max( $floorplan['maximum_rent'] );
        } else {
            $max = 0;
        }
        
        if ( !empty( $floorplan['minimum_rent'] ) ) {
            $min = min( $floorplan['minimum_rent'] );
        } else {
            $min = 0;
        }
        
        if ( $max == $min ) {
            $floorplans[$key]['rentrange'] = '$' . $max;
        } else {
            $floorplans[$key]['rentrange'] = '$' . $min . '-' . $max;
        }
        
        if ( $min < 100 || $max < 100 )
            $floorplans[$key]['rentrange'] = apply_filters( 'rentfetch_floorplan_pricing_unavailable_text', 'Pricing unavailable' );
        
        $max = max( $floorplan['maximum_sqft'] );
        $min = min( $floorplan['minimum_sqft'] );
        
        if ( $max == $min ) {
            $floorplans[$key]['sqftrange'] = $max;
        } else {
            $floorplans[$key]['sqftrange'] = $min . '-' . $max;
        }
        
        // default value
        $floorplans[$key]['property_has_specials'] = false;
        
        // if there are specials, save that
        $has_specials = $floorplan['has_specials'];
        
        if ( in_array( true, $has_specials ) )        
            $floorplans[$key]['property_has_specials'] = true;
        
    }
    
    $property_ids = array_keys( $floorplans );
    if ( empty( $property_ids ) )
    $property_ids = array( '1' ); // if there aren't any properties, we shouldn't find anything – empty array will let us find everything, so let's pass nonsense to make the search find nothing
    
    
    
    
    // echo '<pre style="font-size: 14px;">';
    // print_r( $property_ids );
    // echo '</pre>';
    
    // set null for $properties_posts_per_page
    $properties_maximum_per_page = null;
    $properties_maximum_per_page = apply_filters( 'rentfetch_properties_maximum', $properties_maximum_per_page );
    
    $orderby = apply_filters( 'rentfetch_get_property_orderby', $orderby = 'menu_order' );
    $order = apply_filters( 'rentfetch_get_property_order', $order = 'ASC' );
    
    //* The base property query
    $propertyargs = array(
        'post_type' => 'properties',
        'posts_per_page' => $properties_maximum_per_page,
		'orderby' => $orderby,
		'order'	=> $order, // ASC or DESC
        'no_found_rows' => true,
	);
    
    //* Add text-based search into the 
    $search = null;
    
    if ( isset( $_POST['textsearch'] ) ) {
        $search = $_POST['textsearch'];
        $search = sanitize_text_field( $search );
    }
        
    if ( $search != null ) {
        $propertyargs['s'] = $search;
        
        // force the site to use relevanssi if it's installed
        if ( function_exists( 'relevanssi_truncate_index_ajax_wrapper' ) )
            $propertyargs['relevanssi'] = true;
    }    
    
    //* Add all of our property IDs into the property search
    $propertyargs['meta_query'] = array(
        array(
            'key' => 'property_id',
            'value' => $property_ids,
        ),
    );
    
    //* Pets (this is a simple one)
    if ( isset( $_POST['pets'] ) ) {
        $propertyargs['meta_query'][] = array(
            array(
                'key' => 'pets',
                'value' => sanitize_text_field( $_POST['pets'] ),
            )
        );
    }
    
    if ( taxonomy_exists( 'propertytypes' ) ) {
        
        //* Add the tax queries
        $propertyargs['tax_query'] = array();
        
        //* propertytype taxonomy
        $propertytypes = get_terms( 
            array(
                'taxonomy' => 'propertytypes',
                'hide_empty' => true,
            ),
        );
        
        // loop through the checkboxes, and for each one that's checked, let's add that value to our tax query array
        foreach ( $propertytypes as $propertytype ) {
            $name = $propertytype->name;
            $propertytype_term_id = $propertytype->term_id;
            
            if ( isset( $_POST['propertytypes-' . $propertytype_term_id ] ) && $_POST['propertytypes-' . $propertytype_term_id ] == 'on' ) {
                $propertytype_term_id = sanitize_text_field( $propertytype_term_id );
                $propertytypeids[] = $propertytype_term_id;
            }
        }
            
        // add the meta query array to our $args
        if ( isset( $propertytypeids ) ) {
            $propertyargs['tax_query'][] = array(
                array(
                    'taxonomy' => 'propertytypes',
                    'terms' => $propertytypeids,
                )
            );
        }
        
        //* amenities taxonomy
        $number_of_amenities_to_show = get_option( 'options_number_of_amenities_to_show' );
        if ( empty( $number_of_amenities_to_show ) )
            $number_of_amenities_to_show = 10;
        
        $amenities = get_terms( 
            array(
                'taxonomy'      => 'amenities',
                'hide_empty'    => true,
                'number'        => $number_of_amenities_to_show,
                'orderby'       => 'count',
                'order'         => 'DESC',
            ),
        );
        
        // loop through the checkboxes, and for each one that's checked, let's add that value to our tax query array
        foreach ( $amenities as $amenity ) {
            $name = $amenity->name;
            $amenity_term_id = $amenity->term_id;
            
            if ( isset( $_POST['amenities-' . $amenity_term_id ] ) && $_POST['amenities-' . $amenity_term_id ] == 'on' ) {
                $amenity_term_id = sanitize_text_field( $amenity_term_id );

                // this is an "AND" query, unlike property types, because here we only want things showing up where ALL of the conditions are met
                $propertyargs['tax_query'][] = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'amenities',
                        'terms' => $amenity_term_id,
                    )
                );
            }
        } 
        
    }
        
    // get the list of properties connected to the selected properties
    $properties_connected_to_selected_neighborhoods = rentfetch_get_connected_properties_from_selected_neighborhoods();
    if ( $properties_connected_to_selected_neighborhoods ) {
        $propertyargs['post__in'] = $properties_connected_to_selected_neighborhoods;            
    }    
    
    // echo '<pre>';
    // print_r( $propertyargs );
    // echo '</pre>';
    
    $propertyquery = new WP_Query( $propertyargs );
    
    // echo '<pre>';
    // print_r( $propertyquery );
    // echo '</pre>';
    
    if( $propertyquery->have_posts() ) :
        
        $numberofposts = $propertyquery->post_count;
        
        if ( $numberofposts == $properties_maximum_per_page ) {
            printf( '<div class="count"><h2 class="post-count">More than <span class="number">%s</span> properties found</h2></div>', $numberofposts );
        } else {
            printf( '<div class="count"><h2 class="post-count"><span class="number">%s</span> results</h2></div>', $numberofposts );
        }
        
        echo '<div class="properties-loop">';
            while( $propertyquery->have_posts() ): $propertyquery->the_post();
                $property_id = get_post_meta( get_the_ID(), 'property_id', true );
                $floorplan = $floorplans[$property_id ];                
                do_action( 'rentfetch_do_each_property', $propertyquery->post->ID, $floorplan );
            endwhile;
        echo '</div>';
        
		wp_reset_postdata();
        
	else :
        
		echo 'No properties with availability were found matching the current search parameters.';
        
	endif;
 
	die();
}

// Add a filter for the maximum properties to show per page, setting the fallback to 100 if there's nothing set
add_filter( 'rentfetch_properties_maximum', 'rentfetch_properties_maximum_setting', 10, 1 );
function rentfetch_properties_maximum_setting( $properties_maximum_per_page ) {
    
    $properties_maximum_per_page = get_option( 'options_maximum_number_of_properties_to_show' );
    
    if ( $properties_maximum_per_page )
        return $properties_maximum_per_page;
        
    return 100;
    
}

function rentfetch_get_connected_properties_from_selected_neighborhoods() {
    
    //bail if there's no relationships installed
    if ( !class_exists( 'MB_Relationships_API' ) )
        return;
    
    $getneighborhoodsargs = array(
        'post_type' => 'neighborhoods',
        'posts_per_page' => '-1',
        'orderby' => 'name',
        'order' => 'DESC',
    );
        
    $neighborhoods = get_posts( $getneighborhoodsargs );
    $selected_neighborhoods = array();
        
    foreach ( $neighborhoods as $neighborhood ) {
                
        $neighborhood_name = $neighborhood->post_title;
        $neighborhood_id = $neighborhood->ID;
        
        if ( isset( $_POST['neighborhoods-' . $neighborhood_id ] ) && $_POST['neighborhoods-' . $neighborhood_id ] == 'on' ) {
            $neighborhood_id = sanitize_text_field( $neighborhood_id );            
            $selected_neighborhoods[] = $neighborhood_id;
        }
    }
    
    $properties = MB_Relationships_API::get_connected( [
        'id'   => 'properties_to_neighborhoods',
        'from' => $selected_neighborhoods,
    ] );
    
    $properties_connected_to_selected_neighborhoods = array();
    foreach ( $properties as $property ) {
        $properties_connected_to_selected_neighborhoods[] = intval( $property->ID );
    }
        
    array_unique( $properties_connected_to_selected_neighborhoods );
    // $properties_connected_to_selected_neighborhoods = implode( ',', $properties_connected_to_selected_neighborhoods );
    // var_dump( $properties_connected_to_selected_neighborhoods );
    
    return $properties_connected_to_selected_neighborhoods;
}