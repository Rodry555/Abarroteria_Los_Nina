// Abarrotería Los Nina — main.js (carrito, UI, validaciones, fetch)
'use strict';

const CARRITO_KEY = 'carrito_los_nina';
const CUPON_KEY = 'cupon_los_nina';

function getCart() {
    try {
        const raw = localStorage.getItem(CARRITO_KEY);
        const cart = raw ? JSON.parse(raw) : [];
        return Array.isArray(cart) ? cart : [];
    } catch (error) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(CARRITO_KEY, JSON.stringify(cart));
}

function getCoupon() {
    try {
        const raw = localStorage.getItem(CUPON_KEY);
        const coupon = raw ? JSON.parse(raw) : null;
        return coupon && coupon.valido ? coupon : null;
    } catch (error) {
        return null;
    }
}

function saveCoupon(coupon) {
    if (coupon && coupon.valido) {
        localStorage.setItem(CUPON_KEY, JSON.stringify(coupon));
    } else {
        localStorage.removeItem(CUPON_KEY);
    }
}

function clearLocalCart() {
    saveCart([]);
    localStorage.removeItem(CUPON_KEY);
}

function addToCart(productId, nombre, emoji, precio, cantidad) {
    const cart = getCart();
    const existing = cart.find(function (item) {
        return item.id === productId;
    });
    if (existing) {
        existing.cantidad += cantidad;
    } else {
        cart.push({
            id: productId,
            nombre: nombre,
            emoji: emoji,
            precio: precio,
            cantidad: cantidad
        });
    }
    saveCart(cart);
    updateBadge();
    renderCart();
    showToast('Producto agregado al carrito', 'success');
}

function removeFromCart(productId) {
    const cart = getCart().filter(function (item) {
        return item.id !== productId;
    });
    saveCart(cart);
    renderCart();
    updateBadge();
}

function setCartQty(productId, cantidad) {
    const cart = getCart();
    const item = cart.find(function (i) {
        return i.id === productId;
    });
    if (!item) {
        return;
    }
    if (cantidad <= 0) {
        removeFromCart(productId);
        return;
    }
    item.cantidad = cantidad;
    saveCart(cart);
    renderCart();
    updateBadge();
}

function clearLocalCartState() {
    clearLocalCart();
    renderCart();
    updateBadge();
}

function updateBadge() {
    const cart = getCart();
    const totalItems = cart.reduce(function (sum, item) {
        return sum + (parseInt(item.cantidad) || 0);
    }, 0);
    document.querySelectorAll('.cart-badge').forEach(function (el) {
        el.textContent = totalItems;
    });
}

function formatPrice(value) {
    const num = parseFloat(value) || 0;
    const simbolo = (window.LOS_NINA && window.LOS_NINA.moneda) ? window.LOS_NINA.moneda : 'Bs';
    return simbolo + ' ' + num.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function showToast(mensaje, tipo) {
    const container = document.getElementById('toast-container');
    if (!container) {
        return;
    }
    const toast = document.createElement('div');
    toast.className = 'toast ' + (tipo || 'success') + ' show';
    toast.textContent = mensaje;
    container.appendChild(toast);
    setTimeout(function () {
        toast.classList.remove('show');
        setTimeout(function () { toast.remove(); }, 300);
    }, 3000);
}

function renderCart() {
    const cart = getCart();
    const container = document.getElementById('cart-items');
    const emptyEl = document.getElementById('cart-empty');
    if (!container) {
        return;
    }

    if (!cart || cart.length === 0) {
        container.innerHTML = '';
        if (emptyEl) {
            emptyEl.hidden = false;
        }
        setSummary(0, 0, 0);
        return;
    }

    if (emptyEl) {
        emptyEl.hidden = true;
    }

    let subtotal = 0;
    container.innerHTML = cart.map(function (item) {
        const cantidad = parseInt(item.cantidad) || 1;
        const precio = parseFloat(item.precio) || 0;
        subtotal += precio * cantidad;
        return '' +
            '<div class="cart-item" data-id="' + item.id + '">' +
                '<div class="cart-item-emoji">' + escapeHtml(item.emoji || '📦') + '</div>' +
                '<div class="cart-item-info">' +
                    '<span class="cart-item-name">' + escapeHtml(item.nombre) + '</span>' +
                    '<span class="cart-item-price">' + formatPrice(precio) + ' c/u</span>' +
                    '<div class="qty-control">' +
                        '<button type="button" class="qty-decrease" data-id="' + item.id + '" aria-label="Disminuir">−</button>' +
                        '<span>' + cantidad + '</span>' +
                        '<button type="button" class="qty-increase" data-id="' + item.id + '" aria-label="Aumentar">+</button>' +
                    '</div>' +
                '</div>' +
                '<div class="cart-item-actions">' +
                    '<span class="cart-item-subtotal">' + formatPrice(precio * cantidad) + '</span>' +
                    '<button type="button" class="btn btn-outline btn-sm remove-item" data-id="' + item.id + '">🗑️ Eliminar</button>' +
                '</div>' +
            '</div>';
    }).join('');

    const calculo = calcularDescuento(subtotal);
    const total = subtotal - calculo.descuento;
    setSummary(subtotal, calculo.descuento, total);

    const appliedInfo = document.getElementById('coupon-applied-info');
    if (appliedInfo) {
        appliedInfo.textContent = calculo.aviso;
    }
}

// Calcula el descuento respetando la compra mínima del cupón (igual que el servidor).
function calcularDescuento(subtotal) {
    const coupon = getCoupon();
    if (!coupon) {
        return { descuento: 0, aviso: '' };
    }

    const minimo = parseFloat(coupon.minimo_compra) || 0;
    if (subtotal < minimo) {
        return {
            descuento: 0,
            aviso: 'El cupón ' + coupon.codigo + ' requiere una compra mínima de ' + formatPrice(minimo) + '.'
        };
    }

    const descuento = (subtotal * (parseFloat(coupon.porcentaje_descuento) || 0)) / 100;

    return {
        descuento: descuento,
        aviso: 'Cupón ' + coupon.codigo + ' aplicado: ' + coupon.porcentaje_descuento + '% de descuento.'
    };
}

function setSummary(subtotal, descuento, total) {
    const subtotalEl = document.getElementById('subtotal');
    const descuentoEl = document.getElementById('descuento');
    const totalEl = document.getElementById('total-final');
    if (subtotalEl) {
        subtotalEl.textContent = formatPrice(subtotal);
    }
    if (descuentoEl) {
        descuentoEl.textContent = '-' + formatPrice(descuento);
    }
    if (totalEl) {
        totalEl.textContent = formatPrice(total);
    }
}

async function handleCoupon(event) {
    event.preventDefault();
    const input = document.getElementById('coupon-input');
    const codigo = (input && input.value ? input.value : '').trim().toUpperCase();
    const appliedInfo = document.getElementById('coupon-applied-info');
    if (!codigo) {
        showToast('Ingresa un código de cupón', 'warning');
        return;
    }
    const btn = document.getElementById('coupon-btn');
    if (btn) {
        btn.disabled = true;
    }
    try {
        const response = await fetch('chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion: 'validar_cupon', codigo: codigo })
        });
        const data = await response.json();
        if (data.valido) {
            saveCoupon(data);
            renderCart();
            const subtotalActual = getCart().reduce(function (sum, item) {
                return sum + (parseFloat(item.precio) || 0) * (parseInt(item.cantidad) || 1);
            }, 0);
            const minimo = parseFloat(data.minimo_compra) || 0;
            if (subtotalActual < minimo) {
                showToast('Cupón válido: agrega ' + formatPrice(minimo - subtotalActual) + ' más para aprovecharlo', 'warning');
            } else {
                showToast('Cupón aplicado: ' + data.porcentaje_descuento + '%', 'success');
            }
        } else {
            saveCoupon(null);
            if (appliedInfo) {
                appliedInfo.textContent = '';
            }
            showToast('Cupón inválido o no aplicable', 'error');
        }
    } catch (error) {
        showToast('Error al validar cupón', 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
        }
    }
}

async function handleFinalizar(event) {
    event.preventDefault();
    const cart = getCart();
    if (!cart || cart.length === 0) {
        showToast('Tu carrito está vacío', 'warning');
        return;
    }
    const btn = document.getElementById('finalizar-btn');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Procesando...';
    }
    try {
        const response = await fetch('compra-venta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items: cart, cupon: (getCoupon() || {}).codigo || '' })
        });
        const data = await response.json();
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Finalizar compra';
        }
        if (data.exito) {
            clearLocalCartState();
            showToast('¡Compra realizada con éxito!', 'success');
        } else {
            showToast(data.mensaje || 'Error al procesar la compra', 'error');
        }
    } catch (error) {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Finalizar compra';
        }
        showToast('Error de conexión al procesar la compra', 'error');
    }
}

function buildProductCard(p) {
    const stock = parseInt(p.stock) || 0;
    const stockClass = stock > 5 ? 'available' : (stock > 0 ? 'low' : 'out');
    const stockText = stock > 5 ? 'Disponible' : (stock > 0 ? '¡Últimas ' + stock + ' unidades!' : 'Agotado');
    const catId = parseInt(p.categoria_id) || 0;

    const safeId = parseInt(p.id) || 0;
    const addBtn = stock <= 0
        ? '<button class="btn btn-primary btn-add" disabled>Agotado</button>'
        : '<button class="btn btn-primary btn-add" data-id="' + safeId + '" data-nombre="' + escapeHtml(p.nombre) + '" data-emoji="' + escapeHtml(p.emoji || '') + '" data-precio="' + escapeHtml(p.precio) + '">Agregar al carrito</button>';

    const reserveBtn = stock > 0
        ? '<button class="btn btn-accent btn-reservar" data-id="' + safeId + '" data-cantidad="1" data-nombre="' + escapeHtml(p.nombre) + '">Reservar</button>'
        : '';

    return '' +
        '<article class="product-card cat-' + catId + '">' +
            '<div class="product-card-img" aria-hidden="true">' + escapeHtml(p.emoji || '📦') + '</div>' +
            '<div class="product-card-body">' +
                '<span class="product-category">' + escapeHtml(p.categoria_nombre || '') + '</span>' +
                '<h3 class="product-name">' + escapeHtml(p.nombre) + '</h3>' +
                '<div class="product-price">' + formatPrice(p.precio) + '</div>' +
                '<div class="product-stock ' + stockClass + '">' + stockText + '</div>' +
                '<div class="product-actions">' + addBtn + reserveBtn + '</div>' +
            '</div>' +
        '</article>';
}

function doSearch() {
    const grid = document.getElementById('products-grid');
    const searchInput = document.getElementById('search-input');
    const catButtons = document.getElementById('cat-buttons');
    const cat = catButtons ? catButtons.querySelector('.cat-filter.active') : null;
    const catId = cat ? cat.getAttribute('data-cat') : '0';
    const q = (searchInput && searchInput.value ? searchInput.value : '').trim();
    let url = 'inventario.php?ajax=1&q=' + encodeURIComponent(q);
    if (catId && catId !== '0') {
        url += '&cat=' + encodeURIComponent(catId);
    }
    fetch(url)
        .then(function (res) { return res.json(); })
        .then(function (products) {
            if (!grid) {
                return;
            }
            if (!products || products.length === 0) {
                grid.innerHTML = '<div class="text-center section">No se encontraron productos</div>';
                return;
            }
            grid.innerHTML = products.map(buildProductCard).join('');
        })
        .catch(function () {
            if (grid) { grid.innerHTML = '<div class="text-center section">Error al buscar productos</div>'; }
        });
}

function initSearch() {
    const searchInput = document.getElementById('search-input');
    if (!searchInput) {
        return;
    }
    let timer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { doSearch(); }, 300);
    });
}

function initCategoryFilters() {
    const container = document.getElementById('cat-buttons');
    if (!container) {
        return;
    }
    container.addEventListener('click', function (e) {
        const btn = e.target.closest('.cat-filter');
        if (!btn) {
            return;
        }
        container.querySelectorAll('.cat-filter').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        const catId = btn.getAttribute('data-cat');
        const searchInput = document.getElementById('search-input');
        const qVal = (searchInput && searchInput.value) ? searchInput.value : '';
        const url = 'inventario.php?ajax=1&q=' + encodeURIComponent(qVal) + (catId && catId !== '0' ? '&cat=' + encodeURIComponent(catId) : '');
        fetch(url)
            .then(function (res) { return res.json(); })
            .then(function (products) {
                const grid = document.getElementById('products-grid');
                if (!products || products.length === 0) {
                    grid.innerHTML = '<div class="text-center section">No se encontraron productos</div>';
                    return;
                }
                grid.innerHTML = products.map(buildProductCard).join('');
            });
    });
}

function initAddToCart() {
    document.addEventListener('click', function (e) {
        const addBtn = e.target.closest('.btn-add');
        if (addBtn) {
            e.preventDefault();
            addToCart(
                parseInt(addBtn.getAttribute('data-id')) || 0,
                addBtn.getAttribute('data-nombre') || '',
                addBtn.getAttribute('data-emoji') || '',
                addBtn.getAttribute('data-precio') || 0,
                1
            );
            return;
        }
        const resBtn = e.target.closest('.btn-reservar');
        if (resBtn) {
            e.preventDefault();
            if (window.LosNinaBot) {
                window.LosNinaBot.sendReservation(
                    parseInt(resBtn.getAttribute('data-id')) || 0,
                    parseInt(resBtn.getAttribute('data-cantidad')) || 1,
                    resBtn.getAttribute('data-nombre') || '',
                    resBtn.getAttribute('data-telefono') || ''
                );
            }
            return;
        }
        const decBtn = e.target.closest('.qty-decrease');
        if (decBtn) {
            e.preventDefault();
            const qtyEl = decBtn.parentElement ? decBtn.parentElement.querySelector('span') : null;
            const current = qtyEl ? (parseInt(qtyEl.textContent) || 1) : 1;
            setCartQty(parseInt(decBtn.getAttribute('data-id')) || 0, current - 1);
            return;
        }
        const incBtn = e.target.closest('.qty-increase');
        if (incBtn) {
            e.preventDefault();
            const qtyEl = incBtn.parentElement ? incBtn.parentElement.querySelector('span') : null;
            const current = qtyEl ? (parseInt(qtyEl.textContent) || 1) : 1;
            setCartQty(parseInt(incBtn.getAttribute('data-id')) || 0, current + 1);
            return;
        }
        const removeBtn = e.target.closest('.remove-item');
        if (removeBtn) {
            e.preventDefault();
            removeFromCart(parseInt(removeBtn.getAttribute('data-id')) || 0);
            return;
        }
    });
}

function initNavToggle() {
    const toggle = document.getElementById('nav-toggle');
    const nav = document.getElementById('navbar-nav');
    if (!toggle || !nav) {
        return;
    }

    function setOpen(isOpen) {
        nav.classList.toggle('open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.setAttribute('aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú');
    }

    toggle.addEventListener('click', function () {
        setOpen(!nav.classList.contains('open'));
    });

    nav.addEventListener('click', function (e) {
        if (e.target.closest('a')) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && nav.classList.contains('open')) {
            setOpen(false);
            toggle.focus();
        }
    });

    document.addEventListener('click', function (e) {
        if (!nav.classList.contains('open')) {
            return;
        }
        if (!nav.contains(e.target) && !toggle.contains(e.target)) {
            setOpen(false);
        }
    });
}

function init() {
    updateBadge();
    renderCart();
    initSearch();
    initCategoryFilters();
    initAddToCart();
    initNavToggle();

    const finalizarForm = document.getElementById('finalizar-form');
    if (finalizarForm) {
        finalizarForm.addEventListener('submit', handleFinalizar);
    }
    const couponForm = document.getElementById('coupon-form');
    if (couponForm) {
        couponForm.addEventListener('submit', handleCoupon);
    }
}

document.addEventListener('DOMContentLoaded', init);
