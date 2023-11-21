<?php

function rentfetch_enqueue_units_admin_style() {
	
	// bail if admin columns pro is active, or admin columns is active, since our styles conflict with those plugins
	if ( is_plugin_active( 'admin-columns-pro/admin-columns-pro.php' ) || is_plugin_active( 'codepress-admin-columns/codepress-admin-columns.php' ) )
		return;
	
	$current_screen = get_current_screen();
  
	// Check if the current screen is the admin archive page of the units content type
	if ( $current_screen->base === 'edit' && $current_screen->post_type === 'units' ) {
		
		// Enqueue your custom admin style
		wp_enqueue_style( 'units-edit-admin-style', RENTFETCH_PATH . 'css/admin/admin-edit-units.css', array(), RENTFETCH_VERSION, 'screen' );
	}
}
add_action( 'admin_enqueue_scripts', 'rentfetch_enqueue_units_admin_style' );

function rentfetch_default_units_admin_columns( $columns ) {
	
	$columns = array(
		'cb' =>                        '<input type="checkbox" />',
		'title' =>                     __( 'Title', 'rentfetch' ),
		'unit_id' =>                   __( 'Unit ID', 'rentfetch' ),
		'floorplan_id' =>              __( 'Floorplan ID', 'rentfetch' ),
		'property_id' =>               __( 'Property ID', 'rentfetch' ),
		'apply_online_url' =>          __( 'Apply URL', 'rentfetch' ),
		'availability_date' =>         __( 'Availability date', 'rentfetch' ),
		'baths' =>                     __( 'Baths', 'rentfetch' ),
		'beds' =>                      __( 'Beds', 'rentfetch' ),
		'deposit' =>                   __( 'Deposit', 'rentfetch' ),
		'minimum_rent' =>              __( 'Min Rent', 'rentfetch' ),
		'maximum_rent' =>              __( 'Max Rent', 'rentfetch' ),
		'sqrft' =>                     __( 'Sqrft', 'rentfetch' ),
		'specials' =>                  __( 'Specials', 'rentfetch' ),
		'yardi_unit_image_urls' =>     __( 'Yardi image', 'rentfetch' ),
		'unit_source' =>               __( 'Integration', 'rentfetch' ),
		// 'updated' =>                   __( 'Updated', 'rentfetch' ),
		// 'api_error' =>                 __( 'API reponse', 'rentfetch' ),
		'api_response' =>               __( 'API response', 'rentfetch' ),
	);
	
	return $columns;
	
}
add_filter( 'manage_units_posts_columns', 'rentfetch_default_units_admin_columns' );

function rentfetch_units_default_column_content( $column, $post_id ) {
		
	if ( 'title' === $column )
		echo esc_attr( get_the_title( $post_id ) );
		
	if ( 'unit_id' === $column )
		echo esc_attr( get_post_meta( $post_id, 'unit_id', true ) );

	if ( 'floorplan_id' === $column )
		echo esc_attr( get_post_meta( $post_id, 'floorplan_id', true ) );

	if ( 'property_id' === $column )
		echo esc_attr( get_post_meta( $post_id, 'property_id', true ) );

	if ( 'apply_online_url' === $column )
		echo esc_attr( get_post_meta( $post_id, 'apply_online_url', true ) );

	if ( 'availability_date' === $column )
		echo esc_attr( get_post_meta( $post_id, 'availability_date', true ) );

	if ( 'baths' === $column )
		echo esc_attr( get_post_meta( $post_id, 'baths', true ) );

	if ( 'beds' === $column )
		echo esc_attr( get_post_meta( $post_id, 'beds', true ) );

	if ( 'deposit' === $column )
		echo esc_attr( get_post_meta( $post_id, 'deposit', true ) );

	if ( 'minimum_rent' === $column )
		echo esc_attr( get_post_meta( $post_id, 'minimum_rent', true ) );

	if ( 'maximum_rent' === $column )
		echo esc_attr( get_post_meta( $post_id, 'maximum_rent', true ) );

	if ( 'sqrft' === $column )
		echo esc_attr( get_post_meta( $post_id, 'sqrft', true ) );

	if ( 'specials' === $column )
		echo esc_attr( get_post_meta( $post_id, 'specials', true ) );

	if ( 'yardi_unit_image_urls' === $column ) {
		
		$yardi_unit_image_urls = get_post_meta( $post_id, 'yardi_unit_image_urls', true );
		
		// escape the array of image urls
		if ( is_array( $yardi_unit_image_urls ) ) {
			$unit_image_urls = array();

			foreach ($yardi_unit_image_urls as $url) {
				$unit_image_urls[] = esc_url($url);
			}
		}
		
		foreach( $unit_image_urls as $url ) {
			if ( $url ) {
				
				$url = explode( ',', $url );
				
				foreach( $url as $imgurl ) {
					printf( '<div style="border: 1px solid gray; margin: 2px; background-image: url(\'%s\'); background-size: cover; background-position: center center; height: 35px; width: 35px; overflow: hidden; position: relative; display: inline-block;"></div>', $imgurl );
				}
					
				
			}
				
		}
		
	}

	if ( 'unit_source' === $column )
		echo esc_attr( get_post_meta( $post_id, 'unit_source', true ) );

	if ( 'updated' === $column )
		echo esc_attr( get_post_meta( $post_id, 'updated', true ) );

	if ( 'api_error' === $column )
		echo esc_attr( get_post_meta( $post_id, 'api_error', true ) );
		
	if ( 'api_response' === $column ) {
		$api_response = get_post_meta( $post_id, 'api_response', true );
				
		if ( !is_array( $api_response ) )
			$api_response = [];
			
		echo '<div class="api-responses">';
		
		foreach( $api_response as $key => $value ) {
			
			echo '<div class="api-response">';
			
				printf( '<strong>%s:</strong><br/>', esc_attr( $key ) );
				
				foreach ( $value as $subkey => $subvalue ) {
					printf( '%s: %s<br/>', esc_attr( $subkey ), esc_attr( $subvalue ) );
				}
				
			echo '</div>';
		}   
		
		echo '</div>';
		
	}
	
}
add_action( 'manage_units_posts_custom_column', 'rentfetch_units_default_column_content', 10, 2);
