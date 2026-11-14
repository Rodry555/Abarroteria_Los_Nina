<?php
define('LOS_NINA_APP', true);
session_start();
require_once __DIR__ . '/../config/database.php';

cabeceras_seguridad();

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
    <?php $mensajesFlash = flash_obtener(); ?>
    <?php if (!empty($mensajesFlash)): ?>
        <div class="container" style="padding-top: 1rem;">
            <?php foreach ($mensajesFlash as $mensaje): ?>
                <div class="toast toast-inline <?= $mensaje['tipo'] === 'error' ? 'error' : 'success' ?> show"
                     role="<?= $mensaje['tipo'] === 'error' ? 'alert' : 'status' ?>">
                    <?= e($mensaje['mensaje']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="hero" id="inicio" aria-labelledby="hero-title">
        <picture class="hero-bg">
            <!-- Móvil (≤600 px): recorte vertical 4:5 -->
            <source media="(max-width: 600px)" type="image/webp"
                    srcset="assets/img/hero/hero-mobile-828.webp">
            <source media="(max-width: 600px)"
                    srcset="assets/img/hero/hero-mobile-828.jpg">
            <!-- Tablet (601–1024 px): recorte 4:3 -->
            <source media="(max-width: 1024px)" type="image/webp"
                    srcset="assets/img/hero/hero-tablet-1280.webp">
            <source media="(max-width: 1024px)"
                    srcset="assets/img/hero/hero-tablet-1280.jpg">
            <!-- Escritorio (>1024 px): recorte 16:9 con dos densidades -->
            <source type="image/webp"
                    srcset="assets/img/hero/hero-desktop-1600.webp 1600w,
                            assets/img/hero/hero-desktop-2066.webp 2066w" sizes="100vw">
            <img src="assets/img/hero/hero-desktop-1600.jpg"
                 srcset="assets/img/hero/hero-desktop-1600.jpg 1600w,
                         assets/img/hero/hero-desktop-2066.jpg 2066w"
                 sizes="100vw"
                 alt="Estantes de la Abarrotería Los Nina con papel higiénico, limpieza y abarrotes"
                 width="2066" height="1162"
                 fetchpriority="high" decoding="async">
        </picture>

        <div class="hero-inner">
            <div class="hero-content">
                <h1 id="hero-title" class="hero-title">Los Nina<span class="accent">.</span></h1>
                <p class="hero-sub">Abarrotería de barrio — todo lo que tu hogar necesita, en un solo lugar.</p>

                <div class="hero-actions">
                    <a href="inventario.php" class="btn btn-primary btn-lg">Ver productos</a>
                    <a href="contacto.php" class="btn btn-light btn-lg">Contáctanos</a>
                </div>
            </div>
        </div>
    </section>

    <section class="section reveal">
        <div class="container">
            <div class="section-head">
                <span class="eyebrow">Destacados</span>
                <h2>Lo que más se lleva en casa</h2>
                <p>Productos de uso diario al mejor precio.</p>
            </div>

            <?php if (empty($productosDestacados)): ?>
                <p class="text-muted text-center">Todavía no hay productos destacados.</p>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($productosDestacados as $p): ?>
                        <article class="product-card">
                            <div class="product-media">
                                <span class="badge badge-ok">Disponible</span>
                                <span aria-hidden="true" style="font-size: 3.5rem;"><?= htmlspecialchars($p['emoji']) ?></span>
                            </div>
                            <div class="product-body">
                                <h3 class="product-name"><?= htmlspecialchars($p['nombre']) ?></h3>
                                <p class="product-meta"><?= htmlspecialchars($p['categoria_nombre']) ?></p>
                                <p class="price"><small><?= e(simbolo_moneda()) ?></small><?= e(number_format((float)$p['precio'], 2, '.', '')) ?></p>
                                <button class="btn btn-primary btn-block btn-add"
                                    data-id="<?= (int) $p['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                    data-emoji="<?= htmlspecialchars($p['emoji']) ?>"
                                    data-precio="<?= htmlspecialchars($p['precio']) ?>">
                                    Agregar al carrito
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="text-center" style="margin-top: var(--s-8);">
                <a href="inventario.php" class="btn btn-secondary btn-lg">Ver inventario completo</a>
            </div>
        </div>
    </section>

    <!-- CTA Band -->
    <section class="section--alt reveal">
        <div class="container">
            <div class="cta-band">
                <h2 style="margin-bottom: var(--s-3);">¿Listo para tu pedido?</h2>
                <p style="max-width: 48ch; margin: 0 auto var(--s-6); opacity: .9;">Escríbenos por WhatsApp y te atendemos al instante.</p>
                <a href="<?= e(whatsapp_enlace()) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-light btn-lg">Pedidos por WhatsApp</a>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>