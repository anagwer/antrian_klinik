<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_prediksi extends CI_Model {
    protected $_table = 'prediksi';

    public function lihat() {
        $this->db->select('prediksi.*, iphone.nama_tipe, user.username');
        $this->db->from($this->_table);
        $this->db->join('iphone', 'iphone.id_iphone = prediksi.id_iphone', 'left');
        $this->db->join('user', 'user.id_user = prediksi.id_user', 'left');
        $this->db->order_by('prediksi.created_at', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function jumlah() {
        $query = $this->db->get($this->_table);
        return $query->num_rows();
    }

    public function lihat_id($id) {
        $query = $this->db->get_where($this->_table, ['id_prediksi' => $id]);
        return $query->row();
    }

    public function tambah($data) {
        $this->db->insert($this->_table, $data);
        return $this->db->insert_id();
    }

    public function hapus($id) {
        return $this->db->delete($this->_table, ['id_prediksi' => $id]);
    }

    public function get_last_prediction_by_iphone($id_iphone) {
        $this->db->select('*');
        $this->db->from($this->_table);
        $this->db->where('id_iphone', $id_iphone);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row();
    }

    public function get_all_for_month($bulan_prediksi) {
        $this->db->select('prediksi.*, iphone.nama_tipe');
        $this->db->from($this->_table);
        $this->db->join('iphone', 'iphone.id_iphone = prediksi.id_iphone', 'left');
        $this->db->where('bulan_prediksi', $bulan_prediksi);
        $query = $this->db->get();
        return $query->result();
    }
}
