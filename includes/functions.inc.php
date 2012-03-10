<?php
function l($message, $type = 'INFO') {
    if ($type == 'DBUG' and !$GLOBALS['site']['debug']) {
        return;
    }

    // Insert log into both logfile and sitelog table.
    global $db, $user, $core;

    $filename = "{$GLOBALS['dir']['log']}{$core->log_date_file}-site.log";
    $url = $_SERVER['REQUEST_URI'];
    $user_name = '';
    if (isset($user)) {
        $user_name = $user->current_user();
    }

    if (is_array($message)) {
        $message = implode(',', $message);
    }

    $log_message = "{$core->log_date_common}|{$type}|{$url}|{$user_name}|{$message}";
    // $log_message = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $log_message);
    $log_message = preg_replace("/\s(\s+)?/", " ", $log_message);

    // Don't log debugging and hard database failure messages to the file.
    if ($type != 'DBUG' and $type != 'FAIL') {
        // Manual insert to avoid shenanigans with repeated logging.
        $sql = "INSERT INTO sitelog (type, url, message, \"user\") VALUES (?, ?, ?, ?)";
        $params = array($type, $url, $message, $user_name);
        if ($db)
        	$result = $db->direct()->Execute($sql, $params);
        if (!isset($result) or !$result) {
        	$db_error = $db ? $db->direct()->ErrorMsg() : '';
            l("Log: Failed to insert into log: " . implode(',', $params) . ", database error " . $db_error, "FAIL");
        }
    }

    @file_put_contents($filename, $log_message . "\n", FILE_APPEND);
}

function core() {
    global $core;
    return $core;
}

function t() {
    global $t;
    return $t;
}

function db() {
    global $db;
    return $db;
}

function user() {
    global $user;
    return $user;
}

function load_module($module) {
    $module_dir = "{$GLOBALS['dir']['modules']}$module";
    if (file_exists("$module_dir/$module.ini") and file_exists("$module_dir/$module.module.php")) {
        require_once "$module_dir/$module.module.php";
        $GLOBALS['modules'][$module] = new $module;
    }
}

function error_and_bail($message, $friendly_message = false, $exit = true) {
    if (!$friendly_message) {
        $friendly_message = $message;
    }
    l($message, "ERRR");
    display_error_template($friendly_message, $exit);
}

function display_error_template($message = false, $exit = true) {
    global $t;

    if ($t) {
    	if ($message) {
    	   $t->assign('error_message', $message);
	   }
    	if ($exit)
            $t->display_end('error.tmpl.php', true);
        else
            $t->display('error.tmpl.php', true);
    }
}

/**
 * Format a date for HTTP standards compliance.
 * @param $cache_time unix_timestamp
 * @return string
*/
function format_http_date($cache_time = false) {
    if (!$cache_time)
        $cache_time = time();

    return date('D, d M Y H:i:s T', $cache_time);
}

function format_rfc_date($cache_time = false) {
	if (!$cache_time)
		$cache_time = time();

	return date('r', $cache_time);
}

function static_expires_header() {
	// Emit a static expiration header for +1 day.
	$expires = time() + (60 * 60 * 24);
	header("Expires: " . format_http_date($expires));
	header("Pragma: ");
}

function theme_dir() {
    return "{$GLOBALS['dir']['theme']}{$GLOBALS['site']['view_settings']['theme']}";
}

function theme_root() {
    global $ROOT;
    return "{$ROOT}themes/{$GLOBALS['site']['view_settings']['theme']}";
}

function validate_db_settings($db_settings) {
    if (isset($db_settings['type']) and
        isset($db_settings['host']) and
        isset($db_settings['user']) and
        isset($db_settings['password']) and
        isset($db_settings['db']))
        return true;

    return false;
}

function paginate($item_count, $items_per_page, $current_page, $get_param = 'p') {
	$page = array();
	$page['item_count'] = $item_count;
	$page['items_per_page'] = $items_per_page;
	$page['no_query_url'] = $_SERVER['REQUEST_URI'];
	$page['last'] = ceil($item_count / $items_per_page);
	if ($current_page > $page['last'] or $current_page < 1)
		$current_page = 1;

	$page['offset'] = ($current_page - 1) * $items_per_page;
	$qpos = strpos($page['no_query_url'], '?');
    if ($qpos !== false) {
    	// strip query completely and reconstruct it
    	$page['no_query_url'] = substr($page['no_query_url'], 0, $qpos);
    	$new_query = '';
    	$invalid_params = array('page');
    	foreach ($_GET as $key => $val) {
    		if ($key != $get_param and !in_array($key, $invalid_params)) {
    		  $new_query .= "$key=$val&";
    		}
    	}

    	if ($new_query) {
    		$page['query'] = "?$new_query&$get_param=";
    	    $page['no_query_url'] .= $page['query'];
    	} else {
    		$page['query'] = "?$get_param=";
    	    $page['no_query_url'] .= $page['query'];
    	}
    } else {
    	$page['query'] = "?$get_param=";
    	$page['no_query_url'] .= $page['query'];
    }

    $page['next'] = $current_page + 1;
    $page['prev'] = $current_page - 1;
    $page['get_param'] = $get_param;
    $page['current'] = $current_page;

    return $page;
}

function query_build($base_url, $modifications) {
    global $ROOT;
    $url = "$ROOT$base_url?";
    $getvars = $_GET;
    if (isset($getvars['page'])) {
        unset($getvars['page']);
    }
    foreach ($getvars as $key => $val) {
        if (isset($modifications[$key])) {
            $url .= "$key=" . $modifications[$key];
        } else {
            $url .= "$key=" . $val;
        }
        $url .= "&";
    }
    foreach ($modifications as $key => $val) {
        if (!isset($getvars[$key])) {
            $url .= "$key=$val&";
        }
    }

    // Remove last & character from URL
    $url = substr($url, 0, -1);

    return $url;
}

function urandom_bits($length = 16) {
    $bits = '';
    $fp = @fopen('/dev/urandom', 'rb');
    if ($fp !== FALSE) {
        $bits .= @fread($fp, $length);
        @fclose($fp);
    }

    return $bits;
}

function append_file_name($path, $append) {
    $pos = strrpos($path, '.');
    if ($pos === FALSE) {
        l("Path $path did not contain extension, returning original path", "WARN");
        return $path;
    }
    $path_pre = substr($path, 0, $pos);
    $path_ext = substr($path, $pos);
    return "{$path_pre}{$append}{$path_ext}";
}