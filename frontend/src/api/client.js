const BASE = '/api';

async function apiFetch(path, options = {}) {
  const res = await fetch(`${BASE}${path}`, {
    headers: { 'Content-Type': 'application/json', ...options.headers },
    ...options,
    body: options.body ? JSON.stringify(options.body) : undefined,
  });
  if (res.status === 204) return null;
  const data = await res.json();
  if (!res.ok) throw new Error(data.message || 'Erreur serveur');
  return data;
}

export const getStats = () => apiFetch('/stats');

export const getVehicles = () => apiFetch('/vehicles');
export const getVehicle = (id) => apiFetch(`/vehicles/${id}`);
export const createVehicle = (body) => apiFetch('/vehicles', { method: 'POST', body });
export const updateVehicle = (id, body) => apiFetch(`/vehicles/${id}`, { method: 'PUT', body });
export const deleteVehicle = (id) => apiFetch(`/vehicles/${id}`, { method: 'DELETE' });

export const getCustomers = () => apiFetch('/customers');
export const getCustomer = (id) => apiFetch(`/customers/${id}`);
export const createCustomer = (body) => apiFetch('/customers', { method: 'POST', body });
export const updateCustomer = (id, body) => apiFetch(`/customers/${id}`, { method: 'PUT', body });
export const deleteCustomer = (id) => apiFetch(`/customers/${id}`, { method: 'DELETE' });

export const getSales = () => apiFetch('/sales');
export const createSale = (body) => apiFetch('/sales', { method: 'POST', body });
export const deleteSale = (id) => apiFetch(`/sales/${id}`, { method: 'DELETE' });
