import { Paiement } from './paiement.model';

export interface Facture {
  id: number;
  devis_id: number;
  ticket_intervention_id: number;
  numero: string;
  montant_ht: number;
  montant_tva: number;
  montant_ttc: number;
  montant_ttc_formate: string;
  statut: 'en_attente' | 'payee';
  date_emission: string;
  date_echeance?: string;
  date_paiement?: string;
  montant_paye: number;
  reste_a_payer: number;
  reste_a_payer_formate: string;
  est_payee: boolean;
  est_echue: boolean;
  cree_le: string;
  modifie_le: string;
  
  // Relations
  devis?: any;
  ticket?: any;
  paiements?: Paiement[];
}

