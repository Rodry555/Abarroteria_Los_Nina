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
    'contacto'   => ['href' => 'contacto.php', 'texto' => 'Contacto'],
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

            <a href="carrito.php" class="cart-link<?= $paginaActiva === 'carrito' ? ' is-active' : '' ?>"
               aria-label="Ver carrito"
               <?= $paginaActiva === 'carrito' ? 'aria-current="page"' : '' ?>>
                🛒<span class="cart-badge" id="cart-badge">0</span>
            </a>
        </div>
    </div>
</nav>
