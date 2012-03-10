<?php
class AdminFunctions {
    // Any functions in this class that return an array with three elements are
    // 0: result of the function call
    // 1: short, friendly status
    // 2: description of function result, and any corrective steps

    private function unix_account_instructions() {
        if (function_exists('posix_getuid') and function_exists('posix_getpwuid') and $unix_user = posix_getpwuid(posix_getuid()) and isset($unix_user['name']))
            return " for the UNIX user account <strong>{$unix_user['name']}</strong>.";

        return ".";
    }

    public function log_dir_writable() {
        if (is_writable($GLOBALS['dir']['log'])) {
            return array(
                true,
                'Writable',
                "The site log directory {$GLOBALS['dir']['log']} is writable by the web server user. Site operations will be logged."
            );
        }

        $unix = $this->unix_account_instructions();

        return array(
            false,
            'Non-writable',
            "The site log directory <strong>{$GLOBALS['dir']['log']}</strong> is not writable by the web server user. Site errors and important operations are not being logged. To fix this problem, enable the write permission on this directory$unix"
        );
    }

    public function cache_dir_writable() {
        global $hh;

        if (is_writable($GLOBALS['dir']['cache'])) {
            if ($GLOBALS['site']['caching']) {
                return array(
                    true,
                    'Enabled',
                    "The site cache directory {$GLOBALS['dir']['cache']} is writable. Pages will be cached and site performance is optimized."
                );
            } else {
                return array(
                    false,
                    'Disabled',
                    "Site caching has been disabled from the {$hh->l('admin/settings', 'site settings')} page. You should enable it as soon as possible.",
                );
            }
        }

        $unix = $this->unix_account_instructions();

        return array(
            false,
            'Non-writable',
            "The site cache directory <strong>{$GLOBALS['dir']['cache']}</strong> is not writable by the web server user. Pages are not being cached and site performance will be severely affected under high loads. To fix this problem, enable the write permission on this directory$unix"
        );
    }

    public function content_dir_writable() {
        global $hh;

        if (is_writable($GLOBALS['dir']['content'])) {
            return array(
                true,
                'Writable',
                "The site content directory {$GLOBALS['dir']['content']} is writable. All enabled modules and uploads will function properly.",
            );
        }
        $unix = $this->unix_account_instructions();
        return array(
            false,
            'Non-writable',
            "The site content directory <strong>{$GLOBALS['dir']['content']}</strong> is not writable by the web server user. Certain modules and file uploads will not function properly. To fix this problem, enable the write permission on this directory$unix",
        );
    }

    public function htaccess_writable() {
        global $hh, $BASE;
        if (is_writable("$BASE/.htaccess")) {
            return array(
                true,
                'Writable',
                "The site .htaccess file is writable. Redirection, custom URLs and modules will function properly.",
            );
        }
        $unix = $this->unix_account_instructions();
        return array(
            false,
            'Non-writable',
            "The site .htaccess file is not writable by the web server user. Redirection, modules and URLs will not function properly. To fix this problem, enable the write permission on this file$unix",
        );
    }

    public function settings_writable() {
        global $hh, $BASE;
        if (is_writable("$BASE/settings.inc.php")) {
            return array(
                true,
                'Writable',
                "The site settings file is writable. Settings can be saved from the Web interface.",
            );
        }
        $unix = $this->unix_account_instructions();
        return array(
            false,
            'Non-writable',
            "The site settings file is not writable by the web server user. Settings cannot be saved from the Web interface. To fix this problem, enable the write permission on settings.inc.php$unix",
        );
    }

    public function site_nondefault_salt() {
        global $hh;
        if (DEFAULT_SALT_SHA256 === hash('sha256', SITE_SALT))
            return array(
                false,
                'Default',
                "The site authentication salt has not been changed from its default value. Having a unique salt helps prevent certain automated attacks if your login database is compromised. You can {$hh->l('admin/site-salt', 'provide a unique salt here')}."
            );

        return array(
            true,
            'Non-default',
            "The site authentication salt has been changed from its default. The user database is more resilient to certain automated attacks. If needed, you can change this salt here."
        );
    }

    public function config_permissions_correct() {
        global $BASE;
        global $hh;
        $perms = fileperms("$BASE/config.inc.php");
        $octal_perms = substr(sprintf('%o', $perms), -4);
        if ($perms & 0x0004 or $perms & 0x0002) {
            if ($perms & 0x0002)
               $fix_append = "You can {$hh->l('admin/config-perms', 'automatically fix permissions for this file here')}.";
            else
               $fix_append = "You may be able to fix this problem using FTP or SSH by changing permissions to 640 or removing the <em>other/everyone</em> read and write flags with <strong>chmod o-rw $BASE/config.inc.php</strong>.";

            return array(
                false,
                'Incorrect',
                "The site configuration settings in <strong>$BASE/config.inc.php</strong> have a permission mask of $octal_perms, which allows any user with access to the server to view or change site settings. $fix_append"
            );
        }
       return array(
            true,
            'Correct',
            "The site configuration settings have a permission mask of $octal_perms, which prevents other users from viewing or changing them."
       );
    }

    public function magic_quotes_disabled() {
    	if (function_exists('get_magic_quotes_gpc') and get_magic_quotes_gpc()) {
    		global $hh;
    		$disable_info = $hh->l('http://www.php.net/manual/en/security.magicquotes.disabling.php', "PHP manual", true);
    		return array(
                false,
                'Enabled',
                "The web server has the <strong>magic_quotes</strong> setting turned on, which can lead to unexpected issues with user data. Please see the $disable_info for instructions on disabling this potentially dangerous setting."
    		);
    	}

    	return array(
    	   'true',
    	   'Disabled',
    	   "The web server has the <strong>magic_quotes</strong> setting turned off. User input will be properly processed by this site."
    	);
    }
}