<?php
// Abarrotería Los Nina — Conexión PDO (formato moderno con DATABASE_URL)
// En producción (Koyeb) las credenciales vienen de variables de entorno.
// Fallback local: si no hay DATABASE_URL, usa valores por defecto para desarrollo local.
//
// IMPORTANTE: este archivo debe guardarse en UTF-8 SIN BOM. Un BOM antes de
// <?php emite bytes en cada request, rompe los header('Location: ...') y deja
// el navegador en una página en blanco.

$DATABASE_URL = getenv('DATABASE_URL') ?: '';
$NVIDIA_API_KEY = getenv('NVIDIA_API_KEY') ?: '';

// Configuración local por defecto (solo si no hay DATABASE_URL)
if ($DATABASE_URL === '') {
    // Desarrollo local: usa SQLite en archivo local o MySQL local.
    // Descomenta la que uses.

    // Opción A: SQLite local (archivo local, sin servidor)
    $directorioDatos = __DIR__ . '/../data';
    if (!is_dir($directorioDatos)) {
        @mkdir($directorioDatos, 0775, true);
    }

    try {
        $pdo = new PDO('sqlite:' . $directorioDatos . '/local.db', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        error_log('Error de conexión BD local (SQLite): ' . $e->getMessage());
        http_response_code(500);
        die('No se pudo abrir la base de datos local. Crea la carpeta data/ o configura DATABASE_URL.');
    }

    // Opción B: MySQL/MariaDB local (descomenta y configura)
    /*
    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=3306;dbname=abarroteria;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    */
} else {

    // parse_url extrae host, puerto, usuario, password y nombre de BD de la URL de TiDB
    $p = parse_url($DATABASE_URL);
    $host = $p['host'];
    $port = $p['port'] ?? 4000;                 // TiDB usa puerto 4000, NO 3306
    $user = $p['user'] ?? '';
    $pass = $p['pass'] ?? '';
    $dbname = isset($p['path']) ? ltrim($p['path'], '/') : '';

    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                // TiDB Cloud exige SSL con certificado autofirmado:
                PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ]
        );
    } catch (PDOException $e) {
        error_log('Error de conexión BD: ' . $e->getMessage());
        http_response_code(500);
        die('No se pudo conectar a la base de datos. Revisa DATABASE_URL.');
    }
} // cierre del else

// Exponer la API key y la configuración de la API de NVIDIA para el chatbot
define('NVIDIA_API_KEY', $NVIDIA_API_KEY);
define('NVIDIA_URL', 'https://integrate.api.nvidia.com/v1/chat/completions');
define('NVIDIA_MODEL', 'nvidia/nemotron-3.5-super-vl-120b-a12b');

// Helpers compartidos: escapes, moneda, configuración, CSRF y sesión admin.
require_once __DIR__ . '/helpers.php';
