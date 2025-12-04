# 📚 DOCUMENTATION API - AUTOTECH CRM

## 🏢 Informations Générales

**Version** : 1.0.0  
**Base URL** : `http://localhost:8000/api`  
**Authentification** : JWT Bearer Token  
**Format** : JSON  
**Charset** : UTF-8

---

## 🔐 Authentification

### Login
```http
POST /auth/login
Content-Type: application/json

{
    "email": "user@autotech.sn",
    "password": "password123"
}
```

**Réponse (200 OK)** :
```json
{
    "success": true,
    "message": "Connexion réussie",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "bearer",
        "expires_in": 3600,
        "user": { ... }
    }
}
```

### Logout
```http
POST /auth/logout
Authorization: Bearer {token}
```

### Refresh Token
```http
POST /auth/refresh
Authorization: Bearer {token}
```

### Profil Utilisateur
```http
GET /auth/me
Authorization: Bearer {token}
```

---

## 👤 CLIENT - Endpoints

### Dashboard
```http
GET /client/dashboard
Authorization: Bearer {token}
```

**Réponse** : Statistiques personnelles (véhicules, tickets, factures, notifications)

---

### Véhicules

#### Lister mes véhicules
```http
GET /client/vehicules
Authorization: Bearer {token}
```

#### Créer un véhicule
```http
POST /client/vehicules
Authorization: Bearer {token}
Content-Type: application/json

{
    "immatriculation": "DK-1234-AB",
    "marque": "Toyota",
    "modele": "Corolla",
    "annee": 2020,
    "couleur": "Blanc",
    "numero_serie": "VIN123456789",
    "type_carburant": "essence",
    "dernier_kilometrage": 45000
}
```

**Validations** :
- `immatriculation` : requis, unique, format sénégalais
- `marque` : requis, max 100 caractères
- `modele` : requis, max 100 caractères
- `annee` : requis, entre 1900 et année en cours
- `type_carburant` : requis, enum (essence, diesel, hybride, electrique, gpl)

#### Voir détail véhicule
```http
GET /client/vehicules/{id}
Authorization: Bearer {token}
```

#### Modifier véhicule
```http
PUT /client/vehicules/{id}
Authorization: Bearer {token}
```

#### Supprimer véhicule
```http
DELETE /client/vehicules/{id}
Authorization: Bearer {token}
```

**Note** : Suppression impossible si ticket en cours

#### Historique véhicule
```http
GET /client/vehicules/{id}/historique
Authorization: Bearer {token}
```

---

### Tickets

#### Lister mes tickets
```http
GET /client/tickets?statut=en_attente&page=1
Authorization: Bearer {token}
```

**Filtres disponibles** :
- `statut` : en_attente, en_diagnostic, devis_envoye, devis_approuve, en_reparation, repare, livre, annule
- `priorite` : basse, normale, haute, urgente
- `page` : pagination (10 par page)

#### Créer un ticket
```http
POST /client/tickets
Authorization: Bearer {token}
Content-Type: application/json

{
    "vehicule_id": 1,
    "description": "Problème de démarrage le matin",
    "priorite": "normale",
    "kilometrage_entree": 45500,
    "services": [1, 2, 6]
}
```

**Validations** :
- `vehicule_id` : requis, doit appartenir au client
- `description` : requis, min 10 caractères
- `priorite` : requis, enum (basse, normale, haute, urgente)
- `services` : requis, array d'IDs de services existants

#### Voir détail ticket
```http
GET /client/tickets/{id}
Authorization: Bearer {token}
```

#### Modifier ticket
```http
PUT /client/tickets/{id}
Authorization: Bearer {token}
```

**Note** : Modification possible uniquement si statut = `en_attente`

#### Annuler ticket
```http
DELETE /client/tickets/{id}
Authorization: Bearer {token}
```

**Note** : Annulation possible uniquement si statut = `en_attente` ou `en_diagnostic`

#### Timeline du ticket
```http
GET /client/tickets/{id}/suivi
Authorization: Bearer {token}
```

**Réponse** : Historique complet avec toutes les étapes (création, affectation, devis, paiement, clôture)

---

### Devis

#### Lister mes devis
```http
GET /client/devis?statut=en_attente
Authorization: Bearer {token}
```

#### Voir détail devis
```http
GET /client/devis/{id}
Authorization: Bearer {token}
```

#### Approuver devis
```http
POST /client/devis/{id}/approuver
Authorization: Bearer {token}
```

**Action** : Génère automatiquement la facture

#### Refuser devis
```http
POST /client/devis/{id}/refuser
Authorization: Bearer {token}
Content-Type: application/json

{
    "motif_refus": "Prix trop élevé"
}
```

**Validation** : `motif_refus` requis, min 10 caractères

#### Télécharger PDF devis
```http
GET /client/devis/{id}/pdf
Authorization: Bearer {token}
```

---

### Factures

#### Lister mes factures
```http
GET /client/factures?statut=impayee
Authorization: Bearer {token}
```

**Filtres** :
- `statut` : impayee, payee, annulee

#### Voir détail facture
```http
GET /client/factures/{id}
Authorization: Bearer {token}
```

**Réponse inclut** :
- `montant_paye_total` : total des paiements confirmés
- `reste_a_payer` : montant restant
- `est_echue` : true/false selon date d'échéance

#### Télécharger PDF facture
```http
GET /client/factures/{id}/pdf
Authorization: Bearer {token}
```

---

### Paiements

#### Initier un paiement
```http
POST /client/paiements
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "facture_id": 1,
    "type_paiement_id": 1,
    "montant": 156940.00,
    "telephone": "+221771234567",
    "justificatif": [fichier] (si virement/chèque)
}
```

**Types de paiement** :
- 1 : Wave
- 2 : Orange Money
- 3 : Free Money
- 4 : Virement bancaire
- 5 : Chèque

**Validations** :
- `montant` : doit être égal au montant TTC (paiement intégral obligatoire)
- `telephone` : requis si mobile money, format +221XXXXXXXXX
- `justificatif` : requis si virement/chèque, PDF/JPG/PNG max 5 Mo

**Règles métier** :
- ✅ Paiement intégral uniquement (pas de paiement partiel)
- ✅ Un seul paiement par facture
- ✅ Mobile Money : notification push pour confirmation
- ✅ Virement/Chèque : vérification manuelle par manager (24-48h)

---

## 📋 SERVICES PUBLICS

### Lister les services
```http
GET /services?categorie=mecanique_generale&actif=1
```

**Filtres** :
- `categorie` : mecanique_generale, electricite_automobile, carrosserie_peinture, pneumatique, climatisation, diagnostic_electronique, entretien_courant
- `actif` : 1 (actifs uniquement)

### Voir détail service
```http
GET /services/{id}
```

### Services par catégorie
```http
GET /services/by-category
```

**Réponse** : Services groupés par catégorie

---

## 🔧 TECHNICIEN - Endpoints

### Dashboard
```http
GET /technicien/dashboard
Authorization: Bearer {token}
```

**Réponse** : Statistiques personnelles, tickets assignés, tickets prioritaires, paiements bloqués

---

### Tickets

#### Lister mes tickets assignés
```http
GET /technicien/tickets?statut=en_diagnostic
Authorization: Bearer {token}
```

#### Voir détail ticket
```http
GET /technicien/tickets/{id}
Authorization: Bearer {token}
```

#### Changer statut ticket
```http
PUT /technicien/tickets/{id}/statut
Authorization: Bearer {token}
Content-Type: application/json

{
    "statut": "en_reparation",
    "observation": "Début des réparations",
    "kilometrage_sortie": 45520
}
```

**Statuts autorisés** :
- `en_diagnostic` : diagnostic en cours
- `devis_envoye` : devis créé et envoyé
- `en_reparation` : réparation en cours (BLOQUÉ si facture impayée)
- `repare` : réparations terminées
- `livre` : véhicule livré au client (clôture automatique)

**Règle métier critique** :
- ⚠️ Passage à `en_reparation` BLOQUÉ si facture non payée
- ✅ Déblocage automatique après confirmation du paiement

---

### Devis

#### Créer un devis
```http
POST /technicien/devis
Authorization: Bearer {token}
Content-Type: application/json

{
    "ticket_intervention_id": 2,
    "description": "Diagnostic complet effectué",
    "date_validite": "2025-12-19",
    "lignes": [
        {
            "designation": "Batterie 12V 70Ah",
            "type": "piece",
            "quantite": 1,
            "prix_unitaire": 85000
        },
        {
            "designation": "Jeu de 4 bougies",
            "type": "piece",
            "quantite": 1,
            "prix_unitaire": 28000
        },
        {
            "designation": "Main d'œuvre diagnostic et installation",
            "type": "main_oeuvre",
            "quantite": 2,
            "prix_unitaire": 10000
        }
    ]
}
```

**Validations** :
- `ticket_intervention_id` : requis, doit être assigné au technicien
- `description` : requis, min 10 caractères
- `date_validite` : requis, date future
- `lignes` : requis, array avec au moins 1 ligne
- `type` : enum (piece, main_oeuvre, fourniture)

**Actions automatiques** :
- ✅ Génération automatique du numéro (DVS-2025-XXX)
- ✅ Calcul automatique TVA 18%
- ✅ Calcul montant TTC
- ✅ Mise à jour statut ticket → `devis_envoye`

#### Voir détail devis
```http
GET /technicien/devis/{id}
Authorization: Bearer {token}
```

#### Modifier devis
```http
PUT /technicien/devis/{id}
Authorization: Bearer {token}
```

**Note** : Modification possible uniquement si statut = `en_attente`

---

## 👔 AGENT - Endpoints

### Dashboard
```http
GET /agent/dashboard
Authorization: Bearer {token}
```

**Réponse** : Vue d'ensemble (tickets en attente, clients à activer, techniciens disponibles, RDV du jour)

---

### Tickets

#### Lister tous les tickets
```http
GET /agent/tickets?statut=en_attente&priorite=haute
Authorization: Bearer {token}
```

#### Voir détail ticket
```http
GET /agent/tickets/{id}
Authorization: Bearer {token}
```

#### Affecter ticket à technicien
```http
POST /agent/tickets/{id}/affecter
Authorization: Bearer {token}
Content-Type: application/json

{
    "technicien_id": 1,
    "date_rdv": "2025-12-05 09:00:00"
}
```

**Validations** :
- `technicien_id` : requis, technicien doit être actif
- `date_rdv` : optionnel, date future

**Vérifications automatiques** :
- ✅ Charge de travail du technicien (max 10 tickets actifs)
- ✅ Spécialité du technicien vs services demandés

#### Réaffecter ticket
```http
POST /agent/tickets/{id}/reaffecter
Authorization: Bearer {token}
Content-Type: application/json

{
    "technicien_id": 2,
    "motif": "Technicien précédent indisponible"
}
```

**Validations** :
- `motif` : requis, min 10 caractères

**Restrictions** :
- ❌ Impossible si ticket en réparation ou terminé

---

### Clients

#### Clients en attente d'activation
```http
GET /agent/clients/en-attente
Authorization: Bearer {token}
```

#### Activer un client
```http
POST /agent/clients/{id}/activer
Authorization: Bearer {token}
```

**Action** : Change le statut du client à `actif`

#### Rejeter un client
```http
POST /agent/clients/{id}/rejeter
Authorization: Bearer {token}
Content-Type: application/json

{
    "motif": "Informations incomplètes"
}
```

**Validation** : `motif` requis, min 10 caractères

---

## 📊 MANAGER - Endpoints

### Dashboard
```http
GET /manager/dashboard
Authorization: Bearer {token}
```

**Réponse** : KPIs globaux, évolution CA, tickets par statut, top clients, performance techniciens, alertes

**Statistiques incluses** :
- Nombre total clients, personnel, tickets, véhicules
- CA du mois, CA total
- Évolution tickets/CA sur 6 mois
- Répartition tickets par statut
- Top 5 clients (par CA)
- Performance techniciens (tickets traités, délai moyen)
- Alertes (paiements à vérifier, tickets bloqués, etc.)

---

### Paiements

#### Paiements en attente de validation
```http
GET /manager/paiements/en-attente
Authorization: Bearer {token}
```

**Réponse** : Liste des paiements virement/chèque à vérifier

#### Voir détail paiement
```http
GET /manager/paiements/{id}
Authorization: Bearer {token}
```

#### Voir justificatif
```http
GET /manager/paiements/{id}/justificatif
Authorization: Bearer {token}
```

**Réponse** : URL du fichier justificatif uploadé

#### Confirmer paiement
```http
POST /manager/paiements/{id}/confirmer
Authorization: Bearer {token}
Content-Type: application/json

{
    "reference_externe": "REF-BANK-123456",
    "commentaire": "Paiement vérifié et validé"
}
```

**Actions automatiques** :
- ✅ Statut paiement → `confirme`
- ✅ Facture marquée comme `payee`
- ✅ Ticket débloqué (statut → `devis_approuve`)
- ✅ Notification envoyée au client et technicien

#### Rejeter paiement
```http
POST /manager/paiements/{id}/rejeter
Authorization: Bearer {token}
Content-Type: application/json

{
    "motif": "Justificatif illisible"
}
```

**Validation** : `motif` requis, min 10 caractères

#### Historique des paiements
```http
GET /manager/paiements/historique?statut=confirme&type_paiement_id=1
Authorization: Bearer {token}
```

**Filtres** :
- `statut` : en_attente, confirme, echoue
- `type_paiement_id` : 1-5
- `date_debut` : format Y-m-d
- `date_fin` : format Y-m-d

---

## 🔔 NOTIFICATIONS

### Lister mes notifications
```http
GET /notifications?lu=0
Authorization: Bearer {token}
```

**Filtres** :
- `lu` : 0 (non lues), 1 (lues)
- `type` : info, success, warning, danger

### Marquer comme lue
```http
PUT /notifications/{id}/read
Authorization: Bearer {token}
```

### Marquer toutes comme lues
```http
PUT /notifications/read-all
Authorization: Bearer {token}
```

### Nombre de non lues
```http
GET /notifications/unread-count
Authorization: Bearer {token}
```

### Supprimer notification
```http
DELETE /notifications/{id}
Authorization: Bearer {token}
```

---

## 📋 CODES D'ERREUR

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 201 | Created | Ressource créée |
| 400 | Bad Request | Données invalides |
| 401 | Unauthorized | Non authentifié |
| 403 | Forbidden | Non autorisé (rôle insuffisant) |
| 404 | Not Found | Ressource introuvable |
| 422 | Unprocessable Entity | Erreur de validation |
| 429 | Too Many Requests | Rate limit dépassé |
| 500 | Internal Server Error | Erreur serveur |

---

## 🔒 SÉCURITÉ

### Rate Limiting
- **API générale** : 60 requêtes/minute
- **Login** : 5 tentatives/minute

### Headers de sécurité
```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
```

### Authentification
- **Type** : JWT Bearer Token
- **Durée de vie** : 60 minutes
- **Refresh** : Disponible via `/auth/refresh`

---

## 📊 FORMAT DES RÉPONSES

### Succès
```json
{
    "success": true,
    "message": "Message de succès",
    "data": { ... }
}
```

### Erreur
```json
{
    "success": false,
    "message": "Message d'erreur",
    "errors": { ... }
}
```

### Pagination
```json
{
    "success": true,
    "message": "Liste des ressources",
    "data": [ ... ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 10,
        "total": 47
    }
}
```

---

## 🧪 TESTS

### Collection Postman
Importez `AUTOTECH-CRM-API.postman_collection.json` pour tester tous les endpoints.

### Comptes de test
```
Client:
  Email: client@autotech.sn
  Password: password123

Agent:
  Email: agent@autotech.sn
  Password: password123

Technicien:
  Email: technicien@autotech.sn
  Password: password123

Manager:
  Email: manager@autotech.sn
  Password: password123
```

---

## 📞 SUPPORT

Pour toute question ou problème :
- **Email** : support@autotech.sn
- **Documentation** : Voir `GUIDE_TESTS_POSTMAN.md`
- **Version** : 1.0.0
- **Dernière mise à jour** : 2025-12-04

---

**Développé avec ❤️ par l'équipe AUTOTECH**

