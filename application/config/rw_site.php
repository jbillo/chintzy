<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config["site"] = array(
    /**
    single_page_mode
    boolean
    Determines whether the root of your site displays a single page.
    When true, the root will show page with ID = 1.
    When false, the root displays the most recent posts (like a blog).
    */
    "single_page_mode" => TRUE,
    
    /**
    posts_per_page
    integer
    Number of posts displayed on one page before pagination kicks in.
    Higher numbers will cause longer load times and larger cached files,
    but lower numbers mean fewer posts per page.
    */
    "posts_per_page" => 10,
);