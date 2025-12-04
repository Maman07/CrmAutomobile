import { inject } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { ToastrService } from 'ngx-toastr';

/**
 * Guard de rôle (Functional style Angular 19)
 * Vérifie que l'utilisateur a le bon rôle
 */
export const roleGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);
  const toastr = inject(ToastrService);

  // Récupérer le(s) rôle(s) autorisé(s) depuis les data de la route
  const allowedRoles = route.data['roles'] as string[];
  const userRole = authService.userRole();

  // Vérifier si l'utilisateur a un des rôles autorisés
  if (userRole && allowedRoles.includes(userRole)) {
    return true;
  }

  // Si rôle non autorisé
  toastr.error('Vous n\'avez pas accès à cette page', 'Accès refusé');
  
  // Rediriger vers le dashboard approprié au rôle
  const dashboards: Record<string, string> = {
    client: '/client/dashboard',
    agent: '/agent/dashboard',
    technicien: '/technicien/dashboard',
    manager: '/manager/dashboard'
  };
  
  router.navigate([dashboards[userRole || ''] || '/']);
  
  return false;
};

