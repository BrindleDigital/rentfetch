<?php
/**
 * AJAX handlers and utilities for property fees CSV processing
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Validate a CSV URL and return validation results
 *
 * @param string $url The URL to validate.
 * @return array Validation result array with 'success', 'data' or 'error' keys.
 */
function rentfetch_validate_fees_csv_url_internal( $url ) {
	// Expected columns for property fees CSV
	$expected_columns = array( 'description', 'price', 'frequency', 'notes', 'category', 'longnotes' );
	$required_columns = array( 'description' ); // Only description is truly required

	// Fetch the CSV file
	$response = wp_remote_get( $url, array(
		'timeout'   => 15,
		'sslverify' => false, // Allow self-signed certs for local development
	) );

	if ( is_wp_error( $response ) ) {
		return array(
			'success' => false,
			'error'   => array(
				'message' => 'Could not fetch CSV file: ' . $response->get_error_message(),
				'type'    => 'fetch_error',
			),
		);
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	if ( $response_code !== 200 ) {
		return array(
			'success' => false,
			'error'   => array(
				'message' => 'CSV file returned HTTP ' . $response_code . ' error',
				'type'    => 'http_error',
			),
		);
	}

	$body = wp_remote_retrieve_body( $response );

	if ( empty( $body ) ) {
		return array(
			'success' => false,
			'error'   => array(
				'message' => 'CSV file is empty',
				'type'    => 'empty_file',
			),
		);
	}

	// Parse the CSV header
	$lines = preg_split( '/\r\n|\r|\n/', $body );

	if ( empty( $lines ) || empty( $lines[0] ) ) {
		return array(
			'success' => false,
			'error'   => array(
				'message' => 'Could not read CSV header row',
				'type'    => 'parse_error',
			),
		);
	}

	// Parse the header row
	$header_row       = str_getcsv( $lines[0] );
	$header_row       = array_map( 'trim', $header_row );
	$header_row_lower = array_map( 'strtolower', $header_row );

	// Count empty columns
	$empty_column_count = 0;
	$non_empty_columns  = array();
	foreach ( $header_row_lower as $col ) {
		if ( $col === '' ) {
			$empty_column_count++;
		} else {
			$non_empty_columns[] = $col;
		}
	}

	// Find missing required columns (check against non-empty columns only)
	$missing_required = array_diff( $required_columns, $non_empty_columns );

	// Find missing optional columns
	$missing_optional = array_diff( $expected_columns, $non_empty_columns );
	$missing_optional = array_diff( $missing_optional, $required_columns ); // Remove required from optional

	// Find extra columns (not in expected list, and not empty)
	$extra_columns = array_diff( $non_empty_columns, $expected_columns );

	// Count data rows (excluding header)
	$data_row_count = 0;
	for ( $i = 1; $i < count( $lines ); $i++ ) {
		if ( ! empty( trim( $lines[ $i ] ) ) ) {
			$data_row_count++;
		}
	}

	// Build validation result
	$validation_result = array(
		'valid'              => empty( $missing_required ),
		'found_columns'      => $non_empty_columns,
		'empty_column_count' => $empty_column_count,
		'expected_columns'   => $expected_columns,
		'missing_required'   => array_values( $missing_required ),
		'missing_optional'   => array_values( $missing_optional ),
		'extra_columns'      => array_values( $extra_columns ),
		'row_count'          => $data_row_count,
	);

	// Generate human-readable messages
	$messages = array();
	$warnings = array();

	if ( ! empty( $missing_required ) ) {
		$messages[] = 'Missing required column(s): ' . implode( ', ', $missing_required );
	}

	if ( ! empty( $missing_optional ) ) {
		$warnings[] = 'Missing optional column(s): ' . implode( ', ', $missing_optional ) . ' (these will be empty)';
	}

	if ( ! empty( $extra_columns ) ) {
		$warnings[] = 'Extra column(s) found: ' . implode( ', ', $extra_columns ) . ' (these will be ignored)';
	}

	if ( $data_row_count === 0 ) {
		$messages[]                   = 'CSV file contains no data rows';
		$validation_result['valid'] = false;
	}

	$validation_result['messages'] = $messages;
	$validation_result['warnings'] = $warnings;

	if ( $validation_result['valid'] ) {
		$success_msg = 'CSV is valid with ' . $data_row_count . ' fee row(s)';
		if ( ! empty( $warnings ) ) {
			$success_msg .= ' (with warnings)';
		}
		$validation_result['success_message'] = $success_msg;
	}

	return array(
		'success' => true,
		'data'    => $validation_result,
	);
}

/**
 * AJAX handler to validate a CSV URL for property fees
 */
function rentfetch_validate_fees_csv_url() {
	// Verify nonce for security
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rentfetch_validate_csv_url' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed' ) );
	}

	// Get the URL
	$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';

	if ( empty( $url ) ) {
		wp_send_json_error( array( 'message' => 'No URL provided' ) );
	}

	$result = rentfetch_validate_fees_csv_url_internal( $url );

	if ( $result['success'] ) {
		wp_send_json_success( $result['data'] );
	} else {
		wp_send_json_error( $result['error'] );
	}
}
add_action( 'wp_ajax_rentfetch_validate_fees_csv_url', 'rentfetch_validate_fees_csv_url' );

/**
 * AJAX handler to download sample fees CSV
 */
function rentfetch_download_fees_csv_sample() {
	// Set headers for CSV download
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=property_fees_sample.csv' );

	// Create sample CSV content
	$csv_content  = "description,price,frequency,notes,category,longnotes\n";
	$csv_content .= "Application Fee,\$100,,Required,Move-In Basics,\"<h4>Application Fee Details</h4><p>A non-refundable fee required for each applicant over the age of 18. This fee covers the cost of background checks and credit verification.</p><h5>What's Included</h5><ul><li>Criminal background check</li><li>Credit history review</li><li>Employment verification</li><li>Rental history verification</li></ul><p>Please allow 2-3 business days for processing.</p>\"\n";
	$csv_content .= "Administration Fee,\$300,,Required,Move-In Basics,\n";
	$csv_content .= "\"One-Time Access Control Setup\",\$50,,Required,Move-In Basics,\"<p>Includes key fobs and access cards for the building.</p>\"\n";
	$csv_content .= "\"One-Time Pet Fee\",\$350,\"Per \"\"pet\"\" (non-refundable)\",,Move-In Basics,\"<h4>Pet Policy Information</h4><p>This one-time, non-refundable fee is required for each approved pet. Maximum of 2 pets allowed per unit.</p><h5>Approved Pet Types</h5><ul><li>Dogs under 50 lbs (breed restrictions may apply)</li><li>Cats (indoor only)</li><li>Small caged animals (hamsters, guinea pigs, etc.)</li></ul><h5>Required Documentation</h5><ol><li>Current vaccination records</li><li>Pet photo for our records</li><li>Signed pet addendum</li></ol><p><strong>Note:</strong> Emotional support animals and service animals are exempt from pet fees with proper documentation.</p>\"\n";
	$csv_content .= "Trash Fee,\$25,,,Essentials,\n";
	$csv_content .= "Amenity Fee,\$25,,,Essentials,\"<p>Covers access to fitness center, pool, and clubhouse.</p>\"\n";
	$csv_content .= "Internet Access,\$85,,Required,Essentials,\"<h4>High-Speed Internet Package</h4><p>Our community offers premium fiber internet service included in your monthly fees.</p><h5>Service Details</h5><ul><li>Speeds up to 1 Gbps download</li><li>Speeds up to 500 Mbps upload</li><li>No data caps</li><li>Professional installation included</li></ul><p>Service is provided by our partner ISP and is available within 48 hours of move-in.</p>\"\n";
	$csv_content .= "Pest Control,\$5,,Required,Essentials,\n";

	echo $csv_content;
	exit;
}
add_action( 'wp_ajax_rentfetch_download_fees_csv_sample', 'rentfetch_download_fees_csv_sample' );

/**
 * AJAX handler to download sample global fees CSV (alias for consistency)
 */
function rentfetch_download_global_fees_csv_sample() {
	rentfetch_download_fees_csv_sample();
}
add_action( 'wp_ajax_rentfetch_download_global_fees_csv_sample', 'rentfetch_download_global_fees_csv_sample' );

/**
 * AJAX handler to download current fees data as CSV (property-specific)
 */
function rentfetch_download_current_fees_csv() {
	// Verify nonce for security
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rentfetch_properties_metabox_nonce' ) ) {
		wp_die( 'Security check failed' );
	}

	// Get the post ID
	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

	if ( ! $post_id ) {
		wp_die( 'Invalid post ID' );
	}

	// Get current fees data
	$fees_data = get_post_meta( $post_id, 'property_fees_data', true );
	if ( ! is_array( $fees_data ) ) {
		$fees_data = array();
	}

	rentfetch_output_fees_csv( $fees_data, 'property_fees_current.csv' );
}
add_action( 'wp_ajax_rentfetch_download_current_fees_csv', 'rentfetch_download_current_fees_csv' );

/**
 * AJAX handler to download current global fees data as CSV
 */
function rentfetch_download_current_global_fees_csv() {
	// Verify nonce for security
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rentfetch_main_options_nonce_action' ) ) {
		wp_die( 'Security check failed' );
	}

	// Get current global fees data
	$fees_data = get_option( 'rentfetch_options_global_property_fees_data' );
	if ( ! is_array( $fees_data ) ) {
		$fees_data = array();
	}

	rentfetch_output_fees_csv( $fees_data, 'global_property_fees_current.csv' );
}
add_action( 'wp_ajax_rentfetch_download_current_global_fees_csv', 'rentfetch_download_current_global_fees_csv' );

/**
 * Output fees data as CSV download
 *
 * @param array  $fees_data Array of fee data.
 * @param string $filename  The filename for the download.
 */
function rentfetch_output_fees_csv( $fees_data, $filename ) {
	// Set headers for CSV download
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );

	// Create CSV content
	$csv_content = "description,price,frequency,notes,category,longnotes\n";

	foreach ( $fees_data as $fee ) {
		$csv_content .= sprintf(
			"\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
			str_replace( '"', '""', $fee['description'] ?? '' ),
			str_replace( '"', '""', $fee['price'] ?? '' ),
			str_replace( '"', '""', $fee['frequency'] ?? '' ),
			str_replace( '"', '""', $fee['notes'] ?? '' ),
			str_replace( '"', '""', $fee['category'] ?? '' ),
			str_replace( '"', '""', $fee['longnotes'] ?? '' )
		);
	}

	echo $csv_content;
	exit;
}

/**
 * AJAX handler to upload and process fees CSV (property-specific)
 */
function rentfetch_upload_fees_csv() {
	// Verify nonce for security
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rentfetch_properties_metabox_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed' ) );
	}

	// Get the post ID
	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

	if ( ! $post_id ) {
		wp_send_json_error( array( 'message' => 'Invalid post ID' ) );
	}

	// Check if file was uploaded
	if ( ! isset( $_FILES['csv_file'] ) || empty( $_FILES['csv_file']['tmp_name'] ) ) {
		wp_send_json_error( array( 'message' => 'No file uploaded' ) );
	}

	$result = rentfetch_process_uploaded_csv_file( $_FILES['csv_file'] );

	if ( ! $result['success'] ) {
		wp_send_json_error( array( 'message' => $result['message'] ) );
	}

	// Save the processed fees data
	update_post_meta( $post_id, 'property_fees_data', $result['data'] );

	// Return the updated JSON
	wp_send_json_success( array(
		'json'  => wp_json_encode( $result['data'], JSON_PRETTY_PRINT ),
		'count' => count( $result['data'] ),
	) );
}
add_action( 'wp_ajax_rentfetch_upload_fees_csv', 'rentfetch_upload_fees_csv' );

/**
 * AJAX handler to process global fees CSV from uploaded file
 */
function rentfetch_process_global_fees_csv() {
	// Verify nonce for security
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'rentfetch_main_options_nonce_action' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed' ) );
	}

	// Check if file was uploaded
	if ( ! isset( $_FILES['csv_file'] ) || empty( $_FILES['csv_file']['tmp_name'] ) ) {
		wp_send_json_error( array( 'message' => 'No file uploaded' ) );
	}

	$result = rentfetch_process_uploaded_csv_file( $_FILES['csv_file'] );

	if ( ! $result['success'] ) {
		wp_send_json_error( array( 'message' => $result['message'] ) );
	}

	// Save the processed fees data
	update_option( 'rentfetch_options_global_property_fees_data', $result['data'] );

	// Return the updated JSON
	wp_send_json_success( array(
		'json'  => wp_json_encode( $result['data'], JSON_PRETTY_PRINT ),
		'count' => count( $result['data'] ),
	) );
}
add_action( 'wp_ajax_rentfetch_process_global_fees_csv', 'rentfetch_process_global_fees_csv' );

/**
 * Process an uploaded CSV file and return fees data
 *
 * @param array $csv_file The $_FILES array element for the uploaded file.
 * @return array Result with 'success', 'data' or 'message' keys.
 */
function rentfetch_process_uploaded_csv_file( $csv_file ) {
	// Check if it's a valid CSV file
	$file_type = wp_check_filetype( $csv_file['name'] );
	if ( 'csv' !== $file_type['ext'] ) {
		return array(
			'success' => false,
			'message' => 'Invalid file type. Please upload a CSV file.',
		);
	}

	// Process the CSV file
	$fees_data = array();

	if ( ( $handle = fopen( $csv_file['tmp_name'], 'r' ) ) !== false ) {
		$header = fgetcsv( $handle, 1000, ',' );

		// Normalize header
		$header = array_map( function( $col ) {
			return strtolower( trim( $col ) );
		}, $header );

		$expected_columns = array( 'description', 'price', 'frequency', 'notes', 'category', 'longnotes' );

		// Find column indices
		$column_indices = array();
		foreach ( $expected_columns as $col ) {
			$index                    = array_search( $col, $header, true );
			$column_indices[ $col ] = ( $index !== false ) ? $index : -1;
		}

		// Must have at least 'description' column
		if ( $column_indices['description'] === -1 ) {
			fclose( $handle );
			return array(
				'success' => false,
				'message' => 'Invalid CSV format. Missing required column: description',
			);
		}

		while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
			// Get value from column index, or empty string if column doesn't exist
			$get_value = function( $col ) use ( $column_indices, $data ) {
				$index = $column_indices[ $col ];
				if ( $index === -1 || ! isset( $data[ $index ] ) ) {
					return '';
				}
				return sanitize_text_field( $data[ $index ] );
			};

			// Skip rows where description is empty
			$description = $get_value( 'description' );
			if ( empty( $description ) ) {
				continue;
			}

			// Get longnotes value - allow HTML so use wp_kses_post instead of sanitize_text_field
			$longnotes_index = $column_indices['longnotes'];
			$longnotes_value = '';
			if ( $longnotes_index !== -1 && isset( $data[ $longnotes_index ] ) ) {
				$longnotes_value = wp_kses_post( $data[ $longnotes_index ] );
			}

			$fees_data[] = array(
				'description' => $description,
				'price'       => $get_value( 'price' ),
				'frequency'   => $get_value( 'frequency' ),
				'notes'       => $get_value( 'notes' ),
				'category'    => $get_value( 'category' ),
				'longnotes'   => $longnotes_value,
			);
		}

		fclose( $handle );
	} else {
		return array(
			'success' => false,
			'message' => 'Could not read CSV file',
		);
	}

	return array(
		'success' => true,
		'data'    => $fees_data,
	);
}

/**
 * AJAX handler to upload global property fees CSV to media library
 */
function upload_global_property_fees_csv() {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'rentfetch_main_options_nonce_action' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed' ) );
	}
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ) );
	}

	// Allow CSV uploads
	add_filter( 'upload_mimes', function( $mimes ) {
		$mimes['csv'] = 'text/csv';
		return $mimes;
	} );

	if ( empty( $_FILES['file'] ) ) {
		wp_send_json_error( array( 'message' => 'No file provided' ) );
	}
	$file             = $_FILES['file'];
	$upload_overrides = array( 'test_form' => false );
	$movefile         = wp_handle_upload( $file, $upload_overrides );
	if ( $movefile && ! isset( $movefile['error'] ) ) {
		$attachment = array(
			'guid'           => $movefile['url'],
			'post_mime_type' => $movefile['type'],
			'post_title'     => sanitize_file_name( $file['name'] ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);
		$attach_id  = wp_insert_attachment( $attachment, $movefile['file'] );
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		wp_send_json_success( array( 'url' => wp_get_attachment_url( $attach_id ) ) );
	} else {
		wp_send_json_error( array( 'message' => $movefile['error'] ) );
	}
}
add_action( 'wp_ajax_upload_global_property_fees_csv', 'upload_global_property_fees_csv' );

/**
 * AJAX handler to upload property fees CSV to media library
 */
function upload_property_fees_csv() {
	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'upload_property_fees_csv' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed' ) );
	}
	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ) );
	}

	// Allow CSV uploads
	add_filter( 'upload_mimes', function( $mimes ) {
		$mimes['csv'] = 'text/csv';
		return $mimes;
	} );

	if ( empty( $_FILES['file'] ) ) {
		wp_send_json_error( array( 'message' => 'No file provided' ) );
	}
	$file             = $_FILES['file'];
	$upload_overrides = array( 'test_form' => false );
	$movefile         = wp_handle_upload( $file, $upload_overrides );
	if ( $movefile && ! isset( $movefile['error'] ) ) {
		$attachment = array(
			'guid'           => $movefile['url'],
			'post_mime_type' => $movefile['type'],
			'post_title'     => sanitize_file_name( $file['name'] ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);
		$attach_id  = wp_insert_attachment( $attachment, $movefile['file'] );
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		wp_send_json_success( array( 'url' => wp_get_attachment_url( $attach_id ) ) );
	} else {
		wp_send_json_error( array( 'message' => $movefile['error'] ) );
	}
}
add_action( 'wp_ajax_upload_property_fees_csv', 'upload_property_fees_csv' );
