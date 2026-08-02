<?php
/**
 * Property editor API response diagnostics.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render the API Response metabox. Displays structured api_response post meta when present.
 *
 * @param WP_Post $post The post object.
 * @return void
 */
function rentfetch_properties_api_response_metabox_callback( $post ) {
	$api_response = get_post_meta( $post->ID, 'api_response', true );

	if ( ! is_array( $api_response ) ) {
		$api_response = array();
	}

	echo '<div class="rf-metabox rf-metabox-api-response">';

	foreach ( $api_response as $key => $value ) {
		echo '<div class="api-response">';
		printf( '<h3 style="margin-top: 0;">%s</h3>', esc_html( $key ) );

		if ( is_array( $value ) ) {
			foreach ( $value as $subkey => $subvalue ) {
				if ( 'api_response' === $subkey ) {
					// Use the shared JSON utility to pretty-print or lightly repair the value.
					$formatted = rentfetch_pretty_json( $subvalue, $repaired );

					echo '<div class="json-content">';
					// Always display in code editor for syntax highlighting.
					printf( '<textarea class="rentfetch-api-response-json" readonly rows="20" style="width:100%%; white-space: pre; word-wrap: normal; overflow-x: auto;">%s</textarea>', esc_textarea( $formatted ) );
					if ( $repaired ) {
						echo '<p style="color: #856404; background: #fff3cd; padding: 5px; border: 1px solid #ffeaa7; margin-top: 5px;">Note: This JSON was automatically repaired for display.</p>';
					}
					echo '</div>';
				} else {
					printf( '<p>%s</p>', esc_html( $subvalue ) );
				}
			}
		} else {
			echo esc_html( $value );
		}

		echo '</div>';
	}

	echo '</div>';
}
