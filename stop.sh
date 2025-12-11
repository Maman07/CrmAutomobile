#!/bin/bash

echo "🛑 =========================================="
echo "🛑 ARRÊT AUTOTECH CRM"
echo "🛑 =========================================="
echo ""

# Arrêter tous les services
echo "🛑 Arrêt de tous les services..."
docker-compose down

echo ""
echo "✅ Tous les services ont été arrêtés!"
echo ""
echo "📋 Pour redémarrer, utilisez: ./start.sh"
echo "📋 Pour tout supprimer (y compris les données): docker-compose down -v"
echo ""


