'use strict';

const { describe, it, before, after } = require('node:test');
const assert = require('node:assert/strict');
const http = require('http');

// We spin up the server on a random port so tests don't clash with a running instance.
let server;
let baseUrl;

before(async () => {
  // Reset in-memory data before tests
  const data = require('./data');
  data.vehicles.length = 0;
  data.customers.length = 0;
  data.sales.length = 0;

  const app = require('./server');
  await new Promise((resolve) => {
    server = app.listen(0, () => {
      baseUrl = `http://localhost:${server.address().port}`;
      resolve();
    });
  });
});

after(() => {
  server.close();
});

async function request(method, path, body) {
  return new Promise((resolve, reject) => {
    const url = new URL(path, baseUrl);
    const options = {
      method,
      hostname: url.hostname,
      port: url.port,
      path: url.pathname + url.search,
      headers: { 'Content-Type': 'application/json' },
    };
    const req = http.request(options, (res) => {
      let raw = '';
      res.on('data', (chunk) => (raw += chunk));
      res.on('end', () => {
        let json = null;
        try { json = JSON.parse(raw); } catch (_) { /* empty body */ }
        resolve({ status: res.statusCode, body: json });
      });
    });
    req.on('error', reject);
    if (body !== undefined) req.write(JSON.stringify(body));
    req.end();
  });
}

// ── Vehicles ──────────────────────────────────────────────────────────────────

describe('Vehicles API', () => {
  let createdId;

  it('GET /api/vehicles returns empty array initially', async () => {
    const res = await request('GET', '/api/vehicles');
    assert.equal(res.status, 200);
    assert.deepEqual(res.body, []);
  });

  it('POST /api/vehicles creates a vehicle', async () => {
    const res = await request('POST', '/api/vehicles', { make: 'Toyota', model: 'Yaris', year: 2023, price: 20000 });
    assert.equal(res.status, 201);
    assert.equal(res.body.make, 'Toyota');
    assert.equal(res.body.status, 'available');
    createdId = res.body.id;
  });

  it('GET /api/vehicles/:id returns the created vehicle', async () => {
    const res = await request('GET', `/api/vehicles/${createdId}`);
    assert.equal(res.status, 200);
    assert.equal(res.body.id, createdId);
  });

  it('PUT /api/vehicles/:id updates the vehicle', async () => {
    const res = await request('PUT', `/api/vehicles/${createdId}`, { status: 'sold' });
    assert.equal(res.status, 200);
    assert.equal(res.body.status, 'sold');
  });

  it('DELETE /api/vehicles/:id removes the vehicle', async () => {
    const res = await request('DELETE', `/api/vehicles/${createdId}`);
    assert.equal(res.status, 204);
    const check = await request('GET', `/api/vehicles/${createdId}`);
    assert.equal(check.status, 404);
  });

  it('POST /api/vehicles returns 400 when required fields are missing', async () => {
    const res = await request('POST', '/api/vehicles', { make: 'Honda' });
    assert.equal(res.status, 400);
  });
});

// ── Customers ─────────────────────────────────────────────────────────────────

describe('Customers API', () => {
  let createdId;

  it('GET /api/customers returns empty array initially', async () => {
    const res = await request('GET', '/api/customers');
    assert.equal(res.status, 200);
    assert.deepEqual(res.body, []);
  });

  it('POST /api/customers creates a customer', async () => {
    const res = await request('POST', '/api/customers', {
      firstName: 'Alice', lastName: 'Durand', email: 'alice@example.com', phone: '0611223344',
    });
    assert.equal(res.status, 201);
    assert.equal(res.body.firstName, 'Alice');
    createdId = res.body.id;
  });

  it('GET /api/customers/:id returns the customer', async () => {
    const res = await request('GET', `/api/customers/${createdId}`);
    assert.equal(res.status, 200);
    assert.equal(res.body.email, 'alice@example.com');
  });

  it('DELETE /api/customers/:id removes the customer', async () => {
    const res = await request('DELETE', `/api/customers/${createdId}`);
    assert.equal(res.status, 204);
  });

  it('POST /api/customers returns 400 when required fields are missing', async () => {
    const res = await request('POST', '/api/customers', { firstName: 'Bob' });
    assert.equal(res.status, 400);
  });
});

// ── Stats ─────────────────────────────────────────────────────────────────────

describe('Stats API', () => {
  it('GET /api/stats returns stats object', async () => {
    const res = await request('GET', '/api/stats');
    assert.equal(res.status, 200);
    assert.ok('totalVehicles' in res.body);
    assert.ok('totalCustomers' in res.body);
    assert.ok('revenue' in res.body);
  });
});
