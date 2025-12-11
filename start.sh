#!/bin/bash

echo "🚀 =========================================="
echo "🚀 DÉMARRAGE AUTOTECH CRM - ENVIRONNEMENT COMPLET"
echo "🚀 =========================================="
echo ""

# Vérifier si Docker est installé
if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé!"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose n'est pas installé!"
    exit 1
fi

echo "✅ Docker et Docker Compose sont installés"
echo ""

# Arrêter les conteneurs existants
echo "🛑 Arrêt des conteneurs existants..."
docker-compose down

# Nettoyer les volumes (optionnel - décommenter si nécessaire)
# echo "🧹 Nettoyage des volumes..."
# docker-compose down -v

# Construire les images
echo ""
echo "🏗️  Construction des images Docker..."
docker-compose build --no-cache

# Démarrer tous les services
echo ""
echo "🚀 Démarrage de tous les services..."
docker-compose up -d

# Attendre que les services soient prêts
echo ""
echo "⏳ Attente du démarrage des services..."
sleep 15

# Vérifier l'état des services
echo ""
echo "📊 État des services:"
docker-compose ps

echo ""
echo "✅ =========================================="
echo "✅ TOUS LES SERVICES SONT DÉMARRÉS!"
echo "✅ =========================================="
echo ""
echo "🌐 URLs d'accès:"
echo "   - Frontend Angular:  http://localhost:4200"
echo "   - Backend Laravel:   http://localhost:8000"
echo "   - Jenkins:           http://localhost:8080"
echo "   - SonarQube:         http://localhost:9000"
echo "   - PostgreSQL:        localhost:5432"
echo ""
echo "📝 Identifiants par défaut:"
echo "   Jenkins:   Voir les logs avec: docker logs autotech-jenkins"
echo "   SonarQube: admin / admin (à changer au premier login)"
echo ""
echo "📋 Commandes utiles:"
echo "   - Voir les logs:        docker-compose logs -f [service]"
echo "   - Arrêter:              docker-compose down"
echo "   - Redémarrer:           docker-compose restart"
echo "   - Voir l'état:          docker-compose ps"
echo ""


