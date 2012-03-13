<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends Chintzy_Controller {
    public function __construct() {
        parent::__construct();
        
        // Disable page cache reading and writing for all admin pages
        $this->pagecache->enabled = FALSE;
    }

    public function index() {
        
    }
    
    public function clear_cache() {
        return $this->pagecache->clear();
    }

}