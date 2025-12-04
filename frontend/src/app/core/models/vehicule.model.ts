export interface Vehicule {
  id: number;
  client_id: number;
  immatriculation: string;
  marque: string;
  modele: string;
  annee: number;
  couleur?: string;
  numero_serie?: string;
  type_carburant: 'essence' | 'diesel' | 'hybride' | 'electrique';
  capacite_reservoir?: number;
  consommation_moyenne?: number;
  dernier_kilometrage?: number;
  date_kilometrage?: string;
  date_ajout: string;
  libelle_complet: string;
  cree_le: string;
}

