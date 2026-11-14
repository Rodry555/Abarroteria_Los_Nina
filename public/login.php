<?php
// ============================================================
// Abarrotería Los Nina — Login Admin
// ============================================================

session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email !== '' && $password !== '') {
        $stmt = $pdo->prepare('SELECT id, password_hash, rol FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $usuario['id'];
            $_SESSION['rol'] = $usuario['rol'];
            header('Location: inventario.php');
            exit;
        }
    }

    $error = 'Credenciales invalidas';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Abarrotería Los Nina</title>
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
                <a href="contacto.php">Contacto</a>
                <a href="carrito.php" class="cart-link">🛒 <span class="cart-badge" id="cart-badge">0</span></a>
            </div>
        </div>
    </nav>

    <section class="section">
        <div class="container" style="max-width: 480px;">
            <h1 class="section-title">Acceso Admin</h1>
            <?php if (isset($error)): ?>
                <div class="toast error show" style="position: static; transform: none;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php" style="background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div style="margin-bottom: 1rem;">
                    <label for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" required>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label for="password">Password</label>
                    <input class="input" type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Iniciar sesión</button>
            </form>
            <p class="text-muted text-center" style="margin-top: 1rem;">
                <a href="index.php">← Volver al inicio</a>
            </p>
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
