<?php
session_start();
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito — Abarrotería Los Nina</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/logo/favicon-32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/img/logo/favicon-192.png">
    <link rel="apple-touch-icon" href="assets/img/logo/favicon-192.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-inner">
            <a href="index.php" class="navbar-logo">
                <picture>
                    <source type="image/webp" media="(min-width: 481px)" srcset="assets/img/logo/logo-320.webp 320w">
                    <source type="image/webp" media="(max-width: 480px)" srcset="assets/img/logo/logo-160.webp 160w">
                    <source type="image/png" media="(min-width: 481px)" srcset="assets/img/logo/logo-320.png 320w">
                    <source type="image/png" media="(max-width: 480px)" srcset="assets/img/logo/logo-160.png 160w">
                    <img src="assets/img/logo/logo-160.png" srcset="assets/img/logo/logo-160.png 160w, assets/img/logo/logo-320.png 320w" sizes="(max-width: 480px) 58px, 82px" width="82" height="56" alt="Abarrotería Los Nina" class="navbar-logo-img">
                </picture>
                Los Nina<span>.</span>
            </a>
            <button id="nav-toggle" class="nav-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="navbar-nav">☰</button>
            <div class="navbar-nav" id="navbar-nav">
                <a href="index.php">Inicio</a>
                <a href="inventario.php">Inventario</a>
                <a href="nosotros.php">Nosotros</a>
                <a href="contacto.php">Contacto</a>
                <a href="carrito.php" class="cart-link">🛒 <span class="cart-badge" id="cart-badge">0</span></a>
            </div>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <h1 class="section-title">Tu Carrito</h1>

            <div class="cart-layout">
                <div>
                    <div id="cart-items" class="cart-items">
                        <div id="cart-empty" style="display: none;">
                            <div style="background: #fff; padding: 3rem; border-radius: 12px; text-align: center;">
                                <p style="font-size: 1.2rem; margin-bottom: 1rem;">Tu carrito está vacío</p>
                                <a href="inventario.php" class="btn btn-primary">Ir a comprar</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="cart-summary">
                        <h3>Resumen de compra</h3>
                        <div class="cart-summary-row">
                            <span>Subtotal</span>
                            <span id="subtotal">S/ 0.00</span>
                        </div>
                        <div class="cart-summary-row">
                            <span>Descuento</span>
                            <span id="descuento">-S/ 0.00</span>
                        </div>
                        <div class="cart-summary-row total">
                            <span>Total final</span>
                            <span id="total-final">S/ 0.00</span>
                        </div>

                        <hr style="border: none; border-top: 1px solid #eee; margin: 1rem 0;">

                        <form id="coupon-form">
                            <label for="coupon-input">Código de cupón</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input class="input" type="text" id="coupon-input" placeholder="BIENVENIDO10">
                                <button type="submit" id="coupon-btn" class="btn btn-primary">Aplicar</button>
                            </div>
                            <p id="coupon-applied-info" class="text-muted" style="margin-top: 0.5rem; font-size: 0.9rem;"></p>
                        </form>

                        <hr style="border: none; border-top: 1px solid #eee; margin: 1rem 0;">

                        <form id="finalizar-form">
                            <button type="submit" id="finalizar-btn" class="btn btn-accent btn-lg btn-block">Finalizar compra</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer style="background-color: #1F2937; color: #fff; padding: 2rem 0; text-align: center;">
        <div class="container">
            <p>© <?= date('Y') ?> Abarrotería Los Nina — Tu tienda de barrio de confianza</p>
        </div>
    </footer>

    <button id="chat-fab" class="chat-fab" aria-label="Abrir chat">💬</button>
    <div id="chat-panel" class="chat-panel">
        <div class="chat-header">
            <h3>💬 Asistente Los Nina</h3>
            <button id="chat-close" class="chat-close" aria-label="Cerrar chat">✕</button>
        </div>
        <div id="chat-body" class="chat-body"></div>
        <div class="chat-footer">
            <input id="chat-input" type="text" placeholder="Escribe tu mensaje...">
            <button id="chat-send" class="btn btn-primary">➤</button>
        </div>
    </div>
    <div id="toast-container" class="toast-container"></div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/chatbot-widget.js"></script>
</body>
</html>
