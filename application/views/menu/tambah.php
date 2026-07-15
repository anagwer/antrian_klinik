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
			<div id="content" data-url="<?= base_url('menu') ?>">
				<!-- load Topbar -->
				<?php $this->load->view('partials/topbar.php') ?>

				<div class="container-fluid">
				<div class="clearfix">
					<div class="float-left">
						<h1 class="h3 m-0 text-gray-800"><?= $title ?></h1>
					</div>
					<div class="float-right">
						<a href="<?= base_url('menu') ?>" class="btn btn-secondary btn-sm"><i class="fa fa-reply"></i>&nbsp;&nbsp;Kembali</a>
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="col-md-12">
						<div class="card shadow">
							<div class="card-body">
								<form action="<?= base_url('menu/proses_tambah') ?>" id="form-tambah" method="POST" enctype="multipart/form-data">
									<div class="form-row">
										<div class="form-group col-md-6">
											<label for="nama_menu"><strong>Nama Menu</strong></label>
											<input type="text" name="nama_menu" placeholder="Masukkan Nama Menu" autocomplete="off"  class="form-control" required>
										</div>
										<div class="form-group col-md-6">
											<label for="kategori"><strong>Kategori</strong></label>
											<select name="kategori" class="form-control" required>
												<option value="">Pilih Kategori</option>
												<?php foreach ($all_kategori as $kat) : ?>
													<option value="<?= $kat->nama_kategori ?>"><?= $kat->nama_kategori ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-md-12">
											<label for="deskripsi"><strong>Deskripsi</strong></label>
											<textarea name="deskripsi" class="form-control" placeholder="Masukkan Deskripsi" rows="3" required></textarea>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-md-6">
											<label for="harga"><strong>Harga</strong></label>
											<input type="number" name="harga" placeholder="Masukkan Harga Jual" autocomplete="off"  class="form-control" required>
										</div>
										<div class="form-group col-md-6">
											<label for="is_aktif"><strong>Status</strong></label>
											<select name="is_aktif" class="form-control" required>
												<option value="1">Aktif</option>
												<option value="0">Tidak Aktif</option>
											</select>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-md-12">
											<label for="foto"><strong>Foto Menu</strong></label>
											<input type="file" name="foto" class="form-control">
											<small class="text-muted">Format: JPG, JPEG, PNG, WEBP. Maks 2MB.</small>
										</div>
									</div>
									<hr>
									<div class="form-group">
										<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;Simpan</button>
										<button type="reset" class="btn btn-danger"><i class="fas fa-sync-alt"></i>&nbsp;&nbsp;Reset</button>
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