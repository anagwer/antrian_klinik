<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($title) ? $title : 'Smartphone Forecasting' ?> — Moving Average + MAPE</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
:root {
  --bg-canvas:   #0d1117;
  --bg-surface:  #161b22;
  --bg-elevated: #1c2128;
  --bg-input:    #0d1117;
  --border:      #30363d;
  --border-muted:#21262d;
  --text-primary:#e6edf3;
  --text-secondary:#8b949e;
  --text-muted:  #484f58;
  --accent-blue: #2f81f7;
  --accent-blue-subtle:#1f6feb22;
  --green:       #3fb950;
  --green-subtle:#1f6a2022;
  --yellow:      #d29922;
  --yellow-subtle:#b2800022;
  --red:         #f85149;
  --red-subtle:  #a9220022;
  --purple:      #bc8cff;
  --orange:      #ffa657;
  --radius:      6px;
  --radius-lg:   10px;
  --font-mono:   'JetBrains Mono', monospace;
  --font-sans:   'Sora', sans-serif;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  background: var(--bg-canvas);
  color: var(--text-primary);
  font-family: var(--font-sans);
  font-size: 14px;
  line-height: 1.6;
  min-height: 100vh;
}

/* ── TOP NAV ─────────────────────────────── */
.topnav {
  background: var(--bg-surface);
  border-bottom: 1px solid var(--border);
  padding: 0 24px;
  display: flex;
  align-items: center;
  gap: 20px;
  height: 56px;
  position: sticky;
  top: 0;
  z-index: 100;
}
.nav-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  font-size: 15px;
  color: var(--text-primary);
  text-decoration: none;
}
.nav-logo svg { color: var(--text-primary); }
.nav-breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--text-secondary);
}
.nav-breadcrumb a { color: var(--accent-blue); text-decoration: none; }
.nav-breadcrumb a:hover { text-decoration: underline; }
.nav-badge {
  background: var(--accent-blue);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 20px;
  font-family: var(--font-mono);
  letter-spacing: .5px;
}
.nav-right { margin-left: auto; display: flex; align-items: center; gap: 10px; }
.nav-btn {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  color: var(--text-primary);
  font-family: var(--font-sans);
  font-size: 12px;
  padding: 5px 12px;
  border-radius: var(--radius);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: background .15s, border-color .15s;
  text-decoration: none;
}
.nav-btn:hover { background: var(--border-muted); border-color: var(--text-muted); }

/* ── LAYOUT ──────────────────────────────── */
.layout {
  display: grid;
  grid-template-columns: 260px 1fr;
  min-height: calc(100vh - 56px);
}

/* ── SIDEBAR ─────────────────────────────── */
.sidebar {
  background: var(--bg-surface);
  border-right: 1px solid var(--border);
  padding: 16px 0;
  position: sticky;
  top: 56px;
  height: calc(100vh - 56px);
  overflow-y: auto;
}
.sidebar-section { margin-bottom: 8px; }
.sidebar-heading {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 8px 16px 4px;
  font-family: var(--font-mono);
}
.sidebar-item {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 8px 16px;
  font-size: 13px;
  color: var(--text-secondary);
  cursor: pointer;
  transition: background .12s, color .12s;
  border-left: 2px solid transparent;
  user-select: none;
  text-decoration: none;
}
.sidebar-item:hover { background: var(--bg-elevated); color: var(--text-primary); }
.sidebar-item.active {
  background: var(--accent-blue-subtle);
  color: var(--accent-blue);
  border-left-color: var(--accent-blue);
}
.sidebar-item .icon { width: 16px; height: 16px; opacity: .7; flex-shrink: 0; stroke: currentColor; }
.sidebar-item.active .icon { opacity: 1; }
.sidebar-divider { border: none; border-top: 1px solid var(--border-muted); margin: 8px 16px; }

/* ── MAIN CONTENT ───────────────────────── */
.main { padding: 24px 32px; overflow-y: auto; }

/* ── PAGE HEADER ─────────────────────────── */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--border);
}
.page-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--text-secondary); }

/* ── BUTTONS ────────────────────────────── */
.btn-primary {
  background: var(--accent-blue);
  color: #fff;
  border: none;
  font-family: var(--font-sans);
  font-size: 13px;
  font-weight: 600;
  padding: 8px 18px;
  border-radius: var(--radius);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 7px;
  transition: opacity .15s, transform .1s;
  white-space: nowrap;
  text-decoration: none;
}
.btn-primary:hover { opacity: .88; }
.btn-primary:active { transform: scale(.97); }

.btn-secondary {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  color: var(--text-primary);
  font-family: var(--font-sans);
  font-size: 13px;
  font-weight: 500;
  padding: 8px 18px;
  border-radius: var(--radius);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 7px;
  transition: background .12s;
  text-decoration: none;
}
.btn-secondary:hover { background: var(--border-muted); }

.btn-danger {
  background: var(--red-subtle);
  border: 1px solid var(--red);
  color: var(--red);
  font-family: var(--font-sans);
  font-size: 13px;
  font-weight: 500;
  padding: 8px 18px;
  border-radius: var(--radius);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 7px;
  transition: background .12s;
  text-decoration: none;
}
.btn-danger:hover { background: var(--red); color: #fff; }

/* ── ALERTS ─────────────────────────────── */
.alert {
  padding: 12px 16px;
  border-radius: var(--radius);
  margin-bottom: 20px;
  font-size: 13px;
  line-height: 1.5;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.alert-success {
  background: var(--green-subtle);
  color: var(--green);
  border: 1px solid var(--green)44;
}
.alert-danger {
  background: var(--red-subtle);
  color: var(--red);
  border: 1px solid var(--red)44;
}

/* ── TABLE CARD ─────────────────────────── */
.table-card {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  margin-bottom: 20px;
}
.table-toolbar {
  background: var(--bg-elevated);
  border-bottom: 1px solid var(--border);
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12px;
  color: var(--text-secondary);
  font-family: var(--font-mono);
}
.table-toolbar-right { margin-left: auto; display: flex; gap: 8px; }

.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
  background: var(--bg-elevated);
  border-bottom: 1px solid var(--border);
  padding: 10px 14px;
  text-align: left;
  font-size: 11px;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: .7px;
  font-family: var(--font-mono);
}
.data-table td {
  border-bottom: 1px solid var(--border-muted);
  padding: 10px 14px;
  vertical-align: middle;
  color: var(--text-primary);
}
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: var(--bg-elevated); }

/* ── FORMS ──────────────────────────────── */
.form-group {
  margin-bottom: 16px;
}
.form-label {
  display: block;
  font-size: 12px;
  color: var(--text-secondary);
  margin-bottom: 6px;
  font-family: var(--font-sans);
  font-weight: 500;
}
.form-input {
  background: var(--bg-canvas);
  border: 1px solid var(--border);
  color: var(--text-primary);
  font-family: var(--font-sans);
  font-size: 14px;
  padding: 8px 12px;
  border-radius: var(--radius);
  width: 100%;
  outline: none;
  transition: border-color .15s;
}
.form-input:focus { border-color: var(--accent-blue); }
select.form-input { cursor: pointer; }

/* ── MODALS ─────────────────────────────── */
.modal {
  display: none;
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(4px);
  z-index: 200;
  align-items: center;
  justify-content: center;
}
.modal.show { display: flex; }
.modal-content {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  width: 450px;
  max-width: 90%;
  overflow: hidden;
  animation: fadeSlideIn .25s ease forwards;
}
.modal-header {
  background: var(--bg-elevated);
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.modal-title { font-size: 14px; font-weight: 600; }
.modal-close {
  background: none;
  border: none;
  color: var(--text-secondary);
  font-size: 18px;
  cursor: pointer;
}
.modal-close:hover { color: var(--text-primary); }
.modal-body { padding: 16px; }
.modal-footer {
  padding: 12px 16px;
  border-top: 1px solid var(--border-muted);
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

/* ── PIPELINE ─────────────────────────────── */
.pipeline {
  display: flex;
  gap: 0;
  margin-bottom: 20px;
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}
.pipe-step {
  flex: 1;
  padding: 14px 16px;
  border-right: 1px solid var(--border);
  position: relative;
  transition: background .15s;
}
.pipe-step:last-child { border-right: none; }
.pipe-step.active { background: var(--accent-blue-subtle); }
.pipe-step.done { background: var(--green-subtle); }
.pipe-step.error { background: var(--red-subtle); }
.pipe-icon { font-size: 16px; margin-bottom: 4px; }
.pipe-name {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: .6px;
  margin-bottom: 2px;
  font-family: var(--font-mono);
}
.pipe-step.active .pipe-name { color: var(--accent-blue); }
.pipe-step.done .pipe-name { color: var(--green); }
.pipe-step.error .pipe-name { color: var(--red); }
.pipe-desc { font-size: 11px; color: var(--text-muted); }
.pipe-connector {
  position: absolute;
  right: -9px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  background: var(--border);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 9px;
  color: var(--text-muted);
  z-index: 1;
  border: 2px solid var(--bg-canvas);
}

/* ── RESULTS GRID ─────────────────────────── */
.results-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
  margin-bottom: 20px;
}
.result-card {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 16px;
  transition: border-color .15s;
  position: relative;
  overflow: hidden;
}
.result-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}
.result-card.green::before { background: var(--green); }
.result-card.yellow::before { background: var(--yellow); }
.result-card.red::before { background: var(--red); }
.result-card:hover { border-color: var(--text-muted); }

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}
.card-series { font-size: 14px; font-weight: 700; color: var(--text-primary); }
.label-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 3px 9px;
  border-radius: 20px;
  font-family: var(--font-mono);
  letter-spacing: .5px;
}
.label-badge.green { background: var(--green-subtle); color: var(--green); border: 1px solid var(--green)44; }
.label-badge.yellow { background: var(--yellow-subtle); color: var(--yellow); border: 1px solid var(--yellow)44; }
.label-badge.red { background: var(--red-subtle); color: var(--red); border: 1px solid var(--red)44; }

.metric-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  margin-bottom: 12px;
}
.metric {
  background: var(--bg-elevated);
  border: 1px solid var(--border-muted);
  border-radius: var(--radius);
  padding: 9px 11px;
}
.metric-label { font-size: 10px; color: var(--text-muted); margin-bottom: 3px; text-transform: uppercase; letter-spacing: .5px; font-family: var(--font-mono); }
.metric-val { font-size: 18px; font-weight: 700; font-family: var(--font-mono); }
.metric-val.green { color: var(--green); }
.metric-val.yellow { color: var(--yellow); }
.metric-val.red { color: var(--red); }
.metric-val.blue { color: var(--accent-blue); }
.metric-val.orange { color: var(--orange); }
.metric-unit { font-size: 10px; color: var(--text-muted); font-family: var(--font-mono); }

.rec-box {
  background: var(--bg-elevated);
  border-radius: var(--radius);
  padding: 10px 12px;
  font-size: 12px;
  color: var(--text-secondary);
  line-height: 1.55;
  border-left: 3px solid var(--border);
  margin-bottom: 8px;
}
.rec-box.green { border-left-color: var(--green); }
.rec-box.yellow { border-left-color: var(--yellow); }
.rec-box.red { border-left-color: var(--red); }
.rec-box strong { color: var(--text-primary); font-weight: 600; }

/* ── CHART AREA ──────────────────────────── */
.chart-card {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 20px;
  margin-bottom: 20px;
}
.chart-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 4px;
}
.chart-subtitle { font-size: 12px; color: var(--text-muted); margin-bottom: 16px; font-family: var(--font-mono); }
.chart-tabs {
  display: flex;
  gap: 4px;
  margin-bottom: 16px;
  border-bottom: 1px solid var(--border);
  padding-bottom: 0;
}
.chart-tab {
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  color: var(--text-secondary);
  font-family: var(--font-sans);
  font-size: 12px;
  font-weight: 500;
  padding: 7px 14px;
  cursor: pointer;
  margin-bottom: -1px;
  transition: color .12s, border-color .12s;
}
.chart-tab:hover { color: var(--text-primary); }
.chart-tab.active { color: var(--accent-blue); border-bottom-color: var(--accent-blue); }
.chart-wrap { position: relative; height: 280px; }

/* ── FORMULA BOX ─────────────────────────── */
.formula-box {
  background: var(--bg-canvas);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 10px 14px;
  font-family: var(--font-mono);
  font-size: 12px;
  color: var(--orange);
  margin-bottom: 12px;
  position: relative;
  overflow-x: auto;
}
.formula-label {
  position: absolute;
  top: 6px;
  right: 10px;
  font-size: 10px;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: .5px;
}

/* ── SECTION TITLE ───────────────────────── */
.section-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 7px;
}
.section-title::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border);
}

/* ── EMPTY STATE ─────────────────────────── */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-muted);
}
.empty-state svg { opacity: .3; margin-bottom: 12px; }
.empty-state p { font-size: 13px; margin-bottom: 6px; color: var(--text-secondary); }
.empty-state small { font-size: 12px; font-family: var(--font-mono); color: var(--text-muted); }

/* ── SCROLLBAR ──────────────────────────── */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

/* ── ANIMATIONS ──────────────────────────── */
@keyframes fadeSlideIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}
.animate-in { animation: fadeSlideIn .35s ease forwards; }
@keyframes pulse {
  0%,100% { opacity: 1; } 50% { opacity: .5; }
}
.pulsing { animation: pulse 1.2s ease infinite; }

/* ── DATATABLES CUSTOM DARK STYLES ───────────────── */
.dataTables_wrapper {
  color: var(--text-secondary);
  font-family: var(--font-sans);
  font-size: 13px;
  width: 100%;
}
.dataTables_wrapper .row {
  margin-right: 0;
  margin-left: 0;
}
.dataTables_wrapper .col-sm-12 {
  padding-right: 0;
  padding-left: 0;
}
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
  padding: 12px 16px;
  color: var(--text-secondary);
}
.dataTables_wrapper .dataTables_length label,
.dataTables_wrapper .dataTables_filter label {
  margin-bottom: 0;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.dataTables_wrapper .dataTables_length select {
  background: var(--bg-canvas);
  border: 1px solid var(--border);
  color: var(--text-primary);
  padding: 4px 8px;
  border-radius: var(--radius);
  outline: none;
  font-family: var(--font-mono);
}
.dataTables_wrapper .dataTables_length select option {
  background: var(--bg-elevated);
  color: var(--text-primary);
}
.dataTables_wrapper .dataTables_filter input {
  background: var(--bg-canvas);
  border: 1px solid var(--border);
  color: var(--text-primary);
  padding: 6px 12px;
  border-radius: var(--radius);
  outline: none;
  transition: border-color .15s;
}
.dataTables_wrapper .dataTables_filter input:focus {
  border-color: var(--accent-blue);
}
.dataTables_wrapper .dataTables_info {
  padding: 16px;
  color: var(--text-muted);
  font-size: 12px;
}
.dataTables_wrapper .dataTables_paginate {
  padding: 12px 16px;
}
.dataTables_wrapper .pagination {
  margin: 0;
  display: flex;
  gap: 4px;
}
.dataTables_wrapper .page-item .page-link {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  color: var(--text-primary);
  padding: 5px 10px;
  border-radius: var(--radius);
  font-size: 12px;
  text-decoration: none;
  transition: background .12s, border-color .12s;
}
.dataTables_wrapper .page-item:hover .page-link {
  background: var(--border-muted);
  border-color: var(--text-muted);
}
.dataTables_wrapper .page-item.active .page-link {
  background: var(--accent-blue-subtle);
  border-color: var(--accent-blue);
  color: var(--accent-blue);
  font-weight: 600;
}
.dataTables_wrapper .page-item.disabled .page-link {
  background: transparent;
  border-color: var(--border-muted);
  color: var(--text-muted);
  cursor: not-allowed;
}

/* ── RESPONSIVE ─────────────────────────── */
@media (max-width: 900px) {
  .layout { grid-template-columns: 1fr; }
  .sidebar { display: none; }
  .results-grid { grid-template-columns: 1fr; }
  .main { padding: 16px; }
}
</style>
</head>
<body>
