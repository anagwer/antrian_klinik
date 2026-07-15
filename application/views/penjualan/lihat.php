<?php $this->load->view('partials/header'); ?>
<?php $this->load->view('partials/sidebar'); ?>

<!-- PAGE HEADER -->
<div class="page-header">
  <div>
    <div class="page-title">📊 Data Penjualan</div>
    <div class="page-subtitle">Pencatatan data historis penjualan smartphone iPhone</div>
  </div>
  <button class="btn-primary" onclick="openAddModal()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    Tambah Transaksi
  </button>
</div>

<!-- DATA TABLE CARD -->
<div class="table-card animate-in">
  <div class="table-toolbar">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
      <line x1="3" y1="9" x2="21" y2="9"/>
      <line x1="9" y1="21" x2="9" y2="9"/>
    </svg>
    data_penjualan.csv
    <span style="color:var(--text-muted)">·</span>
    <span><?= count($all_penjualan) ?> transaksi tercatat</span>
  </div>
  <div style="overflow-x: auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 60px; text-align: center;">No</th>
          <th>Kode Penjualan</th>
          <th>Model iPhone</th>
          <th>Tanggal Transaksi</th>
          <th style="text-align: right;">Jumlah Terjual (unit)</th>
          <th>Input Oleh</th>
          <th style="width: 180px; text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($all_penjualan)) : ?>
          <?php foreach ($all_penjualan as $row) : ?>
            <tr>
              <td style="text-align: center; font-family: var(--font-mono); color: var(--text-muted);"><?= $no++ ?></td>
              <td style="font-family: var(--font-mono); font-weight: 700; color: var(--accent-blue);"><?= $row->id_penjualan ?></td>
              <td style="font-weight: 600; color: var(--text-primary);"><?= $row->nama_tipe ?></td>
              <td style="font-family: var(--font-mono);"><?= date('d F Y', strtotime($row->tanggal_transaksi)) ?></td>
              <td style="text-align: right; font-family: var(--font-mono); font-weight: bold; color: var(--green);"><?= $row->jumlah_terjual ?> unit</td>
              <td>
                <span class="label-badge blue" style="font-size: 10px; font-weight: bold; padding: 2px 6px; font-family: var(--font-mono);">
                  <?= $row->username ? htmlspecialchars($row->username) : '—' ?>
                </span>
              </td>
              <td style="text-align: center;">
                <div style="display: inline-flex; gap: 8px;">
                  <button class="nav-btn" onclick="openEditModal('<?= $row->id_penjualan ?>', '<?= $row->id_iphone ?>', '<?= $row->tanggal_transaksi ?>', <?= $row->jumlah_terjual ?>)" style="padding: 4px 10px; font-size: 11px;">
                    Edit
                  </button>
                  <a href="<?= base_url('penjualan/hapus/' . $row->id_penjualan) ?>" class="nav-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus data transaksi penjualan ini?')" style="padding: 4px 10px; font-size: 11px; color: var(--red); border-color: var(--red)44;">
                    Hapus
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px 10px;">
              Belum ada data penjualan historis. Klik tombol "Tambah Transaksi" untuk memulai.
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
      <div class="modal-title">Tambah Transaksi Penjualan</div>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST" action="<?= base_url('penjualan/proses_tambah') ?>">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="id_penjualan">Kode Penjualan (Opsional)</label>
          <input type="text" class="form-input" id="id_penjualan" name="id_penjualan" value="<?= $next_id ?>" placeholder="Kode otomatis jika kosong" style="font-family: var(--font-mono); text-transform: uppercase;">
        </div>
        <div class="form-group">
          <label class="form-label" for="id_iphone">Model iPhone</label>
          <select class="form-input" id="id_iphone" name="id_iphone" required>
            <option value="" disabled selected>Pilih tipe iPhone</option>
            <?php foreach ($all_iphone as $ip) : ?>
              <option value="<?= $ip->id_iphone ?>"><?= $ip->nama_tipe ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="tanggal_transaksi">Tanggal Transaksi</label>
          <input type="date" class="form-input" id="tanggal_transaksi" name="tanggal_transaksi" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="jumlah_terjual">Jumlah Terjual (unit)</label>
          <input type="number" class="form-input" id="jumlah_terjual" name="jumlah_terjual" placeholder="Contoh: 15" required min="1">
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
      <div class="modal-title">Ubah Transaksi Penjualan</div>
      <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form id="editForm" method="POST" action="">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Kode Penjualan</label>
          <input type="text" class="form-input" id="edit_id_penjualan" disabled style="opacity: 0.6; cursor: not-allowed; font-family: var(--font-mono);">
        </div>
        <div class="form-group">
          <label class="form-label" for="edit_id_iphone">Model iPhone</label>
          <select class="form-input" id="edit_id_iphone" name="id_iphone" required>
            <?php foreach ($all_iphone as $ip) : ?>
              <option value="<?= $ip->id_iphone ?>"><?= $ip->nama_tipe ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="edit_tanggal_transaksi">Tanggal Transaksi</label>
          <input type="date" class="form-input" id="edit_tanggal_transaksi" name="tanggal_transaksi" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="edit_jumlah_terjual">Jumlah Terjual (unit)</label>
          <input type="number" class="form-input" id="edit_jumlah_terjual" name="jumlah_terjual" required min="1">
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

function openEditModal(id, id_iphone, tanggal, jumlah) {
  document.getElementById('edit_id_penjualan').value = id;
  document.getElementById('edit_id_iphone').value = id_iphone;
  document.getElementById('edit_tanggal_transaksi').value = tanggal;
  document.getElementById('edit_jumlah_terjual').value = jumlah;
  document.getElementById('editForm').action = '<?= base_url('penjualan/proses_ubah/') ?>' + id;
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