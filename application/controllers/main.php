<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends Chintzy_Controller {
    public function __construct() {
        parent::__construct();
    }

	public function index($params = array()) {
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
            $total_posts = $this->Post_model->get_num_posts();
            $max_pages = ceil($total_posts / $this->site["posts_per_page"]);
                        
            if ($this->page_num > $max_pages) {
                return $this->error_404("page_number_too_high");
            }
            $posts = $this->Post_model->get_last_posts($this->site["posts_per_page"], ($this->page_num - 1));
            if ($this->page_num != 1) {
                $this->header["title"] = "Page {$this->page_num}{$this->site["title_separator"]}{$this->header["title"]}";
            }
        }
        
        $this->load->helper("url");
        
        if ($this->site["single_page_mode"]) {
            $this->single_post($posts[0]);
            return;
        } 
        
        $this->view_main_header();
        
        foreach ($posts as $post) {
            $this->load->view("post", array("post" => $post));
        }

        if ($this->page_num < $max_pages) {
            $this->load->view("main/page_nav", array(
                "prev_page" => ($this->page_num != 1),
                "prev_page_num" => ($this->page_num - 1),
                "next_page" => ($this->page_num != $max_pages),
                "next_page_num" => ($this->page_num + 1),
            ));
        }
        
       $this->view_main_footer();
	}
	
	private function single_post($post) {
	    $this->load->helper("url");
        $this->view_main_header();	
	    $this->load->view("post", array("post" => $post));
	    $this->load->view("comment/view", $post);
	    $this->view_main_footer();
	}
	
	public function catchall() {
	    $args = func_get_args();
	    
	    // Recompress out the arguments and see if there is a matching post.
	    $test_slug = implode("/", $args);
	    $this->load->model("Post_model");
	    
	    $post = $this->Post_model->get_by_slug($test_slug);
	    if (!$post) {
            return $this->error_404("post_not_found");
	    } else {
	        // Display the post
	        $this->single_post($post);
	        return;    
	    }
	}
	
	public function error_404($params = array()) {
	    echo "404: ";
	    print_r($params);
	    
	    $this->check_cache();
	    
	    $this->view_main_header();
        $this->view_main_footer();
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */