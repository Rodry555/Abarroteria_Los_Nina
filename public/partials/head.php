<?php
// Partial protegido: solo se incluye desde páginas de la app.
if (!defined('LOS_NINA_APP')) {
    http_response_code(404);
    exit;
}

$titulo = $titulo ?? 'Abarrotería Los Nina';
$descripcion = $descripcion ?? 'Tu tienda de barrio de confianza: abarrotes, lácteos, bebidas y más.';
$preloadHero = $preloadHero ?? false;
$bodyClass = $bodyClass ?? '';
$noindex = $noindex ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <meta name="description" content="<?= htmlspecialchars($descripcion) ?>">
    <?php if ($noindex): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>

    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/logo/favicon-32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/img/logo/favicon-192.png">
    <link rel="apple-touch-icon" href="assets/img/logo/favicon-192.png">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php if ($preloadHero): ?>
        <link rel="preload" as="image" type="image/webp"
              href="assets/img/hero/hero-desktop-1033.webp" media="(min-width: 1025px)">
        <link rel="preload" as="image" type="image/webp"
              href="assets/img/hero/hero-mobile-621.webp" media="(max-width: 600px)">
    <?php endif; ?>

    <script>
        // Configuración compartida con el JavaScript (moneda y sesión).
        window.LOS_NINA = {
            moneda: <?= json_encode(simbolo_moneda(), JSON_UNESCAPED_UNICODE) ?>,
            esAdmin: <?= es_admin() ? 'true' : 'false' ?>
        };
    </script>
</head>
<body<?= $bodyClass !== '' ? ' class="' . htmlspecialchars($bodyClass) . '"' : '' ?>>
