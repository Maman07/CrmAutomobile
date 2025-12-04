import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatTableModule } from '@angular/material/table';
import { MatChipsModule } from '@angular/material/chips';
import { MatTooltipModule } from '@angular/material/tooltip';
import { ToastrService } from 'ngx-toastr';

import { ApiService } from '../../../core/services/api.service';

interface DashboardStats {
  total_vehicules: number;
  total_tickets: number;
  tickets_en_cours: number;
  tickets_clotures: number;
  factures_en_attente: number;
  montant_factures_impayees: number;
}

@Component({
  selector: 'app-client-dashboard',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatTableModule,
    MatChipsModule,
    MatTooltipModule
  ],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.scss'
})
export class DashboardComponent implements OnInit {
  isLoading = signal(true);
  stats = signal<DashboardStats | null>(null);
  derniersTickets = signal<any[]>([]);
  vehicules = signal<any[]>([]);

  displayedColumns: string[] = ['numero', 'vehicule', 'statut', 'date', 'actions'];

  constructor(
    private apiService: ApiService,
    private toastr: ToastrService
  ) {}

  ngOnInit(): void {
    this.loadDashboard();
  }

  /**
   * Charger données dashboard
   */
  loadDashboard(): void {
    this.isLoading.set(true);

    this.apiService.get<any>('client/dashboard').subscribe({
      next: (response) => {
        if (response.success) {
          this.stats.set(response.data.stats);
          this.derniersTickets.set(response.data.derniers_tickets || []);
          this.vehicules.set(response.data.vehicules || []);
        }
        this.isLoading.set(false);
      },
      error: (error) => {
        this.isLoading.set(false);
        this.toastr.error('Impossible de charger le dashboard', 'Erreur');
      }
    });
  }

  /**
   * Classe CSS selon statut ticket
   */
  getStatutClass(statut: string): string {
    const classes: Record<string, string> = {
      'en_attente': 'badge-warning',
      'en_diagnostic': 'badge-info',
      'devis_envoye': 'badge-primary',
      'devis_approuve': 'badge-success',
      'en_reparation': 'badge-info',
      'repare': 'badge-success',
      'livre': 'badge-success',
      'cloture': 'badge-secondary',
      'annule': 'badge-danger'
    };
    return classes[statut] || 'badge-secondary';
  }

  /**
   * Label français du statut
   */
  getStatutLabel(statut: string): string {
    const labels: Record<string, string> = {
      'en_attente': 'En attente',
      'en_diagnostic': 'En diagnostic',
      'devis_envoye': 'Devis envoyé',
      'devis_approuve': 'Devis approuvé',
      'en_reparation': 'En réparation',
      'repare': 'Réparé',
      'livre': 'Livré',
      'cloture': 'Clôturé',
      'annule': 'Annulé'
    };
    return labels[statut] || statut;
  }
}

