<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= $title ?></title>
	<style>
		body {
			font-family: 'Courier New', Courier, monospace;
			margin: 0;
			padding: 20px;
			width: 300px; /* Adjust for thermal printer */
		}
		.text-center { text-align: center; }
		.text-right { text-align: right; }
		.text-left { text-align: left; }
		h2 { margin: 0; font-size: 24px; font-weight: bold; }
		h4 { margin: 5px 0 15px 0; font-size: 14px; font-weight: normal; }
		.divider { border-top: 1px dashed #000; margin: 10px 0; }
		table { width: 100%; border-collapse: collapse; font-size: 12px; }
		td { vertical-align: top; }
		.details-table td { padding: 2px 0; }
		.items-table th, .items-table td { padding: 4px 0; }
		.items-table th { border-bottom: 1px dashed #000; text-align: left; }
		.fw-bold { font-weight: bold; }
		.footer { margin-top: 20px; font-size: 12px; }
	</style>
</head>
<body onload="window.print()">
	<div class="text-center">
		<h2>TERAS GENZ</h2>
		<h4>Nota Pembayaran</h4>
	</div>

	<div class="divider"></div>

	<table class="details-table">
		<tr>
			<td width="35%">No. Trx</td>
			<td width="5%">:</td>
			<td><?= $penjualan->no_penjualan ?></td>
		</tr>
		<tr>
			<td>Waktu</td>
			<td>:</td>
			<td><?= $penjualan->tgl_penjualan ?> <?= $penjualan->jam_penjualan ?></td>
		</tr>
		<tr>
			<td>Kasir</td>
			<td>:</td>
			<td><?= $penjualan->nama_kasir ?></td>
		</tr>
		<tr>
			<td>Pelanggan</td>
			<td>:</td>
			<td><?= $penjualan->atas_nama ?></td>
		</tr>
		<tr>
			<td>No Meja</td>
			<td>:</td>
			<td><?= $penjualan->no_meja ?></td>
		</tr>
		<tr>
			<td>Metode</td>
			<td>:</td>
			<td><?= $penjualan->metode_pembayaran ?></td>
		</tr>
	</table>

	<div class="divider"></div>

	<table class="items-table">
		<thead>
			<tr>
				<th>Menu</th>
				<th class="text-center">Qty</th>
				<th class="text-right">Subtotal</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($all_detail_penjualan as $item): ?>
				<tr>
					<td><?= $item->nama_menu ?><br><small>Rp <?= number_format($item->harga_menu, 0, ',', '.') ?></small></td>
					<td class="text-center"><?= $item->jumlah_menu ?></td>
					<td class="text-right">Rp <?= number_format($item->sub_total, 0, ',', '.') ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="divider"></div>

	<table class="details-table">
		<tr>
			<td class="fw-bold">TOTAL BAYAR</td>
			<td class="text-right fw-bold">Rp <?= number_format($penjualan->total, 0, ',', '.') ?></td>
		</tr>
		<tr>
			<td>STATUS</td>
			<td class="text-right"><?= $penjualan->status ?></td>
		</tr>
	</table>

	<div class="divider"></div>

	<div class="text-center footer">
		<p>Terima Kasih Atas Kunjungan Anda<br>Silakan Datang Kembali</p>
	</div>
</body>
</html>
