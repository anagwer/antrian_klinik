<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penjualan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            redirect('login');
        }
        $this->load->model('M_penjualan', 'm_penjualan');
        $this->load->model('M_iphone', 'm_iphone');
        $this->data['aktif'] = 'penjualan';
    }

    public function index() {
        $this->data['title'] = 'Data Penjualan';
        $this->data['all_penjualan'] = $this->m_penjualan->lihat();
        $this->data['all_iphone'] = $this->m_iphone->lihat();
        $this->data['no'] = 1;
        $this->data['next_id'] = $this->generate_id_penjualan();

        $this->load->view('penjualan/lihat', $this->data);
    }

    private function generate_id_penjualan() {
        // Query to find maximum PJ ID
        $this->db->select_max('id_penjualan');
        $this->db->like('id_penjualan', 'PJ');
        $query = $this->db->get('data_penjualan');
        $res = $query->row();

        if ($res && $res->id_penjualan) {
            $num = (int) substr($res->id_penjualan, 2);
            $next = $num + 1;
            return 'PJ' . str_pad($next, 3, '0', STR_PAD_LEFT);
        }
        return 'PJ001';
    }

    public function proses_tambah() {
        $id_penjualan = $this->input->post('id_penjualan');
        if (empty($id_penjualan)) {
            $id_penjualan = $this->generate_id_penjualan();
        }

        // Validate uniqueness
        if ($this->m_penjualan->lihat_id($id_penjualan)) {
            $this->session->set_flashdata('error', 'Kode Penjualan <strong>' . $id_penjualan . '</strong> sudah digunakan!');
            redirect('penjualan');
        }

        $login_user = $this->session->userdata('login');
        $data = [
            'id_penjualan' => $id_penjualan,
            'id_iphone' => $this->input->post('id_iphone'),
            'tanggal_transaksi' => $this->input->post('tanggal_transaksi'),
            'jumlah_terjual' => (int) $this->input->post('jumlah_terjual'),
            'id_user' => $login_user['id_user']
        ];

        if ($this->m_penjualan->tambah($data)) {
            $this->session->set_flashdata('success', 'Data Penjualan <strong>Berhasil</strong> Ditambahkan!');
        } else {
            $this->session->set_flashdata('error', 'Data Penjualan <strong>Gagal</strong> Ditambahkan!');
        }
        redirect('penjualan');
    }

    public function proses_ubah($id) {
        $login_user = $this->session->userdata('login');
        $data = [
            'id_iphone' => $this->input->post('id_iphone'),
            'tanggal_transaksi' => $this->input->post('tanggal_transaksi'),
            'jumlah_terjual' => (int) $this->input->post('jumlah_terjual'),
            'id_user' => $login_user['id_user']
        ];

        if ($this->m_penjualan->ubah($data, $id)) {
            $this->session->set_flashdata('success', 'Data Penjualan <strong>Berhasil</strong> Diubah!');
        } else {
            $this->session->set_flashdata('error', 'Data Penjualan <strong>Gagal</strong> Diubah!');
        }
        redirect('penjualan');
    }

    public function hapus($id) {
        if ($this->m_penjualan->hapus($id)) {
            $this->session->set_flashdata('success', 'Data Penjualan <strong>Berhasil</strong> Dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Data Penjualan <strong>Gagal</strong> Dihapus!');
        }
        redirect('penjualan');
    }
}