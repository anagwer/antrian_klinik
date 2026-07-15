<!-- ── TOP NAV ── -->
<nav class="topnav">
  <a href="<?= base_url('beranda') ?>" class="nav-logo">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
      <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
      <line x1="12" y1="18" x2="12.01" y2="18"/>
    </svg>
    Lestari iPhone Prediksi
  </a>
  <div class="nav-breadcrumb">
    <span>/</span>
    <span><?= isset($title) ? strtolower(str_replace(' ', '-', $title)) : 'dashboard' ?></span>
  </div>
  <div class="nav-right">
    <span style="font-size: 12px; color: var(--text-secondary); margin-right: 10px; font-family: var(--font-mono);">
      👤 <?= $this->session->login['username'] ?>
    </span>
    <a href="<?= base_url('logout') ?>" class="nav-btn" style="color: var(--red); border-color: var(--red)44;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
      Keluar
    </a>
  </div>
</nav>

<div class="layout">

<!-- ── SIDEBAR ── -->
<aside class="sidebar">
  <div class="sidebar-section">
    <div class="sidebar-heading">Menu Utama</div>
    
    <a href="<?= base_url('beranda') ?>" class="sidebar-item <?= $aktif === 'beranda' ? 'active' : '' ?>">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
      Beranda
    </a>

    <a href="<?= base_url('penjualan') ?>" class="sidebar-item <?= $aktif === 'penjualan' ? 'active' : '' ?>">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
        <line x1="3" y1="9" x2="21" y2="9"/>
        <line x1="9" y1="21" x2="9" y2="9"/>
      </svg>
      Data Penjualan
    </a>

    <a href="<?= base_url('iphone') ?>" class="sidebar-item <?= $aktif === 'iphone' ? 'active' : '' ?>">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
        <line x1="12" y1="18" x2="12.01" y2="18"/>
      </svg>
      Master Tipe iPhone
    </a>

    <?php if ($this->session->login['role'] === 'admin') : ?>
    <a href="<?= base_url('prediksi') ?>" class="sidebar-item <?= $aktif === 'prediksi' ? 'active' : '' ?>">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
      </svg>
      Prediksi Penjualan
    </a>

    <a href="<?= base_url('setting') ?>" class="sidebar-item <?= $aktif === 'setting' ? 'active' : '' ?>">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="3"/>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
      </svg>
      Setting & User
    </a>
    <?php endif; ?>
  </div>
  
  <hr class="sidebar-divider">

  <div class="sidebar-section">
    <div class="sidebar-heading">Informasi Akun</div>
    <div style="padding: 4px 16px; font-size: 12px; color: var(--text-muted); font-family: var(--font-mono)">
      Username: <?= $this->session->login['username'] ?>
    </div>
    <div style="padding: 4px 16px; font-size: 11px; color: var(--text-muted); font-family: var(--font-mono)">
      Masuk: <?= $this->session->login['jam_masuk'] ?>
    </div>
  </div>
</aside>

<!-- ── MAIN ── -->
<main class="main">

  <!-- FLASH DATA ALERTS -->
  <?php if ($this->session->flashdata('success')) : ?>
    <div class="alert alert-success animate-in">
      <span><?= $this->session->flashdata('success') ?></span>
      <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;font-size:16px;font-weight:bold;">✕</button>
    </div>
  <?php elseif ($this->session->flashdata('error')) : ?>
    <div class="alert alert-danger animate-in">
      <span><?= $this->session->flashdata('error') ?></span>
      <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;font-size:16px;font-weight:bold;">✕</button>
    </div>
  <?php endif ?>