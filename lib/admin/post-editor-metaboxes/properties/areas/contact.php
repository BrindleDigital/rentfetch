<?php
/**
 * Property editor contact fields.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Properties contact metabox callback
 *
 * @param object $post The post object.
 *
 * @return void.
 */
function rentfetch_properties_contact_metabox_callback( $post ) {
	$array_disabled_fields = apply_filters( 'rentfetch_filter_property_syncing_fields', array(), $post->ID );
	?>
	<div class="rf-metabox rf-metabox-properties rf-property-contact-metabox">
		
		<div class="columns">
			
			<?php
			// * Property Email
			$email    = get_post_meta( $post->ID, 'email', true );
			$disabled = in_array( 'email', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="email">Email</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="email" name="email" value="<?php echo esc_attr( $email ); ?>">
				</div>
			</div>

		</div>

		<div class="columns columns-2 rf-contact-primary-fields">
			
			<?php
			// * Property Phone
			$phone    = get_post_meta( $post->ID, 'phone', true );
			$disabled = in_array( 'phone', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="phone">Phone</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="phone" name="phone" value="<?php echo esc_attr( $phone ); ?>">
				</div>
			</div>

			<?php
			// * Property Phone Override
			$phone_override = get_post_meta( $post->ID, 'phone_override', true );
			?>
			<div class="field">
				<div class="column">
					<label for="phone_override">Phone override</label>
				</div>
				<div class="column">
					<input type="text" id="phone_override" name="phone_override" value="<?php echo esc_attr( $phone_override ); ?>">
					<p class="description">Override the synced phone number here.</p>
				</div>
			</div>
			
		</div>
		
		<div class="columns columns-2 rf-contact-link-fields">
			
			<?php
			// * Property URL
			$url      = get_post_meta( $post->ID, 'url', true );
			$disabled = in_array( 'url', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="url">URL</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="url" name="url" value="<?php echo esc_attr( $url ); ?>">
				</div>
			</div>
			
			<?php
			// * Property URL
			$url      = get_post_meta( $post->ID, 'url_override', true );
			$disabled = in_array( 'url_override', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="url">URL override</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="url_override" name="url_override" value="<?php echo esc_attr( $url ); ?>">
					<p class="description">Override the synced URL here.</p>
				</div>
			</div>

		</div>

		<div class="columns">

			<?php
			// * Resident Portal Link
			$resident_portal_url = get_post_meta( $post->ID, 'resident_portal_url', true );
			?>
			<div class="field">
				<div class="column">
					<label for="resident_portal_url">Resident Portal Link</label>
				</div>
				<div class="column">
					<input type="text" id="resident_portal_url" name="resident_portal_url" value="<?php echo esc_attr( $resident_portal_url ); ?>">
				</div>
			</div>
			
			<?php
			// * Tour Booking Link
			$tour_booking_link = get_post_meta( $post->ID, 'tour_booking_link', true );
			$disabled          = in_array( 'tour_booking_link', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="tour_booking_link">Tour Booking Link</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="tour_booking_link" name="tour_booking_link" value="<?php echo esc_attr( $tour_booking_link ); ?>">
				</div>
			</div>

			<?php
			// * Apply Online Link
			$apply_online_url = get_post_meta( $post->ID, 'apply_online_url', true );
			$disabled         = in_array( 'apply_online_url', $array_disabled_fields, true ) ? 'disabled' : '';
			?>
			<div class="field">
				<div class="column">
					<label for="apply_online_url">Apply Online Link</label>
				</div>
				<div class="column">
					<input type="text" <?php echo esc_attr( $disabled ); ?> id="apply_online_url" name="apply_online_url" value="<?php echo esc_attr( $apply_online_url ); ?>">
				</div>
			</div>

		</div>
		
	</div>
	<?php
}
