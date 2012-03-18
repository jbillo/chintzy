<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config["site"] = array(
    /**
    single_page_mode
    boolean
    Determines whether the root of your site displays a single page.
    When true, the root will show page with ID = 1.
    When false, the root displays the most recent posts (like a blog).
    */
    "single_page_mode" => FALSE,

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
    "static_asset_url" => "/static/",

    /**
    global_caching
    boolean
    Determines whether the main feature of Chintzy (page caching) is turned on or not.
    The only reasons you'd really want to turn it off would be benchmarking or dev.
    After you change this setting, remove all .html files from the application/cache
    directory in order to make sure that the aggressive nonsense is taken care of.
    */
    "global_caching" => TRUE,

    /**
    site_title
    string
    Default site title. Shows up in a few places, like the title tag for pages.
    */
    "site_title" => "ChintzyCMS",

    /**
    title_separator
    string
    Separator between title elements, eg: |, >, &raquo;
    */
    "title_separator" => " &raquo; ",

    /**
    date_format
    string
    Standard PHP date formatting
    */
    "date_format" => "%Y-%m-%d %H:%M",

    /**
    recaptcha
    array
    Settings for reCAPTCHA (http://www.google.com/recaptcha/whyrecaptcha)
    */
    "recaptcha" => array(
        "enabled" => TRUE,
        "path" => APPPATH . "third_party/recaptcha-php-1.11/recaptchalib.php",
        "public_key" => "6LdeFM8SAAAAAKKgNvqM7JzEBT1ZsvXS_azsY1bc",
        "private_key" => "6LdeFM8SAAAAAFcKZDRf_RvEgXO5-IAYDGJh_ow-",
    ),
);