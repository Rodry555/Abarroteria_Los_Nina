<?php
// Partial protegido: solo se incluye desde páginas de la app.
if (!defined('LOS_NINA_APP')) {
    http_response_code(404);
    exit;
}

$paginaActiva = $paginaActiva ?? '';

$enlaces = [
    'inicio'     => ['href' => 'index.php', 'texto' => 'Inicio'],
    'inventario' => ['href' => 'inventario.php', 'texto' => 'Inventario'],
    'nosotros'   => ['href' => 'nosotros.php', 'texto' => 'Nosotros'],
];
?>
<nav class="navbar" aria-label="Navegación principal">
    <div class="container navbar-inner">
        <a href="index.php" class="navbar-logo" aria-label="Abarrotería Los Nina — Ir al inicio">
            <picture>
                <source type="image/webp" media="(max-width: 480px)" srcset="assets/img/logo/logo-160.webp">
                <source type="image/webp" srcset="assets/img/logo/logo-320.webp">
                <source type="image/png" media="(max-width: 480px)" srcset="assets/img/logo/logo-160.png">
                <img src="assets/img/logo/logo-320.png"
                     width="76" height="52"
                     alt="Abarrotería Los Nina"
                     class="navbar-logo-img"
                     fetchpriority="high"
                     decoding="async">
            </picture>
        </a>

        <button id="nav-toggle" class="nav-toggle"
                aria-label="Abrir menú"
                aria-expanded="false"
                aria-controls="navbar-nav">☰</button>

        <div class="navbar-nav" id="navbar-nav">
            <?php foreach ($enlaces as $clave => $enlace): ?>
                <a href="<?= htmlspecialchars($enlace['href']) ?>"
                   class="<?= $paginaActiva === $clave ? 'is-active' : '' ?>"
                   <?= $paginaActiva === $clave ? 'aria-current="page"' : '' ?>>
                    <?= htmlspecialchars($enlace['texto']) ?>
                </a>
            <?php endforeach; ?>

            <a href="<?= e(whatsapp_enlace()) ?>" target="_blank" rel="noopener"
               class="btn btn-whatsapp" aria-label="Contactar por WhatsApp">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M15 11h.01"/><path d="M15 16h.01"/></svg>
                WhatsApp
            </a>

            <a href="carrito.php" class="cart-link<?= $paginaActiva === 'carrito' ? ' is-active' : '' ?>"
               aria-label="Ver carrito"
               <?= $paginaActiva === 'carrito' ? 'aria-current="page"' : '' ?>>
                🛒<span class="cart-badge" id="cart-badge">0</span>
            </a>

            <?php if (es_admin()): ?>
                <a href="admin.php" class="<?= $paginaActiva === 'admin' ? 'is-active' : '' ?>"
                   <?= $paginaActiva === 'admin' ? 'aria-current="page"' : '' ?>>Panel</a>
                <form method="POST" action="logout.php" class="nav-logout">
                    <?= csrf_campo() ?>
                    <button type="submit" class="nav-logout-btn">Salir</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</nav>
