<?php
// Tell PHP to display all errors (assuming debug mode configuration)
if ($GLOBALS['site']['debug']) {
	ini_set('display_errors', '1');
	error_reporting(E_ALL);
}

$core = new ChintzyCore();
require_once("{$BASE}/includes/functions.inc.php");

// Log POST variables in debug mode.
$safe_post = $_POST;

// Specifically, if a field starts with cc_, remove it from any logging.
// This helps prevent accidental credit card disclosure with the integrated
// store module.
foreach ($safe_post as $key => &$val) {
    if (substr($key, 0, 3) == "cc_") {
        $val = "***REMOVED***";
    }
}
l("Init: POST " . var_export($safe_post, TRUE), "DBUG");

// Load the ADODB library.
$core->load_library('adodb');

// Connect to PostgreSQL db via ADODB.
// HACK: For Eclipse to recognize autocomplete options, define the ADOConnection object.
if (false) {
    $db_conn = new ADOConnection();
}

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$ADODB_CACHE_DIR = $GLOBALS['dir']['cache'];

// Validate DB settings and connect if available
if (isset($db_settings) and validate_db_settings($db_settings)) {
    $db_conn = NewADOConnection($db_settings['type']);
    @$db_conn->Connect("host={$db_settings['host']} user={$db_settings['user']} password={$db_settings['password']} dbname={$db_settings['db']}");
} else {
    $db_conn = false;
}

// Nuke database settings so they can't be accessed from a different part of the site.
$db_settings = null;

// Check to see if the database connection was successful.
if (!$db_conn) {
    l("Could not connect to the database: host [{$db_settings['host']}] user [{$db_settings['user']}]", "ERRR");
}

// For cached queries, store results for 15 minutes (60*15).
$db_conn->cacheSecs = 900;

// Create database helper object
$db = new DBHelper($db_conn);
$dbq = new DBHelper_ChintzyQueries();	

// Require the user class if we're in an administrative section.
// This has to come before the template engine so caching can be disabled appropriately.
$user = new User();

// Require the template engine.
// Don't even load the database engine if this page is already cached.
$t = new ChintzTemplateEngine();

// Check if hash() extension is enabled. (Ran into this on Gentoo's stock configuration)
if (!function_exists('hash')) {
    error_and_bail(ERROR_NO_HASH_FUNCTION);
}

// Load all enabled modules, if possible.
if (isset($GLOBALS['site']['modules'])) {
	foreach ($GLOBALS['site']['modules'] as $module) {
    	load_module($module);
	}
}

// Set up default variables
$a = isset($_GET['a']) ? $_GET['a'] : false;
$id = isset($_GET['id']) ? $_GET['id'] : 0;
if (isset($_GET['c_msg']) and isset($GLOBALS['c_msg'][$_GET['c_msg']])) {
    $t->assign('c_msg', $GLOBALS['c_msg'][$_GET['c_msg']]);
}
$page['num'] = (isset($_GET['p']) and $_GET['p'] > 0) ? $_GET['p'] : 1;
$page['no_query_url'] = $_SERVER['REQUEST_URI'];

// Set up HTMLHelper class for lazy HTML generation
$hh = new HTMLHelper();
$t->assign('hh', $hh);

// Load the current theme
$theme_bootstrap = theme_dir() . "/index.php";
if (file_exists($theme_bootstrap)) {
    require_once theme_dir() . "/index.php";
} else {
    l("No valid theme was specified for the site. Bootstrap path: $theme_bootstrap", "ERRR");
    $theme = array('css' => array());
    $t->assign('theme', $theme);
}

// Check if the submitted POST token is valid
if (!defined('NO_TOKEN') and count($_POST) > 0) {
    $user->start_session();
    $user->verify_post_token();
}

try {
    // Populate the page links for the home page, even if there's no content.
    $t->assign('pages', $t->populate_page_links(1));
} catch (Exception $e) {
    // Perhaps there are no links, or the database has shat itself. Ignore this.
    l("Could not populate page links for the home page", "WARN");
}

/**
 * autoload function: simply define a class in includes/classes/
 * and this feature of PHP5 will load it for you
 * @param string $class_name
 * @return void
 */
function __autoload($class_name) {
    global $BASE;

    $class_name = str_replace('/', '', $class_name);
    $class_name = str_replace('\\', '', $class_name);
    $class_name = str_replace('.', '', $class_name);

    $class_path = "$BASE/includes/classes/$class_name.class.php";
    
    if (function_exists('l'))
    	l("Init: Attempting to load class $class_path", 'DBUG');

    if (file_exists($class_path))
        require_once($class_path);
    else	
        error_and_bail(ERROR_LOAD_CLASS."$class_path");
} // function