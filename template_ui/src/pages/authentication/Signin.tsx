import { useState, FormEvent } from 'react';
import Stack from '@mui/material/Stack';
import Button from '@mui/material/Button';
import Divider from '@mui/material/Divider';
import InputAdornment from '@mui/material/InputAdornment';
import Typography from '@mui/material/Typography';
import IconButton from '@mui/material/IconButton';
import TextField from '@mui/material/TextField';
import Box from '@mui/material/Box';
import IconifyIcon from 'components/base/IconifyIcon';
import { API_BASE } from 'api';

const Signin = () => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);
    setErrorMsg('');

    fetch(`${API_BASE}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ username, password })
    })
      .then((res) => {
        if (!res.ok) {
          throw new Error('Username atau password salah.');
        }
        return res.json();
      })
      .then((data) => {
        setLoading(false);
        if (data.status === 'success') {
          localStorage.setItem('user', JSON.stringify(data.user));
          // Redirect to home/dashboard
          window.location.hash = '#/';
        } else {
          setErrorMsg(data.message || 'Login gagal.');
        }
      })
      .catch((err) => {
        setLoading(false);
        setErrorMsg(err.message || 'Terjadi kesalahan koneksi server.');
      });
  };

  return (
    <>
      <Typography align="center" variant="h4" fontWeight="800" color="primary">
        Klinik Aurelia
      </Typography>
      <Typography mt={1.5} align="center" variant="body2" color="text.secondary">
        Selamat datang! Silakan login untuk mengelola antrean pasien.
      </Typography>

      <Divider sx={{ my: 4 }}>Sign In Admin / Petugas</Divider>

      <Stack component="form" mt={1} onSubmit={handleSubmit} direction="column" gap={2}>
        <TextField
          id="username"
          name="username"
          type="text"
          value={username}
          onChange={(e) => setUsername(e.target.value)}
          variant="filled"
          placeholder="Username"
          fullWidth
          autoFocus
          required
          InputProps={{
            startAdornment: (
              <InputAdornment position="start">
                <IconifyIcon icon="ic:round-person" />
              </InputAdornment>
            ),
          }}
        />
        <TextField
          id="password"
          name="password"
          type={showPassword ? 'text' : 'password'}
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          variant="filled"
          placeholder="Password"
          autoComplete="current-password"
          fullWidth
          required
          InputProps={{
            startAdornment: (
              <InputAdornment position="start">
                <IconifyIcon icon="ic:outline-lock" />
              </InputAdornment>
            ),
            endAdornment: (
              <InputAdornment
                position="end"
                sx={{
                  opacity: password ? 1 : 0,
                  pointerEvents: password ? 'auto' : 'none',
                }}
              >
                <IconButton
                  aria-label="toggle password visibility"
                  onClick={() => setShowPassword(!showPassword)}
                  sx={{ border: 'none', bgcolor: 'transparent !important' }}
                  edge="end"
                >
                  <IconifyIcon
                    icon={showPassword ? 'ic:outline-visibility' : 'ic:outline-visibility-off'}
                    color="neutral.light"
                  />
                </IconButton>
              </InputAdornment>
            ),
          }}
        />

        {errorMsg && (
          <Box p={1.5} bgcolor="error.light" color="error.contrastText" borderRadius="8px">
            <Typography variant="body2">{errorMsg}</Typography>
          </Box>
        )}

        <Button type="submit" variant="contained" size="medium" fullWidth disabled={loading}>
          {loading ? 'Logging in...' : 'Sign In'}
        </Button>
      </Stack>

      <Box mt={3} textAlign="center">
        <Typography variant="caption" color="text.secondary">
          Gunakan username: <strong>admin</strong> / password: <strong>admin123</strong>
        </Typography>
      </Box>
    </>
  );
};

export default Signin;
