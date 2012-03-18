<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comment_model extends CI_Model {
    function __construct() {
        parent::__construct();

        $this->load->database();
    }

    public function get_comments_on($post_id) {
        $sql = "SELECT c.id, c.text, c.created_on, c.updated_on, c.user_name, c.user_url
            FROM comments c
            WHERE c.post_id = ? AND c.status = ?
            ORDER BY c.created_on";
        $params = array($post_id, "approved");

        $query = $this->db->query($sql, $params);
        $rows = $query->result();
        $this->format_dates($rows);

        return $rows;
    }

    public function add_comment($post_id, $name, $email, $url, $text, $status) {
        $sql = "INSERT INTO comments
            (post_id, created_on, updated_on, user_name, user_email, user_url, text, status)
            VALUES
            (?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, ?, ?, ?, ?)";

        $params = array(
            $post_id,
            $name,
            $email,
            $url,
            $text,
            $status
        );

        return $this->db->query($sql, $params);
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