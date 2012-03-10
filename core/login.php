<?php
define('NO_CACHE', true);
require_once "{$BASE}/includes/init.php";
$t->require_ssl();

$t->assign('from_login', TRUE);

// Check if the user is logged in already
if ($user->current_user())
    $t->redirect('?c_msg=already_logged_in');
    
$core->load_library('recaptcha');
    
$t->common_template()->login_form($user);
$t->end();