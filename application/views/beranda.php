<?php $this->load->view('partials/header'); ?>
<?php $this->load->view('partials/sidebar'); ?>

<!-- PAGE HEADER -->
<div class="page-header">
  <div>
    <div class="page-title">📱 Beranda</div>
    <div class="page-subtitle">Sistem Informasi Analitik & Prediksi Stok Lestari iPhone</div>
  </div>
</div>

<!-- ANALYTICS CARDS -->
<div class="results-grid animate-in" style="margin-bottom: 24px;">
  <!-- Card 1: Total Sales -->
  <div class="result-card green">
    <div class="card-header">
      <div class="card-series">Transaksi Penjualan</div>
      <div class="label-badge green">DATABASE</div>
    </div>
    <div class="metric-row" style="grid-template-columns: 1fr;">
      <div class="metric" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
          <div class="metric-label">Total Record</div>
          <div class="metric-val green"><?= $total_penjualan ?></div>
          <div class="metric-unit">transaksi terdaftar</div>
        </div>
        <div style="font-size: 24px;">📊</div>
      </div>
    </div>
  </div>

  <!-- Card 2: Total iPhone -->
  <div class="result-card green">
    <div class="card-header">
      <div class="card-series">Model iPhone</div>
      <div class="label-badge green">MASTER DATA</div>
    </div>
    <div class="metric-row" style="grid-template-columns: 1fr;">
      <div class="metric" style="display: flex; align-items: center; justify-content: space-between;">
        <div>
          <div class="metric-label">Jumlah Tipe</div>
          <div class="metric-val blue"><?= $total_iphone ?></div>
          <div class="metric-unit">tipe aktif</div>
        </div>
        <div style="font-size: 24px;">📱</div>
      </div>
    </div>
  </div>

  <!-- Card 3: Last Run & Avg MAPE -->
  <div class="result-card yellow">
    <div class="card-header">
      <div class="card-series">Akurasi & Run Terakhir</div>
      <div class="label-badge yellow">ENGINE STATE</div>
    </div>
    <div class="metric-row" style="grid-template-columns: 1fr 1fr; gap: 8px;">
      <div class="metric">
        <div class="metric-label">Avg MAPE Error</div>
        <div class="metric-val yellow"><?= $avg_mape ?></div>
        <div class="metric-unit">error rata-rata</div>
      </div>
      <div class="metric">
        <div class="metric-label">Run Terakhir</div>
        <div style="font-size: 13px; font-family: var(--font-mono); font-weight: 700; color: var(--text-primary); margin-top: 5px;">
          <?= $last_run ?>
        </div>
        <div class="metric-unit" style="margin-top: 5px;">waktu hitung</div>
      </div>
    </div>
  </div>
</div>

<!-- RECOMMENDATIONS SECTION -->
<div class="section-title">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
  Grafik Tren &amp; Rekomendasi Prioritas Stok Terkini
</div>

<?php if (!empty($forecast_results)) : ?>
  <!-- CHART CARD -->
  <div class="chart-card animate-in" style="margin-bottom: 24px;">
    <div class="chart-title" id="chart-title">Tren Penjualan &amp; Moving Average</div>
    <div class="chart-subtitle" id="chart-subtitle">—</div>
    <div class="chart-tabs" id="chart-tabs-container">
      <!-- Dynamic tabs injected by JS -->
    </div>
    <div class="chart-wrap">
      <canvas id="main-chart"></canvas>
    </div>
  </div>

  <!-- RECOMMENDATION CARDS CONTAINER -->
  <div id="recommendation-container" style="margin-top: 20px;" class="animate-in">
    <div id="recommend-content">
      <!-- Dynamic cards injected by JS -->
    </div>
  </div>

  <script>
  let mainChart = null;
  let lastChartMode = 'all';
  const lastForecastData = <?= json_encode($forecast_results) ?>;

  document.addEventListener('DOMContentLoaded', function() {
    // Build Tabs dynamically
    const tabsContainer = document.getElementById('chart-tabs-container');
    let tabsHtml = `<button class="chart-tab active" onclick="switchChart('all', this)">Semua Tipe</button>`;
    
    Object.keys(lastForecastData).forEach(id => {
      const d = lastForecastData[id];
      tabsHtml += `<button class="chart-tab" onclick="switchChart('${id}', this)">${d.nama_tipe}</button>`;
    });
    
    tabsHtml += `<button class="chart-tab" onclick="switchChart('mape', this)">MAPE Error (%)</button>`;
    tabsContainer.innerHTML = tabsHtml;

    // Initial render
    renderChart(lastForecastData, 'all');
    renderRecommendations(lastForecastData);
  });

  // ─── Render Chart ─────────────────────────────────────
  function renderChart(data, mode) {
    const ctx = document.getElementById('main-chart');
    if (mainChart) {
      mainChart.destroy();
      mainChart = null;
    }

    lastChartMode = mode;
    const maPeriod = <?= $this->config->item('ma_period') ?>;
    const threshold_green  = <?= $this->config->item('mape_green') ?>;
    const threshold_yellow = <?= $this->config->item('mape_yellow') ?>;

    const colorPalette = ['#2f81f7', '#3fb950', '#bc8cff', '#ffa657', '#ff7b72', '#79c0ff'];
    const maColorPalette = ['#ffa657', '#ff7b72', '#79c0ff', '#2f81f7', '#3fb950', '#bc8cff'];

    let datasets = [];
    let chartLabels = [];

    const firstKey = Object.keys(data)[0];
    const baseLabels = data[firstKey].labels;
    const nextMonth = data[firstKey].next_month;

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

      // Add thresholds
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

    } else if (mode === 'all') {
      chartLabels = [...baseLabels, '⬡ Prediksi (' + nextMonth + ')'];
      
      let idx = 0;
      Object.keys(data).forEach(id => {
        const d = data[id];
        const color = colorPalette[idx % colorPalette.length];
        const forecastData = d.sales.map(() => null);
        forecastData.push(Math.round(d.forecast_adj));

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

  // ─── Switch Chart Tabs ────────────────────────────────
  function switchChart(mode, btn) {
    document.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    if (lastForecastData) {
      renderChart(lastForecastData, mode);
    }
  }

  // ─── Render Recommendations ───────────────────────────
  function renderRecommendations(data) {
    const models = Object.keys(data).map(id => data[id]);
    models.sort((a, b) => a.peringkat - b.peringkat);

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

  // ─── Helpers ──────────────────────────────────────────
  function getMapeColor(val) {
    const g = <?= $this->config->item('mape_green') ?>;
    const y = <?= $this->config->item('mape_yellow') ?>;
    if (val <= g) return 'var(--green)';
    if (val <= y) return 'var(--yellow)';
    return 'var(--red)';
  }
  </script>
<?php else : ?>
  <!-- Empty State -->
  <div class="table-card animate-in">
    <div class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <p>Data transaksi penjualan belum mencukupi untuk peramalan</p>
      <small>Harap masukkan data transaksi penjualan minimal selama <strong>4 bulan</strong>.</small>
    </div>
  </div>
<?php endif; ?>

<?php $this->load->view('partials/footer'); ?>
