<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Chintzy_Controller extends CI_Controller {

    protected $site = array();
    protected $page_num = 1;
    protected $params = array();

    public function __construct($controller_args = NULL) {
        parent::__construct();
        
        $this->params = $controller_args;
        $this->site = $this->config->item("site");
        
        // Obtain current page number
        $page_num = $this->input->get("p");
        if (!$page_num or $page_num < 1 or !is_numeric($page_num)) {
            $page_num = 1;
        }
        
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'dummy'));
    }
    
    public function __destruct() {
        $this->pagecache->set($this->uri->uri_string(), $this->output->get_output());
    }
    
    public function check_cache() {
        // If we have the cache, load that.
        if ($this->pagecache->get($this->uri->uri_string())) {
            return;
        }
    }    

}
