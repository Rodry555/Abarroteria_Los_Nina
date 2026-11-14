<?php
// Abarrotería Los Nina — Conexión PDO (formato moderno con DATABASE_URL)
// En producción (Koyeb) las credenciales vienen de variables de entorno.
// El fallback con valores vacíos es SOLO para no romper si alguien prueba local.

$DATABASE_URL = getenv('DATABASE_URL') ?: 'mysql://XN6UxpJ9uFm6YyX.root:XLAzdJAIKZqL3iVo@gateway01.sa-east-1.prod.aws.tidbcloud.com:4000/sys';
$NVIDIA_API_KEY = getenv('NVIDIA_API_KEY') ?: '';

if ($DATABASE_URL === '') {
    // Sin DATABASE_URL no hay BD. En Koyeb esto significa: falta configurar la env var.
    http_response_code(500);
    die('Falta configurar la variable de entorno DATABASE_URL en el panel del servidor.');
}

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

// Exponer la API key y la configuracion de la API de NVIDIA para el chatbot
define('NVIDIA_API_KEY', $NVIDIA_API_KEY);
define('NVIDIA_URL', 'https://integrate.api.nvidia.com/v1/chat/completions');
define('NVIDIA_MODEL', 'nvidia/nemotron-3.5-super-vl-120b-a12b');
?>
