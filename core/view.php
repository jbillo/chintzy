<?php
require_once "{$BASE}/includes/init.php";
$core->url_navigate($GLOBALS['url']);

try {
    if (isset($query_slug) and $query_slug) {
        // Only retrieving one page
        $result = $dbq->one_page($query_slug);
    } else {
    	// Are we in single page mode or blog mode?
    	if ($GLOBALS['site']['view_settings']['homepage_blog']) {
    	   // Retrieve count of posts pulled by this query
    	    if (isset($page['num']) and $page['num'] > 1) {
               $t->assign('page_title', "Page {$page['num']} | {$GLOBALS['site']['title']}");
            }

    	    $page = paginate(
    	    	$dbq->blog_count(),
    	    	$GLOBALS['site']['view_settings']['posts_per_page'],
    	    	$page['num'],
    	    	'p'
    	    );

    	    // Retrieve all posts
            $result = $dbq->blog_posts($page['offset']);

    	    $page['ui'] = true;
    	} else {
    	    // Only retrieving one post
            $result = $dbq->home_page();
    	}
    }
} catch (Exception $e) {
    $t->add_script('js/post-retrieve-error.js');
    error_and_bail($e->getMessage() . $sql, $t->fetch('error/post-retrieve.tmpl.php'));
}

if (!$result or $result->RecordCount() == 0) {
    // This site has no content: check if we're on the homepage
    $t->display_end('nopost.tmpl.php', null, 'persistent');
}

try {
    // If a single record exists, we're on an individual post or page. Title the page and set up subpages.
    if ($result->RecordCount() == 1) {
        $single_post = true;
        $page_title = $result->fields['title'];
        $t->assign('page_title', "{$result->fields['title']} | {$GLOBALS['site']['title']}");
        // Also get the IDs and titles of all subpages under this category.
        // Only perform subpage retrieval on non-homepage items.
        if ($result->fields['id'] != 1) {
        	if ($result->fields['parent_id'] == 1) {
        	   $subpage_id = $result->fields['id'];
        	} else {
        	   $subpage_id = $result->fields['parent_id'];
        	}
        	$t->assign('subpages', $t->populate_page_links($subpage_id));
        }
    } else {
        $single_post = false;
    }
} catch (Exception $e) {
    error_and_bail($e->getMessage(), $t->fetch('error/post-retrieve.tmpl.php'));
}

while (!$result->EOF) {
    $t->assign('single_post', $single_post);
    $result->fields['created_on'] = date($t->date_format(), strtotime($result->fields['created_on']));
    $t->assign_array($result->fields);
    $t->display('post.tmpl.php', null, $result->fields['id']);
    $last_fields = $result->fields;
    $result->MoveNext();
}

// Check for pagination UI
if (isset($page['ui']) and $page['ui']) {
    $t->assign('page', $page);
    $t->display('pagination.tmpl.php');
}

// Again, if we're only viewing one post/page, and there are comments, pull them and display them.
if ($single_post and $last_fields['page'] == 'f') {
    if ($last_fields['comment_count'] > 0) {
    	// Determine how many comments we can display on this page.
    	// Keep the p=X variable consistent because we will only ever paginate comments
    	// on a single post.
		$page = paginate(
			$last_fields['comment_count'],
			$GLOBALS['site']['view_settings']['comments_per_page'],
			$page['num'],
			'p');

	    if (isset($page['num']) and $page['num'] > 1 and isset($page_title)) {
            // Reassign the page title for SEO
            $t->assign('page_title', "$page_title - page {$page['num']} | {$GLOBALS['site']['title']}");
	    }

		$result = $dbq->comments($last_fields['id'], $page['offset']);
        $t->display('comment/header.tmpl.php', null, $last_fields['id']);

        while (!$result->EOF) {
            $result->fields['created_on'] = date($t->date_format(), strtotime($result->fields['created_on']));
            $t->assign_array($result->fields);
            $t->display('comment/comment.tmpl.php', null, $result->fields['id']);
            $result->MoveNext();
        }

        $t->display('comment/footer.tmpl.php', null, $last_fields['id']);

        // Reassign and redisplay pagination UI for comments
        $page['anchor'] = '#comment';
    	$t->assign('page', $page);
    	$t->display('pagination.tmpl.php', null, $last_fields['id']);
    }

    // Add comment functions and validation checking
    $t->add_script('js/comment.js');
    // Require the reCAPTCHA library
    require_once "{$BASE}/libs/recaptcha/recaptchalib.php";
    // Output the comment field
    $t->display('comment/form.tmpl.php', null, $last_fields['id']);
}

$t->end();