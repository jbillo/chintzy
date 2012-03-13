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
    
    // Default maximum cache size
    private $max_size = 1048576; // 1MB
    
    // CodeIgniter reference object
    private $CI = NULL;
    
    // App path, because I'm lazy
    private $APPPATH = APPPATH;
    
    // Whether the caching mechanism should be enabled for this page
    public $enabled = TRUE;

    public function __construct() {
        $this->CI =& get_instance();
        
        $this->CI->config->load("cache");
        $this->hash_algorithm = $this->CI->config->item("hash_algorithm", "cache");
        $this->max_size = $this->CI->config->item("max_size", "cache");
    }
    
    public function get($key) {
        if (!$this->enabled) {
            return FALSE;
        }
    
        // Check APC/CodeIgniter built in cache functions first.
        $hashed_key = hash($this->hash_algorithm, $key);
        $cache_data = $this->CI->cache->get($hashed_key);
        
        if ($cache_data) {
            // Serve out this content
            $this->CI->output->set_output("$cache_data");
            return TRUE;
        }
        
        // Now check the caching directory in the filesystem
        if (file_exists("{$this->APPPATH}cache/$hashed_key.html")) {
            $output = file_get_contents("{$this->APPPATH}cache/$hashed_key.html");
            $this->CI->cache->save($hashed_key, $output, $this->cache_expiry);
            $this->CI->output->set_output("$output");
            return TRUE;
        }
        
        // Return false since we don't actually have any cached data
        return FALSE;
    }
    
    public function set($key, $data = "") {
        if (!$this->enabled) {
            return FALSE;
        }
        
        // Check if the output is too large to be cached.
        if (strlen($data) > $this->max_size) {
            return FALSE;
        }
    
        $hashed_key = hash($this->hash_algorithm, $key);
        $this->CI->cache->save($hashed_key, "$data<!-- APC -->", $this->cache_expiry);
        file_put_contents("{$this->APPPATH}cache/$hashed_key.html", "$data<!-- filecache -->");
    }
    
    public function clear($key = NULL) {
        if ($key) {
            $hashed_key = hash($this->hash_algorithm, $key);
            // Clear specific cache elements
            $this->CI->cache->delete($hashed_key);
            unlink("{$this->APPPATH}cache/$hashed_key.html");
        } else {
            // Clear entire cache
            $this->CI->cache->clean();
            if ($handle = opendir("{$this->APPPATH}cache")) {
                while (FALSE !== ($file = readdir($handle))) {
                    if ($file === "." or $file === ".." or substr($file, -5) !== ".html") {
                        // Do nothing and don't go up one level
                    } else {
                        // Delete cache file
                        unlink("{$this->APPPATH}cache/$file");
                    }
                }
                closedir($handle);
            }
        }
    }

}

/* End of file Someclass.php */