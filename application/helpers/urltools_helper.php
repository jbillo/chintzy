<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function current_url()
{
    $CI =& get_instance();
    return $CI->config->site_url($CI->uri->uri_string());
}

function static_url($path = "") {
    $CI =& get_instance();
    return $CI->config->item("static_asset_url", "site") . $path;
}