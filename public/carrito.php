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
    <section class="section">
        <div class="container">
            <div class="page-header">
                <h1>Tu carrito</h1>
                <p>Revisa tus productos, aplica un cupón y confirma tu pedido.</p>
            </div>

            <div class="cart-layout">
                <div>
                    <div id="cart-items" class="cart-items"></div>

                    <div id="cart-empty" class="empty-state" hidden>
                        <div class="empty-state-icon" aria-hidden="true">🛒</div>
                        <p>Tu carrito está vacío</p>
                        <a href="inventario.php" class="btn btn-primary">Ir a comprar</a>
                    </div>
                </div>

                <aside class="cart-summary" aria-label="Resumen de compra">
                    <h3>Resumen de compra</h3>
                    <div class="cart-summary-row">
                        <span>Subtotal</span>
                        <span id="subtotal"><?= e(formato_moneda(0)) ?></span>
                    </div>
                    <div class="cart-summary-row">
                        <span>Descuento</span>
                        <span id="descuento">-<?= e(formato_moneda(0)) ?></span>
                    </div>
                    <div class="cart-summary-row total">
                        <span>Total final</span>
                        <span id="total-final"><?= e(formato_moneda(0)) ?></span>
                    </div>

                    <hr>

                    <form id="coupon-form">
                        <label for="coupon-input">Código de cupón</label>
                        <div class="coupon-row">
                            <input class="input" type="text" id="coupon-input" placeholder="BIENVENIDO10" autocomplete="off">
                            <button type="submit" id="coupon-btn" class="btn btn-primary">Aplicar</button>
                        </div>
                        <p id="coupon-applied-info" class="text-muted" style="margin-top: 0.5rem; font-size: 0.9rem;"></p>
                    </form>

                    <hr>

                    <form id="finalizar-form">
                        <button type="submit" id="finalizar-btn" class="btn btn-accent btn-lg btn-block">Finalizar compra</button>
                    </form>
                </aside>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
