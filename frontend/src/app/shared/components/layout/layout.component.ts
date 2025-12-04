import { Component, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink, Router } from '@angular/router';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatListModule } from '@angular/material/list';
import { MatMenuModule } from '@angular/material/menu';
import { MatBadgeModule } from '@angular/material/badge';
import { MatDividerModule } from '@angular/material/divider';

import { AuthService } from '../../../core/services/auth.service';

interface MenuItem {
  label: string;
  icon: string;
  route: string;
  badge?: number;
}

@Component({
  selector: 'app-layout',
  standalone: true,
  imports: [
    CommonModule,
    RouterOutlet,
    RouterLink,
    MatToolbarModule,
    MatButtonModule,
    MatIconModule,
    MatSidenavModule,
    MatListModule,
    MatMenuModule,
    MatBadgeModule,
    MatDividerModule
  ],
  templateUrl: './layout.component.html',
  styleUrl: './layout.component.scss'
})
export class LayoutComponent {
  // User actuel
  currentUser = this.authService.currentUser;
  userRole = this.authService.userRole;

  // Menu selon rôle
  menuItems = computed(() => {
    const role = this.userRole();
    return this.getMenuByRole(role || '');
  });

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  /**
   * Menu selon rôle utilisateur
   */
  private getMenuByRole(role: string): MenuItem[] {
    const menus: Record<string, MenuItem[]> = {
      client: [
        { label: 'Tableau de bord', icon: 'dashboard', route: '/client/dashboard' },
        { label: 'Mes véhicules', icon: 'directions_car', route: '/client/vehicules' },
        { label: 'Mes tickets', icon: 'confirmation_number', route: '/client/tickets' },
        { label: 'Mes devis', icon: 'description', route: '/client/devis' },
        { label: 'Mes factures', icon: 'receipt', route: '/client/factures' },
        { label: 'Historique', icon: 'history', route: '/client/historique' }
      ],
      agent: [
        { label: 'Tableau de bord', icon: 'dashboard', route: '/agent/dashboard' },
        { label: 'Nouveaux clients', icon: 'person_add', route: '/agent/clients/en-attente', badge: 3 },
        { label: 'Tickets', icon: 'confirmation_number', route: '/agent/tickets' },
        { label: 'Planning', icon: 'calendar_today', route: '/agent/planning' }
      ],
      technicien: [
        { label: 'Tableau de bord', icon: 'dashboard', route: '/technicien/dashboard' },
        { label: 'Mes tickets', icon: 'build', route: '/technicien/tickets' },
        { label: 'Tickets débloqués', icon: 'lock_open', route: '/technicien/tickets/debloques', badge: 2 },
        { label: 'Historique', icon: 'history', route: '/technicien/historique' }
      ],
      manager: [
        { label: 'Tableau de bord', icon: 'dashboard', route: '/manager/dashboard' },
        { label: 'Paiements à vérifier', icon: 'payment', route: '/manager/paiements/en-attente', badge: 5 },
        { label: 'Personnel', icon: 'people', route: '/manager/personnel' },
        { label: 'Services', icon: 'build_circle', route: '/manager/services' },
        { label: 'Statistiques', icon: 'bar_chart', route: '/manager/statistiques' },
        { label: 'Paramètres', icon: 'settings', route: '/manager/parametres' }
      ]
    };

    return menus[role] || [];
  }

  /**
   * Déconnexion
   */
  logout(): void {
    this.authService.logout().subscribe({
      next: () => {
        this.router.navigate(['/auth/login']);
      },
      error: () => {
        // Déjà géré par AuthService
      }
    });
  }

  /**
   * Icône selon rôle
   */
  getRoleIcon(role: string): string {
    const icons: Record<string, string> = {
      client: 'person',
      agent: 'support_agent',
      technicien: 'engineering',
      manager: 'admin_panel_settings'
    };
    return icons[role] || 'person';
  }

  /**
   * Label français du rôle
   */
  getRoleLabel(role: string): string {
    const labels: Record<string, string> = {
      client: 'Client',
      agent: 'Agent d\'accueil',
      technicien: 'Technicien',
      manager: 'Manager'
    };
    return labels[role] || role;
  }
}

