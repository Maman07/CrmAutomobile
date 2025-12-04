import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

import { environment } from '../../../environments/environment';
import { ApiResponse, PaginatedResponse } from '../models';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private readonly API_URL = environment.apiUrl;

  constructor(private http: HttpClient) {}

  /**
   * GET request
   */
  get<T>(endpoint: string, params?: any): Observable<ApiResponse<T>> {
    const httpParams = this.buildParams(params);
    return this.http.get<ApiResponse<T>>(`${this.API_URL}/${endpoint}`, { params: httpParams });
  }

  /**
   * GET request paginée
   */
  getPaginated<T>(endpoint: string, params?: any): Observable<PaginatedResponse<T>> {
    const httpParams = this.buildParams(params);
    return this.http.get<PaginatedResponse<T>>(`${this.API_URL}/${endpoint}`, { params: httpParams });
  }

  /**
   * POST request
   */
  post<T>(endpoint: string, body: any): Observable<ApiResponse<T>> {
    return this.http.post<ApiResponse<T>>(`${this.API_URL}/${endpoint}`, body);
  }

  /**
   * POST avec FormData (upload fichiers)
   */
  postFormData<T>(endpoint: string, formData: FormData): Observable<ApiResponse<T>> {
    return this.http.post<ApiResponse<T>>(`${this.API_URL}/${endpoint}`, formData);
  }

  /**
   * PUT request
   */
  put<T>(endpoint: string, body: any): Observable<ApiResponse<T>> {
    return this.http.put<ApiResponse<T>>(`${this.API_URL}/${endpoint}`, body);
  }

  /**
   * PATCH request
   */
  patch<T>(endpoint: string, body: any): Observable<ApiResponse<T>> {
    return this.http.patch<ApiResponse<T>>(`${this.API_URL}/${endpoint}`, body);
  }

  /**
   * DELETE request
   */
  delete<T>(endpoint: string): Observable<ApiResponse<T>> {
    return this.http.delete<ApiResponse<T>>(`${this.API_URL}/${endpoint}`);
  }

  /**
   * Construire HttpParams depuis un objet
   */
  private buildParams(params?: any): HttpParams {
    let httpParams = new HttpParams();
    
    if (params) {
      Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined) {
          httpParams = httpParams.set(key, params[key].toString());
        }
      });
    }
    
    return httpParams;
  }

  /**
   * URL complète pour fichier uploadé
   */
  getFileUrl(path: string): string {
    return `${environment.storageUrl}/${path}`;
  }
}

