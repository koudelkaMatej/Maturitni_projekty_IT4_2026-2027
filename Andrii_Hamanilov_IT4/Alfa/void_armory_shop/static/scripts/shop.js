'use strict';

const products = [
  { id: 'vx01', name: 'VX-01 Airsoft karabina', category: 'replica', categoryLabel: 'AIRSOFT REPLIKA', price: 3890, tag: 'TOP VOLBA', description: 'Airsoftová replika s moderním vzhledem. Vhodná jako základ pro kosmetickou personalizaci.' },
  { id: 'px9', name: 'PX-9 Airsoft pistole', category: 'replica', categoryLabel: 'AIRSOFT REPLIKA', price: 1590, tag: '', description: 'Kompaktní airsoftová replika pro rekreační hru na schváleném hřišti.' },
  { id: 'eyepro', name: 'Spectra ochranné brýle', category: 'gear', categoryLabel: 'OCHRANNÉ VYBAVENÍ', price: 690, tag: 'SAFETY', description: 'Ochranné brýle určené pro airsoft. Před použitím ověř kompatibilitu s pravidly hřiště.' },
  { id: 'sling', name: 'Urban textilní popruh', category: 'gear', categoryLabel: 'VYBAVENÍ', price: 450, tag: '', description: 'Textilní doplněk pro vzhled a přenášení airsoftové repliky.' },
  { id: 'skin', name: 'Custom skin kit', category: 'custom', categoryLabel: 'KOSMETICKÝ DOPLNĚK', price: 320, tag: 'CUSTOM', description: 'Sada odnímatelných dekorativních samolepek pro osobitý vzhled vybavení.' },
  { id: 'patch', name: 'Patch set // VOID', category: 'custom', categoryLabel: 'KOSMETICKÝ DOPLNĚK', price: 250, tag: '', description: 'Sada textilních nášivek pro batoh, pouzdro nebo oblečení.' }
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
  showToast('Demo objednávka: nebyla odeslána ani zaplacena.');
});

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

renderProducts();
renderCart();
updateCustomizer();
