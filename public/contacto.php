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
            <a href="index.php" class="navbar-logo">
                <img src="assets/img/logo.jpeg" alt="Los Nina" class="navbar-logo-img">
                Los Nina<span>.</span>
            </a>
            <button id="nav-toggle" class="nav-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="navbar-nav">☰</button>
            <div class="navbar-nav" id="navbar-nav">
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

    <footer style="background-color: #1F2937; color: #fff; padding: 2rem 0; text-align: center;">
        <div class="container">
            <p>© <?= date('Y') ?> Abarrotería Los Nina — Tu tienda de barrio de confianza</p>
        </div>
    </footer>

    <button id="chat-fab" class="chat-fab" aria-label="Abrir chat">💬</button>
    <div id="chat-panel" class="chat-panel">
        <div class="chat-header">
            <h3>💬 Asistente Los Nina</h3>
            <button id="chat-close" class="chat-close" aria-label="Cerrar chat">✕</button>
        </div>
        <div id="chat-body" class="chat-body"></div>
        <div class="chat-footer">
            <input id="chat-input" type="text" placeholder="Escribe tu mensaje...">
            <button id="chat-send" class="btn btn-primary">➤</button>
        </div>
    </div>
    <div id="toast-container" class="toast-container"></div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/chatbot-widget.js"></script>
</body>
</html>
