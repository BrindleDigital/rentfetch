<?php

add_action( 'add_meta_boxes', 'rf_register_properties_details_metabox' );
function rf_register_properties_details_metabox() {
        
    add_meta_box(
        'rf_properties_identifiers', // ID of the metabox
        'Property Identifiers', // Title of the metabox
        'rf_properties_identifiers_metabox_callback', // Callback function to render the metabox
        'properties', // Post type to add the metabox to
        'normal', // Priority of the metabox
        'default' // Context of the metabox
    );
   
    add_meta_box(
        'rf_properties_contact', // ID of the metabox
        'Property Contact Information', // Title of the metabox
        'rf_properties_contact_metabox_callback', // Callback function to render the metabox
        'properties', // Post type to add the metabox to
        'normal', // Priority of the metabox
        'default' // Context of the metabox
    );
    
    add_meta_box(
        'rf_properties_location', // ID of the metabox
        'Property Location', // Title of the metabox
        'rf_properties_location_metabox_callback', // Callback function to render the metabox
        'properties', // Post type to add the metabox to
        'normal', // Priority of the metabox
        'default' // Context of the metabox
    );
    
    add_meta_box(
        'rf_properties_details', // ID of the metabox
        'Property Display Information', // Title of the metabox
        'rf_properties_display_information_metabox_callback', // Callback function to render the metabox
        'properties', // Post type to add the metabox to
        'normal', // Priority of the metabox
        'default' // Context of the metabox
    );
        
}

function rf_properties_identifiers_metabox_callback( $post ) {
    wp_nonce_field( 'rf_properties_metabox_nonce', 'rf_properties_metabox_nonce' );
    
    
    ?>
    <div class="rf-metabox rf-metabox-properties">
        
        <div class="columns columns-3">
        
            <?php 
            //* Property Source
            $property_source = get_post_meta( $post->ID, 'property_source', true ); 
            if ( !$property_source )
                $property_source = 'Manually managed';
            ?>
            
            <div class="field">
                <div class="column">
                    <label for="property_source">Property Source</label>
                </div>
                <div class="column">
                    <input disabled type="text" id="property_source" name="property_source" value="<?php echo esc_attr( $property_source ); ?>">
                    <p class="description">This isn't a field meant to be edited; it's here to show you how this property is currently being managed (whether it syncs from a data source or it's manually managed).</p>
                </div>
            </div>
                                
            
            <?php 
            //* Property ID
            $property_id = get_post_meta( $post->ID, 'property_id', true ); ?>
            <div class="field">
                <div class="column">
                    <label for="property_id">Property ID</label>
                </div>
                <div class="column">
                    <input type="text" id="property_id" name="property_id" value="<?php echo esc_attr( $property_id ); ?>">
                    <p class="description">The Property ID should match the Property ID on each associated floorplan, and every property should always have a property ID.</p>
                    <p class="description"><span id="view-related-floorplans"></span> <span id="view-related-units"></span></p>
                    
                    <script type="text/javascript">
                        jQuery(document).ready(function($) {
                            function updateLink() {
                                var propertyId = $('#property_id').val();
                                var floorplanLink = '/wp-admin/edit.php?ac-actions-form=1&orderby=607b962c381064&order=asc&post_status=all&post_type=floorplans&layout=6048fca7a7894&action=-1&paged=1&action2=-1';
                                var unitLink = '/wp-admin/edit.php?ac-actions-form=1&orderby=607b962c381064&order=asc&post_status=all&post_type=units&layout=6048fca7a7894&action=-1&paged=1&action2=-1';

                                if (propertyId) {
                                    floorplanLink += '&s=' + propertyId;
                                    unitLink += '&s=' + propertyId;
                                }

                                $('#view-related-floorplans').html('<a href="' + floorplanLink + '" target="_blank">View Related Floorplans</a>');
                                $('#view-related-units').html('<a href="' + unitLink + '" target="_blank">View Related Units</a>');
                            }

                            // On load
                            updateLink();

                            // On change
                            $('#property_id').on('change', function() {
                                updateLink();
                            });
                        });
                    </script>
                    
                </div>
            </div>
            
            <?php 
            //* Property Code
            $property_code = get_post_meta( $post->ID, 'property_code', true ); ?>
            <div class="field">
                <div class="column">
                    <label for="property_code">Voyager Property Code</label>
                </div>
                <div class="column">
                    <input type="text" id="property_code" name="property_code" value="<?php echo esc_attr( $property_code ); ?>">
                    <p class="description">In Yardi, properties also have a Voyager property code, so if this property is synced with Yardi, that may show below as well (if this is not a Yardi property, you can probably ignore this).</p>
                </div>
            </div>
            
        </div>
    </div>
    <?php
}

function rf_properties_location_metabox_callback( $post ) {
    ?>
    <div class="rf-metabox rf-metabox-properties">
        
        <div class="columns columns-4">
        
            <?php 
            //* Property Address
            $address = get_post_meta( $post->ID, 'address', true ); ?>
            <div class="field">
                <div class="column">
                    <label for="address">Address</label>
                </div>
                <div class="column">
                    <input type="text" id="address" name="address" value="<?php echo esc_attr( $address ); ?>">
                </div>
            </div>
            
            <?php 
            //* Property City
            $city = get_post_meta( $post->ID, 'city', true ); ?>
            <div class="field">
                <div class="column">
                    <label for="city">City</label>
                </div>
                <div class="column">
                    <input type="text" id="city" name="city" value="<?php echo esc_attr( $city ); ?>">
                </div>
            </div>
            
            <?php 
            //* Property State
            $state = get_post_meta( $post->ID, 'state', true ); ?>
            <div class="field">
                <div class="column">
                    <label for="state">State</label>
                </div>
                <div class="column">
                    <input type="text" id="state" name="state" value="<?php echo esc_attr( $state ); ?>">
                </div>
            </div>
            
            <?php 
            //* Property Zipcode
            $zipcode = get_post_meta( $post->ID, 'zipcode', true ); ?>
            <div class="field">
                <div class="column">
                    <label for="zipcode">Zipcode</label>
                </div>
                <div class="column">
                    <input type="text" id="zipcode" name="zipcode" value="<?php echo esc_attr( $zipcode ); ?>">
                </div>
            </div>
        
        </div>
        
        <div class="columns columns-2">
        
            <?php 
            //* Property Latitude
            $latitude = get_post_meta( $post->ID, 'latitude', true ); ?>
            <div class="field">
                <div class="column">
                    <label for="latitude">Latitude</label>
                </div>
                <div class="column">
                    <input type="text" id="latitude" name="latitude" value="<?php echo esc_attr( $latitude ); ?>">
                </div>
            </div>
            
            <?php 
            //* Property Longitude
            $longitude = get_post_meta( $post->ID, 'longitude', true ); ?>
            <div class="field">
                <div class="column">
                    <label for="longitude">Longitude</label>
                </div>
                <div class="column">
                    <input type="text" id="longitude" name="longitude" value="<?php echo esc_attr( $longitude ); ?>">
                </div>
            </div>
            
        </div>
       
    </div>
    <?php
}

function rf_properties_contact_metabox_callback( $post ) {
    ?>
    <div class="rf-metabox rf-metabox-properties">
        
        <div class="columns columns-3">
            
            <?php 
            //* Property Email
            $email = get_post_meta( $post->ID, 'email', true ); ?>
            <div class="field">
                <div class="column">
                    <label for="email">Email</label>
                </div>
                <div class="column">
                    <input type="text" id="email" name="email" value="<?php echo esc_attr( $email ); ?>">
                </div>
            </div>
            
            <?php 
            //* Property Phone
            $phone = get_post_meta( $post->ID, 'phone', true ); ?>
            <div class="field">
                <div class="column">
                    <label for="phone">Phone</label>
                </div>
                <div class="column">
                    <input type="text" id="phone" name="phone" value="<?php echo esc_attr( $phone ); ?>">
                </div>
            </div>
            
            <?php 
            //* Property URL
            $url = get_post_meta( $post->ID, 'url', true ); ?>
            <div class="field">
                <div class="column">
                    <label for="url">URL</label>
                </div>
                <div class="column">
                    <input type="text" id="url" name="url" value="<?php echo esc_attr( $url ); ?>">
                </div>
            </div>
            
        </div>
        
    </div>
    <?php
}

function rf_properties_display_information_metabox_callback( $post ) {
    wp_enqueue_media();
    wp_enqueue_script( 'rentfetch-metabox-properties-images' );
    wp_enqueue_script( 'rentfetch-metabox-properties-matterport' );
    wp_enqueue_script( 'rentfetch-metabox-properties-video' );
    ?>
    <div class="rf-metabox rf-metabox-properties">
        <?php //* Property Images ?>
        <div class="field">
            <div class="column">
                <label for="images">Custom Images</label>
            </div>
            <div class="column"> 
                <p class="description">These are custom images added to the site, and are never synced. Any image here will override any synced images.</p>               
                <?php
                
                $images = get_post_meta( $post->ID, 'images', true );
                
                // convert to string
                if ( is_array( $images ) )
                    $images = implode( ',', $images );
                                    
                $images_ids_array = explode( ',', $images );
                $image_url = '';
                
                echo '<input type="hidden" id="images" name="images" value="' . esc_attr( $images ) . '">';
                
                if ( $images ) {
                    echo '<div id="gallery-container">';
                        foreach( $images_ids_array as $image_id ) {
                            $attachment_url = wp_get_attachment_image_src( $image_id, 'thumbnail' );
                            printf( '<div class="gallery-image" data-id="%s"><img src="%s"><button class="remove-image">Remove</button></div>', $image_id, $attachment_url[0] );
                        }
                    echo '</div>';
                }
                
                echo '<div id="gallery-container">' . $image_url . '</div>';                
                echo '<input type="button" id="images_button" class="button" value="Add Images">';
        
                ?>
                
            </div>
        </div>
        
        <?php
        
        $property_source = get_post_meta($post->ID, 'property_source', true );
        if ( $property_source == 'yardi' ) {
            
            //* Property Images from Yardi
            $property_images_json = get_post_meta( $post->ID, 'property_images', true );
            $property_images = json_decode( $property_images_json );
            ?>
             
            <div class="field">
                <div class="column">
                    <label for="property_images">Yardi Property Images</label>
                    <p class="description">These images are not editable, because they're from Yardi. This is merely a preview so that you can see the images being provided. Feel free to click 'download' on any of these so that you can easily grab any that you want if you're adding more.</p>
                </div>
                <div class="column">                
                    <?php
                    if ( $property_images ) {
                        echo '<div class="property_images">';
                        
                        foreach ( $property_images as $property_image ) {
                            
                            if ( !property_exists( $property_image, 'ImageURL' ) ) {
                                printf( '<p>%s <em>An error typically indicates either a failed sync or a problem with API authentication. Be sure to look up your specific error code with the owner of this API.</em></p>', $property_images_json );
                                continue;
                            }
                                                        
                            printf( '<div class="property-image"><img src="%s"/><a href="%s" target="_blank" class="download" download>Download</a></div>', $property_image->ImageURL, $property_image->ImageURL );                
                        }
                        echo '</div>';
                    } else {
                        echo '<p class="description">No images available</p>';
                    }
                    ?>
                    
                </div>
            </div>
            <?php
        }
        ?>
             
        <?php 
        //* Property Description
        $description = get_post_meta( $post->ID, 'description', true ); ?>
        <div class="field">
            <div class="column">
                <label for="description">Description</label>
            </div>
            <div class="column">                
                <textarea rows="3" id="description" name="description"><?php echo esc_attr( $description ); ?></textarea>
                <p class="description">The description is synced from most APIs, but if yours is not, this is the main place to put general information about this property.</p>
            </div>
        </div>

        <?php 
        //* Matterport
        $matterport = get_post_meta( $post->ID, 'matterport', true ); ?>
        <div class="field">
            <div class="column">
                <label for="matterport">Tour Matterport embed code</label>
            </div>
            <div class="column">
                <input type="text" id="matterport" name="matterport" value="<?php echo esc_attr( $matterport ); ?>">
                <?php 
                $iframeCode = '<iframe src="https://my.matterport.com/showcase-beta?m=VBHn8iJQ1h4" width="640" height="480" frameborder="0" allowfullscreen allow="vr"></iframe>';
                $escapedIframeCode = htmlspecialchars($iframeCode);
                ?>

                <p class="description">Paste in a Matterport iframe code. This code will look something like this: <?php echo $escapedIframeCode; ?></p>
                <div id="matterport-preview"></div>
            </div>
        </div>
                
        <?php 
        //* Tour video
        $video = get_post_meta( $post->ID, 'video', true ); ?>
        <div class="field">
            <div class="column">
                <label for="video">Tour video oembed link</label>
            </div>
            <div class="column">
                <input type="text" id="video" name="video" value="<?php echo esc_attr( $video ); ?>">
                <p class="description">Just a Youtube link (e.g. <a href="https://www.youtube.com/watch?v=C0DPdy98e4c" target="_blank">https://www.youtube.com/watch?v=C0DPdy98e4c</a>). Vimeo videos will <em>usually</em> work as well, but be sure to test these on the frontend.</p>
                <div id="video-container" style="width:100%; max-width: 300px;"></div> <!-- a container to hold the video -->
            </div>
        </div>
                
        <?php 
        //* Property Pets
        $pets = get_post_meta( $post->ID, 'pets', true ); ?>
        <div class="field">
            <div class="column">
                <label for="pets">Pets</label>
            </div>
            <div class="column">
                <input type="text" id="pets" name="pets" value="<?php echo esc_attr( $pets ); ?>">
            </div>
        </div>
        
        <?php 
        //* Property Content Area
        $content_area = get_post_meta( $post->ID, 'content_area', true ); ?>
        <div class="field">
            <div class="column">
                <label for="content_area">Content area</label>
                <p class="description">The content area is always unsynced, so if you have more to say, you can say it here.</p>
            </div>
            <div class="column">
                <?php
                wp_editor( $content_area, 'content_area', array(
                    'textarea_name' => 'content_area',
                    'media_buttons' => false,
                    'textarea_rows' => 10,
                    'teeny' => false,
                    'tinymce' => true,
                ) );
                ?>
                <p class="description">It's always recommended to start this section with a heading level 2. If this is empty, the content area section of the single-properties template will not be displayed (there won't be a blank space). By default, if there's something to say here, this section will display below the amenities.</p>
            </div>
        </div>
        
    </div>
    
    <?php
}

add_action( 'save_post', 'rf_save_properties_metaboxes' );
function rf_save_properties_metaboxes( $post_id ) {
    
    if ( !isset( $_POST['rf_properties_metabox_nonce'] ) )
        return;

    if ( ! wp_verify_nonce( $_POST['rf_properties_metabox_nonce'], 'rf_properties_metabox_nonce' ) )
        return;
    
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;
    
    if ( isset( $_POST['property_id'] ) )
        update_post_meta( $post_id, 'property_id', sanitize_text_field( $_POST['property_id'] ) );
        
    if ( isset( $_POST['property_code'] ) )
        update_post_meta( $post_id, 'property_code', sanitize_text_field( $_POST['property_code'] ) );
        
    if ( isset( $_POST['address'] ) )
        update_post_meta( $post_id, 'address', sanitize_text_field( $_POST['address'] ) );
        
    if ( isset( $_POST['city'] ) )
        update_post_meta( $post_id, 'city', sanitize_text_field( $_POST['city'] ) );
        
    if ( isset( $_POST['state'] ) )
        update_post_meta( $post_id, 'state', sanitize_text_field( $_POST['state'] ) );
        
    if ( isset( $_POST['zipcode'] ) )
        update_post_meta( $post_id, 'zipcode', sanitize_text_field( $_POST['zipcode'] ) );
        
    if ( isset( $_POST['latitude'] ) )
        update_post_meta( $post_id, 'latitude', sanitize_text_field( $_POST['latitude'] ) );
        
    if ( isset( $_POST['longitude'] ) )
        update_post_meta( $post_id, 'longitude', sanitize_text_field( $_POST['longitude'] ) );
        
    if ( isset( $_POST['email'] ) )
        update_post_meta( $post_id, 'email', sanitize_text_field( $_POST['email'] ) );
        
    if ( isset( $_POST['phone'] ) )
        update_post_meta( $post_id, 'phone', sanitize_text_field( $_POST['phone'] ) );
        
    if ( isset( $_POST['url'] ) )
        update_post_meta( $post_id, 'url', sanitize_text_field( $_POST['url'] ) );
        
    if ( isset( $_POST['images'] ) ) {
        $property_images = sanitize_text_field( $_POST['images'] );
        $property_images = trim($property_images, ",");
        $property_images = explode(",", $property_images);
        $property_images = array_unique( $property_images );
        
        update_post_meta( $post_id, 'images', $property_images );
    }
        
    if ( isset( $_POST['description'] ) )
        update_post_meta( $post_id, 'description', sanitize_text_field( $_POST['description'] ) );
        
    if ( isset( $_POST['matterport'] ) ) {
        
        $allowed_tags = array(
            'iframe' => [
                'src' => [],
                'width' => [],
                'height' => [],
                'frameborder' => [],
                'allowfullscreen' => [],
                'allow' => [],
            ],
        );
        
        update_post_meta( $post_id, 'matterport', wp_kses( $_POST['matterport'], $allowed_tags ) );
        
    }
        
    if ( isset( $_POST['video'] ) )
        update_post_meta( $post_id, 'video', sanitize_text_field( $_POST['video'] ) );
        
    if ( isset( $_POST['pets'] ) )
        update_post_meta( $post_id, 'pets', sanitize_text_field( $_POST['pets'] ) );
        
    if ( isset( $_POST['content_area'] ) ) {
        $allowed_tags = array(
            'h2' => [],
            'h3' => [],
            'p' => [],
            'ul' => [],
            'ol' => [],
            'li' => [],
            'a' => [
                'href' => [],
                'title' => [],
                'target' => [],
            ],
            'br' => [],
            'em' => [],
            'strong' => [],
        );
                
        update_post_meta( $post_id, 'content_area', wp_kses( $_POST['content_area'], $allowed_tags ) );
    }

}