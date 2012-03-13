<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends Chintzy_Controller {

    public function __construct() {
        parent::__construct();
    }

	public function index($params = array()) {
//         echo "Index: ";
//         print_r($params);

        $this->check_cache();
        
        // If the page wasn't found in the cache, generate it.
        $this->load->model("Post_model");
        
        // Are we in single page mode or blog mode?
        if ($this->site["single_page_mode"]) {
            // Retrieve the page with ID 1
            $posts = $this->Post_model->get_page_by_id(1);
        } else {
            // Retrieve count of posts
            $posts = $this->Post_model->get_last_posts($this->site["posts_per_page"]);
        }
        
        $this->load->view("main/header");
        
        $this->load->view("main/footer");
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
	    
	    
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */