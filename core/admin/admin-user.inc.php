<?php
if (!defined('INCLUDE_CHAIN')) {
    exit;
}

function admin_user_dashboard() {
    // determine which action is being taken
    global $t;
    global $db;
        
    $t->require_permission('manage user');

    if (!isset($GLOBALS['url'][2])) {
        return admin_user_manage(); 
    }
    
    switch ($GLOBALS['url'][2]) {
        case 'edit':
            return admin_user_edit();
        break;
        case 'delete':
            return admin_user_delete();
        break;
    }
}

function admin_user_manage() {
    global $t;
    global $db;
    
    $t->require_permission('manage user');
    
    $users = array();

    $result = $db->get('users', array(), array('id', 'email'), "ORDER BY email");
    while (!$result->EOF) {
        $users[] = $result->fields;
        $result->MoveNext();
    }
    $t->assign('users', $users);
    $t->display_end('admin/user-manage.tmpl.php');
}

function admin_user_delete() {
    global $db;
    global $t;
    global $user;
    
    $t->set_format('json');
    
    // deleting a specific user, performed by AJAX only
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if (!$id) {
        l("No ID specified for user deletion", "ERRR");
        echo "0No ID was specified to delete."; exit;
    } else {
        $db->start_trans();
        $result = $db->delete($id, 'user_roles', 'user_id');
        $result = $db->delete($id, 'users', 'id');
        if ($db->has_failed_trans()) {
            l("Database error while deleting user account $id", "ERRR");
            echo "0A database error occurred while deleting this account."; exit;
        }
        $db->complete_trans();
        l("User {$user->current_user()} deleted account $id", "INFO");
        if ($id == $user->id()) {
            $user->logout();
        }
        $db->clear_cache();
        echo "1"; exit;
    }
}

function admin_user_edit() {
    global $t;
    global $db;
    global $user;
    
    if (isset($_POST['save_action']) and $_POST['save_action']) {
        // save user
        $id = (isset($_POST['id'])) ? (int) $_POST['id'] : 0;

        if (!isset($_POST['email']) or !$_POST['email']) {
            error_and_bail("Email address not provided for account", "Please provide an email address for this account.");
        }

        $clean['email'] = strtolower(htmlspecialchars($_POST['email']));

        // If we're editing, pull previous data.
        if ($id) {
            $t->require_permission('edit user');
            $result = $db->get_one($id, 'users', 'id');
            $prev_data = $result->fields;
            $old_roles = array_keys($user->roles($prev_data['email']));

            // Have we changed the email address and not provided a password?
            if ($prev_data['email'] != $clean['email'] and (!isset($_POST['password']) or !$_POST['password'])) {
                error_and_bail("Email changed without password confirmation", "Samuel L. Jackson is displeased. What did we tell you about email addresses and passwords?");
            }
        } else {
            $t->require_permission('add user');
            if (!isset($_POST['password']) or !$_POST['password']) {
                error_and_bail("No password provided for new account", "Please provide a password for this new user account.");
            }
        }

        $new_roles = array();
        if (!isset($old_roles)) {
            $old_roles = array();
        }
        if (isset($_POST['roles'])) {
            foreach ($_POST['roles'] as $role) {
                $new_roles[] = (int) $role;
            }
        }

        l("Assigning new roles to {$clean['email']}: " . print_r($new_roles, true), "DBUG");
        l("Removing old roles from {$clean['email']}: " . print_r($old_roles, true), "DBUG");

        // Determine which roles to add.
        $add_roles = array_diff($new_roles, $old_roles);
        $del_roles = array_diff($old_roles, $new_roles);

        $params = array(
            'email' => $clean['email'],
        );

        if (!$id or ($id and isset($_POST['password']))) {
            $params['password'] = $user->password_hash($params['email'], $_POST['password']);
        }

        $db->start_trans();

        if ($id) {
            l("User account {$prev_data['email']} ($id) updated by {$user->current_user()}", "INFO");
            $result = $db->update($params, $id, 'users');
        } else {
            $result = $db->add($params, 'users');
            $id = $db->last_id('users_id_seq');
            l("New account {$params['email']} ($id) added by {$user->current_user()}", "INFO");
        }

        if (!$result) {
            error_and_bail("Failed to save user account {$clean['email']}");
        }

        $db->clear_cache();

        // Apply roles to account
        foreach ($add_roles as $role) {
            $result = $db->add(array(
                'user_id' => $id,
                'role_id' => $role,
            ), 'user_roles');
            if (!$result) {
                $msg = print_r($db->last_error(), true);
                error_and_bail("Could not assign role $role to user $id ($msg)");
            }
        }

        $db->clear_cache();

        // Remove roles from account
        foreach($del_roles as $role) {
            $sql = "DELETE FROM user_roles WHERE role_id = ? AND user_id = ?";
            $params = array($role, $id);
            $result = $db->execute($sql, $params);
            if (!$result) {
                $msg = print_r($db->last_error(), true);
                error_and_bail("Could not remove role $role from user $id ($msg)");
            }
        }

        if ($db->has_failed_trans()) {
            $db->clear_cache();
            error_and_bail("One or more database errors occurred while saving user account {$clean['email']}.");
        }

        $db->complete_trans();
        $db->clear_cache();

        // If the account being edited was your own, log out as the session won't be valid anymore.
        if (isset($prev_data['id']) and $user->id() == $prev_data['id']) {
            $user->logout();
        }

        l("User account {$clean['email']} saved successfully.", "INFO");

        $t->redirect('admin', 'user_saved');
    } else {
        // editing a specific user - has to be POST args though
        $t->add_script('js/admin-user.js');

        if (isset($_POST['id'])) {
            $t->require_permission('edit user');
            $id = (int) $_POST['id'];
            $result = $db->get('users', array('id' => $id), 'email', '', 1);
            if ($result) {
                $acct = $result->fields;
            }
            $t->add_script("var orig_user = '{$acct['email']}';", true);
            // pull user permissions
            $acct['roles'] = $user->roles($acct['email']);
        } else {
            $t->require_permission('add user');
            $id = 0;
            $acct = array('email' => '', 'roles' => array());
            $t->add_script("var orig_user = '';", true);
        }

        $t->assign('id', $id);
        $t->assign('acct', $acct);
        $t->assign('site_roles', $user->site_roles());

        $t->display_end('admin/user-edit.tmpl.php');
        // edit user
    }
}