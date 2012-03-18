<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function recaptcha_html() {
    $CI =& get_instance();
    $recaptcha = $CI->config->item("recaptcha", "site");
    if ($recaptcha["enabled"] and $recaptcha["public_key"]) {
        $CI->load->file($recaptcha["path"]);
        return recaptcha_get_html($recaptcha["public_key"]);
	}

	return NULL;
}