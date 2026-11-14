<?php
session_start();
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nosotros — Abarrotería Los Nina</title>
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
        <div class="container" style="max-width: 720px;">
            <h1 class="section-title">¿Quiénes somos?</h1>
            <div style="background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <p>Abarrotería Los Nina nació como una tienda de barrio con un solo objetivo: <strong>llevar lo mejor de la abarrotería al alcance de tu puerta</strong>.</p>
                <p>Somos una familia apasionada por los productos frescos y de calidad. Trabajamos cada día para tener los mejores abarrotes, lácteos, bebidas y accesorios al alcance de todos.</p>
                <p>En Los Nina creemos en la confianza, el trato cercano y la calidad de lo que vendemos. ¡Bienvenidos a la familia!</p>
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
