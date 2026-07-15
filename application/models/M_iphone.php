<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_iphone extends CI_Model {
    protected $_table = 'iphone';

    public function lihat() {
        $query = $this->db->get($this->_table);
        return $query->result();
    }

    public function jumlah() {
        $query = $this->db->get($this->_table);
        return $query->num_rows();
    }

    public function lihat_id($id) {
        $query = $this->db->get_where($this->_table, ['id_iphone' => $id]);
        return $query->row();
    }

    public function tambah($data) {
        return $this->db->insert($this->_table, $data);
    }

    public function ubah($data, $id) {
        $this->db->where(['id_iphone' => $id]);
        return $this->db->update($this->_table, $data);
    }

    public function hapus($id) {
        return $this->db->delete($this->_table, ['id_iphone' => $id]);
    }
}
