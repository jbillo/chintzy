<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This file contains an example array of site settings that can
// be used to construct your own configuration. By default, the 
// administration panel will update and overwrite rw_site.php
// for basic site configuration.

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
    
    /**
    static_asset_url
    string
    URL pointing to static assets (JavaScript, CSS) - basically anything
    that could be served by a webserver that doesn't interpret PHP. Hey, we're already
    caching your content, we might as well squeeze some more performance
    out of your files.
    */
    "static_asset_url" => "static/",
    
    /**
    global_caching
    boolean
    Determines whether the main feature of Chintzy (page caching) is turned on or not.
    The only reasons you'd really want to turn it off would be benchmarking or dev.
    */
    "global_caching" => TRUE,
    
    /**
    site_title
    string
    Default site title. Shows up in a few places, like the title tag for pages.
    */
    "site_title" => "ChintzyCMS",            
);