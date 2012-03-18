<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function akismet_check($name, $email, $url, $comment, $slug) {
    $CI =& get_instance();
    $akismet = $CI->config->item("akismet", "site");

    if (!$akismet["enabled"]) {
        // Approve comments automatically if Akismet is not in the chain
        return "approved";
    }

    if (!isset($akismet_class)) {
        $CI->load->file($akismet["path"]);
        $CI->load->helper("url");
        $akismet_class = new Akismet(site_url(), $akismet["api_key"]);
    }

    $akismet_class->setCommentAuthor($name);
    $akismet_class->setCommentAuthorEmail($email);
    $akismet_class->setCommentAuthorURL($url);
    $akismet_class->setCommentContent($comment);
    $akismet_class->setPermalink(site_url($slug));

    if ($akismet_class->isCommentSpam()) {
        return "spam";
    }

    return "approved";
}