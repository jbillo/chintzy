<?php
define('NO_CACHE', true);
require_once 'includes/init.php';
$t->require_ssl();
$t->require_permission('view administration dashboard');

// Check if we need to navigate to a custom URL. If so, the buck stops here.
$core->url_navigate($GLOBALS['url']);

$page_title = "Administration | {$GLOBALS['site']['title']}";
$t->assign('page_title', $page_title);

// Check which action is being taken.
switch ($query_slug) {
    case 'admin/clear-cache':
        $t->require_permission('clear site cache');
        if (!isset($_POST['clear-cache-confirm']))
	        error_and_bail("Missing POST action for cache clear operation", "Could not clear cache. Please access through the admin panel.");
		$result = $t->clear_cache_dir();
		if (!$result) {
            error_and_bail("Clear cache directory failed", "Could not clear cache directory");
		}
        $t->redirect('admin/', 'cache_cleared');
    break;
    case 'admin/site-salt':
        $t->require_permission('edit site salt');
        $t->assign('salt_hash', hash('sha256', SITE_SALT));
        $t->assign('file_writable', is_writable("$BASE/salt.inc.php"));
        $t->display('admin/site-salt.tmpl.php');
        $t->display_end('admin/return.tmpl.php');
    break;
    case 'admin/site-salt-save':
    	// TODO Attempt to save the site salt to salt.inc.php
    	$t->require_permission('edit site salt');
    break;
    case 'admin/logviewer':
        $t->require_permission('view site logs');
        $db->clear_cache();
        // Display the log file.
        $sql = "SELECT url, message, type, user, created_on FROM sitelog ORDER BY created_on DESC";
        $result = $db->select_limit($sql, array(), 1000);
        $logs = array();

        if ($result) {
            while (!$result->EOF) {
                if (!isset($result->fields['user']) or !$result->fields['user']) {
                    $result->fields['user'] = '';
                }
                $logs[] = $result->fields;
                $result->MoveNext();
            }
        }

        $t->assign('logs', $logs);
        $t->display_end('admin/log.tmpl.php');
    break;
    case 'admin/settings':
    	$t->require_permission('edit site settings');
        // Available date formats
        $date_formats = array(
            'Y-m-d',
            'd/m/Y',
            'm/d/Y',
            'F j, Y'
        );
        $time_formats = array(
            'g:i A',
            'h:i A',
            'H:i'
        );
        $t->assign('date_formats', $date_formats);
        $t->assign('time_formats', $time_formats);
        $t->assign('theme_dirs', $core->theme_dirs());
        $t->assign('modules_list', $core->modules_list());
        $t->assign('module_settings', $core->module_settings());

    	$t->display('admin/settings.tmpl.php');
    	$t->end();
    break;
    case 'admin/settings-save':
        $t->require_permission('edit site settings');
        $prev_settings = $GLOBALS['site'];

        if (!$core->append_save_settings()) {
            error_and_bail("Could not save settings", $t->fetch('error/settings-save.tmpl.php'));
        }

        // Determine which modules were added and which were removed.
        $old_modules = $prev_settings['modules'];
        $new_modules = $GLOBALS['site']['modules'];

        // Determine if a module has been uninstalled.
        foreach ($old_modules as $old_module) {
            if (!in_array($old_module, $new_modules)) {
            	l("Uninstalling module $old_module", "INFO");
                $core->execute_module_function($old_module, 'uninstall');
            }
        }

        // Determine if a module has been installed.
        foreach ($new_modules as $new_module) {
            if (!in_array($new_module, $old_modules)) {
                // Load module and execute installer function
                l("Installing module $new_module", "INFO");
                load_module($new_module);
                $core->execute_module_function($new_module, 'install');
            }
        }

    	$t->clear_cache_dir();

    	// Redirect to saved settings page
    	$t->redirect("admin", "settings_saved");
    break;
    case 'admin/help':
        $t->require_permission('view site help');
        $t->display_end('admin/help.tmpl.php');
    break;
    case 'admin/rebuild-htaccess':
        $t->require_permission('edit htaccess');
        if (!isset($_POST['rebuild-htaccess-confirm'])) {
            error_and_bail("Missing POST action for rebuild operation", "Could not rebuild .htaccess. Please access through the admin panel.");
        }

        if ($core->write_htaccess() > 0) {
            $t->redirect('admin', 'rebuild_htaccess_success');
        } else {
            $t->redirect('admin', 'rebuild_htaccess_failure');
        }
    break;
    case 'admin/wp-import':
        $t->require_permision('import wordpress content');
        $t->display_end('admin/wp-import-form.tmpl.php');
    break;
    case 'admin/wp-import-finish':
        $t->require_permission('import wordpress content');
        $core->import_wp_content($_POST['db_host'], $_POST['db_user'], $_POST['db_password'], $_POST['db_name'], $_POST['db_prefix']);
        $t->end();
    break;
    case 'admin/user':
    case 'admin/user/edit':
    case 'admin/user/delete':
        define('INCLUDE_CHAIN', true);
        require_once "$BASE/core/admin/admin-user.inc.php";
        return admin_user_dashboard();
    break;
    case 'admin/role':
    case 'admin/role/edit':
    case 'admin/role/delete':
        define('INCLUDE_CHAIN', true);
        require_once "$BASE/core/admin/admin-role.inc.php";
        return admin_role_dashboard();
    break;
    case 'admin/permission':
        define('INCLUDE_CHAIN', true);
        require_once "$BASE/core/admin/admin-permission.inc.php";
        return admin_permission_manage();
    break;
    case 'admin':
        // Do nothing, fall through to default actions
    break;
    default:
        // Bad URL, redirect to /admin
        $t->redirect('admin');
    break;
}

// For the site status table, retrieve the necessary information.
$admin = new AdminFunctions();

// Log writable
$status_log_dir = $admin->log_dir_writable();
if ($status_log_dir[0])
    $t->assign('status_log_dir_class', 'status-ok');
else
    $t->assign('status_log_dir_class', 'status-fail');
$t->assign('status_log_dir', $status_log_dir);

// Cache writable
$status_cache_dir = $admin->cache_dir_writable();
if ($status_cache_dir[0])
    $t->assign('status_cache_dir_class', 'status-ok');
else
    $t->assign('status_cache_dir_class', 'status-fail');
$t->assign('status_cache_dir', $status_cache_dir);

// Content writable
$status_content_dir = $admin->content_dir_writable();
if ($status_content_dir[0])
    $t->assign('status_content_dir_class', 'status-ok');
else
    $t->assign('status_content_dir_class', 'status-fail');
$t->assign('status_content_dir', $status_content_dir);

// .htaccess writable
$status_htaccess = $admin->htaccess_writable();
if ($status_htaccess[0])
    $t->assign('status_htaccess_class', 'status-ok');
else
    $t->assign('status_htaccess_class', 'status-fail');
$t->assign('status_htaccess', $status_htaccess);

// settings.inc.php writable
$status_settings = $admin->settings_writable();
if ($status_settings[0])
    $t->assign('status_settings_class', 'status-ok');
else
    $t->assign('status_settings_class', 'status-fail');
$t->assign('status_settings', $status_settings);

// Non-default salt
$nondefault_salt = $admin->site_nondefault_salt();
if ($nondefault_salt[0])
    $t->assign('nondefault_salt_class', 'status-ok');
else
    $t->assign('nondefault_salt_class', 'status-fail');
$t->assign('status_nondefault_salt', $nondefault_salt);

// Configuration permissions
$status_config_permissions = $admin->config_permissions_correct();
if ($status_config_permissions[0])
    $t->assign('status_config_permissions_class', 'status-ok');
else
    $t->assign('status_config_permissions_class', 'status-fail');
$t->assign('status_config_permissions', $status_config_permissions);

// Magic quotes
$status_magic_quotes = $admin->magic_quotes_disabled();
if ($status_magic_quotes[0])
    $t->assign('status_magic_quotes_disabled_class', 'status-ok');
else
    $t->assign('status_magic_quotes_disabled_class', 'status-fail');
$t->assign('status_magic_quotes_disabled', $status_magic_quotes);

$core->admin_page_before();
$t->display('admin.tmpl.php');
$core->admin_page_after();
$t->end();