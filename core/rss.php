<?php
require_once "{$BASE}/includes/init.php";

try {
    $result = $db->get_all('posts', array('page' => false),
        "ORDER BY created_on DESC", $GLOBALS['site']['view_settings']['posts_per_page']);
} catch (Exception $e) {
    // Error processing RSS...
    // TODO put in error handling here
}

$t->set_format('rss');
$t->assign('ROOTURI_NOSSL', $ROOTURI_NOSSL);
$t->display('rss/header.tmpl.php');

if ($result->RecordCount() > 0) {
    while (!$result->EOF) {
        $fixed_fields = array();
        foreach ($result->fields as $field => $val) {
            $fixed_fields[$field] = $val;
        }
        $t->assign_array($fixed_fields);
        $t->display('rss/item.tmpl.php');
        $result->MoveNext();
    }
}

$t->display_end('rss/footer.tmpl.php');