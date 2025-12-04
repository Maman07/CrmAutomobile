# 📱 POSTMAN - Configuration et Import

## 🎯 FICHIERS DISPONIBLES

1. **`AUTOTECH-CRM-API.postman_collection.json`** - Collection complète (60+ requêtes)
2. **`GUIDE_TESTS_POSTMAN.md`** - Guide détaillé des tests
3. **`README_POSTMAN.md`** - Ce fichier (instructions d'import)

---

## 📥 IMPORT DE LA COLLECTION

### Méthode 1 : Import direct

1. Ouvrir Postman Desktop ou Web
2. Cliquer sur **"Import"** (en haut à gauche)
3. Glisser-déposer le fichier `AUTOTECH-CRM-API.postman_collection.json`
4. Cliquer sur **"Import"**

### Méthode 2 : Import depuis fichier

1. Postman → **File** → **Import**
2. Sélectionner **"Upload Files"**
3. Choisir `AUTOTECH-CRM-API.postman_collection.json`
4. Confirmer l'import

---

## ⚙️ CONFIGURATION DE L'ENVIRONNEMENT

### 1. Créer un environnement

1. Cliquer sur **"Environments"** (icône œil en haut à droite)
2. Cliquer sur **"+"** pour créer un nouvel environnement
3. Nommer : **"AUTOTECH Local"**

### 2. Ajouter les variables

| Variable | Initial Value | Current Value |
|----------|---------------|---------------|
| `base_url` | `http://localhost:8000` | `http://localhost:8000` |
| `token` | (vide) | (vide) |
| `user_id` | (vide) | (vide) |
| `client_id` | (vide) | (vide) |
| `vehicule_id` | (vide) | (vide) |
| `ticket_id` | (vide) | (vide) |
| `devis_id` | (vide) | (vide) |
| `facture_id` | (vide) | (vide) |
| `paiement_id` | (vide) | (vide) |

### 3. Activer l'environnement

1. Sélectionner **"AUTOTECH Local"** dans le dropdown en haut à droite
2. L'environnement est maintenant actif ✅

---

## 🚀 DÉMARRAGE RAPIDE

### 1. Démarrer le serveur Laravel

```bash
cd backend
php artisan serve
```

Le serveur doit tourner sur `http://localhost:8000`

### 2. Premier test : Login Client

1. Ouvrir la collection **"AUTOTECH CRM API"**
2. Aller dans **"🔐 Authentication"** → **"Login Client"**
3. Cliquer sur **"Send"**

**Résultat attendu :**
```json
{
    "success": true,
    "message": "Connexion réussie",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "bearer",
        "expires_in": 3600,
        "user": {
            "id": 1,
            "nom": "Diallo",
            "prenom": "Amadou",
            "email": "client@autotech.sn",
            "role": "client"
        }
    }
}
```

✅ **Le token est automatiquement sauvegardé dans la variable `{{token}}`**

### 3. Test avec authentification

Toutes les requêtes suivantes utilisent automatiquement le token via :
```
Authorization: Bearer {{token}}
```

Exemple : **Dashboard Client**
1. Aller dans **"👤 CLIENT - Dashboard & Profile"** → **"Dashboard Client"**
2. Cliquer sur **"Send"**
3. Vous devriez voir les statistiques du client

---

## 📂 STRUCTURE DE LA COLLECTION

```
AUTOTECH CRM API
├── 🔐 Authentication (7 requêtes)
│   ├── Register Client
│   ├── Login Client
│   ├── Login Technicien
│   ├── Login Agent
│   ├── Login Manager
│   ├── Get User Info
│   └── Logout
│
├── 🔓 Public - Services (3 requêtes)
│   ├── Liste des services
│   ├── Services par catégorie
│   └── Détail service
│
├── 👤 CLIENT (25+ requêtes)
│   ├── Dashboard & Profile
│   ├── Véhicules (CRUD + historique)
│   ├── Tickets (CRUD + suivi)
│   ├── Devis (liste, détail, approuver, refuser)
│   ├── Factures (liste, détail)
│   └── Paiements (liste, initier Wave/Orange/Free/Virement)
│
├── 🔧 TECHNICIEN (10+ requêtes)
│   ├── Dashboard
│   ├── Tickets (liste, détail, changer statut)
│   └── Devis (créer, modifier)
│
├── 👔 AGENT (8+ requêtes)
│   ├── Dashboard
│   ├── Tickets (liste, affecter, réaffecter)
│   └── Clients (en attente, activer, rejeter)
│
├── 💼 MANAGER (7+ requêtes)
│   ├── Dashboard
│   └── Paiements (en attente, détail, confirmer, rejeter, historique)
│
└── 🔔 Notifications (4 requêtes)
    ├── Liste notifications
    ├── Nombre non lues
    ├── Marquer comme lue
    └── Marquer toutes comme lues
```

---

## 🔄 VARIABLES AUTOMATIQUES

Certaines requêtes remplissent automatiquement les variables pour faciliter les tests :

| Requête | Variable remplie |
|---------|------------------|
| Login | `token` |
| Créer véhicule | `vehicule_id` |
| Créer ticket | `ticket_id` |
| Créer devis | `devis_id` |
| Approuver devis | `facture_id` |
| Initier paiement | `paiement_id` |

**Exemple de script automatique :**
```javascript
// Dans l'onglet "Tests" de la requête "Login Client"
if (pm.response.code === 200) {
    const jsonData = pm.response.json();
    if (jsonData.data && jsonData.data.access_token) {
        pm.environment.set('token', jsonData.data.access_token);
    }
}
```

---

## 🧪 WORKFLOW DE TEST RECOMMANDÉ

### Scénario complet (30 min)

1. **Login Client** → Token sauvegardé
2. **Dashboard Client** → Voir stats
3. **Créer véhicule** → `vehicule_id` sauvegardé
4. **Créer ticket** → `ticket_id` sauvegardé
5. **Login Agent** → Nouveau token
6. **Affecter ticket** → Assigner à technicien
7. **Login Technicien** → Nouveau token
8. **Passer en diagnostic** → Changer statut
9. **Créer devis** → `devis_id` sauvegardé
10. **Login Client** → Reprendre token client
11. **Approuver devis** → `facture_id` sauvegardé
12. **Initier paiement Wave** → `paiement_id` sauvegardé
13. **Login Manager** → Token manager
14. **Confirmer paiement** → Déblocage automatique
15. **Login Technicien** → Reprendre token technicien
16. **Passer en réparation** → Maintenant autorisé ✅
17. **Terminer réparation** → Ticket clos
18. **Login Client** → Vérifier timeline complète

---

## 🎯 COMPTES DE TEST

| Rôle | Email | Password | Description |
|------|-------|----------|-------------|
| **Client** | client@autotech.sn | password123 | Client particulier |
| **Technicien** | technicien@autotech.sn | password123 | Mécanicien expert |
| **Agent** | agent@autotech.sn | password123 | Agent d'accueil |
| **Manager** | manager@autotech.sn | password123 | Directeur général |

---

## 🔧 DÉPANNAGE

### Problème : "Could not get response"

**Solution :**
```bash
# Vérifier que le serveur tourne
cd backend
php artisan serve
```

### Problème : "Unauthenticated"

**Solution :**
1. Refaire un login
2. Vérifier que la variable `{{token}}` est remplie
3. Vérifier que l'environnement est bien sélectionné

### Problème : "SQLSTATE[42P01]: Undefined table"

**Solution :**
```bash
# Exécuter les migrations
php artisan migrate

# Exécuter les seeders
php artisan db:seed
```

### Problème : "Rate limit exceeded"

**Solution :**
- Attendre 1 minute (limite : 60 req/min)
- Pour les tests, vous pouvez temporairement désactiver le rate limiting dans `routes/api.php`

---

## 📊 CODES DE RÉPONSE HTTP

| Code | Signification | Exemple |
|------|---------------|---------|
| **200** | Succès | Données retournées |
| **201** | Créé | Ressource créée avec succès |
| **400** | Erreur client | Validation échouée, règle métier violée |
| **401** | Non authentifié | Token manquant ou invalide |
| **403** | Interdit | Pas les droits pour cette action |
| **404** | Non trouvé | Ressource inexistante |
| **422** | Validation échouée | Champs requis manquants |
| **429** | Trop de requêtes | Rate limit dépassé |
| **500** | Erreur serveur | Bug backend (à corriger) |

---

## 📄 FORMAT DES RÉPONSES

### Succès
```json
{
    "success": true,
    "message": "Opération réussie",
    "data": {
        // Données
    }
}
```

### Erreur
```json
{
    "success": false,
    "message": "Description de l'erreur",
    "errors": {
        "field": ["Message d'erreur"]
    }
}
```

### Pagination
```json
{
    "success": true,
    "message": "Liste des ressources",
    "data": [...],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 73
    }
}
```

---

## 🎉 PRÊT À TESTER !

Vous avez maintenant tout ce qu'il faut pour tester l'API complète.

**Bon courage ! 🚀**

Pour un guide détaillé des tests, consultez : **`GUIDE_TESTS_POSTMAN.md`**

