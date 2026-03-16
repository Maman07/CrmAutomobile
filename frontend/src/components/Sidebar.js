import React from 'react';
import { NavLink } from 'react-router-dom';

function Sidebar() {
  return (
    <aside className="sidebar">
      <div className="sidebar-logo">🚗 CRM Automobile</div>
      <nav>
        <NavLink to="/" end>📊 Tableau de bord</NavLink>
        <NavLink to="/vehicles">🚘 Véhicules</NavLink>
        <NavLink to="/customers">👥 Clients</NavLink>
        <NavLink to="/sales">💰 Ventes</NavLink>
      </nav>
    </aside>
  );
}

export default Sidebar;
