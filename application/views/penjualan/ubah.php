<!DOCTYPE html>
<html lang="en">
<head>
	<?php $this->load->view('partials/head.php') ?>
</head>

<body id="page-top">
	<div id="wrapper">
		<!-- load sidebar -->
		<?php $this->load->view('partials/sidebar.php') ?>

		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content" data-url="<?= base_url('penjualan') ?>">
				<!-- load Topbar -->
				<?php $this->load->view('partials/topbar.php') ?>

				<div class="container-fluid">
				<div class="clearfix">
					<div class="float-left">
						<h1 class="h3 m-0 text-gray-800"><?= $title ?></h1>
					</div>
					<div class="float-right">
						<a href="<?= base_url('penjualan') ?>" class="btn btn-secondary btn-sm"><i class="fa fa-reply"></i>&nbsp;&nbsp;Kembali</a>
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="col-md-6">
						<div class="card shadow">
							<div class="card-header"><strong>Update Status Penjualan</strong></div>
							<div class="card-body">
								<form action="<?= base_url('penjualan/proses_ubah/' . $penjualan->no_penjualan) ?>" id="form-tambah" method="POST">
									<div class="form-group">
										<label>No. Penjualan</label>
										<input type="text" value="<?= $penjualan->no_penjualan ?>" readonly class="form-control">
									</div>
									<div class="form-group">
										<label>Atas Nama <span class="text-danger">*</span></label>
										<input type="text" name="atas_nama" class="form-control" value="<?= $penjualan->atas_nama ?>" required>
									</div>
									<div class="form-group">
										<label>No Meja <span class="text-danger">*</span></label>
										<input type="text" name="no_meja" class="form-control" value="<?= $penjualan->no_meja ?>" required>
									</div>
									<div class="form-group">
										<label>Metode Pembayaran <span class="text-danger">*</span></label>
										<select name="metode_pembayaran" class="form-control" required>
											<option value="Cash" <?= $penjualan->metode_pembayaran == 'Cash' ? 'selected' : '' ?>>Cash</option>
											<option value="QRIS" <?= $penjualan->metode_pembayaran == 'QRIS' ? 'selected' : '' ?>>QRIS</option>
										</select>
									</div>
									<div class="form-group">
										<label>Status <span class="text-danger">*</span></label>
										<select name="status" class="form-control" required>
											<option value="Belum Bayar" <?= $penjualan->status == 'Belum Bayar' ? 'selected' : '' ?>>Belum Bayar</option>
											<option value="Sudah Dibayar" <?= $penjualan->status == 'Sudah Dibayar' ? 'selected' : '' ?>>Sudah Dibayar</option>
											<option value="Dikonfirmasi" <?= $penjualan->status == 'Dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
											<option value="Selesai" <?= $penjualan->status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
										</select>
									</div>
									<hr>
									<div class="form-group">
										<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;Simpan Perubahan</button>
									</div>
								</form>
							</div>				
						</div>
					</div>
				</div>
				</div>
			</div>
			<!-- load footer -->
			<?php $this->load->view('partials/footer.php') ?>
		</div>
	</div>
	<?php $this->load->view('partials/js.php') ?>
</body>
</html>
