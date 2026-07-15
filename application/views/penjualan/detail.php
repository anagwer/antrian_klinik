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
						<a href="<?= base_url('penjualan/export_detail/' . $penjualan->no_penjualan) ?>" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf"></i>&nbsp;&nbsp;Export</a>
						<a href="<?= base_url('penjualan') ?>" class="btn btn-secondary btn-sm"><i class="fa fa-reply"></i>&nbsp;&nbsp;Kembali</a>
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
				<div class="card shadow">
					<div class="card-header"><strong>Detail Penjualan - <?= $penjualan->no_penjualan ?></strong></div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">
								<table class="table table-borderless">
									<tr>
										<td><strong>No Penjualan</strong></td>
										<td>:</td>
										<td><?= $penjualan->no_penjualan ?></td>
									</tr>
									<tr>
										<td><strong>Nama Kasir</strong></td>
										<td>:</td>
										<td><?= $penjualan->nama_kasir ?></td>
									</tr>
									<tr>
										<td><strong>Waktu Penjualan</strong></td>
										<td>:</td>
										<td><?= $penjualan->tgl_penjualan ?> - <?= $penjualan->jam_penjualan ?></td>
									</tr>
								</table>
							</div>
							<div class="col-md-6">
								<table class="table table-borderless">
									<tr>
										<td><strong>Atas Nama</strong></td>
										<td>:</td>
										<td><?= $penjualan->atas_nama ?></td>
									</tr>
									<tr>
										<td><strong>No Meja</strong></td>
										<td>:</td>
										<td><?= $penjualan->no_meja ?></td>
									</tr>
									<tr>
										<td><strong>Metode Pembayaran</strong></td>
										<td>:</td>
										<td><?= $penjualan->metode_pembayaran ?></td>
									</tr>
									<tr>
										<td><strong>Status</strong></td>
										<td>:</td>
										<td>
											<?php
												$status_color = 'secondary';
												if($penjualan->status == 'Belum Bayar') $status_color = 'danger';
												else if($penjualan->status == 'Sudah Dibayar') $status_color = 'warning';
												else if($penjualan->status == 'Dikonfirmasi') $status_color = 'info';
												else if($penjualan->status == 'Selesai') $status_color = 'success';
											?>
											<span class="badge badge-<?= $status_color ?>"><?= $penjualan->status ?></span>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="col-md-12">
								<table class="table table-bordered">
									<thead>
										<tr>
											<td><strong>No</strong></td>
											<td><strong>Nama Menu</strong></td>
											<td><strong>Harga Menu</strong></td>
											<td><strong>Jumlah</strong></td>
											<td><strong>Sub Total</strong></td>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($all_detail_penjualan as $detail_penjualan): ?>
											<tr>
												<td><?= $no++ ?></td>
												<td><?= $detail_penjualan->nama_menu ?></td>
												<td>Rp <?= number_format($detail_penjualan->harga_menu, 0, ',', '.') ?></td>
												<td><?= $detail_penjualan->jumlah_menu ?></td>
												<td>Rp <?= number_format($detail_penjualan->sub_total, 0, ',', '.') ?></td>
											</tr>
										<?php endforeach ?>
									</tbody>
									<tfoot>
										<tr>
											<td colspan="4" align="right"><strong>Total : </strong></td>
											<td>Rp <?= number_format($penjualan->total, 0, ',', '.') ?></td>
										</tr>
									</tfoot>
								</table>
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
	<script src="<?= base_url('sb-admin/js/demo/datatables-demo.js') ?>"></script>
	<script src="<?= base_url('sb-admin') ?>/vendor/datatables/jquery.dataTables.min.js"></script>
	<script src="<?= base_url('sb-admin') ?>/vendor/datatables/dataTables.bootstrap4.min.js"></script>
</body>
</html>