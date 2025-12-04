import { Vehicule } from './vehicule.model';
import { Service } from './service.model';
import { Devis } from './devis.model';
import { Facture } from './facture.model';

export interface Ticket {
  id: number;
  numero_ticket: string;
  description: string;
  statut: TicketStatut;
  priorite: TicketPriorite;
  kilometrage_entree?: number;
  kilometrage_sortie?: number;
  duree_estimee?: number;
  observation?: string;
  date_rdv?: string;
  date_affectation?: string;
  date_cloture?: string;
  est_cloture: boolean;
  cree_le: string;
  modifie_le: string;
  
  // Relations
  client?: any;
  vehicule?: Vehicule;
  technicien?: any;
  services?: Service[];
  devis?: Devis;
  facture?: Facture;
}

export type TicketStatut = 
  | 'en_attente'
  | 'en_diagnostic'
  | 'devis_envoye'
  | 'devis_approuve'
  | 'en_reparation'
  | 'repare'
  | 'livre'
  | 'cloture'
  | 'annule';

export type TicketPriorite = 
  | 'basse'
  | 'normale'
  | 'haute'
  | 'urgente';

