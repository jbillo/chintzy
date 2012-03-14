<?php

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

define('CACHE_HIT_NONE', 0);
define('CACHE_HIT_DISK', 1);
define('CACHE_HIT_MEMORY', 2);

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
    private $APPPATH = "";
    
    // Whether the caching mechanism should be enabled for this page
    public $enabled = TRUE;
    
    // Whether the cache successfully 'hit' an item
    private $cache_hit = CACHE_HIT_NONE;
    
    // Generation timestamp
    private $timestamp = NULL;

    public function __construct($app_path = '') {
        if (function_exists("get_instance")) {
            $this->CI =& get_instance();
        
            $this->CI->config->load("cache");
            $this->hash_algorithm = $this->CI->config->item("hash_algorithm", "cache");            
            $this->max_size = $this->CI->config->item("max_size", "cache");
        }
    
        $this->APPPATH = defined('APPPATH') ? APPPATH : $app_path;
        $this->timestamp = time();
    }
    
    public function hit() {
        return $this->cache_hit;
    }
    
    public function get($key) {
        if (!$this->enabled) {
            return FALSE;
        }
    
        // Check APC/CodeIgniter built in cache functions first.
        $hashed_key = hash($this->hash_algorithm, $key);
        $cache_data = $this->CI ? $this->CI->cache->get($hashed_key) : apc_fetch($hashed_key);
        
        if ($cache_data) {
            // Serve out this content
            $this->cache_hit = CACHE_HIT_MEMORY;
            if ($this->CI) {
                $this->CI->output->set_output("$cache_data");    
                return TRUE;
            }
            
            // Return first element in array if it's an APC hit
            $cache_data = $cache_data[0];
            return $cache_data;
        }
        
        // Now check the caching directory in the filesystem
        if (file_exists("{$this->APPPATH}cache/$hashed_key.html")) {
            $output = file_get_contents("{$this->APPPATH}cache/$hashed_key.html");
            $this->cache_hit = CACHE_HIT_DISK;
            if ($this->CI) {
                $this->CI->cache->save($hashed_key, $output, $this->cache_expiry);
                $this->CI->output->set_output("$output");
                return TRUE;
            }
            
            // If we're not running inside CI, return the output.
            return $output;
        }
        
        // Return false since we don't actually have any cached data
        $this->cache_hit = FALSE;
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
        
        // Cache data according to its tier. Higher tier data is faster and more easily returned.
        // Lower tier data is on slower storage, and the base level is "not currently cached".
        
        // If the cache hit the disk, but nothing higher tier than that, store in memory.
        if ($this->cache_hit <= CACHE_HIT_DISK) {
            $this->CI->cache->save($hashed_key, "$data<!-- mem {$this->timestamp} -->", $this->cache_expiry);
        }
        
        // If the cache didn't hit, store on disk.
        if ($this->cache_hit <= CACHE_HIT_NONE) {
            file_put_contents("{$this->APPPATH}cache/$hashed_key.html", "$data<!-- disk {$this->timestamp} -->");
        }
        
        // The cache chain at the end of an output file will appear like this:
        /*
        * No end comment: file was served from database/PHP generation and will be cached on
        *    the next round in both memory and disk tiers
        *
        * mem comment: file was served from memory cache tier
        * disk comment: file was served from disk cache tier and will be cached on the 
        *    next round in memory tier
        * disk + mem comment: file was served from memory cache tier, but originally retrieved
        *    from the disk cache. Timestamps will indicate as much.
        */
    
    }
    
    public function clear($key = NULL, $tier = NULL) {
        if ($key) {
            $hashed_key = hash($this->hash_algorithm, $key);
            // Clear specific cache elements
            if (!$tier or $tier == CACHE_HIT_MEMORY) {
                $this->CI->cache->delete($hashed_key);
            }
            if (!$tier or $tier == CACHE_HIT_DISK) {
                @unlink("{$this->APPPATH}cache/$hashed_key.html");
            }
            return;
        }

        // Clear entire cache
        if (!$tier or $tier == CACHE_HIT_MEMORY) {
            $this->CI->cache->clean();
        }
        
        if (!$tier or $tier == CACHE_HIT_DISK) {
            if ($handle = opendir("{$this->APPPATH}cache")) {
                while (FALSE !== ($file = readdir($handle))) {
                    if ($file === "." or $file === ".." or substr($file, -5) !== ".html") {
                        // Do nothing and don't go up one level
                    } else {
                        // Delete cache file
                        @unlink("{$this->APPPATH}cache/$file");
                    }
                }
                closedir($handle);
            }
        }
    }

}

/* End of file Someclass.php */