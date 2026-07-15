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
						<?php if ($this->session->login['role'] == 'admin'): ?>
							<a href="<?= base_url('menu/tambah') ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;&nbsp;Tambah</a>
						<?php endif ?>
					</div>
				</div>
				<hr>
				<?php if ($this->session->flashdata('success')) : ?>
					<div class="alert alert-success alert-dismissible fade show" role="alert">
						<?= $this->session->flashdata('success') ?>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				<?php elseif($this->session->flashdata('error')) : ?>
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<?= $this->session->flashdata('error') ?>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				<?php endif ?>

				<!-- Category Filter -->
				<div class="row mb-3">
					<div class="col-md-4">
						<select id="kategoriFilter" class="form-control">
							<option value="">-- Semua Kategori --</option>
							<?php foreach($all_kategori as $kat): ?>
								<option value="<?= strtolower($kat->nama_kategori) ?>"><?= $kat->nama_kategori ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="card shadow">
					<div class="card-header"><strong>Daftar Menu</strong></div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-bordered" id="dataTableMenu" width="100%" cellspacing="0">
								<thead>
									<tr>
										<td>No</td>
										<td>Foto</td>
										<td>Nama Menu</td>
										<td>Deskripsi</td>
										<td>Kategori</td>
										<td>Harga</td>
										<td>Status</td>
										<?php if ($this->session->login['role'] == 'admin'): ?>
											<td>Aksi</td>
										<?php endif ?>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($all_menu as $menu): ?>
										<tr data-kategori="<?= strtolower($menu->kategori) ?>">
											<td><?= $no++ ?></td>
											<td>
												<?php if(!empty($menu->foto) && file_exists('./files/menu/' . $menu->foto)): ?>
													<img src="<?= base_url('files/menu/' . $menu->foto) ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
												<?php else: ?>
													<span class="text-muted small">Tidak ada foto</span>
												<?php endif; ?>
											</td>
											<td><?= $menu->nama_menu ?></td>
											<td><?= $menu->deskripsi ?></td>
											<td>
												<?php
													$kat_color = 'secondary';
													$kat = strtolower($menu->kategori);
													if($kat == 'makanan') $kat_color = 'success';
													elseif($kat == 'minuman') $kat_color = 'info';
													elseif($kat == 'promo') $kat_color = 'danger';
													elseif($kat == 'paket') $kat_color = 'primary';
													elseif($kat == 'snack') $kat_color = 'warning';
												?>
												<span class="badge badge-<?= $kat_color ?> px-2 py-1"><?= ucfirst($menu->kategori) ?></span>
											</td>
											<td>Rp <?= number_format($menu->harga, 0, ',', '.') ?></td>
											<td>
												<?php if($menu->is_aktif == 1): ?>
													<span class="badge badge-success">Aktif</span>
												<?php else: ?>
													<span class="badge badge-secondary">Tidak Aktif</span>
												<?php endif; ?>
											</td>
											<?php if ($this->session->login['role'] == 'admin'): ?>
												<td>
													<a href="<?= base_url('menu/ubah/' . $menu->id) ?>" class="btn btn-success btn-sm"><i class="fa fa-pen"></i></a>
													<a onclick="return confirm('apakah anda yakin?')" href="<?= base_url('menu/hapus/' . $menu->id) ?>" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
												</td>
											<?php endif ?>
										</tr>
									<?php endforeach ?>
								</tbody>
							</table>
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
	<script src="<?= base_url('sb-admin/js/demo/datatables-demo.js') ?>"></script>
	<script src="<?= base_url('sb-admin') ?>/vendor/datatables/jquery.dataTables.min.js"></script>
	<script src="<?= base_url('sb-admin') ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
	<script>
		$(document).ready(function(){
			var table = $('#dataTableMenu').DataTable();
			
			$('#kategoriFilter').on('change', function(){
				var filterValue = $(this).val();
				if(filterValue) {
					table.column(4).search('^' + filterValue + '$', true, false).draw();
				} else {
					table.column(4).search('').draw();
				}
			});
		});
	</script>
</body>
</html>