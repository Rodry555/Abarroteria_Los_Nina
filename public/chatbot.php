<?php
// ============================================================
// Abarrotería Los Nina — Endpoint del Chatbot (NVIDIA NIM API)
// ============================================================

session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$mensaje = trim($data['mensaje'] ?? '');
$reserva = isset($data['reserva']) && is_array($data['reserva']) ? $data['reserva'] : null;

// ---------- Validación de cupón de descuento ----------
if (isset($data['accion']) && $data['accion'] === 'validar_cupon') {
    $codigo = strtoupper(trim($data['codigo'] ?? ''));
    $respuesta = ['valido' => false];

    if ($codigo !== '') {
        $stmt = $pdo->prepare(
            'SELECT codigo, porcentaje_descuento, minimo_compra, activo, expira_en FROM cupones WHERE codigo = :codigo'
        );
        $stmt->execute([':codigo' => $codigo]);
        $cupon = $stmt->fetch();

        $hoy = date('Y-m-d');
        if (
            $cupon
            && (int) $cupon['activo'] === 1
            && ($cupon['expira_en'] === null || $cupon['expira_en'] >= $hoy)
        ) {
            $respuesta = [
                'valido' => true,
                'codigo' => $cupon['codigo'],
                'porcentaje_descuento' => (int) $cupon['porcentaje_descuento'],
                'minimo_compra' => (float) $cupon['minimo_compra'],
            ];
        }
    }

    echo json_encode($respuesta);
    exit;
}

// ---------- Guardar reserva (vía chatbot) ----------
if (
    $reserva !== null
    && !empty($reserva['producto_id'])
    && !empty($reserva['cantidad'])
) {
    $productoId = (int) $reserva['producto_id'];
    $cantidad = (int) $reserva['cantidad'];
    $nombreCliente = trim($reserva['nombre_cliente'] ?? '');
    $telefono = trim($reserva['telefono'] ?? '');

    if ($nombreCliente === '' || $telefono === '') {
        echo json_encode([
            'respuesta' => '¡Claro! Para reservar necesito tu nombre y tu teléfono. Cuéntame tu nombre y teléfono y te confirmo la reserva.',
        ]);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, nombre, stock FROM productos WHERE id = :id');
    $stmt->execute([':id' => $productoId]);
    $producto = $stmt->fetch();

    if (!$producto) {
        echo json_encode(['respuesta' => 'No encontré ese producto. ¿Quieres buscar otro?']);
        exit;
    }

    if ((int) $producto['stock'] < $cantidad) {
        echo json_encode(['respuesta' => 'Lo sentimos, no hay stock suficiente para esa reserva.']);
        exit;
    }

    $insert = $pdo->prepare(
        'INSERT INTO reservas (nombre_cliente, telefono, producto_id, cantidad) VALUES (:nombre, :telefono, :pid, :cant)'
    );
    $insert->execute([
        ':nombre' => $nombreCliente,
        ':telefono' => $telefono,
        ':pid' => $productoId,
        ':cant' => $cantidad,
    ]);

    echo json_encode([
        'respuesta' => '¡Reserva confirmada! Te esperamos. Tu reserva de ' . $cantidad . ' unidad(es) de ' . $producto['nombre'] . ' fue registrada.',
    ]);
    exit;
}

// ---------- Chat con NVIDIA NIM API ----------
$systemPrompt = 'Eres el asistente de Abarrotería Los Nina. Ayudas con reservas, descuentos y consultas de stock. Responde en español, de forma amigable y concisa. Si el usuario quiere reservar un producto, responde confirmando y pide nombre y teléfono. Si pregunta por descuentos, menciona los cupones disponibles.';

$contexto = 'Catálogo disponible: ';
try {
    $stmt = $pdo->query(
        'SELECT p.id, p.nombre, p.stock, p.precio, c.nombre AS categoria_nombre
         FROM productos p
         INNER JOIN categorias c ON p.categoria_id = c.id
         ORDER BY p.id
         LIMIT 60'
    );
    $productos = $stmt->fetchAll();
    $contexto .= json_encode($productos);
} catch (Exception $e) {
    $contexto .= '[]';
}

$messages = [
    ['role' => 'system', 'content' => $systemPrompt . ' Contexto del catálogo: ' . $contexto],
    ['role' => 'user', 'content' => $mensaje],
];

$ch = curl_init(NVIDIA_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'model' => NVIDIA_MODEL,
    'messages' => $messages,
    'max_tokens' => 300,
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . NVIDIA_API_KEY,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$respuesta = null;
if ($response !== false && $httpCode === 200) {
    $dataApi = json_decode($response, true);
    if (isset($dataApi['choices'][0]['message']['content'])) {
        $respuesta = trim($dataApi['choices'][0]['message']['content']);
    }
}

if ($respuesta === null) {
    echo json_encode(['respuesta' => 'Estoy teniendo problemas para conectarme. Intenta de nuevo en unos momentos.']);
    exit;
}

echo json_encode(['respuesta' => $respuesta]);
