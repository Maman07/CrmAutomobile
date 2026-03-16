'use strict';

// In-memory data store for the CRM
const vehicles = [
  { id: '1', make: 'Toyota', model: 'Corolla', year: 2022, price: 22000, status: 'available', color: 'Blue' },
  { id: '2', make: 'Honda', model: 'Civic', year: 2023, price: 25000, status: 'available', color: 'Red' },
  { id: '3', make: 'Renault', model: 'Clio', year: 2021, price: 18000, status: 'sold', color: 'White' },
  { id: '4', make: 'Peugeot', model: '208', year: 2023, price: 21000, status: 'available', color: 'Grey' },
  { id: '5', make: 'BMW', model: 'Serie 3', year: 2022, price: 45000, status: 'reserved', color: 'Black' },
];

const customers = [
  { id: '1', firstName: 'Jean', lastName: 'Dupont', email: 'jean.dupont@example.com', phone: '0601020304', createdAt: '2024-01-15' },
  { id: '2', firstName: 'Marie', lastName: 'Martin', email: 'marie.martin@example.com', phone: '0612345678', createdAt: '2024-02-10' },
  { id: '3', firstName: 'Pierre', lastName: 'Bernard', email: 'pierre.bernard@example.com', phone: '0698765432', createdAt: '2024-03-05' },
];

const sales = [
  { id: '1', vehicleId: '3', customerId: '1', amount: 18000, saleDate: '2024-03-20', status: 'completed' },
  { id: '2', vehicleId: '5', customerId: '2', amount: 44500, saleDate: '2024-04-01', status: 'pending' },
];

module.exports = { vehicles, customers, sales };
