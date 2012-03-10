<?php
if (!defined('INCLUDE_CHAIN')) {
    exit;
}

function admin_permission_manage() {
    global $t;
    global $db;
    global $core;

    // Build permissions that haven't been added
    $core->build_permissions();

    $result = $db->get_all('permissions', array(), "ORDER BY name");
    $permissions = array();
    if ($result) {
        while (!$result->EOF) {
            $permissions[] = $result->fields;
            $result->MoveNext();
        }
    }

    $t->assign('permissions', $permissions);

    $t->display_end('admin/permission-manage.tmpl.php');
}