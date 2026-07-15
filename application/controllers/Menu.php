<?php

use Dompdf\Dompdf;

class Menu extends CI_Controller{
	public function __construct(){
		parent::__construct();
		if($this->session->login['role'] != 'kasir' && $this->session->login['role'] != 'admin') redirect();
		$this->data['aktif'] = 'menu';
		$this->load->model('M_menu', 'm_menu');
		$this->load->model('M_kategori', 'm_kategori');
	}

	public function index(){
		$this->data['title'] = 'Data Menu';
		$this->data['all_menu'] = $this->m_menu->lihat();
		$this->data['all_kategori'] = $this->m_kategori->lihat();
		$this->data['no'] = 1;

		$this->load->view('menu/lihat', $this->data);
	}

	public function tambah(){
		if ($this->session->login['role'] == 'kasir'){
			$this->session->set_flashdata('error', 'Tambah data hanya untuk admin!');
			redirect('penjualan');
		}

		$this->data['title'] = 'Tambah Menu';
		$this->data['all_kategori'] = $this->m_kategori->lihat();

		$this->load->view('menu/tambah', $this->data);
	}

	public function proses_tambah(){
		if ($this->session->login['role'] == 'kasir'){
			$this->session->set_flashdata('error', 'Tambah data hanya untuk admin!');
			redirect('penjualan');
		}
		$config['upload_path'] = './files/menu/';
		$config['allowed_types'] = 'jpg|jpeg|png|webp';
		$config['file_name'] = uniqid();
		$config['max_size'] = 2048;

		$this->load->library('upload', $config);
		$foto = '';
		if ($this->upload->do_upload('foto')) {
			$uploadData = $this->upload->data();
			$foto = $uploadData['file_name'];
		}

		$data = [
			'nama_menu' => $this->input->post('nama_menu'),
			'deskripsi' => $this->input->post('deskripsi'),
			'kategori'  => $this->input->post('kategori'),
			'harga'     => $this->input->post('harga'),
			'is_aktif'  => $this->input->post('is_aktif') ? 1 : 0,
			'foto'      => $foto
		];

		if($this->m_menu->tambah($data)){
			$this->session->set_flashdata('success', 'Data Menu <strong>Berhasil</strong> Ditambahkan!');
			redirect('menu');
		} else {
			$this->session->set_flashdata('error', 'Data Menu <strong>Gagal</strong> Ditambahkan!');
			redirect('menu');
		}
	}
	
	public function ubah($id){
		if ($this->session->login['role'] == 'kasir'){
			$this->session->set_flashdata('error', 'Ubah data hanya untuk admin!');
			redirect('penjualan');
		}

		$this->data['title'] = 'Ubah Menu';
		$this->data['menu'] = $this->m_menu->lihat_id($id);
		$this->data['all_kategori'] = $this->m_kategori->lihat();

		$this->load->view('menu/ubah', $this->data);
	}

	public function proses_ubah($id){
		if ($this->session->login['role'] == 'kasir'){
			$this->session->set_flashdata('error', 'Ubah data hanya untuk admin!');
			redirect('penjualan');
		}
		$data = [
			'nama_menu' => $this->input->post('nama_menu'),
			'deskripsi' => $this->input->post('deskripsi'),
			'kategori'  => $this->input->post('kategori'),
			'harga'     => $this->input->post('harga'),
			'is_aktif'  => $this->input->post('is_aktif') ? 1 : 0,
		];

		if (!empty($_FILES['foto']['name'])) {
			$config['upload_path'] = './files/menu/';
			$config['allowed_types'] = 'jpg|jpeg|png|webp';
			$config['file_name'] = uniqid();
			$config['max_size'] = 2048;

			$this->load->library('upload', $config);

			if ($this->upload->do_upload('foto')) {
				$uploadData = $this->upload->data();
				$data['foto'] = $uploadData['file_name'];

				$menu = $this->m_menu->lihat_id($id);
				if (!empty($menu->foto) && file_exists('./files/menu/' . $menu->foto)) {
					unlink('./files/menu/' . $menu->foto);
				}
			} else {
				$this->session->set_flashdata('error', $this->upload->display_errors());
				redirect('menu');
			}
		}
	
		if ($this->m_menu->ubah($data, $id)){
			$this->session->set_flashdata('success', 'Data Menu <strong>Berhasil</strong> Diubah!');
			redirect('menu');
		} else {
			$this->session->set_flashdata('error', 'Data Menu <strong>Gagal</strong> Diubah!');
			redirect('menu');
		}
	}
	

	public function hapus($id){
		if ($this->session->login['role'] == 'kasir'){
			$this->session->set_flashdata('error', 'Hapus data hanya untuk admin!');
			redirect('penjualan');
		}
		
		$menu = $this->m_menu->lihat_id($id);
		if($this->m_menu->hapus($id)){
			if (!empty($menu->foto) && file_exists('./files/menu/' . $menu->foto)) {
				unlink('./files/menu/' . $menu->foto);
			}
			$this->session->set_flashdata('success', 'Data Menu <strong>Berhasil</strong> Dihapus!');
			redirect('menu');
		} else {
			$this->session->set_flashdata('error', 'Data Menu <strong>Gagal</strong> Dihapus!');
			redirect('menu');
		}
	}
}