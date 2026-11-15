
# VISUAL_AND_TECHNICAL_GUIDE.md — Abarrotería Los Nina

## 🎨 1. SISTEMA DE DISEÑO (Design System)

El agente debe usar **CSS puro con variables CSS (Custom Properties)** para mantener la consistencia. No usar frameworks. El estilo debe evocar una "tienda de barrio moderna, limpia y de confianza".

### 1.1 Paleta de Colores (Variables CSS)
Definir en `:root` de `style.css`:
```css
:root {
  /* Colores Principales */
  --color-primary: #2E7D32;       /* Verde fresco (confianza, productos frescos) */
  --color-primary-dark: #1B5E20;  /* Hover/Active states */
  --color-accent: #FF9800;        /* Naranja cálido (llamadas a la acción, ofertas) */
  --color-accent-hover: #F57C00;
  
  /* Fondos y Textos */
  --color-bg: #F9FAFB;            /* Gris muy claro, limpio */
  --color-surface: #FFFFFF;       /* Blanco puro para cards y modales */
  --color-text-main: #1F2937;     /* Gris oscuro (mejor que negro puro) */
  --color-text-muted: #6B7280;    /* Gris medio para descripciones */
  
  /* Estados */
  --color-success: #10B981;       /* Stock disponible, éxito */
  --color-warning: #F59E0B;       /* Stock bajo (< 5) */
  --color-danger: #EF4444;        /* Sin stock, errores */
  
  /* UI */
  --radius-sm: 6px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
  --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
  --transition: all 0.2s ease-in-out;
}
```

### 1.2 Tipografía
- Usar **System UI Font Stack** para máximo rendimiento y cero dependencias:
  `font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;`
- Títulos: `font-weight: 700`, `letter-spacing: -0.025em`.
- Cuerpo: `font-weight: 400`, `line-height: 1.6`.

---

## 📐 2. LAYOUT Y RESPONSIVE (Mobile-First)

- **Breakpoints**: 
  - Móvil: `< 768px` (1 columna, menús hamburguesa o scroll horizontal).
  - Tablet: `768px - 1023px` (2-3 columnas en grids).
  - Desktop: `>= 1024px` (4 columnas en grids, sidebars visibles).
- **Contenedor máximo**: `max-width: 1200px`, centrado con `margin: 0 auto`, padding lateral `1rem` en móvil, `2rem` en desktop.

---

## 🧩 3. ESPECIFICACIONES DE COMPONENTES CLAVE

### 3.1 Navbar (Encabezado)
- Sticky (`position: sticky; top: 0; z-index: 50`).
- Fondo blanco con sombra suave (`--shadow-sm`).
- Logo a la izquierda (texto "Los Nina" con icono 🛒 o 🏪).
- Enlaces de navegación a la derecha (escritorio) o menú hamburguesa (móvil).
- **Icono de carrito**: Debe tener un "badge" (círculo rojo/naranja) con el número de items, actualizado en tiempo real por JS.

### 3.2 Product Card (Tarjeta de Producto)
- Fondo `--color-surface`, `border-radius: var(--radius-md)`, `--shadow-sm`, transición en hover a `--shadow-md` y ligero `transform: translateY(-4px)`.
- **Área de imagen**: Fondo gris claro (`#F3F4F6`), centrado, con un emoji grande (ej. `font-size: 3rem`) o placeholder.
- **Cuerpo**: 
  - Categoría (texto pequeño, `--color-text-muted`, mayúsculas).
  - Nombre del producto (negrita, `--color-text-main`).
  - Precio (grande, `--color-primary`, negrita).
  - Stock: Badge pequeño. Si `stock > 5`: verde ("Disponible"). Si `stock <= 5`: naranja ("¡Últimas X unidades!"). Si `stock == 0`: gris ("Agotado"), botón deshabilitado.
- **Botón "Agregar"**: Ancho completo o icono grande, color `--color-primary`, hover `--color-primary-dark`.

### 3.3 Carrito de Compras (`carrito.php`)
- Layout de 2 columnas en desktop: Lista de items (izquierda, 65%) y Resumen de compra (derecha, 35%, sticky).
- **Item en carrito**: Miniatura (emoji), nombre, selector de cantidad (`-` `1` `+`), precio unitario, subtotal, botón de eliminar (icono 🗑️).
- **Resumen**: Subtotal, input para "Código de cupón" con botón "Aplicar", Descuento (en verde), **Total Final** (grande, negrita), Botón "Finalizar Compra" (grande, `--color-accent`, ancho completo).

### 3.4 Chatbot Widget (`chatbot-widget.js` + CSS)
- **Botón flotante**: Esquina inferior derecha, `border-radius: 50%`, `width: 60px`, `height: 60px`, color `--color-primary`, icono 💬, sombra `--shadow-lg`. Animación de pulso suave.
- **Ventana de chat**: 
  - Oculta por defecto, se despliega con animación (`transform: scale(0.9) opacity(0)` a `scale(1) opacity(1)`).
  - Header: Color `--color-primary`, texto "Asistente Los Nina", botón de cerrar (X).
  - Cuerpo: Fondo `--color-bg`, scrollable, `max-height: 400px`.
  - **Burbujas de mensaje**: 
    - Bot: Fondo blanco, alineado a la izquierda, `border-radius: 12px 12px 12px 2px`.
    - Usuario: Fondo `--color-primary`, texto blanco, alineado a la derecha, `border-radius: 12px 12px 2px 12px`.
  - **Indicador de escritura**: 3 puntos animados (`...`) cuando se espera la respuesta de la API.
  - Footer: Input de texto (sin bordes feos, solo borde inferior o redondeado) y botón de enviar (➤).

### 3.5 Notificaciones (Toasts)
- Crear un contenedor fijo (esquina superior derecha) para mensajes de éxito/error (ej. "Producto agregado", "Cupón inválido", "Error de red").
- Aparecen con `transform: translateX(100%)` a `translateX(0)`, duran 3 segundos y desaparecen con fade-out.

---

## ⚙️ 4. DETALLES TÉCNICOS DE UX (JavaScript)

El agente debe implementar estas mejoras de experiencia de usuario en `main.js` y `chatbot-widget.js`:

1. **Carrito en LocalStorage**: 
   - Al cargar la página, leer `localStorage.getItem('carrito')` y actualizar el badge del navbar y la vista del carrito.
   - Al agregar/quitar, guardar inmediatamente y actualizar el DOM **sin recargar**.
2. **Debounced Search**: El buscador de productos en `inventario.php` debe filtrar en tiempo real, pero con un pequeño debounce (300ms) para no saturar el renderizado.
3. **Fetch con Estados de Carga**: 
   - Al aplicar un cupón o enviar el formulario de compra, el botón debe mostrar un spinner o texto "Procesando..." y deshabilitarse (`disabled`) para evitar doble envío.
4. **Manejo de Errores de Red**: Si `chatbot.php` o `compra-venta.php` fallan, mostrar un Toast de error amigable, no dejar la página colgada.
5. **Scroll Suave**: `html { scroll-behavior: smooth; }` para navegación interna.

---

## 📂 5. ESTRUCTURA DEL ARCHIVO CSS (`style.css`)

El agente debe generar un único archivo `style.css` bien organizado, simulando la modularidad de SCSS mediante comentarios y agrupación:

```css
/* =========================================
   1. VARIABLES & RESET
   ========================================= */
:root { ... }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* =========================================
   2. BASE & TIPOGRAFÍA
   ========================================= */
body { ... }
h1, h2, h3 { ... }

/* =========================================
   3. LAYOUT & UTILIDADES
   ========================================= */
.container { ... }
.grid-products { ... }
.text-center { ... }

/* =========================================
   4. COMPONENTES
   ========================================= */
/* 4.1 Navbar */
/* 4.2 Product Card */
/* 4.3 Buttons & Inputs */
/* 4.4 Cart & Checkout */
/* 4.5 Chatbot Widget */
/* 4.6 Toast Notifications */

/* =========================================
   5. RESPONSIVE (Media Queries)
   ========================================= */
@media (min-width: 768px) { ... }
@media (min-width: 1024px) { ... }
```

---

## ✅ 6. CHECKLIST DE CALIDAD VISUAL Y TÉCNICA

Antes de entregar el código, el agente debe verificar mentalmente:
- [ ] ¿El CSS usa variables para todos los colores y espaciados?
- [ ] ¿Las tarjetas de producto tienen un estado visual claro para "stock bajo"?
- [ ] ¿El chatbot tiene animación de "escribiendo..." y las burbujas están bien diferenciadas?
- [ ] ¿Los botones de acción (Agregar al carrito, Pagar) tienen estados `:hover` y `:disabled`?
- [ ] ¿El sitio es completamente usable en un móvil de 375px de ancho?
- [ ] ¿No hay NINGUNA referencia a Bootstrap, Tailwind, jQuery o librerías externas?
- [ ] ¿El código JS maneja los errores de `fetch` con `try/catch` y muestra feedback al usuario?

---

## 🤖 INSTRUCCIÓN FINAL PARA EL AGENTE

**Lee este archivo junto con `AGENT.md`. Al generar el código, prioriza la estética limpia, las micro-interacciones (hover, transiciones) y la robustez del JavaScript vanilla. Entrega el archivo `style.css` completo y listo para producción, sin pasos de compilación pendientes.**
