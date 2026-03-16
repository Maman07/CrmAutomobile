import { render, screen } from '@testing-library/react';
import App from './App';

// Mock the fetch API so tests don't call the real backend
global.fetch = jest.fn(() =>
  Promise.resolve({
    ok: true,
    status: 200,
    json: () => Promise.resolve({ totalVehicles: 0, availableVehicles: 0, totalCustomers: 0, totalSales: 0, revenue: 0 }),
  })
);

test('renders the sidebar logo', () => {
  render(<App />);
  const logo = screen.getByText(/CRM Automobile/i);
  expect(logo).toBeInTheDocument();
});

test('renders navigation links', () => {
  render(<App />);
  expect(screen.getByText(/Tableau de bord/i)).toBeInTheDocument();
  expect(screen.getByText(/Véhicules/i)).toBeInTheDocument();
  expect(screen.getByText(/Clients/i)).toBeInTheDocument();
  expect(screen.getByText(/Ventes/i)).toBeInTheDocument();
});
