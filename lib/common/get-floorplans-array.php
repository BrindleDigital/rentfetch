<?php

function rentfetch_get_floorplans_array() {
    
    global $floorplans;
    
	$floorplans_args = array(
        'post_type' => 'floorplans',
        'posts_per_page' => -1,
		'orderby' => 'date', // we will sort posts by date
		'order'	=> 'ASC', // ASC or DESC
        'no_found_rows' => true,
	);
    
    $floorplans_args = apply_filters( 'rentfetch_search_property_map_floorplans_query_args', $floorplans_args );
    
    // console_log( 'Floorplans search args:' );
    // console_log( $floorplans_args );
    
	$floorplans_query = new WP_Query( $floorplans_args );
        
    // reset the floorplans array
    $floorplans = array();
     
	if( $floorplans_query->have_posts() ) :
                
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
            $availability_date = get_post_meta( $id, 'availability_date', true );
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
                    'availability_date' => array( $availability_date ),
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
                $floorplans[ $property_id ]['availability_date'][] = $availability_date;
                $floorplans[ $property_id ]['has_specials'][] = $has_specials;
            }
            
        endwhile;
        
		wp_reset_postdata();
        
	endif;
        
    foreach ( $floorplans as $key => $floorplan ) {
        
        //* BEDS 
        
        $max = max( $floorplan['beds'] );
        $min = min( $floorplan['beds'] );
        
        if ( $max == $min ) {
            $floorplans[$key]['bedsrange'] = $max;
        } else {
            $floorplans[$key]['bedsrange'] = $min . '-' . $max;
        }
        
        //* BATHS
        
        $max = max( $floorplan['baths'] );
        $min = min( $floorplan['baths'] );
        
        if ( $max == $min ) {
            $floorplans[$key]['bathsrange'] = $max;
        } else {
            $floorplans[$key]['bathsrange'] = $min . '-' . $max;
        }
        
        //* MAX RENT
        
        $floorplan['maximum_rent'] = array_filter( $floorplan['maximum_rent'], 'rentfetch_check_if_above_100' );
        $floorplan['minimum_rent'] = array_filter( $floorplan['minimum_rent'], 'rentfetch_check_if_above_100' );
        
        if ( !empty( $floorplan['maximum_rent'] ) ) {
            $max = max( $floorplan['maximum_rent'] );
        } else {
            $max = 0;
        }
        
        //* MIN RENT
        
        if ( !empty( $floorplan['minimum_rent'] ) ) {
            $min = min( $floorplan['minimum_rent'] );
        } else {
            $min = 0;
        }
        
        //* RENT RANGE
        
        if ( $max == $min ) {
            $floorplans[$key]['rentrange'] = number_format($max);
        } else {
            $floorplans[$key]['rentrange'] = number_format($min) . '-' . number_format($max);
        }
        
        if ( $min < 100 || $max < 100 )
            $floorplans[$key]['rentrange'] = null;
            
        //* SQFT RANGE
        
        $max = intval( max( $floorplan['maximum_sqft'] ) );
        $min = intval( min( $floorplan['minimum_sqft'] ) );
        
        if ( $max == $min ) {
            $floorplans[$key]['sqftrange'] = number_format($max);
        } else {
            $floorplans[$key]['sqftrange'] = number_format($min) . '-' . number_format($max);
        }
        
        //* AVAILABLE UNITS
        
        $units_array = $floorplan['available_units'];
        if ( $units_array ) {
            $units = array_sum( $units_array );
        } else {
            $units = 0;
        }
        
        $floorplans[$key]['availability'] = $units;
        
        //* AVAILABILITY DATE
        
        $availability_date_array = $floorplan['availability_date'];
        $floorplans[$key]['available_date'] = null;  // Initialize the available_date to null

        if ($availability_date_array) {
            foreach ($availability_date_array as $date_string) {
                // Skip if date string is empty
                if ($date_string == '') {
                    continue;
                }

                // Convert date string to DateTime object for comparison
                $date = DateTime::createFromFormat('Ymd', $date_string);

                // Skip if date string is not a valid date
                if ($date === false) {
                    continue;
                }

                // If available_date is null or the current date is earlier, update available_date
                if ($floorplans[$key]['available_date'] === null || $date < $floorplans[$key]['available_date']) {
                    $floorplans[$key]['available_date'] = $date;
                }
            }
        }

        // Convert the earliest date back to string format 'Ymd', if there's a valid date
        if ($floorplans[$key]['available_date'] !== null) {
            $floorplans[$key]['available_date'] = $floorplans[$key]['available_date']->format('F j');
        }

        
        
        //* SPECIALS
        
        // default value
        $floorplans[$key]['property_has_specials'] = false;
        
        // if there are specials, save that
        $has_specials = $floorplan['has_specials'];
        
        if ( in_array( true, $has_specials ) )        
            $floorplans[$key]['property_has_specials'] = true;
        
    }
    
    return $floorplans;
}

/**
 * Get the floorplans using the default function and make them available globally
 */
add_action( 'init', 'rentfetch_set_floorplans' );
function rentfetch_set_floorplans() {
    
    global $rentfetch_floorplans;
    $rentfetch_floorplans = rentfetch_get_floorplans_array();
    
}

/**
 * Get the floorplans from the global variable, and return those for a particular property
 */
function rentfetch_get_floorplans( $property_id = null ) {
    
    global $rentfetch_floorplans;
    $property_id = intval( $property_id );
    
    if ( $property_id && isset( $rentfetch_floorplans[$property_id] ) )
        return $rentfetch_floorplans[$property_id];
                        
    return $rentfetch_floorplans;
    
}

/**
 * For testing
 */
// add_action( 'wp_footer', 'rentfetch_dump_floorplan_array' );
function rentfetch_dump_floorplan_array() {
    
    global $rentfetch_floorplans;
    var_dump( $rentfetch_floorplans );
    
}
