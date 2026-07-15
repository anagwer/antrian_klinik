<?php
class Kategori extends CI_Controller {
	public function __construct(){
		parent::__construct();
		if($this->session->login['role'] != 'admin') redirect();
		$this->data['aktif'] = 'kategori';
		$this->load->model('M_kategori', 'm_kategori');
	}

	public function index(){
		$this->data['title'] = 'Data Kategori Menu';
		$this->data['all_kategori'] = $this->m_kategori->lihat();
		$this->data['no'] = 1;
		$this->load->view('kategori/lihat', $this->data);
	}

	public function tambah(){
		$this->data['title'] = 'Tambah Kategori';
		$this->load->view('kategori/tambah', $this->data);
	}

	public function proses_tambah(){
		$data = [
			'nama_kategori' => $this->input->post('nama_kategori')
		];

		if($this->m_kategori->tambah($data)){
			$this->session->set_flashdata('success', 'Data Kategori <strong>Berhasil</strong> Ditambahkan!');
			redirect('kategori');
		} else {
			$this->session->set_flashdata('error', 'Data Kategori <strong>Gagal</strong> Ditambahkan!');
			redirect('kategori');
		}
	}

	public function ubah($id){
		$this->data['title'] = 'Ubah Kategori';
		$this->data['kategori'] = $this->m_kategori->lihat_id($id);
		$this->load->view('kategori/ubah', $this->data);
	}

	public function proses_ubah($id){
		$data = [
			'nama_kategori' => $this->input->post('nama_kategori')
		];

		if($this->m_kategori->ubah($data, $id)){
			$this->session->set_flashdata('success', 'Data Kategori <strong>Berhasil</strong> Diubah!');
			redirect('kategori');
		} else {
			$this->session->set_flashdata('error', 'Data Kategori <strong>Gagal</strong> Diubah!');
			redirect('kategori');
		}
	}

	public function hapus($id){
		if($this->m_kategori->hapus($id)){
			$this->session->set_flashdata('success', 'Data Kategori <strong>Berhasil</strong> Dihapus!');
			redirect('kategori');
		} else {
			$this->session->set_flashdata('error', 'Data Kategori <strong>Gagal</strong> Dihapus!');
			redirect('kategori');
		}
	}
}
