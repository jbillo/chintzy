<?php
define('NO_CACHE', true);
require_once "{$BASE}/includes/init.php";
$t->require_ssl();

// Check if the user is logged out already
if (!$user->current_user())
    $t->redirect('', 'already_logged_out');

$user->logout();
$t->redirect('', 'logged_out');