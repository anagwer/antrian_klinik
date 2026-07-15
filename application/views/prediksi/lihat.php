<?php $this->load->view('partials/header'); ?>
<?php $this->load->view('partials/sidebar'); ?>

<!-- PAGE HEADER -->
<div class="page-header">
  <div>
    <div class="page-title">🔮 Prediksi Penjualan (Forecasting Engine)</div>
    <div class="page-subtitle">Peramalan kuantitas stok menggunakan Simple Moving Average (SMA) &amp; MAPE untuk Semua Tipe iPhone</div>
  </div>
</div>

<!-- CONFIGURATION CARD -->
<div class="table-card animate-in" style="padding: 20px; margin-bottom: 20px;">
  <form id="forecastForm" onsubmit="runForecasting(event)">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
      <div>
        <label class="form-label" for="ma_period">Periode Moving Average (n-bulan)</label>
        <input type="number" class="form-input" id="ma_period" name="ma_period" value="<?= $ma_period ?>" min="2" max="12" required>
        <small style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;">Disarankan: 3 - 4 bulan untuk produk elektronik.</small>
      </div>
      <div>
        <label class="form-label" for="safety_factor">Safety Stock Factor</label>
        <input type="number" class="form-input" id="safety_factor" name="safety_factor" value="<?= $safety_factor ?>" min="1.0" max="2.0" step="0.05" required>
        <small style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;">Faktor pengali buffer pengaman (biasanya 1.15).</small>
      </div>
      <div style="display: flex; align-items: flex-end;">
        <button type="submit" class="btn-primary" id="run-btn" style="width: 100%; justify-content: center; height: 38px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          Mulai Peramalan
        </button>
      </div>
    </div>
  </form>
</div>

<!-- FORMULA REFERENCE -->
<div class="section-title" style="margin-bottom: 10px;">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
  Formula Reference
</div>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
  <div class="formula-box" style="margin-bottom: 0;">
    <span class="formula-label">moving average</span>
    MA(t) = [ Sales(t-1) + Sales(t-2) + ... + Sales(t-N) ] / N
  </div>
  <div class="formula-box" style="margin-bottom: 0;">
    <span class="formula-label">mape</span>
    MAPE = (1/n) × Σ |( Aktual(t) - MA(t) ) / Aktual(t)| × 100%
  </div>
  <div class="formula-box" style="margin-bottom: 0;">
    <span class="formula-label">safety stock (95% svc level)</span>
    Safety Stock = Z₀.₉₅ × σ(sales_6mo) = 1.645 × StdDev
  </div>
  <div class="formula-box" style="margin-bottom: 0;">
    <span class="formula-label">final recommendation qty</span>
    Rec_Qty = CEIL( Forecast_Adjusted × Safety_Factor ) + Safety_Stock
  </div>
</div>

<!-- PIPELINE STATUS -->
<div class="pipeline animate-in" id="pipeline" style="display: none;">
  <div class="pipe-step" id="pipe-0">
    <div class="pipe-icon">📥</div>
    <div class="pipe-name">Input Data</div>
    <div class="pipe-desc">Mengambil data historis</div>
    <div class="pipe-connector">▶</div>
  </div>
  <div class="pipe-step" id="pipe-1">
    <div class="pipe-icon">📐</div>
    <div class="pipe-name">Moving Average</div>
    <div class="pipe-desc">Kalkulasi SMA-n</div>
    <div class="pipe-connector">▶</div>
  </div>
  <div class="pipe-step" id="pipe-2">
    <div class="pipe-icon">📊</div>
    <div class="pipe-name">MAPE</div>
    <div class="pipe-desc">Mengukur akurasi</div>
    <div class="pipe-connector">▶</div>
  </div>
  <div class="pipe-step" id="pipe-3">
    <div class="pipe-icon">🔮</div>
    <div class="pipe-name">Forecast</div>
    <div class="pipe-desc">Prediksi bulan depan</div>
    <div class="pipe-connector">▶</div>
  </div>
  <div class="pipe-step" id="pipe-4">
    <div class="pipe-icon">🏷️</div>
    <div class="pipe-name">Label &amp; Rec</div>
    <div class="pipe-desc">Kategori &amp; rekomendasi</div>
  </div>
</div>

<!-- FORECAST RESULTS DISPLAY -->
<div id="forecast-results-container" style="display: none;">

  <!-- KPI SCORECARDS -->
  <div class="results-grid animate-in" id="scorecards-container">
    <!-- Filled by JS -->
  </div>

  <!-- EXPORT & ACTIONS BAR -->
  <div style="display: flex; gap: 10px; margin-bottom: 20px; justify-content: space-between; align-items: center; flex-wrap: wrap;">
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      <button class="btn-secondary" onclick="scrollToCalc()" id="toggle-calc-btn">
        👁️ Lihat Perhitungan Detail
      </button>
      <button class="btn-secondary" onclick="scrollToRecommend()">
        📋 Lihat Rekomendasi
      </button>
    </div>
  </div>

  <!-- CHART CARD -->
  <div class="chart-card animate-in">
    <div class="chart-title" id="chart-title">Tren Penjualan &amp; Moving Average</div>
    <div class="chart-subtitle" id="chart-subtitle">—</div>
    <div class="chart-tabs" id="chart-tabs-container">
      <!-- Dynamic tabs injected by JS -->
    </div>
    <div class="chart-wrap">
      <canvas id="main-chart"></canvas>
    </div>
  </div>

  <!-- DETAIL CALCULATION TABLES (Process) -->
  <div id="calculation-tables-wrapper" class="animate-in">
    <!-- Dynamic tables injected by JS -->
  </div>

  <!-- RECOMMENDATION SECTION -->
  <div id="recommendation-container" class="animate-in" style="margin-top: 24px;">
    <div class="section-title">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      Rekomendasi Stok &amp; Penjualan
    </div>
    <div id="recommend-content">
      <!-- Filled by JS -->
    </div>
  </div>

</div>

<!-- EMPTY STATE -->
<div class="table-card animate-in" id="empty-state-card">
  <div class="empty-state">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <p>Belum ada data peramalan yang dijalankan</p>
    <small>Klik <strong>Mulai Peramalan</strong> untuk melihat hasil proyeksi seluruh tipe iPhone.</small>
  </div>
</div>

<script>
// Objek chart utama, data peramalan terakhir, dan mode grafik saat ini
let mainChart = null;
let lastForecastData = null;
let lastChartMode = 'all';

// ─── Animasi Jalur Proses (Pipeline Animation) ───────────────────────────────
// Menampilkan indikator visual langkah demi langkah jalannya algoritma peramalan
function animatePipeline(callback) {
  const steps = 5;
  const pipeline = document.getElementById('pipeline');
  pipeline.style.display = 'flex'; // Menampilkan container jalur proses

  // Reset status kelas pada setiap langkah
  for (let i = 0; i < steps; i++) {
    const el = document.getElementById('pipe-' + i);
    el.classList.remove('active', 'done', 'error');
  }

  let current = 0;
  // Fungsi rekursif untuk menjalankan animasi secara berurutan dengan jeda waktu
  function next() {
    if (current > 0) {
      document.getElementById('pipe-' + (current - 1)).classList.add('done'); // Langkah sebelumnya selesai
    }
    if (current < steps) {
      document.getElementById('pipe-' + current).classList.add('active'); // Langkah saat ini aktif
      current++;
      setTimeout(next, 280); // Berpindah ke langkah berikutnya setelah 280ms
    } else {
      callback(); // Panggil fungsi callback setelah semua langkah animasi selesai
    }
  }
  next();
}

// ─── Eksekusi Peramalan (Run Forecasting) ──────────────────────────────────
// Mengirim data form konfigurasi peramalan ke backend menggunakan Fetch API (AJAX)
function runForecasting(e) {
  e.preventDefault();

  const form = document.getElementById('forecastForm');
  const formData = new FormData(form);
  const runBtn = document.getElementById('run-btn');

  // Menonaktifkan tombol sementara proses berjalan untuk menghindari double-click
  runBtn.disabled = true;
  runBtn.innerHTML = '<span class="pulsing">⏳</span> Memproses...';

  // Menyembunyikan hasil lama dan status kosong
  document.getElementById('forecast-results-container').style.display = 'none';
  document.getElementById('empty-state-card').style.display = 'none';

  // Jalankan animasi jalur proses terlebih dahulu, baru kirim request data
  animatePipeline(() => {
    fetch('<?= base_url('prediksi/hitung') ?>', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(res => {
      // Mengaktifkan kembali tombol proses
      runBtn.disabled = false;
      runBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg> Mulai Peramalan';

      // Tangani jika terjadi error pada perhitungan (misal data penjualan kurang)
      if (res.status === 'error') {
        for (let i = 0; i < 5; i++) {
          const el = document.getElementById('pipe-' + i);
          el.classList.remove('active', 'done');
          el.classList.add('error'); // Set status merah/error pada visual pipeline
        }
        showToast('❌ ' + res.message, 'error');
        document.getElementById('empty-state-card').style.display = 'block';
        return;
      }

      // Jika berhasil, panggil fungsi untuk merender hasil ke layar
      displayResults(res.data, res.warnings);
    })
    .catch(err => {
      runBtn.disabled = false;
      runBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg> Mulai Peramalan';
      showToast('❌ Terjadi kesalahan sistem saat memproses peramalan!', 'error');
      document.getElementById('empty-state-card').style.display = 'block';
    });
  });
}

// ─── Tampilkan Hasil Peramalan (Display Results) ──────────────────────────────
// Memasukkan dan merender data kalkulasi peramalan dari server ke elemen HTML di halaman
function displayResults(data, warnings) {
  document.getElementById('forecast-results-container').style.display = 'block';
  lastForecastData = data; // Simpan data untuk pemindahan/switch jenis grafik nanti

  const maPeriod = parseInt(document.getElementById('ma_period').value);
  
  // Tampilkan peringatan jika ada tipe iPhone yang dilewati karena kekurangan data
  if (warnings && warnings.length > 0) {
    const skippedNames = warnings.map(w => w.nama_tipe).join(', ');
    showToast('⚠️ Beberapa tipe dilewati (kurang data): ' + skippedNames, 'error');
  } else {
    showToast('✅ Peramalan berhasil dijalankan untuk seluruh model iPhone!', 'success');
  }

  // 1. Render KPI Scorecards (Kartu Ringkasan untuk setiap tipe iPhone)
  let scorecardsHtml = '';
  Object.keys(data).forEach(id => {
    const d = data[id];
    const labelCls = d.label.toLowerCase(); // green, yellow, atau red
    const labelText = d.label === 'GREEN' ? '🟢 AKURAT (Profit)'
                    : d.label === 'YELLOW' ? '🟡 MODERAT (Cukup)'
                    : '🔴 RISIKO (Rendah)';
    const trendIcon = d.trend > 5 ? '📈' : (d.trend < -5 ? '📉' : '➡️');
    const trendSign = d.trend >= 0 ? '+' : '';
    const trendText = trendSign + d.trend.toFixed(1) + '%';

    scorecardsHtml += `
      <div class="result-card ${labelCls}">
        <div class="card-header">
          <div class="card-series">📱 ${d.nama_tipe}</div>
          <div class="label-badge ${labelCls}">${labelText}</div>
        </div>
        <div class="metric-row">
          <div class="metric">
            <div class="metric-label">Forecast (SMA-${maPeriod})</div>
            <div class="metric-val ${labelCls}">${Math.round(d.forecast_adj).toLocaleString()}</div>
            <div class="metric-unit">unit / bulan (${d.next_label})</div>
          </div>
          <div class="metric">
            <div class="metric-label">Saran Pengadaan</div>
            <div class="metric-val blue">${d.rec_qty.toLocaleString()}</div>
            <div class="metric-unit">unit total (safety incl.)</div>
          </div>
        </div>
      </div>
    `;
  });
  document.getElementById('scorecards-container').innerHTML = scorecardsHtml;

  // 2. Mengatur Subtitle Grafik & Membuat Tombol Tab Tipe iPhone secara Dinamis
  const firstKey = Object.keys(data)[0];
  const nextLabel = data[firstKey].next_label;
  const numPeriods = data[firstKey].labels.length;
  document.getElementById('chart-subtitle').textContent = `Periode Target: ${nextLabel} · ${numPeriods} Periode Historis · MA-${maPeriod}`;

  let tabsHtml = `<button class="chart-tab active" onclick="switchChart('all', this)">Semua Tipe</button>`;
  Object.keys(data).forEach(id => {
    const d = data[id];
    tabsHtml += `<button class="chart-tab" onclick="switchChart('${id}', this)">${d.nama_tipe}</button>`;
  });
  tabsHtml += `<button class="chart-tab" onclick="switchChart('mape', this)">MAPE Error</button>`;
  document.getElementById('chart-tabs-container').innerHTML = tabsHtml;

  // 3. Render Grafik Penjualan Awal (Menampilkan semua tipe iPhone)
  renderChart(data, 'all');

  // 4. Render Tabel Kalkulasi Detail beserta Tombol Export File (CSV & PDF)
  let tablesHtml = '';
  Object.keys(data).forEach(id => {
    const d = data[id];
    const exportCsvUrl = '<?= base_url('prediksi/export_csv/') ?>' + d.id_prediksi;
    const exportPdfUrl = '<?= base_url('prediksi/export_pdf/') ?>' + d.id_prediksi;
    const mapeColor = getMapeColor(d.avg_mape);

    tablesHtml += `
      <div style="margin-bottom: 30px;">
        <div class="section-title">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
          Tabel Kalkulasi: ${d.nama_tipe}
        </div>
        <div class="table-card">
          <div class="table-toolbar" style="font-size:11px; display:flex; justify-content:space-between; align-items:center;">
            <div>
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              <span>moving_average_${d.nama_tipe.toLowerCase().replace(/\s+/g, '_')}.log</span>
              <span style="color:var(--text-muted);">·</span>
              <span>MAPE rata-rata: <b style="color:${mapeColor}">${d.avg_mape.toFixed(2)}%</b></span>
            </div>
            <div style="display:flex; gap:6px;">
              <a href="${exportCsvUrl}" class="tb-btn" style="text-decoration:none;">📥 Export CSV</a>
              <a href="${exportPdfUrl}" target="_blank" class="tb-btn primary" style="text-decoration:none;">📄 Export PDF</a>
            </div>
          </div>
          <div style="overflow-x: auto;">
            <table class="data-table" style="width:100%; border-collapse:collapse; font-family: var(--font-mono); font-size:12px;">
              <thead>
                <tr>
                  <th style="background:var(--bg-elevated); border-bottom:1px solid var(--border); padding:7px 12px; text-align:left; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">Bulan</th>
                  <th style="background:var(--bg-elevated); border-bottom:1px solid var(--border); padding:7px 12px; text-align:right; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">Aktual (unit)</th>
                  <th style="background:var(--bg-elevated); border-bottom:1px solid var(--border); padding:7px 12px; text-align:right; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">SMA-${maPeriod} (unit)</th>
                  <th style="background:var(--bg-elevated); border-bottom:1px solid var(--border); padding:7px 12px; text-align:right; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">Error (unit)</th>
                  <th style="background:var(--bg-elevated); border-bottom:1px solid var(--border); padding:7px 12px; text-align:right; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">|Error/Aktual|</th>
                  <th style="background:var(--bg-elevated); border-bottom:1px solid var(--border); padding:7px 12px; text-align:right; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">MAPE (%)</th>
                  <th style="background:var(--bg-elevated); border-bottom:1px solid var(--border); padding:7px 12px; text-align:center; color:var(--text-muted); font-size:10px; text-transform:uppercase; letter-spacing:.5px; font-weight:600;">Status Akurasi</th>
                </tr>
              </thead>
              <tbody>
    `;

    // Looping data penjualan aktual bulanan untuk mengisi baris tabel kalkulasi historis
    d.sales.forEach((actual, i) => {
      const ma_val  = d.ma[i];
      const ape_val = d.ape[i];
      const isLast  = (i === d.sales.length - 1); // Menandai baris historis paling akhir

      let ma_str    = '<span style="color:var(--text-muted)">—</span>';
      let err_str   = '<span style="color:var(--text-muted)">—</span>';
      let ratio_str = '<span style="color:var(--text-muted)">—</span>';
      let ape_str   = '<span style="color:var(--text-muted)">—</span>';
      let badge_str = '<span style="color:var(--text-muted);font-size:11px;">Belum cukup data</span>';

      // Jika data SMA pada bulan ke-i tersedia, hitung nilai error-nya
      if (ma_val !== null) {
        const err   = Math.abs(actual - ma_val); // Error = |Aktual - Peramalan|
        const ratio = actual > 0 ? (err / actual) : 0; // Rasio kesalahan dibanding aktual

        ma_str    = ma_val.toFixed(1);
        err_str   = err.toFixed(1);
        ratio_str = ratio.toFixed(4);
        ape_str   = `<span style="color:${getMapeColor(ape_val)};font-weight:600">${ape_val.toFixed(2)}%</span>`;
        badge_str = mapeBadge(ape_val); // Badge tingkat akurasi (Akurat, Cukup, Rendah)
      }

      // Memberi warna latar khusus pada baris data historis terakhir
      const rowStyle = isLast ? 'background:var(--accent-blue-subtle);' : '';
      const labelStyle = isLast ? 'font-weight:700;color:var(--accent-blue);' : 'color:var(--text-primary);';

      tablesHtml += `
        <tr style="${rowStyle}">
          <td style="padding:6px 12px; border-bottom:1px solid var(--border-muted); ${labelStyle}">${d.labels[i]}</td>
          <td style="padding:6px 12px; border-bottom:1px solid var(--border-muted); text-align:right;">${actual.toLocaleString()}</td>
          <td style="padding:6px 12px; border-bottom:1px solid var(--border-muted); text-align:right;">${ma_str}</td>
          <td style="padding:6px 12px; border-bottom:1px solid var(--border-muted); text-align:right;">${err_str}</td>
          <td style="padding:6px 12px; border-bottom:1px solid var(--border-muted); text-align:right; font-family:var(--font-mono);">${ratio_str}</td>
          <td style="padding:6px 12px; border-bottom:1px solid var(--border-muted); text-align:right;">${ape_str}</td>
          <td style="padding:6px 12px; border-bottom:1px solid var(--border-muted); text-align:center;">${badge_str}</td>
        </tr>
      `;
    });

    // Menambahkan baris kaki tabel (Tfoot) untuk menampilkan proyeksi bulan depan secara ringkas
    tablesHtml += `
      </tbody>
      <tfoot>
        <tr style="background:var(--green-subtle); font-weight:bold;">
          <td style="padding:6px 12px; color:var(--green);">⬡ PREDIKSI DEPAN</td>
          <td style="padding:6px 12px; text-align:right; color:var(--green);">${Math.round(d.forecast_adj).toLocaleString()}</td>
          <td style="padding:6px 12px; text-align:right; color:var(--text-muted); font-size:11px;">Adjusted</td>
          <td colspan="4" style="padding:6px 12px; color:var(--text-muted); font-size:11px; font-style:italic;">
            Seasonal Indeks: ${d.seasonal_idx.toFixed(3)} &nbsp;·&nbsp; Safety Stock Buffer: +${d.safety_stock} unit &nbsp;·&nbsp; Saran Stok Akhir: <strong style="color:var(--green)">${d.rec_qty} unit</strong>
          </td>
        </tr>
      </tfoot>
      </table></div></div></div>
    `;
  });
  document.getElementById('calculation-tables-wrapper').innerHTML = tablesHtml;

  // Inisialisasi plugin jQuery DataTables untuk fitur pencarian, filter, dan pagination dinamis
  $('#calculation-tables-wrapper table.data-table').each(function() {
    if (!$.fn.dataTable.isDataTable(this)) {
      $(this).DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
        language: {
          search: 'Cari:',
          lengthMenu: 'Tampilkan _MENU_ baris',
          info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ baris',
          paginate: { previous: 'Sebelumnya', next: 'Selanjutnya' },
          zeroRecords: 'Tidak ditemukan data yang cocok',
          infoEmpty: 'Tidak ada data tersedia',
          infoFiltered: '(difilter dari _MAX_ total baris)'
        }
      });
    }
  });

  // 5. Render Rekomendasi Stok Akhir & Strategi Bisnis
  renderRecommendations(data);
}

// ─── Render Grafik Penjualan (Render Chart) ─────────────────────────────────────
// Menggunakan library Chart.js untuk menggambar visualisasi data aktual vs peramalan SMA
function renderChart(data, mode) {
  const ctx = document.getElementById('main-chart');
  // Jika grafik sudah ada sebelumnya, hancurkan/destroy agar tidak tumpang tindih
  if (mainChart) {
    mainChart.destroy();
    mainChart = null;
  }

  lastChartMode = mode;
  const maPeriod = parseInt(document.getElementById('ma_period').value);
  const threshold_green  = <?= $this->config->item('mape_green') ?>;
  const threshold_yellow = <?= $this->config->item('mape_yellow') ?>;

  const colorPalette = ['#2f81f7', '#3fb950', '#bc8cff', '#ffa657', '#ff7b72', '#79c0ff'];
  const maColorPalette = ['#ffa657', '#ff7b72', '#79c0ff', '#2f81f7', '#3fb950', '#bc8cff'];

  let datasets = [];
  let chartLabels = [];

  const firstKey = Object.keys(data)[0];
  const baseLabels = data[firstKey].labels;
  const nextMonth = data[firstKey].next_month;

  // MODE A: Menampilkan Grafik Kesalahan MAPE (Mean Absolute Percentage Error)
  if (mode === 'mape') {
    chartLabels = baseLabels;
    let idx = 0;
    Object.keys(data).forEach(id => {
      const d = data[id];
      const color = colorPalette[idx % colorPalette.length];
      const mapeData = baseLabels.map((_, i) => d.ape[i] !== null ? parseFloat(d.ape[i].toFixed(2)) : null);

      datasets.push({
        label: `MAPE Error ${d.nama_tipe} (%)`,
        data: mapeData,
        borderColor: color,
        backgroundColor: color + '15',
        fill: true,
        tension: 0.4,
        spanGaps: true,
        pointRadius: mapeData.map(v => v !== null ? 4 : 0),
        borderWidth: 2
      });
      idx++;
    });

    // Menambahkan garis batas (threshold) evaluasi kualitas peramalan
    datasets.push({
      label: `Threshold Hijau (${threshold_green}%)`,
      data: baseLabels.map(() => threshold_green),
      borderColor: 'rgba(63,185,80,0.5)',
      borderDash: [6, 4],
      borderWidth: 1.5,
      pointRadius: 0,
      fill: false
    });
    datasets.push({
      label: `Threshold Kuning (${threshold_yellow}%)`,
      data: baseLabels.map(() => threshold_yellow),
      borderColor: 'rgba(210,153,34,0.5)',
      borderDash: [6, 4],
      borderWidth: 1.5,
      pointRadius: 0,
      fill: false
    });

  // MODE B: Menampilkan Data Aktual & Hasil Prediksi Besok untuk SEMUA tipe iPhone
  } else if (mode === 'all') {
    chartLabels = [...baseLabels, '⬡ Prediksi (' + nextMonth + ')'];
    
    let idx = 0;
    Object.keys(data).forEach(id => {
      const d = data[id];
      const color = colorPalette[idx % colorPalette.length];
      const forecastData = d.sales.map(() => null); // Isi null untuk data historis
      forecastData.push(Math.round(d.forecast_adj)); // Letakkan data prediksi di akhir

      datasets.push({
        label: `Aktual ${d.nama_tipe}`,
        data: [...d.sales, null],
        borderColor: color,
        backgroundColor: color + '12',
        fill: true,
        tension: 0.4,
        borderWidth: 2.5,
        pointRadius: 4
      });

      datasets.push({
        label: `Forecast ${d.nama_tipe}`,
        data: forecastData,
        borderColor: color,
        backgroundColor: color,
        pointStyle: 'triangle',
        pointRadius: 9,
        showLine: false
      });
      idx++;
    });

  // MODE C: Menampilkan Grafik Detail khusus untuk SATU model iPhone terpilih (Aktual vs SMA-n vs Prediksi)
  } else {
    const d = data[mode];
    if (!d) return;

    chartLabels = [...d.labels, '⬡ Prediksi (' + nextMonth + ')'];
    const color = colorPalette[0];
    const maColor = maColorPalette[0];
    const forecastData = d.sales.map(() => null);
    forecastData.push(Math.round(d.forecast_adj));

    datasets = [
      {
        label: `Aktual Penjualan ${d.nama_tipe}`,
        data: [...d.sales, null],
        borderColor: color,
        backgroundColor: 'rgba(47,129,247,0.12)',
        fill: true,
        tension: 0.4,
        borderWidth: 2.5,
        pointRadius: 4
      },
      {
        label: `Moving Average SMA-${maPeriod} ${d.nama_tipe}`,
        data: [...d.ma, null],
        borderColor: maColor,
        borderDash: [6, 4],
        fill: false,
        tension: 0.4,
        borderWidth: 2,
        pointRadius: 0,
        spanGaps: true
      },
      {
        label: `Forecast (Prediksi) ${d.nama_tipe}`,
        data: forecastData,
        borderColor: '#3fb950',
        backgroundColor: '#3fb950',
        pointStyle: 'triangle',
        pointRadius: 9,
        showLine: false
      }
    ];
  }

  // Membuat instance Chart.js baru dengan konfigurasi yang ditentukan
  mainChart = new Chart(ctx, {
    type: 'line',
    data: { labels: chartLabels, datasets },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          labels: {
            color: '#8b949e',
            font: { family: "'JetBrains Mono', monospace", size: 11 },
            boxWidth: 14
          }
        },
        tooltip: {
          backgroundColor: '#161b22',
          borderColor: '#30363d',
          borderWidth: 1,
          titleColor: '#e6edf3',
          bodyColor: '#8b949e',
          callbacks: {
            label: function(context) {
              const v = context.parsed.y;
              return ` ${context.dataset.label}: ${v !== null ? (mode === 'mape' ? v.toFixed(2) + '%' : Math.round(v).toLocaleString() + ' unit') : '—'}`;
            }
          }
        }
      },
      scales: {
        x: {
          ticks: { color: '#8b949e', font: { family: "'JetBrains Mono', monospace", size: 10 } },
          grid: { color: '#21262d' }
        },
        y: {
          ticks: {
            color: '#8b949e',
            font: { family: "'JetBrains Mono', monospace", size: 10 },
            callback: v => mode === 'mape' ? v.toFixed(1) + '%' : v.toLocaleString()
          },
          grid: { color: '#21262d' }
        }
      }
    }
  });
}

// ─── Pindah Tab Grafik (Switch Chart Tabs) ────────────────────────────────
// Fungsi saat tombol pilihan tipe iPhone di atas grafik di-klik
function switchChart(mode, btn) {
  document.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active'); // Set status tombol aktif
  if (lastForecastData) {
    renderChart(lastForecastData, mode); // Gambar ulang grafik dengan filter yang baru
  }
}

// ─── Render Rekomendasi Stok & Penjualan (Render Recommendations) ──────────
// Menyusun peringkat akurasi peramalan serta melampirkan strategi bisnis (stok, penjualan, promo)
function renderRecommendations(data) {
  const models = Object.keys(data).map(id => data[id]);
  // Urutkan model iPhone berdasarkan peringkat dari server (ascending: peringkat 1, 2, 3...)
  models.sort((a, b) => a.peringkat - b.peringkat);

  // Menentukan model iPhone dengan peringkat ke-1 (akurasi tertinggi/terbaik)
  const best = models.find(m => m.peringkat === 1);
  let bestHtml = '';
  if (best) {
    bestHtml = `
      <div style="background:var(--accent-blue-subtle); border: 1px solid rgba(47,129,247,0.2); border-radius:var(--radius-lg); padding:14px 18px; margin-bottom:16px; display:flex; align-items:center; gap:12px" class="animate-in">
        <div style="font-size:22px">⭐</div>
        <div>
          <div style="font-weight:700;color:var(--accent-blue);font-size:13px;margin-bottom:2px">Prioritas Utama — Rekomendasi Utama Pengadaan Stok</div>
          <div style="font-size:13px;color:var(--text-primary)">
            Model <b>${best.nama_tipe}</b> menempati peringkat ke-1 dengan akurasi peramalan tertinggi untuk bulan <b>${best.next_label}</b>.
            Disarankan menyuplai <b>${best.rec_qty.toLocaleString()} unit</b>.
          </div>
          <div style="font-size:11px;color:var(--text-muted);margin-top:2px;font-family:var(--font-mono)">
            Rata-rata MAPE Error: ${best.avg_mape.toFixed(2)}% · Alokasikan modal &amp; display toko pada tipe prioritas utama ini.
          </div>
        </div>
      </div>
    `;
  }

  // Template strategi bisnis untuk setiap kategori status (GREEN, YELLOW, RED)
  const recStrategies = {
    GREEN: {
      stok: 'Stok penuh sesuai rekomendasi',
      jual: 'Jual harga normal + upsell aksesori (case, pelindung layar, earphone)',
      promo: 'Tidak perlu promo besar, cukup program loyalitas',
      risiko: 'Rendah'
    },
    YELLOW: {
      stok: 'Stok 80% dari rekomendasi',
      jual: 'Bundling produk + cicilan 0% untuk percepat perputaran',
      promo: 'Flash sale ringan, cashback 5-10%',
      risiko: 'Sedang — monitor mingguan'
    },
    RED: {
      stok: 'Maks 60% dari rekomendasi, utamakan clearance stok lama',
      jual: 'Diskon agresif 10-20%, alihkan anggaran ke seri lebih prospektif',
      promo: 'Clearance event, bundling wajib aksesori',
      risiko: 'Tinggi — hindari overstock'
    }
  };

  // Membuat susunan kartu rekomendasi untuk seluruh model iPhone
  let cardsHtml = '';
  models.forEach(d => {
    const cls = d.label.toLowerCase();
    const rec = recStrategies[d.label] || recStrategies['RED'];
    const trendIcon = d.trend > 5 ? '📈' : (d.trend < -5 ? '📉' : '➡️');
    const trendStr = (d.trend >= 0 ? '+' : '') + d.trend.toFixed(1) + '%';
    const mapeColor = getMapeColor(d.avg_mape);

    cardsHtml += `
      <div class="result-card ${cls}" style="margin-bottom:14px;">
        <div class="card-header">
          <div class="card-series">📱 Peringkat #${d.peringkat} : ${d.nama_tipe}</div>
          <div class="label-badge ${cls}">${d.label === 'GREEN' ? '🟢 PROFIT' : d.label === 'YELLOW' ? '🟡 MODERAT' : '🔴 RISIKO'}</div>
        </div>
        <div class="metric-row" style="grid-template-columns:repeat(4,1fr)">
          <div class="metric">
            <div class="metric-label">Forecast Adj.</div>
            <div class="metric-val ${cls}">${Math.round(d.forecast_adj).toLocaleString()}</div>
            <div class="metric-unit">unit</div>
          </div>
          <div class="metric">
            <div class="metric-label">Safety Stock</div>
            <div class="metric-val blue">+${d.safety_stock}</div>
            <div class="metric-unit">buffer</div>
          </div>
          <div class="metric">
            <div class="metric-label">Saran Stok</div>
            <div class="metric-val blue">${d.rec_qty.toLocaleString()}</div>
            <div class="metric-unit">unit total</div>
          </div>
          <div class="metric">
            <div class="metric-label">Tren 3bln</div>
            <div class="metric-val orange">${trendIcon} ${trendStr}</div>
            <div class="metric-unit">growth</div>
          </div>
        </div>
        <div class="rec-box ${cls}"><strong>📦 Stok:</strong> ${rec.stok}</div>
        <div class="rec-box ${cls}"><strong>🛒 Strategi Jual:</strong> ${rec.jual}</div>
        <div class="rec-box ${cls}"><strong>📣 Promosi:</strong> ${rec.promo}</div>
        <div class="rec-box ${cls}" style="font-size:11px">
          <strong>⚠️ Risiko:</strong> ${rec.risiko} &nbsp;·&nbsp;
          <strong>MAPE:</strong> <span style="color:${mapeColor}">${d.avg_mape.toFixed(2)}%</span> &nbsp;·&nbsp;
          <strong>Seasonal Idx:</strong> ${d.seasonal_idx.toFixed(3)} &nbsp;·&nbsp;
          <strong>Target Bulan:</strong> ${d.next_label}
        </div>
      </div>
    `;
  });

  const footerHtml = `
    <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius);padding:12px 16px;font-size:11px;color:var(--text-muted);font-family:var(--font-mono)">
      🔢 Formula: MA-<?= $this->config->item('ma_period') ?> · Threshold MAPE Hijau≤<?= $this->config->item('mape_green') ?>% Kuning≤<?= $this->config->item('mape_yellow') ?>% · Safety Stock Z₀.₉₅=1.645 · Safety Factor=<?= $this->config->item('safety_factor') ?>
    </div>
  `;

  document.getElementById('recommend-content').innerHTML = bestHtml + cardsHtml + footerHtml;
}

// ─── Helpers & Utilities ──────────────────────────────────────────
// Mendapatkan kode warna berdasarkan nilai error MAPE (Hijau = Bagus, Kuning = Sedang, Merah = Kurang)
function getMapeColor(val) {
  const g = <?= $this->config->item('mape_green') ?>;
  const y = <?= $this->config->item('mape_yellow') ?>;
  if (val <= g) return 'var(--green)';
  if (val <= y) return 'var(--yellow)';
  return 'var(--red)';
}

// Mendapatkan elemen badge tingkat akurasi MAPE untuk baris tabel kalkulasi
function mapeBadge(val) {
  const g = <?= $this->config->item('mape_green') ?>;
  const y = <?= $this->config->item('mape_yellow') ?>;
  const cls   = val <= g ? 'green' : val <= y ? 'yellow' : 'red';
  const label = val <= g ? '✓ Akurat' : val <= y ? '~ Cukup' : '✗ Rendah';
  const bg = { green: 'var(--green-subtle)', yellow: 'var(--yellow-subtle)', red: 'var(--red-subtle)' }[cls];
  const fg = { green: 'var(--green)',        yellow: 'var(--yellow)',        red: 'var(--red)'        }[cls];
  return `<span style="background:${bg};color:${fg};border:1px solid ${fg}44;padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700">${label}</span>`;
}

// Melakukan scroll layar otomatis secara halus ke tabel perhitungan detail
function scrollToCalc() {
  document.getElementById('calculation-tables-wrapper').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Melakukan scroll layar otomatis secara halus ke panel rekomendasi stok
function scrollToRecommend() {
  document.getElementById('recommendation-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ─── Notifikasi Toast (Toast Notification) ───────────────────────────────
// Menampilkan pop-up notifikasi melayang di sudut kanan bawah layar selama 4 detik
function showToast(msg, type) {
  let toast = document.getElementById('forecast-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'forecast-toast';
    toast.style.cssText = `
      position: fixed; bottom: 24px; right: 24px; z-index: 9999;
      padding: 12px 18px; border-radius: 8px; font-size: 13px;
      font-family: var(--font-sans); font-weight: 500;
      box-shadow: 0 4px 24px rgba(0,0,0,0.4);
      transition: opacity 0.4s ease, transform 0.4s ease;
      max-width: 380px;
    `;
    document.body.appendChild(toast);
  }

  const colors = {
    success: { bg: 'var(--green-subtle)', color: 'var(--green)', border: 'rgba(63,185,80,0.3)' },
    error:   { bg: 'var(--red-subtle)',   color: 'var(--red)',   border: 'rgba(248,81,73,0.3)' }
  };
  const c = colors[type] || colors.success;
  toast.style.background = c.bg;
  toast.style.color = c.color;
  toast.style.border = `1px solid ${c.border}`;
  toast.style.opacity = '1';
  toast.style.transform = 'translateY(0)';
  toast.textContent = msg;

  clearTimeout(toast._timer);
  // Hilangkan toast secara otomatis setelah 4 detik
  toast._timer = setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
  }, 4000);
}
</script>

<?php $this->load->view('partials/footer'); ?>
