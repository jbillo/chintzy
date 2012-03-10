<?php
$BASE = dirname(__FILE__);
$ROOT = dirname($_SERVER['SCRIPT_NAME']);
if (substr($ROOT, -1) != '/') {
    $ROOT .= "/";
}
 
$HOSTNAME = $_SERVER['SERVER_NAME'];
$port = $_SERVER['SERVER_PORT'];
if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']) {
    $protocol = "https";
} else {
    $protocol = "http";
}

$ROOTURI = "$protocol://$HOSTNAME$ROOT";
$ROOTURI_NOSSL = "http://$HOSTNAME$ROOT";
$ROOTURI_SSL = "https://$HOSTNAME$ROOT";
$GLOBALS['ssl'] = ($protocol == 'https');

/**
 * Database and site settings
 */
if (file_exists("{$BASE}/db.inc.php")) {
    include_once "{$BASE}/db.inc.php";
}
include_once "./settings.inc.php";

/**
 * Permission constants (bitmask)
 */
define('PERM_ROOT',            1);
define('PERM_ADD',             2);
define('PERM_EDIT',            4);
define('PERM_DELETE',          8);
define('PERM_COMMENT_EDIT',   16);
define('PERM_COMMENT_DELETE', 32);
// Any permissions under the value of PERM_INSECURE will require HTTPS access to the site.
define('PERM_INSECURE',     1024);
define('PERM_EXAMPLE',      2048);

/**
 * Salt for account passwords
 */
include_once './salt.inc.php';
define('SITE_SALT', $salt);
$salt = '';

/**
 * Internal constants
 */
define('SID', 'chintzy');
define('MAX_CACHE_FILESIZE', 1024*1024*2); // 2M
define('GENERATOR', "ChintzyCMS");

/**
 * URL reserved slugs and actions
 */
$VALID_ACTIONS = array('login', 'logout', 'admin', 'edit', 'rss', 'install', 'comment', 'module', 'account');
$RESERVED_SLUGS = array('view', 'add-post', 'edit-post', 'delete', 'delete-confirm');

/**
 * Error constants
 */
define('ERROR_NO_HASH_FUNCTION', 'Please install or enable the HASH Message Digest Framework PHP extension to continue.');
define('ERROR_LOAD_CLASS', 'One or more class files could not be loaded. Please check the includes/classes directory to make sure it exists. If it does not exist, you may need to reinstall this software. The class file is: ');
define('ERROR_NO_TOKEN', 'This action requires a valid security token. Please enable JavaScript if it is disabled.');

/**
 * Custom confirmation messages that appear in the ?c_msg=X GET parameter
 */
$GLOBALS['c_msg'] = array(
    'logged_in'                 => 'You have successfully logged in.',
    'logged_out'                => 'You have successfully logged out.',
    'already_logged_out'        => 'You are already logged out.',
    'already_logged_in'         => 'You are already logged in.',
    'post_saved'                => 'Post successfully saved.',
    'post_deleted'              => 'Post and all attached comments successfully deleted.',
    'settings_saved'            => 'Site settings successfully saved.',
    'user_saved'                => 'User account successfully saved.',
    'role_saved'                => 'Role successfully saved.',
    'cache_cleared'             => 'Site cache successfully cleared.',
    'recaptcha_invalid'         => 'Could not validate the verification challenge response. Please try again.',
    'comment_saved'             => 'Comment successfully saved.',
    'comment_deleted'           => 'Comment successfully deleted.',
    'rebuild_htaccess_success'  => '.htaccess rebuilt successfully.',
    'rebuild_htaccess_failure'  => 'Could not rebuild .htaccess. Please view the site status in the admin panel.',
);

/**
 * Special directories
 */
$GLOBALS['dir']['cache'] = "$BASE/cache/";
$GLOBALS['dir']['template'] = "$BASE/templates/";
$GLOBALS['dir']['theme'] = "$BASE/themes/";
$GLOBALS['dir']['log'] = "$BASE/log/";
$GLOBALS['dir']['modules'] = "$BASE/modules/";
$GLOBALS['dir']['content'] = "$BASE/content/";
