<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends Chintzy_Controller {

    public function __construct() {
        parent::__construct();
    }

	public function index($params = array()) {
//         echo "Index: ";
//         print_r($params);

        if ($this->check_cache()) {
            return;
        }
        
        // If the page wasn't found in the cache, generate it.
        $this->load->model("Post_model");
        $posts = array();
        
        // Are we in single page mode or blog mode?
        if ($this->site["single_page_mode"]) {
            // Retrieve the page with ID 1
            $posts = array($this->Post_model->get_page_by_id(1));
            $this->header["title"] = "{$posts[0]->title}{$this->site["title_separator"]}{$this->header["title"]}";
        } else {
            // Retrieve count of posts
            $posts = $this->Post_model->get_last_posts($this->site["posts_per_page"]);
        }
        
        $this->view_main_header();
        foreach ($posts as $post) {
            $this->load->view("post", array("post" => $post));
        }
        if (count($posts) == 1) {
            // Load comment details
            // $this->load->view("comment/view", $post);
        }
        $this->view_main_footer();
	}
	
	public function catchall() {
	    echo "Catchall: ";
	    $args = func_get_args();
	    print_r($args);
	    
	    // Now
	    
	    
	}
	
	public function error_404($params = array()) {
	    echo "404: ";
	    print_r($params);
	    
	    $this->check_cache();
	    
	    $this->load->view("main/header");
	    $this->load->view("main/footer");
	    
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */