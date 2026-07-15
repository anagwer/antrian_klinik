<?php $this->load->view('partials/header'); ?>
<?php $this->load->view('partials/sidebar'); ?>

<!-- PAGE HEADER -->
<div class="page-header">
  <div>
    <div class="page-title">📱 Master Tipe iPhone</div>
    <div class="page-subtitle">Manajemen data tipe smartphone iPhone untuk parameter peramalan</div>
  </div>
  <button class="btn-primary" onclick="openAddModal()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    Tambah Tipe
  </button>
</div>

<!-- DATA TABLE CARD -->
<div class="table-card animate-in">
  <div class="table-toolbar">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
      <line x1="12" y1="18" x2="12.01" y2="18"/>
    </svg>
    master_iphone.csv
    <span style="color:var(--text-muted)">·</span>
    <span><?= count($all_iphone) ?> tipe terdaftar</span>
  </div>
  <div style="overflow-x: auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 60px; text-align: center;">No</th>
          <th>ID iPhone (Kode)</th>
          <th>Nama Tipe iPhone</th>
          <th style="width: 180px; text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($all_iphone)) : ?>
          <?php foreach ($all_iphone as $row) : ?>
            <tr>
              <td style="text-align: center; font-family: var(--font-mono); color: var(--text-muted);"><?= $no++ ?></td>
              <td style="font-family: var(--font-mono); font-weight: 700; color: var(--accent-blue);"><?= $row->id_iphone ?></td>
              <td style="font-weight: 600; color: var(--text-primary);"><?= $row->nama_tipe ?></td>
              <td style="text-align: center;">
                <div style="display: inline-flex; gap: 8px;">
                  <button class="nav-btn" onclick="openEditModal('<?= $row->id_iphone ?>', '<?= htmlspecialchars($row->nama_tipe, ENT_QUOTES) ?>')" style="padding: 4px 10px; font-size: 11px;">
                    Edit
                  </button>
                  <a href="<?= base_url('iphone/hapus/' . $row->id_iphone) ?>" class="nav-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus tipe iPhone ini? Semua data penjualan dan prediksi yang merujuk tipe ini juga akan dihapus.')" style="padding: 4px 10px; font-size: 11px; color: var(--red); border-color: var(--red)44;">
                    Hapus
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 40px 10px;">
              Belum ada data tipe iPhone. Klik tombol "Tambah Tipe" untuk memulai.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Tambah Tipe iPhone</div>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST" action="<?= base_url('iphone/proses_tambah') ?>">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="id_iphone">ID iPhone (Kode Unik)</label>
          <input type="text" class="form-input" id="id_iphone" name="id_iphone" placeholder="Contoh: IP15PM" required maxlength="50" style="text-transform: uppercase;">
          <small style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;">Masukkan kode singkat tanpa spasi.</small>
        </div>
        <div class="form-group">
          <label class="form-label" for="nama_tipe">Nama Tipe iPhone</label>
          <input type="text" class="form-input" id="nama_tipe" name="nama_tipe" placeholder="Contoh: iPhone 15 Pro Max" required maxlength="50">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="closeModal('addModal')">Batal</button>
        <button type="submit" class="btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="editModal">
  <div class="modal-content">
    <div class="modal-header">
      <div class="modal-title">Ubah Tipe iPhone</div>
      <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form id="editForm" method="POST" action="">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">ID iPhone (Kode)</label>
          <input type="text" class="form-input" id="edit_id_iphone" disabled style="opacity: 0.6; cursor: not-allowed; font-family: var(--font-mono);">
        </div>
        <div class="form-group">
          <label class="form-label" for="edit_nama_tipe">Nama Tipe iPhone</label>
          <input type="text" class="form-input" id="edit_nama_tipe" name="nama_tipe" required maxlength="50">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Batal</button>
        <button type="submit" class="btn-primary">Perbarui</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('addModal').classList.add('show');
}

function openEditModal(id, nama) {
  document.getElementById('edit_id_iphone').value = id;
  document.getElementById('edit_nama_tipe').value = nama;
  document.getElementById('editForm').action = '<?= base_url('iphone/proses_ubah/') ?>' + id;
  document.getElementById('editModal').classList.add('show');
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
