<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comment extends Chintzy_Controller {
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
            )
        );

        $this->view_plain_footer();
    }

    public function submit() {
        $this->load->library('form_validation');

        $post_id = $this->input->post("post_id");
        if (!$post_id or !is_numeric($post_id) or $post_id < 1) {
            show_error('The post specified for this comment was invalid. It may have been moved or deleted.');
            return;
        }

        $this->form_validation->set_rules('comment_name', 'Your name', 'required');

		if ($this->form_validation->run() == FALSE) {
		    // fail out
		    $this->load->helper("recaptcha");
            $this->load->view("comment/new", array(
                "post_id" => 0,
                "recaptcha" => recaptcha_html(),
            ));
		} else {
		    // success; redirect
			// $this->load->view('formsuccess');
		}
    }

}