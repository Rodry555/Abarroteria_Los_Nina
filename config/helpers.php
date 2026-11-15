<?php
// ============================================================
// Abarrotería Los Nina — Helpers compartidos
// Escapes, moneda, configuración editable, CSRF y sesión admin.
// Se carga automáticamente desde config/database.php.
// ============================================================

/**
 * Escapa texto para HTML. Atajo de htmlspecialchars con UTF-8 y comillas.
 */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Lee un valor de la tabla configuracion (clave/valor) con cache por request.
 * Si la tabla no existe o la clave no está, devuelve el valor por defecto.
 */
function config_get(string $clave, string $porDefecto = ''): string
{
    global $pdo;
    static $cache = null;

    if ($cache === null) {
        $cache = [];
        if (isset($pdo) && $pdo instanceof PDO) {
            try {
                $filas = $pdo->query('SELECT clave, valor FROM configuracion')->fetchAll();
                foreach ($filas as $fila) {
                    $cache[$fila['clave']] = (string) $fila['valor'];
                }
            } catch (Throwable $e) {
                // La tabla aún no existe (migración pendiente): seguimos con defaults.
                $cache = [];
            }
        }
    }

    if (array_key_exists($clave, $cache) && trim($cache[$clave]) !== '') {
        return $cache[$clave];
    }

    return $porDefecto;
}

/**
 * Símbolo de moneda del negocio. Configurable desde el panel admin.
 */
function simbolo_moneda(): string
{
    return config_get('moneda_simbolo', 'Bs');
}

/**
 * Formatea un monto: "Bs 1,234.50".
 * Punto decimal y coma de miles para que coincida con main.js.
 */
function formato_moneda($valor): string
{
    return simbolo_moneda() . ' ' . number_format((float) $valor, 2, '.', ',');
}

/**
 * Normaliza un monto escrito por el usuario (acepta "12,50" y "12.50").
 */
function monto_desde_input($valor): float
{
    $texto = trim((string) $valor);
    $texto = str_replace([' ', 'Bs', 'bs'], '', $texto);

    // Si hay coma y no hay punto, la coma es separador decimal.
    if (strpos($texto, ',') !== false && strpos($texto, '.') === false) {
        $texto = str_replace(',', '.', $texto);
    } else {
        // Si hay ambos, el último separador manda; asumimos formato 1.234,56.
        if (strpos($texto, ',') !== false) {
            $texto = str_replace('.', '', $texto);
            $texto = str_replace(',', '.', $texto);
        }
    }

    return max(0.0, (float) $texto);
}

/**
 * Devuelve solo dígitos del número de WhatsApp configurado.
 * Quita espacios, guiones, paréntesis y el signo '+'.
 * Default: +591 64049678 (Bolivia).
 */
function whatsapp_numero(): string
{
    $raw = config_get('whatsapp', '+591 64049678');
    return preg_replace('/[^0-9]/', '', $raw);
}

/**
 * Genera el enlace wa.me con mensaje precargado.
 * $mensaje opcional; si se omite usa un default amigable.
 */
function whatsapp_enlace(?string $mensaje = null): string
{
    $numero = whatsapp_numero();
    $texto = $mensaje ?? 'Hola, quiero hacer un pedido en Abarrotería Los Nina.';
    return 'https://wa.me/' . $numero . '?text=' . rawurlencode($texto);
}

/**
 * Token CSRF de la sesión (se genera en el primer uso).
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Input oculto listo para insertar dentro de un <form>.
 */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Valida el token CSRF enviado por POST.
 */
function csrf_validar(): bool
{
    $enviado = $_POST['csrf_token'] ?? '';
    $esperado = $_SESSION['csrf_token'] ?? '';

    return is_string($enviado) && $enviado !== '' && $esperado !== '' && hash_equals($esperado, $enviado);
}

/**
 * ¿La sesión actual es de un administrador?
 */
function es_admin(): bool
{
    return ($_SESSION['rol'] ?? '') === 'admin';
}

/**
 * Exige sesión de administrador. Corta la ejecución si no la hay.
 */
function requerir_admin(): void
{
    if (!es_admin()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Guarda un mensaje flash para mostrarlo después de un redirect (PRG).
 */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/**
 * Devuelve y limpia los mensajes flash pendientes.
 */
function flash_obtener(): array
{
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return is_array($mensajes) ? $mensajes : [];
}

/**
 * Cabeceras de seguridad comunes. Debe llamarse antes de imprimir HTML.
 */
function cabeceras_seguridad(bool $noIndex = false): void
{
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');

    if ($noIndex) {
        header('X-Robots-Tag: noindex, nofollow');
    }
}

/**
 * Icono SVG de línea para categoría de producto.
 * Estilo: trazo 1.75px, color --red-600, 56px, centrado.
 * Categorías: 1=Abarrotes, 2=Lácteos, 3=Bebidas, 4=Limpieza, 5=Higiene, 6=Snacks, 7=Enlatados, 8=Panadería
 */
function product_icon_svg(int $categoriaId): string
{
    $icons = [
        1 => '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l2.5-2.5L12 3l6.5 6.5L21 9"/><path d="M5 18c0 2.2 1.8 4 4 4h6c2.2 0 4-1.8 4-4v-5"/></svg>', // Abarrotes - grano/saco
        2 => '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3v18"/><path d="M10 3v18"/><path d="M14 3v18"/><path d="M18 3v18"/><rect x="4" y="3" width="16" height="18" rx="2"/></svg>', // Lácteos - botellas de leche
        3 => '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v6"/><path d="M9 6h6"/><path d="M6 12c0-2.5 2-4.5 5-4.5s5 2 5 4.5"/><path d="M6 18c0 2.5 2 4.5 5 4.5s5-2 5-4.5"/></svg>', // Bebidas - vasos
        4 => '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M12 3v6"/><path d="M7 15l5-5 5 5"/></svg>', // Limpieza - spray
        5 => '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a5 5 0 0 1 5 5c0 2.5-2 4.5-3 5.5-1 1-3 3-5 5s-4-2-5-5c-1-1-3-3-3-5.5a5 5 0 0 1 5-5z"/><path d="M9 12h6"/></svg>', // Higiene - jabón/champú
        6 => '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 9v6"/><path d="M9 12h6"/></svg>', // Snacks - paquete/bolsa
        7 => '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 12h10"/><path d="M12 8v8"/></svg>', // Enlatados - lata
        8 => '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 18c0-3 2.5-5.5 5-6 2.5.5 5 3 5 6"/><path d="M16 12c0 3-2.5 5.5-5 6-2.5-.5-5-3-5-6"/></svg>', // Panadería - panes
    ];
    return $icons[$categoriaId] ?? $icons[1];
}
