'use strict';

const express = require('express');
const cors = require('cors');
const { randomUUID } = require('crypto');
const data = require('./data');

const app = express();
const PORT = process.env.PORT || 5000;

app.use(cors());
app.use(express.json());

// ── Vehicles ──────────────────────────────────────────────────────────────────

app.get('/api/vehicles', (_req, res) => {
  res.json(data.vehicles);
});

app.get('/api/vehicles/:id', (req, res) => {
  const vehicle = data.vehicles.find((v) => v.id === req.params.id);
  if (!vehicle) return res.status(404).json({ message: 'Véhicule non trouvé' });
  res.json(vehicle);
});

app.post('/api/vehicles', (req, res) => {
  const { make, model, year, price, status, color } = req.body;
  if (!make || !model || !year || !price) {
    return res.status(400).json({ message: 'Les champs make, model, year et price sont obligatoires' });
  }
  const vehicle = { id: randomUUID(), make, model, year: Number(year), price: Number(price), status: status || 'available', color: color || '' };
  data.vehicles.push(vehicle);
  res.status(201).json(vehicle);
});

app.put('/api/vehicles/:id', (req, res) => {
  const index = data.vehicles.findIndex((v) => v.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Véhicule non trouvé' });
  data.vehicles[index] = { ...data.vehicles[index], ...req.body, id: req.params.id };
  res.json(data.vehicles[index]);
});

app.delete('/api/vehicles/:id', (req, res) => {
  const index = data.vehicles.findIndex((v) => v.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Véhicule non trouvé' });
  data.vehicles.splice(index, 1);
  res.status(204).end();
});

// ── Customers ─────────────────────────────────────────────────────────────────

app.get('/api/customers', (_req, res) => {
  res.json(data.customers);
});

app.get('/api/customers/:id', (req, res) => {
  const customer = data.customers.find((c) => c.id === req.params.id);
  if (!customer) return res.status(404).json({ message: 'Client non trouvé' });
  res.json(customer);
});

app.post('/api/customers', (req, res) => {
  const { firstName, lastName, email, phone } = req.body;
  if (!firstName || !lastName || !email) {
    return res.status(400).json({ message: 'Les champs firstName, lastName et email sont obligatoires' });
  }
  const customer = {
    id: randomUUID(),
    firstName,
    lastName,
    email,
    phone: phone || '',
    createdAt: new Date().toISOString().split('T')[0],
  };
  data.customers.push(customer);
  res.status(201).json(customer);
});

app.put('/api/customers/:id', (req, res) => {
  const index = data.customers.findIndex((c) => c.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Client non trouvé' });
  data.customers[index] = { ...data.customers[index], ...req.body, id: req.params.id };
  res.json(data.customers[index]);
});

app.delete('/api/customers/:id', (req, res) => {
  const index = data.customers.findIndex((c) => c.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Client non trouvé' });
  data.customers.splice(index, 1);
  res.status(204).end();
});

// ── Sales ─────────────────────────────────────────────────────────────────────

app.get('/api/sales', (_req, res) => {
  const enriched = data.sales.map((s) => ({
    ...s,
    vehicle: data.vehicles.find((v) => v.id === s.vehicleId) || null,
    customer: data.customers.find((c) => c.id === s.customerId) || null,
  }));
  res.json(enriched);
});

app.get('/api/sales/:id', (req, res) => {
  const sale = data.sales.find((s) => s.id === req.params.id);
  if (!sale) return res.status(404).json({ message: 'Vente non trouvée' });
  res.json({
    ...sale,
    vehicle: data.vehicles.find((v) => v.id === sale.vehicleId) || null,
    customer: data.customers.find((c) => c.id === sale.customerId) || null,
  });
});

app.post('/api/sales', (req, res) => {
  const { vehicleId, customerId, amount, saleDate, status } = req.body;
  if (!vehicleId || !customerId || !amount) {
    return res.status(400).json({ message: 'Les champs vehicleId, customerId et amount sont obligatoires' });
  }
  const sale = {
    id: randomUUID(),
    vehicleId,
    customerId,
    amount: Number(amount),
    saleDate: saleDate || new Date().toISOString().split('T')[0],
    status: status || 'pending',
  };
  data.sales.push(sale);
  res.status(201).json(sale);
});

app.delete('/api/sales/:id', (req, res) => {
  const index = data.sales.findIndex((s) => s.id === req.params.id);
  if (index === -1) return res.status(404).json({ message: 'Vente non trouvée' });
  data.sales.splice(index, 1);
  res.status(204).end();
});

// ── Dashboard stats ───────────────────────────────────────────────────────────

app.get('/api/stats', (_req, res) => {
  const totalVehicles = data.vehicles.length;
  const availableVehicles = data.vehicles.filter((v) => v.status === 'available').length;
  const totalCustomers = data.customers.length;
  const totalSales = data.sales.length;
  const revenue = data.sales.filter((s) => s.status === 'completed').reduce((sum, s) => sum + s.amount, 0);
  res.json({ totalVehicles, availableVehicles, totalCustomers, totalSales, revenue });
});

// ── Start ─────────────────────────────────────────────────────────────────────

if (require.main === module) {
  app.listen(PORT, () => {
    console.log(`Serveur CRM démarré sur http://localhost:${PORT}`);
  });
}

module.exports = app;
