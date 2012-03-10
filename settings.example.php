<?php
/**
 * Site configuration and settings
 */
$GLOBALS['site'] = array(
    'debug'         => false,                                       // Certain debugging messages enabled
    'minify'        => true,                                        // Minify HTML output to save bandwidth
    'caching'       => true,                                        // Cache page output
    'title'         => 'ChintzyCMS',                                // Site title
    'description'   => 'Content management system',                 // Site description (RSS)
    'language'      => 'en-us',                                     // Site language (RSS)
    'editor'        => 'editor@example.com',                        // Editor (RSS)
    'webmaster'     => 'webmaster@example.com',                     // Webmaster (RSS)
    'recaptcha'		=> array(
                        'public_key'		=> '6LfPiwkAAAAAAPLwO3dybkB5rIKWOU4Ro4cf_9RI',
                        'private_key'		=> '6LfPiwkAAAAAAIi4d8ij61dPYpc7hxvdb7QOWgTo',
    ),
    'view_settings' => array(
                        'posts_per_page'    => 10,                  // Number of posts to show per page
                        'comments_per_page' => 25,                  // Number of comments to show per page
                        'date_format'       => 'Y-m-d',             // Date format for posts and pages
                        'time_format'       => 'h:i A',             // Time format for posts and pages
                        'homepage_blog'     => false,               // Whether to show post ID=1 or blogs on the home page
                        'theme'             => 'default'),
    'security'      => array(
                        'min_pw_length'     => 10)                  // Minimum password length for administration accounts
);