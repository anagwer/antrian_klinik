<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        
        // If already logged in, redirect to beranda
        if ($this->session->userdata('login')) {
            redirect('beranda');
        }
        $this->load->model('M_user', 'm_user');
    }

    public function index() {
        $this->load->view('login');
    }

    public function proses_login() {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $get_user = $this->m_user->lihat_username($username);

        if ($get_user) {
            if (password_verify($password, $get_user->password)) {
                 $session = [
                    'id_user' => $get_user->id_user,
                    'username' => $get_user->username,
                    'role' => $get_user->role,
                    'jam_masuk' => date('H:i:s')
                ];

                $this->session->set_userdata('login', $session);
                $this->session->set_flashdata('success', '<strong>Login</strong> Berhasil!');
                redirect('beranda');
            } else {
                $this->session->set_flashdata('error', 'Password Salah!');
                redirect('login');
            }
        } else {
            $this->session->set_flashdata('error', 'Username Salah!');
            redirect('login');
        }
    }
}