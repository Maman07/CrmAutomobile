export interface User {
  id: number;
  nom: string;
  prenom: string;
  nom_complet: string;
  email: string;
  telephone: string;
  role: 'client' | 'agent' | 'technicien' | 'manager';
  statut: 'actif' | 'inactif' | 'suspendu';
  email_verifie: boolean;
  telephone_verifie: boolean;
  derniere_connexion?: string;
  cree_le: string;
  modifie_le: string;
  
  // Relations (chargées conditionnellement)
  client?: Client;
  agent?: Agent;
  technicien?: Technicien;
  manager?: Manager;
}

export interface Client {
  id: number;
  user_id: number;
  adresse?: string;
  ville?: string;
  type_client: 'particulier' | 'entreprise';
  nom_entreprise?: string;
  est_entreprise: boolean;
  cree_le: string;
  
  user?: User;
}

export interface Agent {
  id: number;
  user_id: number;
  matricule: string;
  date_embauche: string;
  anciennete_annees: number;
  poste?: string;
  cree_le: string;
  
  user?: User;
}

export interface Technicien {
  id: number;
  user_id: number;
  matricule: string;
  date_embauche: string;
  anciennete_annees: number;
  specialite: string;
  niveau_experience: 'debutant' | 'confirme' | 'expert';
  certifications?: string[];
  est_expert: boolean;
  tickets_en_cours?: number;
  cree_le: string;
  
  user?: User;
}

export interface Manager {
  id: number;
  user_id: number;
  matricule: string;
  date_embauche: string;
  anciennete_annees: number;
  cree_le: string;
  
  user?: User;
}

