/* eslint-disable @typescript-eslint/no-explicit-any */
import { useState, useEffect } from 'react';
import {
  Container,
  Grid,
  Paper,
  Typography,
  Box,
  Divider,
  Card,
  CardContent,
  IconButton
} from '@mui/material';
import { Icon } from '@iconify/react';

const API_BASE = window.location.origin + '/project/antrian/index.php/api';

export default function Monitor() {
  const [monitors, setMonitors] = useState<any[]>([]);
  const [activeCall, setActiveCall] = useState<any>(null);
  const [lastSpeechId, setLastSpeechId] = useState<Record<string, string>>({});
  const [isMuted, setIsMuted] = useState(false);
  const [timeString, setTimeString] = useState('');

  // Clock effect
  useEffect(() => {
    const timer = setInterval(() => {
      const d = new Date();
      setTimeString(
        d.toLocaleTimeString('id-ID', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
        }) + ' WIB'
      );
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  // Fetch active queue calls
  const fetchActiveCalls = () => {
    fetch(`${API_BASE}/queue/active_calls`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setMonitors(data.data);
          
          // Determine the most recently updated active call to show as the primary feature
          // Since the API returns them, let's check which one was called last.
          // In our database structure, we don't have update timestamp directly, but we can look for any active call that differs from our lastSpeechId map
          const spokenMap = { ...lastSpeechId };
          let newSpeechTriggered = false;

          data.data.forEach((item: any) => {
            const poliId = item.id_poli;
            const currentCall = item.active_call;

            if (currentCall && currentCall !== '---') {
              // If it's a new call that we haven't spoken yet
              if (spokenMap[poliId] !== currentCall) {
                spokenMap[poliId] = currentCall;
                newSpeechTriggered = true;
                
                // Trigger voice announcement
                if (!isMuted) {
                  announceQueue(currentCall, item.nama_poli);
                }

                // Set as the primary highlighted active call
                setActiveCall(item);
              }
            }
          });

          if (newSpeechTriggered) {
            setLastSpeechId(spokenMap);
          } else if (!activeCall && data.data.length > 0) {
            // Default highlight to the first active call
            const firstActive = data.data.find((item: any) => item.active_call !== '---');
            if (firstActive) {
              setActiveCall(firstActive);
            }
          }
        }
      })
      .catch((err) => console.error('Gagal mengambil antrean aktif:', err));
  };

  useEffect(() => {
    if ('speechSynthesis' in window) {
      window.speechSynthesis.getVoices();
    }
    fetchActiveCalls();
    // Poll every 4 seconds for real-time responsiveness
    const interval = setInterval(fetchActiveCalls, 4000);
    return () => clearInterval(interval);
  }, [lastSpeechId, isMuted, activeCall]);

  // Voice Announcement helper
  const announceQueue = (number: string, poliName: string) => {
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel(); // Cancel current speech to prevent overlapping

      // Clean up number for better TTS readout: e.g. A-01 -> "A nol satu"
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
      utterance.pitch = 1.25; // Raised pitch for female voice effect
      
      // Attempt to find Indonesian female voice
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

  const forceAnnounce = (item: any) => {
    if (item && item.active_call !== '---') {
      announceQueue(item.active_call, item.nama_poli);
    }
  };

  return (
    <Box
      sx={{
        minHeight: '100vh',
        bgcolor: '#0a192f',
        color: '#fff',
        display: 'flex',
        flexDirection: 'column',
        overflow: 'hidden'
      }}
    >
      {/* Header Bar */}
      <Paper
        elevation={4}
        sx={{
          bgcolor: '#172a45',
          borderRadius: 0,
          p: 2,
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          borderBottom: '3px solid #1976d2'
        }}
      >
        <Box display="flex" alignItems="center" gap={1.5}>
          <Icon icon="material-symbols:local-hospital" width={32} color="#1976d2" />
          <Box>
            <Typography variant="h5" fontWeight="bold" letterSpacing={0.5}>
              MONITOR ANTRIAN UTAMA
            </Typography>
            <Typography variant="caption" color="text.secondary">
              Klinik Anagwer - Mengutamakan Pelayanan Berkualitas
            </Typography>
          </Box>
        </Box>

        <Box display="flex" alignItems="center" gap={3}>
          <Typography variant="h5" fontWeight="bold" sx={{ fontFamily: 'monospace' }}>
            {timeString}
          </Typography>
          <IconButton onClick={() => setIsMuted(!isMuted)} sx={{ color: '#fff' }}>
            <Icon icon={isMuted ? 'ic:round-volume-off' : 'ic:round-volume-up'} width={28} />
          </IconButton>
        </Box>
      </Paper>

      {/* Main Panel */}
      <Container maxWidth="xl" sx={{ flexGrow: 1, py: 4, display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
        <Grid container spacing={4} alignItems="stretch">
          
          {/* Left Side: Highlight/Current Called Patient */}
          <Grid item xs={12} md={7} display="flex" flexDirection="column">
            <Paper
              elevation={6}
              sx={{
                flexGrow: 1,
                bgcolor: '#172a45',
                borderRadius: '16px',
                p: 4,
                textAlign: 'center',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'center',
                alignItems: 'center',
                border: '2px solid rgba(25, 118, 210, 0.3)',
                position: 'relative'
              }}
            >
              {activeCall ? (
                <>
                  <Typography variant="subtitle1" color="primary.light" fontWeight="bold" letterSpacing={2}>
                    PANGGILAN AKTIF
                  </Typography>
                  <Typography
                    variant="h1"
                    fontWeight="900"
                    sx={{
                      fontSize: { xs: '6rem', md: '10rem' },
                      color: '#00e676',
                      fontFamily: 'monospace',
                      textShadow: '0 0 20px rgba(0,230,118,0.3)',
                      my: 2
                    }}
                  >
                    {activeCall.active_call}
                  </Typography>
                  <Divider sx={{ width: '80%', bgcolor: 'rgba(255,255,255,0.1)', mb: 3 }} />
                  
                  <Typography variant="h3" fontWeight="bold" gutterBottom>
                    {activeCall.nama_poli}
                  </Typography>
                  <Typography variant="h5" color="text.secondary" gutterBottom>
                    Dokter: {activeCall.doctor_name}
                  </Typography>
                  <Typography variant="h6" color="primary.light" fontWeight="bold" sx={{ mt: 1 }}>
                    Pasien: {activeCall.patient_name}
                  </Typography>

                  <IconButton
                    onClick={() => forceAnnounce(activeCall)}
                    sx={{
                      position: 'absolute',
                      bottom: 16,
                      right: 16,
                      color: 'primary.light',
                      bgcolor: 'rgba(25,118,210,0.1)',
                      '&:hover': { bgcolor: 'rgba(25,118,210,0.2)' }
                    }}
                  >
                    <Icon icon="ic:round-volume-up" width={24} />
                  </IconButton>
                </>
              ) : (
                <Box>
                  <Icon icon="ic:round-queue" width={100} color="rgba(255,255,255,0.1)" />
                  <Typography variant="h5" color="text.secondary" mt={2}>
                    Belum ada antrean yang dipanggil saat ini.
                  </Typography>
                </Box>
              )}
            </Paper>
          </Grid>

          {/* Right Side: Grid of all Poliklinik boards */}
          <Grid item xs={12} md={5}>
            <Grid container spacing={3}>
              {monitors.map((item) => (
                <Grid item xs={12} sm={6} md={12} key={item.id_poli}>
                  <Card
                    sx={{
                      bgcolor: '#172a45',
                      color: '#fff',
                      borderRadius: '12px',
                      borderLeft: '6px solid #1976d2',
                      boxShadow: '0 4px 20px rgba(0,0,0,0.15)',
                      transition: 'transform 0.2s',
                      '&:hover': { transform: 'translateY(-2px)' }
                    }}
                  >
                    <CardContent sx={{ p: 2.5, '&:last-child': { pb: 2.5 } }}>
                      <Box display="flex" justifyContent="space-between" alignItems="center">
                        <Box>
                          <Typography variant="h6" fontWeight="bold">
                            {item.nama_poli}
                          </Typography>
                          <Typography variant="caption" color="text.secondary" display="block">
                            Dokter: {item.doctor_name}
                          </Typography>
                        </Box>
                        
                        {/* Call Number Display */}
                        <Box
                          sx={{
                            bgcolor: '#0a192f',
                            px: 3,
                            py: 1.5,
                            borderRadius: '8px',
                            border: '1px solid rgba(255,255,255,0.05)',
                            textAlign: 'center'
                          }}
                        >
                          <Typography variant="caption" color="text.secondary" display="block" fontSize="9px">
                            SEDANG MELAYANI
                          </Typography>
                          <Typography
                            variant="h4"
                            fontWeight="bold"
                            color={item.active_call !== '---' ? '#00e676' : 'text.secondary'}
                            sx={{ fontFamily: 'monospace' }}
                          >
                            {item.active_call}
                          </Typography>
                        </Box>
                      </Box>

                      <Divider sx={{ my: 1.5, bgcolor: 'rgba(255,255,255,0.05)' }} />

                      <Box display="flex" justifyContent="space-between" alignItems="center">
                        <Typography variant="caption" color="text.secondary" display="flex" alignItems="center" gap={0.5}>
                          <Icon icon="ic:round-people" /> Antrean Menunggu: <strong>{item.waiting_count}</strong>
                        </Typography>
                        
                        {item.active_call !== '---' && (
                          <IconButton
                            size="small"
                            onClick={() => forceAnnounce(item)}
                            sx={{ color: 'primary.light', p: 0.5 }}
                          >
                            <Icon icon="ic:round-volume-up" width={18} />
                          </IconButton>
                        )}
                      </Box>
                    </CardContent>
                  </Card>
                </Grid>
              ))}
            </Grid>
          </Grid>
        </Grid>
      </Container>

      {/* Marquee Footer */}
      <Box
        sx={{
          bgcolor: '#1976d2',
          color: '#fff',
          py: 1.5,
          position: 'relative',
          overflow: 'hidden',
          whiteSpace: 'nowrap'
        }}
      >
        <style>{`
          @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
          }
          .marquee-content {
            display: inline-block;
            animation: marquee 25s linear infinite;
            font-size: 16px;
            font-weight: 500;
          }
        `}</style>
        <div className="marquee-content">
          Selamat datang di Klinik Anagwer. Harap melakukan verifikasi tiket di loket pendaftaran. Antrean diproses secara adil menggunakan Algoritma FIFO (First In First Out). Jaga kesehatan Anda dan tetap patuhi protokol kesehatan di dalam area klinik. Terima kasih.
        </div>
      </Box>
    </Box>
  );
}
