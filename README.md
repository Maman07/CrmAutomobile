# CRM Automobile 🚗

Application de gestion de relation client (CRM) pour une concession automobile, construite avec **React** (frontend) et **Node.js + Express** (backend).

## Technologies utilisées

| Couche | Technologie |
|--------|-------------|
| Frontend | React, React Router |
| Backend | Node.js, Express |
| Tests | Jest (frontend), Node:test (backend) |

## Structure du projet

```
CrmAutomobile/
├── backend/          # Serveur API Node.js/Express
│   ├── server.js     # Point d'entrée du serveur
│   ├── data.js       # Données en mémoire
│   └── server.test.js
├── frontend/         # Application React
│   └── src/
│       ├── api/      # Client HTTP
│       ├── components/
│       └── pages/    # Dashboard, Véhicules, Clients, Ventes
└── README.md
```

## Démarrage rapide

### 1. Backend

```bash
cd backend
npm install
npm start          # http://localhost:5000
```

### 2. Frontend (dans un autre terminal)

```bash
cd frontend
npm install
npm start          # http://localhost:3000
```

## API Endpoints

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | /api/stats | Statistiques du tableau de bord |
| GET/POST | /api/vehicles | Liste / Créer un véhicule |
| GET/PUT/DELETE | /api/vehicles/:id | Détail / Modifier / Supprimer |
| GET/POST | /api/customers | Liste / Créer un client |
| GET/PUT/DELETE | /api/customers/:id | Détail / Modifier / Supprimer |
| GET/POST | /api/sales | Liste / Créer une vente |
| DELETE | /api/sales/:id | Supprimer une vente |

## Tests

```bash
# Backend
cd backend && npm test

# Frontend
cd frontend && npm test
```
