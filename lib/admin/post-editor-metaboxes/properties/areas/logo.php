<?php
/**
 * Property editor logo field.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render the property logo picker using the WordPress media library.
 *
 * @param WP_Post $post Current property.
 */
function rentfetch_properties_logo_metabox_callback( $post ) {
	$logo_id = rentfetch_get_property_logo_id( $post->ID );
	wp_nonce_field( 'rentfetch_property_logo', 'rentfetch_property_logo_nonce' );
	wp_enqueue_media();
	wp_enqueue_script(
		'rentfetch-property-logo',
		RENTFETCH_PATH . 'js/property-logo.js',
		array( 'media-views' ),
		RENTFETCH_VERSION,
		true
	);
	?>
	<div class="rf-metabox rf-metabox-properties" id="rentfetch-property-logo">
		<div class="field">
			<div class="column">
				<strong><?php esc_html_e( 'Logo', 'rentfetch' ); ?></strong>
			</div>
			<div class="column">
				<p class="description" id="rentfetch-property-logo-description"><?php esc_html_e( 'Select a logo for this property. This is managed manually and is not synced.', 'rentfetch' ); ?></p>
				<input type="hidden" id="rentfetch-property-logo-id" name="rentfetch_property_logo_id" value="<?php echo esc_attr( $logo_id ); ?>">
				<div id="rentfetch-property-logo-preview" aria-live="polite">
					<?php
					if ( $logo_id ) {
						echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'style' => 'max-width:200px;max-height:150px;width:auto;height:auto;' ) );
					}
					?>
				</div>
				<button type="button" class="button" id="rentfetch-property-logo-select" aria-describedby="rentfetch-property-logo-description" data-title="<?php esc_attr_e( 'Select Property Logo', 'rentfetch' ); ?>" data-select="<?php esc_attr_e( 'Select Logo', 'rentfetch' ); ?>" data-replace="<?php esc_attr_e( 'Replace Logo', 'rentfetch' ); ?>"><?php echo $logo_id ? esc_html__( 'Replace Logo', 'rentfetch' ) : esc_html__( 'Select Logo', 'rentfetch' ); ?></button>
				<button type="button" class="button" id="rentfetch-property-logo-remove" style="<?php echo $logo_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove Logo', 'rentfetch' ); ?></button>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Save the manually managed property logo for an authorized editor.
 *
 * @param int $post_id WordPress property post ID.
 * @return void
 */
function rentfetch_save_property_logo( $post_id ) {
	if (
		'properties' !== get_post_type( $post_id ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		wp_is_post_revision( $post_id ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	if (
		isset( $_POST['rentfetch_property_logo_nonce'], $_POST['rentfetch_property_logo_id'] ) &&
		is_string( $_POST['rentfetch_property_logo_nonce'] ) &&
		is_string( $_POST['rentfetch_property_logo_id'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rentfetch_property_logo_nonce'] ) ), 'rentfetch_property_logo' )
	) {
		$logo_id = filter_var( wp_unslash( $_POST['rentfetch_property_logo_id'] ), FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 0 ) ) );
		if ( 0 === $logo_id ) {
			delete_post_meta( $post_id, 'property_logo_id' );
			delete_post_meta( $post_id, '_land_co_property_logo_id' );
		} elseif ( false !== $logo_id && wp_attachment_is_image( $logo_id ) ) {
			update_post_meta( $post_id, 'property_logo_id', $logo_id );
			delete_post_meta( $post_id, '_land_co_property_logo_id' );
		}
	}
}
add_action( 'save_post_properties', 'rentfetch_save_property_logo' );
