export interface Paiement {
  id: number;
  facture_id: number;
  type_paiement_id: number;
  montant: number;
  montant_formate: string;
  date_paiement: string;
  statut: 'en_attente' | 'confirme' | 'echoue';
  reference_externe?: string;
  justificatif?: string;
  justificatif_url?: string;
  est_confirme: boolean;
  cree_le: string;
  modifie_le: string;
  
  // Relations
  facture?: any;
  type_paiement?: TypePaiement;
}

export interface TypePaiement {
  id: number;
  libelle: string;
  description?: string;
  actif: boolean;
}

