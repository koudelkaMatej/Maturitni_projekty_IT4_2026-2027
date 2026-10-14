'use strict';

const inventoryCatalog = [
  { id: 'vx01', name: 'VX-01 Airsoft karabina', category: 'AIRSOFT' },
  { id: 'px9', name: 'PX-9 Airsoft pistole', category: 'AIRSOFT' },
  { id: 'eyepro', name: 'Spectra ochranné brýle', category: 'OCHRANA' },
  { id: 'mesh-mask', name: 'Obličejová maska Grid', category: 'OCHRANA' },
  { id: 'gloves', name: 'Rukavice Field Flex', category: 'OCHRANA' },
  { id: 'shirt', name: 'Field košile // Olive', category: 'OBLEČENÍ' },
  { id: 'cap', name: 'Kšiltovka Utility', category: 'OBLEČENÍ' },
  { id: 'sling', name: 'Urban textilní popruh', category: 'VYBAVENÍ' },
  { id: 'bag', name: 'Modulární transportní taška', category: 'VYBAVENÍ' },
  { id: 'care-kit', name: 'Sada péče Airsoft Care', category: 'ÚDRŽBA' },
  { id: 'skin', name: 'Custom skin kit', category: 'CUSTOM' },
  { id: 'patch', name: 'Patch set // VOID', category: 'CUSTOM' },
  { id: 'case', name: 'Ochranný přepravní obal', category: 'VYBAVENÍ' }
];
const defaultStock = { vx01: 8, px9: 12, eyepro: 24, 'mesh-mask': 18, gloves: 15, shirt: 10, cap: 20, sling: 11, bag: 7, 'care-kit': 16, skin: 30, patch: 25, case: 9 };
const formatCurrency = value => `${Number(value).toLocaleString('cs-CZ')} Kč`;
const escapeHtml = value => String(value).replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
let db;

function openDatabase() {
  return new Promise((resolve, reject) => {
    if (!('indexedDB' in window)) return reject(new Error('IndexedDB není v tomto prohlížeči dostupné.'));
    const request = indexedDB.open('voidArmoryDemo', 1);
    request.onupgradeneeded = () => {
      const database = request.result;
      if (!database.objectStoreNames.contains('orders')) database.createObjectStore('orders', { keyPath: 'orderId', autoIncrement: true });
      if (!database.objectStoreNames.contains('feedback')) database.createObjectStore('feedback', { keyPath: 'feedbackId', autoIncrement: true });
      if (!database.objectStoreNames.contains('inventory')) database.createObjectStore('inventory', { keyPath: 'productId' });
    };
    request.onsuccess = () => {
      db = request.result;
      const tx = db.transaction('inventory', 'readwrite');
      const store = tx.objectStore('inventory');
      inventoryCatalog.forEach(product => {
        const get = store.get(product.id);
        get.onsuccess = () => { if (!get.result) store.add({ productId: product.id, stock: defaultStock[product.id] }); };
      });
      tx.oncomplete = resolve;
      tx.onerror = () => reject(tx.error || new Error('Databázi se nepodařilo inicializovat.'));
    };
    request.onerror = () => reject(request.error || new Error('Databázi nelze otevřít.'));
  });
}

function readAll(name) {
  return new Promise((resolve, reject) => {
    const request = db.transaction(name).objectStore(name).getAll();
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

function renderInventory(rows) {
  const stockById = Object.fromEntries(rows.map(row => [row.productId, row.stock]));
  document.getElementById('inventoryRows').innerHTML = inventoryCatalog.map(product => `
    <tr><td>${escapeHtml(product.name)}</td><td>${product.category}</td><td><input class="stock-input" type="number" min="0" max="9999" step="1" value="${stockById[product.id] ?? defaultStock[product.id]}" data-stock-id="${product.id}" aria-label="Počet kusů: ${escapeHtml(product.name)}"></td><td><button class="admin-button" type="button" data-save-stock="${product.id}">ULOŽIT</button></td></tr>`).join('');
}

function renderDemand(orders) {
  const summary = new Map(inventoryCatalog.map(product => [product.id, { ...product, qty: 0, revenue: 0 }]));
  orders.forEach(order => order.items.forEach(item => {
    const productId = item.productId || (item.name.includes('VX-01') ? 'vx01' : '');
    const row = summary.get(productId);
    if (row) {
      row.qty += Number(item.qty) || 0;
      row.revenue += (Number(item.unitPrice) || 0) * (Number(item.qty) || 0);
    }
  }));
  const ranked = [...summary.values()].sort((a, b) => b.qty - a.qty || a.name.localeCompare(b.name, 'cs'));
  document.getElementById('demandRows').innerHTML = ranked.map(item => `<tr><td>${escapeHtml(item.name)}</td><td>${item.qty}</td><td>${formatCurrency(item.revenue)}</td></tr>`).join('');
}

function renderOrders(orders) {
  const latest = [...orders].sort((a, b) => b.createdAt.localeCompare(a.createdAt)).slice(0, 12);
  document.getElementById('orderRows').innerHTML = latest.length ? latest.map(order => `
    <tr><td>${new Date(order.createdAt).toLocaleString('cs-CZ')}</td><td>${escapeHtml(order.email || '—')}</td><td>${order.items.map(item => `${escapeHtml(item.name)} × ${item.qty}`).join('<br>')}</td><td>${formatCurrency(order.total)}</td></tr>`).join('') : '<tr><td colspan="4" class="admin-empty">Zatím nejsou uložené test objednávky.</td></tr>';
}

function renderFeedback(entries) {
  const latest = [...entries].sort((a, b) => b.createdAt.localeCompare(a.createdAt)).slice(0, 12);
  document.getElementById('feedbackRows').innerHTML = latest.length ? latest.map(entry => `
    <tr><td>${new Date(entry.createdAt).toLocaleString('cs-CZ')}</td><td>${escapeHtml(entry.email)}</td><td>${escapeHtml(entry.comment)}</td></tr>`).join('') : '<tr><td colspan="3" class="admin-empty">Zatím nejsou uložené komentáře.</td></tr>';
}

async function refreshDashboard() {
  const [orders, feedback, stock] = await Promise.all([readAll('orders'), readAll('feedback'), readAll('inventory')]);
  document.getElementById('statOrders').textContent = String(orders.length);
  document.getElementById('statRevenue').textContent = formatCurrency(orders.reduce((sum, order) => sum + (Number(order.total) || 0), 0));
  document.getElementById('statUnits').textContent = String(orders.reduce((sum, order) => sum + order.items.reduce((count, item) => count + (Number(item.qty) || 0), 0), 0));
  document.getElementById('statFeedback').textContent = String(feedback.length);
  renderInventory(stock);
  renderDemand(orders);
  renderOrders(orders);
  renderFeedback(feedback);
}

const status = document.getElementById('adminStatus');
openDatabase().then(refreshDashboard).catch(error => {
  status.textContent = `${error.message} Otevři obě stránky přes stejný lokální server.`;
});

document.getElementById('inventoryRows').addEventListener('click', event => {
  const button = event.target.closest('[data-save-stock]');
  if (!button || !db) return;
  const id = button.dataset.saveStock;
  const input = document.querySelector(`[data-stock-id="${id}"]`);
  const stock = Number(input.value);
  if (!Number.isInteger(stock) || stock < 0 || stock > 9999) {
    status.textContent = 'Zadej celé číslo od 0 do 9999.';
    return;
  }
  const tx = db.transaction('inventory', 'readwrite');
  tx.objectStore('inventory').put({ productId: id, stock });
  tx.oncomplete = () => { status.textContent = 'Zásoba uložena v tomto prohlížeči.'; refreshDashboard(); };
  tx.onerror = () => { status.textContent = 'Změnu zásoby se nepodařilo uložit.'; };
});

document.getElementById('exportData').addEventListener('click', async () => {
  if (!db) return;
  try {
    const [orders, feedback, inventory] = await Promise.all([readAll('orders'), readAll('feedback'), readAll('inventory')]);
    const payload = { exportedAt: new Date().toISOString(), notice: 'Demo data only; includes personal email addresses. Keep this file private.', orders, feedback, inventory };
    const url = URL.createObjectURL(new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' }));
    const link = document.createElement('a');
    link.href = url;
    link.download = 'void-armory-demo-export.json';
    link.click();
    URL.revokeObjectURL(url);
    status.textContent = 'JSON export stažen. Obsahuje e-maily — uchovávej jej bezpečně.';
  } catch {
    status.textContent = 'Export se nepodařilo vytvořit.';
  }
});

document.getElementById('clearData').addEventListener('click', () => {
  if (!db || !window.confirm('Opravdu smazat všechny lokální test objednávky a komentáře? Zásoby zůstanou zachované.')) return;
  const tx = db.transaction(['orders', 'feedback'], 'readwrite');
  tx.objectStore('orders').clear();
  tx.objectStore('feedback').clear();
  tx.oncomplete = () => { status.textContent = 'Test objednávky a komentáře smazány.'; refreshDashboard(); };
  tx.onerror = () => { status.textContent = 'Data se nepodařilo smazat.'; };
});
