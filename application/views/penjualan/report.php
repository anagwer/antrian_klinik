<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?= $title ?></title>
	<link href="<?= base_url('sb-admin') ?>/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body>
	<div class="row">
		<div class="col text-center">
			<h3 class="h3 text-dark"><?= $title ?></h3>
			<h4 class="h4 text-dark "><strong>TERAS GENZ</strong></h4>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-md-12">
			<table class="table table-bordered">
				<thead>
					<tr>
						<td><strong>No</strong></td>
						<td><strong>No Penjualan</strong></td>
						<td><strong>Atas Nama</strong></td>
						<td><strong>No Meja</strong></td>
						<td><strong>Waktu Penjualan</strong></td>
						<td><strong>Metode</strong></td>
						<td><strong>Total</strong></td>
						<td><strong>Status</strong></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($all_penjualan as $penjualan): ?>
						<tr>
							<td><?= $no++ ?></td>
							<td><?= $penjualan->no_penjualan ?></td>
							<td><?= $penjualan->atas_nama ?></td>
							<td><?= $penjualan->no_meja ?></td>
							<td><?= $penjualan->tgl_penjualan ?> <?= $penjualan->jam_penjualan ?></td>
							<td><?= $penjualan->metode_pembayaran ?></td>
							<td>Rp <?= number_format($penjualan->total, 0, ',', '.') ?></td>
							<td><?= $penjualan->status ?></td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
</body>
</html>
