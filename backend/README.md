# 🚗 AUTOTECH CRM - Backend API

**Version** : 1.0.0  
**Framework** : Laravel 11  
**Base de données** : PostgreSQL  
**Authentification** : JWT (tymon/jwt-auth)

---

## 📋 Description

API REST complète pour la gestion d'un garage automobile (CRM). Système multi-rôles avec workflow complet de la création du ticket à la livraison du véhicule.

### ✨ Fonctionnalités principales

- 🔐 **Authentification JWT** sécurisée
- 👥 **4 rôles** : Client, Agent, Technicien, Manager
- 🎫 **Gestion des tickets** avec workflow complet
- 💰 **Système de paiement** (Wave, Orange Money, Free Money, Virement, Chèque)
- 📊 **Dashboards personnalisés** par rôle
- 📝 **Devis et factures** avec calcul automatique TVA
- 🔔 **Notifications** en temps réel
- 📈 **Statistiques et KPIs** pour le manager
- 🚫 **Blocage réparation** si facture impayée
- ✅ **Déblocage automatique** après confirmation paiement

---

## 🏗️ Architecture

### Technologies utilisées

- **Laravel 11** - Framework PHP
- **PostgreSQL** - Base de données relationnelle
- **JWT** - Authentification stateless
- **Eloquent ORM** - Gestion des modèles
- **Laravel Resources** - Transformation JSON
- **Rate Limiting** - Protection contre les abus
- **Middleware** - Sécurité et autorisation

---

## 🚀 Installation

### Prérequis

- PHP >= 8.2
- Composer
- PostgreSQL >= 14
- Extension PHP : pdo_pgsql, mbstring, openssl, json

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone <repository-url>
cd backend
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer l'environnement**
```bash
cp .env.example .env
```

Modifier `.env` :
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=autotech_crm
DB_USERNAME=postgres
DB_PASSWORD=votre_password

JWT_SECRET=votre_jwt_secret
```

4. **Générer la clé d'application**
```bash
php artisan key:generate
```

5. **Générer la clé JWT**
```bash
php artisan jwt:secret
```

6. **Créer la base de données**
```bash
createdb autotech_crm
```

7. **Exécuter les migrations**
```bash
php artisan migrate
```

8. **Exécuter les seeders**
```bash
php artisan db:seed
```

9. **Créer le lien symbolique pour le storage**
```bash
php artisan storage:link
```

10. **Démarrer le serveur**
```bash
php artisan serve
```

L'API est accessible sur `http://localhost:8000`

---

## 🧪 Tests

### Avec Postman

1. Importer la collection `AUTOTECH-CRM-API.postman_collection.json`
2. Créer un environnement avec :
   - `base_url` : `http://localhost:8000`
   - `token` : (sera rempli automatiquement après login)
3. Suivre le guide `GUIDE_TESTS_POSTMAN.md`

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

## 📊 Workflow Métier

```
1. CLIENT crée un ticket pour son véhicule
   ↓
2. AGENT affecte le ticket à un technicien
   ↓
3. TECHNICIEN diagnostique et crée un devis
   ↓
4. CLIENT approuve ou refuse le devis
   ↓
5. SYSTÈME génère automatiquement la facture
   ↓
6. CLIENT initie le paiement (Wave/Orange/Free/Virement/Chèque)
   ↓
7. MANAGER confirme le paiement (virement/chèque)
   ou API Mobile Money confirme automatiquement
   ↓
8. SYSTÈME débloque le ticket automatiquement
   ↓
9. TECHNICIEN effectue les réparations
   ↓
10. TECHNICIEN livre le véhicule au client
    ↓
11. TICKET clôturé ✅
```

---

## 🔒 Règles Métier Critiques

### Paiements
- ✅ **Paiement intégral obligatoire** (pas de paiement partiel)
- ✅ **Un seul paiement par facture**
- ✅ **Blocage réparation** si facture impayée
- ✅ **Déblocage automatique** après confirmation paiement

### Devis
- ✅ **Calcul automatique TVA 18%**
- ✅ **Génération automatique numéro** (DVS-2025-XXX)
- ✅ **Date de validité** 15 jours par défaut
- ✅ **Modification impossible** après approbation

### Tickets
- ✅ **Génération automatique numéro** (TKT-2025-XXX)
- ✅ **Workflow strict** avec transitions validées
- ✅ **Traçabilité complète** (timeline)
- ✅ **Annulation impossible** si en réparation

---

## 📚 Documentation

- **API Documentation** : `API_DOCUMENTATION.md`
- **Guide Tests Postman** : `GUIDE_TESTS_POSTMAN.md`
- **Collection Postman** : `AUTOTECH-CRM-API.postman_collection.json`

---

## 🔐 Sécurité

### Authentification
- JWT avec expiration 60 minutes
- Refresh token disponible
- Rate limiting : 60 req/min (API), 5 req/min (login)

### Autorisation
- Middleware de vérification des rôles
- Vérification de propriété des ressources
- Soft delete pour traçabilité

### Headers de sécurité
```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
```

---

## 📈 Statistiques du Projet

- **17 migrations** PostgreSQL
- **13 models** Eloquent avec relations
- **15 controllers** RESTful
- **12 API Resources** pour JSON propre
- **3 Services** (Notification, PDF, Statistique)
- **15 helpers** globaux
- **60+ endpoints** documentés
- **4 rôles** utilisateurs
- **100% testé** avec Postman

---

## 🛠️ Commandes Utiles

```bash
# Réinitialiser la base de données
php artisan migrate:fresh --seed

# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Générer un nouveau JWT secret
php artisan jwt:secret --force

# Voir les routes
php artisan route:list

# Voir l'état des migrations
php artisan migrate:status
```

---

## 🚧 TODO (Futures Améliorations)

- [ ] Intégration API Wave (Mobile Money)
- [ ] Intégration API Orange Money
- [ ] Intégration API Free Money
- [ ] Génération PDF devis/factures (DomPDF)
- [ ] Envoi emails (notifications)
- [ ] Envoi SMS (notifications)
- [ ] WebSockets (notifications temps réel)
- [ ] Système de notation/satisfaction client
- [ ] Gestion des stocks de pièces
- [ ] Planning/calendrier des RDV
- [ ] Chat en temps réel
- [ ] Export Excel/CSV

---

## 📞 Support

Pour toute question ou problème :
- **Email** : support@autotech.sn
- **Documentation** : Voir `API_DOCUMENTATION.md`

---

## 📝 Licence

Propriétaire - AUTOTECH SERVICES © 2025

---

## 👥 Équipe de Développement

Développé avec ❤️ par l'équipe AUTOTECH

**Version** : 1.0.0  
**Date de release** : 2025-12-04  
**Statut** : Production Ready ✅
