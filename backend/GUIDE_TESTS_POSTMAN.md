# 📱 GUIDE COMPLET DES TESTS POSTMAN - AUTOTECH CRM API

## 🎯 OBJECTIF
Tester l'ensemble du workflow de l'API du CRM Automobile AUTOTECH SERVICES, de la création d'un compte client jusqu'à la clôture d'un ticket de réparation.

---

## 📋 PRÉREQUIS

### 1. **Serveur Laravel démarré**
```bash
cd backend
php artisan serve
# Doit tourner sur http://localhost:8000
```

### 2. **Base de données PostgreSQL**
- Base créée et configurée dans `.env`
- Migrations exécutées : `php artisan migrate`
- Seeders exécutés : `php artisan db:seed`

### 3. **Postman installé**
- Version Desktop ou Web
- Collection importée : `AUTOTECH-CRM-API.postman_collection.json`

### 4. **Variables d'environnement Postman**
Créer un environnement "AUTOTECH Local" avec :
- `base_url` = `http://localhost:8000`
- `token` = (vide, sera rempli auto après login)
- `vehicule_id` = (vide, sera rempli auto)
- `ticket_id` = (vide, sera rempli auto)
- `devis_id` = (vide, sera rempli auto)
- `facture_id` = (vide, sera rempli auto)
- `paiement_id` = (vide, sera rempli auto)

---

## 🧪 SCÉNARIO DE TEST COMPLET

### ✅ **PHASE 1 : AUTHENTIFICATION**

#### 1.1 - Login Client
```http
POST /api/auth/login
{
    "email": "client@autotech.sn",
    "password": "password123"
}
```
**Résultat attendu :**
- ✅ Status 200
- ✅ Token JWT retourné
- ✅ Variable `token` remplie automatiquement
- ✅ Informations utilisateur + profil client

**Comptes de test disponibles :**
| Rôle | Email | Password |
|------|-------|----------|
| Client | client@autotech.sn | password123 |
| Technicien | technicien@autotech.sn | password123 |
| Agent | agent@autotech.sn | password123 |
| Manager | manager@autotech.sn | password123 |

---

### ✅ **PHASE 2 : CONSULTATION PUBLIQUE**

#### 2.1 - Liste des services (sans auth)
```http
GET /api/services
```
**Résultat attendu :**
- ✅ Status 200
- ✅ 25 services retournés
- ✅ Catégories : mécanique, électricité, carrosserie, etc.

#### 2.2 - Services par catégorie
```http
GET /api/services/by-category
```
**Résultat attendu :**
- ✅ Services groupés par 7 catégories
- ✅ Format : `{ "mecanique_generale": [...], "electricite_automobile": [...] }`

---

### ✅ **PHASE 3 : CLIENT - DASHBOARD**

#### 3.1 - Dashboard client
```http
GET /api/client/dashboard
Authorization: Bearer {{token}}
```
**Résultat attendu :**
- ✅ Statistiques : nb véhicules, tickets, factures
- ✅ Derniers tickets (3)
- ✅ Derniers véhicules (3)
- ✅ Notifications non lues

---

### ✅ **PHASE 4 : CLIENT - CRÉATION VÉHICULE**

#### 4.1 - Créer un véhicule
```http
POST /api/client/vehicules
{
    "immatriculation": "DK-1234-AB",
    "marque": "Toyota",
    "modele": "Corolla",
    "annee": 2020,
    "couleur": "Blanc",
    "numero_serie": "VIN123456789",
    "type_carburant": "essence",
    "kilometrage": 45000
}
```
**Résultat attendu :**
- ✅ Status 201
- ✅ Véhicule créé avec ID
- ✅ Variable `vehicule_id` remplie automatiquement
- ✅ `libelle_complet` = "Toyota Corolla (2020)"

#### 4.2 - Liste mes véhicules
```http
GET /api/client/vehicules
```
**Résultat attendu :**
- ✅ Véhicule créé visible dans la liste

---

### ✅ **PHASE 5 : CLIENT - CRÉATION TICKET**

#### 5.1 - Créer un ticket d'intervention
```http
POST /api/client/tickets
{
    "vehicule_id": {{vehicule_id}},
    "description": "Problème de démarrage le matin. Le moteur tousse.",
    "kilometrage_actuel": 45500,
    "priorite": "moyenne",
    "services": [1, 2, 6]
}
```
**Résultat attendu :**
- ✅ Status 201
- ✅ Ticket créé avec numéro auto (ex: TKT-20241204-0001)
- ✅ Statut initial : `en_attente`
- ✅ Variable `ticket_id` remplie
- ✅ 3 services attachés

#### 5.2 - Suivi du ticket (Timeline)
```http
GET /api/client/tickets/{{ticket_id}}/suivi
```
**Résultat attendu :**
- ✅ Événement : "Ticket créé"
- ✅ Date de création
- ✅ Statut actuel : en_attente

---

### ✅ **PHASE 6 : AGENT - AFFECTATION**

#### 6.1 - Login Agent
```http
POST /api/auth/login
{
    "email": "agent@autotech.sn",
    "password": "password123"
}
```

#### 6.2 - Dashboard Agent
```http
GET /api/agent/dashboard
```
**Résultat attendu :**
- ✅ Tickets en attente d'affectation
- ✅ Liste des techniciens disponibles avec charge de travail

#### 6.3 - Affecter le ticket à un technicien
```http
POST /api/agent/tickets/{{ticket_id}}/affecter
{
    "technicien_id": 1
}
```
**Résultat attendu :**
- ✅ Status 200
- ✅ Ticket affecté
- ✅ Statut reste `en_attente` (le technicien doit le prendre en charge)

---

### ✅ **PHASE 7 : TECHNICIEN - DIAGNOSTIC**

#### 7.1 - Login Technicien
```http
POST /api/auth/login
{
    "email": "technicien@autotech.sn",
    "password": "password123"
}
```

#### 7.2 - Dashboard Technicien
```http
GET /api/technicien/dashboard
```
**Résultat attendu :**
- ✅ Tickets assignés
- ✅ Tickets en diagnostic
- ✅ Tickets bloqués (en attente paiement)

#### 7.3 - Passer le ticket en diagnostic
```http
PUT /api/technicien/tickets/{{ticket_id}}/statut
{
    "statut": "en_diagnostic",
    "commentaire": "Début du diagnostic"
}
```
**Résultat attendu :**
- ✅ Status 200
- ✅ Statut mis à jour : `en_diagnostic`

---

### ✅ **PHASE 8 : TECHNICIEN - CRÉATION DEVIS**

#### 8.1 - Créer un devis
```http
POST /api/technicien/tickets/{{ticket_id}}/devis
{
    "description": "Diagnostic complet effectué. Remplacement nécessaire.",
    "lignes": [
        {
            "designation": "Batterie 12V 70Ah",
            "type": "piece",
            "quantite": 1,
            "prix_unitaire": 45000
        },
        {
            "designation": "Filtre à air",
            "type": "piece",
            "quantite": 1,
            "prix_unitaire": 8000
        },
        {
            "designation": "Bougies d'allumage (x4)",
            "type": "piece",
            "quantite": 4,
            "prix_unitaire": 3500
        },
        {
            "designation": "Main d'œuvre diagnostic",
            "type": "main_oeuvre",
            "quantite": 2,
            "prix_unitaire": 15000
        },
        {
            "designation": "Main d'œuvre remplacement",
            "type": "main_oeuvre",
            "quantite": 3,
            "prix_unitaire": 12000
        }
    ]
}
```
**Résultat attendu :**
- ✅ Status 201
- ✅ Devis créé avec numéro auto (ex: DEV-20241204-0001)
- ✅ Montant HT calculé : 125 000 FCFA
- ✅ TVA 18% : 22 500 FCFA
- ✅ Montant TTC : 147 500 FCFA
- ✅ Variable `devis_id` remplie
- ✅ Statut ticket : `devis_envoye`

---

### ✅ **PHASE 9 : CLIENT - APPROBATION DEVIS**

#### 9.1 - Login Client
```http
POST /api/auth/login
{
    "email": "client@autotech.sn",
    "password": "password123"
}
```

#### 9.2 - Consulter le devis
```http
GET /api/client/devis/{{devis_id}}
```
**Résultat attendu :**
- ✅ Détail du devis avec toutes les lignes
- ✅ Montants HT, TVA, TTC
- ✅ Statut : `en_attente`

#### 9.3 - Approuver le devis
```http
POST /api/client/devis/{{devis_id}}/approuver
```
**Résultat attendu :**
- ✅ Status 200
- ✅ Devis approuvé
- ✅ **FACTURE GÉNÉRÉE AUTOMATIQUEMENT**
- ✅ Variable `facture_id` remplie
- ✅ Numéro facture auto (ex: FACT-20241204-0001)
- ✅ Statut ticket reste : `devis_envoye` (en attente paiement)

---

### ✅ **PHASE 10 : CLIENT - PAIEMENT**

#### 10.1 - Consulter la facture
```http
GET /api/client/factures/{{facture_id}}
```
**Résultat attendu :**
- ✅ Montant TTC : 147 500 FCFA
- ✅ Statut : `impayee`
- ✅ `reste_a_payer` : 147 500 FCFA

#### 10.2 - Initier paiement Wave
```http
POST /api/client/paiements
{
    "facture_id": {{facture_id}},
    "type_paiement_id": 1,
    "montant": 147500,
    "telephone": "+221771234567"
}
```
**Résultat attendu :**
- ✅ Status 201
- ✅ Paiement créé avec statut `en_attente`
- ✅ Variable `paiement_id` remplie
- ✅ Message : "Vous recevrez une notification pour confirmer"

**⚠️ RÈGLE MÉTIER TESTÉE :**
- ❌ Paiement partiel refusé (montant < montant_ttc)
- ❌ Paiement multiple refusé (déjà un paiement en attente/confirmé)

#### 10.3 - Tester paiement partiel (DOIT ÉCHOUER)
```http
POST /api/client/paiements
{
    "facture_id": {{facture_id}},
    "type_paiement_id": 1,
    "montant": 50000,
    "telephone": "+221771234567"
}
```
**Résultat attendu :**
- ✅ Status 400
- ✅ Message : "Le paiement doit être intégral. Montant requis : 147 500 FCFA"

---

### ✅ **PHASE 11 : MANAGER - VALIDATION PAIEMENT**

#### 11.1 - Login Manager
```http
POST /api/auth/login
{
    "email": "manager@autotech.sn",
    "password": "password123"
}
```

#### 11.2 - Dashboard Manager
```http
GET /api/manager/dashboard
```
**Résultat attendu :**
- ✅ KPIs globaux (CA, tickets, clients)
- ✅ Alertes : paiements à vérifier

#### 11.3 - Paiements en attente
```http
GET /api/manager/paiements/en-attente
```
**Résultat attendu :**
- ✅ Liste des paiements Wave/Orange/Free/Virement en attente

#### 11.4 - Confirmer le paiement
```http
POST /api/manager/paiements/{{paiement_id}}/confirmer
{
    "reference_bancaire": "WAVE-2024-001234",
    "commentaire": "Paiement Wave vérifié et validé"
}
```
**Résultat attendu :**
- ✅ Status 200
- ✅ Paiement confirmé
- ✅ **FACTURE MARQUÉE PAYÉE AUTOMATIQUEMENT**
- ✅ **TICKET DÉBLOQUÉ : statut → `devis_approuve`**

---

### ✅ **PHASE 12 : TECHNICIEN - RÉPARATION**

#### 12.1 - Login Technicien
```http
POST /api/auth/login
{
    "email": "technicien@autotech.sn",
    "password": "password123"
}
```

#### 12.2 - Tenter de passer en réparation AVANT paiement (DOIT ÉCHOUER)
```http
PUT /api/technicien/tickets/{{ticket_id}}/statut
{
    "statut": "en_reparation",
    "commentaire": "Début réparations"
}
```
**Résultat attendu (si facture non payée) :**
- ✅ Status 400
- ✅ Message : "Impossible de démarrer la réparation : la facture n'est pas payée"

#### 12.3 - Passer en réparation APRÈS paiement confirmé
```http
PUT /api/technicien/tickets/{{ticket_id}}/statut
{
    "statut": "en_reparation",
    "commentaire": "Début des réparations"
}
```
**Résultat attendu (si facture payée) :**
- ✅ Status 200
- ✅ Statut mis à jour : `en_reparation`

#### 12.4 - Terminer la réparation
```http
PUT /api/technicien/tickets/{{ticket_id}}/statut
{
    "statut": "termine",
    "commentaire": "Réparations terminées avec succès"
}
```
**Résultat attendu :**
- ✅ Status 200
- ✅ Statut : `termine`

---

### ✅ **PHASE 13 : CLIENT - VÉRIFICATION FINALE**

#### 13.1 - Login Client
```http
POST /api/auth/login
{
    "email": "client@autotech.sn",
    "password": "password123"
}
```

#### 13.2 - Consulter le ticket terminé
```http
GET /api/client/tickets/{{ticket_id}}
```
**Résultat attendu :**
- ✅ Statut : `termine`
- ✅ Devis approuvé
- ✅ Facture payée

#### 13.3 - Timeline complète
```http
GET /api/client/tickets/{{ticket_id}}/suivi
```
**Résultat attendu :**
- ✅ 8+ événements :
  1. Ticket créé
  2. Affecté à technicien
  3. En diagnostic
  4. Devis envoyé
  5. Devis approuvé
  6. Facture générée
  7. Paiement confirmé
  8. En réparation
  9. Terminé

---

## 🎯 CHECKLIST DE VALIDATION

### ✅ **Authentification**
- [ ] Login Client OK
- [ ] Login Technicien OK
- [ ] Login Agent OK
- [ ] Login Manager OK
- [ ] Token JWT généré et sauvegardé
- [ ] Logout OK

### ✅ **Services Publics**
- [ ] Liste services (25 services)
- [ ] Services par catégorie (7 catégories)
- [ ] Détail service

### ✅ **Workflow Client**
- [ ] Dashboard client
- [ ] Créer véhicule
- [ ] Créer ticket
- [ ] Consulter devis
- [ ] Approuver devis → Facture générée
- [ ] Paiement intégral (partiel refusé ✅)
- [ ] Paiement multiple refusé ✅
- [ ] Timeline complète

### ✅ **Workflow Agent**
- [ ] Dashboard agent
- [ ] Affecter ticket à technicien
- [ ] Réaffecter ticket (avec motif)
- [ ] Activer client en attente

### ✅ **Workflow Technicien**
- [ ] Dashboard technicien
- [ ] Passer ticket en diagnostic
- [ ] Créer devis avec calculs automatiques
- [ ] Modifier devis (si en_attente)
- [ ] Bloquer réparation si facture impayée ✅
- [ ] Démarrer réparation après paiement
- [ ] Terminer réparation

### ✅ **Workflow Manager**
- [ ] Dashboard manager avec KPIs
- [ ] Liste paiements en attente
- [ ] Confirmer paiement → Facture payée + Ticket débloqué ✅
- [ ] Rejeter paiement (avec motif)
- [ ] Historique paiements

### ✅ **Règles Métier Validées**
- [ ] Paiement intégral obligatoire
- [ ] Un seul paiement par facture
- [ ] Blocage réparation si impayé
- [ ] Déblocage automatique après paiement
- [ ] Génération auto numéros (TKT, DEV, FACT)
- [ ] Calcul TVA 18%
- [ ] Soft delete véhicules (si pas de tickets actifs)

---

## 📊 RÉSULTATS ATTENDUS

### ✅ **Tous les tests passent**
- 60+ endpoints testés
- 0 erreur 500
- Workflow complet validé
- Règles métier respectées

### ✅ **Performance**
- Temps de réponse < 500ms
- Rate limiting actif (60 req/min)
- Login rate limiting (5 req/min)

### ✅ **Sécurité**
- JWT valide requis
- Middleware rôles fonctionnel
- Validation stricte des entrées
- Headers sécurisés (CORS, X-Frame-Options, etc.)

---

## 🚀 PROCHAINES ÉTAPES

1. ✅ **Tests Postman** - EN COURS
2. 🔗 **Webhooks Mobile Money** - À implémenter
3. 📄 **Génération PDF** - À implémenter
4. 💻 **Frontend** - À développer

---

**Bon courage pour les tests ! 🎉**

