/* eslint-disable @typescript-eslint/no-explicit-any */
import React, { useState, useEffect, useRef } from 'react';
import {
  Container,
  Paper,
  Typography,
  TextField,
  Button,
  Grid,
  MenuItem,
  FormControl,
  InputLabel,
  Select,
  CircularProgress,
  Box,
  Card,
  CardContent,
  IconButton,
  Divider,
  Dialog,
  DialogContent,
  DialogActions,
  LinearProgress
} from '@mui/material';
import { Icon } from '@iconify/react';
import { createWorker } from 'tesseract.js';
import { API_BASE } from 'api';

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

export default function Register() {
  // Form states
  const [nik, setNik] = useState('');
  const [nama, setNama] = useState('');
  const [tempatLahir, setTempatLahir] = useState('');
  const [tanggalLahir, setTanggalLahir] = useState('');
  const [jenisKelamin, setJenisKelamin] = useState('');
  const [alamat, setAlamat] = useState('');
  const [noHp, setNoHp] = useState('');
  const [keluhan, setKeluhan] = useState('');
  const [idPoli, setIdPoli] = useState('');
  const [idTerapis, setIdTerapis] = useState('');

  // Master Data
  const [poliklinik, setPoliklinik] = useState<any[]>([]);
  const [terapisList, setTerapisList] = useState<any[]>([]);

  // OCR and Loading States
  const [imagePreview, setImagePreview] = useState<string | null>(null);
  const [isScanning, setIsScanning] = useState(false);
  const [scanProgress, setScanProgress] = useState(0);
  const [loading, setLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [successMsg, setSuccessMsg] = useState('');

  // Ticket Modal State
  const [ticketData, setTicketData] = useState<any>(null);
  const [openTicket, setOpenTicket] = useState(false);

  // Camera states
  const [useCamera, setUseCamera] = useState(false);
  const videoRef = useRef<HTMLVideoElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const streamRef = useRef<MediaStream | null>(null);

  // Fetch poliklinik on mount
  useEffect(() => {
    fetch(`${API_BASE}/poliklinik`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setPoliklinik(data.data);
        }
      })
      .catch((err) => console.error('Gagal mengambil poliklinik:', err));
  }, []);

  // Fetch therapists when poliklinik changes
  useEffect(() => {
    if (!idPoli) {
      setTerapisList([]);
      setIdTerapis('');
      return;
    }

    fetch(`${API_BASE}/terapis?id_poli=${idPoli}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setTerapisList(data.data);
          if (data.data.length > 0) {
            setIdTerapis(data.data[0].id_terapis);
          }
        }
      })
      .catch((err) => console.error('Gagal mengambil terapis/bidan:', err));
  }, [idPoli]);

  // Handle file select for KTP upload
  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      const file = e.target.files[0];
      setImagePreview(URL.createObjectURL(file));
      runOcr(file);
    }
  };

  // Stop Camera Stream
  const stopCamera = () => {
    if (streamRef.current) {
      streamRef.current.getTracks().forEach((track) => track.stop());
      streamRef.current = null;
    }
    setUseCamera(false);
  };

  // Start Camera
  const startCamera = async () => {
    setUseCamera(true);
    setImagePreview(null);
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
      streamRef.current = stream;
      if (videoRef.current) {
        videoRef.current.srcObject = stream;
      }
    } catch (err) {
      console.error('Kamera gagal diakses:', err);
      alert('Kamera tidak dapat diakses, silakan unggah foto secara manual.');
      setUseCamera(false);
    }
  };

  // Capture Photo
  const capturePhoto = () => {
    if (videoRef.current && canvasRef.current) {
      const video = videoRef.current;
      const canvas = canvasRef.current;
      const ctx = canvas.getContext('2d');
      if (ctx) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL('image/jpeg');
        setImagePreview(dataUrl);

        // Convert base64 dataUrl to file
        fetch(dataUrl)
          .then((res) => res.blob())
          .then((blob) => {
            const file = new File([blob], 'ktp_capture.jpg', { type: 'image/jpeg' });
            runOcr(file);
          });
        stopCamera();
      }
    }
  };

  // Run Tesseract OCR on KTP image
  const runOcr = async (file: File) => {
    setIsScanning(true);
    setScanProgress(10);
    try {
      const worker = await createWorker('ind');
      setScanProgress(30);
      const ret = await worker.recognize(file);
      setScanProgress(80);
      const text = ret.data.text;
      await worker.terminate();

      setScanProgress(100);
      setTimeout(() => setIsScanning(false), 500);

      // Parse KTP text fields (simple heuristics / regex)
      parseKtpText(text);
    } catch (err) {
      console.error('OCR Error:', err);
      setIsScanning(false);
      alert('OCR Gagal membaca foto. Silakan isi form secara manual.');
    }
  };

  // Regex and Heuristic parser for Indonesian KTP
  const parseKtpText = (text: string) => {
    console.log('Raw KTP OCR Text:', text);
    const lines = text.split('\n');

    // NIK Parsing (16-digit number)
    const nikMatch = text.match(/\d{16}/);
    if (nikMatch) {
      setNik(nikMatch[0]);
    }

    // Heuristics for Nama, Alamat, etc.
    lines.forEach((line) => {
      const upper = line.toUpperCase();

      // Check NIK again if regex failed
      if (upper.includes('NIK') && !nik) {
        const nums = line.replace(/[^0-9]/g, '');
        if (nums.length >= 16) {
          setNik(nums.substring(0, 16));
        }
      }

      // Check Nama
      if (upper.includes('NAMA') || upper.startsWith('NAMA')) {
        const value = line.replace(/nama/i, '').replace(/[:=]/g, '').trim();
        if (value && value.length > 2) setNama(value);
      }

      // Check Tempat / Tgl Lahir
      if (upper.includes('TEMPAT') || upper.includes('LAHIR')) {
        const value = line.replace(/tempat/i, '').replace(/tgl/i, '').replace(/lahir/i, '').replace(/[:=]/g, '').trim();
        // E.g. "JAKARTA, 12-12-1995"
        const parts = value.split(',');
        if (parts.length > 0) {
          setTempatLahir(parts[0].trim());
          if (parts[1]) {
            const dateMatch = parts[1].match(/(\d{2})[-/](\d{2})[-/](\d{4})/);
            if (dateMatch) {
              setTanggalLahir(`${dateMatch[3]}-${dateMatch[2]}-${dateMatch[1]}`);
            }
          }
        }
      }

      // Check Gender (Jenis Kelamin)
      if (upper.includes('KELAMIN') || upper.includes('GENDER') || upper.includes('LAKI')) {
        if (upper.includes('LAKI') || upper.includes('LAK1') || upper.includes('LKP')) {
          setJenisKelamin('L');
        } else if (upper.includes('PEREMPUAN') || upper.includes('PRM') || upper.includes('WANI')) {
          setJenisKelamin('P');
        }
      }

      // Check Alamat
      if (upper.includes('ALAMAT') || upper.startsWith('ALAMAT')) {
        const value = line.replace(/alamat/i, '').replace(/[:=]/g, '').trim();
        if (value && value.length > 5) setAlamat(value);
      }
    });
  };

  // Demo auto-fill helper
  const handleSelectDemo = (e: any) => {
    const idx = parseInt(e.target.value);
    if (!isNaN(idx)) {
      const data = SAMPLE_KTP_DATA[idx];
      setNik(data.nik);
      setNama(data.nama);
      setTempatLahir(data.tempat_lahir);
      setTanggalLahir(data.tanggal_lahir);
      setJenisKelamin(data.jenis_kelamin);
      setAlamat(data.alamat);
      setNoHp(data.no_hp);
    }
  };

  // Handle Form Submit
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setErrorMsg('');
    setSuccessMsg('');

    // Prepare payload
    const payload = {
      nik,
      nama,
      tempat_lahir: tempatLahir,
      tanggal_lahir: tanggalLahir,
      jenis_kelamin: jenisKelamin,
      alamat,
      no_hp: noHp,
      keluhan,
      id_poli: idPoli,
      id_terapis: idTerapis,
      tipe_pendaftaran: 'online',
      foto_ktp: imagePreview || '' // If webcam base64 is captured
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
        setLoading(false);
        if (data.status === 'success') {
          setSuccessMsg(data.message);
          setTicketData(data.data);
          setOpenTicket(true);
          // Reset complaints
          setKeluhan('');
        } else {
          setErrorMsg(data.message || 'Gagal mendaftar antrean.');
        }
      })
      .catch((err) => {
        setLoading(false);
        setErrorMsg('Terjadi kesalahan koneksi server.');
        console.error(err);
      });
  };

  // Print Queue Ticket helper
  const handlePrint = () => {
    window.print();
  };

  return (
    <Container maxWidth="lg" sx={{ py: 4 }}>
      {/* Print styles to only print the ticket */}
      <style>{`
        @media print {
          body * {
            visibility: hidden;
          }
          #print-area, #print-area * {
            visibility: visible;
          }
          #print-area {
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

      <Grid container spacing={4}>
        {/* Left Side: Instructions and KTP Scanner */}
        <Grid item xs={12} md={5}>
          <Box display="flex" flexDirection="column" gap={3}>
            <Box>
              <Typography variant="h4" fontWeight="800" gutterBottom color="primary.main">
                Klinik Aurelia
              </Typography>
              <Typography variant="subtitle1" color="text.secondary">
                Pendaftaran & Pengambilan Antrean Online Pasien
              </Typography>
            </Box>

            <Card
              sx={{
                background: 'rgba(255, 255, 255, 0.7)',
                backdropFilter: 'blur(10px)',
                borderRadius: '16px',
                border: '1px solid rgba(255, 255, 255, 0.3)',
                boxShadow: '0 8px 32px 0 rgba(31, 38, 135, 0.08)'
              }}
            >
              <CardContent>
                <Typography variant="h6" fontWeight="bold" gutterBottom display="flex" alignItems="center" gap={1}>
                  <Icon icon="ic:round-qr-code-scanner" /> Scan KTP untuk Auto-Fill
                </Typography>
                <Typography variant="body2" color="text.secondary" paragraph>
                  Ambil foto KTP Anda melalui kamera atau unggah file gambar untuk mengisi form data diri secara otomatis.
                </Typography>

                {/* Demo Dropdown */}
                <FormControl fullWidth margin="normal" size="small">
                  <InputLabel>Demo: Gunakan Data KTP Contoh</InputLabel>
                  <Select label="Demo: Gunakan Data KTP Contoh" onChange={handleSelectDemo} defaultValue="">
                    <MenuItem value="">-- Pilih KTP Demo --</MenuItem>
                    {SAMPLE_KTP_DATA.map((ktp, i) => (
                      <MenuItem key={i} value={i}>
                        {ktp.label}
                      </MenuItem>
                    ))}
                  </Select>
                </FormControl>

                <Box display="flex" gap={2} mt={2}>
                  <Button
                    variant="contained"
                    color="primary"
                    startIcon={<Icon icon="ic:round-photo-camera" />}
                    onClick={useCamera ? stopCamera : startCamera}
                    fullWidth
                  >
                    {useCamera ? 'Matikan Kamera' : 'Ambil Foto'}
                  </Button>
                  <Button
                    variant="outlined"
                    component="label"
                    startIcon={<Icon icon="ic:round-cloud-upload" />}
                    fullWidth
                  >
                    Unggah File
                    <input type="file" accept="image/*" hidden onChange={handleFileChange} />
                  </Button>
                </Box>

                {useCamera && (
                  <Box mt={3} position="relative" borderRadius="8px" overflow="hidden" bgcolor="#000">
                    <video ref={videoRef} autoPlay playsInline style={{ width: '100%', display: 'block' }} />
                    <Box position="absolute" bottom={16} left="50%" sx={{ transform: 'translateX(-50%)' }}>
                      <IconButton
                        onClick={capturePhoto}
                        sx={{
                          bgcolor: 'primary.main',
                          color: '#fff',
                          '&:hover': { bgcolor: 'primary.dark' },
                          width: 56,
                          height: 56
                        }}
                      >
                        <Icon icon="ic:round-photo-camera" width={32} />
                      </IconButton>
                    </Box>
                    <canvas ref={canvasRef} style={{ display: 'none' }} />
                  </Box>
                )}

                {imagePreview && !useCamera && (
                  <Box mt={3} position="relative" borderRadius="8px" overflow="hidden" border="1px solid #ddd">
                    <img src={imagePreview} alt="KTP Preview" style={{ width: '100%', display: 'block' }} />
                    {isScanning && (
                      <Box
                        position="absolute"
                        top={0}
                        left={0}
                        width="100%"
                        height="100%"
                        display="flex"
                        flexDirection="column"
                        alignItems="center"
                        justifyContent="center"
                        sx={{ background: 'rgba(0,0,0,0.6)', color: '#fff' }}
                      >
                        <CircularProgress color="inherit" size={40} />
                        <Typography variant="body2" fontWeight="bold" mt={2}>
                          Membaca KTP... {scanProgress}%
                        </Typography>
                        <Box width="80%" mt={1}>
                          <LinearProgress variant="determinate" value={scanProgress} color="primary" />
                        </Box>
                      </Box>
                    )}
                  </Box>
                )}
              </CardContent>
            </Card>

            <Card
              sx={{
                borderRadius: '16px',
                bgcolor: 'primary.light',
                color: 'primary.contrastText',
                p: 1
              }}
            >
              <CardContent>
                <Typography variant="subtitle2" fontWeight="bold" display="flex" alignItems="center" gap={1}>
                  <Icon icon="material-symbols:info" /> FIFO (First In First Out)
                </Typography>
                <Typography variant="caption" display="block" mt={1}>
                  Urutan antrean dihitung berdasarkan waktu pendaftaran Anda. Siapa yang mendaftar lebih awal akan dilayani terlebih dahulu oleh terapis/bidan pilihan Anda.
                </Typography>
              </CardContent>
            </Card>
          </Box>
        </Grid>

        {/* Right Side: Registration Form */}
        <Grid item xs={12} md={7}>
          <Paper
            elevation={4}
            sx={{
              p: 4,
              borderRadius: '16px',
              background: 'rgba(255, 255, 255, 0.9)',
              backdropFilter: 'blur(10px)'
            }}
          >
            <Typography variant="h5" fontWeight="bold" gutterBottom>
              Formulir Pendaftaran
            </Typography>
            <Divider sx={{ mb: 3 }} />

            <form onSubmit={handleSubmit}>
              <Grid container spacing={2}>
                <Grid item xs={12} sm={6}>
                  <TextField
                    label="NIK (Nomor Induk Kependudukan)"
                    fullWidth
                    required
                    value={nik}
                    onChange={(e) => setNik(e.target.value.replace(/[^0-9]/g, ''))}
                    inputProps={{ maxLength: 16 }}
                  />
                </Grid>
                <Grid item xs={12} sm={6}>
                  <TextField
                    label="Nama Lengkap Pasien"
                    fullWidth
                    required
                    value={nama}
                    onChange={(e) => setNama(e.target.value.toUpperCase())}
                  />
                </Grid>

                <Grid item xs={12} sm={4}>
                  <TextField
                    label="Tempat Lahir"
                    fullWidth
                    value={tempatLahir}
                    onChange={(e) => setTempatLahir(e.target.value.toUpperCase())}
                  />
                </Grid>
                <Grid item xs={12} sm={4}>
                  <TextField
                    label="Tanggal Lahir"
                    type="date"
                    fullWidth
                    InputLabelProps={{ shrink: true }}
                    value={tanggalLahir}
                    onChange={(e) => setTanggalLahir(e.target.value)}
                  />
                </Grid>
                <Grid item xs={12} sm={4}>
                  <FormControl fullWidth>
                    <InputLabel>Jenis Kelamin</InputLabel>
                    <Select
                      value={jenisKelamin}
                      label="Jenis Kelamin"
                      onChange={(e) => setJenisKelamin(e.target.value)}
                    >
                      <MenuItem value="L">Laki-Laki</MenuItem>
                      <MenuItem value="P">Perempuan</MenuItem>
                    </Select>
                  </FormControl>
                </Grid>

                <Grid item xs={12} sm={6}>
                  <TextField
                    label="Nomor HP / WhatsApp"
                    fullWidth
                    required
                    value={noHp}
                    onChange={(e) => setNoHp(e.target.value.replace(/[^0-9]/g, ''))}
                  />
                </Grid>
                <Grid item xs={12} sm={6}>
                  <TextField
                    label="Alamat Lengkap"
                    fullWidth
                    multiline
                    rows={1}
                    value={alamat}
                    onChange={(e) => setAlamat(e.target.value.toUpperCase())}
                  />
                </Grid>

                <Grid item xs={12}>
                  <Divider sx={{ my: 1 }} />
                  <Typography variant="subtitle1" fontWeight="bold" color="primary" gutterBottom>
                    Tujuan Pemeriksaan
                  </Typography>
                </Grid>

                <Grid item xs={12} sm={6}>
                  <FormControl fullWidth required>
                    <InputLabel>Layanan Tujuan</InputLabel>
                    <Select
                      value={idPoli}
                      label="Layanan Tujuan"
                      onChange={(e) => setIdPoli(e.target.value)}
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
                  <FormControl fullWidth required disabled={!idPoli}>
                    <InputLabel>Terapis / Bidan</InputLabel>
                    <Select
                      value={idTerapis}
                      label="Terapis / Bidan"
                      onChange={(e) => setIdTerapis(e.target.value)}
                    >
                      {terapisList.map((d) => (
                        <MenuItem key={d.id_terapis} value={d.id_terapis}>
                          {d.nama_terapis}
                        </MenuItem>
                      ))}
                    </Select>
                  </FormControl>
                </Grid>

                <Grid item xs={12}>
                  <TextField
                    label="Keluhan Utama Pasien"
                    fullWidth
                    required
                    multiline
                    rows={3}
                    placeholder="Contoh: Batuk berdahak sejak 3 hari yang lalu disertai demam tinggi..."
                    value={keluhan}
                    onChange={(e) => setKeluhan(e.target.value)}
                  />
                </Grid>
              </Grid>

              {errorMsg && (
                <Box mt={2} p={1.5} bgcolor="error.light" color="error.contrastText" borderRadius="8px">
                  <Typography variant="body2">{errorMsg}</Typography>
                </Box>
              )}

              {successMsg && (
                <Box mt={2} p={1.5} bgcolor="success.light" color="success.contrastText" borderRadius="8px">
                  <Typography variant="body2">{successMsg}</Typography>
                </Box>
              )}

              <Box mt={3} display="flex" justifyContent="flex-end">
                <Button
                  type="submit"
                  variant="contained"
                  color="primary"
                  size="large"
                  disabled={loading}
                  startIcon={loading ? <CircularProgress size={20} color="inherit" /> : <Icon icon="ic:round-send" />}
                  sx={{ borderRadius: '8px', px: 4 }}
                >
                  {loading ? 'Mendaftarkan...' : 'Ambil Antrean (FIFO)'}
                </Button>
              </Box>
            </form>
          </Paper>
        </Grid>
      </Grid>

      {/* Ticket Result Dialog */}
      <Dialog open={openTicket} onClose={() => setOpenTicket(false)} maxWidth="xs" fullWidth>
        <DialogContent id="print-area">
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
                KLINIK Aurelia
              </Typography>
              <Typography variant="caption" display="block" color="text.secondary" gutterBottom>
                Jl. Sehat Walafiat No. 1, Kota Sehat
              </Typography>
              <Divider sx={{ my: 1.5 }} />

              <Typography variant="subtitle2" color="text.secondary">
                NOMOR ANTREAN ANDA
              </Typography>
              <Typography variant="h2" fontWeight="800" color="primary.main" my={1}>
                {ticketData.nomor_antrian}
              </Typography>
              <Typography variant="body2" fontWeight="bold" gutterBottom>
                {ticketData.nama_poli}
              </Typography>
              <Typography variant="caption" display="block" color="text.secondary" paragraph>
                Terapis/Bidan: {ticketData.nama_terapis}
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
                  <Typography variant="caption" color="text.secondary">Estimasi Tunggu</Typography>
                </Grid>
                <Grid item xs={7}>
                  <Typography variant="body2" fontWeight="bold" color="success.main">
                    {ticketData.estimasi_tunggu_menit > 0 ? `~${ticketData.estimasi_tunggu_menit} Menit` : 'Langsung Dilayani'}
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
                Simpan tiket ini untuk verifikasi saat dipanggil.
              </Typography>
              <Typography variant="caption" fontWeight="bold" display="block" color="primary" mt={1}>
                Metode Antrean FIFO (Keadilan Terjamin)
              </Typography>
            </Box>
          )}
        </DialogContent>
        <DialogActions sx={{ p: 2, justifyContent: 'space-between' }} className="no-print">
          <Button onClick={() => setOpenTicket(false)} color="inherit">
            Tutup
          </Button>
          <Button onClick={handlePrint} variant="contained" color="primary" startIcon={<Icon icon="ic:round-print" />}>
            Cetak Tiket
          </Button>
        </DialogActions>
      </Dialog>
    </Container>
  );
}
