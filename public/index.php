<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function formatPrice($value) {
    return 'S/ ' . number_format((float) $value, 2, '.', '');
}

$productosDestacados = [];
try {
    $stmt = $pdo->query(
        'SELECT p.*, c.nombre AS categoria_nombre FROM productos p
         INNER JOIN categorias c ON p.categoria_id = c.id
         WHERE p.stock > 20
         ORDER BY p.stock DESC
         LIMIT 6'
    );
    $productosDestacados = $stmt->fetchAll();
} catch (Exception $e) {
    $productosDestacados = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abarrotería Los Nina — Tu tienda de barrio de confianza</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-inner">
            <a href="index.php" class="navbar-logo">Los Nina<span>.</span></a>
            <div class="navbar-nav">
                <a href="index.php">Inicio</a>
                <a href="inventario.php">Inventario</a>
                <a href="nosotros.php">Nosotros</a>
                <a href="contacto.php">Contacto</a>
                <a href="carrito.php" class="cart-link">🛒 <span class="cart-badge" id="cart-badge">0</span></a>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <h1>Abarrotería Los Nina</h1>
            <p>Tu tienda de barrio de confianza</p>
            <div class="text-center">
                <a href="inventario.php" class="btn btn-accent btn-lg">Ver productos</a>
                <a href="contacto.php" class="btn btn-outline btn-lg">Contáctanos</a>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title">Productos Destacados</h2>
            <?php if (empty($productosDestacados)): ?>
                <p class="text-muted text-center">Todavía no hay productos destacados.</p>
            <?php endif; ?>
            <div class="grid-products">
                <?php foreach ($productosDestacados as $p): ?>
                    <article class="product-card">
                        <div class="product-card-img"><?= htmlspecialchars($p['emoji']) ?></div>
                        <div class="product-card-body">
                            <span class="product-category"><?= htmlspecialchars($p['categoria_nombre']) ?></span>
                            <h3 class="product-name"><?= htmlspecialchars($p['nombre']) ?></h3>
                            <div class="product-price"><?= formatPrice($p['precio']) ?></div>
                            <div class="product-stock available">Disponible</div>
                            <div class="product-actions">
                                <button class="btn btn-primary btn-add"
                                    data-id="<?= (int) $p['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                    data-emoji="<?= htmlspecialchars($p['emoji']) ?>"
                                    data-precio="<?= htmlspecialchars($p['precio']) ?>">
                                    Agregar al carrito
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="text-center" style="margin-top: 2rem;">
                <a href="inventario.php" class="btn btn-primary btn-lg">Ver Inventario Completo</a>
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
