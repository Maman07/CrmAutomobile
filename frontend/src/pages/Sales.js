import React, { useEffect, useState } from 'react';
import { getSales, createSale, deleteSale, getVehicles, getCustomers } from '../api/client';

const EMPTY = { vehicleId: '', customerId: '', amount: '', saleDate: '', status: 'pending' };

function SaleModal({ onClose, onSave, vehicles, customers }) {
  const [form, setForm] = useState(EMPTY);
  const [error, setError] = useState('');

  function handleChange(e) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setError('');
    try {
      await onSave(form);
      onClose();
    } catch (err) {
      setError(err.message);
    }
  }

  return (
    <div className="modal-overlay">
      <div className="modal">
        <h2>Enregistrer une vente</h2>
        {error && <div className="error-msg">{error}</div>}
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label>Véhicule *</label>
            <select name="vehicleId" value={form.vehicleId} onChange={handleChange} required>
              <option value="">-- Choisir --</option>
              {vehicles.map((v) => (
                <option key={v.id} value={v.id}>{v.year} {v.make} {v.model}</option>
              ))}
            </select>
          </div>
          <div className="form-group">
            <label>Client *</label>
            <select name="customerId" value={form.customerId} onChange={handleChange} required>
              <option value="">-- Choisir --</option>
              {customers.map((c) => (
                <option key={c.id} value={c.id}>{c.firstName} {c.lastName}</option>
              ))}
            </select>
          </div>
          <div className="form-group">
            <label>Montant (€) *</label>
            <input name="amount" type="number" value={form.amount} onChange={handleChange} required />
          </div>
          <div className="form-group">
            <label>Date de vente</label>
            <input name="saleDate" type="date" value={form.saleDate} onChange={handleChange} />
          </div>
          <div className="form-group">
            <label>Statut</label>
            <select name="status" value={form.status} onChange={handleChange}>
              <option value="pending">En attente</option>
              <option value="completed">Terminée</option>
            </select>
          </div>
          <div className="modal-actions">
            <button type="button" className="btn btn-secondary" onClick={onClose}>Annuler</button>
            <button type="submit" className="btn btn-primary">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  );
}

function Sales() {
  const [sales, setSales] = useState([]);
  const [vehicles, setVehicles] = useState([]);
  const [customers, setCustomers] = useState([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [error, setError] = useState('');

  function load() {
    Promise.all([getSales(), getVehicles(), getCustomers()])
      .then(([s, v, c]) => { setSales(s); setVehicles(v); setCustomers(c); })
      .catch((err) => setError(err.message));
  }

  useEffect(() => { load(); }, []);

  async function handleSave(form) {
    await createSale(form);
    load();
  }

  async function handleDelete(id) {
    if (!window.confirm('Supprimer cette vente ?')) return;
    try {
      await deleteSale(id);
      load();
    } catch (err) {
      setError(err.message);
    }
  }

  return (
    <div>
      <div className="page-header">
        <h1>Ventes</h1>
        <button className="btn btn-primary" onClick={() => setModalOpen(true)}>
          + Nouvelle vente
        </button>
      </div>

      {error && <div className="error-msg">{error}</div>}

      <div className="card">
        {sales.length === 0 ? (
          <div className="empty-state">Aucune vente enregistrée.</div>
        ) : (
          <table>
            <thead>
              <tr>
                <th>Véhicule</th>
                <th>Client</th>
                <th>Montant</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {sales.map((s) => (
                <tr key={s.id}>
                  <td>{s.vehicle ? `${s.vehicle.year} ${s.vehicle.make} ${s.vehicle.model}` : '—'}</td>
                  <td>{s.customer ? `${s.customer.firstName} ${s.customer.lastName}` : '—'}</td>
                  <td>{Number(s.amount).toLocaleString('fr-FR')} €</td>
                  <td>{s.saleDate}</td>
                  <td>
                    <span className={`badge badge-${s.status}`}>
                      {s.status === 'completed' ? 'Terminée' : 'En attente'}
                    </span>
                  </td>
                  <td>
                    <button className="btn btn-danger btn-sm" onClick={() => handleDelete(s.id)}>🗑️</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {modalOpen && (
        <SaleModal
          onClose={() => setModalOpen(false)}
          onSave={handleSave}
          vehicles={vehicles}
          customers={customers}
        />
      )}
    </div>
  );
}

export default Sales;
