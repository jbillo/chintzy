<?php
if (!defined('INCLUDE_CHAIN')) {
    exit;
}

function admin_role_dashboard() {
    if (!isset($GLOBALS['url'][2])) {
        return admin_role_menu();
    }

    switch ($GLOBALS['url'][2]) {
        case 'edit':
            return admin_role_edit();
        break;
        case 'delete':
            return admin_role_delete();
        break;
    }
}

function admin_role_menu() {
    global $t;
    global $db;

    $t->require_permission('manage role');

    $result = $db->get_all('roles', array(), "ORDER BY name");
    $roles = array();
    if ($result) {
        while (!$result->EOF) {
            $roles[] = $result->fields;
            $result->MoveNext();
        }
    }

    $t->assign('roles', $roles);

    $t->display_end('admin/role-manage.tmpl.php');
}

function admin_role_edit() {
    global $t;
    global $db;
    global $user;

    if (isset($_POST['save_role']) and $_POST['save_role']) {
        return admin_role_save();
    }

    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    // get information for editing
    if ($id) {
        $t->require_permission('edit role');
        $result = $db->get_one($id, 'roles');
        if (!$result) {
            error_and_bail("Could not retrieve role information for $id");
        }

        $role = $result->fields;

        // pull subsequent permissions for role
        $sql = "SELECT id, name FROM permissions WHERE id IN (SELECT permission_id FROM role_permissions WHERE role_id = ?) ORDER BY name ASC";
        $params = array($id);
        $result = $db->execute($sql, $params);

        $role_perms = array();

        while (!$result->EOF) {
            $role_perms[$result->fields['id']] = $result->fields['name'];
            $result->MoveNext();
        }
    } else {
        $t->require_permission('add role');
        $role = array('name' => '');
        $role_perms = array();
    }

    $t->assign('id', $id);
    $t->assign('role', $role);
    $t->assign('role_perms', $role_perms);

    // pull all site permissions
    $site_perms = $user->site_permissions();
    $t->assign('site_perms', $site_perms);

    $t->display_end('admin/role-edit.tmpl.php');
}

function admin_role_save() {
    global $db;
    global $t;

    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if (!isset($_POST['role']) or !$_POST['role']) {
        error_and_bail("A role name must be specified.");
    }

    // Check if we're trying to edit anonymous/authenticated user, and don't allow name changes
    if ($id) {
        $t->require_permission('edit role');
        $result = $db->get('roles', array('id' => $id), array('name'), '', 1);
        if ($result and $result->fields) {
            if ($result->fields['name'] == 'anonymous user' or $result->fields['name'] == 'authenticated user') {
                l("Admin: User attempted to change role name of {$result->fields['name']}", "WARN");
                $_POST['role'] = $result->fields['name'];
            }
        }
    } else {
        $t->require_permission('add role');
    }

    $clean = array(
        'name' => htmlspecialchars($_POST['role']),
    );

    $new_perms = array();
    if (isset($_POST['permissions'])) {
        foreach ($_POST['permissions'] as $perm) {
            $new_perms[] = (int) $perm;
        }
    }

    // Get existing permissions for this role
    $old_perms = array();
    if ($id) {
        $sql = "SELECT permission_id FROM role_permissions WHERE role_id = ?";
        $params = array($id);
        $result = $db->execute($sql, $params);
        if ($result) {
            while (!$result->EOF) {
                $old_perms[] = $result->fields['permission_id'];
                $result->MoveNext();
            }
        } else {
            error_and_bail("Could not retrieve existing permissions for role $id");
        }
    }

    $add_perms = array_diff($new_perms, $old_perms);
    $del_perms = array_diff($old_perms, $new_perms);

    // Start transaction and remove permissions, then add new permissions.
    $db->start_trans();
    foreach ($del_perms as $perm) {
        $sql = "DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?";
        $params = array($id, $perm);
        $result = $db->execute($sql, $params);
    }

    $db->clear_cache();

    // Check if this is a new role and add it
    if ($id == 0) {
        $result = $db->add(array(
            'name' => $clean['name'],
        ), 'roles');
        $id = $db->last_id('roles_id_seq');
        $db->clear_cache();
    }

    // Add new permissions
    foreach ($add_perms as $perm_id) {
        $result = $db->add(array(
            'role_id' => $id,
            'permission_id' => $perm_id,
        ), "role_permissions");
    }

    // Update or insert as necessary.
    if ($id) {
        $result = $db->update($clean, $id, 'roles');
    } else {
        $result = $db->add($clean, 'roles');
        $id = $db->last_id('roles_id_seq');
    }

    if ($db->has_failed_trans()) {
        $db->clear_cache();
        error_and_bail("A database error occurred while saving permissions for the role.");
    }

    $db->complete_trans();
    $db->clear_cache();

    l("Role $id saved successfully", "INFO");
    $t->redirect('admin/role', 'role_saved');
}

function admin_role_delete() {
    global $db;
    global $t;
    // exclusively AJAX call
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if (!$id) {
        l("No ID specified for deleting role", "WARN");
        echo "0No role ID was specified to delete"; exit;
    }

    // check if role exists and is valid
    $result = $db->get_one($id, 'roles', 'id');
    if (!$result) {
        l("Invalid ID specified for deleting role", "WARN");
        echo "0Invalid ID specified for role to delete"; exit;
    }
    if ($result->fields['name'] == 'anonymous user' or $result->fields['name'] == 'authenticated user') {
        l("User attempted to delete anonymous or authenticated user role", "ERRR");
        echo "0Cannot delete anonymous or authenticated user role"; exit;
    }

    // delete item: prelim check - do any users have this role?
    $sql = "SELECT id FROM role_users WHERE role_id = ?";
    $params = array($id);
    $result = $db->execute($sql, $params);

    if ($result) {
        l("One or more active user accounts exist. Cannot delete role $id", "ERRR");
        echo "0One or more user accounts have role $id assigned. You must change or remove their roles before deleting the role.";
    }

    // start transaction
    $db->start_trans();

    // remove role_permissions entries
    $sql = "DELETE FROM role_permissions WHERE role_id = ?";
    $params = array($id);
    $result = $db->execute($sql, $params);

    // remove role entry
    $result = $db->delete($id, 'roles', 'id');

    if ($db->has_failed_trans()) {
        l("Database error: one or more failed queries in transaction while deleting role $id", "ERRR");
        echo "0A database error occurred while deleting role $id.";
        exit;
    }

    $db->complete_trans();
    l("Role $id deleted successfully", "INFO");
    $db->clear_cache();
    echo "1"; exit;
}