<?php
define('LOS_NINA_APP', true);
session_start();
require_once __DIR__ . '/../config/database.php';

$error = '';

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

    $error = 'Credenciales inválidas. Verifica tu email y contraseña.';
}

$titulo = 'Acceso administrador — Abarrotería Los Nina';
$descripcion = 'Acceso al panel de administración de Abarrotería Los Nina.';
$paginaActiva = 'login';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/navbar.php';
?>

<main>
    <section class="section">
        <div class="container auth-wrap">
            <div class="page-header">
                <h1>Acceso administrador</h1>
                <p>Solo personal autorizado de la tienda.</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="toast error show toast-inline" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="card form-stack">
                <div>
                    <label for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" autocomplete="username" required>
                </div>
                <div>
                    <label for="password">Contraseña</label>
                    <input class="input" type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Iniciar sesión</button>
            </form>

            <p class="text-muted text-center" style="margin-top: 1rem;">
                <a href="index.php">← Volver al inicio</a>
            </p>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
