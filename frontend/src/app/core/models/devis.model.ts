export interface Devis {
  id: number;
  ticket_intervention_id: number;
  technicien_id: number;
  numero: string;
  description?: string;
  montant_ht: number;
  montant_tva: number;
  montant_ttc: number;
  montant_ttc_formate: string;
  statut: 'en_attente' | 'approuve' | 'refuse';
  date_validite: string;
  date_approbation?: string;
  motif_refus?: string;
  est_expire: boolean;
  est_approuve: boolean;
  cree_le: string;
  modifie_le: string;
  
  // Relations
  ticket?: any;
  technicien?: any;
  lignes?: LigneDevis[];
  facture?: any;
}

export interface LigneDevis {
  id: number;
  devis_id: number;
  designation: string;
  type: 'main_oeuvre' | 'piece' | 'fourniture';
  quantite: number;
  prix_unitaire: number;
  montant: number;
  ordre: number;
  est_main_oeuvre: boolean;
}

