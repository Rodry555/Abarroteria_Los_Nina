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

// Obtener categorías para la sección de categorías
$categorias = [];
try {
    $stmt = $pdo->query('SELECT id, nombre FROM categorias ORDER BY id');
    $categorias = $stmt->fetchAll();
} catch (Exception $e) {
    $categorias = [];
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
                <h1 id="hero-title" class="hero-title"><span class="accent">Los Nina</span></h1>
                <p class="hero-sub">Abarrotería de barrio — todo lo que tu hogar necesita, en un solo lugar.</p>

                <div class="hero-actions">
                    <a href="inventario.php" class="btn btn-primary btn-lg">Ver productos</a>
                    <a href="contacto.php" class="btn btn-light btn-lg">Contáctanos</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Categorías -->
    <section class="section reveal">
        <div class="container">
            <div class="section-head">
                <span class="eyebrow">Categorías</span>
                <h2>Explora por departamento</h2>
                <p>Encuentra lo que necesitas en un clic.</p>
            </div>

            <div class="product-grid" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: var(--s-4);">
                <?php foreach ($categorias as $c): ?>
                    <a href="inventario.php?cat=<?= (int)$c['id'] ?>" class="product-card" style="flex-direction: row; align-items: center; padding: var(--s-4); gap: var(--s-3); text-decoration: none; color: inherit; min-height: auto;">
                        <div style="width: 56px; height: 56px; border-radius: var(--r-md); background: var(--red-50); display: grid; place-items: center; flex-shrink: 0;">
                            <?= product_icon_svg((int)$c['id']) ?>
                        </div>
                        <span style="font: 600 1rem var(--font-body); color: var(--ink); white-space: nowrap;"><?= htmlspecialchars($c['nombre']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Productos Destacados -->
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
                                <?= product_icon_svg((int)$p['categoria_id']) ?>
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

    <!-- Por qué elegirnos -->
    <section class="section reveal">
        <div class="container">
            <div class="section-head">
                <span class="eyebrow">Nuestra promesa</span>
                <h2>Por qué elegirnos</h2>
                <p>Tres razones para confiar en tu tienda de barrio.</p>
            </div>

            <div class="product-grid" style="grid-template-columns: repeat(3, 1fr); gap: var(--s-6);">
                <div class="card" style="text-align: center; padding: var(--s-8) var(--s-4);">
                    <div style="width: 56px; height: 56px; border-radius: var(--r-md); background: var(--red-50); display: grid; place-items: center; margin: 0 auto var(--s-4);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--red-600)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3 style="margin-bottom: var(--s-2);">Trato cercano</h3>
                    <p style="color: var(--ink-2);">Te conocemos por tu nombre y te atendemos como en casa.</p>
                </div>
                <div class="card" style="text-align: center; padding: var(--s-8) var(--s-4);">
                    <div style="width: 56px; height: 56px; border-radius: var(--r-md); background: var(--red-50); display: grid; place-items: center; margin: 0 auto var(--s-4);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--red-600)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a5 5 0 0 1 5 5c0 2.5-2 4.5-3 5.5-1 1-3 3-5 5s-4-2-5-5c-1-1-3-3-3-5.5a5 5 0 0 1 5-5z"/><path d="M9 12h6"/></svg>
                    </div>
                    <h3 style="margin-bottom: var(--s-2);">Productos frescos</h3>
                    <p style="color: var(--ink-2);">Seleccionamos lo mejor cada día para tu mesa.</p>
                </div>
                <div class="card" style="text-align: center; padding: var(--s-8) var(--s-4);">
                    <div style="width: 56px; height: 56px; border-radius: var(--r-md); background: var(--red-50); display: grid; place-items: center; margin: 0 auto var(--s-4);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--red-600)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                    <h3 style="margin-bottom: var(--s-2);">Cerca de ti</h3>
                    <p style="color: var(--ink-2);">Pedidos simples y entrega en el barrio, sin complicaciones.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Band -->
    <section class="section--alt reveal">
        <div class="container">
            <div class="cta-band">
                <h2 style="margin-bottom: var(--s-3);">¿Listo para tu pedido?</h2>
                <p style="max-width: 48ch; margin: 0 auto var(--s-6); opacity: .9;">Escríbenos por WhatsApp y te atendemos al instante.</p>
                <a href="<?= e(whatsapp_enlace()) ?>" target="_blank" rel="noopener noreferrer" class="btn" style="background: #fff; color: var(--red-700); font-weight: 700; border: none;">Pedidos por WhatsApp</a>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>