<?php
define('LOS_NINA_APP', true);
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
        $_SESSION['admin_msg'] = 'Producto agregado correctamente';
        header('Location: inventario.php');
        exit;
    }

    if (isset($_POST['eliminar_producto'])) {
        $pid = (int) $_POST['eliminar_producto'];
        $pdo->prepare('DELETE FROM productos WHERE id = :id')->execute([':id' => $pid]);
        $_SESSION['admin_msg'] = 'Producto eliminado correctamente';
        header('Location: inventario.php');
        exit;
    }
}

$adminMsg = $_SESSION['admin_msg'] ?? '';
unset($_SESSION['admin_msg']);

$esAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';

$titulo = 'Inventario — Abarrotería Los Nina';
$descripcion = 'Catálogo completo de productos de Abarrotería Los Nina con stock actualizado.';
$paginaActiva = 'inventario';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/navbar.php';
?>

<main>
    <section class="section">
        <div class="container">
            <div class="page-header">
                <h1>Inventario</h1>
                <p>Explora por categoría o busca por nombre. Agrega al carrito en un clic.</p>
            </div>

            <?php if ($adminMsg !== ''): ?>
                <div class="toast success show toast-inline" role="status"><?= htmlspecialchars($adminMsg) ?></div>
            <?php endif; ?>

            <?php if ($esAdmin): ?>
                <div class="text-center" style="margin-bottom: 1.5rem;">
                    <a href="#admin-panel" class="btn btn-ghost btn-sm">Ir al panel de administración</a>
                </div>
            <?php endif; ?>

            <div class="inventory-toolbar">
                <div class="category-filters" id="cat-buttons" role="group" aria-label="Filtrar por categoría">
                    <button class="btn btn-outline cat-filter active" data-cat="0">Todas</button>
                    <?php foreach ($categorias as $c): ?>
                        <button class="btn btn-outline cat-filter" data-cat="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></button>
                    <?php endforeach; ?>
                </div>

                <div class="search-field">
                    <label for="search-input" class="sr-only">Buscar producto</label>
                    <input type="text" id="search-input" class="input" placeholder="Buscar producto…" autocomplete="off">
                </div>
            </div>

            <div class="grid-products" id="products-grid" aria-live="polite">
                <?php foreach ($productos as $p): ?>
                    <?php
                    $stock = (int) $p['stock'];
                    $stockClass = $stock > 5 ? 'available' : ($stock > 0 ? 'low' : 'out');
                    $stockText = $stock > 5 ? 'Disponible' : ($stock > 0 ? '¡Últimas ' . $stock . ' unidades!' : 'Agotado');
                    ?>
                    <article class="product-card cat-<?= (int) $p['categoria_id'] ?>">
                        <div class="product-card-img" aria-hidden="true"><?= htmlspecialchars($p['emoji']) ?></div>
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
                                    data-precio="<?= htmlspecialchars($p['precio']) ?>"
                                    <?= $stock <= 0 ? 'disabled' : '' ?>>
                                    <?= $stock <= 0 ? 'Agotado' : 'Agregar al carrito' ?>
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

    <?php if ($esAdmin): ?>
        <section class="section admin-panel" id="admin-panel">
            <div class="container">
                <div class="page-header">
                    <h2>Panel de administración</h2>
                    <p>Agrega productos y actualiza stock o precios sin salir de la página.</p>
                </div>

                <div class="admin-card">
                    <h3>Agregar producto</h3>
                    <form method="POST" action="inventario.php">
                        <div class="admin-grid-2">
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
                            <div class="admin-full">
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
                                <input class="input" type="text" id="new_emoji" name="emoji" value="📦" maxlength="8">
                            </div>
                        </div>
                        <button type="submit" name="agregar_producto" class="btn btn-primary" style="margin-top: 1rem;">Agregar producto</button>
                    </form>
                </div>

                <form id="delete-form" method="POST" action="inventario.php"></form>

                <form method="POST" action="inventario.php">
                    <h3>Actualizar stock y precios</h3>
                    <div class="admin-list">
                        <?php foreach ($productos as $p): ?>
                            <div class="admin-row">
                                <span class="admin-row-name"><?= htmlspecialchars($p['nombre']) ?></span>
                                <div class="admin-row-fields">
                                    <input type="number" step="0.01" min="0"
                                           name="edit_precio[<?= (int) $p['id'] ?>]"
                                           value="<?= htmlspecialchars($p['precio']) ?>"
                                           class="input" aria-label="Precio de <?= htmlspecialchars($p['nombre']) ?>">
                                    <input type="number" min="0"
                                           name="edit_stock[<?= (int) $p['id'] ?>]"
                                           value="<?= (int) $p['stock'] ?>"
                                           class="input" aria-label="Stock de <?= htmlspecialchars($p['nombre']) ?>">
                                    <button type="submit" form="delete-form" name="eliminar_producto" value="<?= (int) $p['id'] ?>" class="btn btn-outline btn-sm"
                                            onclick="return confirm('¿Eliminar este producto?');">Eliminar</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="actualizar_stock" class="btn btn-primary" style="margin-top: 1rem;">Guardar cambios</button>
                </form>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
