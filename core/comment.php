<?php
function validate_comment($post_id) {
    global $t;
    global $db;
    global $core;
    // check for proper POST values
    // check for input fields
    if (!isset($_POST['comment_text']) or !$_POST['comment_text'] or
        !isset($_POST['user_name']) or !$_POST['user_name'] or
        !isset($_POST['user_email']) or !$_POST['user_email'])
        error_and_bail("Missing one or more required comment fields", "One or more required fields was not provided.");

    // validate email
    if (filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL) === false)
        error_and_bail("Email {$_POST['user_email']} did not pass validation filter", "The email address provided was invalid.");

    // validate URL
    $user_url = isset($_POST['user_url']) ? $_POST['user_url'] : '';
    if ($user_url and !filter_var($user_url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED))
        $user_url = "http://$user_url";

    // Initiate strict HTML filter for comments.
    $purifier_config = $core->load_library('htmlpurifier');
    $purifier_config->set('HTML.AllowedElements', array(
        'a', 'em', 'strong', 'abbr', 'code', 'blockquote'
    ));
    $purifier_config->set('HTML.AllowedAttributes', array(
        'href'
    ));
    $purifier = new HTMLPurifier($purifier_config);

    $params = array();
    $params['post_id'] = $post_id;
    $params['text'] = $purifier->purify($_POST['comment_text']);
    $params['user_name'] = htmlspecialchars($_POST['user_name']);
    $params['user_email'] = htmlspecialchars($_POST['user_email']);
    $params['user_url'] = htmlspecialchars($user_url);

    return $params;
}

// Since we have a CAPTCHA check here, and initiating the code for a session wouldn't be awesome here,
// ignore the token request and just check validity.
define('NO_TOKEN', true);
define('NO_CACHE', true);
require_once 'includes/init.php';

$ajax = false;
$t->add_script('js/comment.js');

switch ($query_slug) {
    case 'comment/add':
        if (!isset($_POST['slug'])) {
            error_and_bail("No POST slug submitted for comment", "You need to specify a post to comment on.");
        }

        // check for reCAPTCHA response
        $core->load_library('recaptcha');
        $response = recaptcha_check_answer($GLOBALS['site']['recaptcha']['private_key'],
                                           $_SERVER['REMOTE_ADDR'],
                                           $_POST['recaptcha_challenge_field'],
                                           $_POST['recaptcha_response_field']);

        if (!$response->is_valid)
            error_and_bail("Invalid reCAPTCHA response: {$response->error}", $GLOBALS['c_msg']['recaptcha_invalid']);

        // Ensure the post slug is valid.
        $result = $db->get_one($_POST['slug'], 'posts', 'slug', array('page' => false), 'id');
        if ($result and $result->fields['id']) {
            $post_id = $result->fields['id'];
        } else {
            error_and_bail("Post ID not found for {$_POST['slug']}", "The post you have specified no longer exists on the site.");
        }

        $params = validate_comment($post_id);
        try {
            // params can be modified so that the comment can have its status changed.
            $core->comment_submit($params);
            $result = $db->add($params, 'comments');
        } catch (Exception $e) {
            error_and_bail("Could not save comment: {$e->getMessage()}", "Your comment could not be saved. Please try again later.");
        }

        // Clear cache and redirect
        $t->clear_cache_dir();
        $t->redirect($_POST['slug'], 'comment_saved');
    break;
	case 'comment/view/new':
	case 'comment/view/approved':
	case 'comment/view/spam':
	case 'comment/view/rejected':
		$filter = substr($query_slug, strrpos($query_slug, '/') + 1);
    case 'comment/view':
		if (!isset($filter)) {
			$filter = '';
		}
        $t->require_permission('edit comment');

        // Prevent navigation bar from appearing
        $t->assign('pages', false);
        $t->assign('subpages', false);

        // Display a table of all active posts
        $result = $dbq->comment_admin_view($filter);

        $posts = array();
        $comments = array();

        $t->assign('headers', array('Post', 'Created', 'Name', 'Email', 'URL', 'Status', 'Actions'));
		if ($result) {
	   		while (!$result->EOF) {
                // Adjust timestamp format
                $result->fields['created_on'] = date($t->date_format(), strtotime($result->fields['created_on']));
                $comments[] = $result->fields;
                $result->MoveNext();
        	}
		}
        $t->assign('comments', $comments);
        $t->assign('filter', $filter);
        $t->display('comment/view.tmpl.php');
        $t->end();
    break;
    case 'comment/edit':
        $t->require_permission('edit comment');
        if (!isset($params) or !$params[1]) {
            $t->redirect("");
        }

        // Parse ID for comment to edit
        $id = $params[1];

        if (!is_numeric($id)) {
            throw new Exception("Invalid ID specified");
        }

        $result = $db->get_all('comments', array('id' => $id));

        if (!$result) {
            throw new Exception("Could not locate comment $id");
        }

        $t->assign('comment', $result->fields);
        $t->display('comment/edit.tmpl.php');
        $t->end();
    break;
    case 'comment/save':
        $t->require_permission('edit comment');
        if (!isset($_POST['id']) or !$_POST['id']) {
            $t->redirect("");
        }

        // Parse information and submit the comment.
        // We're not allowing modification of post_id in an edit.
        $params = validate_comment();
        unset($params['post_id']);
        $params['id'] = $_POST['id'];
        $params['status'] = $_POST['status'];

        $result = $db->update($params, $params['id'], 'comments');
        if (!$result) {
            throw new Exception("Could not edit comment: " . $db->last_error());
        }
        $t->redirect("comment/view");
    break;
    case 'comment/delete-ajax':
        $ajax = true;
        if (!$user->has_permission('delete comment')) {
            echo '0'; exit;
        }
        // fall through to delete
    case 'comment/delete':
        if (!$ajax) {
            $t->require_permission('delete comment');
        }
        // Parse ID from post arguments
        if (!isset($_POST['id']) or !$_POST['id']) {
            if ($ajax) {
                echo "0"; exit;
            } else {
                $t->redirect("");
            }
        }

        $id = $_POST['id'];
        $result = $db->get_one($id, 'comments', 'id');
        if (!$result) {
            if ($ajax) {
                echo "0"; exit;
            } else {
                error_and_bail("Comment ID $id not found", "The specified comment could not be located.");
            }
        }

        $result = $db->delete($id, 'comments');

        if (!$result) {
            if ($ajax) {
                echo "0"; exit;
            } else {
                error_and_bail("Comment ID $id could not be deleted", "The specified comment could not be deleted.");
            }
        }

        $t->clear_cache_dir();

        if ($ajax) {
            echo "1"; exit;
        } else {
            $t->redirect('comment/view', 'comment_deleted');
        }
    break;
    default:
        $t->redirect("");
    break;
}