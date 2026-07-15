<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            redirect('login');
        }
        $login_user = $this->session->userdata('login');
        if ($login_user['role'] !== 'admin') {
            $this->session->set_flashdata('error', 'Akses ke Setting & User hanya untuk Admin!');
            redirect('beranda');
        }
        $this->load->model('M_user', 'm_user');
        $this->config->load('forecasting');
        $this->data['aktif'] = 'setting';
    }

    public function index() {
        $this->data['title'] = 'Pengaturan & User';
        $this->data['all_users'] = $this->m_user->lihat();
        $this->data['no'] = 1;

        // Current parameter configuration
        $this->data['ma_period'] = $this->config->item('ma_period');
        $this->data['mape_green'] = $this->config->item('mape_green');
        $this->data['mape_yellow'] = $this->config->item('mape_yellow');
        $this->data['safety_factor'] = $this->config->item('safety_factor');

        $this->load->view('setting', $this->data);
    }

    // Save parameter configurations to config/forecasting.php
    public function simpan_parameter() {
        $ma_period = (int) $this->input->post('ma_period');
        $mape_green = (int) $this->input->post('mape_green');
        $mape_yellow = (int) $this->input->post('mape_yellow');
        $safety_factor = (float) $this->input->post('safety_factor');

        $content = "<?php\n";
        $content .= "defined('BASEPATH') OR exit('No direct script access allowed');\n\n";
        $content .= "\$config['ma_period'] = " . $ma_period . ";\n";
        $content .= "\$config['mape_green'] = " . $mape_green . ";\n";
        $content .= "\$config['mape_yellow'] = " . $mape_yellow . ";\n";
        $content .= "\$config['safety_factor'] = " . $safety_factor . ";\n";

        if (file_put_contents(APPPATH . 'config/forecasting.php', $content)) {
            $this->session->set_flashdata('success', 'Parameter Peramalan <strong>Berhasil</strong> Diperbarui!');
        } else {
            $this->session->set_flashdata('error', 'Parameter Peramalan <strong>Gagal</strong> Diperbarui!');
        }
        redirect('setting');
    }

    // User CRUD: Add new user
    public function proses_tambah_user() {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        // Validate username length
        if (strlen($username) > 15) {
            $this->session->set_flashdata('error', 'Username maksimal 15 karakter!');
            redirect('setting');
        }

        // Validate unique username
        if ($this->m_user->lihat_username($username)) {
            $this->session->set_flashdata('error', 'Username <strong>' . $username . '</strong> sudah digunakan!');
            redirect('setting');
        }

        $role = $this->input->post('role') ? $this->input->post('role') : 'karyawan';
        $data = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role
        ];

        if ($this->m_user->tambah($data)) {
            $this->session->set_flashdata('success', 'User Baru <strong>Berhasil</strong> Ditambahkan!');
        } else {
            $this->session->set_flashdata('error', 'User Baru <strong>Gagal</strong> Ditambahkan!');
        }
        redirect('setting');
    }

    // User CRUD: Edit user
    public function proses_ubah_user($id) {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        
        $user_id = (int) $id;

        // Validate username length
        if (strlen($username) > 15) {
            $this->session->set_flashdata('error', 'Username maksimal 15 karakter!');
            redirect('setting');
        }

        // Validate unique username if changed
        $current = $this->m_user->lihat_id($user_id);
        if ($current->username !== $username && $this->m_user->lihat_username($username)) {
            $this->session->set_flashdata('error', 'Username <strong>' . $username . '</strong> sudah digunakan oleh user lain!');
            redirect('setting');
        }

        $role = $this->input->post('role');
        $data = [
            'username' => $username
        ];
        if (!empty($role)) {
            $data['role'] = $role;
        }

        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($this->m_user->ubah($data, $user_id)) {
            // Update session if editing self
            $login_sess = $this->session->userdata('login');
            if ($login_sess['id_user'] == $user_id) {
                $login_sess['username'] = $username;
                if (!empty($role)) {
                    $login_sess['role'] = $role;
                }
                $this->session->set_userdata('login', $login_sess);
            }
            $this->session->set_flashdata('success', 'Data User <strong>Berhasil</strong> Diubah!');
        } else {
            $this->session->set_flashdata('error', 'Data User <strong>Gagal</strong> Diubah!');
        }
        redirect('setting');
    }

    // User CRUD: Delete user
    public function hapus_user($id) {
        $user_id = (int) $id;

        // Prevent self-deletion
        $login_sess = $this->session->userdata('login');
        if ($login_sess['id_user'] == $user_id) {
            $this->session->set_flashdata('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif!');
            redirect('setting');
        }

        if ($this->m_user->hapus($user_id)) {
            $this->session->set_flashdata('success', 'User <strong>Berhasil</strong> Dihapus!');
        } else {
            $this->session->set_flashdata('error', 'User <strong>Gagal</strong> Dihapus!');
        }
        redirect('setting');
    }
}
