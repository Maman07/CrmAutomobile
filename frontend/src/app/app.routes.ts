import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';
import { roleGuard } from './core/guards/role.guard';

export const routes: Routes = [
  // Redirect root vers login
  {
    path: '',
    redirectTo: '/auth/login',
    pathMatch: 'full'
  },

  // Routes Auth (publiques)
  {
    path: 'auth',
    children: [
      {
        path: 'login',
        loadComponent: () => import('./modules/auth/login/login.component').then(m => m.LoginComponent)
      },
      {
        path: 'register',
        loadComponent: () => import('./modules/auth/register/register.component').then(m => m.RegisterComponent)
      }
    ]
  },

  // Routes Client (protégées, rôle client uniquement)
  {
    path: 'client',
    canActivate: [authGuard, roleGuard],
    data: { roles: ['client'] },
    loadComponent: () => import('./shared/components/layout/layout.component').then(m => m.LayoutComponent),
    children: [
      {
        path: 'dashboard',
        loadComponent: () => import('./modules/client/dashboard/dashboard.component').then(m => m.DashboardComponent)
      },
      {
        path: '',
        redirectTo: 'dashboard',
        pathMatch: 'full'
      }
    ]
  },

  // 404 Not Found
  {
    path: '**',
    redirectTo: '/auth/login'
  }
];
