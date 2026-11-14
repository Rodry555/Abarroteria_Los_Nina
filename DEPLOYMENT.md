
# DEPLOYMENT.md — Estrategia de Despliegue (Moderna, Gratuita y Estable)

## 1. CONTEXTO: POR QUÉ CAMBIAMOS DE SERVIDOR

Se **abandona** el hosting gratuito tradicional (InfinityFree, 000webhost, etc.) porque en 2026 es inestable: los subdominios gratuitos se caen con frecuencia y no garantizan disponibilidad para una tienda real.

Se adopta un flujo **tipo Vercel + Neon** (push a GitHub → deploy automático → HTTPS gratis), pero **adaptado a PHP + MySQL sin cambiar el stack**.

### Aclaración técnica importante (leer antes de elegir)
- **Vercel NO ejecuta PHP nativo.** Forzarlo requiere reescribir a serverless Node.js. Por eso NO usamos Vercel para el backend.
- **Neon es SOLO PostgreSQL.** Nuestro stack es MySQL. Por eso NO usamos Neon.

### El equivalente exacto, moderno y gratuito para nuestro stack:
| Rol | Servicio elegido | Equivalente conceptual |
|-----|------------------|------------------------|
| Base de datos MySQL serverless | **TiDB Cloud** (capa free 5 GB) | Lo que Neon es para Postgres, pero **100% compatible con MySQL** |
| App PHP + deploy desde GitHub | **Koyeb** (capa free, no se duerme) | Lo que Vercel es para frontend, pero con Docker/PHP |

> **Koyeb** se usa como principal porque su plan gratuito **NO duerme el servicio** (a diferencia de Render), lo cual es crítico para una tienda que debe estar siempre en línea. **Render** queda como Plan B (ver sección 9).

---

## 2. QUÉ CAMBIA Y QUÉ NO CAMBIA EN EL CÓDIGO

### ✅ NO CAMBIA (se mantiene 100% vanilla, tal como se definió en AGENT.md)
- PHP 8 puro, sin frameworks, sin Composer.
- MySQL (TiDB Cloud es MySQL-compatible; el código PDO no se toca).
- JavaScript vanilla (ES6+), sin librerías, sin npm.
- CSS puro compilado (sin Bootstrap/Tailwind).
- Chatbot con NVIDIA NIM API vía cURL nativo.

### 🔧 SÍ CAMBIA (solo el "envoltorio" de despliegue, no la lógica)
1. Se añade un **`Dockerfile`** (≈10 líneas). No es un lenguaje nuevo ni una dependencia: es solo la receta para que Koyeb sepa cómo servir tu PHP, igual que Vercel usa builders internos por debajo. Tu código interno sigue siendo PHP puro.
2. `config/database.php` pasa a leer una **URL de conexión** (`DATABASE_URL`) en lugar de 4 constantes sueltas. Es el estándar moderno (igual que hace Neon/Vercel). El resto del código PDO no cambia.
3. Las credenciales y la API key de NVIDIA se ponen como **variables de entorno** en el panel de Koyeb, **no dentro del código**.

---

## 3. EL DOCKERFILE (obligatorio — copiar EXACTAMENTE)

Crear en la raíz del proyecto:

```dockerfile
FROM php:8.2-apache

# Solo las extensiones que necesita nuestro stack (sin Composer, sin npm)
RUN docker-php-ext-install pdo pdo_mysql

# Copiar todo el proyecto
COPY . /var/www/html/

# Apuntar el DocumentRoot a /public y habilitar .htaccess (rewrite + seguridad)
RUN a2enmod rewrite && \
    printf '<VirtualHost *:80>\n    DocumentRoot /var/www/html/public\n    <Directory /var/www/html/public>\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>\n' > /etc/apache2/sites-enabled/000-default.conf

# Koyeb/Render usan el puerto que se configure en el servicio; Apache escucha en 80
EXPOSE 80
```

Crear también un **`.dockerignore`** en la raíz (evita subir basura):
```
.git
README.md
AGENT.md
VISUAL_AND_TECHNICAL_GUIDE.md
DEPLOYMENT.md
node_modules
```

> Nota de estructura: el código PHP vive en `public/` (index.php, inventario.php, etc.), igual que en AGENT.md. El Dockerfile apunta Apache a `public/`. Las carpetas `config/` y `sql/` quedan FUERA del DocumentRoot, así que **no son accesibles por web** (seguridad gratis). El `.htaccess` interno solo controla rewrite/seguridad dentro de `public/`.

---

## 4. config/database.php (VERSIÓN MODERNA — copiar EXACTAMENTE)

```php
<?php
// Abarrotería Los Nina — Conexión PDO (formato moderno con DATABASE_URL)
// En producción (Koyeb) las credenciales vienen de variables de entorno.
// El fallback con valores vacíos es SOLO para no romper si alguien prueba local.

$DATABASE_URL = getenv('DATABASE_URL') ?: '';
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

// Exponer la API key de NVIDIA para el chatbot (se usa en chatbot.php)
define('NVIDIA_API_KEY', $NVIDIA_API_KEY);
?>
```

> El resto de los archivos PHP (`compra-venta.php`, `chatbot.php`, etc.) siguen usando `$pdo` y `NVIDIA_API_KEY` exactamente como antes. **No hay que reescribir la lógica.**

---

## 5. VARIABLES DE ENTORNO (se configuran en el panel, NO en el código)

| Variable | Valor | Dónde se pega |
|----------|-------|---------------|
| `DATABASE_URL` | La URL que da TiDB Cloud (formato `mysql://user:pass@gateway...:4000/dbname?ssl-mode=REQUIRED`) | Koyeb → Environment variables |
| `NVIDIA_API_KEY` | Tu clave de build.nvidia.com | Koyeb → Environment variables |

Nunca se commitean al repo. El `.gitignore` debe incluir cualquier `.env` local.

---

## 6. FLUJO DE DESPLIEGUE PASO A PASO (para el README.md que genere el agente)

El agente debe escribir el `README.md` con ESTE orden exacto:

```markdown
# Despliegue — Abarrotería Los Nina (TiDB Cloud + Koyeb)

## A. Base de datos (TiDB Cloud — gratuito, 5 GB)
1. Crear cuenta en https://tidbcloud.com (login con GitHub, sin tarjeta).
2. Crear un cluster **Serverless** (capa free).
3. Ir a "Connect" → elegir "General" → copiar el **Connection String** (formato mysql://...).
   - Anotar también usuario, password y host si se pide por separado.
4. Abrir el **SQL Editor** web de TiDB Cloud y pegar TODO el contenido de `sql/schema.sql` → Run.
   (Esto crea las tablas y los 60 productos. TiDB es MySQL-compatible, el schema funciona tal cual.)

## B. Código (GitHub)
5. Crear un repositorio en GitHub y subir todo el proyecto (incluye Dockerfile y .dockerignore).

## C. App (Koyeb — gratuito, no se duerme)
6. Crear cuenta en https://app.koyeb.com (login con GitHub).
7. "Create Service" → "Web service" → "Deploy from a container / GitHub".
   - Conectar el repo. Koyeb detecta el `Dockerfile`.
8. En la configuración del servicio:
   - **Port = 80** (HTTP).
   - Añadir **Environment variables**: `DATABASE_URL` (del paso A) y `NVIDIA_API_KEY`.
9. "Deploy". En 2-3 min se publica en una URL `https://<app>-<user>.koyeb.app` con HTTPS automático.

## D. Pruebas
10. Abrir la URL → debe cargar la portada con los 60 productos.
11. Login admin: admin@losnina.com / admin123 (cambiar password después).
12. Probar carrito, compra (ver que baja el stock en TiDB) y chatbot (responde vía NVIDIA).

## E. Actualizaciones
13. Cada `git push` al repo → Koyeb redeploya automáticamente (igual que Vercel).
```

---

## 7. COMPATIBILIDAD DEL schema.sql CON TiDB

El schema definido en AGENT.md (tablas con `ENUM`, `FOREIGN KEY`, `AUTO_INCREMENT`, `DECIMAL`, `TIMESTAMP`, `VARCHAR`, `TEXT`, `BOOLEAN`) **es compatible con TiDB** sin cambios. Asegurar que el agente:
- Use `ENGINE=InnoDB` (TiDB lo ignora/acepta, no rompe).
- No use funciones MySQL exóticas (no las necesitamos).
- Incluya `SET FOREIGN_KEY_CHECKS` si hace falta (TiDB soporta FK).
- El `password_hash` del admin se genera en PHP, no en SQL, así que el INSERT del admin va con el hash ya calculado (o el agente puede dejar un placeholder y crear el admin vía `registrar`/seed en PHP). **Recomendado:** el agente deja el hash de `admin123` precalculado en el INSERT de `schema.sql`.

---

## 8. CHECKLIST DE DESPLIEGUE (el agente verifica antes de entregar)

- [ ] Existe `Dockerfile` idéntico al de la sección 3.
- [ ] Existe `.dockerignore` con los archivos de meta-documentación excluidos.
- [ ] `config/database.php` usa `getenv('DATABASE_URL')` + `parse_url` + SSL (sección 4).
- [ ] Ninguna credencial ni API key está hardcodeada en el repo.
- [ ] `chatbot.php` usa `NVIDIA_API_KEY` (constante definida en database.php) con cURL nativo.
- [ ] El DocumentRoot apunta a `public/` (config/ y sql/ no accesibles por web).
- [ ] `README.md` sigue el flujo TiDB → GitHub → Koyeb (sección 6).

---

## 9. PLAN B (si Koyeb no estuviera disponible)

**Render** (render.com) con el mismo `Dockerfile`:
- Crear "Web Service" → conectar GitHub → Render detecta Dockerfile.
- Puerto 80, mismas env vars (`DATABASE_URL`, `NVIDIA_API_KEY`).
- ⚠️ Contras: el plan free de Render **duerme** tras 15 min sin tráfico (el primer visitante espera ~30 s). Para una tienda, Koyeb es preferible.

---

## 10. INSTRUCCIÓN FINAL PARA EL AGENTE

> Al generar el proyecto, **incluye el `Dockerfile`, el `.dockerignore`, el `config/database.php` moderno (sección 4) y el `README.md` con el flujo de la sección 6**. Todo el resto del código (PHP, JS, CSS, schema.sql, chatbot con NVIDIA) se mantiene **exactamente** como define `AGENTS.md`, sin frameworks ni dependencias. El despliegue es TiDB Cloud + Koyeb; NO usar InfinityFree ni hosting tradicional.
