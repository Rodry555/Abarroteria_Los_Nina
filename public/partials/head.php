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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <meta name="description" content="<?= htmlspecialchars($descripcion) ?>">

    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/logo/favicon-32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/img/logo/favicon-192.png">
    <link rel="apple-touch-icon" href="assets/img/logo/favicon-192.png">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php if ($preloadHero): ?>
        <link rel="preload" as="image" type="image/webp" href="assets/img/hero/hero-desktop-960.webp" media="(min-width: 1025px)">
    <?php endif; ?>
</head>
<body<?= $bodyClass !== '' ? ' class="' . htmlspecialchars($bodyClass) . '"' : '' ?>>
