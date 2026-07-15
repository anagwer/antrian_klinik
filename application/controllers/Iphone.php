<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Iphone extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            redirect('login');
        }
        $this->load->model('M_iphone', 'm_iphone');
        $this->data['aktif'] = 'iphone';
    }

    public function index() {
        $this->data['title'] = 'Master iPhone';
        $this->data['all_iphone'] = $this->m_iphone->lihat();
        $this->data['no'] = 1;

        $this->load->view('iphone/lihat', $this->data);
    }

    public function proses_tambah() {
        $id_iphone = $this->input->post('id_iphone');
        $nama_tipe = $this->input->post('nama_tipe');

        // Check if ID already exists
        if ($this->m_iphone->lihat_id($id_iphone)) {
            $this->session->set_flashdata('error', 'ID iPhone <strong>' . $id_iphone . '</strong> sudah terdaftar!');
            redirect('iphone');
        }

        $data = [
            'id_iphone' => $id_iphone,
            'nama_tipe' => $nama_tipe
        ];

        if ($this->m_iphone->tambah($data)) {
            $this->session->set_flashdata('success', 'Tipe iPhone <strong>Berhasil</strong> Ditambahkan!');
        } else {
            $this->session->set_flashdata('error', 'Tipe iPhone <strong>Gagal</strong> Ditambahkan!');
        }
        redirect('iphone');
    }

    public function proses_ubah($id) {
        $data = [
            'nama_tipe' => $this->input->post('nama_tipe')
        ];

        if ($this->m_iphone->ubah($data, $id)) {
            $this->session->set_flashdata('success', 'Tipe iPhone <strong>Berhasil</strong> Diubah!');
        } else {
            $this->session->set_flashdata('error', 'Tipe iPhone <strong>Gagal</strong> Diubah!');
        }
        redirect('iphone');
    }

    public function hapus($id) {
        $this->db->trans_start();
        $this->db->delete('data_penjualan', ['id_iphone' => $id]);
        $this->db->delete('prediksi', ['id_iphone' => $id]);
        $success = $this->m_iphone->hapus($id);
        $this->db->trans_complete();

        if ($success && $this->db->trans_status()) {
            $this->session->set_flashdata('success', 'Tipe iPhone <strong>Berhasil</strong> Dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Tipe iPhone <strong>Gagal</strong> Dihapus!');
        }
        redirect('iphone');
    }
}
