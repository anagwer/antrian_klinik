<?php $this->load->view('partials/header'); ?>
<?php $this->load->view('partials/sidebar'); ?>

<!-- PAGE HEADER -->
<div class="page-header">
  <div>
    <div class="page-title">⚙️ Setting & User</div>
    <div class="page-subtitle">Konfigurasi parameter peramalan dan manajemen pengguna sistem</div>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;" class="animate-in">

  <!-- CARD 1: PARAMETER FORCASTING -->
  <div>
    <div class="section-title">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/></svg>
      Parameter Peramalan
    </div>
    <div class="table-card" style="padding: 20px;">
      <form method="POST" action="<?= base_url('setting/simpan_parameter') ?>">
        <div class="form-group">
          <label class="form-label" for="ma_period">MA Period Default (bulan)</label>
          <input type="number" class="form-input" id="ma_period" name="ma_period" value="<?= $ma_period ?>" min="2" max="12" required>
          <small style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;">Nilai default n-bulan dalam perhitungan Moving Average.</small>
        </div>
        <div class="form-group">
          <label class="form-label" for="mape_green">MAPE Threshold Hijau (%)</label>
          <input type="number" class="form-input" id="mape_green" name="mape_green" value="<?= $mape_green ?>" min="1" max="100" required>
          <small style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;">Tingkat persentase error maksimal untuk kategori Akurat (Hijau).</small>
        </div>
        <div class="form-group">
          <label class="form-label" for="mape_yellow">MAPE Threshold Kuning (%)</label>
          <input type="number" class="form-input" id="mape_yellow" name="mape_yellow" value="<?= $mape_yellow ?>" min="1" max="100" required>
          <small style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;">Tingkat persentase error maksimal untuk kategori Moderat (Kuning).</small>
        </div>
        <div class="form-group">
          <label class="form-label" for="safety_factor">Safety Stock Factor</label>
          <input type="number" class="form-input" id="safety_factor" name="safety_factor" value="<?= $safety_factor ?>" min="1.0" max="2.0" step="0.05" required>
          <small style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;">Faktor pengali safety stock buffer pengadaan barang.</small>
        </div>
        
        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border)">
          <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
            Simpan Konfigurasi
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- CARD 2: USER MANAGEMENT -->
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
      <div class="section-title" style="margin-bottom: 0; width: auto; flex: none;">
        Manajemen Pengguna
      </div>
      <button class="btn-primary" onclick="openAddUserModal()" style="padding: 4px 10px; font-size: 11px;">
        + Tambah User
      </button>
    </div>
    
    <div class="table-card">
      <div class="table-toolbar">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        registered_users.db
        <span style="color:var(--text-muted)">·</span>
        <span><?= count($all_users) ?> akun terdaftar</span>
      </div>
      <div style="overflow-x: auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 50px; text-align: center;">No</th>
              <th>Username</th>
              <th>Role</th>
              <th>Dibuat Pada</th>
              <th style="width: 130px; text-align: center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_users as $row) : ?>
              <tr>
                <td style="text-align: center; font-family: var(--font-mono); color: var(--text-muted);"><?= $no++ ?></td>
                <td style="font-weight: 600; color: var(--text-primary); font-family: var(--font-mono);"><?= $row->username ?></td>
                <td>
                  <span class="label-badge <?= $row->role === 'admin' ? 'green' : 'yellow' ?>" style="font-size: 10px; font-weight: bold; padding: 2px 6px;">
                    <?= strtoupper($row->role) ?>
                  </span>
                </td>
                <td style="font-size: 12px; color: var(--text-secondary);"><?= date('d M Y, H:i', strtotime($row->created_at)) ?></td>
                <td style="text-align: center;">
                  <div style="display: inline-flex; gap: 6px;">
                    <button class="nav-btn" onclick="openEditUserModal(<?= $row->id_user ?>, '<?= htmlspecialchars($row->username, ENT_QUOTES) ?>', '<?= $row->role ?>')" style="padding: 2px 8px; font-size: 10px;">
                      Edit
                    </button>
                    <a href="<?= base_url('setting/hapus_user/' . $row->id_user) ?>" class="nav-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')" style="padding: 2px 8px; font-size: 10px; color: var(--red); border-color: var(--red)44;">
                      Hapus
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- ADD USER MODAL -->
<div class="modal" id="addUserModal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Tambah User Baru</div>
      <button class="modal-close" onclick="closeModal('addUserModal')">✕</button>
    </div>
    <form method="POST" action="<?= base_url('setting/proses_tambah_user') ?>">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <input type="text" class="form-input" id="username" name="username" placeholder="Maksimal 15 karakter" required maxlength="15" autocomplete="off" style="font-family: var(--font-mono);">
        </div>
        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input type="password" class="form-input" id="password" name="password" placeholder="Masukkan password akun" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="role">Role / Hak Akses</label>
          <select class="form-input" id="role" name="role" required>
            <option value="karyawan" selected>Karyawan (Akses Terbatas)</option>
            <option value="admin">Admin (Akses Penuh)</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="closeModal('addUserModal')">Batal</button>
        <button type="submit" class="btn-primary">Tambah</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT USER MODAL -->
<div class="modal" id="editUserModal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Ubah Data User</div>
      <button class="modal-close" onclick="closeModal('editUserModal')">✕</button>
    </div>
    <form id="editUserForm" method="POST" action="">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="edit_username">Username</label>
          <input type="text" class="form-input" id="edit_username" name="username" required maxlength="15" style="font-family: var(--font-mono);">
        </div>
        <div class="form-group">
          <label class="form-label" for="edit_password">Password Baru (Kosongkan jika tidak diubah)</label>
          <input type="password" class="form-input" id="edit_password" name="password" placeholder="Masukkan password baru">
        </div>
        <div class="form-group">
          <label class="form-label" for="edit_role">Role / Hak Akses</label>
          <select class="form-input" id="edit_role" name="role" required>
            <option value="karyawan">Karyawan (Akses Terbatas)</option>
            <option value="admin">Admin (Akses Penuh)</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="closeModal('editUserModal')">Batal</button>
        <button type="submit" class="btn-primary">Perbarui</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddUserModal() {
  document.getElementById('addUserModal').classList.add('show');
}

function openEditUserModal(id, username, role) {
  document.getElementById('edit_username').value = username;
  document.getElementById('edit_password').value = '';
  document.getElementById('edit_role').value = role;
  document.getElementById('editUserForm').action = '<?= base_url('setting/proses_ubah_user/') ?>' + id;
  document.getElementById('editUserModal').classList.add('show');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('show');
}

// Close modals when clicking outside
window.onclick = function(event) {
  if (event.target.classList.contains('modal')) {
    event.target.classList.remove('show');
  }
}
</script>

<?php $this->load->view('partials/footer'); ?>
