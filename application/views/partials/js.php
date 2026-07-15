<script src="<?= base_url('sb-admin') ?>/vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url('sb-admin') ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('sb-admin') ?>/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= base_url('sb-admin') ?>/js/sb-admin-2.min.js"></script>
<script>
$(document).ready(function() {
    $('table.data-table, table[id^="dataTable"]').each(function() {
        if (!$.fn.dataTable.isDataTable(this)) {
            $(this).DataTable({
                responsive: true,
                pageLength: 15,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ baris',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ baris',
                    paginate: { previous: 'Sebelumnya', next: 'Selanjutnya' },
                    zeroRecords: 'Tidak ditemukan data yang cocok',
                    infoEmpty: 'Tidak ada data tersedia',
                    infoFiltered: '(difilter dari _MAX_ total baris)'
                }
            });
        }
    });
});
</script>