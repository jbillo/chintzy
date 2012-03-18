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
        if (count($rows) < 1) {
            return NULL;
        }

        return $rows[0];
    }

    public function get_post_slug($post_id) {
        $sql = "SELECT p.slug FROM posts p WHERE p.id = ? LIMIT 1";
        $params = array($post_id);

        $query = $this->db->query($sql, $params);
        $rows = $query->result();
        if (count($rows) < 1) {
            return NULL;
        }

        return $rows[0]->slug;
    }

    public function get_by_slug($slug) {
        $fields = "p.id, p.title, p.slug, p.text, p.created_on, p.updated_on,
                    p.page, p.parent_id, p.display_in_nav";

        $sql = "SELECT $fields,
                (SELECT COUNT(c.post_id) FROM comments c WHERE c.post_id = p.id AND c.status = ?) AS comment_count
                  FROM posts p
                 WHERE p.slug = ?
                 GROUP BY $fields LIMIT 1";
        $params = array('approved', $slug);

        $query = $this->db->query($sql, $params);
        $rows = $query->result();
        $this->format_dates($rows);
        if (count($rows) < 1) {
            return NULL;
        }
        return $rows[0];
    }

    public function get_num_posts() {
        $sql = "SELECT COUNT(id) AS cnt FROM posts WHERE page = ?";
        $params = array('f');

        $query = $this->db->query($sql, $params);
        $rows = $query->result();

        if (!$rows or count($rows) < 1) {
            return 0;
        }

        return $rows[0]->cnt;
    }

    public function get_last_posts($num_posts, $start_from = 0) {
        // start_from will be overwritten:
        $start_from = $start_from * $num_posts;

        $fields = "p.id, p.title, p.slug, p.text, p.created_on, p.updated_on,
                    p.page";
        $sql = "SELECT $fields,
                (SELECT COUNT(c.post_id) FROM comments c WHERE c.post_id = p.id AND c.status = ?) AS comment_count
                  FROM posts p
                 WHERE p.page = ?
                 GROUP BY $fields
                 ORDER BY p.created_on DESC LIMIT ? OFFSET ?";
        $params = array('approved', 'f', $num_posts, $start_from);

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