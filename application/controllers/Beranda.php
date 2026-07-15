<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Beranda extends CI_Controller {

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
        $this->load->model('M_penjualan', 'm_penjualan');
        $this->load->model('M_iphone', 'm_iphone');
        $this->load->model('M_prediksi', 'm_prediksi');
        $this->load->model('M_rekomendasi', 'm_rekomendasi');
        $this->config->load('forecasting');
        $this->data['aktif'] = 'beranda';
    }

    public function index() {
        $this->data['title'] = 'Beranda';
        
        // Compute general metrics for analytics cards
        $this->data['total_penjualan'] = $this->m_penjualan->jumlah();
        $this->data['total_iphone'] = $this->m_iphone->jumlah();
        
        // Default parameters from config
        $ma_period = (int) $this->config->item('ma_period');
        $safety_factor = (float) $this->config->item('safety_factor');

        $iphones = $this->m_iphone->lihat();
        $forecast_results = [];

        foreach ($iphones as $ip) {
            $sales_data = $this->m_penjualan->get_monthly_sales($ip->id_iphone);
            $total_months = count($sales_data);

            if ($total_months < $ma_period + 1) {
                continue;
            }

            $result = $this->run_forecasting_logic($ip->id_iphone, $sales_data, $ma_period, $safety_factor);
            $result['nama_tipe'] = $ip->nama_tipe;
            $result['id_iphone'] = $ip->id_iphone;
            
            $forecast_results[$ip->id_iphone] = $result;
        }

        // Rank by lowest MAPE error (highest accuracy)
        uasort($forecast_results, function($a, $b) {
            return $a['avg_mape'] <=> $b['avg_mape'];
        });

        // Map rank to each model
        $rank = 1;
        foreach ($forecast_results as $id => &$res) {
            $res['peringkat'] = $rank++;
        }
        unset($res); // break reference

        $this->data['forecast_results'] = $forecast_results;

        // Fetch latest prediction date and average MAPE from database
        $this->db->select('created_at, nilai_mape');
        $this->db->from('prediksi');
        $this->db->order_by('created_at', 'DESC');
        $preds = $this->db->get()->result();

        if (count($preds) > 0) {
            $this->data['last_run'] = date('H:i, d M Y', strtotime($preds[0]->created_at));
            
            $mape_sum = 0;
            foreach ($preds as $p) {
                $mape_sum += $p->nilai_mape;
            }
            $this->data['avg_mape'] = number_format($mape_sum / count($preds), 1) . '%';
        } else {
            $this->data['last_run'] = 'Belum Ada';
            $this->data['avg_mape'] = '—';
        }

        // Load dashboard view
        $this->load->view('beranda', $this->data);
    }

    private function run_forecasting_logic($id_iphone, $sales_data, $ma_period, $safety_factor) {
        $sales = [];
        $labels = [];
        $months_raw = [];
        foreach ($sales_data as $row) {
            $sales[] = (int) $row->total_terjual;
            $months_raw[] = $row->bulan_tahun;
            
            $parts = explode('-', $row->bulan_tahun);
            $year_short = substr($parts[0], 2);
            $month_name = $this->get_month_name_indonesian((int) $parts[1]);
            $labels[] = substr($month_name, 0, 3) . ' ' . $year_short;
        }

        $n = count($sales);
        $ma = array_fill(0, $n, null);
        $ape = array_fill(0, $n, null);
        
        $mape_sum = 0;
        $mape_count = 0;

        for ($i = $ma_period; $i < $n; $i++) {
            $sum = 0;
            for ($j = 1; $j <= $ma_period; $j++) {
                $sum += $sales[$i - $j];
            }
            $ma[$i] = $sum / $ma_period;
            
            if ($sales[$i] > 0) {
                $err = abs($sales[$i] - $ma[$i]);
                $ape[$i] = ($err / $sales[$i]) * 100;
                $mape_sum += $ape[$i];
                $mape_count++;
            }
        }

        $avg_mape = $mape_count > 0 ? $mape_sum / $mape_count : 0;

        $sum_last = 0;
        for ($i = 0; $i < $ma_period; $i++) {
            $sum_last += $sales[$n - 1 - $i];
        }
        $forecast_raw = $sum_last / $ma_period;

        $last_month_str = $months_raw[$n - 1];
        $time = strtotime($last_month_str . '-01');
        $next_time = strtotime('+1 month', $time);
        $next_month = date('Y-m', $next_time);

        $global_avg = array_sum($sales) / $n;
        $next_month_num = (int) date('m', $next_time);
        
        $matching_sales = [];
        for ($i = 0; $i < $n; $i++) {
            $month_num = (int) explode('-', $months_raw[$i])[1];
            if ($month_num === $next_month_num) {
                $matching_sales[] = $sales[$i];
            }
        }

        $seasonal_idx = 1.0;
        if (count($matching_sales) > 0 && $global_avg > 0) {
            $seasonal_idx = (array_sum($matching_sales) / count($matching_sales)) / $global_avg;
        }
        
        $forecast_adj = $forecast_raw * $seasonal_idx;

        $recent_sales = array_slice($sales, -$ma_period);
        $prev_sales = array_slice($sales, -($ma_period * 2), $ma_period);
        
        $recent_avg = array_sum($recent_sales) / $ma_period;
        $prev_avg = count($prev_sales) > 0 ? array_sum($prev_sales) / count($prev_sales) : $recent_avg;
        
        $trend = 0.0;
        if ($prev_avg > 0) {
            $trend = (($recent_avg - $prev_avg) / $prev_avg) * 100;
        }

        $last_6_sales = array_slice($sales, -6);
        $count_6 = count($last_6_sales);
        $mean_6 = array_sum($last_6_sales) / $count_6;
        
        $variance_sum = 0;
        foreach ($last_6_sales as $val) {
            $variance_sum += pow($val - $mean_6, 2);
        }
        $std_dev = sqrt($variance_sum / $count_6);
        $safety_stock = (int) ceil($std_dev * 1.645);

        $rec_qty = (int) ceil($forecast_adj * $safety_factor) + $safety_stock;
        
        if ($trend > 10) {
            $rec_qty = (int) ceil($rec_qty * 1.1);
        } elseif ($trend < -10) {
            $rec_qty = (int) ceil($rec_qty * 0.9);
        }

        $threshold_green = $this->config->item('mape_green');
        $threshold_yellow = $this->config->item('mape_yellow');

        $label = 'RED';
        if ($avg_mape <= $threshold_green && $trend >= -5) {
            $label = 'GREEN';
        } elseif ($avg_mape <= $threshold_yellow) {
            $label = 'YELLOW';
        }

        $next_parts = explode('-', $next_month);
        $next_label = $this->get_month_name_indonesian((int) $next_parts[1]) . ' ' . $next_parts[0];

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

    private function get_month_name_indonesian($num) {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $months[$num] ?? '';
    }
}
