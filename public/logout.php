<?php
// ============================================================
// Abarrotería Los Nina — Cierre de sesión
// Solo por POST con token CSRF (evita que un enlace externo
// cierre la sesión del administrador).
// ============================================================

session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validar()) {
    header('Location: index.php');
    exit;
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Lax',
        ]
    );
}

session_destroy();

// Nueva sesión vacía solo para mostrar el aviso en la portada.
session_start();
session_regenerate_id(true);
flash('success', 'Sesión cerrada correctamente.');

header('Location: index.php');
exit;
