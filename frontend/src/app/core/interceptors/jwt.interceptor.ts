import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { StorageService } from '../services/storage.service';

/**
 * Interceptor JWT (Functional style Angular 19)
 * Ajoute automatiquement le token Bearer dans les headers
 */
export const jwtInterceptor: HttpInterceptorFn = (req, next) => {
  const storage = inject(StorageService);
  const token = storage.get<string>('access_token');

  // Si pas de token OU si c'est une requête auth (login/register), on passe sans modifier
  if (!token || req.url.includes('/auth/login') || req.url.includes('/auth/register')) {
    return next(req);
  }

  // Cloner la requête et ajouter le header Authorization
  const clonedRequest = req.clone({
    setHeaders: {
      Authorization: `Bearer ${token}`
    }
  });

  return next(clonedRequest);
};

