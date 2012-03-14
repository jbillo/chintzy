<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Post_model extends CI_Model {
    function __construct() {
        parent::__construct();
        
        $this->load->database();   
    }
    
    public function get_page_by_id($id) {
        $sql = "SELECT id, title, slug, text, created_on, updated_on, 
                    page, parent_id, display_in_nav 
                  FROM posts
                 WHERE id = ? AND page = ? AND parent_id = ? LIMIT 1";
        $params = array(1, 't', 0);
        
        $query = $this->db->query($sql, $params);
        $rows = $query->result();
        $this->format_dates($rows);
        return $rows[0];
    }
    
    public function get_last_posts($num_posts) {
        $fields = "p.id, p.title, p.slug, p.text, p.created_on, p.updated_on,
                    p.page";
        $sql = "SELECT $fields,
                    COUNT(c.id) AS comment_count 
                  FROM posts p
                LEFT JOIN comments c on c.post_id = p.id 
                 WHERE p.page = ? 
                 GROUP BY $fields 
                 ORDER BY p.created_on DESC LIMIT ?";
        $params = array('f', $num_posts);
        
        $query = $this->db->query($sql, $params);
        $rows = $query->result();
        $this->format_dates($rows);
        return $rows;
    }
    
    private function format_dates(&$rows) {
        foreach ($rows as &$row) {
            if ($row->created_on) {
                $row->f_created_on = strftime($this->config->item("date_format", "site"), strtotime($row->created_on));
            }
            
            if ($row->updated_on) {
                $row->f_updated_on = strftime($this->config->item("date_format", "site"), strtotime($row->updated_on));
            }
        }
    }
}