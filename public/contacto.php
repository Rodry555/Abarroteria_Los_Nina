<?php
define('LOS_NINA_APP', true);
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
        $error = 'Revisa los datos: nombre, email válido y mensaje son obligatorios.';
    }
}

$titulo = 'Contacto — Abarrotería Los Nina';
$descripcion = 'Escríbenos tus consultas, pedidos o sugerencias. En Abarrotería Los Nina te respondemos pronto.';
$paginaActiva = 'contacto';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/navbar.php';
?>

<main>
    <section class="section">
        <div class="container narrow-wrap">
            <div class="page-header">
                <h1>Contacto</h1>
                <p>¿Tienes una consulta o quieres hacer un pedido? Escríbenos.</p>
            </div>

            <?php if ($exito): ?>
                <div class="toast success show toast-inline" role="status">Mensaje enviado con éxito. Te responderemos pronto.</div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="toast error show toast-inline" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="contacto.php" class="card form-stack" novalidate>
                <div>
                    <label for="nombre">Nombre</label>
                    <input class="input" type="text" id="nombre" name="nombre" autocomplete="name" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" autocomplete="email" required>
                </div>
                <div>
                    <label for="mensaje">Mensaje</label>
                    <textarea class="input" id="mensaje" name="mensaje" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Enviar mensaje</button>
            </form>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
