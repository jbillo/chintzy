<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Chintzy_Controller extends CI_Controller {

    protected $site = array();
    protected $page_num = 1;

    public function __construct() {
        parent::__construct();
        
        $this->site = $this->config->item("site");
        
        // Obtain current page number
        $page_num = $this->input->get("p");
        if (!$page_num or $page_num < 1 or !is_numeric($page_num)) {
            $page_num = 1;
        }
        
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'dummy'));
    }

}
