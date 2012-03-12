<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
* The entire reason for Chintzy to exist. This class should be loaded
* and used well before any database lookups or model accessors.
*
* So how does this thing work?
* 1. User submits a request for a page (eg: the root of the site.)
* 2. Pagecache takes the hash (user-specified, but MD5 by default) of
*    the complete URL and then checks to see if there's an existing
*    cache entry or file on the filesystem. If so, it will serve that
*    content; if not, returns FALSE.
* 3. If a page has to be regenerated freshly, Pagecache will store the
*    output generated in cache or filesystem and use it for later
*    storage.
*/

class Pagecache {
    // Default hash algorithm : MD5
    private $hash_algorithm = "md5";
    
    // Default CodeIgniter (APC/file fallback) cache lifetime
    private $cache_expiry = 3600; // 1 hour for APC
    
    // CodeIgniter reference object
    private $CI = NULL;
    
    // App path, because I'm lazy
    private $APPPATH = APPPATH;

    public function __construct() {
        parent::__construct();
        
        $this->CI =& get_instance();
        
        $this->config->load("cache", TRUE);
        $this->hash_algorithm = $this->config->item("hash_algorithm", "cache");
    }
    
    public function get($key) {
        $APPPATH = $this->APPPATH;
        // Check APC/CodeIgniter built in cache functions first.
        $hashed_key = hash($this->hash_algorithm, $key);
        $cache_data = $this->CI->cache->get($hashed_key);
        
        if ($cache_data) {
            // Serve out this content
            $this->CI->output->set_output("$cache_data<!-- APC -->");
            return TRUE;
        }
        
        // Now check the caching directory in the filesystem
        if (file_exists("$APPPATH/cache/$hashed_key.html")) {
            $output = file_get_contents("$APPPATH/cache/$hashed_key.html");
            $this->CI->cache->save($hashed_key, $output, $this->cache_expiry);
            $this->CI->output->set_output("$output<!-- filecache -->");
            return TRUE;
        }
        
        // Return false since we don't actually have any cached data
        return FALSE;
    }
    
    public function set($key, $data) {
        $APPPATH = $this->APPPATH;
        $hashed_key = hash($this->hash_algorithm, $key);
        $this->CI->cache->save($hashed_key, $data, $this->cache_expiry);
        file_put_contents("$APPPATH/cache/$hashed_key.html", $data);
    }

}

/* End of file Someclass.php */