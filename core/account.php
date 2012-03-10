<?php
require_once "{$BASE}/includes/init.php";
$t->require_ssl();

switch ($query_slug) {
    case 'account/create':
        account_create();
    break;
    case 'account':
        account_management();
    break;
    case 'account/forgot':
        account_forgot();
    break;
    case 'account/recovery':
        account_recovery();
    break;
    case 'account/save':
        account_save();
    break;
    default:
        $t->redirect('account');
    break;
}

function account_management() {
    global $user;
    global $t;
    global $core;
    
    $core->load_library('recaptcha');
    
    $t->add_script('js/account.js');
    $t->assign('page_title', "Account Management | {$GLOBALS['site']['title']}");

    if (!$user->exists()) {
        $t->assign('prev_page', $_GET['page']);
        if (isset($_GET['recovery']) and $_GET['recovery']) {
            $t->display('account/account-recovered.tmpl.php');
        }
        $t->display('account/account-none.tmpl.php');
    } else {
        $t->assign('user_id', $user->id());
        $t->assign('user_email', $user->email());

        if (isset($_GET['create']) and $_GET['create']) {
            $t->display('account/account-created.tmpl.php');
        }
        if (isset($_GET['changepw']) and $_GET['changepw']) {
            $t->display('account/account-changepw.tmpl.php');
        }

        $t->assign('account_page_before', $core->account_page_before());

        $t->display('account.tmpl.php');
    }

    $t->end();
}

function account_save() {
    global $t;
    global $db;

    $pw['current'] = isset($_POST['current_password']) ? $_POST['current_password'] : false;
    $pw['new'] = isset($_POST['new_password']) ? $_POST['new_password'] : false;
    $pw['new_confirm'] = isset($_POST['new_password_confirm']) ? $_POST['new_password_confirm'] : false;

    if (!$pw['new'] or !$pw['new_confirm'] or !$pw['current']) {
        error_and_bail("Please provide all requested password fields.");
    }

    if ($pw['new'] != $pw['new_confirm']) {
        error_and_bail("The passwords provided did not match.");
    }

    global $user;
    if (!$user->validate_password($user->email(), $pw['current'])) {
        error_and_bail("The current password for this account was not correct.");
    }

    // Update user account
    $result = $db->update(
        array(
            'password' => $user->password_hash($user->email(), $pw['new'])
        ),
        $user->id(), 'users');
    if (!$result) {
        error_and_bail("An error occurred changing the account password.");
    }

    $db->clear_cache();
    $t->redirect('account?changepw=1');
}

function account_create() {
    global $user;
    global $db;
    global $t;
    global $core;

    foreach (array('create_email', 'create_password', 'create_password_confirm') as $item) {
        if (!isset($_POST[$item]) or !$_POST[$item]) {
            error_and_bail("One or more required fields was missing.");
        } else {
            $$item = $_POST[$item];
        }
    }

    $create_email = trim(strtolower($create_email));
    if (filter_var($create_email, FILTER_VALIDATE_EMAIL) === FALSE) {
        error_and_bail("Please provide a valid email address to create an account.");
    }

    if ($create_password !== $create_password_confirm) {
        error_and_bail("The passwords provided did not match.");
    }
    
    // check for password length
    if (strlen($create_password) < 8) {
        error_and_bail("The password provided was less than 8 characters.");
    }
    
    // check for reCAPTCHA response
    $core->load_library('recaptcha');
    $response = recaptcha_check_answer($GLOBALS['site']['recaptcha']['private_key'],
                                       $_SERVER['REMOTE_ADDR'],
                                       $_POST['recaptcha_challenge_field'],
                                       $_POST['recaptcha_response_field']);

    if (!$response->is_valid)
        error_and_bail("Invalid reCAPTCHA response: {$response->error}", $GLOBALS['c_msg']['recaptcha_invalid']);    
    
    // Check if account exists - if so, attempt to log in.
    $sql = "SELECT id FROM users WHERE email = ?";
    $params = array($create_email);
    $result = $db->select_limit($sql, $params, 1);
    
    if ($result and $result->RecordCount() > 0) {
        // try to log in with create_password
        $result = $user->login($create_email, $create_password);
        if ($result) {
            $t->redirect("account");
        }
    }

    // Create account and login, then redirect to management.
    $result = $db->add(array('email' => $create_email, 'password' => $user->password_hash($create_email, $create_password)), 'users');

    if (!$result) {
        error_and_bail("An error occurred creating the account. Please contact {$GLOBALS['site']['webmaster']}.");
    }

    $db->clear_cache();

    $user->login($create_email, $create_password);
    
    // FIXME send out an email advising the user of account creation
    
    $t->redirect('account?create=1');
}

function account_recovery() {
    // FIXME add appropriate notifications
    global $db;

    if (isset($_POST['recovery_form']) and $_POST['recovery_form']) {
        return account_recovery_phase2();
    }

    // Check for key and valid email
    if (!isset($_GET['key']) or !isset($_GET['email']) or !$_GET['key'] or !$_GET['email']) {
        error_and_bail("The recovery key or email address provided is invalid.");
    }

    $email = trim(strtolower(htmlspecialchars($_GET['email'])));
    $recovery_key = htmlspecialchars($_GET['key']);

    $sql = "SELECT id FROM users WHERE email = ?";
    $params = array($email);
    $result = $db->select_limit($sql, $params, 1);

    if (!$result) {
        // TODO add tech support notification
        error_and_bail("An error occurred recovering the specified account. Please contact technical support.");
    }

    // Determine if we have a matching recovery record
    $sql = "SELECT id FROM user_recovery WHERE user_id = ? AND recovery_key = ?";
    $params = array($result->fields['id'], $recovery_key);
    $result = $db->select_limit($sql, $params, 1);

    if (!$result or $result->RecordCount() < 1) {
        // TODO add tech support notification
        error_and_bail("An error occurred recovering the specified account. Please contact technical support.");
    }

    // Display password reset boxes since the user is authenticated
    global $t;
    $t->assign('email', $email);
    $t->assign('key', $recovery_key);
    $t->display('account/account-new-password.tmpl.php');
    $t->end();
}

function account_recovery_phase2() {
    global $user;
    global $db;
    global $t;

    // check that we have all fields
    foreach (array('email', 'key', 'recovery_password', 'recovery_password_confirm') as $item) {
        if (!isset($_POST[$item]) or !$_POST[$item]) {
            error_and_bail("One or more required fields was missing.");
        } else {
            $$item = $_POST[$item];
        }
    }

    $email = trim(strtolower($email));

    // compare password to confirm password
    if ($recovery_password !== $recovery_password_confirm) {
        error_and_bail("The new passwords provided did not match.");
    }

    // Update user account with new password and remove entry
    $user_id = $user->exists($email);
    if (!$user_id) {
        error_and_bail("An error occurred setting a new password for this account.");
    }

    $result = $db->update(array('password' => $user->password_hash($email, $recovery_password)), $user_id, 'users');
    $result = $db->delete($user_id, 'user_recovery', 'user_id');

    $db->clear_cache();

    $t->redirect('account?recovery=1');
}

function account_forgot() {
    if (isset($_POST['forgot_form']) and $_POST['forgot_form']) {
        return account_forgot_phase1();
    }

    global $t;
    if (isset($_GET['finish']) and $_GET['finish']) {
        $t->display('account/account-forgot-phase1.tmpl.php');
    }
    $t->display('account/account-forgot.tmpl.php');
}

function account_forgot_phase1() {
    global $core;
    global $db;
    global $t;

    if (!isset($_POST['forgot_email']) or !$_POST['forgot_email']) {
        error_and_bail("Please provide a valid email address to recover an account.");
    }

    $email = trim(htmlspecialchars($_POST['forgot_email']));
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
        error_and_bail("Please provide a valid email address to recover an account.");
    }

    // Search for valid user account
    $sql = "SELECT id FROM users WHERE email = ?";
    $params = array($email);
    $result = $db->select_limit($sql, $params, 1);

    if (!$result or !$result->fields['id']) {
        l("Account: Could not reset account $email, account does not exist", "WARN");
        return account_forgot_phase1_finish();
    }

    $user_id = $result->fields['id'];
    // Determine if we have an authorization token for this user
    $sql = "SELECT id, created_on, recovery_key, sent_messages FROM user_recovery WHERE user_id = ?";
    $params = array($user_id);
    $result = $db->select_limit($sql, $params, 1);

    if (!$result or !$result->fields) {
        // create an account reset attempt
        $recovery_key = hash('sha256', mt_rand() . urandom_bits());
        l("Account: Creating new account reset attempt for $email with key $recovery_key", "DBUG");
        $result = $db->add(array('user_id' => $user_id, 'sent_messages' => 1, 'recovery_key' => $recovery_key), 'user_recovery');
        $reset_id = $db->last_id('user_recovery_id_seq');
    } else {
        l("Account: Account reset attempt exists for $email", "DBUG");
        // check if account reset is valid and update
        $reset_id = $result->fields['id'];
        $recovery_key = $result->fields['recovery_key'];
        if ($result->fields['sent_messages'] >= $GLOBALS['site']['account']['daily_attempts']) {
            l("Account: Could not reset account $email, messages sent today ({$result->fields['sent_messages']}) exceed daily limit", "WARN");
            return account_forgot_phase1_finish();
        }
        $result = $db->update(array('sent_messages' => ($result->fields['sent_messages'] + 1)), $reset_id, 'user_recovery');
        l("Account: Updated sent messages for account $email, key $recovery_key", "DBUG");
    }

    $t->assign('recovery_key', $recovery_key);
    $t->assign('email', $email);

    $transport = $core->load_library('swift');
    $mailer = Swift_Mailer::newInstance($transport);
    $message = Swift_Message::newInstance()
        ->setSubject("Account recovery for $email at {$GLOBALS['site']['title']}")
        ->setFrom(array($GLOBALS['site']['webmaster'] => $GLOBALS['site']['title']))
        ->setTo(array($email))
        ->setBody($t->fetch('account/email-recovery.tmpl.php'));

    $result = $mailer->send($message);
    l("Account: Sent recovery message for account $email with result " . var_export($result, TRUE), "INFO");

    $db->clear_cache();
    return account_forgot_phase1_finish();
}

function account_forgot_phase1_finish() {
    global $t;
    $t->redirect('account/forgot?finish=1');
}