<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // CORS Headers to allow Vite development server to communicate
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }

        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
    }

    /**
     * Send response helper
     */
    protected function json_response($data, $status_code = 200) {
        $this->output
             ->set_status_header($status_code)
             ->set_content_type('application/json', 'utf-8')
             ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
             ->_display();
        exit;
    }

    /**
     * Parse raw JSON input if sent by client (useful for modern React axios/fetch)
     */
    protected function get_json_input() {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?: array();
    }

    // ==========================================
    // AUTHENTICATION
    // ==========================================

    public function login() {
        $input = $this->get_json_input();
        $username = isset($input['username']) ? $input['username'] : $this->input->post('username');
        $password = isset($input['password']) ? $input['password'] : $this->input->post('password');

        if (empty($username) || empty($password)) {
            $this->json_response(['status' => 'error', 'message' => 'Username dan password wajib diisi.'], 400);
        }

        $user = $this->db->get_where('users', ['username' => $username])->row();

        if ($user && password_verify($password, $user->password)) {
            $session_data = [
                'id_user' => $user->id_user,
                'username' => $user->username,
                'role' => $user->role,
                'nama_lengkap' => $user->nama_lengkap,
                'logged_in' => TRUE
            ];
            $this->session->set_userdata($session_data);
            
            // Return user info excluding password hash
            unset($user->password);
            $this->json_response(['status' => 'success', 'message' => 'Login berhasil.', 'user' => $user]);
        } else {
            $this->json_response(['status' => 'error', 'message' => 'Username atau password salah.'], 401);
        }
    }

    public function session() {
        if ($this->session->userdata('logged_in')) {
            $this->json_response([
                'status' => 'success',
                'user' => [
                    'id_user' => $this->session->userdata('id_user'),
                    'username' => $this->session->userdata('username'),
                    'role' => $this->session->userdata('role'),
                    'nama_lengkap' => $this->session->userdata('nama_lengkap')
                ]
            ]);
        } else {
            $this->json_response(['status' => 'error', 'message' => 'Sesi tidak aktif.'], 401);
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        $this->json_response(['status' => 'success', 'message' => 'Logout berhasil.']);
    }

    // ==========================================
    // CLINIC MASTER DATA
    // ==========================================

    public function poliklinik() {
        $this->db->where('status', 'aktif');
        $poli = $this->db->get('poliklinik')->result();
        $this->json_response(['status' => 'success', 'data' => $poli]);
    }

    public function dokter() {
        $id_poli = $this->input->get('id_poli');
        $this->db->select('dokter.*, poliklinik.nama_poli');
        $this->db->from('dokter');
        $this->db->join('poliklinik', 'dokter.id_poli = poliklinik.id_poli');
        $this->db->where('dokter.status', 'aktif');
        
        if (!empty($id_poli)) {
            $this->db->where('dokter.id_poli', $id_poli);
        }
        
        $dokter = $this->db->get()->result();
        $this->json_response(['status' => 'success', 'data' => $dokter]);
    }

    // ==========================================
    // PATIENT QUEUE REGISTRATION & DETAILS
    // ==========================================

    public function queue_register() {
        $input = $this->get_json_input();
        
        // Gather post data, standard or JSON format
        $nik = trim(isset($input['nik']) ? $input['nik'] : $this->input->post('nik'));
        $nama = trim(isset($input['nama']) ? $input['nama'] : $this->input->post('nama'));
        $tempat_lahir = trim(isset($input['tempat_lahir']) ? $input['tempat_lahir'] : $this->input->post('tempat_lahir'));
        $tanggal_lahir = trim(isset($input['tanggal_lahir']) ? $input['tanggal_lahir'] : $this->input->post('tanggal_lahir'));
        $jenis_kelamin = isset($input['jenis_kelamin']) ? $input['jenis_kelamin'] : $this->input->post('jenis_kelamin');
        $alamat = trim(isset($input['alamat']) ? $input['alamat'] : $this->input->post('alamat'));
        $no_hp = trim(isset($input['no_hp']) ? $input['no_hp'] : $this->input->post('no_hp'));
        $keluhan = trim(isset($input['keluhan']) ? $input['keluhan'] : $this->input->post('keluhan'));
        $id_poli = isset($input['id_poli']) ? $input['id_poli'] : $this->input->post('id_poli');
        $id_dokter = isset($input['id_dokter']) ? $input['id_dokter'] : $this->input->post('id_dokter');
        $tipe_pendaftaran = isset($input['tipe_pendaftaran']) ? $input['tipe_pendaftaran'] : $this->input->post('tipe_pendaftaran');
        $foto_ktp = isset($input['foto_ktp']) ? $input['foto_ktp'] : $this->input->post('foto_ktp'); // filename or base64

        // If base64 file string is sent, decode and save it
        if (preg_match('/^data:image\/(\w+);base64,/', $foto_ktp, $type)) {
            $data = substr($foto_ktp, strpos($foto_ktp, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, etc

            if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                $this->json_response(['status' => 'error', 'message' => 'Tipe file foto KTP tidak didukung.'], 400);
            }
            $data = base64_decode($data);

            if ($data === false) {
                $this->json_response(['status' => 'error', 'message' => 'Dekode foto KTP gagal.'], 400);
            }

            $dir = './uploads/ktp/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = 'ktp_' . $nik . '_' . time() . '.' . $type;
            file_put_contents($dir . $filename, $data);
            $foto_ktp = $filename;
        }

        // Validation
        if (empty($nik) || empty($nama) || empty($id_poli) || empty($id_dokter) || empty($keluhan)) {
            $this->json_response(['status' => 'error', 'message' => 'NIK, Nama, Poliklinik, Dokter, dan Keluhan wajib diisi.'], 400);
        }

        if (empty($tipe_pendaftaran)) {
            $tipe_pendaftaran = 'online';
        }

        // Step 1: Manage Patient Master Record (Add or Update)
        $existing_pasien = $this->db->get_where('pasien', ['nik' => $nik])->row();
        $pasien_data = [
            'nik' => $nik,
            'nama' => $nama,
            'tempat_lahir' => $tempat_lahir,
            'tanggal_lahir' => $tanggal_lahir ?: null,
            'jenis_kelamin' => $jenis_kelamin,
            'alamat' => $alamat,
            'no_hp' => $no_hp
        ];
        if (!empty($foto_ktp)) {
            $pasien_data['foto_ktp'] = $foto_ktp;
        }

        if ($existing_pasien) {
            $this->db->where('id_pasien', $existing_pasien->id_pasien);
            $this->db->update('pasien', $pasien_data);
            $id_pasien = $existing_pasien->id_pasien;
        } else {
            $this->db->insert('pasien', $pasien_data);
            $id_pasien = $this->db->insert_id();
        }

        // Step 2: FIFO Queue Calculation
        $today = date('Y-m-d');
        
        // Fetch the clinic details (mainly to get the Queue Prefix code e.g. A, B, etc.)
        $poli = $this->db->get_where('poliklinik', ['id_poli' => $id_poli])->row();
        if (!$poli) {
            $this->json_response(['status' => 'error', 'message' => 'Poliklinik tidak ditemukan.'], 404);
        }
        $kode_antrian = $poli->kode_antrian;

        // Fetch the doctor
        $dokter = $this->db->get_where('dokter', ['id_dokter' => $id_dokter])->row();
        if (!$dokter) {
            $this->json_response(['status' => 'error', 'message' => 'Dokter tidak ditemukan.'], 404);
        }

        // Check if patient already registered for this poliklinik today and is still waiting/calling
        $already_queued = $this->db->where([
            'nik_pasien' => $nik,
            'id_poli' => $id_poli,
            'tanggal_antrian' => $today
        ])->group_start()
          ->where('status', 'menunggu')
          ->or_where('status', 'dipanggil')
          ->group_end()
          ->get('antrian')
          ->row();

        if ($already_queued) {
            $this->json_response([
                'status' => 'error',
                'message' => 'Pasien dengan NIK ini sudah terdaftar dalam antrean aktif poliklinik ini hari ini.',
                'data' => $already_queued
            ], 400);
        }

        // Calculate next queue sequence for today for this Poliklinik (FIFO queue index)
        $this->db->select_max('nomor_urut');
        $this->db->where([
            'id_poli' => $id_poli,
            'tanggal_antrian' => $today
        ]);
        $row = $this->db->get('antrian')->row();
        $next_urut = $row->nomor_urut ? $row->nomor_urut + 1 : 1;
        $nomor_antrian = $kode_antrian . '-' . sprintf('%02d', $next_urut);

        // Calculate estimated wait time (FIFO Queue Length * 10 mins)
        // Find count of patients currently 'menunggu' before this patient (since we register at current timestamp)
        $this->db->where([
            'id_poli' => $id_poli,
            'status' => 'menunggu',
            'tanggal_antrian' => $today
        ]);
        $waiting_count = $this->db->count_all_results('antrian');
        $estimasi_menit = $waiting_count * 10;

        // Create the queue row
        $queue_data = [
            'nik_pasien' => $nik,
            'nama_pasien' => $nama,
            'jenis_kelamin' => $jenis_kelamin,
            'tanggal_lahir' => $tanggal_lahir ?: null,
            'alamat' => $alamat,
            'no_hp' => $no_hp,
            'foto_ktp' => $foto_ktp ?: ($existing_pasien ? $existing_pasien->foto_ktp : null),
            'keluhan' => $keluhan,
            'id_poli' => $id_poli,
            'id_dokter' => $id_dokter,
            'nomor_antrian' => $nomor_antrian,
            'nomor_urut' => $next_urut,
            'status' => 'menunggu',
            'tipe_pendaftaran' => $tipe_pendaftaran,
            'tanggal_antrian' => $today,
            'waktu_daftar' => date('Y-m-d H:i:s')
        ];

        if ($this->db->insert('antrian', $queue_data)) {
            $id_antrian = $this->db->insert_id();
            
            // Format printable ticket data
            $ticket = [
                'id_antrian' => $id_antrian,
                'nomor_antrian' => $nomor_antrian,
                'nomor_urut' => $next_urut,
                'nik' => $nik,
                'nama' => $nama,
                'nama_poli' => $poli->nama_poli,
                'nama_dokter' => $dokter->nama_dokter,
                'keluhan' => $keluhan,
                'estimasi_tunggu_menit' => $estimasi_menit,
                'tanggal_antrian' => $today,
                'waktu_daftar' => $queue_data['waktu_daftar'],
                'tipe_pendaftaran' => $tipe_pendaftaran
            ];

            $this->json_response(['status' => 'success', 'message' => 'Pendaftaran antrean berhasil.', 'data' => $ticket]);
        } else {
            $this->json_response(['status' => 'error', 'message' => 'Gagal mendaftarkan antrean.'], 500);
        }
    }

    // ==========================================
    // QUEUE MONITOR & MANAGEMENT (FIFO ORDERING)
    // ==========================================

    public function queue_list() {
        $today = date('Y-m-d');
        $status = $this->input->get('status');
        $id_poli = $this->input->get('id_poli');

        $this->db->select('antrian.*, poliklinik.nama_poli, dokter.nama_dokter');
        $this->db->from('antrian');
        $this->db->join('poliklinik', 'antrian.id_poli = poliklinik.id_poli');
        $this->db->join('dokter', 'antrian.id_dokter = dokter.id_dokter');
        $this->db->where('antrian.tanggal_antrian', $today);

        if (!empty($status)) {
            $this->db->where('antrian.status', $status);
        }
        if (!empty($id_poli)) {
            $this->db->where('antrian.id_poli', $id_poli);
        }

        // Ordered by waktu_daftar ASC to respect FIFO (First In First Out)
        $this->db->order_by('antrian.waktu_daftar', 'ASC');
        
        $queues = $this->db->get()->result();
        $this->json_response(['status' => 'success', 'data' => $queues]);
    }

    /**
     * Call next patient based strictly on FIFO
     */
    public function queue_call_next() {
        // Only admin/petugas/dokter can call next
        $input = $this->get_json_input();
        $id_poli = isset($input['id_poli']) ? $input['id_poli'] : $this->input->post('id_poli');

        if (empty($id_poli)) {
            $this->json_response(['status' => 'error', 'message' => 'Poliklinik harus dipilih.'], 400);
        }

        $today = date('Y-m-d');

        // FIFO query: Find the oldest 'menunggu' patient for this poli today
        $this->db->select('antrian.*, poliklinik.nama_poli, dokter.nama_dokter');
        $this->db->from('antrian');
        $this->db->join('poliklinik', 'antrian.id_poli = poliklinik.id_poli');
        $this->db->join('dokter', 'antrian.id_dokter = dokter.id_dokter');
        $this->db->where([
            'antrian.id_poli' => $id_poli,
            'antrian.status' => 'menunggu',
            'antrian.tanggal_antrian' => $today
        ]);
        $this->db->order_by('antrian.waktu_daftar', 'ASC'); // FIFO core logic!
        $this->db->limit(1);
        
        $next_patient = $this->db->get()->row();

        if ($next_patient) {
            // Step 1: Update any currently 'dipanggil' patient for this Poliklinik to 'selesai' or keep them.
            // In standard clinics, when a new patient is called, the previous one gets marked 'selesai' automatically.
            $this->db->where([
                'id_poli' => $id_poli,
                'status' => 'dipanggil',
                'tanggal_antrian' => $today
            ]);
            $this->db->update('antrian', ['status' => 'selesai']);

            // Step 2: Set the next patient's status to 'dipanggil' and log served time
            $this->db->where('id_antrian', $next_patient->id_antrian);
            $this->db->update('antrian', [
                'status' => 'dipanggil',
                'waktu_dilayani' => date('Y-m-d H:i:s')
            ]);

            // Re-fetch to return latest status
            $next_patient->status = 'dipanggil';
            $next_patient->waktu_dilayani = date('Y-m-d H:i:s');

            $this->json_response([
                'status' => 'success',
                'message' => 'Memanggil antrean berikutnya (FIFO).',
                'data' => $next_patient
            ]);
        } else {
            $this->json_response([
                'status' => 'info',
                'message' => 'Antrean untuk Poliklinik ini sudah habis.'
            ], 200);
        }
    }

    /**
     * Recall/re-announce a patient
     */
    public function queue_recall() {
        $input = $this->get_json_input();
        $id_antrian = isset($input['id_antrian']) ? $input['id_antrian'] : $this->input->post('id_antrian');

        if (empty($id_antrian)) {
            $this->json_response(['status' => 'error', 'message' => 'ID Antrean tidak ditemukan.'], 400);
        }

        $this->db->select('antrian.*, poliklinik.nama_poli, dokter.nama_dokter');
        $this->db->from('antrian');
        $this->db->join('poliklinik', 'antrian.id_poli = poliklinik.id_poli');
        $this->db->join('dokter', 'antrian.id_dokter = dokter.id_dokter');
        $this->db->where('antrian.id_antrian', $id_antrian);
        $queue = $this->db->get()->row();

        if ($queue) {
            // Update status back to 'dipanggil' to re-broadcast on TV screen
            $this->db->where('id_antrian', $id_antrian);
            $this->db->update('antrian', ['status' => 'dipanggil']);
            $queue->status = 'dipanggil';

            $this->json_response(['status' => 'success', 'message' => 'Panggilan antrean diulang.', 'data' => $queue]);
        } else {
            $this->json_response(['status' => 'error', 'message' => 'Data antrean tidak ditemukan.'], 404);
        }
    }

    /**
     * Update queue status explicitly (e.g. Selesai, Dilewati)
     */
    public function queue_update_status() {
        $input = $this->get_json_input();
        $id_antrian = isset($input['id_antrian']) ? $input['id_antrian'] : $this->input->post('id_antrian');
        $status = isset($input['status']) ? $input['status'] : $this->input->post('status');

        if (empty($id_antrian) || empty($status)) {
            $this->json_response(['status' => 'error', 'message' => 'ID Antrean dan Status harus diisi.'], 400);
        }

        if (!in_array($status, ['menunggu', 'dipanggil', 'selesai', 'dilewati'])) {
            $this->json_response(['status' => 'error', 'message' => 'Status tidak valid.'], 400);
        }

        $this->db->where('id_antrian', $id_antrian);
        if ($this->db->update('antrian', ['status' => $status])) {
            $this->json_response(['status' => 'success', 'message' => 'Status antrean berhasil diperbarui.']);
        } else {
            $this->json_response(['status' => 'error', 'message' => 'Gagal memperbarui status antrean.'], 500);
        }
    }

    /**
     * Get active calls per poliklinik (for TV Monitor Display)
     */
    public function queue_active_calls() {
        $today = date('Y-m-d');
        
        $this->db->select('antrian.*, poliklinik.nama_poli, dokter.nama_dokter');
        $this->db->from('antrian');
        $this->db->join('poliklinik', 'antrian.id_poli = poliklinik.id_poli');
        $this->db->join('dokter', 'antrian.id_dokter = dokter.id_dokter');
        $this->db->where([
            'antrian.status' => 'dipanggil',
            'antrian.tanggal_antrian' => $today
        ]);
        $this->db->order_by('antrian.waktu_dilayani', 'DESC');
        
        $calls = $this->db->get()->result();
        
        // Also fetch total stats per poli (total waiting)
        $poli_list = $this->db->get_where('poliklinik', ['status' => 'aktif'])->result();
        $monitors = [];
        
        foreach ($poli_list as $p) {
            // Find active call for this poli
            $active = null;
            foreach ($calls as $c) {
                if ($c->id_poli === $p->id_poli) {
                    $active = $c;
                    break;
                }
            }
            
            // Total waiting in queue for this poli
            $this->db->where([
                'id_poli' => $p->id_poli,
                'status' => 'menunggu',
                'tanggal_antrian' => $today
            ]);
            $waiting_count = $this->db->count_all_results('antrian');
            
            $monitors[] = [
                'id_poli' => $p->id_poli,
                'nama_poli' => $p->nama_poli,
                'kode_antrian' => $p->kode_antrian,
                'active_call' => $active ? $active->nomor_antrian : '---',
                'patient_name' => $active ? $active->nama_pasien : '---',
                'doctor_name' => $active ? $active->nama_dokter : '---',
                'waiting_count' => $waiting_count
            ];
        }
        
        $this->json_response(['status' => 'success', 'data' => $monitors]);
    }

    // ==========================================
    // DASHBOARD & ANALYTICS STATS
    // ==========================================

    public function dashboard_stats() {
        $today = date('Y-m-d');
        
        // 1. Core Card Metrics
        $total = $this->db->where('tanggal_antrian', $today)->count_all_results('antrian');
        $waiting = $this->db->where(['tanggal_antrian' => $today, 'status' => 'menunggu'])->count_all_results('antrian');
        $serving = $this->db->where(['tanggal_antrian' => $today, 'status' => 'dipanggil'])->count_all_results('antrian');
        $completed = $this->db->where(['tanggal_antrian' => $today, 'status' => 'selesai'])->count_all_results('antrian');
        $skipped = $this->db->where(['tanggal_antrian' => $today, 'status' => 'dilewati'])->count_all_results('antrian');

        // 2. Queue Counts by Poliklinik (Pie/Bar Chart)
        $this->db->select('poliklinik.nama_poli, COUNT(antrian.id_antrian) as count');
        $this->db->from('poliklinik');
        $this->db->join('antrian', 'poliklinik.id_poli = antrian.id_poli AND antrian.tanggal_antrian = "' . $today . '"', 'left');
        $this->db->where('poliklinik.status', 'aktif');
        $this->db->group_by('poliklinik.id_poli');
        $by_poli = $this->db->get()->result();

        // 3. Online vs Offline Registration
        $online = $this->db->where(['tanggal_antrian' => $today, 'tipe_pendaftaran' => 'online'])->count_all_results('antrian');
        $offline = $this->db->where(['tanggal_antrian' => $today, 'tipe_pendaftaran' => 'offline'])->count_all_results('antrian');

        // 4. Hourly Distribution (for Today)
        $this->db->select('HOUR(waktu_daftar) as hour_reg, COUNT(id_antrian) as count');
        $this->db->from('antrian');
        $this->db->where('tanggal_antrian', $today);
        $this->db->group_by('hour_reg');
        $this->db->order_by('hour_reg', 'ASC');
        $hourly = $this->db->get()->result();

        $this->json_response([
            'status' => 'success',
            'data' => [
                'metrics' => [
                    'total' => $total,
                    'waiting' => $waiting,
                    'serving' => $serving,
                    'completed' => $completed,
                    'skipped' => $skipped
                ],
                'by_poli' => $by_poli,
                'registration_types' => [
                    'online' => $online,
                    'offline' => $offline
                ],
                'hourly' => $hourly
            ]
        ]);
    }
}
