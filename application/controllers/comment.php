<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comment extends Chintzy_Controller {
    private $recaptcha_error = FALSE;

    public function __construct() {
        parent::__construct();
    }

    public function form($post_id) {
        // Disable pagecache because we have a CSRF token in the form
        $this->pagecache->enabled = FALSE;
        $this->load->helper(array("recaptcha", "form"));

        $this->view_plain_header();

        $this->load->view("comment/new", array(
                "post_id" => $post_id,
                "recaptcha" => recaptcha_html(),
                "recaptcha_error" => $this->recaptcha_error,
            )
        );

        $this->view_plain_footer();
    }

    public function submit() {
        $post_id = $this->input->post("post_id");
        if (!$post_id or !is_numeric($post_id) or $post_id < 1) {
            show_error('The post specified for this comment was invalid. It may have been moved or deleted.');
            return;
        }
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters("", "<br />");

        $this->form_validation->set_rules('comment_name', 'Your name', 'trim|required');
        $this->form_validation->set_rules('comment_email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('comment_url', 'Website', 'trim|prep_url');
        $this->form_validation->set_rules('comment_text', 'Comment', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
		    // fail out
		    return $this->form($post_id);
		}

		// Check reCAPTCHA for success or failure
		$this->load->helper("recaptcha");
        if (!recaptcha_check(
            $this->input->post("recaptcha_challenge_field"),
            $this->input->post("recaptcha_response_field"))) {
            $this->recaptcha_error = TRUE;
            return $this->form($post_id);
        }

        // Use Akismet, if available, to check and score comment for spam.
        $this->load->helper("akismet");
        $this->load->model("Post_model");

        $slug = $this->Post_model->get_post_slug($post_id);
        if (!$slug) {
            show_error("The post specified for this comment was invalid.");
            return;
        }

        $akismet_result = akismet_check(
            $this->input->post("comment_name"),
            $this->input->post("comment_email"),
            $this->input->post("comment_url"),
            $this->input->post("comment_text"),
            $slug
        );

        // Submit the comment.
        $this->load->model("Comment_model");
        $result = $this->Comment_model->add_comment(
            $post_id,
            $this->input->post("comment_name"),
            $this->input->post("comment_email"),
            $this->input->post("comment_url"),
            $this->input->post("comment_text"),
            $akismet_result
        );

        if (!$result) {
            show_error("A problem occurred submitting your comment. Please try again later.");
            return;
        }

        // success; redirect
        $this->load->view("comment/submit_success", array("slug" => $slug));
        // $this->load->view('formsuccess');
    }

}