-- ============================================================
-- Abarrotería Los Nina — Esquema de Base de Datos
-- ============================================================

CREATE DATABASE IF NOT EXISTS abarroteria_los_nina;
USE abarroteria_los_nina;

-- Categorías de productos
CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Usuarios (admin y clientes)
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('admin', 'cliente') DEFAULT 'cliente',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Productos
CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  categoria_id INT NOT NULL,
  descripcion TEXT,
  precio DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  emoji VARCHAR(10) DEFAULT '📦',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Compras
CREATE TABLE compras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  total DECIMAL(10,2) NOT NULL,
  descuento_aplicado DECIMAL(10,2) DEFAULT 0,
  cupon_usado VARCHAR(50) NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  estado ENUM('pendiente', 'completada', 'cancelada') DEFAULT 'completada',
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Detalle de compras
CREATE TABLE compra_detalle (
  id INT AUTO_INCREMENT PRIMARY KEY,
  compra_id INT NOT NULL,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Reservas (hechas vía chatbot)
CREATE TABLE reservas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre_cliente VARCHAR(100) NOT NULL,
  telefono VARCHAR(20) NOT NULL,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL,
  fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  estado ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'pendiente',
  FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Cupones de descuento
CREATE TABLE cupones (
  codigo VARCHAR(50) PRIMARY KEY,
  porcentaje_descuento INT NOT NULL,
  minimo_compra DECIMAL(10,2) DEFAULT 0,
  activo BOOLEAN DEFAULT TRUE,
  expira_en DATE
);

-- Mensajes de contacto
CREATE TABLE mensajes_contacto (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  mensaje TEXT NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Datos semilla
-- ============================================================

-- Categorías (8)
INSERT INTO categorias (nombre) VALUES
('Abarrotes'),
('Lácteos'),
('Bebidas'),
('Limpieza'),
('Higiene'),
('Snacks'),
('Enlatados'),
('Panadería');

-- Usuario admin
INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES
('Admin Los Nina', 'admin@losnina.com', '$2y$10$VWaNXyZGwY/NlRCnPnJTb.u33VIjMAg9EPBmmHgSpAsFzfQ0ztqmS', 'admin');

-- Cupones
INSERT INTO cupones (codigo, porcentaje_descuento, minimo_compra, activo, expira_en) VALUES
('BIENVENIDO10', 10, 0, TRUE, NULL),
('COMPRA20', 20, 50, TRUE, NULL),
('NINA5', 5, 0, TRUE, DATE_ADD(CURDATE(), INTERVAL 30 DAY));

-- Productos (60)
INSERT INTO productos (nombre, categoria_id, descripcion, precio, stock, emoji) VALUES
('Arroz Extra 5kg', 1, 'Arroz premium extra tamaño familia', 22.90, 45, '🍚'),
('Aceite Primor 1L', 1, 'Aceite vegetal premium', 8.90, 60, '🛢️'),
('Azúcar Blanca 1kg', 1, 'Azúcar rubia blanca', 3.50, 80, '🍬'),
('Sal Marina 500g', 1, 'Sal marina de mar', 2.50, 90, '🧂'),
('Fideos 500g', 1, 'Fideos para sopa', 2.20, 95, '🍝'),
('Papa 5kg', 1, 'Papa amarilla familiar', 4.80, 70, '🥔'),
('Harina 1kg', 1, 'Harina de trigo', 3.90, 75, '🌾'),
('Atún en Aceite 3L', 1, 'Atún seleccionado en aceite', 6.70, 55, '🐟'),

('Leche Gloria 1L', 2, 'Leche entera con vitaminas', 4.50, 100, '🥛'),
('Yogur Natural 12 und', 2, 'Yogur natural sabor original', 5.90, 65, '🍶'),
('Queso Fresco 500g', 2, 'Queso fresco artesanal', 7.20, 50, '🧀'),
('Mantequilla 250g', 2, 'Mantequilla sin sal', 4.60, 58, '🧈'),
('Miel 500g', 2, 'Miel natural de abeja', 6.40, 40, '🍯'),
('Manzana 1kg', 2, 'Manzanas frescas del campo', 3.80, 72, '🍎'),
('Plátano 1kg', 2, 'Plátanos maduros', 2.90, 85, '🍌'),
('Crema Nata 200ml', 2, 'Crema de leche para cocinar', 3.50, 30, '🥛'),

('Agua Mineral 1.5L x12', 3, 'Botellas de agua mineral', 9.90, 100, '💧'),
('Coca-Cola 2L x6', 3, 'Seis botellas de Coca-Cola', 10.50, 90, '🥤'),
('Inka Beer 330ml x12', 3, 'Cerveza artesanal', 14.90, 60, '🍺'),
('Café Premium 500g', 3, 'Café gourmet en grano', 12.90, 48, '☕'),
('Jugo de Naranja 1L x6', 3, 'Jugo natural de naranja', 11.90, 55, '🧃'),
('Té Verde 80 und', 3, 'Té verde en saquitos', 6.80, 70, '🍵'),
('Red Bull 250 x6', 3, 'Energéticas Red Bull', 18.90, 40, '🐂'),

('Detergente Ariel 1kg', 4, 'Detergente para ropa', 8.50, 80, '🧴'),
('Cloro 1L', 4, 'Cloro para ropa', 2.80, 90, '🧪'),
('Jabón Lavanda 500ml', 4, 'Jabón líquido lavanda', 5.50, 75, '🧼'),
('Pañales 50 und', 4, 'Pañales para bebé', 22.90, 35, '👶'),
('Papel Higiénico 4', 4, 'Papel higiénico', 6.90, 100, '🧻'),
('Desinfectante Multiusos 500ml', 4, 'Desinfectante multiusos', 4.50, 65, '🧽'),
('Suavizante Lavanda 750ml', 4, 'Suavizante con aroma lavanda', 5.20, 55, '🌸'),

('Shampoo 400ml', 5, 'Shampoo con proteína', 9.90, 85, '🧴'),
('Acondicionador 400ml', 5, 'Acondicionador suave', 9.50, 80, '🧴'),
('Cepillo Dental 120 und', 5, 'Cepillos dentales', 7.90, 60, '🪥'),
('Desodorante Aerosol 150ml', 5, 'Desodorante spray', 6.50, 70, '💨'),
('Toallitas Húmedas 8', 5, 'Toallitas húmedas', 8.90, 62, '🧻'),
('Crema para Bebé 100g', 5, 'Crema para bebé', 12.90, 45, '🍼'),
('Alcohol Isopropílico 70', 5, 'Alcohol isopropílico', 4.20, 80, '🧪'),

('Papas Fritas 150g x10', 6, 'Papas fritas bolsa familiar', 12.90, 70, '🍟'),
('Chicharrón 500g', 6, 'Chicharrón de cerdo', 8.90, 50, '🥓'),
('Maní Salado 400g', 6, 'Maní salado', 7.50, 60, '🥜'),
('Chocolate 7.5g x24', 6, 'Chocolate para churros', 9.90, 85, '🍫'),
('Galleta María 300g', 6, 'Galletas María', 3.50, 95, '🍪'),
('Palomitas 100g x5', 6, 'Palomitas de maíz', 6.50, 75, '🍿'),
('Alfajor 250g', 6, 'Alfajores mixtos', 8.90, 55, '🥠'),
('Mix Frutas 500g', 6, 'Mix de frutas', 9.50, 40, '🍇'),

('Sopa Fideos 300g', 7, 'Sopa de fideos', 3.50, 88, '🥘'),
('Lentejas 400g', 7, 'Lentejas secas', 3.80, 65, '🫘'),
('Frijoles 400g', 7, 'Frijoles negros', 3.60, 70, '🫘'),
('Tomate 410g', 7, 'Tomate triturado', 2.60, 92, '🍅'),
('Papa Enlatada 500g', 7, 'Papa enlatada', 4.20, 58, '🥔'),
('Pollo Enlatado 400g', 7, 'Pollo enlatado', 7.90, 62, '🍗'),
('Maíz Enlatado 400g', 7, 'Maíz enlatado', 3.40, 66, '🌽'),

('Pan Blanco 800g', 8, 'Pan blanco', 3.90, 78, '🍞'),
('Pan Integral 800g', 8, 'Pan integral', 4.20, 72, '🥖'),
('Empanada de Carne 150g x6', 8, 'Empanadas de carne', 8.90, 64, '🥟'),
('Croissant 4 und', 8, 'Croissants', 5.50, 40, '🥐'),
('Pan Dulce 500g', 8, 'Pan dulce', 4.60, 50, '🍩'),
('Torta de Chocolate 450g', 8, 'Torta de chocolate', 6.80, 35, '🎂'),
('Pan de Ajo 400g', 8, 'Pan de ajo', 4.90, 45, '🍞'),
('Bizcocho de Vainilla 300g', 8, 'Bizcocho de vainilla', 5.20, 42, '🍰');
