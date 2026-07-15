import paths from 'routes/paths';

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
    path: '/',
    icon: 'ri:dashboard-fill',
    active: true,
  },
  {
    id: 'register-portal',
    subheader: 'Pendaftaran Online',
    path: '/register',
    icon: 'material-symbols:patient-list-outline',
  },
  {
    id: 'tv-monitor',
    subheader: 'Monitor TV Antrean',
    path: '/monitor',
    icon: 'material-symbols:tv-outline',
  },
  {
    id: 'authentication',
    subheader: 'Authentication',
    icon: 'ic:round-security',
    active: true,
    items: [
      {
        name: 'Sign In',
        pathName: 'signin',
        path: paths.signin,
      }
    ],
  }
];

export default sitemap;
