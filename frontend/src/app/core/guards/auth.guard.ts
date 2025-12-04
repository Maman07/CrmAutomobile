import { inject } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { ToastrService } from 'ngx-toastr';

/**
 * Guard d'authentification (Functional style Angular 19)
 * Vérifie que l'utilisateur est connecté
 */
export const authGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);
  const toastr = inject(ToastrService);

  // Vérifier si l'utilisateur est authentifié
  if (authService.isAuthenticated()) {
    return true;
  }

  // Si non authentifié, rediriger vers login
  toastr.warning('Veuillez vous connecter pour accéder à cette page', 'Authentification requise');
  router.navigate(['/auth/login'], {
    queryParams: { returnUrl: state.url }
  });
  
  return false;
};

