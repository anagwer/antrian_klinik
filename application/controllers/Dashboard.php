<?php

class Dashboard extends CI_Controller {
	public function __construct(){
		parent::__construct();
		if($this->session->login['role'] != 'kasir' && $this->session->login['role'] != 'admin') redirect();
		$this->data['aktif'] = 'dashboard';
		$this->load->model('M_menu', 'm_menu');
		$this->load->model('M_penjualan', 'm_penjualan');
		$this->load->model('M_users', 'm_users');
	}
	public function index(){
		$this->data['title'] = 'Halaman Dashboard';
		$this->data['jumlah_menu'] = $this->m_menu->jumlah();
		$this->data['jumlah_penjualan'] = $this->m_penjualan->jumlah();
		$this->data['jumlah_pengguna'] = $this->m_users->jumlah();
		$this->load->view('dashboard', $this->data);
	}
}