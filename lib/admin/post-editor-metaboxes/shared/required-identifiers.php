<?php
/**
 * Manual-record identifier validation.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Require relationship identifiers when an admin manually saves a record.
 *
 * Sync-created records do not submit these editor nonces and are unaffected.
 * Browser validation handles the normal UI path; this is the server safeguard
 * for bypassed or scripted admin submissions.
 *
 * @param int                  $post_id         Current post ID.
 * @param string               $source_key      Source meta/input key.
 * @param array<string,string> $fields          Required input names and labels.
 * @param array<string,mixed>  $submitted_values Sanitized values submitted by the verified editor.
 * @return bool Whether all required identifiers are present.
 */
function rentfetch_validate_manual_record_identifiers( $post_id, $source_key, $fields, $submitted_values = array() ) {
	static $enforcing = false;

	if ( $enforcing ) {
		return false;
	}

	$source = isset( $submitted_values[ $source_key ] )
		? sanitize_key( $submitted_values[ $source_key ] )
		: sanitize_key( get_post_meta( $post_id, $source_key, true ) );

	if ( '' !== $source ) {
		return true;
	}

	$missing = array();
	foreach ( $fields as $field => $label ) {
		$value = isset( $submitted_values[ $field ] )
			? sanitize_text_field( $submitted_values[ $field ] )
			: (string) get_post_meta( $post_id, $field, true );

		if ( '' === trim( $value ) ) {
			$missing[] = $label;
		}
	}

	if ( empty( $missing ) ) {
		return true;
	}

	$missing_value = implode( ', ', $missing );
	add_filter(
		'redirect_post_location',
		static function ( $location ) use ( $missing_value ) {
			return add_query_arg( 'rentfetch_missing_identifiers', rawurlencode( $missing_value ), $location );
		}
	);

	$post_status = get_post_status( $post_id );
	if ( in_array( $post_status, array( 'publish', 'future', 'private' ), true ) ) {
		$enforcing = true;
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'draft',
			)
		);
		$enforcing = false;
	}

	return false;
}

/**
 * Explain why a manual record was kept as a draft.
 *
 * @return void
 */
function rentfetch_manual_identifier_validation_notice() {
	$missing = filter_input( INPUT_GET, 'rentfetch_missing_identifiers', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	if ( empty( $missing ) ) {
		return;
	}

	$missing = sanitize_text_field( $missing );
	?>
	<div class="notice notice-error">
		<p><?php echo esc_html( sprintf( 'This manual record was not published. Complete the required identifier fields: %s.', $missing ) ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'rentfetch_manual_identifier_validation_notice' );
