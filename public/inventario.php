<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$categorias = $pdo->query('SELECT id, nombre FROM categorias ORDER BY id')->fetchAll();
$productos = $pdo->query(
    'SELECT p.*, c.nombre AS categoria_nombre FROM productos p
     INNER JOIN categorias c ON p.categoria_id = c.id
     ORDER BY p.id'
)->fetchAll();

$categoriaId = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Respuesta AJAX para el buscador en tiempo real
if (isset($_GET['ajax'])) {
    $sql = 'SELECT p.*, c.nombre AS categoria_nombre FROM productos p
            INNER JOIN categorias c ON p.categoria_id = c.id
            WHERE 1=1';
    $condiciones = [];
    $parametros = [];
    if ($categoriaId > 0) {
        $condiciones[] = 'p.categoria_id = :cat';
        $parametros[':cat'] = $categoriaId;
    }
    if ($q !== '') {
        $condiciones[] = '(p.nombre LIKE :q OR p.descripcion LIKE :q)';
        $parametros[':q'] = '%' . $q . '%';
    }
    if (!empty($condiciones)) {
        $sql .= ' AND ' . implode(' AND ', $condiciones);
    }
    $sql .= ' ORDER BY p.id';

    $stmt = $pdo->prepare($sql);
    foreach ($parametros as $clave => $valor) {
        $tipo = ($clave === ':q') ? PDO::PARAM_STR : PDO::PARAM_INT;
        $stmt->bindValue($clave, $valor, $tipo);
    }
    $stmt->execute();
    echo json_encode($stmt->fetchAll());
    exit;
}

// Acciones del panel admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['actualizar_stock'])) {
        if (isset($_POST['edit_stock']) && is_array($_POST['edit_stock'])) {
            foreach ($_POST['edit_stock'] as $pid => $stock) {
                $pdo->prepare('UPDATE productos SET stock = :stock WHERE id = :id')
                    ->execute([':stock' => max(0, (int) $stock), ':id' => (int) $pid]);
            }
        }
        if (isset($_POST['edit_precio']) && is_array($_POST['edit_precio'])) {
            foreach ($_POST['edit_precio'] as $pid => $precio) {
                $pdo->prepare('UPDATE productos SET precio = :precio WHERE id = :id')
                    ->execute([':precio' => (float) $precio, ':id' => (int) $pid]);
            }
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['admin_msg'] = 'Stock y precios actualizados correctamente';
        header('Location: inventario.php');
        exit;
    }

    if (isset($_POST['agregar_producto'])) {
        $nombre = trim($_POST['nombre'] ?? '');
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = (float) ($_POST['precio'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $emoji = trim($_POST['emoji'] ?? '') ?: '📦';
        $pdo->prepare(
            'INSERT INTO productos (nombre, categoria_id, descripcion, precio, stock, emoji) VALUES (:nombre, :cat, :desc, :precio, :stock, :emoji)'
        )->execute([
            ':nombre' => $nombre,
            ':cat' => $categoriaId,
            ':desc' => $descripcion,
            ':precio' => $precio,
            ':stock' => $stock,
            ':emoji' => $emoji,
        ]);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['admin_msg'] = 'Producto agregado correctamente';
        header('Location: inventario.php');
        exit;
    }

    if (isset($_POST['eliminar_producto'])) {
        $pid = (int) $_POST['eliminar_producto'];
        $pdo->prepare('DELETE FROM productos WHERE id = :id')->execute([':id' => $pid]);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['admin_msg'] = 'Producto eliminado correctamente';
        header('Location: inventario.php');
        exit;
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$adminMsg = $_SESSION['admin_msg'] ?? '';
unset($_SESSION['admin_msg']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario — Abarrotería Los Nina</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-inner">
            <a href="index.php" class="navbar-logo">Los Nina<span>.</span></a>
            <div class="navbar-nav">
                <a href="index.php">Inicio</a>
                <a href="inventario.php">Inventario</a>
                <a href="nosotros.php">Nosotros</a>
                <a href="contacto.php">Contacto</a>
                <a href="carrito.php" class="cart-link">🛒 <span class="cart-badge" id="cart-badge">0</span></a>
            </div>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <h1 class="section-title">Inventario</h1>

            <?php if ($adminMsg !== ''): ?>
                <div class="toast success show" style="position: static; transform: none;"><?= htmlspecialchars($adminMsg) ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <div class="text-center" style="margin-bottom: 2rem;">
                    <a href="login.php" class="btn btn-primary">Ir a Login Admin</a>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 2rem;" id="cat-buttons">
                <button class="btn btn-outline cat-filter active" data-cat="0">Todas</button>
                <?php foreach ($categorias as $c): ?>
                    <button class="btn btn-outline cat-filter" data-cat="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></button>
                <?php endforeach; ?>
            </div>

            <div style="margin-bottom: 2rem;">
                <input type="text" id="search-input" class="input" placeholder="Buscar producto...">
            </div>

            <div class="grid-products" id="products-grid">
                <?php foreach ($productos as $p): ?>
                    <?php
                    $stock = (int) $p['stock'];
                    $stockClass = $stock > 5 ? 'available' : ($stock > 0 ? 'low' : 'out');
                    $stockText = $stock > 5 ? 'Disponible' : ($stock > 0 ? '¡Últimas ' . $stock . ' unidades!' : 'Agotado');
                    ?>
                    <article class="product-card">
                        <div class="product-card-img"><?= htmlspecialchars($p['emoji']) ?></div>
                        <div class="product-card-body">
                            <span class="product-category"><?= htmlspecialchars($p['categoria_nombre']) ?></span>
                            <h3 class="product-name"><?= htmlspecialchars($p['nombre']) ?></h3>
                            <div class="product-price">S/ <?= number_format((float) $p['precio'], 2) ?></div>
                            <div class="product-stock <?= $stockClass ?>"><?= $stockText ?></div>
                            <div class="product-actions">
                                <button class="btn btn-primary btn-add"
                                    data-id="<?= (int) $p['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                    data-emoji="<?= htmlspecialchars($p['emoji']) ?>"
                                    data-precio="<?= htmlspecialchars($p['precio']) ?>">
                                    Agregar al carrito
                                </button>
                                <?php if ($stock > 0): ?>
                                    <button class="btn btn-accent btn-reservar"
                                        data-id="<?= (int) $p['id'] ?>"
                                        data-cantidad="1"
                                        data-nombre="<?= htmlspecialchars($p['nombre']) ?>">
                                        Reservar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <section class="section" id="admin-panel" style="background: #fff;">
            <div class="container">
                <h2 class="section-title">Panel Admin</h2>

                <div style="background: #F9FAFB; padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
                    <h3>Agregar producto</h3>
                    <form method="POST" action="inventario.php" style="margin-top: 1rem;">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                            <div>
                                <label for="new_nombre">Nombre</label>
                                <input class="input" type="text" id="new_nombre" name="nombre" required>
                            </div>
                            <div>
                                <label for="new_categoria">Categoría</label>
                                <select class="input" id="new_categoria" name="categoria_id" required>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <label for="new_descripcion">Descripción</label>
                                <input class="input" type="text" id="new_descripcion" name="descripcion">
                            </div>
                            <div>
                                <label for="new_precio">Precio (S/)</label>
                                <input class="input" type="number" step="0.01" min="0" id="new_precio" name="precio" required>
                            </div>
                            <div>
                                <label for="new_stock">Stock</label>
                                <input class="input" type="number" min="0" id="new_stock" name="stock" required>
                            </div>
                            <div>
                                <label for="new_emoji">Emoji</label>
                                <input class="input" type="text" id="new_emoji" name="emoji" value="📦">
                            </div>
                        </div>
                        <button type="submit" name="agregar_producto" class="btn btn-primary" style="margin-top: 1rem;">Agregar producto</button>
                    </form>
                </div>

                <form method="POST" action="inventario.php">
                    <h3>Actualizar stock y precios</h3>
                    <div style="margin-top: 1rem;">
                        <?php foreach ($productos as $p): ?>
                            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; align-items: center; padding: 0.75rem; border-bottom: 1px solid #eee;">
                                <span><?= htmlspecialchars($p['nombre']) ?></span>
                                <input type="number" step="0.01" min="0" name="edit_precio[<?= (int) $p['id'] ?>]" value="<?= htmlspecialchars($p['precio']) ?>" class="input" style="padding: 0.35rem;">
                                <input type="number" min="0" name="edit_stock[<?= (int) $p['id'] ?>]" value="<?= (int) $p['stock'] ?>" class="input" style="padding: 0.35rem;">
                                <button type="submit" name="eliminar_producto" value="<?= (int) $p['id'] ?>" class="btn btn-outline btn-sm">Eliminar</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="actualizar_stock" class="btn btn-primary" style="margin-top: 1rem;">Actualizar stock y precios</button>
                </form>
            </div>
        </section>
    <?php endif; ?>
</div>

    <footer style="background-color: #1F2937; color: #fff; padding: 2rem 0; text-align: center;">
        <div class="container">
            <p>© <?= date('Y') ?> Abarrotería Los Nina — Tu tienda de barrio de confianza</p>
        </div>
    </footer>

    <button id="chat-fab" class="chat-fab" aria-label="Abrir chat">💬</button>
    <div id="chat-panel" class="chat-panel">
        <div class="chat-header">
            <h3>💬 Asistente Los Nina</h3>
            <button id="chat-close" class="chat-close" aria-label="Cerrar chat">✕</button>
        </div>
        <div id="chat-body" class="chat-body"></div>
        <div class="chat-footer">
            <input id="chat-input" type="text" placeholder="Escribe tu mensaje...">
            <button id="chat-send" class="btn btn-primary">➤</button>
        </div>
    </div>
    <div id="toast-container" class="toast-container"></div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/chatbot-widget.js"></script>
</body>
</html>
