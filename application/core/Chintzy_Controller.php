<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Chintzy_Controller extends CI_Controller {

    protected $site = array();
    protected $page_num = 1;
    protected $params = array();
    
    // Data variables to always be passed
    protected $header = array();
    protected $footer = array();

    public function __construct($controller_args = NULL) {
        parent::__construct();
        
        $this->params = $controller_args;
        $this->site = $this->config->item("site");
        
        $this->header["title"] = $this->site["site_title"];
        $this->header["site_title"] = $this->site["site_title"];
        
        // Obtain current page number
        $this->page_num = $this->input->get("p");
        if (!$this->page_num or $this->page_num < 1 or !is_numeric($this->page_num)) {
            $this->page_num = 1;
        }
        
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'dummy'));

        if (!$this->site["global_caching"]) {
            $this->pagecache->enabled = FALSE;
        }
    }
    
    public function __destruct() {
        if (!$this->pagecache->hit()) {
            $this->pagecache->set($_SERVER['REQUEST_URI'], $this->output->get_output());
        }        
    }
    
    public function check_cache() {
        // If we have the cache, load that.
        return ($this->pagecache->get($_SERVER['REQUEST_URI']));
    }
    
    public function view_main_header() {
        $this->load->view("main/header", $this->header);
    }
    
    public function view_main_footer() {
        $this->load->view("main/footer", $this->footer);
    }

}
