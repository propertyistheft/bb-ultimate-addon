<?php
/**
 * Do recaptcha validation here so we can only load for php 5.3 and above.
 * For Valudating Recaptcha.
 *
 *  @package UABB Contact Form Module
 */

?>

<?php

require_once FL_BUILDER_DIR . 'includes/vendor/recaptcha/autoload.php';

$recaptcha = new \ReCaptcha\ReCaptcha( $settings->uabb_recaptcha_secret_key, new \ReCaptcha\RequestMethod\CurlPost() );
$resp      = isset( $_SERVER['REMOTE_ADDR'] ) ? $recaptcha->verify( $recaptcha_response, sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) ) : null; // phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.UserControlledHeaders, WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___SERVER__REMOTE_ADDR__

if ( $resp->isSuccess() ) {
	$response['error'] = false;
} else {
	$response['error']   = true;
	$response['message'] = '<strong>reCAPTCHA Error: </strong>';
	$error_codes         = array();
	foreach ( $resp->getErrorCodes() as $code ) {
		$error_codes[] = $code;
	}
	$response['message'] .= implode( ' | ', $error_codes );
}
