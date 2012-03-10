<?php
require_once "./config.inc.php";
// TODO: Check if we have a functional database and redirect to the installer if needed.

if (!isset($_GET['page']) or !$_GET['page']) {
    // Immediately redirect to view.php and don't bother processing data.
	$GLOBALS['url'] = array();
    require_once "{$BASE}/core/view.php";
    exit;
}

// Check if we're running in a subdirectory. If so, remove the root path from the $_GET['page'] variable.
$remove_root = substr($ROOT, 1);
if ($remove_root and strpos($_GET['page'], $remove_root) === 0) {
    // Cut down the page variable
    $_GET['page'] = substr($_GET['page'], strlen($remove_root));
}

// Split up page array based on slashes.
$GLOBALS['url'] = explode('/', $_GET['page']);
foreach ($GLOBALS['url'] as $id => $urlcomponent) {
    $GLOBALS['url'][$id] = htmlspecialchars($urlcomponent);
}
$query_slug = htmlspecialchars($_GET['page']);

if (in_array($GLOBALS['url'][0], $VALID_ACTIONS)) {
    $path = "$BASE/core/{$GLOBALS['url'][0]}.php";
    if (file_exists($path)) {
        include $path;
    } else {
        include "$BASE/core/404.php";
    }
    exit;
}

require_once "{$BASE}/core/view.php";