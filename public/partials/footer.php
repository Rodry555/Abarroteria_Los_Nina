<?php
// Partial protegido: solo se incluye desde páginas de la app.
if (!defined('LOS_NINA_APP')) {
    http_response_code(404);
    exit;
}
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <img src="assets/img/logo/logo-160.png"
                     width="64" height="64"
                     alt="Abarrotería Los Nina"
                     class="footer-logo"
                     loading="lazy"
                     decoding="async">
                <div>
                    <strong><?= e(config_get('negocio_nombre', 'Abarrotería Los Nina')) ?></strong>
                    <p><?= e(config_get('negocio_eslogan', 'Tu tienda de barrio de confianza')) ?></p>
                </div>
            </div>

            <div class="footer-col">
                <h4>Tienda</h4>
                <a href="inventario.php">Inventario</a>
                <a href="carrito.php">Carrito</a>
                <a href="nosotros.php">Nosotros</a>
                <a href="contacto.php">Contacto</a>
            </div>

            <div class="footer-col">
                <h4>Atención</h4>
                <p><?= e(config_get('horario', 'Lun a Sáb · 8:00 – 21:00')) ?></p>
                <?php if (config_get('direccion') !== ''): ?>
                    <p><?= e(config_get('direccion')) ?></p>
                <?php endif; ?>
                <?php if (config_get('ciudad') !== ''): ?>
                    <p><?= e(config_get('ciudad')) ?></p>
                <?php endif; ?>
                <?php if (config_get('telefono') !== ''): ?>
                    <p><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', config_get('telefono'))) ?>">📞 <?= e(config_get('telefono')) ?></a></p>
                <?php endif; ?>
                <?php if (config_get('whatsapp') !== ''): ?>
                    <p><a href="<?= e(whatsapp_enlace()) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-wa btn-sm" style="width: 100%; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                            <path d="M17.5 1.9C12.3 1.9 8.1 4.6 5.2 8.6L2 22l13.4-3.2C21.4 19.5 22 13.7 22 8.5 22 3.3 17.7 1.9 17.5 1.9z" fill="currentColor"/>
                            <path d="M15 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-3 4a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" fill="#fff"/>
                        </svg>
                        <?= e(config_get('whatsapp')) ?>
                    </a></p>
                <?php endif; ?>
                <p>Pedidos por el asistente 💬</p>
            </div>
        </div>

        <div class="footer-bottom">
            © <?= date('Y') ?> Abarrotería Los Nina — Hecho con cariño para el barrio.
        </div>
    </div>
</footer>

<button id="chat-fab" class="chat-fab" aria-label="Abrir asistente de chat">💬</button>
<div id="chat-panel" class="chat-panel" role="dialog" aria-modal="false" aria-label="Asistente Los Nina">
    <div class="chat-header">
        <h3><span class="chat-avatar" aria-hidden="true">🛒</span> Asistente Los Nina</h3>
        <button id="chat-close" class="chat-close" aria-label="Cerrar chat">✕</button>
    </div>
    <div id="chat-body" class="chat-body" aria-live="polite" aria-relevant="additions"></div>
    <div class="chat-footer">
        <input id="chat-input" class="input" type="text" placeholder="Escribe tu mensaje…" aria-label="Mensaje para el asistente">
        <button id="chat-send" class="btn btn-primary" aria-label="Enviar mensaje">➤</button>
    </div>
</div>

<div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="false"></div>

<script src="assets/js/main.js"></script>
<script src="assets/js/chatbot-widget.js"></script>
</body>
</html>
