<?php

//* Load WordPress so that we get access to those functions
$path =  $_POST['path']; // because the yardi-form-proxy.php file doesn't have access to the wp-load.php URL (and some hosts change that), we're passing that location into this file.
// require_once(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/wp-load.php');
require_once( $path );

// //* Google reCAPTCHA (we're validating this on the frontend and removed for now)
// $google_recaptcha = get_option( 'rentfetch_options_google_recaptcha' );
// $google_recaptcha_v2_site_key = $google_recaptcha['google_recaptcha_v2_site_key'];
// $google_recaptcha_v2_secret = $google_recaptcha['google_recaptcha_v2_secret'];

// if ( isset( $_POST['captcha'] ) )
// 	$captcha_response = $_POST['captcha'];
	
// $file = sprintf( 'https://www.google.com/recaptcha/api/siteverify?secret=%s&captcha_response=%s', $google_recaptcha_v2_secret, $captcha_response );

// $verify = file_get_contents( $file );
// $captcha_success = json_decode( $verify );

// if ($captcha_success->success == false) {
// 	echo 'Captcha not successful.';
// 	echo $captcha_success->error-codes;
// } elseif ( $captcha_success->success == true) {
// 	echo 'Captcha successful.';
// 	echo $captcha_success->success;
// }

//* We know the URL already
$url = 'https://api.rentcafe.com/rentcafeapi.aspx?requestType=lead';

//* Get the variables from the POST request
if ( isset( $_POST['FirstName'] ) )
	$FirstName = urlencode( htmlspecialchars( $_POST['FirstName'] ) );
	
if ( isset( $_POST['LastName'] ) )
	$LastName = urlencode( htmlspecialchars( $_POST['LastName'] ) );

if ( isset( $_POST['Email'] ) )
	$Email = urlencode( htmlspecialchars( $_POST['Email'] ) ); 
	
if ( isset( $_POST['Phone'] ) )
	$Phone = urlencode( htmlspecialchars( $_POST['Phone'] ) );
	
if ( isset( $_POST['Message'] ) )
	$Message = urlencode( htmlspecialchars( $_POST['Message'] ) );
	
if ( isset( $_POST['PropertyCode'] ) )
	$propertycode = urlencode( htmlspecialchars( $_POST['PropertyCode'] ) );
	
if ( isset( $_POST['Source'] ) )
	$source = urlencode( htmlspecialchars( $_POST['Source'] ) );
	
//* Get private information
$yardi_integration_creds = get_option( 'rentfetch_options_yardi_integration_creds' );
$username = $yardi_integration_creds['yardi_username'];
$password = $yardi_integration_creds['yardi_password'];

//* Add the variables to the URL
if ( $FirstName )
	$url = $url . '&firstName=' . $FirstName;
	
if ( $LastName )
	$url = $url . '&lastName=' . $LastName;
	
if ( $Email )
	$url = $url . '&email=' . $Email;
	
if ( $Phone )
	$url = $url . '&phone=' . $Phone;
	
if ( $Message )
	$url = $url . '&message=' . $Message;
	
if ( $propertycode )
	$url = $url . '&propertycode=' . $propertycode;
	
if ( $username )
	$url = $url . '&username=' . $username;
	
if ( $password )
	$url = $url . '&password=' . $password;
	
if ( $source )
	$url = $url . '&source=' . $source;
	
$curl = curl_init();
	
//* Set up CURL
curl_setopt_array($curl, array(
	CURLOPT_URL => $url,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_HTTPHEADER => array(  
		'Content-Type: application/json',  
		'Content-Length: 0',
	),
));

//* Execute
$response = curl_exec($curl);

curl_close($curl);

//* Return the response
echo $response;
