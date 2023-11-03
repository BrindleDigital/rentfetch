<?php

function rentfetch_search_filters_property_types() {
           
    // bail if propertytypes taxonomy does not exist
    if ( !taxonomy_exists('propertytypes') )
        return;
    
    // get information about types from the database
    $propertytypes = get_terms( 
        array(
            'taxonomy' => 'propertytypes',
            'hide_empty' => true,
        ),
    );
                    
    // build types search
    if ( !empty( $propertytypes && taxonomy_exists( 'propertytypes' ) ) ) {
        echo '<fieldset class="property-type">';
            echo '<legend>Property type</legend>';
            echo '<button class="toggle">Property type</button>';
            echo '<div class="input-wrap checkboxes">';
                    
                    foreach ($propertytypes as $propertytype) {
                        $name = $propertytype->name;
                        $propertytype_term_id = $propertytype->term_id;

                        // Check if the propertytype's term ID is in the GET parameter array
                        $checked = in_array($propertytype_term_id, $_GET['search-property-types'] ?? array());

                        printf(
                            '<label>
                                <input type="checkbox" 
                                    name="search-property-types[]" 
                                    value="%s" 
                                    data-propertytypes="%s" 
                                    data-propertytypesname="%s" 
                                    %s /> <!-- Add checked attribute if necessary -->
                                <span>%s</span>
                            </label>',
                            $propertytype_term_id,
                            $propertytype_term_id,
                            $name,
                            $checked ? 'checked' : '', // Apply checked attribute
                            $name
                        );
                    }
                    
            echo '</div>'; // .checkboxes
        echo '</fieldset>';
    }
        
}

add_filter( 'rentfetch_search_property_map_properties_query_args', 'rentfetch_search_properties_args_types', 10, 1 );
function rentfetch_search_properties_args_types( $property_args ) {
    
    if ( isset( $_POST['search-property-types'] ) && is_array( $_POST['search-property-types'] ) ) {
        
        // Get the values
        $property_types = $_POST['search-property-types'];
        
        // Escape the values
        $property_types = array_map( 'sanitize_text_field', $property_types );
        
        // This is an "AND" query, where we want posts to match ALL of the specified amenities
        $property_types_query = array(
            'relation' => 'AND',
        );

        foreach ( $property_types as $property_type ) {
            $property_types_query[] = array(
                'taxonomy' => 'propertytypes',
                'terms' => $property_type,
            );
        }
        
        // Add the amenities query to the property args tax query
        $property_args['tax_query'][] = $property_types_query;
    }
        
    return $property_args;
}