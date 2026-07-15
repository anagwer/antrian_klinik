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
					<div class="col">
						<div class="card shadow">
							<div class="card-body">
								<form action="<?= base_url('penjualan/proses_tambah') ?>" id="form-tambah" method="POST">
									<h5>Data Kasir</h5>
									<hr>
									<div class="form-row">
										<div class="form-group col-3">
											<label>No. Penjualan</label>
											<input type="text" name="no_penjualan" value="PJ<?= time() ?>" readonly class="form-control">
										</div>
										<div class="form-group col-3">
											<label>Tanggal Penjualan</label>
											<input type="text" name="tgl_penjualan" value="<?= date('d/m/Y') ?>" readonly class="form-control">
										</div>
										<div class="form-group col-3">
											<label>Jam</label>
											<input type="text" name="jam_penjualan" value="<?= date('H:i:s') ?>" readonly class="form-control">
										</div>
										<div class="form-group col-3">
											<label>Nama Kasir</label>
											<input type="text" name="nama_kasir" value="<?= $this->session->login['nama'] ?>" readonly class="form-control">
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-3">
											<label>Atas Nama <span class="text-danger">*</span></label>
											<input type="text" name="atas_nama" class="form-control" required placeholder="Nama Pelanggan">
										</div>
										<div class="form-group col-3">
											<label>No Meja <span class="text-danger">*</span></label>
											<input type="text" name="no_meja" class="form-control" required placeholder="Nomor Meja">
										</div>
										<div class="form-group col-3">
											<label>Metode Pembayaran <span class="text-danger">*</span></label>
											<select name="metode_pembayaran" class="form-control" required>
												<option value="Cash">Cash</option>
												<option value="QRIS">QRIS</option>
											</select>
										</div>
										<div class="form-group col-3">
											<label>Status <span class="text-danger">*</span></label>
											<select name="status" class="form-control" required>
												<option value="Belum Bayar">Belum Bayar</option>
												<option value="Sudah Dibayar">Sudah Dibayar</option>
												<option value="Dikonfirmasi">Dikonfirmasi</option>
												<option value="Selesai">Selesai</option>
											</select>
										</div>
									</div>
									<h5>Data Menu</h5>
									<hr>
									<div class="form-row">
										<div class="form-group col-4">
											<label for="nama_menu">Nama Menu</label>
											<select name="nama_menu" id="nama_menu" class="form-control">
												<option value="">Pilih Menu</option>
												<?php foreach ($all_menu as $menu): ?>
													<option value="<?= $menu->nama_menu ?>"><?= $menu->nama_menu ?></option>
												<?php endforeach ?>
											</select>
										</div>
										<div class="form-group col-3">
											<label>Harga Menu</label>
											<input type="text" name="harga_menu" value="" readonly class="form-control">
										</div>
										<div class="form-group col-2">
											<label>Jumlah</label>
											<input type="number" name="jumlah" value="" class="form-control" readonly min='1'>
										</div>
										<div class="form-group col-2">
											<label>Sub Total</label>
											<input type="number" name="sub_total" value="" class="form-control" readonly>
										</div>
										<div class="form-group col-1">
											<label for="">&nbsp;</label>
											<button disabled type="button" class="btn btn-primary btn-block" id="tambah"><i class="fa fa-plus"></i></button>
										</div>
									</div>
									<div class="keranjang">
										<h5>Detail Pembelian</h5>
										<hr>
										<table class="table table-bordered" id="keranjang">
											<thead>
												<tr>
													<td width="35%">Nama Menu</td>
													<td width="15%">Harga</td>
													<td width="15%">Jumlah</td>
													<td width="20%">Sub Total</td>
													<td width="15%">Aksi</td>
												</tr>
											</thead>
											<tbody>
												
											</tbody>
											<tfoot>
												<tr>
													<td colspan="4" align="right"><strong>Total : </strong></td>
													<td id="total"></td>
													
													<td>
														<input type="hidden" name="total_hidden" value="">
														<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;Simpan</button>
													</td>
												</tr>
											</tfoot>
										</table>
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
	<script>
		$(document).ready(function(){
			$('tfoot').hide()

			$(document).keypress(function(event){
		    	if (event.which == '13') {
		      		event.preventDefault();
			   	}
			})

			$('#nama_menu').on('change', function(){

				if($(this).val() == '') reset()
				else {
					const url_get_all_menu = $('#content').data('url') + '/get_all_menu'
					$.ajax({
						url: url_get_all_menu,
						type: 'POST',
						dataType: 'json',
						data: {nama_menu: $(this).val()},
						success: function(data){
							$('input[name="harga_menu"]').val(data.harga)
							$('input[name="jumlah"]').val(1)
							$('input[name="jumlah"]').prop('readonly', false)
							$('button#tambah').prop('disabled', false)

							$('input[name="sub_total"]').val($('input[name="jumlah"]').val() * $('input[name="harga_menu"]').val())
							
							$('input[name="jumlah"]').on('keydown keyup change blur', function(){
								$('input[name="sub_total"]').val($('input[name="jumlah"]').val() * $('input[name="harga_menu"]').val())
							})
						}
					})
				}
			})

			$(document).on('click', '#tambah', function(e){
				const url_keranjang_menu = $('#content').data('url') + '/keranjang_menu'
				const data_keranjang = {
					nama_menu: $('select[name="nama_menu"]').val(),
					harga_menu: $('input[name="harga_menu"]').val(),
					jumlah: $('input[name="jumlah"]').val(),
					sub_total: $('input[name="sub_total"]').val(),
				}

				$.ajax({
					url: url_keranjang_menu,
					type: 'POST',
					data: data_keranjang,
					success: function(data){
						if($('select[name="nama_menu"]').val() == data_keranjang.nama_menu) $('option[value="' + data_keranjang.nama_menu + '"]').hide()
						reset()

						$('table#keranjang tbody').append(data)
						$('tfoot').show()

						$('#total').html('<strong>' + hitung_total() + '</strong>')
						$('input[name="total_hidden"]').val(hitung_total())
					}
				})

			})


			$(document).on('click', '#tombol-hapus', function(){
				$(this).closest('.row-keranjang').remove()

				$('option[value="' + $(this).data('nama-menu') + '"]').show()

				if($('tbody').children().length == 0) $('tfoot').hide()
			})

			$('button[type="submit"]').on('click', function(){
				$('select[name="nama_menu"]').prop('disabled', true)
				$('input[name="harga_menu"]').prop('disabled', true)
				$('input[name="jumlah"]').prop('disabled', true)
				$('input[name="sub_total"]').prop('disabled', true)
			})

			function hitung_total(){
				let total = 0;
				$('.sub_total').each(function(){
					total += parseInt($(this).text())
				})

				return total;
			}

			function reset(){
				$('#nama_menu').val('')
				$('input[name="harga_menu"]').val('')
				$('input[name="jumlah"]').val('')
				$('input[name="sub_total"]').val('')
				$('input[name="jumlah"]').prop('readonly', true)
				$('button#tambah').prop('disabled', true)
			}
		})
	</script>
</body>
</html>