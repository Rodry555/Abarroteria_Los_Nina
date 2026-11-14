<?php
define('LOS_NINA_APP', true);
session_start();
require_once __DIR__ . '/../config/database.php';

function formatPrice($value)
{
    return 'S/ ' . number_format((float) $value, 2, '.', '');
}

$productosDestacados = [];
try {
    $stmt = $pdo->query(
        'SELECT p.*, c.nombre AS categoria_nombre FROM productos p
         INNER JOIN categorias c ON p.categoria_id = c.id
         WHERE p.stock > 20
         ORDER BY p.stock DESC
         LIMIT 8'
    );
    $productosDestacados = $stmt->fetchAll();
} catch (Exception $e) {
    $productosDestacados = [];
}

$titulo = 'Abarrotería Los Nina — Tu tienda de barrio de confianza';
$descripcion = 'Abarrotes, lácteos, bebidas, limpieza y más con entrega en el barrio. Compra en línea y recibe asesoría por chat.';
$preloadHero = true;
$paginaActiva = 'inicio';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/navbar.php';
?>

<main>
    <section class="hero" aria-labelledby="hero-title">
        <picture class="hero-bg">
            <source media="(max-width: 600px)" type="image/webp" srcset="assets/img/hero/hero-mobile-480.webp 480w, assets/img/hero/hero-mobile-828.webp 828w" sizes="100vw">
            <source media="(max-width: 600px)" type="image/jpeg" srcset="assets/img/hero/hero-mobile-480.jpg 480w, assets/img/hero/hero-mobile-828.jpg 828w" sizes="100vw">
            <source media="(max-width: 1024px)" type="image/webp" srcset="assets/img/hero/hero-tablet-640.webp 640w, assets/img/hero/hero-tablet-960.webp 960w" sizes="100vw">
            <source media="(max-width: 1024px)" type="image/jpeg" srcset="assets/img/hero/hero-tablet-640.jpg 640w, assets/img/hero/hero-tablet-960.jpg 960w" sizes="100vw">
            <source type="image/webp" srcset="assets/img/hero/hero-desktop-640.webp 640w, assets/img/hero/hero-desktop-960.webp 960w, assets/img/hero/hero-desktop-1254.webp 1254w" sizes="100vw">
            <img src="assets/img/hero/hero-desktop-960.jpg"
                 srcset="assets/img/hero/hero-desktop-960.jpg 960w, assets/img/hero/hero-desktop-1254.jpg 1254w"
                 sizes="100vw"
                 alt="Productos de abarrotería: lácteos, limpieza y papel"
                 width="1254" height="705"
                 fetchpriority="high" decoding="async">
        </picture>

        <div class="container hero-content">
            <span class="hero-eyebrow">🏪 Tu tienda de barrio</span>
            <h1 id="hero-title" class="hero-title">Todo lo que necesitas, a un clic de tu puerta.</h1>
            <p class="hero-text">Abarrotes, lácteos, bebidas y productos de limpieza con la atención cercana de siempre. Pide por la web o pregúntale a nuestro asistente.</p>

            <div class="hero-actions">
                <a href="inventario.php" class="btn btn-primary btn-lg">Ver productos</a>
                <a href="contacto.php" class="btn btn-outline btn-lg">Contáctanos</a>
            </div>

            <div class="hero-stats">
                <div class="hero-stat">
                    <strong>+60</strong>
                    <span>productos disponibles</span>
                </div>
                <div class="hero-stat">
                    <strong>8</strong>
                    <span>categorías</span>
                </div>
                <div class="hero-stat">
                    <strong>100%</strong>
                    <span>atención de barrio</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title">Productos destacados</h2>
            <p class="section-subtitle">Una selección de lo más pedido esta semana.</p>

            <?php if (empty($productosDestacados)): ?>
                <p class="text-muted text-center">Todavía no hay productos destacados.</p>
            <?php else: ?>
                <div class="grid-products">
                    <?php foreach ($productosDestacados as $p): ?>
                        <article class="product-card cat-<?= (int) $p['categoria_id'] ?>">
                            <div class="product-card-img" aria-hidden="true"><?= htmlspecialchars($p['emoji']) ?></div>
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
            <?php endif; ?>

            <div class="text-center" style="margin-top: 2rem;">
                <a href="inventario.php" class="btn btn-primary btn-lg">Ver inventario completo</a>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
