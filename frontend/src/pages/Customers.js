import React, { useEffect, useState } from 'react';
import { getCustomers, createCustomer, updateCustomer, deleteCustomer } from '../api/client';

const EMPTY = { firstName: '', lastName: '', email: '', phone: '' };

function CustomerModal({ initial, onClose, onSave }) {
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
        <h2>{initial ? 'Modifier le client' : 'Ajouter un client'}</h2>
        {error && <div className="error-msg">{error}</div>}
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label>Prénom *</label>
            <input name="firstName" value={form.firstName} onChange={handleChange} required />
          </div>
          <div className="form-group">
            <label>Nom *</label>
            <input name="lastName" value={form.lastName} onChange={handleChange} required />
          </div>
          <div className="form-group">
            <label>Email *</label>
            <input name="email" type="email" value={form.email} onChange={handleChange} required />
          </div>
          <div className="form-group">
            <label>Téléphone</label>
            <input name="phone" value={form.phone} onChange={handleChange} />
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

function Customers() {
  const [customers, setCustomers] = useState([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState(null);
  const [error, setError] = useState('');

  function load() {
    getCustomers().then(setCustomers).catch((err) => setError(err.message));
  }

  useEffect(() => { load(); }, []);

  async function handleSave(form) {
    if (editing) {
      await updateCustomer(editing.id, form);
    } else {
      await createCustomer(form);
    }
    load();
  }

  async function handleDelete(id) {
    if (!window.confirm('Supprimer ce client ?')) return;
    try {
      await deleteCustomer(id);
      load();
    } catch (err) {
      setError(err.message);
    }
  }

  return (
    <div>
      <div className="page-header">
        <h1>Clients</h1>
        <button className="btn btn-primary" onClick={() => { setEditing(null); setModalOpen(true); }}>
          + Ajouter
        </button>
      </div>

      {error && <div className="error-msg">{error}</div>}

      <div className="card">
        {customers.length === 0 ? (
          <div className="empty-state">Aucun client enregistré.</div>
        ) : (
          <table>
            <thead>
              <tr>
                <th>Prénom</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Inscrit le</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {customers.map((c) => (
                <tr key={c.id}>
                  <td>{c.firstName}</td>
                  <td>{c.lastName}</td>
                  <td>{c.email}</td>
                  <td>{c.phone}</td>
                  <td>{c.createdAt}</td>
                  <td style={{ display: 'flex', gap: '6px' }}>
                    <button className="btn btn-secondary btn-sm" onClick={() => { setEditing(c); setModalOpen(true); }}>✏️</button>
                    <button className="btn btn-danger btn-sm" onClick={() => handleDelete(c.id)}>🗑️</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {modalOpen && (
        <CustomerModal
          initial={editing}
          onClose={() => { setModalOpen(false); setEditing(null); }}
          onSave={handleSave}
        />
      )}
    </div>
  );
}

export default Customers;
