<tr class="row-keranjang">
	<td class="nama_menu">
		<?= $this->input->post('nama_menu') ?>
		<input type="hidden" name="nama_menu_hidden[]" value="<?= $this->input->post('nama_menu') ?>">
	</td>
	<td class="harga_menu">
		<?= $this->input->post('harga_menu') ?>
		<input type="hidden" name="harga_menu_hidden[]" value="<?= $this->input->post('harga_menu') ?>">
	</td>
	<td class="jumlah">
		<?= $this->input->post('jumlah') ?>
		<input type="hidden" name="jumlah_hidden[]" value="<?= $this->input->post('jumlah') ?>">
	</td>
	<td class="sub_total">
		<?= $this->input->post('sub_total') ?>
		<input type="hidden" name="sub_total_hidden[]" value="<?= $this->input->post('sub_total') ?>">
	</td>
	<td class="aksi">
		<button type="button" class="btn btn-danger btn-sm" id="tombol-hapus" data-nama-menu="<?= $this->input->post('nama_menu') ?>"><i class="fa fa-trash"></i></button>
	</td>
</tr>