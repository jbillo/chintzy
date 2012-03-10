<?php
define('NO_CACHE', true);
require_once 'includes/init.php';
$t->assign('page', 'edit');
$t->assign('page_title', "Edit Posts and Pages | {$GLOBALS['site']['title']}");
$t->require_permission('edit post');

// Add inline script for slugs that are invalid
add_restricted_slugs();

if (isset($query_slug)) {
    switch ($query_slug) {
    	case 'edit/view':
    		// Display a table of all active posts
    		$t->assign('page_title', "View All Posts and Pages | {$GLOBALS['site']['title']}");
    		$result = $db->get_all('posts', false, "ORDER BY created_on DESC");

    		$posts = array();
    		$t->assign('headers', array('ID', 'Title', 'Slug', 'Created', 'Updated', 'Page/Post', 'Parent', 'Actions'));
    		while (!$result->EOF) {
    			// Adjust timestamp format
    			$result->fields['created_on'] = date($t->date_format(), strtotime($result->fields['created_on']));
    			$result->fields['updated_on'] = date($t->date_format(), strtotime($result->fields['updated_on']));
    			$result->fields['page'] = ($result->fields['page'] == 't') ? 'Page' : 'Post';
                $posts[] = $result->fields;
    			$result->MoveNext();
    		}
    		$t->assign('posts', $posts);
    		$t->display('edit/view.tmpl.php');
    		$t->end();
        break;
        // Add and edit posts
        case 'edit/add-post': // fall through to post edit
        case 'edit/edit-post':
            if (count($_POST) == 0) {
                $t->redirect('edit');
            }

            // Check and validate all POST variables
            $user->verify_post_token();
            if (in_array($_POST['slug'], $RESERVED_SLUGS)) {
            	$t->add_script('js/post-save-error.js');
            	error_and_bail("The slug selected for this post is reserved.",
                    $t->fetch('error/post-save-slug.tmpl.php'), false);
                assign_fields();
                break;
            }

            try {
            	if (!$_POST['title'] or !$_POST['slug'] or !$_POST['text'] or !$_POST['parent_id'] or !is_numeric($_POST['parent_id']))
            	   throw new Exception("One or more required fields was missing.");

            	if ((!isset($_POST['is_page']) or !$_POST['is_page']) and $id != 1) {
                    $is_page = false;
            	} else {
                    $is_page = true;
            	}

            	if (!isset($_POST['display_in_nav']) or !$_POST['display_in_nav']) {
            	    $display_in_nav = false;
            	} else {
            	    $display_in_nav = true;
            	}

            	// Clean variables.
            	$clean = $core->htmlspecialchars($_POST);

            	// Check if another post exists with the same slug.
            	$check_slug = false;
            	if ($query_slug == 'edit/edit-post') {
            	    $check_slug = array('id' => new NotField($id));
            	}
            	$result = $db->get_one($clean['slug'], 'posts', 'slug', $check_slug, array('id'));
            	if ($result and $result->RecordCount() > 0) {
            	    throw new Exception("The slug <em>{$clean['slug']}</em>is already in use by another post.");
            	}

            	$purifier_config = $core->load_library('htmlpurifier');
                $purifier = new HTMLPurifier($purifier_config);

            	if ($query_slug == 'edit/add-post') {
                    $result = $db->add(array(
                        'title' => $clean['title'],
                        'slug' => $clean['slug'],
                        'text' => $purifier->purify($_POST['text']),
                        'parent_id' => $clean['parent_id'],
                        'page' => $is_page,
                        'display_in_nav' => $display_in_nav,
                    ), 'posts');
            	} else {
                    $result = $db->update(array(
                            'title' => $clean['title'],
                            'slug' => $clean['slug'],
                            'text' => $purifier->purify($_POST['text']),
                            'parent_id' => $clean['parent_id'],
                            'page' => $is_page,
                            'display_in_nav' => $display_in_nav,
                        ), $id, 'posts');
            	}

	            $t->clear_cache_dir();
                if ($query_slug == 'edit/add-post') {
                    $core->post_add_after();
                } else {
                    $core->post_edit_after();
                }
	            $t->redirect("edit/view", "post_saved");
            } catch (Exception $e) {
            	$msg = $e->getMessage();
            	if (is_a($e, 'DBException')) {
            		$msg .= "|" . $e->getDBError();
            	}
            	$t->add_script('js/post-save-error.js');
            	error_and_bail($msg, $t->fetch('error/post-save.tmpl.php'), false);
                assign_fields();
            }
        break;
        case 'edit/delete':
            // Confirm the deletion action
            if (!$user->has_permission('delete post')) {
                error_and_bail("Post: User does not have *delete post* permission.", "You do not have permission to delete posts.");
            }

            try {
                if (!isset($_POST['slug']))
                    throw new Exception("One or more required fields was missing.");

                $slug = $core->htmlspecialchars($_POST['slug']);
                $result = $db->get_one($slug, 'posts', 'slug', false, array('id', 'title'));

                if ($result) {
                    $t->assign('slug', $slug);
                    $t->assign_array($result->fields);
                } else {
                    throw new Exception("Could not find post with slug $slug");
                }
                $t->display_end('edit/delete.tmpl.php');
            } catch (Exception $e) {
                $msg = $e->getMessage();
            	if (is_a($e, 'DBException')) {
            		$msg .= "|" . $e->getDBError();
            	}
                $t->add_script('js/post-save-error.js');
                error_and_bail($msg, $t->fetch('error/post-delete.tmpl.php'), false);
            }
        break;
        case 'edit/ajax-delete-confirm':
            $ajax = true;
        case 'edit/delete-confirm':
            if (!isset($ajax)) {
                $ajax = false;
            }

            if (!isset($_POST['slug'])) {
                if ($ajax) {
                    echo '0'; exit;
                } else {
                    error_and_bail("Slug POST argument missing", "One or more required fields was missing.");
                }
            }

            $slug = $core->htmlspecialchars($_POST['slug']);
            $result = $db->get_one($slug, 'posts', 'slug', false, 'id');

            try {
                if (!$result or !$result->fields) {
                    throw new Exception("Could not find post with slug $slug");
                }

                if ($result->fields['id'] == 1) {
                    throw new Exception("Cannot delete the home page.");
                }

                // Since we have a foreign key constraint, delete all comments referencing this key.
                $del = $db->delete($result->fields['id'], 'comments', 'post_id');
                if (!$del) {
                	throw new DBException("Error deleting comments for post with slug $slug, ID {$result->fields['id']}");
                }

                $del = $db->delete($result->fields['id'], 'posts', 'id');
                if ($del) {
                    $t->clear_cache_dir();
                    if ($ajax) {
                        echo "1"; exit;
                    } else {
                        $t->redirect("edit/view", "post_deleted");
                    }
                } else {
                    throw new DBException("Error deleting post with slug $slug / ID $id");
                }
            } catch (Exception $e) {
                $msg = $e->getMessage();
            	if (is_a($e, 'DBException')) {
            		$msg .= "|" . $e->getDBError();
            	}
            	if ($ajax) {
            	    echo "0"; exit;
            	}
                $t->add_script('js/post-save-error.js');
                error_and_bail($msg, $t->fetch('error/post-delete.tmpl.php'), false);
            }
        break;
        default:
        	// Check if this is a valid slug - if so, go edit it
        	if (isset($GLOBALS['url'][0]) and $GLOBALS['url'][0] == 'edit') {
        	    $post_slug = substr($query_slug, 5); // take out 'edit/'
        	    $result = $db->get_one($post_slug, 'posts', 'slug');
                if ($result) {
                    $id = $result->fields['id'];
                } else {
                    $t->redirect('edit/view');
            	}
        	}
        break;
    }
}

// Separate out the homepage so we can optgroup it.
$homepage = $db->get_one(1, 'posts', 'id', false, array('title'));
$subpage_items = array(1 => $homepage->fields['title'] . " (Home)");

// Grab all the top-level categories for subpages (since we only want to allow one level)
$subpage_list = $db->get('posts',
    array('parent_id' => 1, 'page' => true, 'id' => new NotField(1)),
    array('id', 'title'), 'ORDER BY title ASC');
while (!$subpage_list->EOF) {
    $subpage_items[$subpage_list->fields['id']] = $subpage_list->fields['title'];
    $subpage_list->MoveNext();
}
$t->assign('subpage_items', $subpage_items);
$t->assign('optgroup_flag', false);

if ($id) {
    $result = $db->get_all('posts', array('id' => $id), '', 1, -1);
    $t->assign('id', $id);
    $t->assign('form_action', 'edit-post');
    $result->fields['is_page'] = ($result->fields['page'] == 't');
    $t->assign_array($result->fields);
} else {
    if (isset($_GET['new']) and $_GET['new'] == 'page') {
        $t->assign('is_page', TRUE);
    }
    $t->assign('form_action', 'add-post');
}

$t->add_script('libs/ckeditor/ckeditor.js');
$t->add_script('libs/ckeditor/adapters/jquery.js');
$t->add_script('js/edit.js');
$t->display('editor.tmpl.php');
$t->end();

function assign_fields() {
    global $t, $core;

    $clean = $core->htmlspecialchars($_POST);

    $t->assign('title', $clean['title']);
    $t->assign('slug', $clean['slug']);
    $t->assign('text', $clean['text']);
    $t->assign('parent_id', $clean['parent_id']);
    $t->assign('is_page', isset($clean['is_page']) ? true : false);
}

function add_restricted_slugs() {
    global $VALID_ACTIONS, $RESERVED_SLUGS;
    global $t;
    $restricted = array_merge($VALID_ACTIONS, $RESERVED_SLUGS);
    $str = 'var restricted_slugs = [';
    foreach ($restricted as $slug)
        $str .= "'$slug',";
    $str .= ' ];';
    $t->add_script($str, true);
}