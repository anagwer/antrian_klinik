<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Forecasting - <?= $iphone->nama_tipe ?></title>
<style>
    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 13px;
        line-height: 1.5;
        color: #333;
        margin: 20px;
    }
    .header {
        border-bottom: 2px solid #2f81f7;
        padding-bottom: 12px;
        margin-bottom: 24px;
        display: block;
    }
    .title {
        font-size: 20px;
        font-weight: bold;
        color: #111;
    }
    .subtitle {
        font-size: 12px;
        color: #666;
        margin-top: 4px;
    }
    .meta-table {
        width: 100%;
        margin-bottom: 24px;
        border-collapse: collapse;
    }
    .meta-table td {
        padding: 4px 0;
        vertical-align: top;
    }
    .meta-label {
        font-weight: bold;
        width: 160px;
        color: #555;
    }
    .meta-value {
        color: #333;
    }
    .summary-section {
        background: #f6f8fa;
        border: 1px solid #d0d7de;
        border-radius: 6px;
        padding: 16px;
        margin-bottom: 24px;
    }
    .summary-title {
        font-size: 14px;
        font-weight: bold;
        color: #2f81f7;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .summary-grid {
        width: 100%;
        border-collapse: collapse;
    }
    .summary-box {
        border: 1px solid #d0d7de;
        background: #fff;
        border-radius: 4px;
        padding: 12px;
        text-align: center;
        width: 30%;
    }
    .summary-box-label {
        font-size: 10px;
        color: #57606a;
        text-transform: uppercase;
        font-weight: bold;
        margin-bottom: 4px;
    }
    .summary-box-val {
        font-size: 20px;
        font-weight: bold;
        color: #1f2328;
    }
    .summary-box-unit {
        font-size: 10px;
        color: #57606a;
    }
    .summary-box.accent {
        border-color: #2f81f7;
        background: #f0f7ff;
    }
    .summary-box.accent .summary-box-val {
        color: #0969da;
    }
    .calc-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }
    .calc-table th {
        background: #f6f8fa;
        border: 1px solid #d0d7de;
        padding: 8px 10px;
        font-size: 11px;
        font-weight: bold;
        color: #57606a;
        text-transform: uppercase;
        text-align: left;
    }
    .calc-table td {
        border: 1px solid #d0d7de;
        padding: 8px 10px;
        vertical-align: middle;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .badge-green {
        background: #dafbe1;
        color: #1a7f37;
    }
    .badge-yellow {
        background: #fff8c5;
        color: #9a6700;
    }
    .badge-red {
        background: #ffebe9;
        color: #cf222e;
    }
    .footer {
        margin-top: 40px;
        font-size: 10px;
        color: #888;
        text-align: center;
        border-top: 1px solid #eee;
        padding-top: 10px;
    }
</style>
</head>
<body>

<div class="header">
    <div class="title">Laporan Analisis & Peramalan Penjualan</div>
    <div class="subtitle">Sistem Forecasting Inventory Lestari iPhone · Moving Average + MAPE</div>
</div>

<table class="meta-table">
    <tr>
        <td class="meta-label">Model iPhone:</td>
        <td class="meta-value"><?= $iphone->nama_tipe ?></td>
        <td class="meta-label">Metode Peramalan:</td>
        <td class="meta-value">Simple Moving Average (SMA-<?= $pred->periode_n ?>)</td>
    </tr>
    <tr>
        <td class="meta-label">Bulan Target Prediksi:</td>
        <td class="meta-value"><strong><?= $calc['next_label'] ?></strong></td>
        <td class="meta-label">Tanggal Cetak:</td>
        <td class="meta-value"><?= date('d F Y, H:i') ?> WIB</td>
    </tr>
</table>

<div class="summary-section">
    <div class="summary-title">Rangkuman Rekomendasi Pengadaan Stok</div>
    <table class="summary-grid">
        <tr>
            <td class="summary-box">
                <div class="summary-box-label">Raw Forecast (SMA)</div>
                <div class="summary-box-val"><?= round($calc['forecast_raw']) ?></div>
                <div class="summary-box-unit">unit</div>
            </td>
            <td style="width: 5%;"></td>
            <td class="summary-box">
                <div class="summary-box-label">Safety Stock Buffer</div>
                <div class="summary-box-val">+<?= $calc['safety_stock'] ?></div>
                <div class="summary-box-unit">unit</div>
            </td>
            <td style="width: 5%;"></td>
            <td class="summary-box accent">
                <div class="summary-box-label">Saran Suplai Akhir</div>
                <div class="summary-box-val"><?= $calc['rec_qty'] ?></div>
                <div class="summary-box-unit">unit</div>
            </td>
        </tr>
    </table>
    <div style="font-size: 11px; margin-top: 14px; color: #555; line-height: 1.4;">
        <strong>Evaluasi Akurasi:</strong> Peramalan memiliki nilai error MAPE sebesar <strong><?= number_format($calc['avg_mape'], 2) ?>%</strong> 
        (Status: <?= $calc['avg_mape'] <= $cfg_green ? 'Akurat' : ($calc['avg_mape'] <= $cfg_yellow ? 'Cukup Akurat' : 'Akurasi Rendah') ?>). 
        Pertumbuhan penjualan dalam 3 bulan terakhir adalah <strong><?= ($calc['trend'] >= 0 ? '+' : '') . number_format($calc['trend'], 1) ?>%</strong>.
    </div>
</div>

<div style="font-weight: bold; font-size: 12px; margin-bottom: 8px; color: #111;">
    Tabel Kalkulasi Historis Moving Average & MAPE Error
</div>

<table class="calc-table">
    <thead>
        <tr>
            <th style="width: 30px; text-align: center;">No</th>
            <th>Bulan Historis</th>
            <th style="text-align: right; width: 100px;">Aktual (unit)</th>
            <th style="text-align: right; width: 100px;">SMA Peramalan</th>
            <th style="text-align: right; width: 100px;">Error (unit)</th>
            <th style="text-align: right; width: 80px;">MAPE (%)</th>
            <th style="width: 100px; text-align: center;">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $n = count($calc['sales']);
        for ($i = 0; $i < $n; $i++) :
            $ma_val = $calc['ma'][$i];
            $err_val = $ma_val !== null ? abs($calc['sales'][$i] - $ma_val) : null;
            $ape_val = $calc['ape'][$i];

            $badge_str = '—';
            if ($ape_val !== null) {
                if ($ape_val <= $cfg_green) {
                    $badge_str = '<span class="badge badge-green">Akurat</span>';
                } elseif ($ape_val <= $cfg_yellow) {
                    $badge_str = '<span class="badge badge-yellow">Cukup</span>';
                } else {
                    $badge_str = '<span class="badge badge-red">Rendah</span>';
                }
            }
        ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td style="font-weight: bold;"><?= $calc['labels'][$i] ?></td>
                <td class="text-right"><?= number_format($calc['sales'][$i]) ?></td>
                <td class="text-right"><?= $ma_val !== null ? number_format($ma_val, 2) : '—' ?></td>
                <td class="text-right"><?= $err_val !== null ? number_format($err_val, 2) : '—' ?></td>
                <td class="text-right" style="font-weight: bold;"><?= $ape_val !== null ? number_format($ape_val, 2) . '%' : '—' ?></td>
                <td class="text-center"><?= $badge_str ?></td>
            </tr>
        <?php endfor; ?>
        <tr style="background-color: #eaf5ff; font-weight: bold;">
            <td class="text-center">⬡</td>
            <td>Proyeksi <?= $calc['next_label'] ?></td>
            <td class="text-right" style="color: #0969da;"><?= round($calc['forecast_adj']) ?></td>
            <td colspan="4" style="font-size: 10px; color: #57606a; font-style: italic; padding-left: 10px;">
                Adj. Seasonal Index: <?= number_format($calc['seasonal_idx'], 3) ?> &nbsp;·&nbsp; Safety Stock: +<?= $calc['safety_stock'] ?> unit &nbsp;·&nbsp; Saran Stok: <?= $calc['rec_qty'] ?> unit
            </td>
        </tr>
    </tbody>
</table>

<div class="footer">
    Dokumen ini digenerate secara otomatis oleh Sistem Lestari iPhone Prediksi. Hak Cipta dilindungi undang-undang.
</div>

</body>
</html>
