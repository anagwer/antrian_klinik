/* eslint-disable @typescript-eslint/no-explicit-any */
import { useState, useEffect } from 'react';
import {
  Box,
  Button,
  Card,
  CardContent,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  FormControl,
  Grid,
  IconButton,
  InputLabel,
  MenuItem,
  Paper,
  Select,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TextField,
  Typography,
  Chip,
  Alert,
  TableSortLabel,
  TablePagination,
} from '@mui/material';
import { Icon } from '@iconify/react';

const API_BASE = window.location.origin + '/project/antrian/index.php/api';

export default function Layanan() {
  const [layananList, setLayananList] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [successMsg, setSuccessMsg] = useState('');

  // DataTables states
  const [searchTerm, setSearchTerm] = useState('');
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [orderBy, setOrderBy] = useState('id_poli');
  const [order, setOrder] = useState<'asc' | 'desc'>('asc');

  // Search filter
  const filteredList = layananList.filter((item) => {
    const term = searchTerm.toLowerCase();
    return (
      item.id_poli.toLowerCase().includes(term) ||
      item.nama_poli.toLowerCase().includes(term) ||
      item.kode_antrian.toLowerCase().includes(term) ||
      (item.deskripsi && item.deskripsi.toLowerCase().includes(term))
    );
  });

  // Sorting
  const sortedList = [...filteredList].sort((a, b) => {
    const aVal = a[orderBy];
    const bVal = b[orderBy];
    if (aVal === null || aVal === undefined) return 1;
    if (bVal === null || bVal === undefined) return -1;
    
    if (typeof aVal === 'string') {
      return order === 'asc' 
        ? aVal.localeCompare(bVal) 
        : bVal.localeCompare(aVal);
    } else {
      return order === 'asc' 
        ? (aVal > bVal ? 1 : -1) 
        : (aVal < bVal ? 1 : -1);
    }
  });

  // Paginated List
  const paginatedList = sortedList.slice(page * rowsPerPage, (page + 1) * rowsPerPage);

  const handleRequestSort = (property: string) => {
    const isAsc = orderBy === property && order === 'asc';
    setOrder(isAsc ? 'desc' : 'asc');
    setOrderBy(property);
  };

  // Dialog states
  const [openDialog, setOpenDialog] = useState(false);
  const [isEdit, setIsEdit] = useState(false);
  const [openDeleteDialog, setOpenDeleteDialog] = useState(false);
  const [selectedLayanan, setSelectedLayanan] = useState<any>(null);

  // Form states
  const [idLayanan, setIdLayanan] = useState('');
  const [namaLayanan, setNamaLayanan] = useState('');
  const [kodeAntrian, setKodeAntrian] = useState('');
  const [deskripsi, setDeskripsi] = useState('');
  const [status, setStatus] = useState('aktif');

  const fetchLayanan = () => {
    setLoading(true);
    fetch(`${API_BASE}/poliklinik?all=true`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setLayananList(data.data);
        } else {
          setErrorMsg(data.message || 'Gagal mengambil data layanan.');
        }
      })
      .catch((err) => {
        console.error(err);
        setErrorMsg('Gagal terhubung ke API backend.');
      })
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetchLayanan();
  }, []);

  const handleOpenAdd = () => {
    setIsEdit(false);
    setIdLayanan('');
    setNamaLayanan('');
    setKodeAntrian('');
    setDeskripsi('');
    setStatus('aktif');
    setErrorMsg('');
    setSuccessMsg('');
    setOpenDialog(true);
  };

  const handleOpenEdit = (item: any) => {
    setIsEdit(true);
    setIdLayanan(item.id_poli);
    setNamaLayanan(item.nama_poli);
    setKodeAntrian(item.kode_antrian);
    setDeskripsi(item.deskripsi || '');
    setStatus(item.status);
    setErrorMsg('');
    setSuccessMsg('');
    setOpenDialog(true);
  };

  const handleOpenDelete = (item: any) => {
    setSelectedLayanan(item);
    setOpenDeleteDialog(true);
  };

  const handleSave = () => {
    if (!idLayanan || !namaLayanan || !kodeAntrian) {
      setErrorMsg('Mohon isi semua field wajib.');
      return;
    }

    const payload = {
      id_poli: idLayanan,
      nama_poli: namaLayanan,
      kode_antrian: kodeAntrian,
      deskripsi: deskripsi,
      status: status,
    };

    const endpoint = isEdit ? 'poliklinik_update' : 'poliklinik_create';

    fetch(`${API_BASE}/${endpoint}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setSuccessMsg(isEdit ? 'Layanan berhasil diperbarui.' : 'Layanan berhasil ditambahkan.');
          setOpenDialog(false);
          fetchLayanan();
        } else {
          setErrorMsg(data.message || 'Gagal menyimpan layanan.');
        }
      })
      .catch((err) => {
        console.error(err);
        setErrorMsg('Gagal terhubung ke server.');
      });
  };

  const handleDelete = () => {
    if (!selectedLayanan) return;

    fetch(`${API_BASE}/poliklinik_delete?id_poli=${selectedLayanan.id_poli}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setSuccessMsg('Layanan berhasil dihapus.');
          setOpenDeleteDialog(false);
          fetchLayanan();
        } else {
          setErrorMsg(data.message || 'Gagal menghapus layanan.');
        }
      })
      .catch((err) => {
        console.error(err);
        setErrorMsg('Gagal terhubung ke server.');
      });
  };

  return (
    <Box p={3}>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
        <Box>
          <Typography variant="h4" fontWeight="bold" color="primary.main">
            Manajemen Layanan Klinik
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Kelola data jenis pelayanan, prefix nomor antrean, dan deskripsi layanan.
          </Typography>
        </Box>
        <Button
          variant="contained"
          color="primary"
          startIcon={<Icon icon="ic:round-add" />}
          onClick={handleOpenAdd}
          sx={{ borderRadius: '8px', textTransform: 'none', px: 3 }}
        >
          Tambah Layanan
        </Button>
      </Box>

      {successMsg && (
        <Alert severity="success" sx={{ mb: 3 }} onClose={() => setSuccessMsg('')}>
          {successMsg}
        </Alert>
      )}

      {errorMsg && (
        <Alert severity="error" sx={{ mb: 3 }} onClose={() => setErrorMsg('')}>
          {errorMsg}
        </Alert>
      )}

      <Card sx={{ borderRadius: '16px', boxShadow: '0 4px 20px rgba(0,0,0,0.05)' }}>
        <CardContent sx={{ p: 0 }}>
          <Box p={2.5} display="flex" justifyContent="space-between" alignItems="center">
            <TextField
              size="small"
              label="Cari Layanan (ID, Nama, Kode, Deskripsi)"
              variant="outlined"
              value={searchTerm}
              onChange={(e) => {
                setSearchTerm(e.target.value);
                setPage(0);
              }}
              InputProps={{
                startAdornment: <Icon icon="ic:round-search" style={{ marginRight: '8px', color: 'gray' }} />,
              }}
              sx={{ width: 320 }}
            />
          </Box>
          <TableContainer component={Paper} sx={{ boxShadow: 'none', borderRadius: '0' }}>
            <Table>
              <TableHead sx={{ bgcolor: 'grey.50' }}>
                <TableRow>
                  <TableCell style={{ fontWeight: 'bold' }}>
                    <TableSortLabel
                      active={orderBy === 'id_poli'}
                      direction={orderBy === 'id_poli' ? order : 'asc'}
                      onClick={() => handleRequestSort('id_poli')}
                    >
                      ID Layanan
                    </TableSortLabel>
                  </TableCell>
                  <TableCell style={{ fontWeight: 'bold' }}>
                    <TableSortLabel
                      active={orderBy === 'nama_poli'}
                      direction={orderBy === 'nama_poli' ? order : 'asc'}
                      onClick={() => handleRequestSort('nama_poli')}
                    >
                      Nama Layanan
                    </TableSortLabel>
                  </TableCell>
                  <TableCell style={{ fontWeight: 'bold' }}>
                    <TableSortLabel
                      active={orderBy === 'kode_antrian'}
                      direction={orderBy === 'kode_antrian' ? order : 'asc'}
                      onClick={() => handleRequestSort('kode_antrian')}
                    >
                      Kode Antrean
                    </TableSortLabel>
                  </TableCell>
                  <TableCell style={{ fontWeight: 'bold' }}>
                    <TableSortLabel
                      active={orderBy === 'deskripsi'}
                      direction={orderBy === 'deskripsi' ? order : 'asc'}
                      onClick={() => handleRequestSort('deskripsi')}
                    >
                      Deskripsi
                    </TableSortLabel>
                  </TableCell>
                  <TableCell style={{ fontWeight: 'bold' }}>
                    <TableSortLabel
                      active={orderBy === 'status'}
                      direction={orderBy === 'status' ? order : 'asc'}
                      onClick={() => handleRequestSort('status')}
                    >
                      Status
                    </TableSortLabel>
                  </TableCell>
                  <TableCell style={{ fontWeight: 'bold' }} align="center">
                    Aksi
                  </TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {loading ? (
                  <TableRow>
                    <TableCell colSpan={6} align="center" sx={{ py: 3 }}>
                      Memuat data...
                    </TableCell>
                  </TableRow>
                ) : filteredList.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={6} align="center" sx={{ py: 3 }}>
                      Belum ada data layanan tersedia.
                    </TableCell>
                  </TableRow>
                ) : (
                  paginatedList.map((item) => (
                    <TableRow key={item.id_poli} hover>
                      <TableCell sx={{ fontWeight: 'bold', color: 'text.primary' }}>
                        {item.id_poli}
                      </TableCell>
                      <TableCell>{item.nama_poli}</TableCell>
                      <TableCell>
                        <Chip
                          label={item.kode_antrian}
                          color="secondary"
                          size="small"
                          sx={{ fontWeight: 'bold', borderRadius: '6px' }}
                        />
                      </TableCell>
                      <TableCell sx={{ maxWidth: 300, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {item.deskripsi || '-'}
                      </TableCell>
                      <TableCell>
                        <Chip
                          label={item.status === 'aktif' ? 'Aktif' : 'Non-Aktif'}
                          color={item.status === 'aktif' ? 'success' : 'default'}
                          size="small"
                          sx={{ fontWeight: 'bold', borderRadius: '6px' }}
                        />
                      </TableCell>
                      <TableCell align="center">
                        <IconButton color="primary" onClick={() => handleOpenEdit(item)}>
                          <Icon icon="ic:round-edit" />
                        </IconButton>
                        <IconButton color="error" onClick={() => handleOpenDelete(item)}>
                          <Icon icon="ic:round-delete" />
                        </IconButton>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </TableContainer>
          <TablePagination
            rowsPerPageOptions={[5, 10, 25]}
            component="div"
            count={sortedList.length}
            rowsPerPage={rowsPerPage}
            page={page}
            onPageChange={(_, newPage) => setPage(newPage)}
            onRowsPerPageChange={(e) => {
              setRowsPerPage(parseInt(e.target.value, 10));
              setPage(0);
            }}
            labelRowsPerPage="Baris per halaman:"
            sx={{ borderTop: '1px solid rgba(0,0,0,0.06)' }}
          />
        </CardContent>
      </Card>

      {/* Add / Edit Dialog */}
      <Dialog open={openDialog} onClose={() => setOpenDialog(false)} fullWidth maxWidth="sm">
        <DialogTitle sx={{ fontWeight: 'bold' }}>
          {isEdit ? 'Edit Layanan' : 'Tambah Layanan Baru'}
        </DialogTitle>
        <DialogContent dividers>
          <Grid container spacing={2} pt={1}>
            <Grid item xs={12}>
              <TextField
                label="ID Layanan (Kode Singkat)"
                fullWidth
                required
                disabled={isEdit}
                value={idLayanan}
                onChange={(e) => setIdLayanan(e.target.value.toUpperCase())}
                placeholder="Contoh: UMM, GIG, OBG"
                helperText="ID unik 3-10 huruf kapital, tidak dapat diubah setelah disimpan."
              />
            </Grid>
            <Grid item xs={12}>
              <TextField
                label="Nama Layanan"
                fullWidth
                required
                value={namaLayanan}
                onChange={(e) => setNamaLayanan(e.target.value)}
                placeholder="Contoh: Layanan Umum"
              />
            </Grid>
            <Grid item xs={12}>
              <TextField
                label="Kode Antrean (Prefix)"
                fullWidth
                required
                value={kodeAntrian}
                onChange={(e) => setKodeAntrian(e.target.value.toUpperCase())}
                placeholder="Contoh: A, B, C"
                inputProps={{ maxLength: 2 }}
                helperText="Prefix huruf untuk tiket antrean (maksimal 2 karakter)"
              />
            </Grid>
            <Grid item xs={12}>
              <TextField
                label="Deskripsi Layanan"
                fullWidth
                multiline
                rows={3}
                value={deskripsi}
                onChange={(e) => setDeskripsi(e.target.value)}
                placeholder="Tuliskan keterangan detail mengenai layanan ini..."
              />
            </Grid>
            <Grid item xs={12}>
              <FormControl fullWidth>
                <InputLabel>Status Layanan</InputLabel>
                <Select
                  value={status}
                  label="Status Layanan"
                  onChange={(e) => setStatus(e.target.value)}
                >
                  <MenuItem value="aktif">Aktif</MenuItem>
                  <MenuItem value="nonaktif">Non-Aktif</MenuItem>
                </Select>
              </FormControl>
            </Grid>
          </Grid>
        </DialogContent>
        <DialogActions sx={{ p: 2.5 }}>
          <Button onClick={() => setOpenDialog(false)} sx={{ textTransform: 'none' }}>
            Batal
          </Button>
          <Button variant="contained" onClick={handleSave} sx={{ textTransform: 'none', px: 3 }}>
            Simpan
          </Button>
        </DialogActions>
      </Dialog>

      {/* Delete Confirmation Dialog */}
      <Dialog open={openDeleteDialog} onClose={() => setOpenDeleteDialog(false)}>
        <DialogTitle sx={{ fontWeight: 'bold' }}>Hapus Layanan</DialogTitle>
        <DialogContent>
          <Typography>
            Apakah Anda yakin ingin menghapus layanan{' '}
            <strong>{selectedLayanan?.nama_poli}</strong>?
          </Typography>
          <Typography color="error" variant="caption" display="block" mt={1}>
            Peringatan: Menghapus layanan ini juga akan menghapus data Dokter dan Antrean yang
            terhubung (Cascade Delete).
          </Typography>
        </DialogContent>
        <DialogActions sx={{ p: 2 }}>
          <Button onClick={() => setOpenDeleteDialog(false)} sx={{ textTransform: 'none' }}>
            Batal
          </Button>
          <Button variant="contained" color="error" onClick={handleDelete} sx={{ textTransform: 'none' }}>
            Hapus
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
