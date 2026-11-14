<?php
// ============================================================
// Abarrotería Los Nina — Procesa Pedido (Transacción PDO)
// ============================================================

session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'mensaje' => 'Metodo no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
$cupon = isset($data['cupon']) ? strtoupper(trim($data['cupon'])) : '';

if (empty($items)) {
    echo json_encode(['exito' => false, 'mensaje' => 'El carrito esta vacio']);
    exit;
}


// Calcular subtotal
$subtotal = 0.0;
foreach ($items as $item) {
    if (!isset($item['id']) || !isset($item['cantidad'])) {
        echo json_encode(['exito' => false, 'mensaje' => 'Carrito con datos invalidos']);
        exit;
    }
    $subtotal += (float) $item['precio'] * (int) $item['cantidad'];
}

// Validar cupón
$descuento = 0.0;
$cuponUsado = null;
if ($cupon !== '') {
    $stmt = $pdo->prepare(
        'SELECT codigo, porcentaje_descuento, minimo_compra, activo, expira_en FROM cupones WHERE codigo = :codigo'
    );
    $stmt->execute([':codigo' => $cupon]);
    $c = $stmt->fetch();
    $hoy = date('Y-m-d');
    if (
        $c
        && (int) $c['activo'] === 1
        && ($c['expira_en'] === null || $c['expira_en'] >= $hoy)
        && $subtotal >= (float) $c['minimo_compra']
    ) {
        $descuento = round($subtotal * ((int) $c['porcentaje_descuento'] / 100), 2);
        $cuponUsado = $c['codigo'];
    }
}

$total = round($subtotal - $descuento, 2);

$pdo->beginTransaction();
try {
    $insertCompra = $pdo->prepare(
        'INSERT INTO compras (usuario_id, total, descuento_aplicado, cupon_usado, estado) VALUES (:uid, :total, :desc, :cupon, :estado)'
    );
    $insertCompra->execute([
        ':uid' => $_SESSION['usuario_id'] ?? null,
        ':total' => $total,
        ':desc' => $descuento,
        ':cupon' => $cuponUsado,
        ':estado' => 'completada',
    ]);
    $compraId = (int) $pdo->lastInsertId();

    foreach ($items as $item) {
        $pid = (int) $item['id'];
        $cantidad = (int) $item['cantidad'];
        if ($cantidad <= 0) {
            continue;
        }
        $stmt = $pdo->prepare('SELECT precio, stock FROM productos WHERE id = :id');
        $stmt->execute([':id' => $pid]);
        $prod = $stmt->fetch();
        if (!$prod) {
            throw new Exception('Producto no encontrado');
        }
        if ((int) $prod['stock'] < $cantidad) {
            throw new Exception('Stock insuficiente para ' . $item['nombre']);
        }
        $sub = round((float) $prod['precio'] * $cantidad, 2);
        $insDetalle = $pdo->prepare(
            'INSERT INTO compra_detalle (compra_id, producto_id, cantidad, subtotal) VALUES (:cid, :pid, :cant, :sub)'
        );
        $insDetalle->execute([':cid' => $compraId, ':pid' => $pid, ':cant' => $cantidad, ':sub' => $sub]);
        $upd = $pdo->prepare('UPDATE productos SET stock = stock - :cant WHERE id = :id');
        $upd->execute([':cant' => $cantidad, ':id' => $pid]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['exito' => false, 'mensaje' => 'Error en la compra: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['exito' => true, 'compra_id' => $compraId]);
