import React, { useEffect, useState } from 'react';
import { getVehicles, createVehicle, updateVehicle, deleteVehicle } from '../api/client';

const EMPTY = { make: '', model: '', year: '', price: '', color: '', status: 'available' };

function VehicleModal({ initial, onClose, onSave }) {
  const [form, setForm] = useState(initial || EMPTY);
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
        <h2>{initial ? 'Modifier le véhicule' : 'Ajouter un véhicule'}</h2>
        {error && <div className="error-msg">{error}</div>}
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label>Marque *</label>
            <input name="make" value={form.make} onChange={handleChange} required />
          </div>
          <div className="form-group">
            <label>Modèle *</label>
            <input name="model" value={form.model} onChange={handleChange} required />
          </div>
          <div className="form-group">
            <label>Année *</label>
            <input name="year" type="number" value={form.year} onChange={handleChange} required />
          </div>
          <div className="form-group">
            <label>Prix (€) *</label>
            <input name="price" type="number" value={form.price} onChange={handleChange} required />
          </div>
          <div className="form-group">
            <label>Couleur</label>
            <input name="color" value={form.color} onChange={handleChange} />
          </div>
          <div className="form-group">
            <label>Statut</label>
            <select name="status" value={form.status} onChange={handleChange}>
              <option value="available">Disponible</option>
              <option value="reserved">Réservé</option>
              <option value="sold">Vendu</option>
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

function statusLabel(status) {
  const labels = { available: 'Disponible', sold: 'Vendu', reserved: 'Réservé' };
  return labels[status] || status;
}

function Vehicles() {
  const [vehicles, setVehicles] = useState([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState(null);
  const [error, setError] = useState('');

  function load() {
    getVehicles().then(setVehicles).catch((err) => setError(err.message));
  }

  useEffect(() => { load(); }, []);

  async function handleSave(form) {
    if (editing) {
      await updateVehicle(editing.id, form);
    } else {
      await createVehicle(form);
    }
    load();
  }

  async function handleDelete(id) {
    if (!window.confirm('Supprimer ce véhicule ?')) return;
    try {
      await deleteVehicle(id);
      load();
    } catch (err) {
      setError(err.message);
    }
  }

  return (
    <div>
      <div className="page-header">
        <h1>Véhicules</h1>
        <button className="btn btn-primary" onClick={() => { setEditing(null); setModalOpen(true); }}>
          + Ajouter
        </button>
      </div>

      {error && <div className="error-msg">{error}</div>}

      <div className="card">
        {vehicles.length === 0 ? (
          <div className="empty-state">Aucun véhicule enregistré.</div>
        ) : (
          <table>
            <thead>
              <tr>
                <th>Marque</th>
                <th>Modèle</th>
                <th>Année</th>
                <th>Couleur</th>
                <th>Prix</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {vehicles.map((v) => (
                <tr key={v.id}>
                  <td>{v.make}</td>
                  <td>{v.model}</td>
                  <td>{v.year}</td>
                  <td>{v.color}</td>
                  <td>{Number(v.price).toLocaleString('fr-FR')} €</td>
                  <td>
                    <span className={`badge badge-${v.status}`}>{statusLabel(v.status)}</span>
                  </td>
                  <td style={{ display: 'flex', gap: '6px' }}>
                    <button className="btn btn-secondary btn-sm" onClick={() => { setEditing(v); setModalOpen(true); }}>✏️</button>
                    <button className="btn btn-danger btn-sm" onClick={() => handleDelete(v.id)}>🗑️</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {modalOpen && (
        <VehicleModal
          initial={editing}
          onClose={() => { setModalOpen(false); setEditing(null); }}
          onSave={handleSave}
        />
      )}
    </div>
  );
}

export default Vehicles;
