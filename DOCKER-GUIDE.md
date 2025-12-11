# 🐳 Guide Docker - AUTOTECH CRM

## 📋 Prérequis

- ✅ Docker Desktop installé
- ✅ Au moins 8 GB de RAM disponible
- ✅ 10 GB d'espace disque libre

## 🚀 Démarrage Rapide

### Windows (PowerShell)

```powershell
# Donner les permissions d'exécution (première fois uniquement)
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass

# Démarrer tous les services
docker-compose up -d
```

### Linux/Mac

```bash
# Rendre les scripts exécutables (première fois uniquement)
chmod +x start.sh stop.sh

# Démarrer tous les services
./start.sh
```

## 🌐 URLs d'Accès

Une fois démarrés, les services sont accessibles via :

| Service | URL | Description |
|---------|-----|-------------|
| **Frontend** | http://localhost:4200 | Application Angular |
| **Backend** | http://localhost:8000 | API Laravel |
| **Jenkins** | http://localhost:8080 | CI/CD Pipeline |
| **SonarQube** | http://localhost:9000 | Analyse de code |
| **PostgreSQL** | localhost:5432 | Base de données |

## 🔐 Identifiants par Défaut

### SonarQube
- **Username:** `admin`
- **Password:** `admin` (à changer au premier login)

### Jenkins
Récupérer le mot de passe initial :
```bash
docker logs autotech-jenkins
```
Cherchez la ligne contenant le mot de passe initial.

### PostgreSQL
- **Database:** `autotech_db`
- **Username:** `postgres`
- **Password:** `lilpas`

## 📊 Commandes Utiles

### Démarrage et Arrêt

```bash
# Démarrer tous les services
docker-compose up -d

# Arrêter tous les services
docker-compose down

# Redémarrer un service spécifique
docker-compose restart backend

# Arrêter et supprimer les volumes (⚠️ supprime les données)
docker-compose down -v
```

### Logs et Monitoring

```bash
# Voir tous les logs
docker-compose logs -f

# Logs d'un service spécifique
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f jenkins
docker-compose logs -f sonarqube

# État des conteneurs
docker-compose ps

# Ressources utilisées
docker stats
```

### Accès aux Conteneurs

```bash
# Accéder au backend
docker exec -it autotech-backend bash

# Accéder à PostgreSQL
docker exec -it autotech-postgres psql -U postgres -d autotech_db

# Accéder à Jenkins
docker exec -it autotech-jenkins bash
```

### Rebuild et Mise à Jour

```bash
# Reconstruire les images
docker-compose build --no-cache

# Reconstruire et redémarrer
docker-compose up -d --build

# Mettre à jour un service spécifique
docker-compose up -d --build backend
```

## 🔧 Configuration Jenkins

### 1. Premier Accès

1. Accéder à http://localhost:8080
2. Récupérer le mot de passe initial :
   ```bash
   docker logs autotech-jenkins
   ```
3. Installer les plugins suggérés
4. Créer un compte administrateur

### 2. Plugins Nécessaires

- Docker Pipeline
- SonarQube Scanner
- Git
- Pipeline
- Email Extension

### 3. Configurer SonarQube dans Jenkins

1. **Manage Jenkins** → **Configure System**
2. Ajouter un serveur SonarQube :
   - Name: `SonarQube`
   - Server URL: `http://sonarqube:9000`
   - Server authentication token: (générer dans SonarQube)

### 4. Créer un Pipeline

1. **New Item** → **Pipeline**
2. Dans **Pipeline** → **Definition** : choisir "Pipeline script from SCM"
3. SCM: Git
4. Repository URL: votre repo
5. Script Path: `Jenkinsfile`

## 📊 Configuration SonarQube

### 1. Premier Accès

1. Accéder à http://localhost:9000
2. Login: `admin` / `admin`
3. Changer le mot de passe

### 2. Créer les Projets

#### Backend
1. **Create Project** → **Manually**
2. Project key: `autotech-crm-backend`
3. Display name: `AUTOTECH CRM - Backend`
4. Générer un token

#### Frontend
1. **Create Project** → **Manually**
2. Project key: `autotech-crm-frontend`
3. Display name: `AUTOTECH CRM - Frontend`
4. Générer un token

### 3. Lancer une Analyse Manuelle

#### Backend
```bash
docker exec -it autotech-backend bash
cd /var/www/html
sonar-scanner
```

#### Frontend
```bash
docker exec -it autotech-frontend sh
cd /app
npm run sonar
```

## 📸 Captures d'Écran pour Documentation

### Docker

1. **Liste des conteneurs**
   ```bash
   docker-compose ps
   ```
   📸 Capturer la sortie

2. **Images Docker**
   ```bash
   docker images | grep autotech
   ```
   📸 Capturer la sortie

3. **Docker Desktop**
   📸 Capturer l'interface avec tous les conteneurs en cours d'exécution

### Jenkins

1. **Dashboard principal**
   📸 http://localhost:8080

2. **Pipeline en exécution**
   📸 Lancer un build et capturer les stages

3. **Console Output**
   📸 Logs d'un build réussi

4. **Blue Ocean** (si installé)
   📸 Vue graphique du pipeline

### SonarQube

1. **Dashboard des projets**
   📸 http://localhost:9000/projects

2. **Analyse Backend**
   📸 Détails du projet backend (bugs, vulnérabilités, code smells)

3. **Analyse Frontend**
   📸 Détails du projet frontend

4. **Quality Gate**
   📸 Statut du quality gate

5. **Métriques détaillées**
   📸 Coverage, duplications, complexity

## 🐛 Dépannage

### Les conteneurs ne démarrent pas

```bash
# Vérifier les logs
docker-compose logs

# Vérifier l'espace disque
docker system df

# Nettoyer Docker
docker system prune -a
```

### SonarQube ne démarre pas

SonarQube nécessite beaucoup de mémoire. Augmenter la RAM allouée à Docker (minimum 4 GB).

### Jenkins ne peut pas accéder à Docker

Vérifier que Docker Desktop est en cours d'exécution.

### Erreur de connexion à PostgreSQL

```bash
# Vérifier que PostgreSQL est démarré
docker-compose ps postgres

# Vérifier les logs
docker-compose logs postgres
```

## 🧹 Nettoyage

### Supprimer tous les conteneurs et volumes

```bash
docker-compose down -v
```

### Supprimer les images

```bash
docker rmi autotech-backend autotech-frontend
```

### Nettoyage complet de Docker

```bash
docker system prune -a --volumes
```

⚠️ **Attention:** Cela supprimera TOUTES les données !

## 📚 Ressources

- [Documentation Docker](https://docs.docker.com/)
- [Documentation Jenkins](https://www.jenkins.io/doc/)
- [Documentation SonarQube](https://docs.sonarqube.org/)
- [Documentation Laravel](https://laravel.com/docs)
- [Documentation Angular](https://angular.io/docs)


