import React, { useEffect, useState } from 'react';
import { getStats } from '../api/client';

function Dashboard() {
  const [stats, setStats] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    getStats()
      .then(setStats)
      .catch((err) => setError(err.message));
  }, []);

  if (error) return <p className="error-msg">{error}</p>;
  if (!stats) return <p>Chargement…</p>;

  return (
    <div>
      <div className="page-header">
        <h1>Tableau de bord</h1>
      </div>

      <div className="stats-grid">
        <div className="stat-card">
          <div className="label">Véhicules au total</div>
          <div className="value">{stats.totalVehicles}</div>
        </div>
        <div className="stat-card">
          <div className="label">Véhicules disponibles</div>
          <div className="value">{stats.availableVehicles}</div>
        </div>
        <div className="stat-card">
          <div className="label">Clients</div>
          <div className="value">{stats.totalCustomers}</div>
        </div>
        <div className="stat-card">
          <div className="label">Ventes</div>
          <div className="value">{stats.totalSales}</div>
        </div>
        <div className="stat-card">
          <div className="label">Chiffre d'affaires</div>
          <div className="value">{stats.revenue.toLocaleString('fr-FR')} €</div>
        </div>
      </div>

      <div className="card">
        <div className="card-header">Bienvenue dans votre CRM Automobile</div>
        <div style={{ padding: '20px', lineHeight: 1.7 }}>
          <p>Utilisez le menu à gauche pour gérer vos <strong>véhicules</strong>, vos <strong>clients</strong> et vos <strong>ventes</strong>.</p>
          <br />
          <p>Cette application est construite avec :</p>
          <ul style={{ marginLeft: '20px', marginTop: '8px' }}>
            <li><strong>React</strong> pour l'interface utilisateur</li>
            <li><strong>Node.js + Express</strong> pour le serveur API</li>
            <li><strong>React Router</strong> pour la navigation</li>
          </ul>
        </div>
      </div>
    </div>
  );
}

export default Dashboard;
