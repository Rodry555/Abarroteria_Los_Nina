<?php
define('LOS_NINA_APP', true);
session_start();
require_once __DIR__ . '/../config/database.php';

cabeceras_seguridad();

$titulo = 'Tu carrito — Abarrotería Los Nina';
$descripcion = 'Revisa tu carrito, aplica cupones y finaliza tu compra en Abarrotería Los Nina.';
$paginaActiva = 'carrito';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/navbar.php';
?>

<main>
    <section class="section reveal">
        <div class="container">
            <div class="section-head">
                <span class="eyebrow">Pedido</span>
                <h1>Tu carrito</h1>
                <p>Revisa tus productos, aplica un cupón y confirma tu pedido.</p>
            </div>

            <div class="cart-layout">
                <div>
                    <div id="cart-items" class="cart-items"></div>

                    <div id="cart-empty" class="empty-state" hidden>
                        <div class="empty-state-icon" aria-hidden="true">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--ink-3)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        </div>
                        <p>Tu carrito está vacío</p>
                        <a href="inventario.php" class="btn btn-primary">Ir a comprar</a>
                    </div>
                </div>

                <aside class="summary" aria-label="Resumen de compra">
                    <h3>Resumen de compra</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="subtotal"><?= e(formato_moneda(0)) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Descuento</span>
                        <span id="descuento">-<?= e(formato_moneda(0)) ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total final</span>
                        <span id="total-final" class="price"><?= e(formato_moneda(0)) ?></span>
                    </div>

                    <div class="coupon">
                        <form id="coupon-form">
                            <label for="coupon-input">Código de cupón</label>
                            <div style="display: flex; gap: var(--s-2); width: 100%;">
                                <input class="input" type="text" id="coupon-input" placeholder="BIENVENIDO10" autocomplete="off" style="flex: 1;">
                                <button type="submit" id="coupon-btn" class="btn btn-secondary">Aplicar</button>
                            </div>
                            <p id="coupon-applied-info" class="text-muted" style="margin-top: var(--s-2); font-size: var(--fs-sm);"></p>
                        </form>
                    </div>

                    <form id="finalizar-form">
                        <button type="submit" id="finalizar-btn" class="btn btn-primary btn-lg btn-block">Finalizar compra</button>
                    </form>
                </aside>
            </div>
        </div>
    </section>
</main>

<div id="purchase-success" class="purchase-modal" hidden>
    <div class="purchase-modal-backdrop" data-close-purchase></div>
    <div class="purchase-card"
         role="dialog"
         aria-modal="true"
         aria-labelledby="purchase-success-title"
         aria-describedby="purchase-success-desc">
        <span class="purchase-icon" aria-hidden="true">✓</span>
        <h2 id="purchase-success-title" class="purchase-title">¡Compra realizada con éxito!</h2>
        <p id="purchase-success-desc" class="purchase-desc">
            Registramos tu pedido. Nos pondremos en contacto para coordinar la entrega.
        </p>

        <dl class="purchase-details">
            <div class="purchase-detail">
                <dt>Pedido</dt>
                <dd id="purchase-order">—</dd>
            </div>
            <div class="purchase-detail">
                <dt>Total</dt>
                <dd id="purchase-total" class="price">—</dd>
            </div>
        </dl>

        <p id="purchase-discount" class="purchase-note" hidden></p>

        <div class="purchase-actions">
            <a href="inventario.php" class="btn btn-primary btn-lg">Seguir comprando</a>
            <a href="index.php" class="btn btn-secondary btn-lg">Volver al inicio</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>