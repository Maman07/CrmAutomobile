import { Injectable, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, tap, catchError, throwError } from 'rxjs';
import { jwtDecode } from 'jwt-decode';

import { environment } from '../../../environments/environment';
import { StorageService } from './storage.service';
import { User, AuthResponse, ApiResponse } from '../models';

interface JwtPayload {
  sub: number;
  email: string;
  role: string;
  exp: number;
  iat: number;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly API_URL = environment.apiUrl;
  private readonly TOKEN_KEY = 'access_token';
  private readonly USER_KEY = 'current_user';

  // Signals Angular 19
  private currentUserSignal = signal<User | null>(null);
  private isAuthenticatedSignal = signal<boolean>(false);

  // Computed signals
  currentUser = this.currentUserSignal.asReadonly();
  isAuthenticated = this.isAuthenticatedSignal.asReadonly();
  userRole = computed(() => this.currentUserSignal()?.role || null);
  isClient = computed(() => this.currentUserSignal()?.role === 'client');
  isAgent = computed(() => this.currentUserSignal()?.role === 'agent');
  isTechnicien = computed(() => this.currentUserSignal()?.role === 'technicien');
  isManager = computed(() => this.currentUserSignal()?.role === 'manager');

  constructor(
    private http: HttpClient,
    private router: Router,
    private storage: StorageService
  ) {
    this.loadUserFromStorage();
  }

  /**
   * Charger l'utilisateur depuis localStorage au démarrage
   */
  private loadUserFromStorage(): void {
    const token = this.storage.get<string>(this.TOKEN_KEY);
    const user = this.storage.get<User>(this.USER_KEY);

    if (token && user && !this.isTokenExpired(token)) {
      this.currentUserSignal.set(user);
      this.isAuthenticatedSignal.set(true);
    } else {
      this.logout();
    }
  }

  /**
   * Inscription client
   */
  register(data: {
    nom: string;
    prenom: string;
    email: string;
    telephone: string;
    password: string;
    password_confirmation: string;
    adresse?: string;
    ville?: string;
  }): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.API_URL}/auth/register`, data)
      .pipe(
        tap(response => {
          if (response.success) {
            this.handleAuthSuccess(response);
          }
        }),
        catchError(this.handleError)
      );
  }

  /**
   * Connexion
   */
  login(email: string, password: string): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.API_URL}/auth/login`, { email, password })
      .pipe(
        tap(response => {
          if (response.success) {
            this.handleAuthSuccess(response);
          }
        }),
        catchError(this.handleError)
      );
  }

  /**
   * Déconnexion
   */
  logout(): Observable<any> {
    return this.http.post(`${this.API_URL}/auth/logout`, {})
      .pipe(
        tap(() => this.clearAuthData()),
        catchError(() => {
          // Même si l'API échoue, on déconnecte localement
          this.clearAuthData();
          return throwError(() => new Error('Erreur déconnexion'));
        })
      );
  }

  /**
   * Récupérer les infos utilisateur depuis l'API
   */
  me(): Observable<ApiResponse<User>> {
    return this.http.get<ApiResponse<User>>(`${this.API_URL}/auth/me`)
      .pipe(
        tap(response => {
          if (response.success && response.data) {
            this.currentUserSignal.set(response.data);
            this.storage.set(this.USER_KEY, response.data);
          }
        }),
        catchError(this.handleError)
      );
  }

  /**
   * Rafraîchir le token
   */
  refreshToken(): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.API_URL}/auth/refresh`, {})
      .pipe(
        tap(response => {
          if (response.success) {
            this.storage.set(this.TOKEN_KEY, response.data.access_token);
          }
        }),
        catchError(this.handleError)
      );
  }

  /**
   * Récupérer le token JWT
   */
  getToken(): string | null {
    return this.storage.get<string>(this.TOKEN_KEY);
  }

  /**
   * Vérifier si le token est expiré
   */
  isTokenExpired(token: string): boolean {
    try {
      const decoded = jwtDecode<JwtPayload>(token);
      const now = Date.now() / 1000;
      return decoded.exp < now;
    } catch {
      return true;
    }
  }

  /**
   * Gérer le succès de l'authentification
   */
  private handleAuthSuccess(response: AuthResponse): void {
    const { user, access_token } = response.data;
    
    this.storage.set(this.TOKEN_KEY, access_token);
    this.storage.set(this.USER_KEY, user);
    this.currentUserSignal.set(user);
    this.isAuthenticatedSignal.set(true);

    // Redirection selon rôle
    this.redirectByRole(user.role);
  }

  /**
   * Redirection après login selon rôle
   */
  private redirectByRole(role: string): void {
    const routes: Record<string, string> = {
      client: '/client/dashboard',
      agent: '/agent/dashboard',
      technicien: '/technicien/dashboard',
      manager: '/manager/dashboard'
    };
    
    this.router.navigate([routes[role] || '/']);
  }

  /**
   * Nettoyer les données d'authentification
   */
  private clearAuthData(): void {
    this.storage.remove(this.TOKEN_KEY);
    this.storage.remove(this.USER_KEY);
    this.currentUserSignal.set(null);
    this.isAuthenticatedSignal.set(false);
    this.router.navigate(['/auth/login']);
  }

  /**
   * Gestion des erreurs
   */
  private handleError(error: any): Observable<never> {
    let errorMessage = 'Une erreur est survenue';
    
    if (error.error?.message) {
      errorMessage = error.error.message;
    } else if (error.status === 0) {
      errorMessage = 'Impossible de contacter le serveur';
    } else if (error.status === 401) {
      errorMessage = 'Email ou mot de passe incorrect';
    } else if (error.status === 422) {
      errorMessage = 'Données invalides';
    }

    return throwError(() => ({
      message: errorMessage,
      errors: error.error?.errors
    }));
  }
}

