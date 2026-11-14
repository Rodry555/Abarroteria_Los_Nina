<?php
// ============================================================
// Abarrotería Los Nina — Prepend automático (solo producción)
// Se ejecuta antes de cada request gracias a auto_prepend_file.
// Marca la cookie de sesión como Secure cuando la conexión es HTTPS,
// sin romper el desarrollo local sobre HTTP.
// ============================================================

if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
    ini_set('session.cookie_secure', '1');
}

// Detrás de un proxy (Koyeb) el esquema real llega en esta cabecera.
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
    ini_set('session.cookie_secure', '1');
}
