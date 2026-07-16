/* eslint-disable @typescript-eslint/no-explicit-any */
import { useState, useEffect } from 'react';
import Box from '@mui/material/Box';
import Menu from '@mui/material/Menu';
import Stack from '@mui/material/Stack';
import Avatar from '@mui/material/Avatar';
import MenuItem from '@mui/material/MenuItem';
import Typography from '@mui/material/Typography';
import ButtonBase from '@mui/material/ButtonBase';
import ListItemIcon from '@mui/material/ListItemIcon';
import IconifyIcon from 'components/base/IconifyIcon';
import ProfileImage from 'assets/images/profile.png';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import Alert from '@mui/material/Alert';
import FormControl from '@mui/material/FormControl';
import InputLabel from '@mui/material/InputLabel';
import Select from '@mui/material/Select';
import Paper from '@mui/material/Paper';
import Grid from '@mui/material/Grid';
import Chip from '@mui/material/Chip';

const API_BASE = window.location.origin + '/project/antrian/index.php/api';

interface MenuItems {
  id: number;
  title: string;
  icon: string;
}

const menuItems: MenuItems[] = [
  {
    id: 2,
    title: 'Pengaturan Akun & Suara',
    icon: 'ic:outline-manage-accounts',
  },
  {
    id: 6,
    title: 'Keluar (Logout)',
    icon: 'ic:baseline-logout',
  },
];

const ProfileMenu = () => {
  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
  const open = Boolean(anchorEl);
  const [user, setUser] = useState({ nama_lengkap: 'Petugas Klinik', role: 'Staff', username: '' });

  // Dialog and form states
  const [openProfileDialog, setOpenProfileDialog] = useState(false);
  const [editNama, setEditNama] = useState('');
  const [editUsername, setEditUsername] = useState('');
  const [editPassword, setEditPassword] = useState('');
  const [errorMsg, setErrorMsg] = useState('');
  const [successMsg, setSuccessMsg] = useState('');

  // Speech synthesis states
  const [availableVoices, setAvailableVoices] = useState<SpeechSynthesisVoice[]>([]);
  const [selectedVoiceName, setSelectedVoiceName] = useState('');

  useEffect(() => {
    if (!openProfileDialog) return;

    const updateVoices = () => {
      if ('speechSynthesis' in window) {
        const list = window.speechSynthesis.getVoices();
        // Sort Indonesian first, then alphabetical
        const sorted = [...list].sort((a, b) => {
          const aId = a.lang.toLowerCase().startsWith('id');
          const bId = b.lang.toLowerCase().startsWith('id');
          if (aId && !bId) return -1;
          if (!aId && bId) return 1;
          return a.name.localeCompare(b.name);
        });
        setAvailableVoices(sorted);

        const saved = localStorage.getItem('selectedVoiceName') || '';
        if (saved && list.some(v => v.name === saved)) {
          setSelectedVoiceName(saved);
        } else {
          const best = list.find(v => {
            const lang = v.lang.toLowerCase();
            const name = v.name.toLowerCase();
            return (lang.startsWith('id') || lang.includes('id')) && 
                   (name.includes('gadis') || name.includes('indonesia') || name.includes('female') || name.includes('google') || name.includes('susan') || name.includes('online') || name.includes('natural'));
          }) || list.find(v => v.lang.toLowerCase().startsWith('id'));

          if (best) {
            setSelectedVoiceName(best.name);
            localStorage.setItem('selectedVoiceName', best.name);
          }
        }
      }
    };

    updateVoices();
    if ('speechSynthesis' in window) {
      window.speechSynthesis.onvoiceschanged = updateVoices;
    }
  }, [openProfileDialog]);

  const handleTestVoice = () => {
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel();
      const utterance = new SpeechSynthesisUtterance("Nomor antrean A satu, silakan menuju Layanan Umum.");
      utterance.lang = 'id-ID';

      const voices = window.speechSynthesis.getVoices();
      const voice = voices.find(v => v.name === selectedVoiceName);
      if (voice) {
        utterance.voice = voice;
      }
      utterance.rate = 0.85;
      utterance.pitch = 1.25;
      window.speechSynthesis.speak(utterance);
    }
  };

  const handleVoiceChange = (name: string) => {
    setSelectedVoiceName(name);
    localStorage.setItem('selectedVoiceName', name);
  };

  useEffect(() => {
    const userStr = localStorage.getItem('user');
    if (userStr) {
      try {
        const parsed = JSON.parse(userStr);
        setUser(parsed);
      } catch (e) {
        console.error(e);
      }
    }
  }, []);

  const handleProfileClick = (event: React.MouseEvent<HTMLElement>) => {
    setAnchorEl(event.currentTarget);
  };

  const handleProfileMenuClose = () => {
    setAnchorEl(null);
  };

  const handleMenuItemClick = (id: number) => {
    handleProfileMenuClose();
    if (id === 6) {
      localStorage.removeItem('user');
      fetch(`${window.location.origin}/project/antrian/index.php/api/logout`)
        .finally(() => {
          window.location.hash = '#/auth/signin';
        });
    } else if (id === 1 || id === 2) {
      setEditNama(user.nama_lengkap);
      setEditUsername(user.username || '');
      setEditPassword('');
      setErrorMsg('');
      setSuccessMsg('');
      setOpenProfileDialog(true);
    }
  };

  const handleSaveProfile = () => {
    if (!editNama || !editUsername) {
      setErrorMsg('Nama lengkap dan username wajib diisi.');
      return;
    }
    if (editPassword && editPassword.length < 6) {
      setErrorMsg('Password minimal 6 karakter.');
      return;
    }

    fetch(`${API_BASE}/profile_update`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        nama_lengkap: editNama,
        username: editUsername,
        password: editPassword,
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setSuccessMsg('Profil berhasil diperbarui!');
          localStorage.setItem('user', JSON.stringify(data.user));
          setUser(data.user);
          setTimeout(() => setOpenProfileDialog(false), 1500);
        } else {
          setErrorMsg(data.message || 'Gagal memperbarui profil.');
        }
      })
      .catch((err) => {
        console.error(err);
        setErrorMsg('Gagal menghubungkan ke server.');
      });
  };

  return (
    <>
      <ButtonBase
        sx={{ ml: 1 }}
        onClick={handleProfileClick}
        aria-controls={open ? 'account-menu' : undefined}
        aria-expanded={open ? 'true' : undefined}
        aria-haspopup="true"
        disableRipple
      >
        <Avatar
          src={ProfileImage}
          sx={{
            height: 44,
            width: 44,
            bgcolor: 'primary.main',
          }}
        />
      </ButtonBase>

      <Menu
        anchorEl={anchorEl}
        id="account-menu"
        open={open}
        onClose={handleProfileMenuClose}
        onClick={handleProfileMenuClose}
        PaperProps={{
          sx: {
            borderRadius: '16px',
            boxShadow: '0 10px 40px rgba(0,0,0,0.12)',
            overflow: 'hidden',
            width: 280,
            mt: 1.5,
          }
        }}
        transformOrigin={{ horizontal: 'right', vertical: 'top' }}
        anchorOrigin={{ horizontal: 'right', vertical: 'bottom' }}
      >
        {/* Modern Premium Header */}
        <Box 
          sx={{
            background: 'linear-gradient(135deg, #1976d2 0%, #115293 100%)',
            pt: 4,
            pb: 3,
            px: 3,
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            color: '#fff'
          }}
        >
          <Avatar 
            src={ProfileImage} 
            sx={{ 
              height: 64, 
              width: 64, 
              border: '3px solid #fff', 
              boxShadow: '0 4px 12px rgba(0,0,0,0.15)' 
            }} 
          />
          <Typography variant="body1" fontWeight="bold" sx={{ mt: 1.5, textAlign: 'center' }}>
            {user.nama_lengkap}
          </Typography>
          <Chip 
            label={user.role.toUpperCase()} 
            size="small" 
            sx={{ 
              mt: 1, 
              color: '#fff', 
              fontWeight: 'bold', 
              fontSize: '10px', 
              bgcolor: 'rgba(255,255,255,0.18)',
              border: 'none'
            }} 
          />
        </Box>

        <Box p={1.5}>
          {menuItems.map((item) => {
            const isLogout = item.id === 6;
            return (
              <MenuItem 
                key={item.id} 
                onClick={() => handleMenuItemClick(item.id)} 
                sx={{ 
                  py: 1.25, 
                  px: 2, 
                  borderRadius: '10px',
                  mb: 0.5,
                  '&:last-child': { mb: 0 },
                  '&:hover': { 
                    bgcolor: isLogout ? 'rgba(211,47,47,0.06)' : 'rgba(25,118,210,0.06)',
                    '& .menu-icon': {
                      color: isLogout ? 'error.main' : 'primary.main'
                    },
                    '& .menu-text': {
                      color: isLogout ? 'error.main' : 'text.primary'
                    }
                  }
                }}
              >
                <ListItemIcon className="menu-icon" sx={{ mr: 1, color: isLogout ? 'error.light' : 'text.secondary', fontSize: '20px' }}>
                  <IconifyIcon icon={item.icon} />
                </ListItemIcon>
                <Typography className="menu-text" variant="body2" color={isLogout ? 'error.main' : 'text.secondary'} fontWeight={600}>
                  {item.title}
                </Typography>
              </MenuItem>
            );
          })}
        </Box>
      </Menu>

      {/* Profile / Account Settings Dialog */}
      <Dialog 
        open={openProfileDialog} 
        onClose={() => setOpenProfileDialog(false)} 
        fullWidth 
        maxWidth="sm"
        PaperProps={{
          sx: {
            borderRadius: '20px',
            boxShadow: '0 12px 40px rgba(0,0,0,0.12)'
          }
        }}
      >
        <DialogTitle sx={{ fontWeight: 'bold', fontSize: '1.3rem', p: 3, pb: 2, display: 'flex', alignItems: 'center', gap: 1.5 }}>
          <Box sx={{ bgcolor: 'rgba(25,118,210,0.08)', p: 1, borderRadius: '12px', display: 'flex' }}>
            <IconifyIcon icon="ic:outline-manage-accounts" style={{ fontSize: '24px', color: '#1976d2' }} />
          </Box>
          Pengaturan Akun & Suara Antrean
        </DialogTitle>
        <DialogContent dividers sx={{ p: 3, borderTop: '1px solid rgba(0,0,0,0.06)', borderBottom: '1px solid rgba(0,0,0,0.06)', bgcolor: '#f8fafc' }}>
          <Grid container spacing={3} pt={1}>
            {/* Left side: Avatar & Personal Info */}
            <Grid item xs={12} md={6}>
              <Paper elevation={0} sx={{ p: 2.5, border: '1px solid rgba(0,0,0,0.06)', borderRadius: '16px', height: '100%', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 2.5, bgcolor: '#ffffff' }}>
                <Typography variant="subtitle2" fontWeight="bold" color="primary" sx={{ alignSelf: 'flex-start' }}>
                  Data Diri & Akun
                </Typography>
                
                <Avatar 
                  src={ProfileImage} 
                  sx={{ width: 80, height: 80, border: '3px solid #1976d2', boxShadow: '0 4px 12px rgba(25,118,210,0.15)' }} 
                />
                
                <Stack spacing={2} sx={{ width: '100%' }}>
                  <TextField
                    label="Nama Lengkap"
                    fullWidth
                    required
                    value={editNama}
                    onChange={(e) => setEditNama(e.target.value)}
                    InputProps={{
                      startAdornment: (
                        <IconifyIcon icon="ic:outline-person" style={{ marginRight: '8px', color: 'gray' }} />
                      ),
                    }}
                    sx={{ '& .MuiOutlinedInput-root': { borderRadius: '10px' } }}
                  />
                  <TextField
                    label="Username"
                    fullWidth
                    required
                    value={editUsername}
                    onChange={(e) => setEditUsername(e.target.value)}
                    InputProps={{
                      startAdornment: (
                        <IconifyIcon icon="ic:outline-alternate-email" style={{ marginRight: '8px', color: 'gray' }} />
                      ),
                    }}
                    sx={{ '& .MuiOutlinedInput-root': { borderRadius: '10px' } }}
                  />
                  <TextField
                    label="Password Baru"
                    type="password"
                    fullWidth
                    value={editPassword}
                    onChange={(e) => setEditPassword(e.target.value)}
                    placeholder="Kosongkan jika tidak diubah"
                    helperText="Minimal 6 karakter"
                    InputProps={{
                      startAdornment: (
                        <IconifyIcon icon="ic:outline-lock" style={{ marginRight: '8px', color: 'gray' }} />
                      ),
                    }}
                    sx={{ '& .MuiOutlinedInput-root': { borderRadius: '10px' } }}
                  />
                </Stack>
              </Paper>
            </Grid>

            {/* Right side: Voice Configuration */}
            <Grid item xs={12} md={6}>
              <Paper elevation={0} sx={{ p: 2.5, border: '1px solid rgba(0,0,0,0.06)', borderRadius: '16px', height: '100%', display: 'flex', flexDirection: 'column', gap: 2.5, bgcolor: '#ffffff' }}>
                <Typography variant="subtitle2" fontWeight="bold" color="primary">
                  Pengaturan Suara Panggilan
                </Typography>
                
                <Typography variant="caption" color="text.secondary">
                  Pilih aksen suara cewek Indonesia untuk pengumuman nomor antrean di ruang tunggu.
                </Typography>

                <FormControl fullWidth size="small" sx={{ mt: 1 }}>
                  <InputLabel>Pilihan Suara Panggilan</InputLabel>
                  <Select
                    value={selectedVoiceName}
                    label="Pilihan Suara Panggilan"
                    onChange={(e: any) => handleVoiceChange(e.target.value)}
                    sx={{ borderRadius: '10px' }}
                  >
                    {availableVoices.length === 0 ? (
                      <MenuItem value="" disabled>
                        Tidak ada suara yang terdeteksi
                      </MenuItem>
                    ) : (
                      availableVoices.map((v) => (
                        <MenuItem key={v.name} value={v.name}>
                          {v.name} ({v.lang})
                        </MenuItem>
                      ))
                    )}
                  </Select>
                </FormControl>

                <Button
                  variant="outlined"
                  size="medium"
                  fullWidth
                  disabled={!selectedVoiceName}
                  startIcon={<IconifyIcon icon="ic:round-volume-up" />}
                  onClick={handleTestVoice}
                  sx={{ 
                    textTransform: 'none', 
                    borderRadius: '10px', 
                    py: 1, 
                    fontWeight: 'bold', 
                    borderColor: 'primary.main',
                    color: 'primary.main',
                    bgcolor: 'rgba(25,118,210,0.02)',
                    '&:hover': {
                      bgcolor: 'rgba(25,118,210,0.08)'
                    }
                  }}
                >
                  Uji Suara Antrean
                </Button>
                
                <Box mt="auto" pt={2} sx={{ display: 'flex', alignItems: 'center', gap: 1, color: 'text.secondary' }}>
                  <IconifyIcon icon="ic:round-info-outline" style={{ fontSize: '18px', color: '#1976d2' }} />
                  <Typography variant="caption">
                    Suara yang Anda pilih akan sinkron ke TV Monitor secara otomatis.
                  </Typography>
                </Box>
              </Paper>
            </Grid>
          </Grid>
        </DialogContent>
        
        {openProfileDialog && (successMsg || errorMsg) && (
          <Box px={3} pt={2}>
            {successMsg && <Alert severity="success" sx={{ borderRadius: '10px' }}>{successMsg}</Alert>}
            {errorMsg && <Alert severity="error" sx={{ borderRadius: '10px' }}>{errorMsg}</Alert>}
          </Box>
        )}

        <DialogActions sx={{ p: 3, gap: 1 }}>
          <Button 
            onClick={() => setOpenProfileDialog(false)} 
            sx={{ textTransform: 'none', borderRadius: '10px', fontWeight: 600, px: 3 }}
          >
            Batal
          </Button>
          <Button 
            variant="contained" 
            onClick={handleSaveProfile} 
            sx={{ textTransform: 'none', borderRadius: '10px', fontWeight: 600, px: 4 }}
          >
            Simpan Perubahan
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default ProfileMenu;
