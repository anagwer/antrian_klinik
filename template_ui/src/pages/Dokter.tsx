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

export default function Dokter() {
  const [dokterList, setDokterList] = useState<any[]>([]);
  const [layananList, setLayananList] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [successMsg, setSuccessMsg] = useState('');

  // DataTables states
  const [searchTerm, setSearchTerm] = useState('');
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [orderBy, setOrderBy] = useState('id_dokter');
  const [order, setOrder] = useState<'asc' | 'desc'>('asc');

  // Search filter
  const filteredList = dokterList.filter((item) => {
    const term = searchTerm.toLowerCase();
    return (
      String(item.id_dokter).includes(term) ||
      item.nama_dokter.toLowerCase().includes(term) ||
      (item.nama_poli && item.nama_poli.toLowerCase().includes(term)) ||
      (item.jadwal_praktek && item.jadwal_praktek.toLowerCase().includes(term))
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
  const [selectedDokter, setSelectedDokter] = useState<any>(null);

  // Form states
  const [idDokter, setIdDokter] = useState('');
  const [namaDokter, setNamaDokter] = useState('');
  const [idLayanan, setIdLayanan] = useState('');
  const [jadwalPraktek, setJadwalPraktek] = useState('');
  const [status, setStatus] = useState('aktif');

  const fetchData = () => {
    setLoading(true);
    // Fetch both doctors and services
    Promise.all([
      fetch(`${API_BASE}/dokter?all=true`).then((res) => res.json()),
      fetch(`${API_BASE}/poliklinik?all=true`).then((res) => res.json()),
    ])
      .then(([dokterData, layananData]) => {
        if (dokterData.status === 'success') {
          setDokterList(dokterData.data);
        } else {
          setErrorMsg(dokterData.message || 'Gagal mengambil data dokter.');
        }

        if (layananData.status === 'success') {
          setLayananList(layananData.data);
        }
      })
      .catch((err) => {
        console.error(err);
        setErrorMsg('Gagal terhubung ke API backend.');
      })
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleOpenAdd = () => {
    setIsEdit(false);
    setIdDokter('');
    setNamaDokter('');
    setIdLayanan(layananList.length > 0 ? layananList[0].id_poli : '');
    setJadwalPraktek('');
    setStatus('aktif');
    setErrorMsg('');
    setSuccessMsg('');
    setOpenDialog(true);
  };

  const handleOpenEdit = (item: any) => {
    setIsEdit(true);
    setIdDokter(item.id_dokter);
    setNamaDokter(item.nama_dokter);
    setIdLayanan(item.id_poli);
    setJadwalPraktek(item.jadwal_praktek || '');
    setStatus(item.status);
    setErrorMsg('');
    setSuccessMsg('');
    setOpenDialog(true);
  };

  const handleOpenDelete = (item: any) => {
    setSelectedDokter(item);
    setOpenDeleteDialog(true);
  };

  const handleSave = () => {
    if (!namaDokter || !idLayanan) {
      setErrorMsg('Mohon isi semua field wajib.');
      return;
    }

    const payload = {
      id_dokter: idDokter,
      nama_dokter: namaDokter,
      id_poli: idLayanan,
      jadwal_praktek: jadwalPraktek,
      status: status,
    };

    const endpoint = isEdit ? 'dokter_update' : 'dokter_create';

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
          setSuccessMsg(isEdit ? 'Dokter berhasil diperbarui.' : 'Dokter berhasil ditambahkan.');
          setOpenDialog(false);
          fetchData();
        } else {
          setErrorMsg(data.message || 'Gagal menyimpan dokter.');
        }
      })
      .catch((err) => {
        console.error(err);
        setErrorMsg('Gagal terhubung ke server.');
      });
  };

  const handleDelete = () => {
    if (!selectedDokter) return;

    fetch(`${API_BASE}/dokter_delete?id_dokter=${selectedDokter.id_dokter}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setSuccessMsg('Dokter berhasil dihapus.');
          setOpenDeleteDialog(false);
          fetchData();
        } else {
          setErrorMsg(data.message || 'Gagal menghapus dokter.');
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
            Manajemen Dokter Klinik
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Kelola data dokter, jadwal praktek, dan penempatan layanan.
          </Typography>
        </Box>
        <Button
          variant="contained"
          color="primary"
          startIcon={<Icon icon="ic:round-add" />}
          onClick={handleOpenAdd}
          sx={{ borderRadius: '8px', textTransform: 'none', px: 3 }}
        >
          Tambah Dokter
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
              label="Cari Bidan/Terapis (ID, Nama, Layanan, Jadwal)"
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
                      active={orderBy === 'id_dokter'}
                      direction={orderBy === 'id_dokter' ? order : 'asc'}
                      onClick={() => handleRequestSort('id_dokter')}
                    >
                      ID
                    </TableSortLabel>
                  </TableCell>
                  <TableCell style={{ fontWeight: 'bold' }}>
                    <TableSortLabel
                      active={orderBy === 'nama_dokter'}
                      direction={orderBy === 'nama_dokter' ? order : 'asc'}
                      onClick={() => handleRequestSort('nama_dokter')}
                    >
                      Nama Terapis / Bidan
                    </TableSortLabel>
                  </TableCell>
                  <TableCell style={{ fontWeight: 'bold' }}>
                    <TableSortLabel
                      active={orderBy === 'nama_poli'}
                      direction={orderBy === 'nama_poli' ? order : 'asc'}
                      onClick={() => handleRequestSort('nama_poli')}
                    >
                      Layanan
                    </TableSortLabel>
                  </TableCell>
                  <TableCell style={{ fontWeight: 'bold' }}>
                    <TableSortLabel
                      active={orderBy === 'jadwal_praktek'}
                      direction={orderBy === 'jadwal_praktek' ? order : 'asc'}
                      onClick={() => handleRequestSort('jadwal_praktek')}
                    >
                      Jadwal Praktek
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
                      Belum ada data dokter tersedia.
                    </TableCell>
                  </TableRow>
                ) : (
                  paginatedList.map((item) => (
                    <TableRow key={item.id_dokter} hover>
                      <TableCell>{item.id_dokter}</TableCell>
                      <TableCell sx={{ fontWeight: 'bold' }}>{item.nama_dokter}</TableCell>
                      <TableCell>
                        <Chip
                          label={item.nama_poli}
                          color="primary"
                          variant="outlined"
                          size="small"
                          sx={{ borderRadius: '6px', fontWeight: '500' }}
                        />
                      </TableCell>
                      <TableCell>{item.jadwal_praktek || '-'}</TableCell>
                      <TableCell>
                        <Chip
                          label={item.status === 'aktif' ? 'Aktif' : 'Cuti'}
                          color={item.status === 'aktif' ? 'success' : 'warning'}
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
          {isEdit ? 'Edit Dokter' : 'Tambah Dokter Baru'}
        </DialogTitle>
        <DialogContent dividers>
          <Grid container spacing={2} pt={1}>
            <Grid item xs={12}>
              <TextField
                label="Nama Dokter"
                fullWidth
                required
                value={namaDokter}
                onChange={(e) => setNamaDokter(e.target.value)}
                placeholder="Contoh: dr. Andi Wibowo, Sp.A"
              />
            </Grid>
            <Grid item xs={12}>
              <FormControl fullWidth required>
                <InputLabel>Layanan Klinik</InputLabel>
                <Select
                  value={idLayanan}
                  label="Layanan Klinik"
                  onChange={(e) => setIdLayanan(e.target.value)}
                >
                  {layananList.length === 0 ? (
                    <MenuItem value="" disabled>
                      Belum ada Layanan tersedia. Buat Layanan dahulu.
                    </MenuItem>
                  ) : (
                    layananList.map((p) => (
                      <MenuItem key={p.id_poli} value={p.id_poli}>
                        {p.nama_poli} ({p.id_poli})
                      </MenuItem>
                    ))
                  )}
                </Select>
              </FormControl>
            </Grid>
            <Grid item xs={12}>
              <TextField
                label="Jadwal Praktek"
                fullWidth
                value={jadwalPraktek}
                onChange={(e) => setJadwalPraktek(e.target.value)}
                placeholder="Contoh: Senin - Jumat (08:00 - 14:00)"
              />
            </Grid>
            <Grid item xs={12}>
              <FormControl fullWidth>
                <InputLabel>Status Dokter</InputLabel>
                <Select
                  value={status}
                  label="Status Dokter"
                  onChange={(e) => setStatus(e.target.value)}
                >
                  <MenuItem value="aktif">Aktif</MenuItem>
                  <MenuItem value="cuti">Cuti</MenuItem>
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
        <DialogTitle sx={{ fontWeight: 'bold' }}>Hapus Dokter</DialogTitle>
        <DialogContent>
          <Typography>
            Apakah Anda yakin ingin menghapus dokter <strong>{selectedDokter?.nama_dokter}</strong>?
          </Typography>
          <Typography color="error" variant="caption" display="block" mt={1}>
            Peringatan: Menghapus dokter ini juga akan menghapus data Antrean yang terhubung (Cascade Delete).
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
