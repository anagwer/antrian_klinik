/* eslint-disable @typescript-eslint/no-explicit-any */
import React, { useState, useEffect } from 'react';
import {
  Grid,
  Paper,
  Typography,
  Box,
  Button,
  Card,
  CardContent,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Chip,
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  Divider,
  CircularProgress
} from '@mui/material';
import { Icon } from '@iconify/react';
import ReactECharts from 'echarts-for-react';

const API_BASE = window.location.origin + '/project/antrian/index.php/api';

const SAMPLE_KTP_DATA = [
  {
    label: 'KTP Contoh 1: Budi Hermawan (Pria)',
    nik: '3201011212950001',
    nama: 'BUDI HERMAWAN',
    tempat_lahir: 'JAKARTA',
    tanggal_lahir: '1995-12-12',
    jenis_kelamin: 'L',
    alamat: 'JL. KENANGA NO. 12, RT 003/RW 004, PALMERAH, JAKARTA BARAT',
    no_hp: '081234567890'
  },
  {
    label: 'KTP Contoh 2: Ani Lestari (Wanita)',
    nik: '3174024508920003',
    nama: 'ANI LESTARI',
    tempat_lahir: 'BANDUNG',
    tanggal_lahir: '1992-08-15',
    jenis_kelamin: 'P',
    alamat: 'JL. MAWAR INDAH NO. 45, RT 001/RW 002, KEBAYORAN BARU, JAKARTA SELATAN',
    no_hp: '085712345678'
  }
];

export default function Dashboard() {
  const [metrics, setMetrics] = useState({
    total: 0,
    waiting: 0,
    serving: 0,
    completed: 0,
    skipped: 0
  });

  const [poliklinik, setPoliklinik] = useState<any[]>([]);
  const [selectedPoli, setSelectedPoli] = useState('');
  const [queues, setQueues] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  
  // Chart states
  const [byPoliChartData, setByPoliChartData] = useState<any[]>([]);
  const [regTypeChartData, setRegTypeChartData] = useState({ online: 0, offline: 0 });

  // Dialog state for registering walk-in offline patient
  const [openRegisterDialog, setOpenRegisterDialog] = useState(false);
  const [regNik, setRegNik] = useState('');
  const [regNama, setRegNama] = useState('');
  const [regTempatLahir, setRegTempatLahir] = useState('');
  const [regTanggalLahir, setRegTanggalLahir] = useState('');
  const [regJenisKelamin, setRegJenisKelamin] = useState('');
  const [regAlamat, setRegAlamat] = useState('');
  const [regNoHp, setRegNoHp] = useState('');
  const [regKeluhan, setRegKeluhan] = useState('');
  const [regIdPoli, setRegIdPoli] = useState('');
  const [regIdDokter, setRegIdDokter] = useState('');
  const [regDokterList, setRegDokterList] = useState<any[]>([]);
  
  // Ticket PDF/Print state
  const [ticketData, setTicketData] = useState<any>(null);
  const [openTicket, setOpenTicket] = useState(false);

  // Fetch poliklinik on mount
  useEffect(() => {
    fetch(`${API_BASE}/poliklinik`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setPoliklinik(data.data);
          if (data.data.length > 0) {
            setSelectedPoli(data.data[0].id_poli);
          }
        }
      })
      .catch((err) => console.error('Gagal mengambil poliklinik:', err));
  }, []);

  // Fetch queues and dashboard stats
  const refreshData = () => {
    if (!selectedPoli) return;

    setLoading(true);
    // 1. Fetch dashboard metrics and chart stats
    fetch(`${API_BASE}/dashboard_stats`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setMetrics(data.data.metrics);
          setByPoliChartData(data.data.by_poli);
          setRegTypeChartData(data.data.registration_types);
        }
      })
      .catch((err) => console.error('Gagal mengambil dashboard stats:', err));

    // 2. Fetch queues list for the selected poliklinik
    fetch(`${API_BASE}/queue/list?id_poli=${selectedPoli}`)
      .then((res) => res.json())
      .then((data) => {
        setLoading(false);
        if (data.status === 'success') {
          setQueues(data.data);
        }
      })
      .catch((err) => {
        setLoading(false);
        console.error('Gagal mengambil antrean:', err);
      });
  };

  useEffect(() => {
    refreshData();
    // Poll updates every 6 seconds
    const interval = setInterval(refreshData, 6000);
    return () => clearInterval(interval);
  }, [selectedPoli]);

  // Fetch doctors for registration dialog
  useEffect(() => {
    if (!regIdPoli) {
      setRegDokterList([]);
      setRegIdDokter('');
      return;
    }

    fetch(`${API_BASE}/dokter?id_poli=${regIdPoli}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setRegDokterList(data.data);
          if (data.data.length > 0) {
            setRegIdDokter(data.data[0].id_dokter);
          }
        }
      })
      .catch((err) => console.error('Gagal mengambil dokter:', err));
  }, [regIdPoli]);

  // Voice Announcement helper
  const announceQueue = (number: string, poliName: string) => {
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel(); // Cancel any current speech queue to call immediately

      let formattedNumber = number.toUpperCase();
      formattedNumber = formattedNumber.replace('-', ' ');
      formattedNumber = formattedNumber.replace(/0/g, ' nol ');
      formattedNumber = formattedNumber.replace(/1/g, ' satu ');
      formattedNumber = formattedNumber.replace(/2/g, ' dua ');
      formattedNumber = formattedNumber.replace(/3/g, ' tiga ');
      formattedNumber = formattedNumber.replace(/4/g, ' empat ');
      formattedNumber = formattedNumber.replace(/5/g, ' lima ');
      formattedNumber = formattedNumber.replace(/6/g, ' enam ');
      formattedNumber = formattedNumber.replace(/7/g, ' tujuh ');
      formattedNumber = formattedNumber.replace(/8/g, ' delapan ');
      formattedNumber = formattedNumber.replace(/9/g, ' sembilan ');

      const speechText = `Nomor antrean, ${formattedNumber}. Silakan menuju, ${poliName}.`;
      
      const utterance = new SpeechSynthesisUtterance(speechText);
      utterance.lang = 'id-ID';
      utterance.rate = 0.85;
      utterance.pitch = 1.25; // High pitch for female effect
      
      const voices = window.speechSynthesis.getVoices();
      let voice = voices.find(v => 
        (v.lang.startsWith('id') || v.lang.includes('ID')) && 
        (v.name.toLowerCase().includes('gadis') || v.name.toLowerCase().includes('google') || v.name.toLowerCase().includes('female') || v.name.toLowerCase().includes('local'))
      );
      if (!voice) {
        voice = voices.find(v => v.lang.startsWith('id') || v.lang.includes('ID'));
      }
      if (voice) {
        utterance.voice = voice;
      }

      window.speechSynthesis.speak(utterance);
    }
  };

  // FIFO control: Call Next patient
  const handleCallNext = () => {
    if (!selectedPoli) return;

    fetch(`${API_BASE}/queue/call_next`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id_poli: selectedPoli })
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          announceQueue(data.data.nomor_antrian, data.data.nama_poli);
          alert(`Memanggil nomor antrean: ${data.data.nomor_antrian} (${data.data.nama_pasien})`);
          refreshData();
        } else if (data.status === 'info') {
          alert(data.message);
        } else {
          alert(data.message || 'Gagal memanggil antrean.');
        }
      })
      .catch((err) => console.error(err));
  };

  // Action: Recall patient
  const handleRecall = (id_antrian: number) => {
    fetch(`${API_BASE}/queue/recall`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id_antrian })
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          // Re-fetch to speak properly
          announceQueue(data.data.nomor_antrian, data.data.nama_poli);
          alert(`Memanggil ulang nomor antrean: ${data.data.nomor_antrian}`);
          refreshData();
        } else {
          alert(data.message || 'Gagal memanggil ulang.');
        }
      })
      .catch((err) => console.error(err));
  };

  // Action: Update status (Selesai / Dilewati)
  const handleUpdateStatus = (id_antrian: number, status: string) => {
    fetch(`${API_BASE}/queue/update_status`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id_antrian, status })
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          refreshData();
        } else {
          alert(data.message || 'Gagal memperbarui status.');
        }
      })
      .catch((err) => console.error(err));
  };

  // Handle offline registration submit
  const handleRegisterOffline = (e: React.FormEvent) => {
    e.preventDefault();
    
    const payload = {
      nik: regNik,
      nama: regNama,
      tempat_lahir: regTempatLahir,
      tanggal_lahir: regTanggalLahir,
      jenis_kelamin: regJenisKelamin,
      alamat: regAlamat,
      no_hp: regNoHp,
      keluhan: regKeluhan,
      id_poli: regIdPoli,
      id_dokter: regIdDokter,
      tipe_pendaftaran: 'offline'
    };

    fetch(`${API_BASE}/queue/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setOpenRegisterDialog(false);
          setTicketData(data.data);
          setOpenTicket(true);
          
          // Clear registration form
          setRegNik('');
          setRegNama('');
          setRegTempatLahir('');
          setRegTanggalLahir('');
          setRegJenisKelamin('');
          setRegAlamat('');
          setRegNoHp('');
          setRegKeluhan('');
          
          refreshData();
        } else {
          alert(data.message || 'Gagal mendaftarkan pasien offline.');
        }
      })
      .catch((err) => console.error(err));
  };

  // Demo auto-fill helper for admin walk-in
  const handleSelectDemo = (e: any) => {
    const idx = parseInt(e.target.value);
    if (!isNaN(idx)) {
      const data = SAMPLE_KTP_DATA[idx];
      setRegNik(data.nik);
      setRegNama(data.nama);
      setRegTempatLahir(data.tempat_lahir);
      setRegTanggalLahir(data.tanggal_lahir);
      setRegJenisKelamin(data.jenis_kelamin);
      setRegAlamat(data.alamat);
      setRegNoHp(data.no_hp);
    }
  };

  // ECharts Configurations
  const poliChartOption = {
    title: { text: 'Jumlah Antrean per Poli', left: 'center', textStyle: { fontSize: 14 } },
    tooltip: { trigger: 'item' },
    legend: { bottom: '0%' },
    series: [
      {
        name: 'Jumlah Antrean',
        type: 'pie',
        radius: '55%',
        data: byPoliChartData.map((item) => ({
          value: parseInt(item.count) || 0,
          name: item.nama_poli
        })),
        emphasis: {
          itemStyle: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: 'rgba(0, 0, 0, 0.5)'
          }
        }
      }
    ]
  };

  const regTypeChartOption = {
    title: { text: 'Tipe Pendaftaran', left: 'center', textStyle: { fontSize: 14 } },
    tooltip: { trigger: 'item' },
    legend: { bottom: '0%' },
    series: [
      {
        name: 'Tipe',
        type: 'pie',
        radius: ['40%', '70%'],
        avoidLabelOverlap: false,
        itemStyle: { borderRadius: 8 },
        data: [
          { value: regTypeChartData.online, name: 'Online' },
          { value: regTypeChartData.offline, name: 'Offline (Walk-in)' }
        ],
        emphasis: {
          label: { show: true, fontSize: 14, fontWeight: 'bold' }
        }
      }
    ]
  };

  return (
    <Box sx={{ p: 3 }}>
      {/* Print Styles for dialog ticket */}
      <style>{`
        @media print {
          body * {
            visibility: hidden;
          }
          #print-area-admin, #print-area-admin * {
            visibility: visible;
          }
          #print-area-admin {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            box-shadow: none;
          }
          .no-print {
            display: none !important;
          }
        }
      `}</style>

      {/* Top Banner Row */}
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={4}>
        <Box>
          <Typography variant="h4" fontWeight="800">
            Dashboard Antrian & Pendaftaran
          </Typography>
          <Typography variant="subtitle2" color="text.secondary">
            Klinik Anagwer - Panel Administrasi Loket Utama
          </Typography>
        </Box>
        <Box display="flex" gap={2}>
          <Button
            variant="contained"
            color="secondary"
            startIcon={<Icon icon="ic:round-refresh" />}
            onClick={refreshData}
          >
            Refresh
          </Button>
          <Button
            variant="contained"
            color="primary"
            startIcon={<Icon icon="ic:round-person-add" />}
            onClick={() => setOpenRegisterDialog(true)}
          >
            Daftar Offline (Walk-in)
          </Button>
        </Box>
      </Box>

      {/* Metrics Row */}
      <Grid container spacing={3} mb={4}>
        {[
          { label: 'Total Antrean', count: metrics.total, icon: 'ic:round-people', color: 'primary.main', bg: 'rgba(25,118,210,0.06)' },
          { label: 'Antrean Menunggu', count: metrics.waiting, icon: 'ic:round-hourglass-empty', color: 'warning.main', bg: 'rgba(239,108,0,0.06)' },
          { label: 'Sedang Dilayani', count: metrics.serving, icon: 'ic:round-play-circle', color: 'success.main', bg: 'rgba(46,125,50,0.06)' },
          { label: 'Selesai Dilayani', count: metrics.completed, icon: 'ic:round-check-circle', color: 'info.main', bg: 'rgba(2,136,209,0.06)' },
          { label: 'Dilewati', count: metrics.skipped, icon: 'ic:round-cancel', color: 'error.main', bg: 'rgba(211,47,47,0.06)' }
        ].map((m, idx) => (
          <Grid item xs={12} sm={6} md={2.4} key={idx}>
            <Card sx={{ bgcolor: m.bg, borderRadius: '12px', border: '1px solid rgba(0,0,0,0.02)' }}>
              <CardContent sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', py: 2.5 }}>
                <Box>
                  <Typography variant="body2" color="text.secondary" fontWeight="bold">
                    {m.label}
                  </Typography>
                  <Typography variant="h3" fontWeight="800" color={m.color} mt={1}>
                    {m.count}
                  </Typography>
                </Box>
                <Icon icon={m.icon} width={40} height={40} color={m.color} style={{ opacity: 0.8 }} />
              </CardContent>
            </Card>
          </Grid>
        ))}
      </Grid>

      {/* Main Queue Management Section */}
      <Grid container spacing={4} mb={4}>
        
        {/* Left Side: Queue Controls and Active Queue Table */}
        <Grid item xs={12} md={8}>
          <Paper elevation={2} sx={{ p: 3, borderRadius: '12px' }}>
            <Box display="flex" justifyContent="space-between" alignItems="center" mb={3} flexWrap="wrap" gap={2}>
              <Typography variant="h6" fontWeight="bold">
                Kontrol Antrean Poliklinik
              </Typography>
              
              {/* Select Clinic to manage */}
              <Box display="flex" alignItems="center" gap={2}>
                <FormControl size="small" sx={{ minWidth: 200 }}>
                  <InputLabel>Pilih Poliklinik</InputLabel>
                  <Select
                    value={selectedPoli}
                    label="Pilih Poliklinik"
                    onChange={(e) => setSelectedPoli(e.target.value)}
                  >
                    {poliklinik.map((p) => (
                      <MenuItem key={p.id_poli} value={p.id_poli}>
                        {p.nama_poli}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
                
                {/* FIFO Call Next Button */}
                <Button
                  variant="contained"
                  color="success"
                  startIcon={<Icon icon="ic:round-volume-up" />}
                  onClick={handleCallNext}
                  disabled={!selectedPoli}
                >
                  Panggil Antrean FIFO
                </Button>
              </Box>
            </Box>

            <Divider sx={{ mb: 2 }} />

            {/* Queue List Table */}
            <TableContainer sx={{ maxHeight: 450 }}>
              <Table stickyHeader size="small">
                <TableHead>
                  <TableRow>
                    <TableCell sx={{ fontWeight: 'bold' }}>No. Antrean</TableCell>
                    <TableCell sx={{ fontWeight: 'bold' }}>Nama Pasien</TableCell>
                    <TableCell sx={{ fontWeight: 'bold' }}>Keluhan</TableCell>
                    <TableCell sx={{ fontWeight: 'bold' }}>Metode</TableCell>
                    <TableCell sx={{ fontWeight: 'bold' }}>Waktu Daftar</TableCell>
                    <TableCell sx={{ fontWeight: 'bold' }}>Status</TableCell>
                    <TableCell align="center" sx={{ fontWeight: 'bold' }}>Aksi</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {loading && queues.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={7} align="center" sx={{ py: 4 }}>
                        <CircularProgress size={30} />
                      </TableCell>
                    </TableRow>
                  ) : queues.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={7} align="center" sx={{ py: 4 }} color="text.secondary">
                        Belum ada antrean terdaftar untuk poliklinik ini hari ini.
                      </TableCell>
                    </TableRow>
                  ) : (
                    queues.map((row) => (
                      <TableRow key={row.id_antrian} hover>
                        <TableCell sx={{ fontWeight: 'bold', fontFamily: 'monospace', fontSize: '15px' }}>
                          {row.nomor_antrian}
                        </TableCell>
                        <TableCell>
                          <Typography variant="body2" fontWeight="bold">
                            {row.nama_pasien}
                          </Typography>
                          <Typography variant="caption" color="text.secondary">
                            NIK: {row.nik_pasien}
                          </Typography>
                        </TableCell>
                        <TableCell sx={{ maxWidth: 180, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                          {row.keluhan}
                        </TableCell>
                        <TableCell>
                          <Chip
                            label={row.tipe_pendaftaran.toUpperCase()}
                            size="small"
                            color={row.tipe_pendaftaran === 'online' ? 'primary' : 'secondary'}
                            variant="outlined"
                          />
                        </TableCell>
                        <TableCell>
                          {new Date(row.waktu_daftar).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                        </TableCell>
                        <TableCell>
                          <Chip
                            label={row.status.toUpperCase()}
                            size="small"
                            color={
                              row.status === 'dipanggil'
                                ? 'success'
                                : row.status === 'selesai'
                                ? 'info'
                                : row.status === 'dilewati'
                                ? 'error'
                                : 'default'
                            }
                          />
                        </TableCell>
                        <TableCell align="center">
                          <Box display="flex" gap={1} justifyContent="center">
                            {row.status === 'dipanggil' ? (
                              <>
                                <Button
                                  variant="contained"
                                  size="small"
                                  color="info"
                                  onClick={() => handleUpdateStatus(row.id_antrian, 'selesai')}
                                  title="Selesai Dilayani"
                                  sx={{ color: '#fff', fontWeight: 'bold' }}
                                >
                                  Selesai
                                </Button>
                                <Button
                                  variant="contained"
                                  size="small"
                                  color="error"
                                  onClick={() => handleUpdateStatus(row.id_antrian, 'dilewati')}
                                  title="Lewati"
                                  sx={{ color: '#fff', fontWeight: 'bold' }}
                                >
                                  Lewati
                                </Button>
                                <IconButton 
                                  color="primary" 
                                  onClick={() => handleRecall(row.id_antrian)} 
                                  title="Panggil Ulang"
                                  sx={{ bgcolor: 'rgba(25, 118, 210, 0.08)' }}
                                >
                                  <Icon icon="ic:round-volume-up" />
                                </IconButton>
                              </>
                            ) : row.status === 'menunggu' ? (
                              <Button
                                variant="contained"
                                size="small"
                                color="success"
                                onClick={() => handleRecall(row.id_antrian)}
                                title="Panggil Antrean Ini"
                                sx={{ color: '#fff', fontWeight: 'bold' }}
                              >
                                Panggil
                              </Button>
                            ) : (
                              <Button
                                variant="contained"
                                size="small"
                                color="secondary"
                                onClick={() => handleUpdateStatus(row.id_antrian, 'menunggu')}
                                title="Kembalikan ke antrean tunggu"
                                sx={{ color: '#fff', fontWeight: 'bold' }}
                              >
                                Reset
                              </Button>
                            )}
                          </Box>
                        </TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </TableContainer>
          </Paper>
        </Grid>

        {/* Right Side: Charts / Real-time Statistics */}
        <Grid item xs={12} md={4} display="flex" flexDirection="column" gap={3}>
          <Paper elevation={2} sx={{ p: 3, borderRadius: '12px' }}>
            <ReactECharts option={poliChartOption} style={{ height: '210px' }} />
          </Paper>

          <Paper elevation={2} sx={{ p: 3, borderRadius: '12px' }}>
            <ReactECharts option={regTypeChartOption} style={{ height: '210px' }} />
          </Paper>
        </Grid>
      </Grid>

      {/* Offline Walk-in Registration Dialog */}
      <Dialog open={openRegisterDialog} onClose={() => setOpenRegisterDialog(false)} maxWidth="sm" fullWidth>
        <DialogTitle fontWeight="bold">Daftar Antrean Offline (Walk-in)</DialogTitle>
        <form onSubmit={handleRegisterOffline}>
          <DialogContent dividers>
            <Grid container spacing={2}>
              {/* KTP Sample Auto-fill for convenience */}
              <Grid item xs={12}>
                <FormControl fullWidth size="small">
                  <InputLabel>Demo: Auto-Fill KTP Walk-in</InputLabel>
                  <Select label="Demo: Auto-Fill KTP Walk-in" onChange={handleSelectDemo} defaultValue="">
                    <MenuItem value="">-- Pilih Data KTP Demo --</MenuItem>
                    {SAMPLE_KTP_DATA.map((ktp, i) => (
                      <MenuItem key={i} value={i}>
                        {ktp.label}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Grid>

              <Grid item xs={12} sm={6}>
                <TextField
                  label="NIK Pasien"
                  fullWidth
                  required
                  value={regNik}
                  onChange={(e) => setRegNik(e.target.value.replace(/[^0-9]/g, ''))}
                  inputProps={{ maxLength: 16 }}
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  label="Nama Lengkap"
                  fullWidth
                  required
                  value={regNama}
                  onChange={(e) => setRegNama(e.target.value.toUpperCase())}
                />
              </Grid>

              <Grid item xs={12} sm={4}>
                <TextField
                  label="Tempat Lahir"
                  fullWidth
                  value={regTempatLahir}
                  onChange={(e) => setRegTempatLahir(e.target.value.toUpperCase())}
                />
              </Grid>
              <Grid item xs={12} sm={4}>
                <TextField
                  label="Tanggal Lahir"
                  type="date"
                  fullWidth
                  InputLabelProps={{ shrink: true }}
                  value={regTanggalLahir}
                  onChange={(e) => setRegTanggalLahir(e.target.value)}
                />
              </Grid>
              <Grid item xs={12} sm={4}>
                <FormControl fullWidth>
                  <InputLabel>Jenis Kelamin</InputLabel>
                  <Select
                    value={regJenisKelamin}
                    label="Jenis Kelamin"
                    onChange={(e) => setRegJenisKelamin(e.target.value)}
                  >
                    <MenuItem value="L">Laki-Laki</MenuItem>
                    <MenuItem value="P">Perempuan</MenuItem>
                  </Select>
                </FormControl>
              </Grid>

              <Grid item xs={12} sm={6}>
                <TextField
                  label="Nomor HP"
                  fullWidth
                  required
                  value={regNoHp}
                  onChange={(e) => setRegNoHp(e.target.value.replace(/[^0-9]/g, ''))}
                />
              </Grid>
              <Grid item xs={12} sm={6}>
                <TextField
                  label="Alamat Pasien"
                  fullWidth
                  value={regAlamat}
                  onChange={(e) => setRegAlamat(e.target.value.toUpperCase())}
                />
              </Grid>

              <Grid item xs={12}>
                <Divider sx={{ my: 1 }} />
                <Typography variant="subtitle2" fontWeight="bold" color="primary">
                  Tujuan Layanan
                </Typography>
              </Grid>

              <Grid item xs={12} sm={6}>
                <FormControl fullWidth required>
                  <InputLabel>Poliklinik</InputLabel>
                  <Select
                    value={regIdPoli}
                    label="Poliklinik"
                    onChange={(e) => setRegIdPoli(e.target.value)}
                  >
                    {poliklinik.map((p) => (
                      <MenuItem key={p.id_poli} value={p.id_poli}>
                        {p.nama_poli}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Grid>

              <Grid item xs={12} sm={6}>
                <FormControl fullWidth required disabled={!regIdPoli}>
                  <InputLabel>Dokter Spesialis</InputLabel>
                  <Select
                    value={regIdDokter}
                    label="Dokter Spesialis"
                    onChange={(e) => setRegIdDokter(e.target.value)}
                  >
                    {regDokterList.map((d) => (
                      <MenuItem key={d.id_dokter} value={d.id_dokter}>
                        {d.nama_dokter}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>
              </Grid>

              <Grid item xs={12}>
                <TextField
                  label="Keluhan Utama"
                  fullWidth
                  required
                  multiline
                  rows={2}
                  value={regKeluhan}
                  onChange={(e) => setRegKeluhan(e.target.value)}
                />
              </Grid>
            </Grid>
          </DialogContent>
          <DialogActions>
            <Button onClick={() => setOpenRegisterDialog(false)} color="inherit">
              Batal
            </Button>
            <Button type="submit" variant="contained" color="primary">
              Cetak & Ambil Antrean
            </Button>
          </DialogActions>
        </form>
      </Dialog>

      {/* Ticket Result Dialog for Offline Registration */}
      <Dialog open={openTicket} onClose={() => setOpenTicket(false)} maxWidth="xs" fullWidth>
        <DialogContent id="print-area-admin">
          {ticketData && (
            <Box
              sx={{
                textAlign: 'center',
                p: 2,
                border: '2px dashed #1976d2',
                borderRadius: '8px',
                bgcolor: '#fff'
              }}
            >
              <Typography variant="h5" fontWeight="bold" color="primary" gutterBottom>
                KLINIK ANAGWER
              </Typography>
              <Typography variant="caption" display="block" color="text.secondary" gutterBottom>
                Jl. Sehat Walafiat No. 1, Kota Sehat
              </Typography>
              <Divider sx={{ my: 1.5 }} />

              <Typography variant="subtitle2" color="text.secondary">
                NOMOR ANTREAN OFFLINE
              </Typography>
              <Typography variant="h2" fontWeight="800" color="primary.main" my={1}>
                {ticketData.nomor_antrian}
              </Typography>
              <Typography variant="body2" fontWeight="bold" gutterBottom>
                {ticketData.nama_poli}
              </Typography>
              <Typography variant="caption" display="block" color="text.secondary" paragraph>
                Dokter: {ticketData.nama_dokter}
              </Typography>

              <Divider sx={{ my: 1.5 }} />

              <Grid container spacing={1} sx={{ textAlign: 'left', fontSize: '13px' }}>
                <Grid item xs={5}>
                  <Typography variant="caption" color="text.secondary">Nama Pasien</Typography>
                </Grid>
                <Grid item xs={7}>
                  <Typography variant="body2" fontWeight="bold">{ticketData.nama}</Typography>
                </Grid>
                <Grid item xs={5}>
                  <Typography variant="caption" color="text.secondary">NIK</Typography>
                </Grid>
                <Grid item xs={7}>
                  <Typography variant="body2">{ticketData.nik}</Typography>
                </Grid>
                <Grid item xs={5}>
                  <Typography variant="caption" color="text.secondary">Waktu Daftar</Typography>
                </Grid>
                <Grid item xs={7}>
                  <Typography variant="body2">{ticketData.waktu_daftar}</Typography>
                </Grid>
                <Grid item xs={5}>
                  <Typography variant="caption" color="text.secondary">Antrean Tunggu</Typography>
                </Grid>
                <Grid item xs={7}>
                  <Typography variant="body2" fontWeight="bold" color="warning.main">
                    {ticketData.estimasi_tunggu_menit > 0 ? `${ticketData.estimasi_tunggu_menit / 10} Pasien Sebelum Anda` : 'Silakan Masuk'}
                  </Typography>
                </Grid>
              </Grid>

              <Divider sx={{ my: 2 }} />

              {/* Simulated QR Code */}
              <Box display="flex" justifyContent="center" mb={2}>
                <Box
                  sx={{
                    width: 100,
                    height: 100,
                    border: '1px solid #ddd',
                    p: 1,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center'
                  }}
                >
                  <Icon icon="ic:round-qr-code" width={80} height={80} />
                </Box>
              </Box>

              <Typography variant="caption" color="text.secondary" display="block">
                Silakan menunggu di ruang tunggu.
              </Typography>
            </Box>
          )}
        </DialogContent>
        <DialogActions sx={{ p: 2, justifyContent: 'space-between' }} className="no-print">
          <Button onClick={() => setOpenTicket(false)} color="inherit">
            Tutup
          </Button>
          <Button onClick={() => window.print()} variant="contained" color="primary" startIcon={<Icon icon="ic:round-print" />}>
            Cetak Tiket
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
