# Despliegue — Abarrotería Los Nina (TiDB Cloud + Koyeb)

## A. Base de datos (TiDB Cloud — gratuito, 5 GB)
1. Crear cuenta en https://tidbcloud.com (login con GitHub, sin tarjeta).
2. Crear un cluster **Serverless** (capa free).
3. Ir a "Connect" → elegir "General" → copiar el **Connection String** (formato `mysql://user:pass@gateway...:4000/dbname?ssl-mode=REQUIRED`).
   - Anotar también usuario, password y host si se piden por separado.
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
