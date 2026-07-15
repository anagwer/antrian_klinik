<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prediksi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (is_cli()) {
            $this->session->set_userdata('login', [
                'id_user' => 1,
                'username' => 'admin',
                'role' => 'admin',
                'jam_masuk' => date('H:i:s')
            ]);
        }
        if (!$this->session->userdata('login')) {
            redirect('login');
        }
        $login_user = $this->session->userdata('login');
        if ($login_user['role'] !== 'admin') {
            $this->session->set_flashdata('error', 'Akses ke Prediksi Penjualan hanya untuk Admin!');
            redirect('beranda');
        }
        $this->load->model('M_penjualan', 'm_penjualan');
        $this->load->model('M_iphone', 'm_iphone');
        $this->load->model('M_prediksi', 'm_prediksi');
        $this->load->model('M_rekomendasi', 'm_rekomendasi');
        $this->config->load('forecasting');
        $this->data['aktif'] = 'prediksi';
    }

    public function index() {
        $this->data['title'] = 'Proses Prediksi';
        $this->data['all_iphone'] = $this->m_iphone->lihat();
        
        // Parameter defaults
        $this->data['ma_period'] = $this->config->item('ma_period');
        $this->data['safety_factor'] = $this->config->item('safety_factor');

        $this->load->view('prediksi/lihat', $this->data);
    }

    // Run prediction algorithm for all iPhone models
    public function hitung() {
        $ma_period = $this->input->post('ma_period') ? (int) $this->input->post('ma_period') : (int) $this->config->item('ma_period');
        $safety_factor = $this->input->post('safety_factor') ? (float) $this->input->post('safety_factor') : (float) $this->config->item('safety_factor');

        $iphones = $this->m_iphone->lihat();
        if (empty($iphones)) {
            $this->output_json(['status' => 'error', 'message' => 'Tidak ada model iPhone terdaftar']);
            return;
        }

        $all_results = [];
        $skipped = [];
        $next_month = null;

        foreach ($iphones as $ip) {
            $sales_data = $this->m_penjualan->get_monthly_sales($ip->id_iphone);
            $total_months = count($sales_data);

            if ($total_months < $ma_period + 1) {
                $skipped[] = [
                    'nama_tipe' => $ip->nama_tipe,
                    'message' => 'Butuh minimal ' . ($ma_period + 1) . ' bulan data (saat ini ' . $total_months . ')'
                ];
                continue;
            }

            $result = $this->run_forecasting_logic($ip->id_iphone, $sales_data, $ma_period, $safety_factor);
            $result['nama_tipe'] = $ip->nama_tipe;
            $result['id_iphone'] = $ip->id_iphone;
            
            // Clear any existing prediction for this model and target month
            // Also delete any predictions for future months since they are invalid now
            $this->db->delete('prediksi', ['id_iphone' => $ip->id_iphone, 'bulan_prediksi' => $result['next_month']]);
            $this->db->where('id_iphone', $ip->id_iphone);
            $this->db->where('bulan_prediksi >', $result['next_month']);
            $this->db->delete('prediksi');

            // Store prediction results in the database
            $login_user = $this->session->userdata('login');
            $prediksi_id = $this->m_prediksi->tambah([
                'id_iphone' => $ip->id_iphone,
                'id_user' => $login_user['id_user'],
                'periode_n' => $ma_period,
                'nilai_sma' => $result['forecast_adj'],
                'nilai_mape' => $result['avg_mape'],
                'bulan_prediksi' => $result['next_month'],
            ]);

            $result['id_prediksi'] = $prediksi_id;
            $all_results[$ip->id_iphone] = $result;
            
            if ($next_month === null) {
                $next_month = $result['next_month'];
            }
        }

        if (empty($all_results)) {
            $msg = 'Tidak ada model iPhone yang memiliki cukup data historis untuk peramalan (min ' . ($ma_period + 1) . ' bulan).';
            if (!empty($skipped)) {
                $msg .= ' Model yang dilewati: ' . implode(', ', array_map(function($s) { return $s['nama_tipe']; }, $skipped));
            }
            $this->output_json(['status' => 'error', 'message' => $msg]);
            return;
        }

        // Update rankings for the predicted month
        $this->update_rankings($next_month);

        // Fetch the updated recommendations/rankings to include in the response
        $rankings = $this->m_rekomendasi->get_latest_recommendations();
        
        // Map the ranking details back to each result
        foreach ($rankings as $rank) {
            if (isset($all_results[$rank->id_iphone])) {
                $all_results[$rank->id_iphone]['peringkat'] = $rank->peringkat;
                $all_results[$rank->id_iphone]['keterangan'] = $rank->keterangan;
            }
        }

        $this->output_json([
            'status' => 'success',
            'data' => $all_results,
            'warnings' => $skipped
        ]);
    }

    /**
     * Menjalankan logika peramalan (forecasting) menggunakan Simple Moving Average (SMA),
     * Penyesuaian Musiman (Seasonal Adjustment), Tren Penjualan, Evaluasi Error (MAPE),
     * serta perhitungan Safety Stock untuk menentukan rekomendasi jumlah pengadaan stok.
     * 
     * RUMUS & ALUR PERHITUNGAN:
     * 1. Simple Moving Average (SMA):
     *    SMA_t = (Sales_{t-1} + Sales_{t-2} + ... + Sales_{t-n}) / n
     * 
     * 2. Absolute Percentage Error (APE) - Mengukur kesalahan peramalan per bulan:
     *    APE_t = (|Aktual_t - SMA_t| / Aktual_t) * 100%
     * 
     * 3. Mean Absolute Percentage Error (MAPE) - Mengukur rata-rata kesalahan keseluruhan:
     *    MAPE = (1 / m) * Σ APE_t
     * 
     * 4. Indeks Musiman (Seasonal Index):
     *    Indeks Musiman = Rata-rata Penjualan Bulan Target / Rata-rata Penjualan Global
     *    Adjusted Forecast = Raw Forecast * Indeks Musiman
     * 
     * 5. Tren Penjualan (Trend):
     *    Trend = ((Rata-rata n-bulan Terkini - Rata-rata n-bulan Sebelumnya) / Rata-rata n-bulan Sebelumnya) * 100%
     * 
     * 6. Safety Stock (95% Service Level):
     *    Standar Deviasi (σ) dari 6 bulan terakhir = √ ( Σ(Sales_i - Mean_6)^2 / 6 )
     *    Safety Stock = ⌈ σ * 1.645 ⌉   (Nilai Z=1.645 mewakili tingkat pelayanan 95%)
     * 
     * 7. Rekomendasi Stok Akhir (Suggested Stock Quantity):
     *    Rec_Qty = ⌈ Forecast_Adjusted * Safety_Factor ⌉ + Safety_Stock
     *    - Jika Tren Naik > 10%: Rec_Qty = ⌈ Rec_Qty * 1.1 ⌉ (+10% untuk mengantisipasi demand naik)
     *    - Jika Tren Turun < -10%: Rec_Qty = ⌈ Rec_Qty * 0.9 ⌉ (-10% untuk menghindari overstock)
     */
    private function run_forecasting_logic($id_iphone, $sales_data, $ma_period, $safety_factor) {
        $sales = [];
        $labels = [];
        $months_raw = [];
        
        // 1. Ekstraksi dan penataan data penjualan historis dari database
        foreach ($sales_data as $row) {
            $sales[] = (int) $row->total_terjual;
            $months_raw[] = $row->bulan_tahun; // Format: YYYY-MM
            
            // Mengubah format bulan untuk label grafik (contoh: "2024-07" menjadi "Jul 24")
            $parts = explode('-', $row->bulan_tahun);
            $year_short = substr($parts[0], 2);
            $month_name = $this->get_month_name_indonesian((int) $parts[1]);
            $labels[] = substr($month_name, 0, 3) . ' ' . $year_short;
        }

        $n = count($sales);
        // Mengisi array awal dengan nilai null untuk periode sebelum data mencukupi n-bulan
        $ma = array_fill(0, $n, null);
        $ape = array_fill(0, $n, null);
        
        $mape_sum = 0;
        $mape_count = 0;

        // 2. Menghitung Simple Moving Average (SMA) dan Absolute Percentage Error (APE) historis
        // Perhitungan dimulai dari indeks $ma_period karena memerlukan data n-bulan sebelumnya
        for ($i = $ma_period; $i < $n; $i++) {
            $sum = 0;
            // Menjumlahkan data penjualan sebanyak n-bulan ke belakang
            // Rumus: Sum = Sales_{i-1} + Sales_{i-2} + ... + Sales_{i-n}
            for ($j = 1; $j <= $ma_period; $j++) {
                $sum += $sales[$i - $j];
            }
            // Rumus SMA: Nilai rata-rata bergerak
            $ma[$i] = $sum / $ma_period;
            
            // Menghitung APE (Absolute Percentage Error) jika penjualan aktual > 0
            // Rumus: APE_t = ( |Aktual_t - SMA_t| / Aktual_t ) * 100
            if ($sales[$i] > 0) {
                $err = abs($sales[$i] - $ma[$i]);
                $ape[$i] = ($err / $sales[$i]) * 100;
                $mape_sum += $ape[$i];
                $mape_count++;
            }
        }

        // Menghitung rata-rata kesalahan (MAPE) secara keseluruhan
        // Rumus MAPE = Total APE / Jumlah Periode yang dihitung
        $avg_mape = $mape_count > 0 ? $mape_sum / $mape_count : 0;

        // 3. Menghitung ramalan mentah (Raw Forecast) untuk bulan depan
        // Menggunakan rata-rata dari n-bulan terakhir di dalam data historis
        $sum_last = 0;
        for ($i = 0; $i < $ma_period; $i++) {
            $sum_last += $sales[$n - 1 - $i];
        }
        $forecast_raw = $sum_last / $ma_period;

        // Menentukan string bulan target berikutnya (misal: "2024-08")
        $last_month_str = $months_raw[$n - 1];
        $time = strtotime($last_month_str . '-01');
        $next_time = strtotime('+1 month', $time);
        $next_month = date('Y-m', $next_time);

        // 4. Penyesuaian Musiman (Seasonal Adjustment)
        // Menghitung rata-rata penjualan secara global untuk melihat garis dasar penjualan
        $global_avg = array_sum($sales) / $n;
        $next_month_num = (int) date('m', $next_time);
        
        // Mengumpulkan data penjualan historis pada bulan kalender yang sama dengan bulan target (misalnya semua bulan Juli)
        $matching_sales = [];
        for ($i = 0; $i < $n; $i++) {
            $month_num = (int) explode('-', $months_raw[$i])[1];
            if ($month_num === $next_month_num) {
                $matching_sales[] = $sales[$i];
            }
        }

        // Rumus Indeks Musiman = Rata-rata Penjualan Bulan Target / Rata-rata Penjualan Global
        $seasonal_idx = 1.0;
        if (count($matching_sales) > 0 && $global_avg > 0) {
            $seasonal_idx = (array_sum($matching_sales) / count($matching_sales)) / $global_avg;
        }
        
        // Proyeksi setelah dikalikan dengan faktor indeks musiman
        // Rumus: Adjusted Forecast = Raw Forecast * Seasonal Index
        $forecast_adj = $forecast_raw * $seasonal_idx;

        // 5. Perhitungan Tren (mengukur pertumbuhan n-bulan terakhir vs n-bulan sebelumnya)
        $recent_sales = array_slice($sales, -$ma_period);
        $prev_sales = array_slice($sales, -($ma_period * 2), $ma_period);
        
        $recent_avg = array_sum($recent_sales) / $ma_period;
        $prev_avg = count($prev_sales) > 0 ? array_sum($prev_sales) / count($prev_sales) : $recent_avg;
        
        // Rumus Tren Persentase: ((Rata-rata Baru - Rata-rata Lama) / Rata-rata Lama) * 100
        $trend = 0.0;
        if ($prev_avg > 0) {
            $trend = (($recent_avg - $prev_avg) / $prev_avg) * 100;
        }

        // 6. Perhitungan Stok Pengaman (Safety Stock)
        // Mengambil data penjualan 6 bulan terakhir untuk mengukur variabilitas penjualan
        $last_6_sales = array_slice($sales, -6);
        $count_6 = count($last_6_sales);
        $mean_6 = array_sum($last_6_sales) / $count_6;
        
        // Menghitung Varian dan Standar Deviasi (σ)
        $variance_sum = 0;
        foreach ($last_6_sales as $val) {
            $variance_sum += pow($val - $mean_6, 2);
        }
        $std_dev = sqrt($variance_sum / $count_6);
        
        // Rumus Safety Stock = Standar Deviasi * Z-score (1.645 untuk Service Level 95%)
        // Pembulatan ke atas menggunakan ceil() agar unit bernilai bulat utuh
        $safety_stock = (int) ceil($std_dev * 1.645);

        // 7. Menentukan Jumlah Rekomendasi Pengadaan Stok Awal
        // Rumus: Rec Qty = (Adjusted Forecast * Safety Factor) + Safety Stock
        $rec_qty = (int) ceil($forecast_adj * $safety_factor) + $safety_stock;
        
        // Penyesuaian Rekomendasi berdasarkan Tren Penjualan:
        // - Jika tren naik signifikan (> 10%), tambah stok rekomendasi sebesar 10% (+10%)
        // - Jika tren turun signifikan (< -10%), kurangi stok rekomendasi sebesar 10% (-10%) untuk cegah overstock
        if ($trend > 10) {
            $rec_qty = (int) ceil($rec_qty * 1.1);
        } elseif ($trend < -10) {
            $rec_qty = (int) ceil($rec_qty * 0.9);
        }

        // 8. Klasifikasi Kategori Akurasi Berdasarkan Nilai Rata-rata MAPE
        $threshold_green = $this->config->item('mape_green'); // Nilai batas hijau (sangat akurat)
        $threshold_yellow = $this->config->item('mape_yellow'); // Nilai batas kuning (cukup akurat)

        $label = 'RED'; // Default: Kurang akurat / Risiko tinggi
        if ($avg_mape <= $threshold_green && $trend >= -5) {
            $label = 'GREEN'; // Sangat Akurat
        } elseif ($avg_mape <= $threshold_yellow) {
            $label = 'YELLOW'; // Cukup Akurat / Moderat
        }

        // Format nama bulan target dalam Bahasa Indonesia (contoh: "Agustus 2024")
        $next_parts = explode('-', $next_month);
        $next_label = $this->get_month_name_indonesian((int) $next_parts[1]) . ' ' . $next_parts[0];

        // Mengembalikan seluruh hasil kalkulasi untuk digunakan oleh controller dan ditampilkan di view
        return [
            'sales' => $sales,
            'labels' => $labels,
            'months_raw' => $months_raw,
            'ma' => $ma,
            'ape' => $ape,
            'avg_mape' => $avg_mape,
            'forecast_raw' => $forecast_raw,
            'forecast_adj' => $forecast_adj,
            'seasonal_idx' => $seasonal_idx,
            'trend' => $trend,
            'safety_stock' => $safety_stock,
            'rec_qty' => $rec_qty,
            'label' => $label,
            'next_month' => $next_month,
            'next_label' => $next_label
        ];
    }

    /**
     * Memperbarui tabel peringkat dan rekomendasi berdasarkan hasil peramalan terbaru
     * untuk seluruh tipe iPhone pada bulan target prediksi tertentu.
     * 
     * ALUR PERINGKAT & REKOMENDASI:
     * 1. Mengambil data prediksi untuk tipe iPhone pada bulan target.
     * 2. Menghitung ulang parameter forecasting untuk membuat keterangan rekomendasi stok.
     * 3. Menyusun saran teks/keterangan bisnis berdasarkan Tren Penjualan dan Label Status MAPE (GREEN, YELLOW, RED).
     * 4. Mengurutkan (sorting) hasil rekomendasi berdasarkan MAPE terkecil (kesalahan terkecil = akurasi tertinggi).
     * 5. Menyimpan peringkat (peringkat 1, 2, 3...) ke database rekomendasi.
     */
    private function update_rankings($bulan_prediksi) {
        // Ambil semua data hasil prediksi pada bulan target tersebut
        $predictions = $this->m_prediksi->get_all_for_month($bulan_prediksi);
        if (count($predictions) === 0) return;

        $recs = [];
        foreach ($predictions as $p) {
            // Mengambil parameter standar dan menjalankan ulang logika peramalan
            $sales_data = $this->m_penjualan->get_monthly_sales($p->id_iphone);
            $ma_period = $p->periode_n;
            $safety_factor = $this->config->item('safety_factor');

            $calc = $this->run_forecasting_logic($p->id_iphone, $sales_data, $ma_period, $safety_factor);
            
            // Menyusun keterangan tren dan deskripsi rekomendasi bisnis secara otomatis
            $trend_icon = $calc['trend'] > 5 ? 'naik' : ($calc['trend'] < -5 ? 'turun' : 'stabil');
            $trend_pct = number_format(abs($calc['trend']), 1) . '%';
            $keterangan = "Tren penjualan {$trend_icon} sebesar {$trend_pct}. ";
            
            // Memberikan arahan stok dan promosi berdasarkan kategori risiko MAPE
            if ($calc['label'] === 'GREEN') {
                $keterangan .= "Kondisi pasar stabil dengan akurasi peramalan sangat tinggi. Direkomendasikan menyuplai stok penuh.";
            } elseif ($calc['label'] === 'YELLOW') {
                $keterangan .= "Kondisi pasar cukup stabil. Direkomendasikan menyuplai 80% stok buffer.";
            } else {
                $keterangan .= "Akurasi peramalan rendah atau tren turun signifikan. Batasi stok untuk menghindari overstock.";
            }

            $recs[] = [
                'id_prediksi' => $p->id_prediksi,
                'nilai_mape' => $calc['avg_mape'],
                'rec_qty' => $calc['rec_qty'],
                'keterangan' => $keterangan
            ];
        }

        // Mengurutkan rekomendasi berdasarkan nilai MAPE terkecil (kesalahan terkecil = akurasi tertinggi)
        // Pengurutan dilakukan secara ascending (<=>)
        usort($recs, function($a, $b) {
            return $a['nilai_mape'] <=> $b['nilai_mape'];
        });

        // Memasukkan data rekomendasi beserta peringkatnya (rank) ke dalam database
        $rank = 1;
        foreach ($recs as $rec) {
            // Menghapus data rekomendasi yang lama untuk prediksi terkait agar tidak duplikat
            $this->m_rekomendasi->clear_by_prediksi($rec['id_prediksi']);

            // Memasukkan data rekomendasi baru (peringkat dimulai dari 1 untuk akurasi terbaik)
            $this->m_rekomendasi->tambah([
                'id_prediksi' => $rec['id_prediksi'],
                'peringkat' => $rank++,
                'saran_stok' => $rec['rec_qty'],
                'keterangan' => $rec['keterangan']
            ]);
        }
    }

    // Export calculation results to CSV
    public function export_csv($id_prediksi) {
        $pred = $this->m_prediksi->lihat_id($id_prediksi);
        if (!$pred) {
            show_404();
        }

        $iphone = $this->m_iphone->lihat_id($pred->id_iphone);
        $sales_data = $this->m_penjualan->get_monthly_sales($pred->id_iphone);
        $calc = $this->run_forecasting_logic($pred->id_iphone, $sales_data, $pred->periode_n, $this->config->item('safety_factor'));

        $filename = 'Kalkulasi_Forecasting_' . str_replace(' ', '_', $iphone->nama_tipe) . '_' . $pred->bulan_prediksi . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, ['Lestari iPhone Prediksi - Moving Average + MAPE Calculation']);
        fputcsv($output, ['iPhone Type', $iphone->nama_tipe]);
        fputcsv($output, ['MA Period', $pred->periode_n . ' months']);
        fputcsv($output, ['Forecast Target Month', $pred->bulan_prediksi]);
        fputcsv($output, []);
        fputcsv($output, ['Bulan', 'Aktual (unit)', 'MA (unit)', 'Absolute Error (unit)', 'MAPE (%)', 'Status']);

        // Historical rows
        $n = count($calc['sales']);
        for ($i = 0; $i < $n; $i++) {
            $ma_val = $calc['ma'][$i];
            $err_val = $ma_val !== null ? abs($calc['sales'][$i] - $ma_val) : null;
            $mape_val = $calc['ape'][$i];

            $status = 'Belum cukup data';
            if ($mape_val !== null) {
                $status = $mape_val <= $this->config->item('mape_green') ? 'Akurat' : ($mape_val <= $this->config->item('mape_yellow') ? 'Cukup' : 'Rendah');
            }

            fputcsv($output, [
                $calc['labels'][$i],
                $calc['sales'][$i],
                $ma_val !== null ? round($ma_val, 2) : '—',
                $err_val !== null ? round($err_val, 2) : '—',
                $mape_val !== null ? round($mape_val, 2) . '%' : '—',
                $status
            ]);
        }

        // Forecast row
        fputcsv($output, []);
        fputcsv($output, [
            'PREDIKSI BULAN DEPAN',
            round($calc['forecast_adj']),
            'Adjusted Forecast',
            'Safety Stock: ' . $calc['safety_stock'],
            'Suggested Stock: ' . $calc['rec_qty'],
            'Overall Accuracy: ' . number_format($calc['avg_mape'], 2) . '%'
        ]);

        fclose($output);
    }

    // Export calculation results to PDF
    public function export_pdf($id_prediksi) {
        $pred = $this->m_prediksi->lihat_id($id_prediksi);
        if (!$pred) {
            show_404();
        }

        $iphone = $this->m_iphone->lihat_id($pred->id_iphone);
        $sales_data = $this->m_penjualan->get_monthly_sales($pred->id_iphone);
        $calc = $this->run_forecasting_logic($pred->id_iphone, $sales_data, $pred->periode_n, $this->config->item('safety_factor'));

        // Load DOMPDF
        require_once FCPATH . 'sb-admin/dompdf/autoload.inc.php';
        
        $data = [
            'pred' => $pred,
            'iphone' => $iphone,
            'calc' => $calc,
            'cfg_green' => $this->config->item('mape_green'),
            'cfg_yellow' => $this->config->item('mape_yellow')
        ];

        $html = $this->load->view('prediksi/pdf_template', $data, TRUE);

        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = 'Hasil_Forecasting_' . str_replace(' ', '_', $iphone->nama_tipe) . '_' . $pred->bulan_prediksi . '.pdf';
        $dompdf->stream($filename, array("Attachment" => false));
    }

    // JSON output helper
    private function output_json($data) {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode($data));
        
        $this->output->_display();
        exit;
    }

    // Helper for indonesian month names
    private function get_month_name_indonesian($num) {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $months[$num] ?? '';
    }
}
