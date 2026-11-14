<?php
define('LOS_NINA_APP', true);
session_start();
require_once __DIR__ . '/../config/database.php';

cabeceras_seguridad();

$exito = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if (!csrf_validar()) {
        $error = 'El formulario venció. Vuelve a intentarlo.';
    } elseif ($nombre !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $mensaje !== '') {
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
    <section class="section reveal">
        <div class="container narrow-wrap">
            <div class="section-head">
                <span class="eyebrow">Escríbenos</span>
                <h1>Contacto</h1>
                <p>¿Tienes una consulta o quieres hacer un pedido? Escríbenos.</p>
            </div>

            <?php if ($exito): ?>
                <div class="toast toast-inline success show" role="status">Mensaje enviado con éxito. Te responderemos pronto.</div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="toast toast-inline error show" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="contacto.php" class="card" novalidate>
                <?= csrf_campo() ?>
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input class="input" type="text" id="nombre" name="nombre" autocomplete="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" autocomplete="email" required>
                </div>
                <div class="form-group">
                    <label for="mensaje">Mensaje</label>
                    <textarea class="input" id="mensaje" name="mensaje" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Enviar mensaje</button>
            </form>

            <?php
            $datosContacto = [
                'Teléfono' => config_get('telefono'),
                'WhatsApp' => config_get('whatsapp'),
                'Dirección' => config_get('direccion'),
                'Ciudad' => config_get('ciudad'),
                'Horario' => config_get('horario'),
            ];
            $datosContacto = array_filter($datosContacto, static function ($valor) {
                return trim((string) $valor) !== '';
            });
            ?>
            <?php if (!empty($datosContacto)): ?>
                <div class="card" style="margin-top: var(--s-6);">
                    <h3>Datos de la tienda</h3>
                    <ul style="list-style: none; margin-top: var(--s-3);">
                        <?php foreach ($datosContacto as $etiqueta => $valor): ?>
                            <li style="margin-bottom: var(--s-2);">
                                <strong><?= e($etiqueta) ?>:</strong>
                                <?php if ($etiqueta === 'WhatsApp'): ?>
                                    <a href="<?= e(whatsapp_enlace()) ?>" target="_blank" rel="noopener noreferrer" style="color: var(--wa); font-weight: 500;">
                                        <?= e($valor) ?>
                                    </a>
                                <?php else: ?>
                                    <?= e($valor) ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div style="margin-top: var(--s-4);">
                        <a href="<?= e(whatsapp_enlace()) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-wa btn-block">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <path d="M17.5 1.9C12.3 1.9 8.1 4.6 5.2 8.6L2 22l13.4-3.2C21.4 19.5 22 13.7 22 8.5 22 3.3 17.7 1.9 17.5 1.9z" fill="currentColor"/>
                                <path d="M15 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-3 4a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" fill="#fff"/>
                            </svg>
                            Escribir por WhatsApp
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>