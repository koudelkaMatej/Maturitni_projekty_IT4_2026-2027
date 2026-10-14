'use strict';

const products = [
  { id: 'vx01', name: 'VX-01 Airsoft karabina', category: 'replica', categoryLabel: 'AIRSOFT REPLIKA', price: 3890, stock: 8, tag: 'TOP VOLBA', description: 'Dekorativní airsoftová replika pro rekreační hru na schváleném hřišti.' },
  { id: 'px9', name: 'PX-9 Airsoft pistole', category: 'replica', categoryLabel: 'AIRSOFT REPLIKA', price: 1590, stock: 12, tag: '', description: 'Kompaktní airsoftová replika pro rekreační hru na schváleném hřišti.' },
  { id: 'eyepro', name: 'Spectra ochranné brýle', category: 'safety', categoryLabel: 'OCHRANA', price: 690, stock: 24, tag: 'SAFETY', description: 'Ochranné brýle pro airsoftové hřiště. Vždy ověř certifikaci a pravidla pořadatele.' },
  { id: 'mesh-mask', name: 'Obličejová maska Grid', category: 'safety', categoryLabel: 'OCHRANA', price: 490, stock: 18, tag: '', description: 'Lehká ochranná maska na obličej pro bezpečnější rekreační hru.' },
  { id: 'gloves', name: 'Rukavice Field Flex', category: 'safety', categoryLabel: 'OCHRANA', price: 590, stock: 15, tag: '', description: 'Textilní rukavice pro pohodlí a ochranu rukou při sportovní hře.' },
  { id: 'shirt', name: 'Field košile // Olive', category: 'clothing', categoryLabel: 'OBLEČENÍ', price: 1190, stock: 10, tag: '', description: 'Pohodlná volnočasová košile v outdoorovém stylu.' },
  { id: 'cap', name: 'Kšiltovka Utility', category: 'clothing', categoryLabel: 'OBLEČENÍ', price: 420, stock: 20, tag: '', description: 'Lehká textilní kšiltovka pro venkovní aktivity.' },
  { id: 'sling', name: 'Urban textilní popruh', category: 'gear', categoryLabel: 'VYBAVENÍ', price: 450, stock: 11, tag: '', description: 'Textilní doplněk pro vzhled a přenášení sportovního vybavení.' },
  { id: 'bag', name: 'Modulární transportní taška', category: 'gear', categoryLabel: 'VYBAVENÍ', price: 1390, stock: 7, tag: '', description: 'Odolná taška na bezpečné přenášení a organizaci osobního vybavení.' },
  { id: 'care-kit', name: 'Sada péče Airsoft Care', category: 'care', categoryLabel: 'PÉČE A ÚDRŽBA', price: 350, stock: 16, tag: '', description: 'Základní čisticí příslušenství určené pro běžnou péči o sportovní vybavení; řiď se pokyny výrobce.' },
  { id: 'skin', name: 'Custom skin kit', category: 'custom', categoryLabel: 'KOSMETICKÝ DOPLNĚK', price: 320, stock: 30, tag: 'CUSTOM', description: 'Sada odnímatelných dekorativních samolepek pro osobitý vzhled vybavení.' },
  { id: 'patch', name: 'Patch set // VOID', category: 'custom', categoryLabel: 'KOSMETICKÝ DOPLNĚK', price: 250, stock: 25, tag: '', description: 'Sada textilních nášivek pro batoh, pouzdro nebo oblečení.' },
  { id: 'case', name: 'Ochranný přepravní obal', category: 'gear', categoryLabel: 'VYBAVENÍ', price: 790, stock: 9, tag: '', description: 'Polstrovaný obal pro bezpečné uložení a přenášení sportovního vybavení.' }
];

const currency = value => `${Number(value).toLocaleString('cs-CZ')} Kč`;
const grid = document.getElementById('productGrid');
const emptyState = document.getElementById('emptyState');
const searchInput = document.getElementById('productSearch');
const filterButtons = [...document.querySelectorAll('[data-filter]')];
const cartCount = document.getElementById('cartCount');
const cartDialog = document.getElementById('cartDialog');
const cartBackdrop = document.getElementById('cartBackdrop');
const cartItems = document.getElementById('cartItems');
const cartEmpty = document.getElementById('cartEmpty');
const cartTotal = document.getElementById('cartTotal');
const toast = document.getElementById('toast');
let activeFilter = 'all';
let toastTimeout;
let cart = loadCart();
let database;
let inventory = Object.fromEntries(products.map(product => [product.id, product.stock]));

const databaseReady = openDatabase();

function openDatabase() {
  return new Promise((resolve, reject) => {
    if (!('indexedDB' in window)) {
      reject(new Error('Tento prohlížeč nepodporuje IndexedDB.'));
      return;
    }
    const request = indexedDB.open('voidArmoryDemo', 1);
    request.onupgradeneeded = () => {
      const db = request.result;
      if (!db.objectStoreNames.contains('orders')) db.createObjectStore('orders', { keyPath: 'orderId', autoIncrement: true });
      if (!db.objectStoreNames.contains('feedback')) db.createObjectStore('feedback', { keyPath: 'feedbackId', autoIncrement: true });
      if (!db.objectStoreNames.contains('inventory')) db.createObjectStore('inventory', { keyPath: 'productId' });
    };
    request.onsuccess = () => {
      database = request.result;
      const tx = database.transaction('inventory', 'readwrite');
      const store = tx.objectStore('inventory');
      products.forEach(product => {
        const getRequest = store.get(product.id);
        getRequest.onsuccess = () => {
          if (!getRequest.result) store.add({ productId: product.id, stock: product.stock });
        };
      });
      tx.oncomplete = () => loadInventory().then(resolve, reject);
      tx.onerror = () => reject(tx.error || new Error('Nepodařilo se inicializovat databázi.'));
    };
    request.onerror = () => reject(request.error || new Error('Databázi nelze otevřít.'));
  });
}

function loadInventory() {
  return new Promise((resolve, reject) => {
    const request = database.transaction('inventory').objectStore('inventory').getAll();
    request.onsuccess = () => {
      inventory = Object.fromEntries(request.result.map(item => [item.productId, item.stock]));
      renderProducts();
      resolve();
    };
    request.onerror = () => reject(request.error);
  });
}

function readAll(storeName) {
  return new Promise((resolve, reject) => {
    const request = database.transaction(storeName).objectStore(storeName).getAll();
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

function maskEmail(email) {
  const [name, domain] = email.split('@');
  return `${name.slice(0, 1)}${'•'.repeat(Math.min(Math.max(name.length - 1, 3), 8))}@${domain}`;
}

function renderFeedback() {
  const list = document.getElementById('feedbackList');
  if (!database) {
    list.innerHTML = '<p class="micro-note">Lokální databáze není dostupná. Otevři projekt přes lokální server.</p>';
    return;
  }
  readAll('feedback').then(entries => {
    const recent = entries.sort((a, b) => b.createdAt.localeCompare(a.createdAt)).slice(0, 6);
    list.innerHTML = recent.length ? recent.map(entry => `
      <article class="feedback-card">
        <div class="feedback-meta"><strong>${escapeHtml(maskEmail(entry.email))}</strong><time>${new Date(entry.createdAt).toLocaleDateString('cs-CZ')}</time></div>
        <p>${escapeHtml(entry.comment)}</p>
      </article>`).join('') : '<p class="micro-note">Zatím tu nejsou žádné komentáře.</p>';
  }).catch(() => { list.innerHTML = '<p class="micro-note">Komentáře se nepodařilo načíst.</p>'; });
}

function loadCart() {
  try {
    const saved = JSON.parse(localStorage.getItem('voidArmoryCart') || '[]');
    return Array.isArray(saved) ? saved.filter(item => item && typeof item.name === 'string' && Number.isFinite(item.price) && Number.isFinite(item.qty) && item.qty > 0) : [];
  } catch (error) {
    return [];
  }
}

function saveCart() {
  try { localStorage.setItem('voidArmoryCart', JSON.stringify(cart)); }
  catch (error) { showToast('Košík nelze uložit do tohoto prohlížeče.'); }
}

function renderProducts() {
  const query = searchInput.value.trim().toLocaleLowerCase('cs-CZ');
  const visible = products.filter(product => {
    const matchesFilter = activeFilter === 'all' || product.category === activeFilter;
    const searchable = `${product.name} ${product.categoryLabel} ${product.description}`.toLocaleLowerCase('cs-CZ');
    return matchesFilter && searchable.includes(query);
  });

  grid.innerHTML = visible.map((product, index) => `
    <article class="product-card">
      <div class="product-visual">
        <span class="product-index">ITEM // ${String(products.indexOf(product) + 1).padStart(2, '0')}</span>
        ${product.tag ? `<span class="product-tag">${product.tag}</span>` : ''}
        <img class="product-logo" src="imgs/logoNback/voidarmlogo.png" alt="" />
      </div>
      <div class="product-copy">
        <p class="product-category">${product.categoryLabel}</p>
        <h3 class="product-name">${product.name}</h3>
        <p class="product-description">${product.description}</p>
        <div class="product-card-bottom">
          <strong class="product-price">${currency(product.price)}</strong>
          <button class="add-product" type="button" data-add-product="${product.id}">PŘIDAT ＋</button>
        </div>
        <p class="product-stock">SKLADEM: ${inventory[product.id] ?? product.stock} KS</p>
      </div>
    </article>`).join('');
  emptyState.hidden = visible.length > 0;
}

function addToCart(item) {
  const existing = cart.find(entry => entry.key === item.key);
  if (existing) existing.qty += 1;
  else cart.push({ ...item, qty: 1 });
  saveCart();
  renderCart();
  showToast('Položka byla přidána do košíku.');
}

function renderCart() {
  const itemCount = cart.reduce((sum, item) => sum + item.qty, 0);
  const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
  cartCount.textContent = String(itemCount);
  cartTotal.textContent = currency(total);
  cartEmpty.hidden = cart.length > 0;
  cartItems.innerHTML = cart.map((item, index) => `
    <article class="cart-row">
      <div>
        <h3 class="cart-row-name">${escapeHtml(item.name)}</h3>
        ${item.details ? `<p class="cart-row-detail">${escapeHtml(item.details)}</p>` : ''}
        <p class="cart-row-detail">${currency(item.price)} / ks</p>
      </div>
      <strong class="cart-row-price">${currency(item.price * item.qty)}</strong>
      <div class="cart-row-actions">
        <button class="qty-btn" type="button" data-cart-action="decrease" data-index="${index}" aria-label="Odebrat jeden kus">−</button>
        <span>${item.qty} ks</span>
        <button class="qty-btn" type="button" data-cart-action="increase" data-index="${index}" aria-label="Přidat jeden kus">＋</button>
        <button class="remove-btn" type="button" data-cart-action="remove" data-index="${index}">ODEBRAT</button>
      </div>
    </article>`).join('');
}

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
}

function showCart() {
  cartDialog.hidden = false;
  cartBackdrop.hidden = false;
  document.body.style.overflow = 'hidden';
  document.getElementById('closeCart').focus();
}

function hideCart() {
  cartDialog.hidden = true;
  cartBackdrop.hidden = true;
  document.body.style.overflow = '';
  document.getElementById('openCart').focus();
}

function showToast(message) {
  toast.textContent = message;
  toast.classList.add('show');
  window.clearTimeout(toastTimeout);
  toastTimeout = window.setTimeout(() => toast.classList.remove('show'), 2400);
}

filterButtons.forEach(button => button.addEventListener('click', () => {
  activeFilter = button.dataset.filter;
  filterButtons.forEach(chip => chip.classList.toggle('active', chip === button));
  renderProducts();
}));
searchInput.addEventListener('input', renderProducts);
grid.addEventListener('click', event => {
  const button = event.target.closest('[data-add-product]');
  if (!button) return;
  const product = products.find(item => item.id === button.dataset.addProduct);
  if (product) addToCart({ key: product.id, name: product.name, price: product.price, details: '' });
});

document.getElementById('openCart').addEventListener('click', showCart);
document.getElementById('closeCart').addEventListener('click', hideCart);
cartBackdrop.addEventListener('click', hideCart);
document.addEventListener('keydown', event => { if (event.key === 'Escape' && !cartDialog.hidden) hideCart(); });

cartItems.addEventListener('click', event => {
  const button = event.target.closest('[data-cart-action]');
  if (!button) return;
  const index = Number(button.dataset.index);
  const item = cart[index];
  if (!item) return;
  if (button.dataset.cartAction === 'increase') item.qty += 1;
  if (button.dataset.cartAction === 'decrease') item.qty -= 1;
  if (button.dataset.cartAction === 'remove' || item.qty <= 0) cart.splice(index, 1);
  saveCart();
  renderCart();
});

document.getElementById('checkoutButton').addEventListener('click', () => {
  if (cart.length === 0) {
    showToast('Nejprve přidej do košíku nějaký produkt.');
    return;
  }
  const email = document.getElementById('checkoutEmail').value.trim();
  if (!document.getElementById('checkoutEmail').reportValidity()) return;
  databaseReady.then(() => saveDemoOrder(email)).then(() => {
    cart = [];
    saveCart();
    renderCart();
    document.getElementById('checkoutEmail').value = '';
    showToast('Test objednávka uložena pouze v lokální databázi.');
  }).catch(error => showToast(error.message || 'Objednávku se nepodařilo uložit.'));
});

function saveDemoOrder(email) {
  return new Promise((resolve, reject) => {
    const tx = database.transaction(['orders', 'inventory'], 'readwrite');
    const inventoryStore = tx.objectStore('inventory');
    const getProductId = key => key.startsWith('custom-vx01-') ? 'vx01' : key.split('-')[0];
    const quantities = new Map();
    cart.forEach(item => {
      const productId = getProductId(item.key);
      quantities.set(productId, (quantities.get(productId) || 0) + item.qty);
    });
    const requests = [...quantities].map(([productId, qty]) => new Promise((done, fail) => {
      const request = inventoryStore.get(productId);
      request.onsuccess = () => done({ productId, qty, row: request.result });
      request.onerror = () => fail(request.error);
    }));
    Promise.all(requests).then(results => {
      const shortage = results.find(({ productId, qty, row }) => !row || row.stock < qty);
      if (shortage) {
        tx.abort();
        const product = products.find(item => item.id === shortage.productId);
        reject(new Error(`Nedostatečná zásoba: ${product ? product.name : shortage.productId}.`));
        return;
      }
      results.forEach(({ qty, row }) => inventoryStore.put({ ...row, stock: row.stock - qty }));
      tx.objectStore('orders').add({
        email,
        createdAt: new Date().toISOString(),
        total: cart.reduce((sum, item) => sum + item.price * item.qty, 0),
        items: cart.map(item => ({ productId: getProductId(item.key), name: item.name, qty: item.qty, unitPrice: item.price }))
      });
    }).catch(error => { tx.abort(); reject(error); });
    tx.oncomplete = () => loadInventory().then(resolve, reject);
    tx.onerror = () => reject(tx.error || new Error('Chyba při zápisu objednávky.'));
    tx.onabort = () => {};
  });
}

// Kosmetická konfigurace replikačního modelu
const basePrice = 3890;
const previewStage = document.getElementById('previewStage');
const previewCode = document.getElementById('previewCode');
const customTotal = document.getElementById('customTotal');
const customOptions = [
  { id: 'decalOption', price: 190, label: 'personalizovaná samolepka' },
  { id: 'patchOption', price: 250, label: 'textilní patch' },
  { id: 'caseOption', price: 490, label: 'ochranný přepravní obal' }
];

function updateCustomizer() {
  const color = document.querySelector('input[name="color"]:checked').value;
  const colorNames = { black: 'BLACK', olive: 'OLIVE DRAB', sand: 'DESERT SAND' };
  previewStage.dataset.color = color;
  previewCode.textContent = `FINISH: ${colorNames[color]}`;
  const optionsPrice = customOptions.reduce((sum, option) => sum + (document.getElementById(option.id).checked ? option.price : 0), 0);
  customTotal.textContent = currency(basePrice + optionsPrice);
}

document.querySelectorAll('input[name="color"]').forEach(input => input.addEventListener('change', updateCustomizer));
customOptions.forEach(option => document.getElementById(option.id).addEventListener('change', updateCustomizer));
document.getElementById('customForm').addEventListener('submit', event => {
  event.preventDefault();
  const color = document.querySelector('input[name="color"]:checked').value;
  const colorNames = { black: 'Matte Black', olive: 'Olive Drab', sand: 'Desert Sand' };
  const selected = customOptions.filter(option => document.getElementById(option.id).checked);
  const addOns = selected.map(option => option.label);
  const price = basePrice + selected.reduce((sum, option) => sum + option.price, 0);
  const details = `Povrch: ${colorNames[color]}${addOns.length ? ` · Doplňky: ${addOns.join(', ')}` : ' · Bez doplňků'}`;
  const key = `custom-vx01-${color}-${selected.map(option => option.id).join('-') || 'basic'}`;
  addToCart({ key, name: 'VX-01 — vlastní konfigurace', price, details });
});

document.getElementById('feedbackForm').addEventListener('submit', event => {
  event.preventDefault();
  const form = event.currentTarget;
  const email = form.elements.email.value.trim();
  const comment = form.elements.comment.value.trim();
  if (!email || !comment) return;
  databaseReady.then(() => new Promise((resolve, reject) => {
    const tx = database.transaction('feedback', 'readwrite');
    tx.objectStore('feedback').add({ email, comment, createdAt: new Date().toISOString() });
    tx.oncomplete = resolve;
    tx.onerror = () => reject(tx.error || new Error('Komentář se nepodařilo uložit.'));
  })).then(() => {
    form.reset();
    renderFeedback();
    showToast('Komentář uložen lokálně.');
  }).catch(error => showToast(error.message || 'Databáze není dostupná.'));
});

renderProducts();
renderCart();
updateCustomizer();
databaseReady.then(renderFeedback).catch(() => renderFeedback());
