/* eslint-disable @typescript-eslint/no-explicit-any */
import { useState, useEffect } from 'react';
import {
  Menu,
  MenuItem,
  Badge,
  IconButton,
  Box,
  Typography,
  Divider,
  Stack,
  Button,
} from '@mui/material';
import IconifyIcon from 'components/base/IconifyIcon';
import { API_BASE } from 'api';

export default function NotificationMenu() {
  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
  const open = Boolean(anchorEl);
  const [notifications, setNotifications] = useState<any[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [viewedCount, setViewedCount] = useState(0); // Track viewed notifications count

  const fetchNotifications = () => {
    fetch(`${API_BASE}/notifications`)
      .then((res) => res.json())
      .then((data) => {
        if (data.status === 'success') {
          setNotifications(data.data);
          // Calculate unread items based on backend 'unread' status and what the user has viewed
          // By default, if the menu has not been opened since new notifications arrived, they count as unread.
          // Let's count how many have registered after the user last closed/opened.
          // For simplicity, we compare total notifications count to viewedCount.
          const newItemsCount = data.data.filter((n: any) => n.unread).length;
          setUnreadCount(Math.max(0, newItemsCount - viewedCount));
        }
      })
      .catch((err) => console.error('Gagal mengambil notifikasi:', err));
  };

  useEffect(() => {
    fetchNotifications();
    // Poll every 10 seconds
    const interval = setInterval(fetchNotifications, 10000);
    return () => clearInterval(interval);
  }, [viewedCount]);

  const handleOpen = (event: React.MouseEvent<HTMLButtonElement>) => {
    setAnchorEl(event.currentTarget);
    // Mark as read/viewed
    setUnreadCount(0);
    setViewedCount(notifications.filter((n: any) => n.unread).length);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleMarkAllRead = () => {
    setUnreadCount(0);
    setViewedCount(notifications.filter((n: any) => n.unread).length);
    handleClose();
  };

  const getIcon = (type: string) => {
    switch (type) {
      case 'call':
        return 'ic:round-volume-up';
      case 'done':
        return 'ic:round-check-circle';
      case 'reg':
      default:
        return 'ic:round-person-add';
    }
  };

  const getIconColor = (type: string) => {
    switch (type) {
      case 'call':
        return 'primary.main';
      case 'done':
        return 'success.main';
      case 'reg':
      default:
        return 'secondary.main';
    }
  };

  return (
    <>
      <IconButton size="large" onClick={handleOpen}>
        <Badge badgeContent={unreadCount} color="error">
          <IconifyIcon icon="ic:outline-notifications-none" />
        </Badge>
      </IconButton>

      <Menu
        anchorEl={anchorEl}
        open={open}
        onClose={handleClose}
        onClick={handleClose}
        sx={{
          mt: 1.5,
          '& .MuiList-root': {
            p: 0,
            width: 320,
            maxHeight: 450,
          },
        }}
        transformOrigin={{ horizontal: 'right', vertical: 'top' }}
        anchorOrigin={{ horizontal: 'right', vertical: 'bottom' }}
      >
        <Box p={2} display="flex" justifyContent="space-between" alignItems="center">
          <Typography variant="subtitle1" fontWeight={600} color="text.primary">
            Notifikasi Klinik
          </Typography>
          {unreadCount > 0 && (
            <Button
              size="small"
              onClick={handleMarkAllRead}
              sx={{ textTransform: 'none', fontSize: '11px', p: 0 }}
            >
              Tandai semua dibaca
            </Button>
          )}
        </Box>
        <Divider sx={{ my: 0 }} />

        <Box sx={{ overflowY: 'auto', maxHeight: 350 }}>
          {notifications.length === 0 ? (
            <Box p={3} textAlign="center">
              <Typography variant="body2" color="text.secondary">
                Belum ada notifikasi hari ini.
              </Typography>
            </Box>
          ) : (
            notifications.map((item) => (
              <MenuItem
                key={item.id}
                onClick={handleClose}
                sx={{
                  py: 1.5,
                  px: 2.5,
                  borderBottom: '1px solid rgba(0,0,0,0.03)',
                  whiteSpace: 'normal',
                }}
              >
                <Stack direction="row" spacing={2} alignItems="flex-start" width={1}>
                  <Box
                    sx={{
                      bgcolor: 'grey.100',
                      p: 1,
                      borderRadius: '50%',
                      display: 'flex',
                      color: getIconColor(item.type),
                    }}
                  >
                    <IconifyIcon icon={getIcon(item.type)} width={20} />
                  </Box>
                  <Stack direction="column" flexGrow={1} spacing={0.25}>
                    <Typography variant="body2" fontWeight={600} color="text.primary">
                      {item.title}
                    </Typography>
                    <Typography variant="caption" color="text.secondary">
                      {item.message}
                    </Typography>
                    <Typography variant="caption" color="grey.400" fontSize="10px">
                      Pukul {item.time}
                    </Typography>
                  </Stack>
                </Stack>
              </MenuItem>
            ))
          )}
        </Box>
      </Menu>
    </>
  );
}
