<?php
define('LOS_NINA_APP', true);
session_start();
require_once __DIR__ . '/../config/database.php';

cabeceras_seguridad(true);

// Si ya hay sesión de admin, no tiene sentido mostrar el login.
if (es_admin()) {
    header('Location: admin.php');
    exit;
}

$error = '';
$maxIntentos = 5;
$ventanaMinutos = 15;

/** Cuenta los intentos fallidos recientes de una IP (si la tabla existe). */
function intentos_recientes(PDO $pdo, string $ip, int $ventanaMinutos): int
{
    try {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM intentos_login
             WHERE ip = :ip AND exito = 0 AND fecha > (NOW() - INTERVAL ' . (int) $ventanaMinutos . ' MINUTE)'
        );
        $stmt->execute([':ip' => $ip]);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        // Tabla todavía no migrada: no bloqueamos el acceso.
        return 0;
    }
}

/** Registra el resultado de un intento de login (si la tabla existe). */
function registrar_intento(PDO $pdo, string $ip, string $email, bool $exito): void
{
    try {
        $pdo->prepare('INSERT INTO intentos_login (ip, email, exito) VALUES (:ip, :email, :exito)')
            ->execute([':ip' => $ip, ':email' => $email, ':exito' => $exito ? 1 : 0]);
    } catch (Throwable $e) {
        // Silencioso: el login no debe romperse por una tabla auxiliar.
    }
}

$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validar()) {
        $error = 'El formulario venció. Vuelve a intentarlo.';
    } elseif (intentos_recientes($pdo, $ip, $ventanaMinutos) >= $maxIntentos) {
        $error = 'Demasiados intentos fallidos. Espera ' . $ventanaMinutos . ' minutos antes de volver a probar.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email !== '' && $password !== '') {
            $stmt = $pdo->prepare('SELECT id, password_hash, rol FROM usuarios WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                // Limpiar los fallos previos de esta IP.
                try {
                    $pdo->prepare('DELETE FROM intentos_login WHERE ip = :ip')->execute([':ip' => $ip]);
                } catch (Throwable $e) {
                    // Ignorar: la tabla puede no existir.
                }

                session_regenerate_id(true);
                unset($_SESSION['csrf_token']);
                $_SESSION['usuario_id'] = (int) $usuario['id'];
                $_SESSION['rol'] = $usuario['rol'];

                header('Location: admin.php');
                exit;
            }

            registrar_intento($pdo, $ip, $email, false);
            usleep(400000); // Medio segundo: frena la fuerza bruta.
        }

        $error = 'Credenciales inválidas. Verifica tu email y contraseña.';
    }
}

$titulo = 'Acceso administrador — Abarrotería Los Nina';
$descripcion = 'Acceso al panel de administración de Abarrotería Los Nina.';
$paginaActiva = 'login';
$noindex = true;
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/navbar.php';
?>

<main>
    <section class="section reveal">
        <div class="container auth-wrap">
            <div class="section-head" style="text-align: center;">
                <span class="eyebrow">Área privada</span>
                <h1>Acceso administrador</h1>
                <p>Solo personal autorizado de la tienda.</p>
            </div>

            <?php foreach (flash_obtener() as $mensaje): ?>
                <div class="toast toast-inline <?= $mensaje['tipo'] === 'error' ? 'error' : 'success' ?> show"
                     role="<?= $mensaje['tipo'] === 'error' ? 'alert' : 'status' ?>">
                    <?= e($mensaje['mensaje']) ?>
                </div>
            <?php endforeach; ?>

            <?php if ($error !== ''): ?>
                <div class="toast toast-inline error show" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="card">
                <?= csrf_campo() ?>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input class="input" type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Iniciar sesión</button>
            </form>

            <p class="text-muted text-center" style="margin-top: var(--s-4);">
                <a href="index.php">← Volver al inicio</a>
            </p>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>