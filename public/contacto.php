<?php
// ============================================================
// Abarrotería Los Nina — Contacto (Formularios → guarda en BD)
// ============================================================

session_start();
require_once __DIR__ . '/../config/database.php';

$exito = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if ($nombre !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $mensaje !== '') {
        $stmt = $pdo->prepare('INSERT INTO mensajes_contacto (nombre, email, mensaje) VALUES (:nombre, :email, :mensaje)');
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':mensaje' => $mensaje,
        ]);
        $exito = true;
    } else {
        $error = 'Datos incompletos o email invalido';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto — Abarrotería Los Nina</title>
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
                <a href="carrito.php" class="cart-link">🛒 <span class="cart-badge" id="cart-badge">0</span></a>
            </div>
        </div>
    </nav>

    <section class="section">
        <div class="container" style="max-width: 640px;">
            <h1 class="section-title">Contacto</h1>
            <?php if ($exito): ?>
                <div class="toast success show" style="position: static; transform: none;">Mensaje enviado con exito. Te responderemos pronto.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="toast error show" style="position: static; transform: none;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="contacto.php" style="background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="margin-bottom: 1rem;">
                    <label for="nombre">Nombre</label>
                    <input class="input" type="text" id="nombre" name="nombre" required>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" required>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="mensaje">Mensaje</label>
                    <textarea class="input" id="mensaje" name="mensaje" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Enviar mensaje</button>
            </form>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>
