<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_rekomendasi extends CI_Model {
    protected $_table = 'rekomendasi';

    public function lihat() {
        $this->db->select('rekomendasi.*, prediksi.bulan_prediksi, prediksi.nilai_sma, prediksi.nilai_mape, iphone.nama_tipe, iphone.id_iphone');
        $this->db->from($this->_table);
        $this->db->join('prediksi', 'prediksi.id_prediksi = rekomendasi.id_prediksi', 'inner');
        $this->db->join('iphone', 'iphone.id_iphone = prediksi.id_iphone', 'inner');
        $this->db->order_by('rekomendasi.peringkat', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function jumlah() {
        $query = $this->db->get($this->_table);
        return $query->num_rows();
    }

    public function tambah($data) {
        return $this->db->insert($this->_table, $data);
    }

    public function hapus($id) {
        return $this->db->delete($this->_table, ['id_rekomendasi' => $id]);
    }

    public function clear_by_prediksi($id_prediksi) {
        return $this->db->delete($this->_table, ['id_prediksi' => $id_prediksi]);
    }

    // Get recommendations for the most recent predicted month
    public function get_latest_recommendations() {
        // Find the latest prediction month in the database
        $this->db->select('bulan_prediksi');
        $this->db->from('prediksi');
        $this->db->order_by('bulan_prediksi', 'DESC');
        $this->db->limit(1);
        $sub = $this->db->get()->row();
        
        if (!$sub) return [];

        $latest_month = $sub->bulan_prediksi;

        $this->db->select('rekomendasi.*, prediksi.bulan_prediksi, prediksi.nilai_sma, prediksi.nilai_mape, prediksi.periode_n, iphone.nama_tipe, iphone.id_iphone');
        $this->db->from($this->_table);
        $this->db->join('prediksi', 'prediksi.id_prediksi = rekomendasi.id_prediksi', 'inner');
        $this->db->join('iphone', 'iphone.id_iphone = prediksi.id_iphone', 'inner');
        $this->db->where('prediksi.bulan_prediksi', $latest_month);
        $this->db->order_by('rekomendasi.peringkat', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }
}
