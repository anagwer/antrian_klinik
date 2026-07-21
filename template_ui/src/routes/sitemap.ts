// Sitemap configuration

export interface SubMenuItem {
  name: string;
  pathName: string;
  path: string;
  icon?: string;
  active?: boolean;
  items?: SubMenuItem[];
}

export interface MenuItem {
  id: string;
  subheader: string;
  path?: string;
  icon?: string;
  avatar?: string;
  active?: boolean;
  items?: SubMenuItem[];
}

const sitemap: MenuItem[] = [
  {
    id: 'dashboard',
    subheader: 'Dashboard',
    path: '/dashboard',
    icon: 'ri:dashboard-fill',
  },
  {
    id: 'register-portal',
    subheader: 'Pendaftaran Online',
    path: '/',
    icon: 'material-symbols:patient-list-outline',
  },
  {
    id: 'tv-monitor',
    subheader: 'Monitor TV Antrean',
    path: '/monitor',
    icon: 'material-symbols:tv-outline',
  },
  {
    id: 'layanan-crud',
    subheader: 'Data Layanan',
    path: '/dashboard/layanan',
    icon: 'material-symbols:medical-services',
  },
  {
    id: 'terapis-crud',
    subheader: 'Data Terapis / Bidan',
    path: '/dashboard/terapis',
    icon: 'material-symbols:medical-information',
  },
];

export default sitemap;
