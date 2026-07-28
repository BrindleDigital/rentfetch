<?php
/**
 * Properties location metabox callback
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_location_metabox_callback( $post ) {
	$array_disabled_fields = apply_filters( 'rentfetch_filter_property_syncing_fields', array(), $post->ID );
	?>
	<div class="rf-metabox rf-metabox-properties rf-property-location-metabox">
		
		<div class="columns columns-4 rf-location-address-fields">
		
			<?php
			// * Property Address
			$address  = get_post_meta( $post->ID, 'address', true );
			$disabled = in_array( 'address', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="address">Address</label>
				</div>
				<div class="column">
					<input <?php echo esc_attr( $disabled ); ?> type="text" id="address" name="address" value="<?php echo esc_attr( $address ); ?>">
				</div>
			</div>
			
			<?php
			// * Property City
			$city     = get_post_meta( $post->ID, 'city', true );
			$disabled = in_array( 'city', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="city">City</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="city" name="city" value="<?php echo esc_attr( $city ); ?>">
				</div>
			</div>
			
			<?php
			// * Property State
			$state    = get_post_meta( $post->ID, 'state', true );
			$disabled = in_array( 'state', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="state">State</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="state" name="state" value="<?php echo esc_attr( $state ); ?>">
				</div>
			</div>
			
			<?php
			// * Property Zipcode
			$zipcode  = get_post_meta( $post->ID, 'zipcode', true );
			$disabled = in_array( 'zipcode', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="zipcode">Zipcode</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="zipcode" name="zipcode" value="<?php echo esc_attr( $zipcode ); ?>">
				</div>
			</div>
		
		</div>
		
		<div class="columns columns-2 rf-location-coordinate-fields">
		
			<?php
			// * Property Latitude
			$latitude = get_post_meta( $post->ID, 'latitude', true );
			$disabled = in_array( 'latitude', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="latitude">Latitude</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="latitude" name="latitude" value="<?php echo esc_attr( $latitude ); ?>">
				</div>
			</div>
			
			<?php
			// * Property Longitude
			$longitude = get_post_meta( $post->ID, 'longitude', true );
			$disabled  = in_array( 'longitude', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="longitude">Longitude</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="longitude" name="longitude" value="<?php echo esc_attr( $longitude ); ?>">
				</div>
			</div>
			
		</div>
	   
	</div>
	<?php
}
