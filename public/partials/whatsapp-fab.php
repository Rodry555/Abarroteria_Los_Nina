<?php
// Partial protegido: solo se incluye desde páginas de la app.
if (!defined('LOS_NINA_APP')) {
    http_response_code(404);
    exit;
}
?>
<a id="whatsapp-fab"
   class="whatsapp-fab"
   href="<?= e(whatsapp_enlace()) ?>"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Escribir por WhatsApp">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
        <path d="M17.5 1.9C12.3 1.9 8.1 4.6 5.2 8.6L2 22l13.4-3.2C21.4 19.5 22 13.7 22 8.5 22 3.3 17.7 1.9 17.5 1.9z" fill="currentColor"/>
        <path d="M15 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-3 4a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" fill="#fff"/>
    </svg>
</a>