<?php

class Users extends CI_Controller {
	public function __construct(){
		parent::__construct();
		if($this->session->login['role'] != 'kasir' && $this->session->login['role'] != 'admin') redirect();
		$this->data['aktif'] = 'users';
		$this->load->model('M_users', 'm_users');
	}

	public function index(){
		$this->data['title'] = 'Data Pengguna';
		$this->data['all_users'] = $this->m_users->lihat();
		$this->data['no'] = 1;

		$this->load->view('users/lihat', $this->data);
	}

	public function tambah(){
		$this->data['title'] = 'Tambah Pengguna';

		$this->load->view('users/tambah', $this->data);
	}

	public function proses_tambah(){
		$data = [
			'username' => $this->input->post('username'),
			'password_hash' => password_hash($this->input->post('password_hash'), PASSWORD_DEFAULT),
			'nama_lengkap' => $this->input->post('nama_lengkap'),
			'no_telp' => $this->input->post('no_telp'),
			'alamat' => $this->input->post('alamat'),
			'status' => $this->input->post('status') ? $this->input->post('status') : 'aktif',
		];

		if($this->m_users->tambah($data)){
			$this->session->set_flashdata('success', 'Data Pengguna <strong>Berhasil</strong> Ditambahkan!');
			redirect('users');
		} else {
			$this->session->set_flashdata('error', 'Data Pengguna <strong>Gagal</strong> Ditambahkan!');
			redirect('users');
		}
	}

	public function ubah($id){
		$this->data['title'] = 'Ubah Pengguna';
		$this->data['user'] = $this->m_users->lihat_id($id);

		$this->load->view('users/ubah', $this->data);
	}

	public function proses_ubah($id){
		$data = [
			'username' => $this->input->post('username'),
			'nama_lengkap' => $this->input->post('nama_lengkap'),
			'no_telp' => $this->input->post('no_telp'),
			'alamat' => $this->input->post('alamat'),
			'status' => $this->input->post('status'),
		];

		if($this->input->post('password_hash')){
			$data['password_hash'] = password_hash($this->input->post('password_hash'), PASSWORD_DEFAULT);
		}

		if($this->m_users->ubah($data, $id)){
			$this->session->set_flashdata('success', 'Data Pengguna <strong>Berhasil</strong> Diubah!');
			redirect('users');
		} else {
			$this->session->set_flashdata('error', 'Data Pengguna <strong>Gagal</strong> Diubah!');
			redirect('users');
		}
	}

	public function hapus($id){
		if($this->m_users->hapus($id)){
			$this->session->set_flashdata('success', 'Data Pengguna <strong>Berhasil</strong> Dihapus!');
			redirect('users');
		} else {
			$this->session->set_flashdata('error', 'Data Pengguna <strong>Gagal</strong> Dihapus!');
			redirect('users');
		}
	}

}
