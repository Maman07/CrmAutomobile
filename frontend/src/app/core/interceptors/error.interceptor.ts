import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { ToastrService } from 'ngx-toastr';
import { StorageService } from '../services/storage.service';

/**
 * Interceptor Error (Functional style Angular 19)
 * Gère les erreurs HTTP globalement
 */
export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  const router = inject(Router);
  const toastr = inject(ToastrService);
  const storage = inject(StorageService);

  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      let errorMessage = 'Une erreur est survenue';

      // Erreur réseau (pas de connexion au serveur)
      if (error.status === 0) {
        errorMessage = '❌ Impossible de contacter le serveur. Vérifiez votre connexion.';
        toastr.error(errorMessage, 'Erreur réseau');
      }
      
      // 401 Unauthorized (token expiré ou invalide)
      else if (error.status === 401) {
        errorMessage = '🔒 Session expirée. Veuillez vous reconnecter.';
        toastr.warning(errorMessage, 'Session expirée');
        
        // Nettoyer les données et rediriger vers login
        storage.clear();
        router.navigate(['/auth/login']);
      }
      
      // 403 Forbidden (pas les permissions)
      else if (error.status === 403) {
        errorMessage = '⛔ Accès refusé. Vous n\'avez pas les permissions nécessaires.';
        toastr.error(errorMessage, 'Accès refusé');
        router.navigate(['/']);
      }
      
      // 404 Not Found
      else if (error.status === 404) {
        errorMessage = '🔍 Ressource non trouvée.';
        toastr.error(errorMessage, 'Non trouvé');
      }
      
      // 422 Validation Error
      else if (error.status === 422) {
        errorMessage = '⚠️ Données invalides. Vérifiez les champs du formulaire.';
        
        // Si erreurs de validation détaillées, on les affiche
        if (error.error?.errors) {
          const errors = error.error.errors;
          Object.keys(errors).forEach(key => {
            errors[key].forEach((msg: string) => {
              toastr.error(msg, 'Erreur de validation');
            });
          });
        } else {
          toastr.error(errorMessage, 'Erreur de validation');
        }
      }
      
      // 429 Too Many Requests (rate limiting)
      else if (error.status === 429) {
        errorMessage = '⏱️ Trop de requêtes. Veuillez patienter un instant.';
        toastr.warning(errorMessage, 'Limite atteinte');
      }
      
      // 500 Internal Server Error
      else if (error.status === 500) {
        errorMessage = '💥 Erreur serveur. Veuillez réessayer plus tard.';
        toastr.error(errorMessage, 'Erreur serveur');
      }
      
      // Autres erreurs
      else {
        errorMessage = error.error?.message || 'Une erreur inattendue est survenue';
        toastr.error(errorMessage, `Erreur ${error.status}`);
      }

      // Propager l'erreur pour que les composants puissent la gérer aussi
      return throwError(() => error);
    })
  );
};

