<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_penjualan extends CI_Model {
    protected $_table = 'data_penjualan';

    public function lihat() {
        $this->db->select('data_penjualan.*, iphone.nama_tipe, user.username');
        $this->db->from($this->_table);
        $this->db->join('iphone', 'iphone.id_iphone = data_penjualan.id_iphone', 'left');
        $this->db->join('user', 'user.id_user = data_penjualan.id_user', 'left');
        $this->db->order_by('tanggal_transaksi', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function jumlah() {
        $query = $this->db->get($this->_table);
        return $query->num_rows();
    }

    public function lihat_id($id) {
        $query = $this->db->get_where($this->_table, ['id_penjualan' => $id]);
        return $query->row();
    }

    public function tambah($data) {
        return $this->db->insert($this->_table, $data);
    }

    public function ubah($data, $id) {
        $this->db->where(['id_penjualan' => $id]);
        return $this->db->update($this->_table, $data);
    }

    public function hapus($id) {
        return $this->db->delete($this->_table, ['id_penjualan' => $id]);
    }

    // Get chronologically sorted monthly sales for a specific iPhone
    public function get_monthly_sales($id_iphone) {
        $this->db->select("DATE_FORMAT(tanggal_transaksi, '%Y-%m') as bulan_tahun, SUM(jumlah_terjual) as total_terjual");
        $this->db->from($this->_table);
        $this->db->where('id_iphone', $id_iphone);
        $this->db->group_by("DATE_FORMAT(tanggal_transaksi, '%Y-%m')");
        $this->db->order_by("bulan_tahun", "ASC");
        $query = $this->db->get();
        return $query->result();
    }
}