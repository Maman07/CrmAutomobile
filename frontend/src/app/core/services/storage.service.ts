import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class StorageService {
  
  /**
   * Sauvegarder une valeur dans localStorage
   */
  set(key: string, value: any): void {
    try {
      const serialized = JSON.stringify(value);
      localStorage.setItem(key, serialized);
    } catch (error) {
      console.error('Erreur sauvegarde localStorage:', error);
    }
  }

  /**
   * Récupérer une valeur depuis localStorage
   */
  get<T>(key: string): T | null {
    try {
      const item = localStorage.getItem(key);
      if (!item) return null;
      return JSON.parse(item) as T;
    } catch (error) {
      console.error('Erreur lecture localStorage:', error);
      return null;
    }
  }

  /**
   * Supprimer une clé
   */
  remove(key: string): void {
    localStorage.removeItem(key);
  }

  /**
   * Vider tout le localStorage
   */
  clear(): void {
    localStorage.clear();
  }

  /**
   * Vérifier si une clé existe
   */
  has(key: string): boolean {
    return localStorage.getItem(key) !== null;
  }
}

