<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends Chintzy_Controller {
    public function __construct() {
        parent::__construct();
        
        // Disable page cache reading and writing for all admin pages
        $this->pagecache->enabled = FALSE;
    }

    public function index() {
        
    }
    
    public function clear_cache($tier = NULL) {
        $this->load->helper("url");
        $this->pagecache->clear($tier);
        $this->view_main_header();
        $this->load->view("admin/clear_cache");
        $this->view_main_footer();
    }

}