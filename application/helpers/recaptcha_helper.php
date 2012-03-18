<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function recaptcha_html() {
    $CI =& get_instance();
    $recaptcha = $CI->config->item("recaptcha", "site");
    if ($recaptcha["enabled"] and $recaptcha["public_key"]) {
        if (!function_exists("recaptcha_get_html")) {
            $CI->load->file($recaptcha["path"]);
        }
        return recaptcha_get_html($recaptcha["public_key"]);
	}

	return NULL;
}

function recaptcha_check($challenge_field, $response_field) {
    $CI =& get_instance();
    $recaptcha = $CI->config->item("recaptcha", "site");

    if (!$recaptcha["enabled"] or !$recaptcha["private_key"]) {
        // Doesn't matter. Return true regardless since reCAPTCHA is disabled.
        return TRUE;
    }

    if (!function_exists("recaptcha_check_answer")) {
        $CI->load->file($recaptcha["path"]);
    }

    $resp = recaptcha_check_answer(
        $recaptcha["private_key"],
        $_SERVER['REMOTE_ADDR'],
        $challenge_field,
        $response_field
    );

    return $resp->is_valid;
}